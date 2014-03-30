<?php

namespace Bangpound\Bridge\Drupal\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class WatchdogHandler extends AbstractProcessingHandler
{
    private $levels = array();

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->levels = [
            Logger::ALERT => WATCHDOG_ALERT,
            Logger::CRITICAL => WATCHDOG_CRITICAL,
            Logger::DEBUG => WATCHDOG_DEBUG,
            Logger::EMERGENCY => WATCHDOG_EMERGENCY,
            Logger::ERROR => WATCHDOG_ERROR,
            Logger::INFO => WATCHDOG_INFO,
            Logger::NOTICE => WATCHDOG_NOTICE,
            Logger::WARNING => WATCHDOG_WARNING,
        ];
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        watchdog($record['channel'], $record['message'], $record['context'], $this->levels[$record['level']]);
    }
}
