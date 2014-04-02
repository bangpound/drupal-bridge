<?php

namespace Bangpound\Bridge\Drupal\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
     * @ParamConverter("router_item", converter="drupal.router_item")
     */
    public function deliverAction($router_item)
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
     * @param $router_item
     * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     * @see drupal_deliver_page
     * @see drupal_deliver_html_page
     *
     * @ParamConverter("router_item", converter="drupal.router_item")
     */
    public function responseAction($router_item)
    {
        menu_set_active_item($router_item['path']);
        $page_callback_result = $this->deliverAction($router_item);

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
            $content = is_array($page_callback_result) ? drupal_render($page_callback_result) : $page_callback_result;

            return new Response($content, http_response_code());
        }
    }
}
