# PSR 7/17 implementation switching

This bundle requires a PSR 7/17 implementation to operate. We recommend that you use [nyholm/psr7](https://github.com/Nyholm/psr7) as this is the one Symfony [suggests](https://symfony.com/doc/current/components/psr7.html#installation) themselves.

The recommended implementation requires no extra configuration. Check out the example below to see how to use a different one.

## Example

In this example we'll use the [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) package.

1. Require the package via Composer:

    ```sh
    composer require zendframework/zend-diactoros
    ```

2. Register factory services and alias them to PSR interfaces in your service configuration file:

    ```yaml
    services:
        # Register services
        Zend\Diactoros\ServerRequestFactory: ~
        Zend\Diactoros\StreamFactory: ~
        Zend\Diactoros\UploadedFileFactory: ~
        Zend\Diactoros\ResponseFactory: ~

        # Setup autowiring aliases
        Psr\Http\Message\ServerRequestFactoryInterface: '@Zend\Diactoros\ServerRequestFactory'
        Psr\Http\Message\StreamFactoryInterface: '@Zend\Diactoros\StreamFactory'
        Psr\Http\Message\UploadedFileFactoryInterface: '@Zend\Diactoros\UploadedFileFactory'
        Psr\Http\Message\ResponseFactoryInterface: '@Zend\Diactoros\ResponseFactory'
    ```
