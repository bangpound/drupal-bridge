<?php

namespace Bangpound\Bridge\Drupal;

use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Drupal\Core\Bootstrap as BaseBootstrap;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap extends BaseBootstrap
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param null $phase
     */
    protected function call($phase = NULL)
    {
        if (isset($phase)) {
            $event = new BootstrapEvent($this, $phase);
            $eventName = BootstrapEvents::getEventNameForPhase($phase);
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
}
