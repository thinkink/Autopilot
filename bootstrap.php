<?php

if (!defined('AUTOPILOT_FRONTEND')) define('AUTOPILOT_FRONTEND', 0);


$this->module("autopilot")->extend([

    "types"     => new \ContainerArray([], $module),
    "widgets"   => new \ContainerArray([], $module),
    "themepath" => __DIR__.'/theme',
    "thememeta" => [],

    "helper"    => function($name) {
        return $this->app->helper("autopilot.{$name}");
    },

    "registerType" => function($name, $definition) {

        $definition = new \ContainerArray(array_merge([
            "name"   => $name,
            "route"  => null,
            "render" => null,
            "views"  => []
        ], $definition));

        $this->types->extend($name, $definition);
    },

    "registerWidget" => function($name, $definition) {

        $definition = new \ContainerArray(array_merge([
            "name"   => $name,
            "render" => null,
            "init"   => null,
            "views"  => []
        ], $definition));

        $this->widgets->extend($name, $definition);
    },

    "renderMenu" => function($name, $options = []) {

        $options = array_merge([
            "renderer" => null,
            "class"    => ""
        ], $options);

        $items = $this->app->helper("autopilot.menu")->getMenu($name);
        $level = 0;

        if (!count($items)) {
            return;
        }

        $view = $options['renderer'];

        if (!$view) {
            $view = $this->frontend->path("theme:renderers/menu.php");
        }

        if (!$view) {
            $view = $this->app->path("custom:autopilot/renderers/menu.php");
        }

        if (!$view) {
            $view = $this->frontend->path("autopilot:renderers/menu.php");
        }

        return $this->frontend->view($view, compact('options', 'items', 'level'));
    },

    "getMenues" => function() {

        return $this->app->helper('autopilot.menu')->getMenues();
    },

    "getMenu" => function($menu) {

        return $this->app->helper('autopilot.menu')->getMenu($menu);

    },

    "getFlattenMenues" => function() {

        return $this->app->helper('autopilot.menu')->flatten();
    },

    "getFlattenMenu" => function($name) {

        return $this->app->helper('autopilot.menu')->flatten($name);
    },

    "settings" => function() {
        return $this->app->db->getKey("autopilot/core", "settings", []);
    },

    "getWidgets" => function($position = null) {
        return [];
    },

    "getPositions" => function() {

        return isset($this->thememeta['positions']) ? $this->thememeta['positions']:['Sidebar'];
    },

    "renderLink" => function($link) {

        $type    = $link['type'];
        $tObject = $this->types[$type];

        if ($tObject) {

            $data = $this->helper("menu")->getItemData($link['_id']);

            $this->frontend->meta['title']       = $link['title'];
            $this->frontend->meta['menuItem']    = $link['_id'];

            // set page keywords
            if (isset($data['meta']['keywords']) && $data['meta']['keywords']) {
                $this->frontend->meta['keywords'] = @$data['meta']['keywords'];
            }

            // set page description
            if (isset($data['meta']['description']) && $data['meta']['description']) {
                $this->frontend->meta['description'] = @$data['meta']['description'];
            }

            return $tObject->render($link, $data);
        }

        return false;
    },

    "widgetsCount" => function($position) {
        return $this->helper('widgets')->filteredWidgets($position);
    },

    "renderWidgets" => function($position, $options = []) {

        $options = array_merge([
            "renderer" => null,
            "class"    => ""
        ], $options);

        $widgets = $this->helper('widgets')->filteredWidgets($position);

        if (!count($widgets)) {
            return;
        }

        $view = $options['renderer'];

        if (!$view) {
            $view = $this->frontend->path("theme:renderers/widgets.php");
        }

        if (!$view) {
            $view = $this->app->path("custom:autopilot/renderers/widgets.php");
        }

        if (!$view) {
            $view = $this->frontend->path("autopilot:renderers/widgets.php");
        }

        return $this->frontend->view($view, compact('options', 'widgets', 'position'));
    },

    "renderWidget" => function($widget, $options = []) {

        $type    = $widget['type'];
        $wObject = $this->widgets[$type];

        if ($wObject) {

            if (isset($wObject->init)) {
                $widget = $wObject->init($widget);
            }

            return $wObject->render($widget, $options);
        }

        return false;
    }
]);

// register helper
$app->helpers["autopilot.menu"]       = 'Autopilot\\Helper\\Menu';
$app->helpers["autopilot.shortcodes"] = 'Autopilot\\Helper\\Shortcodes';
$app->helpers["autopilot.widgets"]    = 'Autopilot\\Helper\\Widgets';
$app->helpers["autopilot.filters"]    = 'Autopilot\\Helper\\Filters';

// register core types

$module->registerType('link', [
    'name'   => 'Link',
    'views'  => [ 'edit' => 'autopilot:types/link/admin/edit.php']
]);

$module->registerType('page', [
    'name'   => 'Page',
    'views'  => [ 'edit' => 'autopilot:types/page/admin/edit.php'],
    'render' => function($link, $data) use($module) {

        // look in the theme first
        $view = $module->frontend->path("theme:types/page/render.php");

        if (!$view) {
            $view = $module->app->path("custom:autopilot/types/page/render.php");
        }

        // fall back to default
        if (!$view) {
            $view = $module->app->path("autopilot:types/page/render.php");
        }

        if ($view) {
            return $module->frontend->view($view.' with theme:layout.php', compact('data', 'link'));
        }

        return false;
    }
]);

$module->registerType('php-file', [
    'name'   => 'PHP Script',
    'views'  => [ 'edit' => 'autopilot:types/php-file/admin/edit.php'],
    'render' => function($link, $data) use($module) {

        // look in the theme first
        $view = $module->frontend->path("theme:types/php-file/render.php");

        if (!$view) {
            $view = $module->app->path("custom:autopilot/types/php-file/render.php");
        }

        // fall back to default
        if (!$view) {
            $view = $module->app->path("autopilot:types/php-file/render.php");
        }

        if ($view) {
            return $module->frontend->view($view.' with theme:layout.php', compact('data', 'link'));
        }

        return false;
    }
]);

$module->registerType('collection', [
    'name'   => 'Collection',
    'views'  => [ 'edit' => 'autopilot:types/collection/admin/edit.php'],
    'route'  => function($link) use($module) {

        $data        = $module->helper("menu")->getItemData($link['_id']);
        $listroute   = $link['slug_path'];
        $detailroute = $link['slug_path'].'/*';

        if ($link['home']) {
           $listroute = '/';
        }

        // bind list view
        $module->frontend->bind($listroute, function() use($link, $data, $module) {

            if (!isset($data['collectionId']) && !$data['collectionId']) {
                return false;
            }

            $collection = $module->app->db->findOne('common/collections', ['_id'=>$data['collectionId']]);;

            if (!$collection) {
                return false;
            }

            $this->meta['title']    = $link['title'];
            $this->meta['menuItem'] = $link['_id'];

            // set page keywords
            if (isset($data['meta']['keywords']) && $data['meta']['keywords']) {
                $this->meta['keywords'] = @$data['meta']['keywords'];
            }

            // set page description
            if (isset($data['meta']['description']) && $data['meta']['description']) {
                $this->meta['description'] = @$data['meta']['description'];
            }

            $colname = $collection['name'];

            // look in the theme first
            $view = $this->path("theme:types/collection/{$colname}/list.php");

            if (!$view) {
                $view = $module->app->path("custom:autopilot/types/collection/{$colname}/list.php");
            }

            if (!$view) {
                $view = $module->app->path("custom:autopilot/types/collection/list.php");
            }

            // fall back to default
            if (!$view) {
                $view = $module->app->path("autopilot:types/collection/list.php");
            }

            if ($view) {

             //   $limit = 10;
$limit = (isset($data['listItemsLimit'])) ? $data['listItemsLimit']: 10;
                $page  = $this->param("page", 1);
                $count = $module->app->module('collections')->collection($colname)->count();
                $pages = ceil($count/$limit);

                $items = $module->app->module('collections')->find($colname, [
                    'limit' => $limit,
                    'skip'  => ($page-1) * $limit
                ]);

                return $module->frontend->view($view.' with theme:layout.php', compact('link', 'data', 'items', 'page', 'pages'));
            }

            return false;
        });

        // bind detail view
        $module->frontend->bind($detailroute, function($params) use($link, $data, $module) {

            if (!isset($data['collectionId']) && !$data['collectionId']) {
                return false;
            }

            $collection = $module->app->db->findOne('common/collections', ['_id'=>$data['collectionId']]);;

            if (!$collection) {
                return false;
            }

            $colname = $collection['name'];

            // look in the theme first
            $view = $this->path("theme:types/collection/{$colname}/item.php");

            if (!$view) {
                $view = $module->app->path("custom:autopilot/types/collection/{$colname}/item.php");
            }

            if (!$view) {
                $view = $module->app->path("custom:autopilot/types/collection/item.php");
            }

            // fall back to default
            if (!$view) {
                $view = $module->app->path("autopilot:types/collection/item.php");
            }

            if ($view) {

                $routeparts = explode('-', $this['route']);
                $item       = $module->app->module('collections')->findOne($colname, ['_id' => array_pop($routeparts)]);

                if (!$item) {
                    return false;
                }

                return $module->frontend->view($view.' with theme:layout.php', compact('widget', 'settings', 'item'));
            }

            return false;


            return 'detail';
        });
    }
]);

// register core widgets

$module->registerWidget('content', [
    'name'   => 'Content',
    'views'  => [ 'edit' => 'autopilot:widgets/content/admin/edit.php'],
    'render' => function($widget) use($module) {

        $settings = &$widget['settings'];

        // look in the theme first
        $view = $module->frontend->path("theme:widgets/content/widget.php");

        if (!$view) {
            $view = $module->app->path("custom:autopilot/widgets/content/widget.php");
        }

        // fall back to default
        if (!$view) {
            $view = $module->app->path("autopilot:widgets/content/widget.php");
        }

        if ($view) {
            return $module->frontend->view($view, compact('widget', 'settings'));
        }

        return false;
    }
]);

// set theme path
if ($themepath = $app->path('custom:autopilot/theme')) {
    $module->themepath = $themepath;
}

// load theme meta
if ($themeconfigfile = $app->path($module->themepath.'/theme.config.php')) {
    $module->thememeta = include_once($themeconfigfile);
}

// BOOT ADMIN
if (COCKPIT_ADMIN && !COCKPIT_REST) include_once(__DIR__.'/admin/bootstrap.php');

// ON COCKPIT BOOT
$app->on('cockpit.bootstrap', function() use($module) {

    // register core filters

    # try to fix relative urls
    $module->helper('filters')->add('content', function($content){

        return $this->helper('utils')->fixRelativeUrls($content, $this->app->baseUrl('site:'));

    }, -100);

    # apply short codes
    $module->helper('filters')->add('content', function($content){

        $content = $this->app->helper('autopilot.shortcodes')->do_shortcode($content);

        return $content;

    }, 10);

    $this->trigger('autopilot.bootstrap');

}, -100);
