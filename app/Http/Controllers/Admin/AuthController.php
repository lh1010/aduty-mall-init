<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class AuthController extends BaseController
{
    private $treeList = [];

    public function action_list(Request $request)
    {
        return view('admin.auth.action_list');
    }

    public function action_set(Request $request)
    {
        $form_type = $request->form_type;
        $params = $request->all();
        $data = $this->setCreateUpdateParams_action($params);
        if ($form_type == 'store') {
            DB::table('admin_action')->insert($data);
        }
        if ($form_type == 'update') {
            DB::table('admin_action')->where('id', $params['id'])->update($data);
        }
        return jsonSuccess();
    }

    private function setCreateUpdateParams_action($params = [])
    {
        $data = [];
        if (isset($params['parent_id'])) $data['parent_id'] = $params['parent_id'];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['icon'])) $data['icon'] = $params['icon'];
        if (isset($params['controller'])) $data['controller'] = $params['controller'];
        if (isset($params['actions'])) $data['actions'] = str_replace('，', ',', $params['actions']);
        if (isset($params['url'])) $data['url'] = $params['url'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }

    public function getActions()
    {
        $actions = DB::table('admin_action')->where('status', '<>', 99)->orderBy('sort', 'desc')->get()->toArray();
        $actions = $this->tree_action($actions);
        return jsonSuccess($actions);
    }

    public function getAction(Request $request)
    {
        $action = DB::table('admin_action')->where('id', $request->id)->first();
        return jsonSuccess($action);
    }

    private function tree_action($data, $parent_id = 0, $level = 1)
    {
        foreach ($data as $value){
            if ($value->parent_id == $parent_id) {
                $value->level = $level;
                $this->treeList[] = $value;
                $this->tree_action($data, $value->id, $level + 1);
            }
        }
        return $this->treeList;
    }

    public function action_delete(Request $request)
    {
        if (DB::table('admin_action')->where('parent_id', $request->id)->first()) {
            return jsonFailed('请先删除该权限的子权限');
        }
        DB::table('admin_action')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function role_list(Request $request)
    {
        $roles = DB::table('admin_role')->where('status', '<>', 99)->paginate();
        return view('admin.auth.role_list', compact('roles'));
    }

    public function role_create(Request $request)
    {
        return view('admin.auth.role_create');
    }

    public function role_store(Request $request)
    {
        $params = $request->all();
        $data = $this->setCreateUpdateParams_role($params);
        DB::table('admin_role')->insert($data);
        return jsonSuccess();
    }

    public function role_edit(Request $request)
    {
        $role = DB::table('admin_role')->where('id', $request->id)->first();
        return view('admin.auth.role_edit', compact('role'));
    }

    public function role_update(Request $request)
    {
        $params = $request->all();
        $data = $this->setCreateUpdateParams_role($params);
        DB::table('admin_role')->where('id', $request->id)->update($data);
        return jsonSuccess();
    }

    private function setCreateUpdateParams_role($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }

    public function role_delete(Request $request)
    {
        DB::table('admin_role')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function set_role_to_action(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            $role = DB::table('admin_role')->where('id', $params['role_id'])->first();
            if (empty($role)) return jsonFailed('角色已被删除');
            DB::table('admin_role_to_action')->where('role_id', $params['role_id'])->delete();
            if (!empty($params['action_ids'])) {
                $action_ids = explode(',', $params['action_ids']);
                $data = [];
                foreach ($action_ids as $key => $value) {
                    $data[$key]['role_id'] = $params['role_id'];
                    $data[$key]['action_id'] = $value;
                }
                DB::table('admin_role_to_action')->insert($data);
            }
            return jsonSuccess();
        }


        $actions = DB::table('admin_action')->where('status', '<>', 99)->orderBy('sort', 'desc')->get()->toArray();
        $actions = $this->tree_action($actions);
        $role_to_action_ids = DB::table('admin_role_to_action')->where('role_id', $request->role_id)->pluck('action_id')->toArray();
        foreach ($actions as $key => $value) {
            $actions[$key]->selected = 0;
            if (in_array($value->id, $role_to_action_ids)) {
                $actions[$key]->selected = 1;
            }
        }
        return view('admin.auth.set_role_to_action', compact('actions'));
    }
}
