<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;
use InvalidArgumentException;
use LogicException;

abstract class ImplodedArray extends TextType
{
    /**
     * @var string
     */
    private const VALUE_DELIMITER = ' ';

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
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
    public function convertToPHPValue($value, AbstractPlatform $platform)
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
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $fieldDeclaration['length'] = 65535;

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    private function assertValueCanBeImploded($value): void
    {
        if (null === $value) {
            return;
        }

        if (is_scalar($value)) {
            return;
        }

        if (\is_object($value) && method_exists($value, '__toString')) {
            return;
        }

        throw new InvalidArgumentException(sprintf('The value of \'%s\' type cannot be imploded.', \gettype($value)));
    }

    abstract protected function convertDatabaseValues(array $values): array;
}
