<?php

namespace App\Repositorys;

use DB;

class DefaultRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 15)
    {
        $select = ['tbname.*'];
        $query = DB::table('tbname');
        $query->select($select);
        $this->setParams($query, $params);

        if ($type == 'paginate') {
            $results = $query->paginate($limit);
            $ids = array_column($results->items(), 'id');
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $results = $query->get()->toArray();
            $ids = array_column($results, 'id');
        }

        foreach ($results as $key => $value) {
            $results[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.tbname_cover');
            $results[$key]->status_show = Config('common.tbname.status')[$value->status];
        }

        return $results;
    }

    private function setParams($query, $params = [])
    {
        $query->where('tbname.status', '<>', 99);

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('tbname.user_id', $params['user_id']);
        }

        if (isset($params['id']) && !empty($params['id'])) {
            $query->where('tbname.id', $params['id']);
        }

        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('tbname.content', 'like', "%" . $params['k'] . "%");
        }

        $query->orderBy('tbname.id', 'desc');
    }

    public function getShow($id = '', $params = [])
    {
        $select = ['tbname.*'];
        $query = DB::table('tbname');
        $query->select($select);

        if ($id != '') {
            $query->where('tbname.id', $id);
        }

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('tbname.user_id', $params['user_id']);
        }

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('tbname.status', $params['status']);
        }

        $result = $query->first();
        if (empty($result)) return $result;

        $result->cover = !empty($result->cover) ? fileView($result->cover) : Config('common.image.tbname_cover');
        $result->status_show = Config('common.tbname.status')[$result->status];

        return $result;
    }

    public function setStoreUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['id'])) $data['id'] = $params['id'];
        if (isset($params['title'])) $data['title'] = $params['title'];
        if (isset($params['cover']) && $params['cover'] != Config('common.image.tbname_cover'))  $data['cover'] = fileFormat($params['cover']);
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        return $data;
    }
}
