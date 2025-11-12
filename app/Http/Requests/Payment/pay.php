<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Price;

class pay extends FormRequest
{
    public function rules()
    {
        return [
            'payment_way' => [
                'required',
                Rule::in(['wallet' ,'alipay_pc', 'alipay_wap', 'alipay_jsapi', 'weixinpay_native', 'weixinpay_h5', 'weixinpay_jsapi_wxmp', 'weixinpay_jsapi_wxapp'])
            ]
        ];
    }

    public function messages()
    {
        return [
            'payment_way.required' => '请选择支付方式',
            'payment_way.in' => '不支持的支付方式',
        ];
    }
}
