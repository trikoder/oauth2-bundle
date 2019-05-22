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

## Installation

1. Require the bundle and a PSR 7/17 implementation with Composer:

    ```sh
    composer require trikoder/oauth2-bundle nyholm/psr7 --no-plugins --no-scripts
    ```

    > **NOTE #1:** Due to required pre-configuration, this bundle is currently not compatible with [Symfony Flex](https://github.com/symfony/flex).

    > **NOTE #2:** This bundle requires a PSR 7/17 implementation to operate. We recommend that you use [nyholm/psr7](https://github.com/Nyholm/psr7). Check out this [document](docs/psr-implementation-switching.md) if you wish to use a different implementation.

2. Create the bundle configuration file under `config/packages/trikoder_oauth2.yaml`. Here is a reference configuration file:

    ```yaml
    trikoder_oauth2:

        authorization_server:

            # Full path to the private key file.
            # How to generate a private key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
            private_key:    # Required, Example: /var/oauth/private.key

            # Passphrase of the private key, if any.
            private_key_passphrase: ~  # Optional, default null

            # The string used as an encryption key.
            # How to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password
            encryption_key:    # Required

            # How long the issued access token should be valid for.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            access_token_ttl: PT1H

            # How long the issued refresh token should be valid for.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            refresh_token_ttl: P1M
         
            # Whether to enable the client credentials grant
            enable_client_credentials_grant: true
         
            # Whether to enable the password grant
            enable_password_grant: true
         
            # Whether to enable the refresh token grant
            enable_refresh_token_grant: true

        resource_server:

            # Full path to the public key file
            # How to generate a public key: https://oauth2.thephpleague.com/installation/#generating-public-and-private-keys
            public_key:    # Required, Example: /var/oauth/public.key

        # Scopes that you wish to utilize in your application.
        # This should be a simple array of strings.
        scopes: []

        # Configures different persistence methods that can be used by the bundle for saving client and token data.
        # Only one persistence method can be configured at a time.
        persistence:

            doctrine:

                # Name of the entity manager that you wish to use for managing clients and tokens.
                entity_manager: default # Required

            in_memory: ~
    ```

3. Enable the bundle in `config/bundles.php` by adding it to the array:

    ```php
    Trikoder\Bundle\OAuth2Bundle\TrikoderOAuth2Bundle::class => ['all' => true]
    ```

4. Update the database so bundle entities can be persisted using Doctrine:

    ```sh
    bin/console doctrine:schema:update --force
    ```

5. Import the routes inside your `config/routes.yaml` file:

    ```yaml
    oauth2:
        resource: '@TrikoderOAuth2Bundle/Resources/config/routes.xml'
    ```

You can verify that everything is working by issuing a `GET` request to the `/token` endpoint.

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
