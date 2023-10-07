# Controlling token scopes

It's possible to alter issued access token's scopes by subscribing to the `trikoder.oauth2.scope_resolve` event.

## Example

### Listener
```php
<?php

namespace App\EventListener;

use Trikoder\Bundle\OAuth2Bundle\Event\ScopeResolveEvent;

final class ScopeResolveListener
{
    public function onScopeResolve(ScopeResolveEvent $event): void
    {
        $requestedScopes = $event->getScopes();

        // Make adjustments to the client's requested scopes...
        ...

        $event->setScopes(...$requestedScopes);
    }
}
```

### Service configuration

```yaml
App\EventListener\ScopeResolveListener:
    tags:
        - { name: kernel.event_listener, event: trikoder.oauth2.scope_resolve, method: onScopeResolve }
```

## Work with refresh token
The scopes created in this way will not be recognized by the library when trying to login using the `refresh_token` grant method.  
In order to append new scopes, you need to override the service `Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface`.  
Here is an example:
```yaml
# services.yaml
Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface:
        alias: Path\To\Custom\Manager
```
in the manager you can implement the business logic needed to validate the tokens you issued in the scope resolve listener
```php
<?php

namespace App\Guards;


use Trikoder\Bundle\OAuth2Bundle\Manager\ScopeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\Scope;

class CustomScopeManager implements ScopeManagerInterface
{

    public function find(string $identifier): ?Scope
    {
        // your logic here, if created return a new scope with the name
        return null;
    }

    public function save(Scope $scope): void
    {
        // your logic here
    }
}
```
