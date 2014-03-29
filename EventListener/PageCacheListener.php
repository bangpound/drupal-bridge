<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageCacheListener implements EventSubscriberInterface
{

    /**
     * Event listener replaces the DRUPAL_BOOTSTRAP_PAGE_CACHE to remove
     * Drupal page caching support.
     *
     * @param BootstrapEvent $event
     */
    public function beforeBootstrapPageCache(BootstrapEvent $event)
    {
        $bootstrap = $event->getBootstrap();
        $phase = $event->getPhase();

        $bootstrap[$phase] = $bootstrap->share(function () {
            // Allow specifying special cache handlers in settings.php, like
            // using memcached or files for storing cache information.
            require_once DRUPAL_ROOT . '/includes/cache.inc';
            foreach (variable_get('cache_backends', array()) as $include) {
                require_once DRUPAL_ROOT . '/' . $include;
            }
            drupal_block_denied(ip_address());
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::PAGE_CACHE => array('beforeBootstrapPageCache', 8),
        );
    }
}
