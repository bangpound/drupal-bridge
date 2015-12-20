<?php

namespace Drufony\Bridge\Drush\Composer;

use Drufony\Bridge\Composer\AbstractScriptHandler;
use Composer\Script\Event;

class ScriptHandler extends AbstractScriptHandler
{
    /**
     * @param Event $event
     */
    public static function drushCommand(Event $event)
    {
        $options = self::getOptions($event);
        $composer = $event->getComposer();

        $drushDir = realpath($composer->getConfig()->get('vendor-dir').'/drush/drush');
        $targetDir = realpath($options['drupal-root']);

        $argv = $GLOBALS['argv'];

        // Remove the composer argument which triggered this function.
        array_shift($argv);
        $argv[] = '--root='.$targetDir;
        $GLOBALS['argc'] = count($argv);
        $GLOBALS['argv'] = $argv;

        require $drushDir.'/includes/preflight.inc';
        if (drush_preflight_prepare() === FALSE) {
            exit(1);
        }

        $event->getIO()->write(self::drushMain());
    }

    /**
     * Calls a Drush command.
     *
     * This is an exact copy of drush_main from drush.php, but that file
     * cannot be loaded because it produces side effects.
     *
     * @see drush_main
     *
     * @return int|string
     */
    protected static function drushMain()
    {
        $return = '';
        // Start code coverage collection.
        if ($coverage_file = drush_get_option('drush-coverage', false)) {
            drush_set_context('DRUSH_CODE_COVERAGE', $coverage_file);
            xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
            register_shutdown_function('drush_coverage_shutdown');
        }

        /* Set up bootstrap object, so that
         * - 'early' files can bootstrap when needed.
         * - bootstrap constants are available.
         */
        $bootstrap_class = drush_get_option('bootstrap_class', 'Drush\Boot\DrupalBoot');
        $bootstrap = new $bootstrap_class();
        drush_set_context('DRUSH_BOOTSTRAP_OBJECT', $bootstrap);
        $bootstrap->preflight();

        // Process initial global options such as --debug.
        _drush_preflight_global_options();

        $return = '';
        drush_preflight();
        if (!drush_get_error()) {
            if ($file = drush_get_option('early', false)) {
                require_once $file;
                $function = 'drush_early_'.basename($file, '.inc');
                if (function_exists($function)) {
                    if ($return = $function()) {
                        // If the function returns FALSE, we continue and attempt to bootstrap
                        // as normal. Otherwise, we exit early with the returned output.
                        if ($return === TRUE) {
                            $return = '';
                        }
                    }
                }
            } else {
                // Do any necessary preprocessing operations on the command,
                // perhaps handling immediately.
                $command_handled = drush_preflight_command_dispatch();
                if (!$command_handled) {
                    $bootstrap = drush_get_context('DRUSH_BOOTSTRAP_OBJECT');
                    $return = $bootstrap->bootstrap_and_dispatch();
                }
            }
        }
        drush_postflight();

        // How strict are we?  If we are very strict, turn 'ok' into 'error'
        // if there are any warnings in the log.
        if (($return == 0) && (drush_get_option('strict') > 1) && drush_log_has_errors()) {
            $return = 1;
        }

        // After this point the drush_shutdown function will run,
        // exiting with the correct exit code.
        return $return;
    }
}
