<?php

namespace Drufony\Bridge;

use Drufony\Bridge\Event\BootstrapEvent;
use Drufony\Bridge\Event\GetCallableForPhase;
use Drupal\Core\BootstrapInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Bootstrap
 * @package Drufony\Bridge
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param  null       $phase
     * @return mixed|void
     */
    public function __invoke($phase)
    {
        $event = new GetCallableForPhase($phase);
        $eventName = BootstrapEvents::getEventNameForPhase($phase);

        $this->dispatcher->dispatch($eventName, $event);

        if ($event->hasCallable()) {
            call_user_func($event->getCallable());
        }

        $event = new BootstrapEvent($phase);
        $eventName = BootstrapEvents::filterEventNameForPhase($phase);

        $this->dispatcher->dispatch($eventName, $event);
    }
}
