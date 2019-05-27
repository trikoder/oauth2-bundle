<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Fixtures;

use ArrayObject;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends ArrayObject implements UserInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this['roles'] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return FixtureFactory::FIXTURE_PASSWORD;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return FixtureFactory::FIXTURE_USER;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return;
    }
}
