<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Security\Authentication\Token;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuth2TokenFactory
{
    /**
     * @var string
     */
    private $rolePrefix;

    /**
     * OAuth2TokenFactory constructor.
     */
    public function __construct(string $rolePrefix)
    {
        $this->rolePrefix = $rolePrefix;
    }

    public function createOAuth2Token(ServerRequestInterface $serverRequest, UserInterface $user): OAuth2Token
    {
        return new OAuth2Token($serverRequest, $user, $this->rolePrefix);
    }
}
