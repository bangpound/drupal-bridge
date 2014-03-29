<?php

namespace Bangpound\Bridge\Drupal;

use Bangpound\Bridge\Drupal\Event\GetCallableEvent;
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
        $event = new GetCallableEvent($this);
        $this->dispatcher->dispatch(BootstrapEvents::preEvent($phase), $event);

        if ($event->hasCallable()) {
            $this[$phase] = $this->share($event->getCallable());
        }

        parent::call($phase);
        $this->dispatcher->dispatch(BootstrapEvents::postEvent($phase));
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
