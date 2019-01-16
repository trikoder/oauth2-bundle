<?php

namespace Trikoder\Bundle\OAuth2Bundle\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\User;
use Zend\Diactoros\Response;

final class AuthorizationController
{
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(AuthorizationServer $server, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->server = $server;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function indexAction(ServerRequestInterface $serverRequest): ResponseInterface
    {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new LogicException('There is no logged in user. Review your security config to protect this endpoint.');
        }

        $serverResponse = new Response();

        try {
            $authRequest = $this->server->validateAuthorizationRequest($serverRequest);
            $authRequest->setUser($this->getUserEntity());
            $authRequest->setAuthorizationApproved($this->authorizationChecker->isGranted($authRequest));

            return $this->server->completeAuthorizationRequest($authRequest, $serverResponse);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($serverResponse);
        }
    }

    private function getUserEntity(): User
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new LogicException('There is no security token available. Review your security config to protect endpoint.');
        }

        $user = $token->getUser();
        $username = $user instanceof UserInterface ? $user->getUsername() : (string) $user;

        $userEntity = new User();
        $userEntity->setIdentifier($username);

        return $userEntity;
    }
}
