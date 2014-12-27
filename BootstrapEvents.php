<?php

namespace Drufony\Bridge;

/**
 * Class BootstrapEvents
 * @package Drufony\Bridge
 */
final class BootstrapEvents
{
    /**
     * The bootstrap phases are dispatched as events so that
     * the process can be controlled by the Symfony kernel.
     *
     * The "get" event listener receives a GetCallableForPhase instance.
     *
     * The first listener to return a callable stops event
     * propagation. Default phases have 0 priority.
     *
     * Listeners who do not return a callable function can
     * still produce side effects prior to the bootstrap
     * phase being achieved.
     *
     * @var string
     */
    const GET_CONFIGURATION = 'drupal_bootstrap.get.configuration';
    const GET_PAGE_CACHE    = 'drupal_bootstrap.get.page_cache';
    const GET_DATABASE      = 'drupal_bootstrap.get.database';
    const GET_VARIABLES     = 'drupal_bootstrap.get.variables';
    const GET_SESSION       = 'drupal_bootstrap.get.session';
    const GET_PAGE_HEADER   = 'drupal_bootstrap.get.page_header';
    const GET_LANGUAGE      = 'drupal_bootstrap.get.language';
    const GET_FULL          = 'drupal_bootstrap.get.full';

    /**
     * The bootstrap phases are dispatched as events so that
     * the process can be controlled by the Symfony kernel.
     *
     * The "filter" event listener receives a BootstrapEvent instance.
     *
     * After the bootstraph phase has started, other listeners can
     * be notified.
     *
     * @var string
     */
    const FILTER_CONFIGURATION = 'drupal_bootstrap.filter.configuration';
    const FILTER_PAGE_CACHE    = 'drupal_bootstrap.filter.page_cache';
    const FILTER_DATABASE      = 'drupal_bootstrap.filter.database';
    const FILTER_VARIABLES     = 'drupal_bootstrap.filter.variables';
    const FILTER_SESSION       = 'drupal_bootstrap.filter.session';
    const FILTER_PAGE_HEADER   = 'drupal_bootstrap.filter.page_header';
    const FILTER_LANGUAGE      = 'drupal_bootstrap.filter.language';
    const FILTER_FULL          = 'drupal_bootstrap.filter.full';

    /**
     * @param $phase
     * @return string
     */
    public static function getEventNameForPhase($phase)
    {
        $events = array(
            DRUPAL_BOOTSTRAP_CONFIGURATION => self::GET_CONFIGURATION,
            DRUPAL_BOOTSTRAP_PAGE_CACHE    => self::GET_PAGE_CACHE,
            DRUPAL_BOOTSTRAP_DATABASE      => self::GET_DATABASE,
            DRUPAL_BOOTSTRAP_VARIABLES     => self::GET_VARIABLES,
            DRUPAL_BOOTSTRAP_SESSION       => self::GET_SESSION,
            DRUPAL_BOOTSTRAP_PAGE_HEADER   => self::GET_PAGE_HEADER,
            DRUPAL_BOOTSTRAP_LANGUAGE      => self::GET_LANGUAGE,
            DRUPAL_BOOTSTRAP_FULL          => self::GET_FULL,
        );

        return $events[$phase];
    }

    /**
     * @param $phase
     * @return string
     */
    public static function filterEventNameForPhase($phase)
    {
        $events = array(
            DRUPAL_BOOTSTRAP_CONFIGURATION => self::FILTER_CONFIGURATION,
            DRUPAL_BOOTSTRAP_PAGE_CACHE    => self::FILTER_PAGE_CACHE,
            DRUPAL_BOOTSTRAP_DATABASE      => self::FILTER_DATABASE,
            DRUPAL_BOOTSTRAP_VARIABLES     => self::FILTER_VARIABLES,
            DRUPAL_BOOTSTRAP_SESSION       => self::FILTER_SESSION,
            DRUPAL_BOOTSTRAP_PAGE_HEADER   => self::FILTER_PAGE_HEADER,
            DRUPAL_BOOTSTRAP_LANGUAGE      => self::FILTER_LANGUAGE,
            DRUPAL_BOOTSTRAP_FULL          => self::FILTER_FULL,
        );

        return $events[$phase];
    }
}
