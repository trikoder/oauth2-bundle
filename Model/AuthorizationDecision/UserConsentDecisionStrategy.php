<?php

namespace Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationDecision;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Trikoder\Bundle\OAuth2Bundle\League\Entity\Scope;
use Trikoder\Bundle\OAuth2Bundle\OAuth2Grants;
use Zend\Diactoros\Response;

/**
 * Class UserConsentDecisionStrategy
 *
 * based on https://gist.github.com/ajgarlag/1f84d29ee0e1a92c8878f44a902338cd
 *
 * This strategy interrupts authorization flow by redirecting user to configured route
 * User must be then directed back to generated callback with query-appended approval result
 *
 * @todo encapsulate query string manipulations to separate service accessible from consent controller
 *
 * @package Trikoder\Bundle\OAuth2Bundle\Model\AuthorizationDecision
 */
class UserConsentDecisionStrategy implements AuthorizationDecisionStrategy
{
    const ATTRIBUTE_DECISION = 'decision';
    const ATTRIBUTE_DECISION_ALLOW = 'allow';

    /**
     * @var string
     */
    private $consentApprovalRoute;

    /**
     * @var UriSigner
     */
    private $uriSigner;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * UserConsentDecisionStrategy constructor.
     * @param UriSigner $uriSigner
     * @param RequestStack $requestStack
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $consentApprovalRoute
     */
    public function __construct(
        UriSigner $uriSigner,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        string $consentApprovalRoute
    ) {
        $this->consentApprovalRoute = $consentApprovalRoute;
        $this->uriSigner = $uriSigner;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param AuthorizationRequestResolveEvent $event
     */
    public function decide(AuthorizationRequestResolveEvent $event): void
    {
        if (null === $request = $this->requestStack->getMasterRequest()) {
            throw new \RuntimeException('Consent decision strategy depends on the request context');
        }

        // if the request carries approval result
        if ($this->canResolveAuthorizationRequest($event, $request)) {
            if ($this->isAuthorizationAllowed($request)) {
                $event->resolveAuthorization(true);
            }

            // disapproved consent is handled by League component
            return;
        }

        $event->setResponse($this->createRedirectToConsentResponse($event));
    }

    private function canResolveAuthorizationRequest(AuthorizationRequestResolveEvent $event, Request $request)
    {
        if (!$request->query->has(self::ATTRIBUTE_DECISION)) {
            return false;
        }

        $currentUri = $request->getRequestUri();
        if (!$this->uriSigner->check($currentUri)) {
            return false;
        }

        if ($request->query->get('client_id') !== $event->getClient()->getIdentifier()) {
            return false;
        }
        if ($request->query->get('response_type') !== $this->getResponseType($event)) {
            return false;
        }
        if ($request->query->get('redirect_uri') !== $event->getRedirectUri()) {
            return false;
        }
        if ($request->query->get('scope') !== $this->getScope($event)) {
            return false;
        }

        return true;
    }

    private function createRedirectToConsentResponse(AuthorizationRequestResolveEvent $event): Response
    {
        $params = [
            'client_id' => $event->getClient()->getIdentifier(),
            'response_type' => $this->getResponseType($event),
        ];
        if (null !== $redirectUri = $event->getRedirectUri()) {
            $params['redirect_uri'] = $redirectUri;
        }
        if (null !== $state = $event->getState()) {
            $params['state'] = $state;
        }
        $scope = $this->getScope($event);
        if (null !== $scope) {
            $params['scope'] = $scope;
        }

        $redirectUri = $this->urlGenerator->generate($this->consentApprovalRoute, $params);
        return new Response\RedirectResponse($redirectUri);
    }

    private function getResponseType(AuthorizationRequestResolveEvent $event): string
    {
        switch ($event->getGrantTypeId()) {
            case OAuth2Grants::AUTHORIZATION_CODE:
                return 'code';
            case OAuth2Grants::IMPLICIT:
                return 'token';
            default:
                return $event->getGrantTypeId();
        }
    }

    private function getScope(AuthorizationRequestResolveEvent $event): ?string
    {
        $scopes = array_map(function (Scope $scope) {
            return $scope->getIdentifier();
        }, $event->getScopes());

        if (empty($scopes)) {
            return null;
        }

        return implode(' ', $scopes);
    }

    private function isAuthorizationAllowed(Request $request): bool
    {
        return $request->get(self::ATTRIBUTE_DECISION) === self::ATTRIBUTE_DECISION_ALLOW;
    }
}
