<?php

namespace Bangpound\Bridge\Drupal\Event;

use Drupal\Core\Bootstrap;
use Symfony\Component\EventDispatcher\Event;

class BootstrapEvent extends Event
{
    private $bootstrap;

    public function __construct(Bootstrap $bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @return \Drupal\Core\Bootstrap
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }
}
