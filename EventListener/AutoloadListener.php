<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Symfony\Component\ClassLoader\MapClassLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoloadListener implements EventSubscriberInterface
{

    public function afterBootstrapDatabase()
    {
        spl_autoload_unregister('drupal_autoload_class');
        spl_autoload_unregister('drupal_autoload_interface');

        global $install_state;

        if (isset($install_state['parameters']['profile'])) {
            $profile = $install_state['parameters']['profile'];
        } else {
            $profile = variable_get('install_profile', 'standard');
        }

        $searchdirs = array();
        $searchdirs[] = DRUPAL_ROOT;
        $searchdirs[] = DRUPAL_ROOT . '/profiles/'. $profile;
        $searchdirs[] = DRUPAL_ROOT . '/sites/all';
        $searchdirs[] = DRUPAL_ROOT . '/'. conf_path();

        foreach ($searchdirs as $dir) {
            $filename = $dir .'/classmap.php';
            if (file_exists($filename)) {
                $loader = new MapClassLoader(require $filename);
                $loader->register(true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::POST_DATABASE => array('afterBootstrapDatabase'),
        );
    }
}
