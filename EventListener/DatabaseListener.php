<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
use Drufony\Bridge\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DatabaseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_DATABASE => array(
                array('onBootstrapDatabase', 8),
            ),
            BootstrapEvents::FILTER_DATABASE => array(
                array('redirectToInstall'),
                array('testSettings'),
                array('initializeDatabaseSystem'),
                array('registerAutoloadFunctions'),
            ),
        );
    }

    public function onBootstrapDatabase(GetCallableForPhase $event)
    {
        // Page cache is always required.
        $event->setCallable(
            function () {
                // Initialize the database system. Note that the connection
                // won't be initialized until it is actually requested.
                require_once DRUPAL_ROOT . '/includes/database/database.inc';
            }
        );
    }

    public function redirectToInstall()
    {
        // Redirect the user to the installation script if Drupal has not been
        // installed yet (i.e., if no $databases array has been defined in the
        // settings.php file) and we are not already installing.
        if (empty($GLOBALS['databases']) && !drupal_installation_attempted()) {
            include_once DRUPAL_ROOT . '/includes/install.inc';
            install_goto('install.php');
        }
    }

    public function testSettings()
    {
        // The user agent header is used to pass a database prefix in the request when
        // running tests. However, for security reasons, it is imperative that we
        // validate we ourselves made the request.
        if ($test_prefix = drupal_valid_test_ua()) {
            // Set the test run id for use in other parts of Drupal.
            $test_info = &$GLOBALS['drupal_test_info'];
            $test_info['test_run_id'] = $test_prefix;
            $test_info['in_child_site'] = TRUE;

            foreach ($GLOBALS['databases']['default'] as &$value) {
                // Extract the current default database prefix.
                if (!isset($value['prefix'])) {
                    $current_prefix = '';
                } elseif (is_array($value['prefix'])) {
                    $current_prefix = $value['prefix']['default'];
                } else {
                    $current_prefix = $value['prefix'];
                }

                // Remove the current database prefix and replace it by our own.
                $value['prefix'] = array(
                    'default' => $current_prefix . $test_prefix,
                );
            }
        }
    }

    public function initializeDatabaseSystem()
    {
        // Initialize the database system. Note that the connection
        // won't be initialized until it is actually requested.
        require_once DRUPAL_ROOT . '/includes/database/database.inc';
    }

    public function registerAutoloadFunctions()
    {
        // Register autoload functions so that we can access classes and interfaces.
        // The database autoload routine comes first so that we can load the database
        // system without hitting the database. That is especially important during
        // the install or upgrade process.
        spl_autoload_register('drupal_autoload_class');
        spl_autoload_register('drupal_autoload_interface');
    }
}
