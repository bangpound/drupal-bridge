<?php

namespace Bangpound\Bridge\Drupal\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class BootstrapEvent
 * @package Bangpound\Bridge\Drupal\Event
 */
class BootstrapEvent extends Event
{
    /**
     * @var int
     */
    private $phase;

    /**
     * @param int|null $phase
     */
    public function __construct($phase = null)
    {
        $this->phase = $phase;
    }

    /**
     * @return null|int
     */
    public function getPhase()
    {
        return $this->phase;
    }
}
