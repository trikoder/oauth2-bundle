<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

final class Client implements ClientEntityInterface
{
    use EntityTrait;
    use ClientTrait;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getIdentifier();
    }

    /**
     * @param string[] $redirectUri
     */
    public function setRedirectUri(array $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }
}
