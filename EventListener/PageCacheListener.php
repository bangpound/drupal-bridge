<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\GetCallableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageCacheListener implements EventSubscriberInterface
{

    public function beforeBootstrapPageCache(GetCallableEvent $event)
    {
        $event->setCallable(function () {
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
            BootstrapEvents::PRE_PAGE_CACHE => array('beforeBootstrapPageCache'),
        );
    }
}
