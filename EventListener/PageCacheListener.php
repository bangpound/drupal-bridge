<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
use Drufony\Bridge\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageCacheListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_PAGE_CACHE => array(
                array('onBootstrapPageCache', 8),
            ),
            BootstrapEvents::FILTER_PAGE_CACHE => array(
                array('denyBlockedIpAddress'),
                array('serveCachedPage'),
            ),
        );
    }

    /**
     * Event listener replaces the DRUPAL_BOOTSTRAP_PAGE_CACHE to remove
     * Drupal page caching support.
     *
     * @param \Drufony\Bridge\Event\GetCallableForPhase $event
     * @see _drupal_bootstrap_page_cache
     */
    public function onBootstrapPageCache(GetCallableForPhase $event)
    {
        // Page cache is always required.
        $event->setCallable(
            function () {
                // Allow specifying special cache handlers in settings.php, like
                // using memcached or files for storing cache information.
                require_once DRUPAL_ROOT . '/includes/cache.inc';
                foreach (variable_get('cache_backends', array()) as $include) {
                    require_once DRUPAL_ROOT . '/' . $include;
                }
            }
        );
    }

    private function getCacheMode()
    {
        // Check for a cache mode force from settings.php.
        if (variable_get('page_cache_without_database')) {
            $cache_enabled = TRUE;
        } else {
            drupal_bootstrap(DRUPAL_BOOTSTRAP_VARIABLES, FALSE);
            $cache_enabled = variable_get('cache');
        }

        return $cache_enabled;
    }

    public function denyBlockedIpAddress()
    {
        drupal_block_denied(ip_address());
    }

    public function serveCachedPage()
    {
        $cache_enabled = $this->getCacheMode();

        // If there is no session cookie and cache is enabled (or forced), try
        // to serve a cached page.
        if (!isset($_COOKIE[session_name()]) && $cache_enabled) {
            global $user;

            // Make sure there is a user object because its timestamp will be
            // checked, hook_boot might check for anonymous user etc.
            $user = drupal_anonymous_user();
            // Get the page from the cache.
            $cache = drupal_page_get_cache();
            // If there is a cached page, display it.
            if (is_object($cache)) {
                header('X-Drupal-Cache: HIT');
                // Restore the metadata cached with the page.
                $_GET['q'] = $cache->data['path'];
                drupal_set_title($cache->data['title'], PASS_THROUGH);
                date_default_timezone_set(drupal_get_user_timezone());
                // If the skipping of the bootstrap hooks is not enforced, call
                // hook_boot.
                if (variable_get('page_cache_invoke_hooks', TRUE)) {
                    bootstrap_invoke_all('boot');
                }
                drupal_serve_page_from_cache($cache);
                // If the skipping of the bootstrap hooks is not enforced, call
                // hook_exit.
                if (variable_get('page_cache_invoke_hooks', TRUE)) {
                    bootstrap_invoke_all('exit');
                }
                // We are done.
                exit;
            } else {
                header('X-Drupal-Cache: MISS');
            }
        }
    }
}
