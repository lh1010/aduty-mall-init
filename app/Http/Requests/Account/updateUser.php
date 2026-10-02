<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class updateUser extends FormRequest
{
    public function rules()
    {
        return [
            'nickname' => ['required', 'min:2', 'max:8'],
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
            'nickname.min' => '昵称长度不能小于2个字符',
            'nickname.max' => '昵称长度不能大于8个字符',
            'sex.required' => '性别不能为空',
            'sex.in' => '请选择正确性别选项',
        ];
    }
}
