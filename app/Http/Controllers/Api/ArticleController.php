<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\ArticleRepository;

class ArticleController extends BaseController
{
    public function getCategory(Request $request)
    {
        $category = DB::table('article_category')->where('id', $request->id)->first();
        return jsonSuccess($category);
    }

    public function getArticlesPaginate(Request $request)
    {
        $params = $request->all();
        $type = $request->input('type', '');

        $types = [
            'help' => 100001, // 帮助中心
        ];
        if (isset($types[$type])) $params['category_id'] = $types[$type];

        $articles = app(ArticleRepository::class)->getArticles($params);
        return jsonSuccess($articles);
    }

    public function getArticle(Request $request)
    {
        $id = $request->id;
        $type = $request->input('type', '');

        $types = [
            'about' => 100002, // 关于我们
            'contact' => 100003, // 联系我们
            'user_agreement' => 100000, // 用户协议
            'privacy_agreement' => 100001, // 隐私协议
            'vip' => 100004, // VIP
        ];
        if (isset($types[$type])) $id = $types[$type];

        $article = app(ArticleRepository::class)->getArticle($id);
        return jsonSuccess($article);
    }
}
