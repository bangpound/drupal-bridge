<?php

namespace Bangpound\Bridge\Drupal\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ViewListener
 * @package Bangpound\Bridge\Drupal\EventListener
 */
class ControllerListener implements EventSubscriberInterface
{
    /**
     * @var RequestMatcherInterface Matches Drupal routes.
     */
    private $matcher;

    /**
     * @param RequestMatcherInterface $matcher
     */
    public function __construct(RequestMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * Drupal may return a string or a render array from its controllers.
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $page_callback_result = $event->getControllerResult();
        if ((is_string($page_callback_result) || is_array($page_callback_result) || is_int($page_callback_result)) && $this->matcher->matches($request)) {
            $router_item = $request->attributes->get('router_item', array());
            $default_delivery_callback = (isset($router_item) && $router_item) ? $router_item['delivery_callback'] : NULL;

            // This renders controller result into an output buffer, so it must be followed by the
            // OutputBufferListener.
            drupal_deliver_page($page_callback_result, $default_delivery_callback);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => array('onKernelView'),
        );
    }
}
