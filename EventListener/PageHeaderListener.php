<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PageHeaderListener
 * @package Bangpound\Bridge\Drupal\EventListener
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
     * @param \Bangpound\Bridge\Drupal\Event\GetCallableForPhase $event
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

    public function startOutputBuffering(BootstrapEvent $event)
    {
        if (!drupal_is_cli()) {
            ob_start();
        }
    }

    public function sendResponseHeaders(BootstrapEvent $event)
    {
        if (!drupal_is_cli()) {
            drupal_page_header();
        }
    }
}
