<?php

namespace Module\System\Role;

use Illuminate\Http\Request;
use App\Role;
use App\Http\Controllers\Controller as AppController;

class Controller extends AppController
{

    /**
     * GET请求入口页面
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
    {
        return view('index', ['module' => module()]);
    }

    /**
     * POST请求入口页面
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postIndex(Request $request, Role $role)
    {
        $rows = $role->get();

        return $this->success([
            'total' => $rows->count(),
            'rows' => $rows->toArray(),
        ]);
    }

    /**
     * GET请求新增页面
     *
     * @return \Illuminate\View\View
     */
    public function getCreate()
    {
        return view('create');
    }

    /**
     * POST请求新增页面
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postCreate(Request $request)
    {
        return $this->error([
            'message' => '功能完善中'
        ]);
    }

    /**
     * GET请求编辑页面
     *
     * @return \Illuminate\View\View
     */
    public function getUpdate(Request $request, Role $role)
    {
        $id = $request->input('id');
        $row = $role->find($id);
        dd($row);
        return view('update', ['row' => $row]);
    }

    /**
     * POST请求编辑页面
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdate(Request $request)
    {
        return $this->error([
            'message' => '功能完善中'
        ]);
    }

    /**
     * POST请求删除页面
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postDelete(Request $request)
    {
        return $this->error([
            'message' => '功能完善中'
        ]);
    }

}