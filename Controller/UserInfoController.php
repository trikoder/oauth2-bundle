<?php

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zend\Diactoros\Response as Psr7Response;

final class UserInfoController
{
    private $server;
    private $identityProvider;
    private $claimExtractor;

    public function __construct(ResourceServer $server, IdentityProviderInterface $identityProvider, ClaimExtractor $claimExtractor)
    {
        $this->server = $server;
        $this->identityProvider = $identityProvider;
        $this->claimExtractor = $claimExtractor;
    }

    public function indexAction(ServerRequestInterface $serverRequest)
    {
        $request = $this->serverRequestWithBearerToken($serverRequest);

        try {
            $validatedRequest = $this->server->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse(new Psr7Response());
        }

        $userEntity = $this->identityProvider->getUserEntityByIdentifier($validatedRequest->getAttribute('oauth_user_id'));
        $claims = $this->claimExtractor->extract($validatedRequest->getAttribute('oauth_scopes', []), $userEntity->getClaims());

        return new JsonResponse(['sub' => $userEntity->getIdentifier()] + $claims);
    }

    private function serverRequestWithBearerToken(ServerRequestInterface $serverRequest): ServerRequestInterface
    {
        if ($serverRequest->hasHeader('Authorization')) {
            return $serverRequest;
        }

        if ('POST' !== $serverRequest->getMethod()) {
            return $serverRequest;
        }

        if (!\is_array($serverRequest->getParsedBody())) {
            return $serverRequest;
        }

        if (!isset($serverRequest->getParsedBody()['access_token'])) {
            return $serverRequest;
        }

        return $serverRequest->withHeader('Authorization', sprintf('Bearer %s', (string) $serverRequest->getParsedBody()['access_token']));
    }
}
