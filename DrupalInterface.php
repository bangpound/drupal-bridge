<?php

namespace Bangpound\Bridge\Drupal;

interface DrupalInterface
{
    /**
     * The current system version.
     */
    const VERSION = '0.0-dev';

    /**
     * Core API compatibility.
     */
    const CORE_COMPATIBILITY = '7.x';

    /**
     * Core minimum schema version.
     */
    const CORE_MINIMUM_SCHEMA_VERSION = 7000;

    /**
     * Returns true if the service id is defined.
     *
     * @param string $id The service id
     *
     * @return Boolean true if the service id is defined, false otherwise
     */
    public static function has($id);

    /**
     * Gets a service by id.
     *
     * @param string $id The service id
     *
     * @return object The service
     */
    public static function get($id);

    public static function getResponse();
}
