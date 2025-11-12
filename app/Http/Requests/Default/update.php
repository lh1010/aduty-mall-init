<?php

namespace App\Http\Requests\Default;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class update extends FormRequest
{
    public function rules()
    {
        return [
            'id' => ['required'],
            'name' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'id不能为空',
            'name.required' => '名字不能为空',
        ];
    }
}
