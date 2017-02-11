<?php

namespace Drufony\Bridge\Twig\Loader;

class DrupalLoader extends \Twig_Loader_Filesystem
{
    /**
     * Constructor.
     *
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function __construct($theme, $base_theme = array())
    {
        // These conditions are true when Drupal is being updated.
        if ($theme === NULL && $base_theme === NULL) {
            return;
        }
        $cached = cache_get($theme.':twig_paths');

        $paths = array();
        if ($cached) {
            $this->setPaths($cached->data, $theme);
            $paths = array_merge($paths, $cached->data);
        }

        foreach ($base_theme as $theme_info) {
            $cached = cache_get($theme_info->name.':twig_paths');
            if ($cached) {
                $this->setPaths($cached->data, $theme_info->name);
                $paths = array_merge($paths, $cached->data);
            }
        }

        parent::__construct($paths);
    }
}
