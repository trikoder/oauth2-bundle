<?php

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant as GrantModel;

/**
 * Class GrantOdm
 *
 * @package Trikoder\Bundle\OAuth2Bundle\DBAL\Type
 */
final class GrantOdm extends Type
{

    use ImplodedArray;

    /**
     * @var string
     */
    private const VALUE_DELIMITER = ' ';

    /**
     * @param array $values
     *
     * @return array
     */
    protected function convertDatabaseValues(array $values): array
    {
        foreach ($values as &$value) {
            $value = new GrantModel($value);
        }

        return $values;
    }
}