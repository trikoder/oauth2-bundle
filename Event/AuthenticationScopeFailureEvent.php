<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class AuthenticationScopeFailureEvent extends AbstractOauthEvent
{
    /**
     * @var TokenInterface
     */
    private $token;

    public function __construct(OAuthServerException $exception, ResponseInterface $response, TokenInterface $token)
    {
        parent::__construct($exception, $response);
        $this->token = $token;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}
