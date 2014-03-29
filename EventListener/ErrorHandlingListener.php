<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ErrorHandlingListener implements EventSubscriberInterface
{
    /**
     * Replace the bootstrap configuration phase
     *
     * @param BootstrapEvent $event
     */
    public function onBootstrapConfigure(BootstrapEvent $event)
    {
        $bootstrap = $event->getBootstrap();
        $phase = $event->getPhase();
        $bootstrap[$phase] = $bootstrap->share(function () {
            drupal_environment_initialize();
            // Start a page timer:
            timer_start('page');
            // Initialize the configuration, including variables from settings.php.
            drupal_settings_initialize();
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::CONFIGURATION => array('onBootstrapConfigure', 8),
        );
    }
}
