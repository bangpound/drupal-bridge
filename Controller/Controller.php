<?php

namespace Bangpound\Bridge\Drupal\Controller;

class Controller
{
    public function indexAction($_router_item)
    {
        $router_item = $_router_item;
        if (!$router_item['access']) {
            return MENU_ACCESS_DENIED;
        }

        if ($router_item['include_file']) {
            require_once DRUPAL_ROOT .'/'. $router_item['include_file'];
        }

        return call_user_func_array($router_item['page_callback'], $router_item['page_arguments']);
    }
}
