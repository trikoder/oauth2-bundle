<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;

/**
 * Class ScopeOdm
 *
 * @package Trikoder\Bundle\OAuth2Bundle\DBAL\Type
 */
final class ScopeOdm extends Type
{

    use ImplodedArray;

    /**
     * @var string
     */
    private const VALUE_DELIMITER = ' ';

    /**
     * {@inheritdoc}
     */
    protected function convertDatabaseValues(array $values): array
    {
        foreach ($values as &$value) {
            $value = new ScopeModel($value);
        }

        return $values;
    }
}
