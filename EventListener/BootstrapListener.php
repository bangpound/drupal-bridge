<?php
namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BootstrapListener implements EventSubscriberInterface
{
    /**
     * @param BootstrapEvent $event
     */
    public function onBootstrapEvent(BootstrapEvent $event)
    {
        $bootstrap = $event->getBootstrap();
        $phase = $event->getPhase();
        $bootstrap[$phase];
    }

    /**
     * Bootstrap phases are events and the default behavior always has priority 0.
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::CONFIGURATION => array('onBootstrapEvent'),
            BootstrapEvents::PAGE_CACHE => array('onBootstrapEvent'),
            BootstrapEvents::DATABASE => array('onBootstrapEvent'),
            BootstrapEvents::VARIABLES => array('onBootstrapEvent'),
            BootstrapEvents::SESSION => array('onBootstrapEvent'),
            BootstrapEvents::PAGE_HEADER => array('onBootstrapEvent'),
            BootstrapEvents::LANGUAGE => array('onBootstrapEvent'),
            BootstrapEvents::FULL => array('onBootstrapEvent'),
        );
    }
}
