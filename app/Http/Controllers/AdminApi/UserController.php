<?php

namespace App\Http\Controllers\AdminApi;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\UserRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\Price;

class UserController extends BaseController
{
    public function getUsersPaginate(Request $request)
    {
        $params = $request->all();
        $limit = $request->input('pageSize', 15);
        $users = app(UserRepository::class)->getUsers($params, $type = 'paginate', $limit);
        return jsonSuccess($users);
    }

    public function getUser(Request $request)
    {
        $user = app(UserRepository::class)->getUser($request->id);
        return jsonSuccess($user);
    }

    public function store(Request $request)
    {
        $rules = [
            'phone' => ['required'],
        ];
        $messages = [
            'phone.required' => '手机号不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

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

    public function update(Request $request)
    {
        $user = DB::table('user')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($user)) return jsonFailed('内容不存在');

        $rules = [
            'phone' => ['required'],
        ];
        $messages = [
            'phone.required' => '手机号不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

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

    public function rechargeGold(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('用户查询为空');

        $rules = [
            'gold' => ['required', 'integer', 'min:1'],
        ];
        $messages = [
            'gold.required' => '充值金币不能为空',
            'gold.integer' => '金币格式错误',
            'gold.min' => '金币格式错误',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        DB::beginTransaction();
        try {
            DB::table('user')->where('id', $user->id)->increment('gold', $request->gold);
            DB::table('user_gold_log')->insert([
                'user_id' => $user->id,
                'gold' => $request->gold,
                'ident' => 'inc',
                'description' => '后台充值'
            ]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function getGoldLogsPaginate(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('内容不存在');
        $logs = DB::table('user_gold_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        return jsonSuccess($logs);
    }

    public function rechargeWallet(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('用户查询为空');

        $rules = [
            'price' => ['required', new Price],
        ];
        $messages = [
            'price.required' => '充值金额不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        DB::beginTransaction();
        try {
            DB::table('user')->where('id', $user->id)->increment('wallet', $request->price);
            DB::table('user_wallet_log')->insert([
                'user_id' => $user->id,
                'price' => $request->price,
                'ident' => 'inc',
                'description' => '后台充值'
            ]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function getWalletLogsPaginate(Request $request)
    {
        $user = DB::table('user')->where('id', $request->user_id)->first();
        if (empty($user)) return jsonFailed('内容不存在');
        $logs = DB::table('user_wallet_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        return jsonSuccess($logs);
    }

    public function realnameAuth(Request $request)
    {
        $log = DB::table('user_realname_auth_log')->where('user_id', $request->user_id)->orderBy('created_at', 'desc')->first();
        if (empty($log)) return jsonFailed('内容不存在');

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

    public function getRealnameAuthLogs(Request $request)
    {
        $logs = DB::table('user_realname_auth_log')
            ->where('user_id', $request->user_id)
            ->orderBy('id', 'desc')
            ->paginate();

        foreach ($logs as $key => $value) {
            $logs[$key]->idcard_img1 = fileView($value->idcard_img1);
            $logs[$key]->idcard_img2 = fileView($value->idcard_img2);
            $logs[$key]->status_show = config('common.user.realname_auth_status')[$value->status];
        }

        return jsonSuccess($logs);
    }

    public function companyAuth(Request $request)
    {
        $log = DB::table('user_company_auth_log')->where('user_id', $request->user_id)->orderBy('created_at', 'desc')->first();
        if (empty($log)) return jsonFailed('内容不存在');

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

    public function getCompanyAuthLogs(Request $request)
    {
        $logs = DB::table('user_company_auth_log')
            ->where('user_id', $request->user_id)
            ->orderBy('id', 'desc')
            ->paginate();

        foreach ($logs as $key => $value) {
            $logs[$key]->business_license = fileView($value->business_license);
            $logs[$key]->status_show = config('common.user.company_auth_status')[$value->status];
        }

        return jsonSuccess($logs);
    }
}
