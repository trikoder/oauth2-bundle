# Basic setup

## Managing clients

There are several commands available to manage clients.

### Add a client

To add a client you should use the `trikoder:oauth2:create-client` command.

```sh
Description:
  Creates a new oAuth2 client

Usage:
  trikoder:oauth2:create-client [options] [--] [<identifier> [<secret>]]

Arguments:
  identifier                         The client identifier
  secret                             The client secret

Options:
      --redirect-uri[=REDIRECT-URI]  Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs. (multiple values allowed)
      --grant-type[=GRANT-TYPE]      Sets allowed grant type for client. Use this option multiple times to set multiple grant types. (multiple values allowed)
      --scope[=SCOPE]                Sets allowed scope for client. Use this option multiple times to set multiple scopes. (multiple values allowed)
```


### Update a client

To update a client you should use the `trikoder:oauth2:update-client` command.

```sh
Description:
  Updates an oAuth2 client

Usage:
  trikoder:oauth2:update-client [options] [--] <identifier>

Arguments:
  identifier                         The client ID

Options:
      --redirect-uri[=REDIRECT-URI]  Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs. (multiple values allowed)
      --grant-type[=GRANT-TYPE]      Sets allowed grant type for client. Use this option multiple times to set multiple grant types. (multiple values allowed)
      --scope[=SCOPE]                Sets allowed scope for client. Use this option multiple times to set multiple scopes. (multiple values allowed)
      --deactivated                  If provided, it will deactivate the given client.
```

#### Restrict which grant types a client can access

```sh
$ bin/console trikoder:oauth2:update-client --grant-type client_credentials --grant-type password foo
```

#### Assign which scopes the client will receive


```sh
$ bin/console trikoder:oauth2:update-client --scope create --scope read foo
```

> **NOTE:** You will have to setup an [event listener](controlling-token-scopes.md#listener) which will assign the client scopes to the issued access token.

### Delete a client
For now, clients deletion have to be managed manually using SQL queries.

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
