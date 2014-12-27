<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface Matches Drupal routes.
     */
    private $matcher;

    /**
     * @param RequestMatcherInterface $matcher
     */
    public function __construct(RequestMatcherInterface $matcher = null)
    {
        $this->matcher = $matcher;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException',
        );
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        if (null === $this->matcher || $this->matcher->matches($request)) {
            $exception = $event->getException();

            $code = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

            switch ($code) {
                case 403:
                    $page_callback_result = MENU_ACCESS_DENIED;
                    break;
                case 404:
                    $page_callback_result = MENU_NOT_FOUND;
                    break;
                case 503:
                    $page_callback_result = MENU_SITE_OFFLINE;
                    break;
                default:
                    return;
            }

            $router_item = $request->attributes->get('router_item', array());
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

                $event->setResponse($delivery_callback($page_callback_result));
            } else {
                // If a delivery callback is specified, but doesn't exist as a function,
                // something is wrong, but don't print anything, since it's not known
                // what format the response needs to be in.
                watchdog('delivery callback not found', 'callback %callback not found: %q.', array('%callback' => $delivery_callback, '%q' => $_GET['q']), WATCHDOG_ERROR);
            }
        }
    }
}
