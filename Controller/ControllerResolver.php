<?php

namespace Bangpound\Bridge\Drupal\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver implements ControllerResolverInterface
{
    private $resolver;

    public function __construct(ControllerResolverInterface $resolver, RequestMatcherInterface $matcher)
    {
        $this->resolver = $resolver;
        $this->matcher = $matcher;
    }

    public function getController(Request $request)
    {
        $controller = $this->resolver->getController($request);

        return $controller;
    }

    public function getArguments(Request $request, $controller)
    {
        if ($this->matcher->matches($request)) {
            return $request->attributes->get('_arguments', array());
        }

        return $this->resolver->getArguments($request, $controller);
    }
}
