<?php

namespace Bangpound\Bridge\Drupal;

use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Drupal\Core\BootstrapInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Bootstrap
 * @package Bangpound\Bridge\Drupal
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
        if (isset($phase)) {
            $event = new GetCallableForPhase($phase);
            $eventName = BootstrapEvents::getEventNameForPhase($phase);

            $this->dispatcher->dispatch($eventName, $event);

            if ($event->hasCallable()) {
                $callable = $event->getCallable();
                $callable();
            }

            $event = new BootstrapEvent($phase);
            $eventName = BootstrapEvents::filterEventNameForPhase($phase);

            $this->dispatcher->dispatch($eventName, $event);
        }
    }
}
