<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Tests\Integration;

use DateTimeImmutable;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\AuthCodeRepository;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;
use Trikoder\Bundle\OAuth2Bundle\Model\Client;

final class AuthCodeRepositoryTest extends AbstractIntegrationTest
{
    public function testAuthCodeRevoking(): void
    {
        $identifier = 'foo';

        $authCode = new AuthorizationCode(
            $identifier,
            new DateTimeImmutable(),
            new Client('bar', 'baz'),
            null,
            []
        );

        $this->authCodeManager->save($authCode);

        $this->assertSame($authCode, $this->authCodeManager->find($identifier));

        $authCodeRepository = new AuthCodeRepository($this->authCodeManager, $this->clientManager, new ScopeConverter());

        $authCodeRepository->revokeAuthCode($identifier);

        $this->assertTrue($authCode->isRevoked());
        $this->assertSame($authCode, $this->authCodeManager->find($identifier));
    }
}
