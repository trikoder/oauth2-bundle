# Authorization code grant

Authorization code grant has two steps

1. Acquiring authorization code
2. Getting token from authorization code

## Requirements

To use authorization code grant `enable_auth_code_grant` parameter inside `authorization_server` must be set to `true` (it is set to `true` by default).

### Example: config.yml

```yaml
trikoder_oauth2:
    authorization_server:
        enable_auth_code_grant: true
```

After authorization code grant is enabled, token and authorization endpoints must be set.
It can be done by including `Resources/config/routes.xml` which will provide `/authorize` or `/token` endpoints or manually by setting

1. Controller `Trikoder\Bundle\OAuth2Bundle\Controller\AuthorizationController::indexAction` with `GET` method for authorization endpoint
2. Controller `Trikoder\Bundle\OAuth2Bundle\Controller\TokenController::indexAction` with `POST` method for token endpoint

### Example: custom setup

```yaml
oauth2_authorization_code:
    controller: Trikoder\Bundle\OAuth2Bundle\Controller\AuthorizationController::indexAction
    path: /oauth2-authorization-code

oauth2_token:
    controller: Trikoder\Bundle\OAuth2Bundle\Controller\TokenController::indexAction
    path: /api/token
```

After assigning routes, listener for `trikoder.oauth2.authorization_request_resolve` must be configured.

`\Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent` (whose name is `trikoder.oauth2.authorization_request_resolve`) consist of three important methods which have to be used

1. `setUser(?UserInterface $user)` and `resolveAuthorization(bool $authorizationResolution)` when user is already logged in when accessing authorization endpoint
2. `setResponse(ResponseInterface $response)` when user needs to log in before authorization server can issue authorization code

### Example: (services.yml and php class)

```yaml
    BestNamespace\OAuthLogin\Listener\AuthorizationCodeListener:
        tags:
            - { name: kernel.event_listener, event: 'trikoder.oauth2.authorization_request_resolve', method: onAuthorizationRequestResolve }
```

```php
<?php

declare(strict_types=1);

namespace BestNamespace\OAuthLogin\Listener;

use Nyholm\Psr7\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;

final class AuthorizationCodeListener
{
    private $security;
    private $urlGenerator;
    private $requestStack;

    public function __construct(
        Security $security,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    public function onAuthorizationRequestResolve(AuthorizationRequestResolveEvent $event)
    {
        if (null !== ($user = $this->security->getUser())) {
            $event->setUser($user);
            $event->resolveAuthorization(true);
        } else {
            $event->setResponse(
                new Response(
                    302,
                    [
                        'Location' => $this->urlGenerator->generate(
                            'login',
                            [
                                'returnUrl' => $this->requestStack->getMasterRequest()->getUri(),
                            ]
                        ),
                    ]
                )
            );
        }
    }
}
```

After listener is configured new client can be registered.

### Example: cli

```
bin/console trikoder:oauth2:create-client best_client not_so_secret --redirect-uri "https://www.bestclient.com/" --grant-type "authorization_code" --scope "user.view"
```

This example assumes scope `user.view` is already registered scope inside `trikoder_oauth2` configuration

### Example: config.yml

```yaml
trikoder_oauth2:
    scopes:
        - 'user.view'
```

After client is registered he can communicate with your server using authorization code grant.
