# Trikoder OAuth 2 Bundle

[![Build Status](https://travis-ci.org/trikoder/oauth2-bundle.svg?branch=master)](https://travis-ci.org/trikoder/oauth2-bundle)
[![Latest Stable Version](https://poser.pugx.org/trikoder/oauth2-bundle/v/stable)](https://packagist.org/packages/trikoder/oauth2-bundle)
[![License](https://poser.pugx.org/trikoder/oauth2-bundle/license)](https://packagist.org/packages/trikoder/oauth2-bundle)
[![Code coverage](https://codecov.io/gh/trikoder/oauth2-bundle/branch/master/graph/badge.svg)](https://codecov.io/gh/trikoder/oauth2-bundle)

Symfony bundle which provides OAuth 2.0 authorization/resource server capabilities. The authorization and resource server actors are implemented using the [thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server) library.

## Important notes

This bundle provides the "glue" between  [thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server) library and Symfony.
It implements [thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server) library in a way specified by its official documentation.
For implementation into Symfony project, please see [bundle documentation](docs/basic-setup.md) and official [Symfony security documentation](https://symfony.com/doc/current/security.html).

## Status

This package is currently in the active development.

## Features

* API endpoint for client authorization and token issuing
* Configurable client and token persistance (includes [Doctrine](https://www.doctrine-project.org/) support)
* Integration with Symfony's [Security](https://symfony.com/doc/current/security.html) layer

## Requirements

* [PHP 7.2](http://php.net/releases/7_2_0.php) or greater
* [Symfony 4.2](https://symfony.com/roadmap/4.2) or [Symfony 3.4](https://symfony.com/roadmap/3.4)
* [league/oauth2-server (versions >=7.2.0 <8.0)](https://packagist.org/packages/league/oauth2-server)

## Installation

1. Require the bundle and a PSR 7/17 implementation with Composer:

    ```sh
    composer require trikoder/oauth2-bundle nyholm/psr7
    ```

    If your project is managed using [Symfony Flex](https://github.com/symfony/flex), the rest of the steps are not required. Just follow the post-installation instructions instead! :tada:

    > **NOTE:** This bundle requires a PSR 7/17 implementation to operate. We recommend that you use [nyholm/psr7](https://github.com/Nyholm/psr7). Check out this [document](docs/psr-implementation-switching.md) if you wish to use a different implementation.

1. Create the bundle configuration file under `config/packages/trikoder_oauth2.yaml`. Here is a reference configuration file:

    ```yaml
    trikoder_oauth2:
        authorization_server: # Required

            # Full path to the private key file.
            # How to generate a private key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
            private_key:          ~ # Required, Example: /var/oauth/private.key

            # Passphrase of the private key, if any
            private_key_passphrase: null

            # The plain string or the ascii safe string used to create a Defuse\Crypto\Key to be used as an encryption key.
            # How to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password
            encryption_key:       ~ # Required

            # The type of value of 'encryption_key'
            encryption_key_type:  plain # One of "plain"; "defuse"

            # How long the issued access token should be valid for.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            access_token_ttl:     PT1H

            # How long the issued refresh token should be valid for.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            refresh_token_ttl:    P1M

            # How long the issued auth code should be valid for.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            auth_code_ttl:        PT10M

            # Whether to enable the client credentials grant
            enable_client_credentials_grant: true

            # Whether to enable the password grant
            enable_password_grant: true

            # Whether to enable the refresh token grant
            enable_refresh_token_grant: true

            # Whether to enable the authorization code grant
            enable_auth_code_grant: true

            # Whether to enable the implicit grant
            enable_implicit_grant: true
        resource_server:      # Required

            # Full path to the public key file
            # How to generate a public key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
            public_key:           ~ # Required, Example: /var/oauth/public.key

        # Scopes that you wish to utilize in your application.
        # This should be a simple array of strings.
        scopes:               []

        # Configures different persistence methods that can be used by the bundle for saving client and token data.
        # Only one persistence method can be configured at a time.
        persistence:          # Required
            doctrine:

                # Name of the entity manager that you wish to use for managing clients and tokens.
                entity_manager:       default
            in_memory:            ~

        # The priority of the event listener that converts an Exception to a Response
        exception_event_listener_priority: 10

        # Set a custom prefix that replaces the default 'ROLE_OAUTH2_' role prefix
        role_prefix:          ROLE_OAUTH2_
    ```

1. Enable the bundle in `config/bundles.php` by adding it to the array:

    ```php
    Trikoder\Bundle\OAuth2Bundle\TrikoderOAuth2Bundle::class => ['all' => true]
    ```

1. Update the database so bundle entities can be persisted using Doctrine:

    ```sh
    bin/console doctrine:schema:update --force
    ```

1. Import the routes inside your `config/routes.yaml` file:

    ```yaml
    oauth2:
        resource: '@TrikoderOAuth2Bundle/Resources/config/routes.xml'
    ```

You can verify that everything is working by issuing a `POST` request to the `/token` endpoint.

**❮ NOTE ❯** It is recommended to control the access to the authorization endpoint
so that only logged in users can approve authorization requests.
You should review your `security.yml` file. Here is a sample configuration:

```yaml
security:
    access_control:
        - { path: ^/authorize, roles: IS_AUTHENTICATED_REMEMBERED }
```

## Configuration

* [Basic setup](docs/basic-setup.md)
* [Controlling token scopes](docs/controlling-token-scopes.md)
* [Password grant handling](docs/password-grant-handling.md)

## Development

[Docker](https://www.docker.com/) 18.03+ and [Docker Compose](https://github.com/docker/compose) 1.13+ are required for the development environment.

### Building the environment

Make sure your Docker images are all built and up-to-date using the following command:

```sh
dev/bin/docker-compose build
```

> **NOTE:** You can target a different version of PHP during development by appending the `--build-arg PHP_VERSION=<version>` argument.

After that, install all the needed packages required to develop the project:

```sh
dev/bin/php composer install
```

### Testing

You can run the test suite using the following command:

```sh
dev/bin/php composer test
```

### Debugging

You can run the debugger using the following command:

```sh
dev/bin/php-debug -d timecop.func_override=1 vendor/bin/phpunit
```

Make sure your IDE is setup properly, for more information check out the [dedicated documentation](docs/debugging.md).

### Code linting

This bundle enforces the PSR-2 and Symfony code standards during development using the [PHP CS Fixer](https://cs.sensiolabs.org/) utility. Before committing any code, you can run the utility so it can fix any potential rule violations for you:

```sh
dev/bin/php composer lint
```

## Changes

All the package releases are recorded in the [CHANGELOG](CHANGELOG.md) file.

## Reporting issues

Use the [issue tracker](https://github.com/trikoder/oauth2-bundle/issues) to report any issues you might have.

## License

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).
