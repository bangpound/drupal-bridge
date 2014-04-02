<?php

/**
 * Overrides Drupal core HTTP request function with Guzzle.
 *
 * @see drupal_http_request()
 */
function bangpound_drupal_http_request($url, array $options = array())
{
    $result = new stdClass();

    // Parse the URL and make sure we can handle the schema.
    $uri = @parse_url($url);

    if ($uri == FALSE) {
        $result->error = 'unable to parse URL';
        $result->code = -1001;

        return $result;
    }

    if (!isset($uri['scheme'])) {
        $result->error = 'missing schema';
        $result->code = -1002;

        return $result;
    }

    timer_start(__FUNCTION__);

    // Merge the default options.
    $options += array(
        'headers' => array(),
        'method' => 'GET',
        'data' => NULL,
        'max_redirects' => 3,
        'timeout' => 30.0,
        'context' => NULL,
    );

    // Merge the default headers.
    $options['headers'] += array(
        'User-Agent' => 'Drupal (+http://drupal.org/)',
    );

    // Concessions to Guzzle.
    if (isset($options['data'])) {
        $options['body'] = $options['data'];
    }
    if (!$options['max_redirects']) {
        $options['allow_redirects'] = FALSE;
    }

    // Use a proxy if one is defined and the host is not on the excluded list.
    $proxy_server = variable_get('proxy_server', '');
    if ($proxy_server && _drupal_http_use_proxy($uri['host'])) {
        // Set the scheme so we open a socket to the proxy server.
        $uri['scheme'] = 'proxy';
        // Set the path to be the full URL.
        $uri['path'] = $url;
        // Since the URL is passed as the path, we won't use the parsed query.
        unset($uri['query']);

        // Add in username and password to Proxy-Authorization header if needed.
        if ($proxy_username = variable_get('proxy_username', '')) {
            $proxy_password = variable_get('proxy_password', '');
            $options['headers']['Proxy-Authorization'] = 'Basic ' . base64_encode($proxy_username . (!empty($proxy_password) ? ":" . $proxy_password : ''));
        }
        // Some proxies reject requests with any User-Agent headers, while others
        // require a specific one.
        $proxy_user_agent = variable_get('proxy_user_agent', '');
        // The default value matches neither condition.
        if ($proxy_user_agent === NULL) {
            unset($options['headers']['User-Agent']);
        } elseif ($proxy_user_agent) {
            $options['headers']['User-Agent'] = $proxy_user_agent;
        }
    }

    // Make sure the socket opened properly.
    // @todo Migrate error checking.

    // If the server URL has a user then attempt to use basic authentication.
    // @todo Migrate authentication.

    // If the database prefix is being used by SimpleTest to run the tests in a copied
    // database then set the user-agent header to the database prefix so that any
    // calls to other Drupal pages will run the SimpleTest prefixed database. The
    // user-agent is used to ensure that multiple testing sessions running at the
    // same time won't interfere with each other as they would if the database
    // prefix were stored statically in a file or database variable.
    $test_info = &$GLOBALS['drupal_test_info'];
    if (!empty($test_info['test_run_id'])) {
        $options['headers']['User-Agent'] = drupal_generate_test_ua($test_info['test_run_id']);
    }

    // Calculate how much time is left of the original timeout value.
    $timeout = $options['timeout'] - timer_read(__FUNCTION__) / 1000;
    if ($timeout > 0) {
        /** @see \Guzzle\Http\StaticClient::request() */
        static $client;
        if (!$client) {
            $client = new \Guzzle\Http\Client();
        }

        $request = $client->createRequest($options['method'], $url, null, null, $options);

        if ($options['max_redirects']) {
            $client->getConfig()->set('redirect.max', $options['max_redirects']);
        }

        if (isset($options['stream'])) {
            if ($options['stream'] instanceof \Guzzle\Stream\StreamRequestFactoryInterface) {
                $response = $options['stream']->fromRequest($request);
            } elseif ($options['stream'] == true) {
                $streamFactory = new \Guzzle\Stream\PhpStreamRequestFactory();
                $response = $streamFactory->fromRequest($request);
            }
        } else {
            $response = $request->send();
        }

        $result->request = $request->__toString();
    } else {
        $result->code = HTTP_REQUEST_TIMEOUT;
        $result->error = 'request timed out';

        return $result;
    }

    if (isset($response)) {
        // Parse response headers from the response body.
        // Be tolerant of malformed HTTP responses that separate header and body with
        // \n\n or \r\r instead of \r\n\r\n.
        $result->data = $response->getBody(true);

        // Parse the response status line.
        $result->protocol = $response->getProtocol() .'/'. $response->getProtocolVersion();
        $result->status_message = $response->getReasonPhrase();
        $result->headers = array_map(function ($input) { return (string) $input; }, $response->getHeaders()->getAll());
        $result->code = $response->getStatusCode();

        switch ($result->code) {
            case 200: // OK
            case 304: // Not modified
                break;
            case 301: // Moved permanently
            case 302: // Moved temporarily
            case 307: // Moved temporarily
                // $result->redirect_code = $code;
                // $result->redirect_url = $location;
                break;
            default:
                $result->error = $response->getReasonPhrase();
        }
    }

    return $result;
}
