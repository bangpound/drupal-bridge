<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FullListener
 * @package Bangpound\Bridge\Drupal\EventListener
 */
class FullListener implements EventSubscriberInterface
{
    /**
     * Event subscriber to stagger the side effects of _drupal_bootstrap_full().
     *
     * Initializing the theme requires the user object, so it should be deferred
     * until after the firewall listeners have loaded the user.
     *
     * @return array
     * @see _drupal_bootstrap_full
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_FULL => array(
                array('onBootstrapFull', 8),
            ),
            BootstrapEvents::FILTER_FULL => array(
                array('initializeTheme'),
                array('hookInit'),
            ),
        );
    }

    /**
     * @param \Bangpound\Bridge\Drupal\Event\GetCallableForPhase $event
     */
    public function onBootstrapFull(GetCallableForPhase $event)
    {
        $event->setCallable(
            function () {
                require_once DRUPAL_ROOT . '/includes/common.inc';
                require_once DRUPAL_ROOT . '/' . variable_get('path_inc', 'includes/path.inc');
                require_once DRUPAL_ROOT . '/includes/theme.inc';
                require_once DRUPAL_ROOT . '/includes/pager.inc';
                require_once DRUPAL_ROOT . '/' . variable_get('menu_inc', 'includes/menu.inc');
                require_once DRUPAL_ROOT . '/includes/tablesort.inc';
                require_once DRUPAL_ROOT . '/includes/file.inc';
                require_once DRUPAL_ROOT . '/includes/unicode.inc';
                require_once DRUPAL_ROOT . '/includes/image.inc';
                require_once DRUPAL_ROOT . '/includes/form.inc';
                require_once DRUPAL_ROOT . '/includes/mail.inc';
                require_once DRUPAL_ROOT . '/includes/actions.inc';
                require_once DRUPAL_ROOT . '/includes/ajax.inc';
                require_once DRUPAL_ROOT . '/includes/token.inc';
                require_once DRUPAL_ROOT . '/includes/errors.inc';

                // Detect string handling method
                unicode_check();
                // Undo magic quotes
                fix_gpc_magic();
                // Load all enabled modules
                module_load_all();
                // Make sure all stream wrappers are registered.
                file_get_stream_wrappers();
                // Ensure mt_rand is reseeded, to prevent random values from one page load
                // being exploited to predict random values in subsequent page loads.
                $seed = unpack("L", drupal_random_bytes(4));
                mt_srand($seed[1]);

                $test_info = &$GLOBALS['drupal_test_info'];
                if (!empty($test_info['in_child_site'])) {
                    // Running inside the simpletest child site, log fatal errors to test
                    // specific file directory.
                    ini_set('log_errors', 1);
                    ini_set('error_log', 'public://error.log');
                }

                // Initialize $_GET['q'] prior to invoking hook_init().
                drupal_path_initialize();

                // Remaining function calls from this phase of bootstrap must happen after
                // the user is authenticated because they initialize the theme and call
                // menu_get_item().
            }
        );
    }

    public function initializeTheme()
    {
        // Let all modules take action before the menu system handles the request.
        // We do not want this while running update.php.
        if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update') {
            // Prior to invoking hook_init(), initialize the theme (potentially a custom
            // one for this page), so that:
            // - Modules with hook_init() implementations that call theme() or
            //   theme_get_registry() don't initialize the incorrect theme.
            // - The theme can have hook_*_alter() implementations affect page building
            //   (e.g., hook_form_alter(), hook_node_view_alter(), hook_page_alter()),
            //   ahead of when rendering starts.
            menu_set_custom_theme();
            drupal_theme_initialize();
        }
    }

    public function hookInit()
    {
        if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update') {
            module_invoke_all('init');
        }
    }
}
