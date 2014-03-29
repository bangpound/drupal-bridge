<?php

namespace Bangpound\Bridge\Drupal;

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
        $this->dispatcher->dispatch(BootstrapEvents::preEvent($phase));
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
