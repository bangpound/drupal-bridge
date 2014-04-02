<?php

namespace Bangpound\Bridge\Drupal\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class RouterItemConverter
 * @package Bangpound\Bridge\Drupal\ParamConverter
 */
class RouterItemConverter implements ParamConverterInterface
{

    /**
     * Stores the object in the request.
     *
     * @param Request        $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws NotFoundHttpException
     * @return boolean               True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $param = $configuration->getName();

        if (!$request->attributes->has('q')) {
            return false;
        }

        $value = $request->attributes->get('q');

        if (!$value && $configuration->isOptional()) {
            return false;
        }

        $router_item = menu_get_item($value);

        if (!$router_item) {
            throw new NotFoundHttpException('Entity not found.');
        }

        $request->attributes->set($param, $router_item);

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return boolean True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getConverter()) {
            return false;
        }

        return 'drupal.router_item' === $configuration->getConverter();
    }
}
