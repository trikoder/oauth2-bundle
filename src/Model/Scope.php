<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Model;

class Scope
{
    /**
     * @var string
     */
    private $scope;

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    public function __toString(): string
    {
        return $this->scope;
    }
}
