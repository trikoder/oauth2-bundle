# Data customization

## Table of contents
- [Customizing the response on invalid authorization header](#customizing-the-response-on-invalid-authorization-header)
- [Customizing the response on invalid scope](#customizing-the-response-on-invalid-scope)
- [Customizing the response on authentication failure](#customizing-the-response-on-authentication-failure)

## Customizing the response on invalid authorization header

Called when the `Authorization Bearer` was not found or is malformed.

Example:

```php
<?php declare(strict_types=1);

namespace App\EventListener\Kernel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Event\InvalidAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

class OAuthListener implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      OAuth2Events::INVALID_AUTHORIZATION_HEADER => "onInvalidAuthorizationHeader",
    ];
  }

  public function onInvalidAuthorizationHeader(InvalidAuthorizationHeaderEvent $event): void {
    $response = new JsonResponse("Invalid header.", Response::HTTP_UNAUTHORIZED);
    $event->setResponse($response);
  }
}
```

## Customizing the response on invalid scope

Called when the user didn't have the right scope defined.

Example:

```php
<?php declare(strict_types=1);

namespace App\EventListener\Kernel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationScopeFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

class OAuthListener implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      OAuth2Events::AUTHENTICATION_SCOPE_FAILURE => "onInvalidScope",
    ];
  }

  public function onInvalidScope(AuthenticationScopeFailureEvent $event): void {
    $response = new JsonResponse("Invalid scope.", Response::HTTP_UNAUTHORIZED);
    $event->setResponse($response);
  }
}
```

## Customizing the response on authentication failure

Called when the authentication failed.

Example:

```php
<?php declare(strict_types=1);

namespace App\EventListener\Kernel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthenticationFailureEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

class OAuthListener implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      OAuth2Events::AUTHENTICATION_FAILURE => "onAuthenticationFailure",
    ];
  }

  public function onAuthenticationFailure(AuthenticationFailureEvent $event): void {
    $response = new JsonResponse("Invalid scope.", Response::HTTP_UNAUTHORIZED);
    $event->setResponse($response);
  }
}
```
