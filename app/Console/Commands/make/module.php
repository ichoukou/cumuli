<?php

namespace App\Console\Commands\make;

use Illuminate\Console\GeneratorCommand;

class module extends GeneratorCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Module';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.stub';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->type;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_map(function ($str) {
            return studly_case(strtolower($str));
        }, explode('\\', $name))), '\\');
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace_first($this->rootNamespace(), '', $name);

        return dirname($this->laravel['path']) . '/module/' . str_replace('\\', '/', $name) . '/src/Controller.php';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
        $name = $this->qualifyClass($this->getNameInput());

        if (!preg_match("#^[a-z]+(_?[a-z]+[0-9]{0,}){0,}\/[a-z]+(_?[a-z]+[0-9]{0,}){0,}$#", $this->getNameInput())) {
            $this->error($this->type . ' name error! FORMAT: [group/module | group_name/module_name]');
            return false;
        }

        $path = $this->getPath($name);
        $view = dirname(dirname($path)) . '/views/index.blade.php';
        $viewCreate = dirname(dirname($path)) . '/views/create.blade.php';
        $viewUpdate = dirname(dirname($path)) . '/views/update.blade.php';
        $composer = dirname(dirname($path)) . '/composer.json';

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $this->makeDirectory($view);

        $this->files->put($path, $this->buildClass($name));
        $html = <<<HTML
<div id="{{ attr_id('panel') }}" class="easyui-panel" title="{{ breadcrumbs() }}" iconCls="{{ \$action['icon'] }}" fit="true" border="false">
    <div>
        <pre>{{ var_export(module(), true) }}</pre>
    </div>
</div>
HTML;

        $this->files->put($view, $html);
        $this->files->put($viewCreate, 'create');
        $this->files->put($viewUpdate, 'update');
        $this->files->put($composer, str_replace('\/', '/', $this->unicodeDecode(json_encode([
            'name' => strtolower($this->getNameInput()),
            'description' => 'Cumuli系统功能模块',
            'type' => 'cumuli-module',
            'license' => 'MIT',
            'authors' => [
                [
                    'name' => 'author name',
                    'email' => 'author email'
                ],
            ],
            'autoload' => [
                'psr-4' => [
                    $this->getNamespace($name) . '\\' => 'src/'
                ]
            ],
            'extra' => [
                'module' => [
                    // 模块信息
                    'module' => [
                        'title' => '模块名称',
                        'icon' => 'fa fa-puzzle-piece',
                        'action' => 'getIndex', // 模块入口，缺省时不显示入口页面
                        'access' => true,        // 当前模块是否启用权限控制
                    ],
                    // 方法分类
                    'action' => [
                        'getIndex' => '查看',
                        'postIndex' => '查看',
                        'getCreate' => '新增',
                        'postCreate' => '新增',
                        'getUpdate' => '编辑',
                        'postUpdate' => '编辑',
                        'postDelete' => '删除',
                    ],
                    // 分类图标
                    'icon' => [
                        '查看' => 'fa fa-list-alt',
                        '新增' => 'fa fa-plus',
                        '编辑' => 'fa fa-edit',
                        '删除' => 'fa fa-minus',
                    ],
                    // 分类权限启用状态
                    'access' => [
                        '查看' => true,
                        '新增' => true,
                        '编辑' => true,
                        '删除' => true,
                    ],
                    // 分类工具栏
                    'toolbar' => [
                        '新增' => [
                            'handle' => 'create'
                        ],
                        '编辑' => [
                            'handle' => 'update'
                        ],
                        '删除' => [
                            'handle' => 'delete'
                        ],
                    ],
                    // 分类右键菜单
                    'menu' => [
                        '编辑' => [
                            'handle' => 'update'
                        ],
                        '删除' => [
                            'handle' => 'delete'
                        ],
                    ],
                ],
            ]
        ], JSON_PRETTY_PRINT))));

        @shell_exec('composer dumpautoload');
        $this->info($this->type . ' created successfully.');
        return true;
    }

    // 处理json_encode中的中文编码问题
    private function unicodeDecode($str)
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-f]{4})/i',
            create_function(
                '$matches',
                'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
            ),
            $str
        );
    }

}
