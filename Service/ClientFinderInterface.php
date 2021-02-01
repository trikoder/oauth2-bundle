<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Service;

use Trikoder\Bundle\OAuth2Bundle\Model\ClientInterface;

/**
 * @api
 */
interface ClientFinderInterface
{
    public function find(string $identifier): ?ClientInterface;
}
