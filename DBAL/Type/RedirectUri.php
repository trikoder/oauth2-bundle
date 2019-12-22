<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\DBAL\Type;

use Doctrine\DBAL\Types\TextType;
use Trikoder\Bundle\OAuth2Bundle\Model\RedirectUri as RedirectUriModel;

final class RedirectUri extends TextType
{
    use ImplodedArray;

    /**
     * @var string
     */
    private const VALUE_DELIMITER = ' ';

    /**
     * @var string
     */
    private const NAME = 'oauth2_redirect_uri';

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
            $value = new RedirectUriModel($value);
        }

        return $values;
    }
}
