<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\ArticleRepository;

class ArticleController extends BaseController
{
    public function list(Request $request)
    {
        $ArticleRepository = new ArticleRepository;
        $params = $request->all();
        $query = DB::table('article');
        $query->select(['article.*']);
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $category_child_ids = $ArticleRepository->getCategoryChildIds($params['category_id']);
            $query->whereIn('article.category_id', $category_child_ids);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('article.title', 'like', "%" . $params['k'] . "%");
        }
        $query->orderBy('article.id', 'desc');
        $articles = $query->paginate();
        foreach ($articles->items() as $key => $value) {
            $category_parent_ids = $ArticleRepository->getCategoryParentIds($value->category_id);
            $categorys_temp = Db::table('article_category')->whereIn('id', $category_parent_ids)->pluck('name')->toArray();
            $articles[$key]->full_category_name = implode(' > ', $categorys_temp);
            $articles[$key]->full_category_name = $articles[$key]->full_category_name ? $articles[$key]->full_category_name : '暂无分类';
        }

        $categorys = $ArticleRepository->getCategorys();
        return view('admin.article.list', compact('articles', 'categorys'));
    }

    public function create(Request $request)
    {
        $categorys = app(ArticleRepository::class)->getCategorys();
        return view('admin.article.create', compact('categorys'));
    }

    public function store(Request $request)
    {
        $params = $request->all();
        $params['content'] = $request->post('editormd-html-code');
        $params['content_markdown'] = $request->content;
        $data = app(ArticleRepository::class)->setCreateUpdateParams($params);
        if (DB::table('article')->where('url', $params['url'])->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
        $id = DB::table('article')->insertGetId($data);
        if (!isset($params['url']) || empty($params['url'])) DB::table('article')->where('id', $id)->update(['url' => $id]);
        return jsonSuccess();
    }

    public function edit(Request $request)
    {
        $article = DB::table('article')->where('id', $request->id)->first();
        $categorys = app(ArticleRepository::class)->getCategorys();
        return view('admin.article.edit', compact('article', 'categorys'));
    }

    public function update(Request $request)
    {
        $article = DB::table('article')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($article)) return jsonFailed('文章不存在');
        $params = $request->all();
        $params['content'] = $request->post('editormd-html-code');
        $params['content_markdown'] = $request->content;
        $data = app(ArticleRepository::class)->setCreateUpdateParams($params);
        if (!isset($params['url']) || empty($params['url'])) $data['url'] = $request->id;
        if (DB::table('article')->where('url', $data['url'])->where('id', '<>', $request->id)->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
        DB::table('article')->where('id', $request->id)->update($data);
        return jsonSuccess();
    }

    public function category_list(Request $request)
    {
        $categorys = app(ArticleRepository::class)->getCategorys();
        return view('admin.article.category_list', compact('categorys'));
    }

    public function category_create(Request $request)
    {
        $categorys = app(ArticleRepository::class)->getCategorys();
        return view('admin.article.category_create', compact('categorys'));
    }

    public function category_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ArticleRepository::class)->setStoreUpdateParams_category($params);
            if (DB::table('article_category')->where('url', $data['url'])->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
            $id = DB::table('article_category')->insertGetId($data);
            if (!isset($params['url']) || empty($params['url'])) DB::table('article_category')->where('id', $id)->update(['url' => $id]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_edit(Request $request)
    {
        $category = DB::table('article_category')->where('id', $request->id)->first();
        if (empty($category)) abort(404);
        $categorys = app(ArticleRepository::class)->getCategorys();
        foreach ($categorys as $key => $value) {
            if ($value->id == $category->id) unset($categorys[$key]);
        }
        return view('admin.article.category_edit', compact('category', 'categorys'));
    }

    public function category_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $category = DB::table('article_category')->where('id', $request->id)->where('status', '<>', 99)->first();
            if (empty($category)) return jsonFailed('分类不存在');
            $params = $request->all();
            $data = app(ArticleRepository::class)->setStoreUpdateParams_category($params);
            if (!isset($params['url']) || empty($params['url'])) $data['url'] = $request->id;
            if (DB::table('article_category')->where('url', $data['url'])->where('id', '<>', $request->id)->first()) return jsonFailed('请保证url的唯一性，当前url已存在');
            DB::table('article_category')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_delete(Request $request)
    {
        if ($request->id == 1) return jsonFailed('系统文档分类不能被删除');
        if (DB::table('article')->where('category_id', $request->id)->first()) return jsonFailed('请先移除该分类下的文档');
        if (DB::table('article_category')->where('parent_id', $request->id)->first()) return jsonFailed('请先移除该分类下的子分类');
        DB::table('article_category')->where('id', $request->id)->delete();
        return jsonSuccess();
    }
}
