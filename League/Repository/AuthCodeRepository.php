<?php

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverter;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\AuthCode as AuthCodeEntity;
use Trikoder\Bundle\OAuth2Bundle\Manager\AuthCodeManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AuthCode as AuthCodeModel;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AuthCodeManagerInterface
     */
    private $authCodeManager;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var ScopeConverter
     */
    private $scopeConverter;

    public function __construct(
        AuthCodeManagerInterface $authCodeManager,
        ClientManagerInterface $clientManager,
        ScopeConverter $scopeConverter
    ) {
        $this->authCodeManager = $authCodeManager;
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $authCode = $this->authCodeManager->find($authCodeEntity->getIdentifier());

        if (null !== $authCode) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $authCode = $this->buildAuthCodeModel($authCodeEntity);

        $this->authCodeManager->save($authCode);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $authCode = $this->authCodeManager->find($codeId);

        if (null === $codeId) {
            return;
        }

        $authCode->revoke();

        $this->authCodeManager->save($authCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $authCode = $this->authCodeManager->find($codeId);

        if (null === $authCode) {
            return true;
        }

        return $authCode->isRevoked();
    }

    private function buildAuthCodeModel(AuthCodeEntity $authCodeEntity): AuthCodeModel
    {
        $client = $this->clientManager->find($authCodeEntity->getClient()->getIdentifier());

        $authCode = new AuthCodeModel(
            $authCodeEntity->getIdentifier(),
            $authCodeEntity->getExpiryDateTime(),
            $client,
            $authCodeEntity->getUserIdentifier(),
            $this->scopeConverter->toDomainArray($authCodeEntity->getScopes())
        );

        return $authCode;
    }
}
