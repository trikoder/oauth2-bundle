<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Service;

use Trikoder\Bundle\OAuth2Bundle\Model\Client;

/**
 * @api
 */
interface ClientFinderInterface
{
    public function find(string $identifier): ?Client;
}
