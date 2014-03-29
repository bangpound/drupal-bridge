<?php

namespace Bangpound\Bridge\Drupal\Event;

use Drupal\Core\Bootstrap;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BootstrapEvent
 * @package Bangpound\Bridge\Drupal\Event
 */
class BootstrapEvent extends Event
{
    private $bootstrap;

    /**
     * @var int
     */
    private $phase;

    /**
     * @param Bootstrap $bootstrap
     * @param int|null  $phase
     */
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

    /**
     * @return null|int
     */
    public function getPhase()
    {
        return $this->phase;
    }
}
