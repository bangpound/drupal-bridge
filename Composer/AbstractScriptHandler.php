<?php

namespace Drufony\Bridge\Composer;

use Composer\Script\Event;

/**
 * Class AbstractScriptHandler
 * @package Drufony\Bridge\Composer
 */
class AbstractScriptHandler
{
    /**
     * @param  Event $event
     * @return array
     */
    protected static function getOptions(Event $event)
    {
        $options = array_merge(
          array(
            'drupal-root' => '',
          ),
          $event->getComposer()->getPackage()->getExtra()
        );

        return $options;
    }
}
