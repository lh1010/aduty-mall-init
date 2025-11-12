<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\SubjectRepository;

class SubjectController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('subject');
        $query->select(['subject.*', 'subject_category.name as category_name']);
        $query->leftJoin('subject_category', 'subject_category.id', 'subject.category_id');
        $query->orderBy('subject.sort', 'desc')->orderBy('subject.id', 'desc');
        $subjects = $query->paginate();

        $subject_ids = array_column($subjects->items(), 'id');
        $fields = DB::table('subject_field')->whereIn('subject_id', $subject_ids)->get()->toArray();
        $array = [];
        foreach ($fields as $key => $value) {
            $array[$value->subject_id][$key] = $value;
        }
        foreach ($subjects as $key => $value) {
            $subjects[$key]->field_count = isset($array[$value->id]) ? count($array[$value->id]) : 0;
        }

        return view('admin.subject.list', compact('subjects'));
    }

    public function create(Request $request)
    {
        $categorys = DB::table('subject_category')->get()->toArray();
        return view('admin.subject.create', compact('categorys'));
    }

    public function store(Request $request)
    {
        $params = $request->all();
        $params['content'] = $request->post('editormd-html-code');
        $params['content_markdown'] = $request->content;
        $data = app(SubjectRepository::class)->setCreateUpdateParams($params);
        if (DB::table('subject')->where('url', $params['url'])->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
        $id = DB::table('subject')->insertGetId($data);
        if (!isset($params['url']) || empty($params['url'])) DB::table('subject')->where('id', $id)->update(['url' => $id]);
        return jsonSuccess();
    }

    public function edit(Request $request)
    {
        $subject = DB::table('subject')->where('id', $request->id)->first();
        $categorys = DB::table('subject_category')->get()->toArray();
        return view('admin.subject.edit', compact('subject', 'categorys'));
    }

    public function update(Request $request)
    {
        $subject = DB::table('subject')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($subject)) return jsonFailed('专题不存在');
        $params = $request->all();
        $params['content'] = $request->post('editormd-html-code');
        $params['content_markdown'] = $request->content;
        $data = app(SubjectRepository::class)->setCreateUpdateParams($params);
        if (!isset($params['url']) || empty($params['url'])) $data['url'] = $request->id;
        if (DB::table('subject')->where('url', $data['url'])->where('id', '<>', $request->id)->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
        DB::table('subject')->where('id', $request->id)->update($data);
        return jsonSuccess();
    }

    public function category_list(Request $request)
    {
        $query = DB::table('subject_category');
        $categorys = $query->paginate();
        return view('admin.subject.category_list', compact('categorys'));
    }

    public function category_create(Request $request)
    {
        return view('admin.subject.category_create');
    }

    public function category_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(SubjectRepository::class)->setStoreUpdateParams_category($params);
            if (DB::table('subject_category')->where('url', $data['url'])->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
            $id = DB::table('subject_category')->insertGetId($data);
            if (!isset($params['url']) || empty($params['url'])) DB::table('subject_category')->where('id', $id)->update(['url' => $id]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_edit(Request $request)
    {
        $category = DB::table('subject_category')->where('id', $request->id)->first();
        if (empty($category)) abort(404);
        return view('admin.subject.category_edit', compact('category'));
    }

    public function category_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $category = DB::table('subject_category')->where('id', $request->id)->where('status', '<>', 99)->first();
            if (empty($category)) return jsonFailed('分类不存在');
            $params = $request->all();
            $data = app(SubjectRepository::class)->setStoreUpdateParams_category($params);
            if (!isset($params['url']) || empty($params['url'])) $data['url'] = $request->id;
            if (DB::table('article_category')->where('url', $data['url'])->where('id', '<>', $request->id)->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
            DB::table('subject_category')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_delete(Request $request)
    {
        DB::table('subject_category')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function field_list(Request $request)
    {
        $subject = DB::table('subject')->where('id', $request->subject_id)->first();
        $query = DB::table('subject_field');
        $query->where('subject_id', $request->subject_id);
        $fields = $query->paginate();
        return view('admin.subject.field_list', compact('fields', 'subject'));
    }

    public function field_create(Request $request)
    {
        $subject = DB::table('subject')->where('id', $request->subject_id)->first();
        return view('admin.subject.field_create', compact('subject'));
    }

    public function field_store(Request $request)
    {
        $params = $request->all();
        $subject = DB::table('subject')->where('id', $request->subject_id)->first();
        if (empty($subject)) return jsonFailed('专题不存在');
        if (DB::table('subject_field')->where(['subject_id' => $subject->id, 'key' => $params['key']])->first()) {
            return jsonFailed('该键值已存在');
        }
        $data = [
            'subject_id' => $subject->id,
            'type' => $params['type'],
            'key' => $params['key'],
            'description' => $params['description'],
        ];
        if ($params['type'] == '文本') {
            $data['value'] = $params['value'];
        }
        if ($params['type'] == '富文本') {
            $data['value'] = $params['editormd-html-code'];
            $data['value_markdown'] = $params['value'];
        }
        DB::table('subject_field')->insert($data);
        return jsonSuccess();
    }

    public function field_edit(Request $request)
    {
        $field = DB::table('subject_field')->where('id', $request->id)->first();
        $subject = DB::table('subject')->where('id', $field->subject_id)->first();
        return view('admin.subject.field_edit', compact('subject', 'field'));
    }

    public function field_update(Request $request)
    {
        $params = $request->all();
        $field = DB::table('subject_field')->where('id', $request->id)->first();
        if (empty($field)) return jsonFailed('字段不存在');
        if (DB::table('subject_field')->where(['subject_id' => $field->subject_id, 'key' => $params['key']])->where('id', '<>', $field->id)->first()) {
            return jsonFailed('该键值已存在');
        }
        $data = [
            'type' => $params['type'],
            'key' => $params['key'],
            'description' => $params['description'],
        ];
        if ($params['type'] == '文本') {
            $data['value'] = $params['value'];
        }
        if ($params['type'] == '富文本') {
            $data['value'] = $params['editormd-html-code'];
            $data['value_markdown'] = $params['value'];
        }
        DB::table('subject_field')->where('id', $field->id)->update($data);
        return jsonSuccess();
    }

    public function field_delete(Request $request)
    {
        DB::table('subject_field')->where('id', $request->id)->delete();
        return jsonSuccess();
    }
}
