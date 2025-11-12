<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\CusfieldRepository;

class CusfieldController extends BaseController
{
    public function list(Request $request)
    {
        $group = DB::table('cusfield_group')->where('id', $request->group_id)->first();
        $params = $request->all();
        $fields = DB::table('cusfield')->where('group_id', $group->id)->orderBy('sort', 'desc')->paginate();
        return view('admin.cusfield.list', compact('fields', 'group'));
    }

    public function create(Request $request)
    {
        $group = DB::table('cusfield_group')->where('id', $request->group_id)->first();
        return view('admin.cusfield.create', compact('group'));
    }

    public function store(Request $request)
    {
        $params = $request->all();
        DB::beginTransaction();
        try {
            $data = app(CusfieldRepository::class)->setCreateUpdateParams($params);
            $id = DB::table('cusfield')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        $cusfield = DB::table('cusfield')->where('cusfield.id', $request->id)->first();
        if (empty($cusfield)) abort(404);
        $cusfield->options = !empty($cusfield->options) ? explode('[luck]', $cusfield->options) : [];
        $group = DB::table('cusfield_group')->where('id', $cusfield->group_id)->first();
        return view('admin.cusfield.edit', compact('cusfield', 'group'));
    }

    public function update(Request $request)
    {
        $params = $request->all();
        DB::beginTransaction();
        try {
            $data = app(CusfieldRepository::class)->setCreateUpdateParams($params);
            DB::table('cusfield')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::table('cusfield')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function group_list(Request $request)
    {
        $groups = DB::table('cusfield_group')->orderBy('id', 'desc')->paginate();
        $group_ids = array_column($groups->items(), 'id');

        // 字段数量
        $cusfields = DB::table('cusfield')->whereIn('group_id', $group_ids)->get()->toArray();
        $array = [];
        foreach ($cusfields as $key => $value) {
            $array[$value->group_id][] = $value;
        }
        foreach ($groups as $key => $value) {
            $groups[$key]->cusfield_count = isset($array[$value->id]) ? count($array[$value->id]) : 0;
        }

        return view('admin.cusfield.group_list', compact('groups'));
    }

    public function group_create(Request $request)
    {
        return view('admin.cusfield.group_create');
    }

    public function group_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['description'])) $data['description'] = $params['description'];
            DB::table('cusfield_group')->insert($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function group_edit(Request $request)
    {
        $group = DB::table('cusfield_group')->where('id', $request->id)->first();
        if (empty($group)) abort(404);
        return view('admin.cusfield.group_edit', compact('group'));
    }

    public function group_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['description'])) $data['description'] = $params['description'];
            DB::table('cusfield_group')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function group_delete(Request $request)
    {
        DB::table('cusfield')->where('group_id', $request->id)->delete();
        DB::table('cusfield_group')->where('id', $request->id)->delete();
        return jsonSuccess();
    }
}
