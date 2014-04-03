<?php

namespace Bangpound\Bridge\Drupal;

interface DrupalInterface
{
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
    public static function getSession();
    public static function getEventDispatcher();
    public static function getKernel();
    public static function getLogger();
}
