# Implementing custom grant type

1. Create a class that implements the `\Trikoder\Bundle\OAuth2Bundle\League\AuthorizationServer\GrantTypeInterface` interface.

    Example:

    ```php
    <?php

    declare(strict_types=1);

    namespace App\Grant;

    use DateInterval;
    use League\OAuth2\Server\Grant\AbstractGrant;
    use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
    use Nyholm\Psr7\Response;
    use Psr\Http\Message\ServerRequestInterface;
    use Trikoder\Bundle\OAuth2Bundle\League\AuthorizationServer\GrantTypeInterface;

    final class FakeGrant extends AbstractGrant implements GrantTypeInterface
    {
        /**
         * @var SomeDependency
         */
        private $foo;

        public function __construct(SomeDependency $foo)
        {
            $this->foo = $foo;
        }

        public function getIdentifier()
        {
            return 'fake_grant';
        }

        public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
        {
            return new Response();
        }

        public function getAccessTokenTTL(): ?DateInterval
        {
            return new DateInterval('PT5H');
        }
    }
    ```

1. In order to enable the new grant type in the authorization server you must register the service in the container.
The service must be autoconfigured or you have to manually tag it with the `trikoder.oauth2.authorization_server.grant` tag:

    ```yaml
    services:
        _defaults:
            autoconfigure: true

        App\Grant\FakeGrant: ~
    ```
