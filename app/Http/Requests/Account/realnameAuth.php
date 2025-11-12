<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class realnameAuth extends FormRequest
{
    public function rules()
    {
        return [
            'realname' => ['required'],
            'idcard' => ['required'],
            'idcard_img1' => ['required'],
            'idcard_img2' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'realname.required' => '真实姓名不能为空',
            'idcard.required' => '身份证号不能为空',
            'idcard_img1.required' => '身份证正面照不能为空',
            'idcard_img2.required' => '身份证反面照不能为空',
        ];
    }
}
