<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Bangpound\Bridge\Drupal\BootstrapEvents;
use Bangpound\Bridge\Drupal\Event\BootstrapEvent;
use Bangpound\Bridge\Drupal\Event\GetCallableForPhase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ConfigurationListener
 * @package Bangpound\Bridge\Drupal\EventListener
 */
class ConfigurationListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            BootstrapEvents::GET_CONFIGURATION => array(
                array('onBootstrapConfiguration', 8),
            ),
            BootstrapEvents::FILTER_CONFIGURATION => array(
                array('setErrorHandling', -1),

                array('initializeEnvironment', -2),
                array('setRequestPath', -2),
                array('setErrorReporting', -2),
                array('overridePhpSettings', -2),

                array('startTimer', -3),

                array('initializeSettings', -4),
                array('generateSessionName', -4),
            )
        );
    }

    /**
     * @param \Bangpound\Bridge\Drupal\Event\GetCallableForPhase $event
     */
    public function onBootstrapConfiguration(GetCallableForPhase $event)
    {
        // Noop function is important because Pimple will throw an exception
        // if an undefined service is requested.
        $event->setCallable(
            function () {

            }
        );
    }

    /**
     * @param BootstrapEvent $event
     */
    public function setErrorHandling(BootstrapEvent $event)
    {
        // Set the Drupal custom error handler.
        set_error_handler('_drupal_error_handler');
        set_exception_handler('_drupal_exception_handler');
    }

    /**
     * @param BootstrapEvent $event
     */
    public function initializeEnvironment(BootstrapEvent $event)
    {
        if (!isset($_SERVER['HTTP_REFERER'])) {
            $_SERVER['HTTP_REFERER'] = '';
        }
        if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            // As HTTP_HOST is user input, ensure it only contains characters allowed
            // in hostnames. See RFC 952 (and RFC 2181).
            // $_SERVER['HTTP_HOST'] is lowercased here per specifications.
            $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
            if (!drupal_valid_http_host($_SERVER['HTTP_HOST'])) {
                // HTTP_HOST is invalid, e.g. if containing slashes it may be an attack.
                header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                exit;
            }
        } else {
            // Some pre-HTTP/1.1 clients will not send a Host header. Ensure the key is
            // defined for E_ALL compliance.
            $_SERVER['HTTP_HOST'] = '';
        }
    }

    /**
     * @param BootstrapEvent $event
     */
    public function setRequestPath(BootstrapEvent $event)
    {
        // When clean URLs are enabled, emulate ?q=foo/bar using REQUEST_URI. It is
        // not possible to append the query string using mod_rewrite without the B
        // flag (this was added in Apache 2.2.8), because mod_rewrite unescapes the
        // path before passing it on to PHP. This is a problem when the path contains
        // e.g. "&" or "%" that have special meanings in URLs and must be encoded.
        $_GET['q'] = request_path();
    }

    /**
     * @param BootstrapEvent $event
     */
    public function setErrorReporting(BootstrapEvent $event)
    {
        // Enforce E_ALL, but allow users to set levels not part of E_ALL.
        error_reporting(E_ALL | error_reporting());
    }

    /**
     * @param BootstrapEvent $event
     */
    public function overridePhpSettings(BootstrapEvent $event)
    {
        // Override PHP settings required for Drupal to work properly.
        // sites/default/default.settings.php contains more runtime settings.
        // The .htaccess file contains settings that cannot be changed at runtime.

        // Don't escape quotes when reading files from the database, disk, etc.
        ini_set('magic_quotes_runtime', '0');
        // Use session cookies, not transparent sessions that puts the session id in
        // the query string.
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        // Don't send HTTP headers using PHP's session handler.
        // An empty string is used here to disable the cache limiter.
        ini_set('session.cache_limiter', '');
        // Use httponly session cookies.
        ini_set('session.cookie_httponly', '1');

        // Set sane locale settings, to ensure consistent string, dates, times and
        // numbers handling.
        setlocale(LC_ALL, 'C');
    }

    /**
     * @param BootstrapEvent $event
     */
    public function startTimer(BootstrapEvent $event)
    {
        // Start a page timer:
        timer_start('page');
    }

    /**
     * Initialize the configuration, including variables from settings.php.
     *
     * @param BootstrapEvent $event
     * @see drupal_settings_initialize
     */
    public function initializeSettings(BootstrapEvent $event)
    {
        global $base_url, $base_path, $base_root;

        // Export these settings.php variables to the global namespace.
        global $databases, $cookie_domain, $conf, $installed_profile, $update_free_access, $db_url, $db_prefix, $drupal_hash_salt, $is_https, $base_secure_url, $base_insecure_url;
        $conf = array();

        if (file_exists(DRUPAL_ROOT . '/' . conf_path() . '/settings.php')) {
            include_once DRUPAL_ROOT . '/' . conf_path() . '/settings.php';
        }
        $is_https = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';

        if (isset($base_url)) {
            // Parse fixed base URL from settings.php.
            $parts = parse_url($base_url);
            if (!isset($parts['path'])) {
                $parts['path'] = '';
            }
            $base_path = $parts['path'] . '/';
            // Build $base_root (everything until first slash after "scheme://").
            $base_root = substr($base_url, 0, strlen($base_url) - strlen($parts['path']));
        } else {
            // Create base URL.
            $http_protocol = $is_https ? 'https' : 'http';
            $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];

            $base_url = $base_root;

            // $_SERVER['SCRIPT_NAME'] can, in contrast to $_SERVER['PHP_SELF'], not
            // be modified by a visitor.
            if ($dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/')) {
                $base_path = $dir;
                $base_url .= $base_path;
                $base_path .= '/';
            } else {
                $base_path = '/';
            }
        }
        $base_secure_url = str_replace('http://', 'https://', $base_url);
        $base_insecure_url = str_replace('https://', 'http://', $base_url);
    }

    /**
     * @param BootstrapEvent $event
     */
    public function generateSessionName(BootstrapEvent $event)
    {
        global $cookie_domain, $base_url, $is_https;

        if ($cookie_domain) {
            // If the user specifies the cookie domain, also use it for session name.
            $session_name = $cookie_domain;
        } else {
            // Otherwise use $base_url as session name, without the protocol
            // to use the same session identifiers across HTTP and HTTPS.
            list( , $session_name) = explode('://', $base_url, 2);
            // HTTP_HOST can be modified by a visitor, but we already sanitized it
            // in drupal_settings_initialize().
            if (!empty($_SERVER['HTTP_HOST'])) {
                $cookie_domain = $_SERVER['HTTP_HOST'];
                // Strip leading periods, www., and port numbers from cookie domain.
                $cookie_domain = ltrim($cookie_domain, '.');
                if (strpos($cookie_domain, 'www.') === 0) {
                    $cookie_domain = substr($cookie_domain, 4);
                }
                $cookie_domain = explode(':', $cookie_domain);
                $cookie_domain = '.' . $cookie_domain[0];
            }
        }
        // Per RFC 2109, cookie domains must contain at least one dot other than the
        // first. For hosts such as 'localhost' or IP Addresses we don't set a cookie domain.
        if (count(explode('.', $cookie_domain)) > 2 && !is_numeric(str_replace('.', '', $cookie_domain))) {
            ini_set('session.cookie_domain', $cookie_domain);
        }
        // To prevent session cookies from being hijacked, a user can configure the
        // SSL version of their website to only transfer session cookies via SSL by
        // using PHP's session.cookie_secure setting. The browser will then use two
        // separate session cookies for the HTTPS and HTTP versions of the site. So we
        // must use different session identifiers for HTTPS and HTTP to prevent a
        // cookie collision.
        if ($is_https) {
            ini_set('session.cookie_secure', TRUE);
        }
        $prefix = ini_get('session.cookie_secure') ? 'SSESS' : 'SESS';
        session_name($prefix . substr(hash('sha256', $session_name), 0, 32));
    }
}
