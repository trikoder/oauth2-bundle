<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Benoit VIGNAL <github@benoit-vignal.fr>
 */
class InvalidAuthorizationHeaderEvent extends AuthenticationFailureEvent
{

}
