<?php

namespace Bangpound\Bridge\Drupal\DataCollector;

use Drupal\Core\BootstrapInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DrupalDatabaseDataCollector
 * @package Bangpound\Bridge\Drupal\DataCollector
 */
class DrupalDatabaseDataCollector extends DataCollector
{

    /**
     *
     */
    public function __construct(BootstrapInterface $object)
    {

        @include_once DRUPAL_ROOT . '/includes/database/log.inc';
        if (isset($GLOBALS['databases']) && is_array($GLOBALS['databases'])) {
            foreach (array_keys($GLOBALS['databases']) as $key) {
                \Database::startLog('devel', $key);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'queries' => array(),
        );
        if (isset($GLOBALS['databases']) && is_array($GLOBALS['databases'])) {
            foreach (array_keys($GLOBALS['databases']) as $key) {
                $this->data['queries'][$key] = \Database::getLog('devel', $key);
            }
        }
    }

    /**
     * @return number
     */
    public function getQueryCount()
    {
        return array_sum(array_map('count', $this->data['queries']));
    }

    /**
     * @return mixed
     */
    public function getQueries()
    {
        return $this->data['queries'];
    }

    /**
     * @return int
     */
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $queries) {
            foreach ($queries as $query) {
                $time += $query['time'];
            }
        }

        return $time;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_db';
    }
}
