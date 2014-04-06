<?php

namespace Bangpound\Bridge\Drupal\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DrupalCollector
 * @package Bangpound\Bridge\Drupal\DataCollector
 */
class DrupalCollector extends DataCollector
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
        }
        else {
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

    public function getGlobals()
    {
        return $this->data['globals'];
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

    public function isDrupal()
    {
        return is_array($this->data);
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName()
    {
        return 'drupal';
    }
}
