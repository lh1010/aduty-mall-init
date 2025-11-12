<?php

namespace App\Repositorys\Admin;

use DB;

class UserRepository
{
    public function getUsers($params = [], $type = 'paginate', $limit = 15)
    {
        $select = ['user.*'];
        $query = DB::table('user');
        $query->select($select);
        $query->where('user.status', '<>', 99);
        $this->setParams($query, $params);
        $query->orderBy('user.id', 'desc');
        if ($type == 'paginate') {
            $users = $query->paginate($limit);
            $user_ids = array_column($users->items(), 'id');
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $users = $query->get()->toArray();
            $user_ids = array_column($users, 'id');
        }
        $now_date = date('Y-m-d H:i:s');

        // 用户VIP
        $user_members = DB::table('user_member')->whereIn('user_id', $user_ids)->where('end_date', '>=', $now_date)->orderBy('end_date', 'desc')->get()->toArray();
        $array = [];
        foreach ($user_members as $key => $value) {
            $array[$value->user_id][] = $value;
        }
        foreach ($users as $key => $value) {
            $users[$key]->vip = 0;
            $users[$key]->identity = '普通用户';
            if (isset($array[$value->id])) {
                $users[$key]->vip = 1;
                $users[$key]->identity = '会员用户';
            }
        }

        // 上级用户
        foreach ($users as $key => $value) {
            $users[$key]->puser = [];
            if ($value->pid != 0) {
                $users[$key]->puser = DB::table('user')->where('id', $value->pid)->first();
            }
        }

        foreach ($users as $key => $value) {
            $users[$key]->status_show = Config('common.user.status')[$value->status];
            $users[$key]->realname_auth_show = Config('common.user.realname_auth_status')[$value->realname_auth];
            $users[$key]->company_auth_show = Config('common.user.company_auth_status')[$value->company_auth];
            $users[$key]->avatar = !empty($value->avatar) ? fileView($value->avatar) : Config('common.image.user_avatar');
        }

        return $users;
    }

    public function setParams($query, $params = [])
    {
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where(function($query) use ($params) {
                $query->where('id', $params['k'])
                    ->orWhere('nickname', 'like', "%" . $params['k'] . "%")
                    ->orWhere('phone', $params['k']);
            });
        }

        // 实名认证
        if (isset($params['realname_auth']) && $params['realname_auth'] != '') {
            $query->where('user.realname_auth', $params['realname_auth']);
        }

        // 企业认证
        if (isset($params['company_auth']) && $params['company_auth'] != '') {
            $query->where('user.company_auth', $params['company_auth']);
        }

        // 排序
        if (isset($params['order']) && $params['order'] != '') {
            if ($params['order'] == '钱包最多') {
                $query->orderBy('user.wallet', 'desc');
            }
            if ($params['order'] == '金币最多') {
                $query->orderBy('user.gold', 'desc');
            }
        }
    }

    public function getUser($id, $params = [])
    {
        $query = DB::table('user');
        $query->where('id', $id);
        $query->where('status', '<>', 99);
        $user = $query->first();
        if (empty($user)) $user;

        $user->avatar = fileView($user->avatar);
        $user->contact = DB::table('user_contact')->where('user_id', $user->id)->first();

        return $user;
    }

    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['nickname'])) $data['nickname'] = $params['nickname'];
        if (isset($params['phone'])) $data['phone'] = $params['phone'];
        if (isset($params['password']) && !empty($params['password'])) $data['password'] = md5($params['password']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        $data['avatar'] = '';
        if (isset($params['avatar']) && !empty($params['avatar']) && $params['avatar'] != Config('common.image.user_avatar')) {
            $data['avatar'] = fileFormat($params['avatar']);
        }
        if (isset($params['sex'])) $data['sex'] = $params['sex'];
        if (isset($params['register_client'])) $data['register_client'] = $params['register_client'];
        return $data;
    }
}
