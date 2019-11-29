<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken;

interface AccessTokenManagerInterface extends TokenManagerInterface
{
    public function find(string $identifier): ?AccessToken;

    /**
     * @param AccessToken $accessToken
     */
    public function save(/* AccessToken */ $accessToken): void;
}
