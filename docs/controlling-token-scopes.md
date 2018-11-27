# Controlling token scopes

It's possible to alter issued access token's scopes by subscribing to the `trikoder.oauth2.scope_resolve` event.

## Example

The following event listener will make sure the client will only be able to select scopes which they have been granted access to.

### Listener
```php
<?php

namespace App\EventListener;

use Trikoder\Bundle\OAuth2Bundle\Event\ScopeResolveEvent;

final class ScopeResolveListener
{
    /**
     * @param ScopeResolveEvent $event
     */
    public function onScopeResolve(ScopeResolveEvent $event): void
    {
        $clientScopes = $event->getClient()->getScopes();

        if (empty($clientScopes)) {
            // If the client doesn't have any scopes defined, that means he can access everything!
            return;
        }

        $requestedScopes = $event->getScopes();

        if (empty($requestedScopes)) {
            // If the client didn't request any scopes, inherit them from the client.
            $requestedScopes = $clientScopes;
        }

        $finalizedScopes = array_intersect($clientScopes, $requestedScopes);

        if (empty($finalizedScopes)) {
            // If the filtered scopes end up being empty, fallback to using client scopes.
            $finalizedScopes = $clientScopes;
        }

        $event->setScopes(...$finalizedScopes);
    }
}
```

### Service configuration

```yaml
App\EventListener\ScopeResolveListener:
    tags:
        - { name: kernel.event_listener, event: trikoder.oauth2.scope_resolve, method: onScopeResolve }
```
