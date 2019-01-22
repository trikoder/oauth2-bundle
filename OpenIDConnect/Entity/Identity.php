<?php

namespace Trikoder\Bundle\OAuth2Bundle\OpenIDConnect\Entity;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class Identity implements UserEntityInterface, ClaimSetInterface
{
    private $claims = [];

    use EntityTrait;

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function setClaims(array $claims)
    {
        $this->claims = $claims;
    }
}
