<?php

namespace Bangpound\Bridge\Drupal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="bangpound_drupal.controller")
 */
class Controller
{
    /**
     * Controller returns the actual callback result
     *
     * This assumes that there are kernel event listeners who will convert
     * the controller result into a Symfony response.
     *
     * @param $router_item
     * @return int|mixed
     * @see menu_execute_active_handler()
     *
     * @Route(requirements={"q" = "\.+"}, defaults={"_legacy" = "drupal"})
     * @ParamConverter("router_item", converter="drupal.router_item")
     */
    public function executeAction($router_item)
    {
        if ($router_item['access']) {
            if ($router_item['include_file']) {
                require_once DRUPAL_ROOT . '/' . $router_item['include_file'];
            }
            $page_callback_result = call_user_func_array($router_item['page_callback'], $router_item['page_arguments']);
        } else {
            $page_callback_result = MENU_ACCESS_DENIED;
        }

        return $page_callback_result;
    }

    /**
     * Controller returns the page callback result as a Symfony response.
     *
     * @param $q
     * @param $router_item
     * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     * @see drupal_deliver_page
     * @see drupal_deliver_html_page
     *
     * @Route(requirements={"q" = "\.+"}, defaults={"_legacy" = "drupal"})
     * @ParamConverter("router_item", converter="drupal.router_item")
     */
    public function respondAction($q, $router_item)
    {
        menu_set_active_item($q);
        $page_callback_result = $this->executeAction($router_item);

        // Menu status constants are integers; page content is a string or array.
        if (is_int($page_callback_result)) {
            switch ($page_callback_result) {
                case MENU_NOT_FOUND:
                    // Print a 404 page.
                    throw new NotFoundHttpException;

                case MENU_ACCESS_DENIED:
                    // Print a 403 page.
                    throw new AccessDeniedHttpException;

                case MENU_SITE_OFFLINE:
                    // Print a 503 page.
                    throw new ServiceUnavailableHttpException;
            }
        } elseif (isset($page_callback_result)) {
            // Print anything besides a menu constant, assuming it's not NULL or
            // undefined.
            $content = drupal_render($page_callback_result);

            return new Response($content);
        }
    }

    /**
     * Controller returns an entire Drupal page as a Symfony response.
     *
     * @param $q
     * @param $router_item
     * @return Response
     * @see drupal_deliver_page
     * @Route(requirements={"q" = "\.+"}, defaults={"_legacy" = "drupal"})
     * @ParamConverter("router_item", converter="drupal.router_item")
     */
    public function deliverAction($q, $router_item)
    {
        menu_set_active_item($q);
        $page_callback_result = $this->executeAction($router_item);
        $default_delivery_callback = NULL;

        if (!isset($default_delivery_callback)) {
            $default_delivery_callback = $router_item['delivery_callback'];
        }
        $delivery_callback = !empty($default_delivery_callback) ? $default_delivery_callback : 'drupal_deliver_html_page';
        // Give modules a chance to alter the delivery callback used, based on
        // request-time context (e.g., HTTP request headers).
        drupal_alter('page_delivery_callback', $delivery_callback);

        if (function_exists($delivery_callback)) {
            $delivery_callback = function ($page_callback_result) use ($delivery_callback) {
                ob_start();
                $delivery_callback($page_callback_result);

                return new Response((string) ob_get_clean());
            };

            return $delivery_callback($page_callback_result);
        } else {
            // If a delivery callback is specified, but doesn't exist as a function,
            // something is wrong, but don't print anything, since it's not known
            // what format the response needs to be in.
            watchdog('delivery callback not found', 'callback %callback not found: %q.', array('%callback' => $delivery_callback, '%q' => $_GET['q']), WATCHDOG_ERROR);
        }
    }
}
