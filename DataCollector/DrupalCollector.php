<?php

namespace Bangpound\Bridge\Drupal\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * Class DrupalCollector
 * @package Bangpound\Bridge\Drupal\DataCollector
 */
class DrupalCollector extends DataCollector
{

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'bootstrap' => function_exists('drupal_get_bootstrap_phase') ? drupal_get_bootstrap_phase() : -1,
            'base_url' => $GLOBALS['base_url'],
            'conf' => $GLOBALS['conf'],
            'base_path' => $GLOBALS['base_path'],
            'conf_path' => conf_path(),
        );

        $filter_keys = array('conf', '_ENV', '_GET', '_SERVER', 'GLOBALS', '_POST', '_REQUEST', '_SESSION', '_COOKIE', '_FILES');
        $this->data['globals'] = array_map(array($this, 'varToString'), array_diff_key($GLOBALS, array_combine($filter_keys, $filter_keys)));
        $this->data['conf'] = array_map(array($this, 'varToString'), $GLOBALS['conf']);
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

    public function getConfPath()
    {
        return $this->data['conf_path'];
    }

    public function getGlobals()
    {
        return $this->data['globals'];
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