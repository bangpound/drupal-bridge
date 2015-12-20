<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
use Drufony\Bridge\Event\GetCallableForPhase;
use Drupal\Core\BootstrapInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DefaultPhasesListener.
 */
class DefaultPhasesListener implements EventSubscriberInterface
{
    /**
     * This marks a phase callback as the native one. Override
     * callbacks should not set a priority.
     */
    const NATIVE = -1;

    /**
     * @var BootstrapInterface Core bootstrap object.
     */
    private $bootstrap;

    /**
     * @param BootstrapInterface $bootstrap
     */
    public function __construct(BootstrapInterface $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @param \Drufony\Bridge\Event\GetCallableForPhase $event
     */
    public function onBootstrapEvent(GetCallableForPhase $event)
    {
        $bootstrap = $this->bootstrap;
        $callback = function () use ($event, $bootstrap) {
            $phase = $event->getPhase();
            $bootstrap($phase);
        };

        $event->setCallable($callback);
    }

    /**
     * Bootstrap phases are events and the default behavior always has priority 0.
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_CONFIGURATION => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_PAGE_CACHE    => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_DATABASE      => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_VARIABLES     => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_SESSION       => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_PAGE_HEADER   => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_LANGUAGE      => array('onBootstrapEvent', self::NATIVE),
            BootstrapEvents::GET_FULL          => array('onBootstrapEvent', self::NATIVE),
        );
    }
}
