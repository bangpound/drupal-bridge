<?php

namespace Drufony\Bridge\Pimple;

use Drufony\Bridge\Manager\Bootstrap;
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
        $pimple['drupal.bootstrap'] = 'DRUPAL_BOOTSTRAP_FULL';
        $pimple['drupal.root'] = '';

        $pimple['drupal.bootstrap_manager'] = function (Container $c) {
            return new Bootstrap($c['drupal.root'], $c['drupal.server_variables']);
        };
    }

    public function boot(Application $app)
    {
        $app['drupal.bootstrap_manager']->boot($app['drupal.bootstrap']);
    }
}
