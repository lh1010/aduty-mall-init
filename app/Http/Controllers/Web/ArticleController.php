<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public function list($id, Request $request)
    {
        $category = DB::table('article_category')->where('id', $id)->where('status', 1)->first();
        if (empty($category)) abort(404);

        $articles = DB::table('article')
            ->select('article.id', 'article.title', 'article.short_title', 'article.created_at', 'article_category.code as category_code')
            ->leftJoin('article_category', 'article_category.id', 'article.category_id')
            ->where('article.category_id', $category->id)
            ->where('article.status', 1)
            ->orderBy('article.sort', 'desc')
            ->orderBy('article.created_at', 'desc')
            ->paginate();

        return view(Config('common.view.tpl_folder') . '.article.list', compact('articles', 'category'));
    }

    public function show($id)
    {
        $article = DB::table('article')
            ->select(['article.*', 'article_category.name as category_name', 'article_category.code as category_code'])
            ->leftJoin('article_category', 'article.category_id', 'article_category.id')
            ->where(['article.status' => 1, 'article.id' => $id])
            ->first();
        if (empty($article)) abort(404);

        $preg = "/<img(.*?)src=\"(.*?)\"(.*?)>/is";
        if (preg_match($preg, $article->content)) {
            $article->content = preg_replace($preg, '<img class="lazy" data-original="' . imageView("$2") . '" src="' . Config('common.image.loading') . '" />', $article->content);
        }

        return view(Config('common.view.tpl_folder') . '.article.show', compact('article'));
    }

    public function help_show(Request $request)
    {
        $categorys = DB::table('article_category')->where('parent_id', 100001)->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
        $category_id = array_column($categorys, 'id');
        $articles = DB::table('article')->whereIn('category_id', $category_id)->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
        $array = [];
        foreach ($articles as $key => $value) {
            $array[$value->category_id][] = $value;
        }
        foreach ($categorys as $key => $value) {
            $categorys[$key]->articles = isset($array[$value->id]) ? $array[$value->id] : [];
        }
        $article = DB::table('article')->where('id', $request->id)->first();
        // 详情图片
        $preg = "/<img(.*?)src=\"(.*?)\"(.*?)>/is";
        if (preg_match_all($preg, $article->content, $matches)) {
            $url = Config('common.app_url');
            if (Config('common.oss.status')) $url = Config('common.oss.url');
            $article->content = preg_replace($preg, '<img src="' . $url . '$2" />', $article->content);
        }
        return view(Config('common.view.tpl_folder') . '.article.help_show', compact('article', 'categorys'));
    }
}
