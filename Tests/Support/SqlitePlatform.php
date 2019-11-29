<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Support;

use Doctrine\DBAL\Platforms\SqlitePlatform as DoctrineSqlitePlatform;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;

/**
 * TODO: Remove this once this issue is resolved: https://github.com/doctrine/orm/issues/7930
 */
final class SqlitePlatform extends DoctrineSqlitePlatform
{
    /**
     * {@inheritdoc}
     */
    public function supportsForeignKeyConstraints()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateForeignKeySQL(ForeignKeyConstraint $foreignKey, $table)
    {
        return 'SELECT 1';
    }

    /**
     * {@inheritdoc}
     */
    public function getDropForeignKeySQL($foreignKey, $table)
    {
        return 'SELECT 1';
    }
}
