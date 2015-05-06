<?php

namespace Drufony\Bridge\Twig;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class RenderExtension.
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
          new \Twig_SimpleFunction(
            'render',
            array($this, 'render'),
            array('is_safe' => array('html'), 'needs_context' => true)
          ),
          new \Twig_SimpleFunction(
            'hide',
            array($this, 'hide'),
            array('needs_context' => true)
          ),
          new \Twig_SimpleFunction(
            'show',
            array($this, 'show'),
            array('needs_context' => true)
          ),
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
     * @param array  $context      Twig rendering context
     * @param string $propertyPath Property Access path
     *
     * @return string
     */
    public function render(&$context, $propertyPath)
    {
        $element = $this->accessor->getValue($context, $propertyPath);
        $output = render($element);
        $this->accessor->setValue($context, $propertyPath, $element);

        return $output;
    }

    /**
     * @param array  $context      Twig rendering context
     * @param string $propertyPath Property Access path
     */
    public function hide(&$context, $propertyPath)
    {
        $this->toggle($context, $propertyPath, true);
    }

    /**
     * @param array $context Twig rendering context
     */
    public function show(&$context, $propertyPath)
    {
        $this->toggle($context, $propertyPath, false);
    }

    /**
     * @param array  $context      Twig rendering context
     * @param string $propertyPath Property Access path
     * @param $value
     */
    private function toggle(&$context, $propertyPath, $value)
    {
        $propertyPath .= '[#printed]';
        $this->accessor->setValue($context, $propertyPath, $value);
    }
}
