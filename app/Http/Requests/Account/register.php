<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NickName;
use App\Rules\Phone;
use App\Rules\Password;

class register extends FormRequest
{
    public function rules()
    {
        return [
            'nickname' => ['required', new NickName],
            'phone' => ['required', new Phone],
            'code' => ['required'],
            'password' => ['required', new Password],
        ];
    }

    public function messages()
    {
        return [
            'nickname.required' => '昵称不能为空',
            'phone.required' => '手机号不能为空',
            'code.required' => '验证码不能为空',
            'password.required' => '密码不能为空',
        ];
    }
}
