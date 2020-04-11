<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverterInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\AuthCode;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthorizationCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationCode;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AuthorizationCodeManagerInterface
     */
    private $authorizationCodeManager;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var ScopeConverterInterface
     */
    private $scopeConverter;

    public function __construct(
        AuthorizationCodeManagerInterface $authorizationCodeManager,
        ClientManagerInterface $clientManager,
        ScopeConverterInterface $scopeConverter
    ) {
        $this->authorizationCodeManager = $authorizationCodeManager;
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCode)
    {
        $authorizationCode = $this->authorizationCodeManager->find($authCode->getIdentifier());

        if (null !== $authorizationCode) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $authorizationCode = $this->buildAuthorizationCode($authCode);

        $this->authorizationCodeManager->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $authorizationCode = $this->authorizationCodeManager->find($codeId);

        if (null === $authorizationCode) {
            return;
        }

        $authorizationCode->revoke();

        $this->authorizationCodeManager->save($authorizationCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $authorizationCode = $this->authorizationCodeManager->find($codeId);

        if (null === $authorizationCode) {
            return true;
        }

        return $authorizationCode->isRevoked();
    }

    private function buildAuthorizationCode(AuthCode $authCode): AuthorizationCode
    {
        $client = $this->clientManager->find($authCode->getClient()->getIdentifier());

        return new AuthorizationCode(
            $authCode->getIdentifier(),
            $authCode->getExpiryDateTime(),
            $client,
            $authCode->getUserIdentifier(),
            $this->scopeConverter->toDomainArray($authCode->getScopes())
        );
    }
}
