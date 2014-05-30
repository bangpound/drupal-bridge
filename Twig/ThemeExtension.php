<?php

namespace Bangpound\Bridge\Drupal\Twig;

/**
 * Class ThemeExtension
 * @package Bangpound\Bridge\Drupal\Twig
 */
class ThemeExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('drupal_theme', 'theme', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'drupal_theme_extension';
    }
}
