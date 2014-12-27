<?php

namespace Drufony\Bridge\Twig;

/**
 * Class UrlExtension
 * @package Drufony\Bridge\Twig
 */
class UrlExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('drupal_url', 'url'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_url_extension';
    }
}
