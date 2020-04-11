<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Entity;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class Scope implements ScopeEntityInterface
{
    use EntityTrait;

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
