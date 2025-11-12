<?php

namespace App\Repositorys\Admin;

use DB;

class ArticleRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 15)
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
            $category_parent_ids = $this->getCategoryParentIds($value->category_id);
            $categorys_temp = Db::table('article_category')->whereIn('id', $category_parent_ids)->pluck('name')->toArray();
            $articles[$key]->full_category_name = implode(' > ', $categorys_temp);
            $articles[$key]->full_category_name = $articles[$key]->full_category_name ? $articles[$key]->full_category_name : '暂无分类';
        }

        return $articles;
    }

    public function setParams($query, $params = [])
    {
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('article.title', 'like', "%" . $params['k'] . "%");
        }

        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $query->where('article.category_id', $params['category_id']);
        }

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('article.status', $params['status']);
        }
    }

    public function getShow($id)
    {
        $query = DB::table('article');
        $query->where('id', $id);
        $query->where('status', 1);
        $article = $query->first();
        if (empty($article)) return $article;

        $article->cover = !empty($article->cover) ? fileView($article->cover) : '';

        // 图片集
        $images = DB::table('article_image')->where('article_id', $article->id)->get()->toArray();
        foreach ($images as $key => $value) {
            $images[$key]->image = fileView($value->image);
        }
        $article->images = $images;

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
            //$article->content = preg_replace($preg, '<img onclick="showImage(\'' . $url . '$2\')" src="' . $url . '$2" />', $article->content);
            $article->content = preg_replace($preg, '<img src="' . $url . '$2" />', $article->content);
        }

        return $article;
    }

    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['title'])) $data['title'] = $params['title'];
        if (isset($params['shorttitle'])) $data['shorttitle'] = $params['shorttitle'];
        if (isset($params['category_id']) && !empty($params['category_id'])) $data['category_id'] = $params['category_id'];
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['content'])) $data['content'] = $params['content'];
        if (isset($params['content_markdown'])) $data['content_markdown'] = $params['content_markdown'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['click']) && is_numeric($params['click'])) $data['click'] = $params['click'];
        if (isset($params['created_at']) && !empty($params['created_at'])) $data['created_at'] = $params['created_at'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        $data['seo_title'] = isset($params['seo_title']) && !empty($params['seo_title']) ? $params['seo_title'] : $params['title'];
        $data['seo_keywords'] = isset($params['seo_keywords']) && !empty($params['seo_keywords']) ? $params['seo_keywords'] : $params['title'];
        $data['seo_description'] = isset($params['seo_description']) && !empty($params['seo_description']) ? $params['seo_description'] : $params['title'];
        if (isset($params['url'])) $data['url'] = $params['url'];
        return $data;
    }

    public function setStoreUpdateParams_category($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['url'])) $data['url'] = $params['url'];
        if (isset($params['parent_id'])) $data['parent_id'] = is_numeric($params['parent_id']) ? $params['parent_id'] : 0;
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        $data['seo_title'] = isset($params['seo_title']) && !empty($params['seo_title']) ? $params['seo_title'] : $params['name'];
        $data['seo_keywords'] = isset($params['seo_keywords']) && !empty($params['seo_keywords']) ? $params['seo_keywords'] : $params['name'];
        $data['seo_description'] = isset($params['seo_description']) && !empty($params['seo_description']) ? $params['seo_description'] : $params['name'];
        $data['tpl_list'] = isset($params['tpl_list']) && !empty($params['tpl_list']) ? $params['tpl_list'] : Config('common.view.article.tpl_list');
        $data['tpl_show'] = isset($params['tpl_show']) && !empty($params['tpl_show']) ? $params['tpl_show'] : Config('common.view.article.tpl_show');
        return $data;
    }

    private $treeList = [];
    public function getCategorys($params = [], $type = 'tree')
    {
        $query = DB::table('article_category');
        $query->where('status', '<>', 99);
        $query->orderBy('sort', 'desc');
        $categorys = $query->get()->toArray();

        foreach ($categorys as $key => $value) {
            $categorys[$key]->status_show = Config('common.article.category_status')[$value->status];
        }

        switch ($type) {
            case 'select':
                return $categorys;
                break;
            case 'tree':
                $categorys = $this->tree($categorys);
                return $categorys;
                break;
            case 'treeList':
                $categorys = $this->treeList($categorys);
                return $categorys;
                break;
        }
    }

    private function tree($data, $parent_id = 0, $level = 1)
    {
        foreach ($data as $value){
            if ($value->parent_id == $parent_id) {
                $value->level = $level;
                $this->treeList[] = $value;
                $this->tree($data, $value->id, $level + 1);
            }
        }
        return $this->treeList;
    }

    private function treeList($data, $parent_id = 0)
    {
        $treeList = [];
        foreach ($data as $value) {
            if ($value->parent_id == $parent_id) {
                $value->children = [];
                $children = $this->treeList($data, $value->id);
                if (!empty($children)) {
                    $value->children = $children;
                }
                $treeList[] = $value;
            }
        }
        return $treeList;
    }

    public function getCategoryParentIds($id, $i = 0)
    {
        $parent_id = DB::table('article_category')->where('id', $id)->value('parent_id');
        if ($i == 0) {
            $this->parent_ids = [];
            $this->parent_ids[] = (int)$id;
        }
        if ($parent_id != 0) {
            array_unshift($this->parent_ids, $parent_id);
            $i++;
            $this->getCategoryParentIds($parent_id, $i);
        }
        return $this->parent_ids;
    }

    public function getCategoryChildIds($id, $i = 0)
	{
        $child_ids = DB::table('article_category')->where('parent_id', $id)->pluck('id')->toArray();
        if ($i == 0) {
            $this->child_ids = [];
            $this->child_ids[] = (int)$id;
        }
        $this->child_ids = array_merge($this->child_ids, $child_ids);
		if (!empty($child_ids)) {
			foreach ($child_ids as $key => $value) {
				$i++;
				$this->getCategoryChildIds($value, $i);
			}
		}
		return $this->child_ids;
    }
}
