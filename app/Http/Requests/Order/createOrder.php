<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class createOrder extends FormRequest
{
    public function rules()
    {
        return [
            'type' => [
                'required',
                Rule::in(['cart', 'onekeybuy']),
            ],
        ];
    }

    public function messages()
    {
        return [
            'type.required' => '未知类型',
            'type.in' => '未知类型',
        ];
    }
}
