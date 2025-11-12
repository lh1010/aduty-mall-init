<?php

namespace App\Repositorys;

use DB;

class ArticleRepository
{
    public function getArticles($params = [], $type = 'paginate', $limit = 15)
    {
        $query = DB::table('article');
        $this->setParams($query, $params);
        $query->where('article.status', '<>', 99);
        $query->orderBy('article.sort', 'desc');
        $query->orderBy('article.created_at', 'desc');

        if ($type == 'paginate') {
            $articles = $query->paginate($limit);
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $articles = $query->get()->toArray();
        }

        foreach ($articles as $key => $value) {
            $articles[$key]->cover = !empty($value->cover) ? fileView($value->cover) : '';
        }

        return $articles;
    }

    public function setParams($query, $params = [])
    {
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $query->where('article.category_id', $params['category_id']);
        }

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('article.status', $params['status']);
        }
    }

    public function getArticle($id)
    {
        $query = DB::table('article');
        $query->where('id', $id);
        $query->where('status', 1);
        $article = $query->first();
        if (empty($article)) return $article;

        $preg = "/<img(.*?)src=\"(.*?)\"(.*?)>/is";
        if (preg_match_all($preg, $article->content, $matches)) {
            foreach ($matches[2] as $key => $value) {
                if (strstr($value, 'http')) {
                    $new_img_url = $value;
                    $new_img_url = str_replace(Config('common.app_url'), '', $new_img_url);
                    $new_img_url = str_replace(Config('common.oss.url'), '', $new_img_url);
                    $article->content = str_replace($value, $new_img_url, $article->content);
                }
            }
            $url = Config('common.app_url');
            if (Config('common.oss.status')) $url = Config('common.oss.url');
            $article->content = preg_replace($preg, '<img onclick="showImage(\'' . $url . '$2\')" src="' . $url . '$2" />', $article->content);
        }

        return $article;
    }
}
