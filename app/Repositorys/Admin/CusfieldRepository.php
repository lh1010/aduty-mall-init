<?php

namespace App\Repositorys\Admin;

use DB;

class CusfieldRepository
{
    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['group_id'])) $data['group_id'] = $params['group_id'];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['type'])) $data['type'] = $params['type'];
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['required'])) $data['required'] = $params['required'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = $params['status'];
        // 选项值
        if (isset($params['options']) && !empty($params['options'])) $data['options'] = implode('[luck]', $params['options']);
        return $data;
    }

    public function setStoreUpdateParams_group($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['description'])) $data['description'] = $params['description'];
        return $data;
    }
}
