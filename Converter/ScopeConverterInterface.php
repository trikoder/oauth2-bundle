<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Converter;

use Trikoder\Bundle\OAuth2Bundle\League\Entity\Scope as ScopeEntity;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;

interface ScopeConverterInterface
{
    public function toDomain(ScopeEntity $scope): ScopeModel;

    /**
     * @param ScopeEntity[] $scopes
     *
     * @return ScopeModel[]
     */
    public function toDomainArray(array $scopes): array;

    public function toLeague(ScopeModel $scope): ScopeEntity;

    /**
     * @param ScopeModel[] $scopes
     *
     * @return ScopeEntity[]
     */
    public function toLeagueArray(array $scopes): array;
}
