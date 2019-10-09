<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\EventListener;

use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Zend\Diactoros\Response\RedirectResponse;

/**
 * Listener that redirects anonymous users to login screen.
 * Enabled automatically with OpenId Connect
 */
final class AuthorizationRequestAuthenticationResolvingListener
{
    use TargetPathTrait;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FirewallMap
     */
    protected $firewallMap;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $loginRoute;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        FirewallMap $firewallMap,
        string $loginRoute = 'app_login'
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->firewallMap = $firewallMap;
        $this->loginRoute = $loginRoute;
    }

    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
    {
        if (null === $request = $this->requestStack->getMasterRequest()) {
            throw new \RuntimeException('Authentication listener depends on the request context');
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $firewallConfig = $this->firewallMap->getFirewallConfig($request);
            $this->saveTargetPath($this->session, $firewallConfig->getProvider(), $request->getUri());
            $this->setResponse($event);
        }
    }

    protected function setResponse(AuthorizationRequestResolveEvent $event): void
    {
        $loginUrl = $this->urlGenerator->generate($this->loginRoute);
        $event->setResponse(new RedirectResponse($loginUrl));
    }
}
