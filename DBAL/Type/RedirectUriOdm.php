<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri as RedirectUriModel;

/**
 * Class RedirectUriOdm
 *
 * @package Trikoder\Bundle\OAuth2Bundle\DBAL\Type
 */
final class RedirectUriOdm extends Type
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
            $value = new RedirectUriModel($value);
        }

        return $values;
    }
}
