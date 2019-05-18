<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Trikoder\Bundle\OAuth2Bundle\Model\Grant as GrantModel;

final class Grant extends ImplodedArray
{
    /**
     * @var string
     */
    private const NAME = 'oauth2_grant';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertDatabaseValues(array $values): array
    {
        foreach ($values as &$value) {
            $value = new GrantModel($value);
        }

        return $values;
    }
}
