<?php

namespace Drufony\Bridge\Pimple;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

class DrupalServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    const DRUPAL_BOOTSTRAP_CONFIGURATION = 0;
    const DRUPAL_BOOTSTRAP_PAGE_CACHE = 1;
    const DRUPAL_BOOTSTRAP_DATABASE = 2;
    const DRUPAL_BOOTSTRAP_VARIABLES = 3;
    const DRUPAL_BOOTSTRAP_SESSION = 4;
    const DRUPAL_BOOTSTRAP_PAGE_HEADER = 5;
    const DRUPAL_BOOTSTRAP_LANGUAGE = 6;
    const DRUPAL_BOOTSTRAP_FULL = 7;

    public function register(Container $pimple)
    {
        $pimple['drupal.server_variables'] = array(
          'url' => 'http://default/index.php',
        );
        $pimple['drupal.bootstrap'] = self::DRUPAL_BOOTSTRAP_FULL;
        $pimple['drupal.root'] = '';
    }

    public function boot(Application $app)
    {
        chdir($app['drupal.root']);
        define('DRUPAL_ROOT', getcwd());

        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_override_server_variables($app['drupal.server_variables']);
        drupal_bootstrap($app['drupal.bootstrap']);
    }
}
