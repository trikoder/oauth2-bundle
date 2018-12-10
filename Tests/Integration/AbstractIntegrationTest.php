<?php

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Integration;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\AccessTokenRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\ClientRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\RefreshTokenRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\ScopeRepository;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\UserRepository;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\AccessTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ClientManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\RefreshTokenManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\InMemory\ScopeManager;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken;
use Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures\FixtureFactory;
use Trikoder\Bundle\OAuth2Bundle\Tests\TestHelper;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

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
     * {@inheritdoc}
     */
    protected function setUp($strictScopes = true)
    {
        $this->scopeManager = new ScopeManager();
        $this->clientManager = new ClientManager();
        $this->accessTokenManager = new AccessTokenManager();
        $this->refreshTokenManager = new RefreshTokenManager();
        $this->eventDispatcher = new EventDispatcher();

        FixtureFactory::initializeFixtures(
            $this->scopeManager,
            $this->clientManager,
            $this->accessTokenManager,
            $this->refreshTokenManager
        );

        $scopeConverter = new ScopeConverter();
        $scopeRepository = new ScopeRepository($this->scopeManager, $this->clientManager, $scopeConverter, $this->eventDispatcher, $strictScopes);
        $clientRepository = new ClientRepository($this->clientManager);
        $accessTokenRepository = new AccessTokenRepository($this->accessTokenManager, $this->clientManager, $scopeConverter);
        $refreshTokenRepository = new RefreshTokenRepository($this->refreshTokenManager, $this->accessTokenManager);
        $userRepository = new UserRepository($this->clientManager, $this->eventDispatcher);

        $this->authorizationServer = $this->createAuthorizationServer(
            $scopeRepository,
            $clientRepository,
            $accessTokenRepository,
            $refreshTokenRepository,
            $userRepository
        );

        $this->resourceServer = $this->createResourceServer($accessTokenRepository);
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
        $headers = [
            'Authorization' => sprintf('Basic %s', base64_encode($credentials)),
        ];

        return new ServerRequest([], [], null, null, 'php://temp', $headers, [], [], $body);
    }

    protected function createResourceRequest(string $jwtToken): ServerRequestInterface
    {
        $headers = [
            'Authorization' => sprintf('Bearer %s', $jwtToken),
        ];

        return new ServerRequest([], [], null, null, 'php://temp', $headers);
    }

    protected function handleAuthorizationRequest(ServerRequestInterface $serverRequest): array
    {
        $response = new Response();

        try {
            $response = $this->authorizationServer->respondToAccessTokenRequest($serverRequest, $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        return json_decode($response->getBody(), true);
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

    private function createAuthorizationServer(
        ScopeRepositoryInterface $scopeRepository,
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        UserRepositoryInterface $userRepository
    ): AuthorizationServer {
        $authorizationServer = new AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            TestHelper::PRIVATE_KEY_PATH,
            TestHelper::ENCRYPTION_KEY
        );

        $authorizationServer->enableGrantType(new ClientCredentialsGrant());
        $authorizationServer->enableGrantType(new RefreshTokenGrant($refreshTokenRepository));
        $authorizationServer->enableGrantType(new PasswordGrant($userRepository, $refreshTokenRepository));

        return $authorizationServer;
    }

    private function createResourceServer(AccessTokenRepositoryInterface $accessTokenRepository): ResourceServer
    {
        return new ResourceServer(
            $accessTokenRepository,
            TestHelper::PUBLIC_KEY_PATH
        );
    }
}
