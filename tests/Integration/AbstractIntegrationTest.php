<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Integration;

use DateInterval;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\Converter\UserConverter;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\AccessTokenRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\AuthCodeRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\ClientRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\RefreshTokenRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\ScopeRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\UserRepository;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\AccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\AuthorizationCodeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ClientManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\RefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ScopeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;

abstract class AbstractIntegrationTest extends TestCase
{
    /**
     * @var ScopeManagerInterface
     */
    protected $scopeManager;

    /**
     * @var ClientManagerInterface
     */
    protected $clientManager;

    /**
     * @var AccessTokenManagerInterface
     */
    protected $accessTokenManager;

    /**
     * @var AuthorizationCodeManagerInterface
     */
    protected $authCodeManager;

    /**
     * @var RefreshTokenManagerInterface
     */
    protected $refreshTokenManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AuthorizationServer
     */
    protected $authorizationServer;

    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var Psr17Factory
     */
    private $psrFactory;

    /**
     * @var bool
     */
    private $requireCodeChallengeForPublicClients = true;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->scopeManager = new ScopeManager();
        $this->clientManager = new ClientManager();
        $this->accessTokenManager = new AccessTokenManager();
        $this->refreshTokenManager = new RefreshTokenManager();
        $this->authCodeManager = new AuthorizationCodeManager();
        $this->eventDispatcher = new EventDispatcher();

        $scopeConverter = new ScopeConverter();
        $scopeRepository = new ScopeRepository($this->scopeManager, $this->clientManager, $scopeConverter, $this->eventDispatcher);
        $clientRepository = new ClientRepository($this->clientManager);
        $accessTokenRepository = new AccessTokenRepository($this->accessTokenManager, $this->clientManager, $scopeConverter);
        $refreshTokenRepository = new RefreshTokenRepository($this->refreshTokenManager, $this->accessTokenManager);
        $userConverter = new UserConverter();
        $userRepository = new UserRepository($this->clientManager, $this->eventDispatcher, $userConverter);
        $authCodeRepository = new AuthCodeRepository($this->authCodeManager, $this->clientManager, $scopeConverter);

        $this->authorizationServer = $this->createAuthorizationServer(
            $scopeRepository,
            $clientRepository,
            $accessTokenRepository,
            $refreshTokenRepository,
            $userRepository,
            $authCodeRepository
        );

        $this->resourceServer = $this->createResourceServer($accessTokenRepository);

        $this->psrFactory = new Psr17Factory();
    }

    protected function getAccessToken(string $jwtToken): ?AccessToken
    {
        $request = $this->createResourceRequest($jwtToken);

        try {
            $response = $this->resourceServer->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            return null;
        }

        return $this->accessTokenManager->find(
            $response->getAttribute('oauth_access_token_id')
        );
    }

    protected function getRefreshToken(string $encryptedPayload): ?RefreshToken
    {
        try {
            $payload = Crypto::decryptWithPassword($encryptedPayload, TestHelper::ENCRYPTION_KEY);
        } catch (CryptoException $e) {
            return null;
        }

        $payload = json_decode($payload, true);

        return $this->refreshTokenManager->find(
            $payload['refresh_token_id']
        );
    }

    protected function createAuthorizationRequest(?string $credentials, array $body = []): ServerRequestInterface
    {
        $request = $this
            ->psrFactory
            ->createServerRequest('', '')
            ->withParsedBody($body)
        ;

        if (null !== $credentials) {
            $request = $request->withHeader('Authorization', sprintf('Basic %s', base64_encode($credentials)));
        }

        return $request;
    }

    protected function createResourceRequest(string $jwtToken): ServerRequestInterface
    {
        return $this
            ->psrFactory
            ->createServerRequest('', '')
            ->withHeader('Authorization', sprintf('Bearer %s', $jwtToken))
        ;
    }

    protected function createAuthorizeRequest(?string $credentials, array $query = []): ServerRequestInterface
    {
        $serverRequest = $this
            ->psrFactory
            ->createServerRequest('', '')
            ->withQueryParams($query)
        ;

        return \is_string($credentials) ? $serverRequest->withHeader('Authorization', sprintf('Basic %s', base64_encode($credentials))) : $serverRequest;
    }

    protected function handleTokenRequest(ServerRequestInterface $serverRequest): array
    {
        $response = $this->psrFactory->createResponse();

        try {
            $response = $this->authorizationServer->respondToAccessTokenRequest($serverRequest, $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        return json_decode($response->getBody()->__toString(), true);
    }

    protected function handleResourceRequest(ServerRequestInterface $serverRequest): ?ServerRequestInterface
    {
        try {
            $serverRequest = $this->resourceServer->validateAuthenticatedRequest($serverRequest);
        } catch (OAuthServerException $e) {
            return null;
        }

        return $serverRequest;
    }

    protected function handleAuthorizationRequest(ServerRequestInterface $serverRequest, $approved = true): ResponseInterface
    {
        $response = $this->psrFactory->createResponse();

        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($serverRequest);
            $user = new User();
            $user->setIdentifier('user');
            $authRequest->setUser($user);
            $authRequest->setAuthorizationApproved($approved);

            $response = $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        return $response;
    }

    protected function extractQueryDataFromUri(string $uri): array
    {
        $uriObject = $this->psrFactory->createUri($uri);

        $data = [];
        parse_str($uriObject->getQuery(), $data);

        return $data;
    }

    protected function enableRequireCodeChallengeForPublicClients(): void
    {
        $this->requireCodeChallengeForPublicClients = true;
    }

    protected function disableRequireCodeChallengeForPublicClients(): void
    {
        $this->requireCodeChallengeForPublicClients = false;
    }

    private function createAuthorizationServer(
        ScopeRepositoryInterface $scopeRepository,
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        UserRepositoryInterface $userRepository,
        AuthCodeRepositoryInterface $authCodeRepository
    ): AuthorizationServer {
        $authorizationServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            new CryptKey(TestHelper::PRIVATE_KEY_PATH, null, false),
            TestHelper::ENCRYPTION_KEY
        );

        $authCodeGrant = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval('PT10M'));

        if (!$this->requireCodeChallengeForPublicClients) {
            $authCodeGrant->disableRequireCodeChallengeForPublicClients();
        }

        $authorizationServer->enableGrantType(new ClientCredentialsGrant());
        $authorizationServer->enableGrantType(new RefreshTokenGrant($refreshTokenRepository));
        $authorizationServer->enableGrantType(new PasswordGrant($userRepository, $refreshTokenRepository));
        $authorizationServer->enableGrantType($authCodeGrant);
        $authorizationServer->enableGrantType(new ImplicitGrant(new DateInterval('PT10M')));

        return $authorizationServer;
    }

    private function createResourceServer(AccessTokenRepositoryInterface $accessTokenRepository): ResourceServer
    {
        return new ResourceServer(
            $accessTokenRepository,
            new CryptKey(TestHelper::PUBLIC_KEY_PATH, null, false)
        );
    }
}
