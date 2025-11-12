<?php

namespace App\Repositorys\Admin;

use DB;

class SubjectRepository
{
    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['category_id'])) $data['category_id'] = $params['category_id'];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['shortname'])) $data['shortname'] = $params['shortname'];
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['content'])) $data['content'] = $params['content'];
        if (isset($params['content_markdown'])) $data['content_markdown'] = $params['content_markdown'];
        if (isset($params['tpl_show'])) $data['tpl_show'] = $params['tpl_show'];
        if (isset($params['url']) && !empty($params['url'])) $data['url'] = $params['url'];
        if (isset($params['seo_title']) && !empty($params['seo_title'])) $data['seo_title'] = $params['seo_title'];
        if (isset($params['seo_keywords']) && !empty($params['seo_keywords'])) $data['seo_keywords'] = $params['seo_keywords'];
        if (isset($params['seo_description']) && !empty($params['seo_description'])) $data['seo_description'] = $params['seo_description'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }

    public function setStoreUpdateParams_category($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['url'])) $data['url'] = $params['url'];
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        $data['seo_title'] = isset($params['seo_title']) && !empty($params['seo_title']) ? $params['seo_title'] : $params['name'];
        $data['seo_keywords'] = isset($params['seo_keywords']) && !empty($params['seo_keywords']) ? $params['seo_keywords'] : $params['name'];
        $data['seo_description'] = isset($params['seo_description']) && !empty($params['seo_description']) ? $params['seo_description'] : $params['name'];
        $data['tpl_list'] = isset($params['tpl_list']) && !empty($params['tpl_list']) ? $params['tpl_list'] : Config('common.view.subject.tpl_list');
        $data['tpl_show'] = isset($params['tpl_show']) && !empty($params['tpl_show']) ? $params['tpl_show'] : Config('common.view.subject.tpl_show');
        return $data;
    }
}
