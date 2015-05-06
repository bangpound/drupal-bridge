<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
use Drufony\Bridge\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PageHeaderListener.
 */
class PageHeaderListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_PAGE_HEADER => array(
                array('onBootstrapPageHeader'),
            ),
            BootstrapEvents::FILTER_PAGE_HEADER => array(
                array('startOutputBuffering'),
                array('sendResponseHeaders'),
            ),
        );
    }

    /**
     * Listener prevents output buffering and headers from being sent.
     *
     * @param \Drufony\Bridge\Event\GetCallableForPhase $event
     *
     * @see _drupal_bootstrap_page_header
     */
    public function onBootstrapPageHeader(GetCallableForPhase $event)
    {
        $event->setCallable(
            function () {
                bootstrap_invoke_all('boot');
            }
        );
    }

    /**
     * @see _drupal_bootstrap_page_header
     */
    public function startOutputBuffering()
    {
        if (!drupal_is_cli()) {
            ob_start();
        }
    }

    /**
     * @see _drupal_bootstrap_page_header
     */
    public function sendResponseHeaders()
    {
        if (!drupal_is_cli()) {
            drupal_page_header();
        }
    }
}
