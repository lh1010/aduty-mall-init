<?php

namespace App\Repositorys;

use DB;

class UserRepository
{
    public function getUsers($params = [], $type = 'paginate', $limit = 15)
    {
        $select = [
            'user.*',
        ];
        $query = DB::table('user')->select($select);
        $this->setParams($query, $params);
        if (!isset($params['random'])) $query->orderBy('user.id', 'desc');
        if ($type == 'paginate') {
            $users = $query->paginate($limit);
            $user_ids = array_column($users->items(), 'id');
        } else {
            if ($limit > 0 ) $query->limit($limit);
            if (isset($params['random']) && $params['random'] == 1) {
                $query->inRandomOrder()->take($limit);
            }
            $users = $query->get()->toArray();
            $user_ids = array_column($users, 'id');
        }

        foreach ($users as $key => $value) {
            $users[$key]->avatar = !empty($value->avatar) ? fileView($value->avatar) : Config('common.image.user_avatar');
            $users[$key]->description_show = str_replace("\n", "<br/>", $value->description);
            $users[$key]->ziyuan_show = str_replace("\n", "<br/>", $value->ziyuan);
            $users[$key]->xuqiu_show = str_replace("\n", "<br/>", $value->xuqiu);
        }
        return $users;
    }

    private function setParams($query, $params = [])
    {
        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('user.status', $params['status']);
        }

        if (isset($params['user_ids'])) {
            $query->whereIn('user.id', $params['user_ids']);
        }

        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('user.nickname', 'like', "%" . $params['k'] . "%");
        }
    }

    public function getUser($id, $params = [])
    {
        $query = DB::table('user');
        $query->where('id', $id);
        $user = $query->first();
        if (empty($user)) return $user;

        $user->avatar = !empty($user->avatar) ? fileView($user->avatar) : Config('common.image.user_avatar');
        $user->description_show = str_replace("\n", "<br/>", $user->description);

        return $user;
    }

    public function setStoreUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['nickname']) && !empty($params['nickname'])) $data['nickname'] = $params['nickname'];
        if (isset($params['sex']) && !empty($params['sex'])) $data['sex'] = $params['sex'];
        if (isset($params['city_id']) && !empty($params['city_id'])) $data['city_id'] = $params['city_id'];
        if (isset($params['phone'])) $data['phone'] = $params['phone'];
        $data['avatar'] = '';
        if (isset($params['avatar']) && !empty($params['avatar']) && $params['avatar'] != Config('common.image.user_avatar')) {
            $data['avatar'] = fileFormat($params['avatar']);
        }
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['company_name'])) $data['company_name'] = $params['company_name'];
        if (isset($params['job'])) $data['job'] = $params['job'];
        if (isset($params['ziyuan'])) $data['ziyuan'] = $params['ziyuan'];
        if (isset($params['xuqiu'])) $data['xuqiu'] = $params['xuqiu'];
        return $data;
    }

    /**
     * 获取团队下级人员信息
     * @param int $i 当前级
     * @param int $max_i 最大级
     * @return array 下级用户信息 含等级level
     *
     * 需修改 传过来所有用户 处理为树状结果
     */
    public function getTeamUsers($user_id, $i = 1, $max_i = 0)
    {
        if ($i == 1) $this->childUsers = [];
        $childUsers = DB::table('user')->where('pid', $user_id)->get()->toArray();
        if (empty($childUsers)) return $this->childUsers;
        if ($max_i > 0 && $i > $max_i) return $this->childUsers;

        foreach ($childUsers as $key => $value) {
            $childUsers[$key]->level = $i;
            $childUsers[$key]->avatar = !empty($value->avatar) ? fileView($value->avatar) : Config('common.image.user_avatar');
        }

        $i++;
        $this->childUsers = array_merge($this->childUsers, $childUsers);

        foreach ($childUsers as $key => $value) {
            $this->getTeamUsers($value->id, $i, $max_i);
        }
        return $this->childUsers;
    }
}
