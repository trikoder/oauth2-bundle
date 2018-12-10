# Basic setup

## Managing clients

For now, clients have to be managed manually using SQL queries. Here are the fields that you can set on the client:

| Field | Type | Required | Description | Notes |
| --- | --- | --- | --- | --- |
| identifier | string(32) | Yes | Client ID used for obtaining an access token. | *N/A* |
| secret | string(128) | Yes | Client secret used for obtaining an access token. | *N/A* |
| redirect_uris | string | No | List of URIs the user can get redirected to after completing the `authorization_code` flow. | Multiple values need to be separated with a space. |
| grants | string | No | List of grants the client is able to utilize. | Multiple values need to be separated with a space. |
| scopes | string | No | List of scopes the client will receive. | Multiple values need to be separated with a space. |
| active | boolean | Yes | Whether the client can obtain new access tokens or not. | *N/A* |

### Add a client

```sql
INSERT INTO `oauth2_client` (`identifier`, `secret`, `active`) VALUES ('foo', 'bar', 1);
```

#### Restrict which grant types a client can access

```sql
UPDATE `oauth2_client` SET `grants` = 'client_credentials password' WHERE `identifier` = 'foo';
```

#### Assign which scopes the client will receive

```sql
UPDATE `oauth2_client` SET `scopes` = 'create read' WHERE `identifier` = 'foo';
```

> **NOTE:** You will have to setup an [event listener](controlling-token-scopes.md#listener) which will assign the client scopes to the issued access token.

### Delete a client

```sql
DELETE FROM `oauth2_client` WHERE `identifier` = 'foo';
```

## Configuring the Security layer

Add two new firewalls in your security configuration:

```yaml
security:
    firewalls:
        api_token:
            pattern: ^/api/token$
            security: false
        api:
            pattern: ^/api
            security: true
            stateless: true
            oauth2: true
```

* The `api_token` firewall will ensure that anyone can access the `/api/token` endpoint in order to be able to retrieve their access tokens.
* The `api` firewall will protect all routes prefixed with `/api` and clients will require a valid access token in order to access them.

Basically, any firewall which sets the `oauth2` parameter to `true` will make any routes that match the selected pattern go through our OAuth 2.0 security layer.

> **NOTE:** The order of firewalls is important because Symfony will evaluate them in the specified order.

## Restricting routes by scope

You can define the `oauth2_scopes` parameter on the route you which to restrict the access to. The user will have to authenticate with **all** scopes which you defined:

```yaml
oauth2_restricted:
    path: /api/restricted
    controller: 'App\Controller\FooController::barAction'
    defaults:
        oauth2_scopes: ['foo', 'bar']
``` 

## Strict scope mode
In default bundle operates in `strict_scopes: true` mode. In this mode scopes must be added to the token request and they must match scopes defined on the client or scopes defined in the configuration. If they don't match or no scope is included in the request invalid scope exception is thrown.

When `strict_scopes` is set to `false` requests with no scope included implicitly get scope from client or configuration.

## Security roles

Once the user gets past the `oauth2` firewall, they will be granted additional roles based on their granted [token scopes](controlling-token-scopes.md). The roles are named in the following format:

```
ROLE_OAUTH2_<scope>
```

Here's one of the example uses cases featuring the [@Security](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/security.html) annotation:

```php
/**
 * @Security("has_role('ROLE_OAUTH2_EDIT')")
 */
public function indexAction()
{
    // ...
}
```

## Auth

There are two possible reasons for the authentication server to reject a request:
- Provided token is expired or invalid (HTTP response 401 `Unauthorized`)
- Provided token is valid but scopes are insufficient (HTTP response 403 `Forbidden`)

## CORS requests

For CORS handling, use [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle)
