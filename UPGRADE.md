# Upgrade
Here you will find upgrade steps between releases.

## From 3.1.0 to 3.1.1

The bundle makes modifications to the existing schema. You will need to run the Doctrine schema update process to sync the changes:

```sh
bin/console doctrine:schema:update
```

The schema changes include:

* Removed the `userIdentifier` index from `oauth2_access_token` and `oauth2_authorization_code` tables

## ~~From 3.0 to 3.1.0~~

> **NOTE:** This is now obsolete due to issue [#196](https://github.com/trikoder/oauth2-bundle/issues/196). You can safely ignore it.

### SQL schema changes

The bundle makes modifications to the existing schema. You will need to run the Doctrine schema update process to sync the changes:

```sh
bin/console doctrine:schema:update
```

The schema changes include:

* New `userIdentifier` index on the `oauth2_access_token` and `oauth2_authorization_code` tables

## From 2.x to 3.0

### Console command changes

#### `trikoder:oauth2:clear-expired-tokens`

The following options have been renamed:

* `access-tokens-only` has been renamed to `access-tokens`
* `refresh-tokens-only` has been renamed to `refresh-tokens`

### SQL schema changes

The bundle makes modifications to the existing schema. You will need to run the Doctrine schema update process to sync the changes:

```sh
bin/console doctrine:schema:update
```

The schema changes include:

* New `allow_plain_text_pkce` field on the `oauth2_client` table
* `secret` field on the `oauth2_client` table is now nullable

### Interface changes

The following interfaces have been changed:

#### `Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface`

- [Added the clearExpired() method](https://github.com/trikoder/oauth2-bundle/blob/v3.0.0/Manager/AuthorizationCodeManagerInterface.php#L15)

### Method signature changes

The following method signatures have been changed:

#### `Trikoder\Bundle\OAuth2Bundle\Model\Client`

- [Return type for getSecret() is now nullable](https://github.com/trikoder/oauth2-bundle/blob/v3.0.0/Model/Client.php#L60)

---

> **NOTE:** The underlying [league/oauth2-server](https://github.com/thephpleague/oauth2-server) library has been upgraded from version `7.x` to `8.x`. Please check your code if you are directly implementing their interfaces or extending existing non-final classes.

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
