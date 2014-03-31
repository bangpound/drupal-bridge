<?php

namespace Bangpound\Bridge\Drupal;

use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Drupal\Core\AbstractBootstrap;
use Drupal\Core\BootstrapPhases;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Bootstrap
 * @package Bangpound\Bridge\Drupal
 */
class Bootstrap extends AbstractBootstrap
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param  null       $phase
     * @return mixed|void
     */
    protected function call($phase = NULL)
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

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return array
     */
    protected function getPhases()
    {
        $phases = new BootstrapPhases();

        return $phases->keys();
    }
}
