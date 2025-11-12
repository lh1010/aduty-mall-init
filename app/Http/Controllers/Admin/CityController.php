<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class CityController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $parent_city = [];
        if ($request->pid) {
            $parent_city = DB::table('city')->where('id', $request->pid)->first();
        }
        $query = DB::table('city');
        if (empty($parent_city)) {
            $query->where('level', 1);
        } else {
            $query->where('pid', $parent_city->id);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where(function($query) use ($params) {
                $query->where('id', $params['k'])
                    ->orWhere('name', 'like', "%" . $params['k'] . "%");
            });
        }
        $query->orderBy('sort', 'desc');
        $citys = $query->paginate();
        return view('admin.city.list', compact('citys', 'parent_city'));
    }

    public function create(Request $request)
    {
        $parent_city = [];
        if ($request->pid) {
            $parent_city = DB::table('city')->where('id', $request->pid)->first();
        }
        return view('admin.city.create', compact('parent_city'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->setCreateUpdateParams($params);
            $city_id = DB::table('city')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        $city = DB::table('city')->where('id', $request->id)->first();
        if (empty($city)) abort(404);
        return view('admin.city.edit', compact('city'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = $this->setCreateUpdateParams($params);
            DB::table('city')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::table('city')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['pid'])) $data['pid'] = $params['pid'];
        if (isset($params['shortname'])) $data['shortname'] = $params['shortname'];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['mergename'])) $data['mergename'] = $params['mergename'];
        if (isset($params['level'])) $data['level'] = $params['level'];
        if (isset($params['pinyin'])) $data['pinyin'] = $params['pinyin'];
        if (isset($params['code'])) $data['code'] = $params['code'];
        if (isset($params['zip'])) $data['zip'] = $params['zip'];
        if (isset($params['first'])) $data['first'] = $params['first'];
        if (isset($params['lng'])) $data['lng'] = $params['lng'];
        if (isset($params['lat'])) $data['lat'] = $params['lat'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }
}
