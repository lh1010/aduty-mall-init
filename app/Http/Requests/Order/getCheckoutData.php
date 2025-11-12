<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class getCheckoutData extends FormRequest
{
    public function rules()
    {
        return [
            'type' => [
                'required',
                Rule::in(['cart' ,'onekeybuy'])
            ]
        ];
    }

    public function messages()
    {
        return [
            'type.required' => '请求类型错误',
            'type.in' => '请求类型错误',
        ];
    }
}
