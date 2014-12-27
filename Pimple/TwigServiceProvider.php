<?php

namespace Drufony\Bridge\Pimple;

use Drufony\Bridge\Twig\Loader\DrupalLoader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class TwigServiceProvider
 * @package Drufony\Bridge\Pimple
 */
class TwigServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['twig.options'] = array();
        $pimple['twig.path'] = array();
        $pimple['twig.templates'] = array();

        $pimple['twig'] = function ($c) {
            $c['twig.options'] = array_replace(
              array(
                'charset' => isset($c['charset']) ? $c['charset'] : 'UTF-8',
                'debug' => isset($c['debug']) ? $c['debug'] : false,
                'strict_variables' => isset($c['debug']) ? $c['debug'] : false,
              ),
              $c['twig.options']
            );

            /** @var \Twig_Environment $twig */
            $twig = $c['twig.environment_factory']($c);

            if (isset($c['debug']) && $c['debug']) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }

            $twig->addExtension(new \Drufony\Bridge\Twig\ThemeExtension());
            $twig->addExtension(new \Drufony\Bridge\Twig\RenderExtension());

            return $twig;
        };

        $pimple['twig.loader.drupal'] = function ($c) {
            return new DrupalLoader($GLOBALS['theme'], $GLOBALS['base_theme_info']);
        };

        $pimple['twig.loader.filesystem'] = function ($c) {
            return new \Twig_Loader_Filesystem($c['twig.path']);
        };

        $pimple['twig.loader.array'] = function ($c) {
            return new \Twig_Loader_Array($c['twig.templates']);
        };

        $pimple['twig.loader.string'] = function ($c) {
            return new \Twig_Loader_String();
        };

        $pimple['twig.loader'] = function ($c) {
            return new \Twig_Loader_Chain(
              array(
                $c['twig.loader.array'],
                $c['twig.loader.drupal'],
                $c['twig.loader.filesystem'],
                $c['twig.loader.string'],
              )
            );
        };

        $pimple['twig.environment_factory'] = $pimple->protect(
          function ($c) {
              return new \Twig_Environment(
                $c['twig.loader'], $c['twig.options']
              );
          }
        );
    }
}
