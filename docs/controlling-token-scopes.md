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
