<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;

trait ImplodedArray
{

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
