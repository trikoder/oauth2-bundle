# Password grant handling

The `password` grant issues access and refresh tokens that are bound to both a client and a user within your application. As user system implementations can differ greatly on an application basis, the `trikoder.oauth2.user_resolve` was created which allows you to decide which user you want to bind to issuing tokens.

## Requirements

The user model should implement the `Symfony\Component\Security\Core\User\UserInterface` interface.

## Example

### Listener

```php
<?php

namespace App\EventListener;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\UserResolveEvent;

final class UserResolveListener
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @param UserProviderInterface $userProvider
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(UserProviderInterface $userProvider, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userProvider = $userProvider;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @param UserResolveEvent $event
     */
    public function onUserResolve(UserResolveEvent $event): void
    {
        $user = $this->userProvider->loadUserByUsername($event->getUsername());

        if (null === $user) {
            return;
        }

        if (!$this->userPasswordEncoder->isPasswordValid($user, $event->getPassword())) {
            return;
        }

        $event->setUser($user);
    }
}
```

### Service configuration

```yaml
App\EventListener\UserResolveListener:
    arguments:
        - '@app.repository.user_repository'
        - '@security.password_encoder'
    tags:
        - { name: kernel.event_listener, event: trikoder.oauth2.user_resolve, method: onUserResolve }
```

> **NOTE:** The first dependency in this example should be any service class that implements the `UserProviderInterface` interface.
