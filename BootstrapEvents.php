<?php

namespace Bangpound\Bridge\Drupal;

/**
 * Class BootstrapEvents
 * @package Bangpound\Bridge\Drupal
 */
final class BootstrapEvents
{
    const CONFIGURATION = 'drupal_bootstrap.configuration';
    const PAGE_CACHE = 'drupal_bootstrap.page_cache';
    const DATABASE = 'drupal_bootstrap.database';
    const VARIABLES = 'drupal_bootstrap.variables';
    const SESSION = 'drupal_bootstrap.session';
    const PAGE_HEADER = 'drupal_bootstrap.page_header';
    const LANGUAGE = 'drupal_bootstrap.language';
    const FULL = 'drupal_bootstrap.full';

    /**
     * @param $phase
     * @return mixed
     */
    public static function getEventNameForPhase($phase)
    {
        $events = array(
            DRUPAL_BOOTSTRAP_CONFIGURATION => self::CONFIGURATION,
            DRUPAL_BOOTSTRAP_PAGE_CACHE => self::PAGE_CACHE,
            DRUPAL_BOOTSTRAP_DATABASE => self::DATABASE,
            DRUPAL_BOOTSTRAP_VARIABLES => self::VARIABLES,
            DRUPAL_BOOTSTRAP_SESSION => self::SESSION,
            DRUPAL_BOOTSTRAP_PAGE_HEADER => self::PAGE_HEADER,
            DRUPAL_BOOTSTRAP_LANGUAGE => self::LANGUAGE,
            DRUPAL_BOOTSTRAP_FULL => self::FULL,
        );

        return $events[$phase];
    }
}
