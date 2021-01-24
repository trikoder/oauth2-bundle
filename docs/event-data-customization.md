# Event/Data customization

## Table of contents
- [MISSING_AUTHORIZATION_HEADER - Customizing the response on invalid authorization header](#oauth2eventsmissing_authorization_header---customizing-the-response-on-invalid-authorization-header)
- [AUTHENTICATION_SCOPE_FAILURE - Customizing the response on invalid scope](#oauth2eventsauthentication_scope_failure---customizing-the-response-on-invalid-scope)
- [INVALID_CREDENTIALS - Customizing the response on credentials failure](#oauth2eventsinvalid_credentials---customizing-the-response-on-credentials-failure)
- [AUTHENTICATION_FAILURE - Customizing the response on authentication failure](#oauth2eventsauthentication_failure---customizing-the-response-on-authentication-failure)

## OAuth2Events::MISSING_AUTHORIZATION_HEADER - Customizing the response on invalid authorization header

Called when the `Authorization Bearer` was not found or is malformed.

Example:

```php
<?php declare(strict_types=1);

namespace App\EventListener\Kernel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Event\MissingAuthorizationHeaderEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

class OAuthListener implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      OAuth2Events::MISSING_AUTHORIZATION_HEADER => "onMissingAuthorizationHeader",
    ];
  }

  public function onMissingAuthorizationHeader(MissingAuthorizationHeaderEvent $event): void {
    $response = new JsonResponse("Invalid header.", Response::HTTP_UNAUTHORIZED);
    $event->setResponse($response);
  }
}
```

## OAuth2Events::AUTHENTICATION_SCOPE_FAILURE - Customizing the response on invalid scope

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

## OAuth2Events::INVALID_CREDENTIALS - Customizing the response on credentials failure

Called when the credential verification failed.

Often when no user was return after calling `OAuth2Events::USER_RESOLVE`

Example:

```php
<?php declare(strict_types=1);

namespace App\EventListener\Kernel;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Trikoder\Bundle\OAuth2Bundle\Event\InvalidCredentialsEvent;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Events;

class OAuthListener implements EventSubscriberInterface {
  public static function getSubscribedEvents() {
    return [
      OAuth2Events::INVALID_CREDENTIALS => "onInvalidCredentials",
    ];
  }

  public function onInvalidCredentials(InvalidCredentialsEvent $event): void {
    $response = new JsonResponse("Wrong username/password.", Response::HTTP_UNAUTHORIZED);
    $event->setResponse($response);
  }
}
```

## OAuth2Events::AUTHENTICATION_FAILURE - Customizing the response on authentication failure

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
