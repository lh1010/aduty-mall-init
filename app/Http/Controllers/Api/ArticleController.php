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

    public function getList(Request $request)
    {
        $params = $request->all();
        $type = $request->input('type', '');
        $types = [
            'help' => 100001, // 帮助中心
        ];
        if (isset($types[$type])) $params['category_id'] = $types[$type];
        $articles = app(ArticleRepository::class)->getList($params);
        return jsonSuccess($articles);
    }

    public function getShow(Request $request)
    {
        $id = $request->id;
        $type = $request->input('type', '');
        $types = [
            'about' => 100002, // 关于我们
            'contact' => 100003, // 联系我们
            'user_agreement' => 100000, // 用户协议
            'privacy_agreement' => 100001, // 隐私协议
            'vip' => 0, // VIP
        ];
        if (isset($types[$type])) $id = $types[$type];
        $article = app(ArticleRepository::class)->getShow($id);
        return jsonSuccess($article);
    }

    public function getHelpCategorys()
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
        return jsonSuccess($categorys);
    }
}
