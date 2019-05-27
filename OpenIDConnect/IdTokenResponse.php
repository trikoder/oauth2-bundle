<?php

namespace Trikoder\Bundle\OAuth2Bundle\OpenIDConnect;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\IdTokenResponse as BaseIdTokenResponse;

class IdTokenResponse extends BaseIdTokenResponse
{
    /** @var string|null */
    private $nonce;

    public function setNonce(string $nonce)
    {
        $this->nonce = $nonce;
    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken, UserEntityInterface $userEntity)
    {
        $builder = parent::getBuilder($accessToken, $userEntity);

        if (null !== $this->nonce) {
            $builder->set('nonce', $this->nonce);
        }

        return $builder;
    }
}
