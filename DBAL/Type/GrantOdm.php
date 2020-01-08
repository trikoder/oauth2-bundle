<?php

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\ODM\MongoDB\Types\Type;
use LogicException;
use Trikoder\Bundle\OAuth2Bundle\Model\Grant as GrantModel;

use function implode;

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
    public const VALUE_DELIMITER = ' ';

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
    public function closureToPHP(): string
    {
        return '$return = explode(\Trikoder\Bundle\OAuth2Bundle\DBAL\Type\GrantOdm::VALUE_DELIMITER, $value);';
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