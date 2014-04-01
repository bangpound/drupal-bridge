<?php
namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Drupal\Core\BootstrapPhases;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DefaultPhasesListener
 * @package Bangpound\Bridge\Drupal\EventListener
 */
class DefaultPhasesListener implements EventSubscriberInterface
{
    private $phases;

    /**
     *
     */
    public function __construct()
    {
        $this->phases = BootstrapPhases::all();
    }

    /**
     * @param \Bangpound\Bridge\Drupal\Event\GetCallableForPhase $event
     */
    public function onBootstrapEvent(GetCallableForPhase $event)
    {
        $phase = $event->getPhase();
        if ($this->phases[$phase]) {
            $event->setCallable($this->phases[$phase]);
        }
    }

    /**
     * Bootstrap phases are events and the default behavior always has priority 0.
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_CONFIGURATION => array('onBootstrapEvent'),
            BootstrapEvents::GET_PAGE_CACHE    => array('onBootstrapEvent'),
            BootstrapEvents::GET_DATABASE      => array('onBootstrapEvent'),
            BootstrapEvents::GET_VARIABLES     => array('onBootstrapEvent'),
            BootstrapEvents::GET_SESSION       => array('onBootstrapEvent'),
            BootstrapEvents::GET_PAGE_HEADER   => array('onBootstrapEvent'),
            BootstrapEvents::GET_LANGUAGE      => array('onBootstrapEvent'),
            BootstrapEvents::GET_FULL          => array('onBootstrapEvent'),
        );
    }
}
