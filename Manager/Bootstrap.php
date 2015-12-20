<?php

namespace Drufony\Bridge\Manager;

final class Bootstrap
{
    /**
     * The app root.
     *
     * @var string
     */
    private $root;

    /**
     * Array of server variables.
     *
     * With these values, the manager
     *
     * @var array
     */
    private $serverVars;

    /**
     * Whether the PHP environment has been initialized.
     *
     * This legacy phase can only be booted once because it sets session INI
     * settings. If a session has already been started, re-generating these
     * settings would break the session.
     *
     * @var bool
     */
    private static $isEnvironmentInitialized = false;

    /**
     * @param string $root
     * @param array  $variables
     */
    public function __construct($root, array $variables = [])
    {
        $this->root = $root;
        $this->serverVars = $variables;
    }

    /**
     * Bootstrap Drupal to the specified phase.
     *
     * @param string $current_phase
     *
     * @return mixed
     */
    public function boot($current_phase)
    {
        if (!static::$isEnvironmentInitialized) {
            self::prepareEnvironment($this->root, $this->serverVars);
        }

        return drupal_bootstrap(constant($current_phase));
    }

    /**
     * Setup a consistent PHP environment.
     *
     * This method sets PHP environment options we want to be sure are set
     * correctly for security or just saneness.
     */
    private static function prepareEnvironment($root, $serverVars)
    {
        if (static::$isEnvironmentInitialized) {
            return;
        }
        chdir($root);
        define('DRUPAL_ROOT', getcwd());

        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_override_server_variables($serverVars);
        static::$isEnvironmentInitialized = true;
    }
}
