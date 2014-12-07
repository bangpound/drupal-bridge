<?php

namespace Bangpound\Bridge\Drupal;

use Bangpound\Bridge\Drupal\EventListener\DefaultPhasesListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BootstrapPhases
 * @package Bangpound\Bridge\Drupal
 */
class BootstrapServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['drupal.bootstrap.conf'] = array();

        $pimple['drupal.bootstrap.core'] = function () {
            require_once DRUPAL_ROOT.'/includes/bootstrap.inc';

            return new \Drupal\Core\Bootstrap();
        };

        $pimple['drupal.bootstrap.default_subscriber'] = function (Container $c) {
            return new DefaultPhasesListener($c['drupal.bootstrap.core']);
        };

        $pimple['drupal.bootstrap'] = function (Container $c) {
            return new Bootstrap($c['dispatcher']);
        };
    }

    public function subscribe(
      Container $app,
      EventDispatcherInterface $dispatcher
    ) {
        $dispatcher->addSubscriber($app['drupal.bootstrap.default_subscriber']);
    }
}
