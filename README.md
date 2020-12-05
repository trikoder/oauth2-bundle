# Trikoder OAuth 2 Bundle

[![Build Status](https://github.com/trikoder/oauth2-bundle/workflows/Tests/badge.svg?branch=master)](https://github.com/trikoder/oauth2-bundle/actions)
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
* [Symfony 4.4](https://symfony.com/roadmap/4.4) or [Symfony 5.x](https://symfony.com/roadmap/5.0)

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

            # Passphrase of the private key, if any.
            private_key_passphrase: null

            # The plain string or the ascii safe string used to create a Defuse\Crypto\Key to be used as an encryption key.
            # How to generate an encryption key: https://oauth2.thephpleague.com/installation/#string-password
            encryption_key:       ~ # Required

            # The type of value of "encryption_key".
            encryption_key_type:  plain # One of "plain"; "defuse"

            # How long the issued access token should be valid for, used as a default if there is no grant type specific value set.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            access_token_ttl:     PT1H

            # How long the issued refresh token should be valid for, used as a default if there is no grant type specific value set.
            # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
            refresh_token_ttl:    P1M

            # Enable and configure grant types.
            grant_types:
                authorization_code:

                    # Whether to enable the authorization code grant.
                    enable:               true

                    # How long the issued access token should be valid for the authorization code grant.
                    access_token_ttl:     ~

                    # How long the issued refresh token should be valid for the authorization code grant.
                    refresh_token_ttl:    ~

                    # How long the issued authorization code should be valid for.
                    # The value should be a valid interval: http://php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters
                    auth_code_ttl:        PT10M

                    # Whether to require code challenge for public clients for the authorization code grant.
                    require_code_challenge_for_public_clients: true
                client_credentials:

                    # Whether to enable the client credentials grant.
                    enable:               true

                    # How long the issued access token should be valid for the client credentials grant.
                    access_token_ttl:     ~
                implicit:

                    # Whether to enable the implicit grant.
                    enable:               true

                    # How long the issued access token should be valid for the implicit grant.
                    access_token_ttl:     ~
                password:

                    # Whether to enable the password grant.
                    enable:               true

                    # How long the issued access token should be valid for the password grant.
                    access_token_ttl:     ~

                    # How long the issued refresh token should be valid for the password grant.
                    refresh_token_ttl:    ~
                refresh_token:

                    # Whether to enable the refresh token grant.
                    enable:               true

                    # How long the issued access token should be valid for the refresh token grant.
                    access_token_ttl:     ~

                    # How long the issued refresh token should be valid for the refresh token grant.
                    refresh_token_ttl:    ~
        resource_server:      # Required

            # Full path to the public key file.
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

        # The priority of the event listener that converts an Exception to a Response.
        exception_event_listener_priority: 10

        # Set a custom prefix that replaces the default "ROLE_OAUTH2_" role prefix.
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
* [Implementing custom grant type](docs/implementing-custom-grant-type.md)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Versioning

This project adheres to [Semantic Versioning 2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

However, starting with version 4, we only promise to follow SemVer on structural elements marked with the [@api tag](https://github.com/php-fig/fig-standards/blob/2668020622d9d9eaf11d403bc1d26664dfc3ef8e/proposed/phpdoc-tags.md#51-api).

## Changes

All the package releases are recorded in the [CHANGELOG](CHANGELOG.md) file.

## Reporting issues

Use the [issue tracker](https://github.com/trikoder/oauth2-bundle/issues) to report any issues you might have.

## License

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).
