<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Phone;

class login_password extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', new Phone],
            'password' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => '手机号不能为空',
            'password.required' => '密码不能为空'
        ];
    }
}
