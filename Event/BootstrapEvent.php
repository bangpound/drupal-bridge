<?php

namespace Bangpound\Bridge\Drupal\Event;

use Drupal\Core\Bootstrap;
use Symfony\Component\EventDispatcher\Event;

class BootstrapEvent extends Event
{
    private $bootstrap;
    private $phase;

    public function __construct(Bootstrap $bootstrap, $phase = null)
    {
        $this->bootstrap = $bootstrap;
        $this->phase = $phase;
    }

    /**
     * @return \Drupal\Core\Bootstrap
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    public function getPhase()
    {
        return $this->phase;
    }
}
