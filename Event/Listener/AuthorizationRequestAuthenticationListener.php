<?php

namespace Trikoder\Bundle\OAuth2Bundle\Event\Listener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Trikoder\Bundle\OAuth2Bundle\Event\AuthorizationRequestResolveEvent;
use Zend\Diactoros\Response;

/**
 * Class AuthorizationRequestAuthenticationListener
 *
 * Listener that redirects anonymous users to login screen.
 * Enabled automatically with OpenId Connect
 *
 * @package Trikoder\Bundle\OAuth2Bundle\Event\Listener
 */
class AuthorizationRequestAuthenticationListener implements AuthorizationEventListener
{
    use TargetPathTrait;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $loginRoute;

    /**
     * AuthorizationRequestAuthenticationListener constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param SessionInterface $session
     * @param RequestStack $requestStack
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $loginRoute
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        SessionInterface $session,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        string $loginRoute = 'app_login'
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->loginRoute = $loginRoute;
    }

    /**
     * @param AuthorizationRequestResolveEvent $event
     */
    public function onAuthorizationRequest(AuthorizationRequestResolveEvent $event): void
    {
        if (null === $request = $this->requestStack->getMasterRequest()) {
            throw new \RuntimeException('Authentication listener depends on the request context');
        }

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->saveTargetPath($this->session, 'main', $request->getUri());

            $loginUrl = $this->urlGenerator->generate($this->loginRoute);
            $event->setResponse(new Response(null, 302, ['Location' => $loginUrl]));
        }
    }
}
