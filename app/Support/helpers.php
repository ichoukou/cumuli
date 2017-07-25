<?php

if (!function_exists('attr_id')) {
    /**
     * 生成html属性ID
     *
     * @param mixed $group
     * @return string
     */
    function attr_id($group = null)
    {
        return 'id' . md5(var_export([$_SERVER, $group], true));
    }
}

if (!function_exists('breadcrumbs')) {
    /**
     * 面包屑
     *
     * @param string $path
     * @return string
     */
    function breadcrumbs($path = '')
    {
        $module = module($path);
        if (empty($module)) {
            return '';
        }

        return implode('/', [
            trans('module.' . array_get($module, 'group')),
            array_get($module, 'composer.extra.module.name', array_get($module, 'module')),
        ]);
    }
}

if (!function_exists('module')) {
    /**
     * 获取模块信息
     *
     * @param  string $name
     * @param  string $method
     * @return array|null
     */
    function module($name = '', $method = '')
    {
        static $modules = [];  // 优化多次获取模块信息

        try {
            $route = collect(explode('/', $name ? trim($name, '/') : app('request')->path()));
        } catch (\Exception $e) {
        }

        if (empty($route)) return null;

        if ($route->count() < 2) {
            return null;
        }

        // 模块名与当前url
        $name = "{$route->get(0)}/{$route->get(1)}";
        $url = $route->get(2) ? "{$name}/{$route->get(2)}" : $name;

        // 请求方式，默认为get
        $method = $method ?: app('request')->method();
        $method = strtolower($method);

        // 控制器参数
        $group = studly_case(strtolower($route->get(0)));
        $module = studly_case(strtolower($route->get(1)));
        $action = $method . studly_case(strtolower($route->get(2, 'index')));

        // 静态变量中获取
        if (array_has($modules, "{$group}.{$module}.{$action}")) {
            return array_get($modules, "{$group}.{$module}.{$action}", null);
        }

        // 控制器类名
        $controller = implode('\\', ['\\Module', $group, $module, 'Controller']);
        if (!class_exists($controller)) {
            return null;
        }

        // 通过类名获取模块文件信息
        $object = new ReflectionClass($controller);      // 反解析类文件信息
        $path = dirname(dirname($object->getFileName())); // 模块根目录

        if (!file_exists(realpath($path . '/composer.json'))) {
            return null;
        }

        // 获取composer配置信息，同时验证模块类型
        $composer = json_decode(file_get_contents(realpath($path . '/composer.json')), true);
        if (array_get($composer, 'type') != 'cumuli-module') {
            return null;
        }

        // 保存模块信息到静态变量
        array_set($modules, "{$group}.{$module}.{$action}", [
            'name' => $name,
            'url' => $url,
            'method' => $method,
            'controller' => $controller,
            'group' => $group,
            'module' => $module,
            'action' => $action,
            'reflection' => $object,
            'path' => $path,
            'composer' => $composer,
            'view' => [
                'paths' => [
                    realpath($path . '/views')
                ]
            ]
        ]);
        return array_get($modules, "{$group}.{$module}.{$action}", null);
    }
}

if (!function_exists('module_lang')) {
    /**
     * 获取模块多语言信息，用于自定义语言合并
     *
     * @return array
     */
    function module_lang()
    {
        static $lang = [];
        if (!empty($lang)) {
            return $lang;
        }

        if (!file_exists(base_path('module/lang.json'))) {
            return [];
        }
        if (!is_writeable(base_path('module/lang.json'))) {
            return [];
        }
        $lang = json_decode(file_get_contents(base_path('module/lang.json')), true);
        return $lang;
    }
}

if (!function_exists('modules')) {
    /**
     * 获取所有模块信息
     *
     * @return array
     */
    function modules()
    {
        if (!file_exists(base_path('module/module.json'))) {
            return [];
        }
        if (!is_writeable(base_path('module/module.json'))) {
            return [];
        }
        return json_decode(file_get_contents(base_path('module/module.json')), true);
    }
}
