<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\UserRepository;
use App\Repositorys\Admin\PaymentRepository;

class UserController extends BaseController
{
    public function list(Request $request)
    {
        $users = app(UserRepository::class)->getUsers($request->all());
        return view('admin.user.list', compact('users'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        $params = $request->all();

        if (empty($params['contact']['weixin']) && empty($params['contact']['qq']) && empty($params['contact']['phone']) && empty($params['contact']['telphone'])) {
            return jsonFailed('必需填写一个联系方式');
        }
        if (DB::table('user')->where('phone', $params['phone'])->where('status', '<>', 99)->first()) {
            return jsonFailed('当前手机号已存在');
        }

        $params['register_client'] = '后台添加';
        $params['nickname'] = !empty($params['nickname']) ? $params['nickname'] : ('u' . rand(100000, 999999));

        DB::beginTransaction();
        try {
            $data = app(UserRepository::class)->setCreateUpdateParams($params);
            $user_id = DB::table('user')->insertGetId($data);

            // 联系方式
            $data_user_contact = [
                'user_id' => $user_id,
                'weixin' => isset($params['contact']['weixin']) ? $params['contact']['weixin'] : '',
                'qq' => isset($params['contact']['qq']) ? $params['contact']['qq'] : '',
                'phone' => isset($params['contact']['phone']) ? $params['contact']['phone'] : '',
                'telphone' => isset($params['contact']['telphone']) ? $params['contact']['telphone'] : '',
            ];
            DB::table('user_contact')->insert($data_user_contact);

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        $user = DB::table('user')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($user)) abort(404);

        $user->contact = DB::table('user_contact')->where('user_id', $user->id)->first();

        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = DB::table('user')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($user)) return jsonFailed('内容不存在');

        $params = $request->all();

        if (empty($params['contact']['weixin']) && empty($params['contact']['qq']) && empty($params['contact']['phone']) && empty($params['contact']['telphone'])) {
            return jsonFailed('必需填写一个联系方式');
        }
        if (DB::table('user')->where('phone', $params['phone'])->where('id', '<>', $user->id)->where('status', '<>', 99)->first()) {
            return jsonFailed('当前手机号已存在');
        }

        $params['nickname'] = !empty($params['nickname']) ? $params['nickname'] : ('u' . rand(100000, 999999));

        DB::beginTransaction();
        try {
            $data = app(UserRepository::class)->setCreateUpdateParams($params);
            DB::table('user')->where('id', $request->id)->update($data);

            // 联系方式
            $data_user_contact = [
                'weixin' => isset($params['contact']['weixin']) ? $params['contact']['weixin'] : '',
                'qq' => isset($params['contact']['qq']) ? $params['contact']['qq'] : '',
                'phone' => isset($params['contact']['phone']) ? $params['contact']['phone'] : '',
                'telphone' => isset($params['contact']['telphone']) ? $params['contact']['telphone'] : '',
            ];
            DB::table('user_contact')->where('user_id', $request->id)->update($data_user_contact);

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::table('user')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function gold(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('用户查询为空');
        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $ident = $request->input('ident', 'inc');
                if ($ident == 'inc') {
                    $final_gold = bcadd($user->gold, $request->gold, 0);
                    $description = '后台充值';
                } else {
                    $final_gold = bcsub($user->gold, $request->gold, 0);
                    $description = '后台扣除';
                }
                if ($final_gold < 0) return jsonFailed('金币余额不能小于0');
                DB::table('user')->where('id', $user->id)->update(['gold' => $final_gold]);
                DB::table('user_gold_log')->insert([
                    'user_id' => $user->id,
                    'gold' => $request->gold,
                    'ident' => $ident,
                    'description' => $description
                ]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed();
            }
        }
        $logs = DB::table('user_gold_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        return view('admin.user.gold', compact('user', 'logs'));
    }

    public function wallet(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('用户查询为空');
        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $ident = $request->input('ident', 'inc');
                if ($ident == 'inc') {
                    $final_wallet = bcadd($user->wallet, $request->price, 2);
                    $description = '后台充值';
                } else {
                    $final_wallet = bcsub($user->wallet, $request->price, 2);
                    $description = '后台扣除';
                }
                if ($final_wallet < 0) return jsonFailed('钱包余额不能小于0');
                DB::table('user')->where('id', $user->id)->update(['wallet' => $final_wallet]);
                DB::table('user_wallet_log')->insert([
                    'user_id' => $user->id,
                    'price' => $request->price,
                    'ident' => $ident,
                    'description' => $description
                ]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed($th->getMessage());
            }
        }
        $logs = DB::table('user_wallet_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        return view('admin.user.wallet', compact('user', 'logs'));
    }

    public function realname_auth(Request $request)
    {
        $log = DB::table('user_realname_auth_log')->where('user_id', $request->user_id)->orderBy('created_at', 'desc')->first();
        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                DB::table('user_realname_auth_log')->where('id', $log->id)->update(['status' => $request->status, 'message' => $request->message]);
                DB::table('user')->where('id', $log->user_id)->update(['realname_auth' => $request->status]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed();
            }
        }
        $logs = DB::table('user_realname_auth_log')->where('user_id', $request->user_id)->limit(5)->orderBy('id', 'desc')->get()->toArray();
        return view('admin.user.realname_auth', compact('log', 'logs'));
    }

    public function company_auth(Request $request)
    {
        $log = DB::table('user_company_auth_log')->where('user_id', $request->user_id)->orderBy('created_at', 'desc')->first();
        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                DB::table('user_company_auth_log')->where('id', $log->id)->update(['status' => $request->status, 'message' => $request->message]);
                DB::table('user')->where('id', $log->user_id)->update(['company_auth' => $request->status]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed();
            }
        }
        $logs = DB::table('user_company_auth_log')->where('user_id', $request->user_id)->limit(5)->orderBy('id', 'desc')->get()->toArray();
        return view('admin.user.company_auth', compact('log', 'logs'));
    }
}
