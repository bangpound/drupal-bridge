<?php

namespace Bangpound\Bridge\Drupal\Twig;

/**
 * Class UrlExtension
 * @package Bangpound\Bridge\Drupal\Twig
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
