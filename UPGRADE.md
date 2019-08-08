# Upgrade
Here you will find upgrade steps between major releases.

## From 1.x to 2.x

### PSR-7/17 HTTP transport implementation

The bundle removed a direct dependency on the [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) package. You now need to explicitly install a PSR 7/17 implementation. We recommand that you use [nyholm/psr7](https://github.com/Nyholm/psr7). Check out this [document](https://github.com/trikoder/oauth2-bundle/blob/v2.0.0/docs/psr-implementation-switching.md) if you wish to use a different implementation.

### Scope resolving changes

Previously [documented](https://github.com/trikoder/oauth2-bundle/blob/v1.1.0/docs/controlling-token-scopes.md) client scope inheriting and restricting is now the new default behavior. You can safely remove the listener from your project.

### SQL schema changes

The bundle adds new tables and constraints to the existing schema. You will need to run the Doctrine schema update process to sync the changes:

```sh
bin/console doctrine:schema:update
```

The schema changes include:

* New `oauth2_authorization_code` table for storing authorization codes
* `access_token` field on the `oauth2_refresh_token` table is now nullable

### Interface changes

The following interfaces have been changed:

#### `Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface`

- [Added the remove() method](https://github.com/trikoder/oauth2-bundle/blob/v2.0.0/Manager/ClientManagerInterface.php#L15)
- [Added the list() method](https://github.com/trikoder/oauth2-bundle/blob/v2.0.0/Manager/ClientManagerInterface.php#L20)

#### `Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface`

- [Added the clearExpired() method](https://github.com/trikoder/oauth2-bundle/blob/v2.0.0/Manager/AccessTokenManagerInterface.php#L15)

#### `Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface`

- [Added the clearExpired() method](https://github.com/trikoder/oauth2-bundle/blob/v2.0.0/Manager/RefreshTokenManagerInterface.php#L15)
