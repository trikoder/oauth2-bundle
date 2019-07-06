<?php

declare(strict_types=1);

namespace Trikoder\Bundle\OAuth2Bundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface as LegacyEventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcher;

/**
 * This event dispatcher works as a BC layer for Symfony < 4.3.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class BCEventDispatcher implements ContractsEventDispatcher
{
    private $eventDispatcher;

    public function __construct(LegacyEventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * This method is only used in this bundle. We will always call dispatch(object, string)
     */
    public function dispatch($event/*, string $eventName = null*/)
    {
        $eventName = 1 < \func_num_args() ? func_get_arg(1) : null;
        $eventName = $eventName ?? \get_class($event);

        return $this->eventDispatcher->dispatch($eventName, $event);
    }
}
