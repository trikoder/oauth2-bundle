<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use LogicException;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope as ScopeModel;

use function explode;
use function implode;

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
    public function convertToPHPValue($value)
    {
        if (null === $value) {
            return [];
        }

        $values = explode(self::VALUE_DELIMITER, $value);

        return $this->convertDatabaseValues($values);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value)
    {
        if (!\is_array($value)) {
            throw new LogicException('This type can only be used in combination with arrays.');
        }

        if (0 === \count($value)) {
            return null;
        }

        foreach ($value as $item) {
            $this->assertValueCanBeImploded($item);
        }

        return implode(self::VALUE_DELIMITER, $value);
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
