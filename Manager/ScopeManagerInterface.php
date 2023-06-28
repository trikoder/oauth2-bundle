<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager;

use Trikoder\Bundle\OAuth2Bundle\Model\ScopeInterface;

interface ScopeManagerInterface
{
    public function find(string $identifier): ?ScopeInterface;

    public function save(ScopeInterface $scope): void;
}
