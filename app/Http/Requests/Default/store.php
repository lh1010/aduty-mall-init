<?php

namespace App\Http\Requests\Default;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class store extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '名字不能为空',
        ];
    }
}
