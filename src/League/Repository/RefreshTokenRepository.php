<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\League\Repository;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\RefreshToken as RefreshTokenEntity;
use Trikoder\Bundle\OAuth2Bundle\Manager\AccessTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Manager\RefreshTokenManagerInterface;
use Trikoder\Bundle\OAuth2Bundle\Model\RefreshToken as RefreshTokenModel;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var RefreshTokenManagerInterface
     */
    private $refreshTokenManager;

    /**
     * @var AccessTokenManagerInterface
     */
    private $accessTokenManager;

    public function __construct(
        RefreshTokenManagerInterface $refreshTokenManager,
        AccessTokenManagerInterface $accessTokenManager
    ) {
        $this->refreshTokenManager = $refreshTokenManager;
        $this->accessTokenManager = $accessTokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $refreshToken = $this->refreshTokenManager->find($refreshTokenEntity->getIdentifier());

        if (null !== $refreshToken) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $refreshToken = $this->buildRefreshTokenModel($refreshTokenEntity);

        $this->refreshTokenManager->save($refreshToken);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $refreshToken = $this->refreshTokenManager->find($tokenId);

        if (null === $refreshToken) {
            return;
        }

        $refreshToken->revoke();

        $this->refreshTokenManager->save($refreshToken);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $refreshToken = $this->refreshTokenManager->find($tokenId);

        if (null === $refreshToken) {
            return true;
        }

        return $refreshToken->isRevoked();
    }

    private function buildRefreshTokenModel(RefreshTokenEntityInterface $refreshTokenEntity): RefreshTokenModel
    {
        $accessToken = $this->accessTokenManager->find($refreshTokenEntity->getAccessToken()->getIdentifier());

        return new RefreshTokenModel(
            $refreshTokenEntity->getIdentifier(),
            $refreshTokenEntity->getExpiryDateTime(),
            $accessToken
        );
    }
}
