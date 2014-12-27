<?php

namespace Bangpound\Bridge\Drupal\Twig;

use Bangpound\Bundle\DrupalBundle\Element;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class RenderExtension
 * @package Bangpound\Bridge\Drupal\Twig
 */
class RenderExtension extends \Twig_Extension
{
    private $accessor;

    /**
     *
     */
    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render', array($this, 'render'), array('is_safe' => array('html'), 'needs_context' => true)),
            new \Twig_SimpleFunction('hide', array($this, 'hide'), array('needs_context' => true)),
            new \Twig_SimpleFunction('show', array($this, 'show'), array('needs_context' => true)),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_render';
    }

    /**
     * @param  array  $context Twig rendering context
     * @return string
     */
    public function render(&$context)
    {
        $propertyPath = self::createPropertyPath(array_slice(func_get_args(), 1));
        $element = $this->accessor->getValue($context, $propertyPath);
        $output = render($element);
        $this->accessor->setValue($context, $propertyPath, $element);

        return $output;
    }

    /**
     * @param array $context Twig rendering context
     */
    public function hide(&$context)
    {
        $this->toggle($context, true);
    }

    /**
     * @param array $context Twig rendering context
     */
    public function show(&$context)
    {
        $this->toggle($context, false);
    }

    private function toggle(&$context, $value)
    {
        $propertyPath = self::createPropertyPath(array_merge(array_slice(func_get_args(), 1)), '#printed');
        $this->accessor->setValue($context, $propertyPath, $value);
    }

    /**
     * @param  array  $args  Property arguments from the calling function.
     * @param  string $final Value to tack on to the end of the path.
     * @return string
     */
    private static function createPropertyPath(array $args, $final = null)
    {
        if ($final) {
            $args = array_merge($args, array($final));
        }

        return '['. implode('][', $args) .']';
    }
}