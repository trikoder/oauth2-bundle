<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event\OauthEvent;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AuthenticationScopeFailureEvent extends AbstractOauthEvent
{
    /**
     * @var TokenInterface|null
     */
    private $token;

    public function __construct(OAuthServerException $exception, ResponseInterface $response, ?TokenInterface $token = null)
    {
        parent::__construct($exception, $response);
        $this->token = $token;
    }

    function getEventName(): string
    {
        return OAuth2Events::AUTHENTICATION_SCOPE_FAILURE;
    }

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }
}
