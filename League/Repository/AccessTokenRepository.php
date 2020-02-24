<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Trikoder\Bundle\OAuth2Bundle\Converter\ScopeConverterInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\AccessToken as AccessTokenEntity;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\ClientManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\AccessToken as AccessTokenModel;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var AccessTokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var ClientManagerInterface
     */
    private $clientManager;

    /**
     * @var ScopeConverterInterface
     */
    private $scopeConverter;

    public function __construct(
        AccessTokenManagerInterface $accessTokenManager,
        ClientManagerInterface $clientManager,
        ScopeConverterInterface $scopeConverter
    ) {
        $this->accessTokenManager = $accessTokenManager;
        $this->clientManager = $clientManager;
        $this->scopeConverter = $scopeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        $accessToken = $this->accessTokenManager->find($accessTokenEntity->getIdentifier());

        if (null !== $accessToken) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $accessToken = $this->buildAccessTokenModel($accessTokenEntity);

        $this->accessTokenManager->save($accessToken);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken($tokenId)
    {
        $accessToken = $this->accessTokenManager->find($tokenId);

        if (null === $accessToken) {
            return;
        }

        $accessToken->revoke();

        $this->accessTokenManager->save($accessToken);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked($tokenId)
    {
        $accessToken = $this->accessTokenManager->find($tokenId);

        if (null === $accessToken) {
            return true;
        }

        return $accessToken->isRevoked();
    }

    private function buildAccessTokenModel(AccessTokenEntityInterface $accessTokenEntity): AccessTokenModel
    {
        $client = $this->clientManager->find($accessTokenEntity->getClient()->getIdentifier());

        return new AccessTokenModel(
            $accessTokenEntity->getIdentifier(),
            $accessTokenEntity->getExpiryDateTime(),
            $client,
            $accessTokenEntity->getUserIdentifier(),
            $this->scopeConverter->toDomainArray($accessTokenEntity->getScopes())
        );
    }
}
