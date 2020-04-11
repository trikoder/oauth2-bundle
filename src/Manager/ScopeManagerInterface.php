<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

interface ScopeManagerInterface
{
    public function find(string $identifier): ?Scope;

    public function save(Scope $scope): void;
}
