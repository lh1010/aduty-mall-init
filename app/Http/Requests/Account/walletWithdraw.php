<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Price;

class walletWithdraw extends FormRequest
{
    public function rules()
    {
        return [
            'price' => ['required', new Price],
            'alipay_account' => ['required'],
            'alipay_name' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'price.required' => '提现金额不能为空',
            'alipay_account.required' => '支付宝账号不能为空',
            'alipay_name.required' => '支付宝账号名字不能为空'
        ];
    }
}
