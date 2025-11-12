<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class AdverController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('adver');
        if (isset($params['client']) && !empty($params['client'])) {
            $query->where('adver.client', $params['client']);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('adver.name', 'like', "%" . $params['k'] . "%");
        }
        $query->orderBy('adver.client', 'asc')->orderBy('adver.id', 'asc');
        $advers = $query->paginate();
        foreach ($advers->items() as $key => $value) {
            switch ($value->client) {
                case 'pc':
                    $advers[$key]->client = '电脑网站';
                    break;
                case 'wxapp':
                    $advers[$key]->client = '微信小程序';
                    break;
            }
        }
        return view('admin.adver.list', compact('advers'));
    }

    public function list1(Request $request)
    {
        $params = $request->all();
        $query = DB::table('adver');
        $query->where('adver.client', 'pc');
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('adver.name', 'like', "%" . $params['k'] . "%");
        }
        $query->orderBy('adver.client', 'asc')->orderBy('adver.id', 'asc');
        $advers = $query->paginate();
        foreach ($advers->items() as $key => $value) {
            switch ($value->client) {
                case 'pc':
                    $advers[$key]->client = '电脑网站';
                    break;
                case 'wxapp':
                    $advers[$key]->client = '微信小程序';
                    break;
            }
        }
        return view('admin.adver.list1', compact('advers'));
    }

    public function list2(Request $request)
    {
        $params = $request->all();
        $query = DB::table('adver');
        $query->where('adver.client', 'wxapp');
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('adver.name', 'like', "%" . $params['k'] . "%");
        }
        $query->orderBy('adver.client', 'asc')->orderBy('adver.id', 'asc');
        $advers = $query->paginate();
        foreach ($advers->items() as $key => $value) {
            switch ($value->client) {
                case 'pc':
                    $advers[$key]->client = '电脑网站';
                    break;
                case 'wxapp':
                    $advers[$key]->client = '微信小程序';
                    break;
            }
        }
        return view('admin.adver.list2', compact('advers'));
    }

    public function create()
    {
        return view('admin.adver.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            if (DB::table('adver')->where('code', $params['code'])->first()) return jsonFailed('当前code已存在');
            $data = $this->setCreateUpdateParams($params);
            $adver_id = DB::table('adver')->insertGetId($data);

            if (isset($params['titles'])) {
                $data_value = [];
                foreach ($params['titles'] as $key => $value) {
                    if (empty($value)) return arrayFailed('广告标题不能为空');
                    $data_value[$key]['adver_id'] = $adver_id;
                    $data_value[$key]['title'] = $value;
                    $data_value[$key]['image'] = isset($params['images'][$key]) ? fileFormat($params['images'][$key]) : '';
                    $data_value[$key]['url'] = $params['urls'][$key];
                    $data_value[$key]['open_mode'] = $params['open_modes'][$key];
                    $data_value[$key]['sort'] = is_numeric($params['sorts'][$key]) ? $params['sorts'][$key] : 0;
                }
                DB::table('adver_value')->insert($data_value);
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function edit(Request $request)
    {
        $adver = DB::table('adver')->where('id', $request->id)->first();
        $adver->values = DB::table('adver_value')->where('adver_id', $adver->id)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        return view('admin.adver.edit', compact('adver'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $adver_id = $request->id;
            $params = $request->all();
            if ($adver = DB::table('adver')->where('code', $params['code'])->where('id', '<>', $adver_id)->first()) return jsonFailed('当前code已存在');
            $data = $this->setCreateUpdateParams($params);
            DB::table('adver')->where('id', $adver_id)->update($data);

            if (isset($params['titles'])) {
                $value_ids = isset($params['value_ids']) ? $params['value_ids'] : [];
                $data_value = [];
                $update_value_ids = [];
                foreach ($params['titles'] as $key => $value) {
                    if (empty($value)) return arrayFailed('广告标题不能为空');
                    // 新内容
                    if (!isset($value_ids[$key]) || empty($value_ids[$key])) {
                        $data_value[$key]['adver_id'] = $adver_id;
                        $data_value[$key]['title'] = $value;
                        $data_value[$key]['image'] = isset($params['images'][$key]) ? fileFormat($params['images'][$key]) : '';
                        $data_value[$key]['url'] = $params['urls'][$key];
                        $data_value[$key]['open_mode'] = $params['open_modes'][$key];
                        $data_value[$key]['sort'] = is_numeric($params['sorts'][$key]) ? $params['sorts'][$key] : 0;
                    } else {
                        // 更新旧内容
                        $update_value_ids[$key] = $value_ids[$key];
                        DB::table('adver_value')->where('id', $value_ids[$key])->update([
                            'title' => $value,
                            'image' => isset($params['images'][$key]) ? fileFormat($params['images'][$key]) : '',
                            'url' => $params['urls'][$key],
                            'open_mode' => $params['open_modes'][$key],
                            'sort' => is_numeric($params['sorts'][$key]) ? $params['sorts'][$key] : 0,
                        ]);
                    }
                }
                // 删除旧内容
                DB::table('adver_value')->where('adver_id', $adver_id)->whereNotIn('id', $update_value_ids)->delete();
                // 新增新内容
                DB::table('adver_value')->insert($data_value);
            } else {
                DB::table('adver_value')->where('adver_id', $adver_id)->delete();
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('adver')->where('id', $request->id)->delete();
            DB::table('adver_value')->where('adver_id', $request->id)->delete();
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    private function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['code'])) $data['code'] = $params['code'];
        if (isset($params['remark'])) $data['remark'] = $params['remark'];
        if (isset($params['client'])) $data['client'] = $params['client'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }
}
