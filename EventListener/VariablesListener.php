<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class VariablesListener
 * @package Bangpound\Bundle\DrupalBundle\EventListener
 */
class VariablesListener implements EventSubscriberInterface
{
    private $conf;

    public function __construct($conf = array())
    {
        $this->conf = $conf;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::FILTER_VARIABLES => array(
                array('onBootstrapVariables'),
            ),
        );
    }

    /**
     * @param BootstrapEvent $event
     */
    public function onBootstrapVariables(BootstrapEvent $event)
    {
        ladybug_dump($this->conf);
        foreach ($this->conf as $key => $value) {
            $GLOBALS['conf'][$key] = $value;
        }
    }
}
