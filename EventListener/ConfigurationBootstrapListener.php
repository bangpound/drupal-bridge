<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ConfigurationBootstrapListener
 * @package Bangpound\Bridge\Drupal\EventListener
 */
class ConfigurationBootstrapListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::CONFIGURATION => array(
                array('onBootstrapConfiguration', 8),

                array('setErrorHandling', -8),
                array('initializeEnvironment', -12),
                array('startTimer', -16),
                array('initializeSettings', -20),
            )
        );
    }

    /**
     * @param BootstrapEvent $event
     */
    public function onBootstrapConfiguration(BootstrapEvent $event)
    {
        $bootstrap = $event->getBootstrap();
        $phase = $event->getPhase();
        $bootstrap[$phase] = $bootstrap->share(function () {});
    }

    /**
     * @param BootstrapEvent $event
     */
    public function setErrorHandling(BootstrapEvent $event)
    {
        // Set the Drupal custom error handler.
        set_error_handler('_drupal_error_handler');
        set_exception_handler('_drupal_exception_handler');
    }

    /**
     * @param BootstrapEvent $event
     */
    public function initializeEnvironment(BootstrapEvent $event)
    {
        drupal_environment_initialize();
    }

    /**
     * @param BootstrapEvent $event
     */
    public function startTimer(BootstrapEvent $event)
    {
        // Start a page timer:
        timer_start('page');
    }

    /**
     * @param BootstrapEvent $event
     */
    public function initializeSettings(BootstrapEvent $event)
    {
        // Initialize the configuration, including variables from settings.php.
        drupal_settings_initialize();
    }
}
