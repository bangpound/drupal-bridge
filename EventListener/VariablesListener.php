<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
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

    public function onBootstrapVariables()
    {
        foreach ($this->conf as $key => $value) {
            $GLOBALS['conf'][$key] = $value;
        }
    }
}
