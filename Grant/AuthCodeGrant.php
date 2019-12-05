<?php

namespace Trikoder\Bundle\OAuth2Bundle\Grant;

use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Grant\AuthCodeGrant as BaseAuthCodeGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Trikoder\Bundle\OAuth2Bundle\League\Repository\AuthCodeRepository;
use Trikoder\Bundle\OAuth2Bundle\OpenIDConnect\IdTokenResponse;

/**
 * @property-read AuthCodeRepository $authCodeRepository
 */
class AuthCodeGrant extends BaseAuthCodeGrant
{
    /** @var string|null */
    private $nonce;

    public function validateAuthorizationRequest(ServerRequestInterface $request)
    {
        $authorizationRequest = parent::validateAuthorizationRequest($request);

        $this->nonce = $this->getQueryStringParameter('nonce', $request, null);

        return $authorizationRequest;
    }

    protected function issueAuthCode(DateInterval $authCodeTTL, ClientEntityInterface $client, $userIdentifier, $redirectUri, array $scopes = [])
    {
        $autCode = parent::issueAuthCode($authCodeTTL, $client, $userIdentifier, $redirectUri, $scopes);

        if ($this->nonce !== null) {
            $this->authCodeRepository->updateWithNonce($autCode, $this->nonce);
        }

        return $autCode;
    }

    public function respondToAccessTokenRequest(ServerRequestInterface $request, ResponseTypeInterface $responseType, DateInterval $accessTokenTTL)
    {
        $response = parent::respondToAccessTokenRequest($request, $responseType, $accessTokenTTL);

        if ($response instanceof IdTokenResponse) {
            $encryptedAuthCode = $this->getRequestParameter('code', $request, null);
            $authCodePayload = json_decode($this->decrypt($encryptedAuthCode));

            $nonce = $this->authCodeRepository->getNonce($authCodePayload->auth_code_id);
            $response->setNonce($nonce);
        }

        return $response;
    }
}
