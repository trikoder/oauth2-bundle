<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;

final class Scope extends ImplodedArray
{
    /**
     * @var string
     */
    private const NAME = 'oauth2_scope';

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
            $value = new ScopeModel($value);
        }

        return $values;
    }
}
