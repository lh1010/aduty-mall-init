<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Phone;

class login extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', new Phone],
            'code' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => '手机号不能为空',
            'code.required' => '验证码不能为空'
        ];
    }
}
