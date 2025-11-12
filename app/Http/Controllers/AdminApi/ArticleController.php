<?php

namespace App\Http\Controllers\AdminApi;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\ArticleRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArticleController extends BaseController
{
    public function getArticlesPaginate(Request $request)
    {
        $params = $request->all();
        $limit = $request->input('page_size', 15);
        $type = $request->input('type', '');

        $types = [
            'help' => 100001, // 帮助中心
        ];
        if (isset($types[$type])) $params['category_id'] = $types[$type];

        $articles = app(ArticleRepository::class)->getList($params, $type = 'paginate', $limit);
        return jsonSuccess($articles);
    }

    public function getArticle(Request $request)
    {
        $id = $request->id;
        $type = $request->input('type', '');

        $types = [
            'about' => 100002, // 关于我们
        ];
        if (isset($types[$type])) $id = $types[$type];

        $article = app(ArticleRepository::class)->getShow($id);
        return jsonSuccess($article);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => ['required'],
        ];
        $messages = [
            'title.required' => '标题不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        if (DB::table('article')->where('url', $request->url)->first()) {
            return jsonFailed('请保证url的唯一性，当前url已存在');
        }

        $params = $request->all();
        $data = app(ArticleRepository::class)->setCreateUpdateParams($params);

        DB::beginTransaction();
        try {
            $article_id = DB::table('article')->insertGetId($data);

            if (!isset($params['url']) || empty($params['url'])) {
                DB::table('article')->where('id', $article_id)->update(['url' => $article_id]);
            }

            // 图片介绍
            if (isset($params['images']) && !empty($params['images'])) {
                $images = array_column($params['images'], 'image');
                $image_ids = array_column($params['images'], 'id');
                $data_images = [];
                foreach ($images as $key => $value) {
                    $data_images[$key]['article_id'] = $article_id;
                    $data_images[$key]['image'] = fileFormat($value);
                }
                DB::table('article_image')->insert($data_images);
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function update(Request $request)
    {
        $article = DB::table('article')->where(['id' => $request->id])->where('status', '<>', 99)->first();
        if (empty($article)) return jsonFailed('内容不存在');

        $rules = [
            'title' => ['required'],
        ];
        $messages = [
            'title.required' => '标题不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        if (DB::table('article')->where('url', $request->url)->where('id', '<>', $article->id)->first()) {
            return jsonFailed('请保证url的唯一性，当前url已存在');
        }

        $params = $request->all();
        $data = app(ArticleRepository::class)->setCreateUpdateParams($params);
        if (!isset($params['url']) || empty($params['url'])) $data['url'] = $article->id;

        DB::beginTransaction();
        try {
            DB::table('article')->where('id', $article->id)->update($data);
            $article_id = $article->id;

            // 图片介绍
            if (isset($params['images']) && !empty($params['images'])) {
                $images = array_column($params['images'], 'image');
                $image_ids = array_column($params['images'], 'id');
                $data_images = []; // 新增的数据
                $update_ids = []; // 需要更新的旧数据的ID集
                foreach ($images as $key => $value) {
                    $value = fileFormat($value);
                    // 新增的数据
                    if (!isset($image_ids[$key]) || empty($image_ids[$key])) {
                        if (!empty($value)) {
                            $data_images[$key]['article_id'] = $article_id;
                            $data_images[$key]['image'] = $value;
                        }
                    } else {
                        // 更新旧数据
                        $update_ids[$key] = $image_ids[$key];
                        DB::table('article_image')->where('id', $image_ids[$key])->update(['image' => $value]);
                    }
                }
                // 删除旧数据
                DB::table('article_image')->where('article_id', $article_id)->whereNotIn('id', $update_ids)->delete();
                // 新增新数据
                DB::table('article_image')->insert($data_images);
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function getCategory(Request $request)
    {
        $category = DB::table('article_category')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($category)) return jsonSuccess($category);

        $category->cover = fileView($category->cover);

        return jsonSuccess($category);
    }

    public function getCategorys(Request $request)
    {
        $categorys = app(ArticleRepository::class)->getCategorys([], $type = 'treeList');
        return jsonSuccess($categorys);
    }

    public function categoryStore(Request $request)
    {
        $rules = [
            'name' => ['required'],
        ];
        $messages = [
            'name.required' => '名字不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        if (DB::table('article_category')->where('url', $request->url)->first()) {
            return jsonFailed('请保证url的唯一性，当前url已存在');
        }

        $params = $request->all();
        $data = app(ArticleRepository::class)->setStoreUpdateParams_category($params);

        DB::beginTransaction();
        try {
            $id = DB::table('article_category')->insertGetId($data);
            if (!isset($params['url']) || empty($params['url'])) {
                DB::table('article_category')->where('id', $id)->update(['url' => $id]);
            }
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function categoryUpdate(Request $request)
    {
        $category = DB::table('article_category')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($category)) return jsonFailed('分类不存在');

        $params = $request->all();
        $data = app(ArticleRepository::class)->setStoreUpdateParams_category($params);
        if (!isset($params['url']) || empty($params['url'])) $data['url'] = $request->id;

        if (DB::table('article_category')->where('url', $data['url'])->where('id', '<>', $request->id)->first()) {
            return jsonFailed('请保证url的唯一性，当前url已存在');
        }

        DB::beginTransaction();
        try {
            DB::table('article_category')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function categoryDelete(Request $request)
    {
        if ($request->id == 100000) return jsonFailed('系统文档分类不能被删除');

        if (DB::table('article')->where('category_id', $request->id)->first()) {
            return jsonFailed('请先移除该分类下的文档');
        }

        if (DB::table('article_category')->where('parent_id', $request->id)->first()) {
            return jsonFailed('请先移除该分类下的子分类');
        }

        DB::table('article_category')->where('id', $request->id)->delete();

        return jsonSuccess();
    }
}
