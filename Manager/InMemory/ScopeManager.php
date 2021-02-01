<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Manager\InMemory;

use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\ScopeInterface;

final class ScopeManager implements ScopeManagerInterface
{
    /**
     * @var ScopeInterface[]
     */
    private $scopes = [];

    /**
     * {@inheritdoc}
     */
    public function find(string $identifier): ?ScopeInterface
    {
        return $this->scopes[$identifier] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ScopeInterface $scope): void
    {
        $this->scopes[(string) $scope] = $scope;
    }
}
