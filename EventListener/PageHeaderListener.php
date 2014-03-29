<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageHeaderListener implements EventSubscriberInterface
{
    /**
     * Listener prevents output buffering and headers from being sent.
     *
     * @param BootstrapEvent $event
     */
    public function onBootstrapPageHeader(BootstrapEvent $event)
    {
        $bootstrap = $event->getBootstrap();
        $phase = $event->getPhase();
        $bootstrap[$phase] = $bootstrap->share(function () {
            bootstrap_invoke_all('boot');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::PAGE_HEADER => array('onBootstrapPageHeader', 8),
        );
    }
}
