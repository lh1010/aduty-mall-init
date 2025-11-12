<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Password;

class updateUserPassword extends FormRequest
{
    public function rules()
    {
        return [
            'password' => ['required', new Password],
        ];
    }

    public function messages()
    {
        return [
            'password.required' => '新密码不能为空',
        ];
    }
}
