<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class updateUser extends FormRequest
{
    public function rules()
    {
        return [
            'nickname' => ['required'],
            'sex' => [
                'required',
                Rule::in(['男', '女']),
            ],
        ];
    }

    public function messages()
    {
        return [
            'nickname.required' => '昵称不能为空',
            'sex.required' => '性别不能为空',
            'sex.in' => '请选择正确性别选项',
        ];
    }
}
