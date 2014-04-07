<?php

namespace Bangpound\Bridge\Drupal\DataCollector;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DrupalCollector
 * @package Bangpound\Bridge\Drupal\DataCollector
 */
class DrupalCollector extends DataCollector implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface
     */
    private $matcher;

    /**
     * @param RequestMatcherInterface $matcher
     */
    public function __construct(RequestMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if ($this->matcher->matches($request)) {
            $this->data = array(
                'bootstrap' => function_exists('drupal_get_bootstrap_phase') ? drupal_get_bootstrap_phase() : -1,
                'base_url' => $GLOBALS['base_url'],
                'base_path' => $GLOBALS['base_path'],
                'base_root' => $GLOBALS['base_root'],
                'conf_path' => conf_path(),
                'queries' => array(),
            );

            // Load .install files
            include_once DRUPAL_ROOT . '/includes/install.inc';
            drupal_load_updates();

            // Check run-time requirements and status information.
            $requirements = module_invoke_all('requirements', 'runtime');
            usort($requirements, '_system_sort_requirements');

            $this->data['requirements'] = $requirements;
            $this->data['severity'] = drupal_requirements_severity($requirements);
            $this->data['status_report'] = theme('status_report', array('requirements' => $requirements));

            if (isset($GLOBALS['databases']) && is_array($GLOBALS['databases'])) {
                foreach (array_keys($GLOBALS['databases']) as $key) {
                    $this->data['queries'][$key] = \Database::getLog('devel', $key);
                }
            }
        } else {
            $this->data = false;
        }
    }

    public function getBootstrapPhase()
    {
        return $this->data['bootstrap'];
    }

    public function getBaseUrl()
    {
        return $this->data['base_url'];
    }

    public function getConf()
    {
        return $this->data['conf'];
    }

    public function getBasePath()
    {
        return $this->data['base_path'];
    }

    public function getBaseRoot()
    {
        return $this->data['base_root'];
    }

    public function getConfPath()
    {
        return $this->data['conf_path'];
    }

    public function getRequirements()
    {
        return $this->data['requirements'];
    }

    public function getSeverity()
    {
        return $this->data['severity'];
    }

    public function getStatusReport()
    {
        return $this->data['status_report'];
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

    public function isDrupal()
    {
        return is_array($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal';
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::FILTER_DATABASE => 'onBootstrapDatabase',
        );
    }

    public function onBootstrapDatabase()
    {
        @include_once DRUPAL_ROOT . '/includes/database/log.inc';
        if (isset($GLOBALS['databases']) && is_array($GLOBALS['databases'])) {
            foreach (array_keys($GLOBALS['databases']) as $key) {
                \Database::startLog('devel', $key);
            }
        }
    }
}
