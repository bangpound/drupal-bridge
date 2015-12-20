<?php

namespace Drufony\Bridge\EventListener;

use Drufony\Bridge\BootstrapEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents as BaseKernelEvents;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class HeaderListener implements EventSubscriberInterface
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

    /**
     * Add response status and headers from legacy controllers.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if (null === $this->matcher || $this->matcher->matches($request)) {
            $response = $event->getResponse();
            $headers = drupal_get_http_header();
            $header_names = _drupal_set_preferred_header_name();
            foreach ($headers as $name_lower => $value) {
                if ($name_lower == 'status') {
                    $response->setStatusCode($value);
                }
                // Skip headers that have been unset.
                elseif ($value !== FALSE) {
                    $response->headers->set($header_names[$name_lower], $value);
                }
            }
        }
    }

    public function sendResponseHeaders()
    {
        $default_headers = array(
            'Expires' => 'Sun, 19 Nov 1978 05:00:00 GMT',
            'Last-Modified' => gmdate(DATE_RFC1123, REQUEST_TIME),
            'Cache-Control' => 'no-cache, must-revalidate, post-check=0, pre-check=0',
            'ETag' => '"' . REQUEST_TIME . '"',
        );
        foreach ($default_headers as $name => $value) {
            drupal_add_http_header($name, $value);
        }
    }

    /**
     * {@inheritDocs}.
     */
    public static function getSubscribedEvents()
    {
        return array(
            BaseKernelEvents::RESPONSE => array('onKernelResponse'),
            BootstrapEvents::FILTER_PAGE_HEADER => array('sendResponseHeaders'),
        );
    }
}
