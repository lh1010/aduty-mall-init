<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Phone;

class sendCode extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', new Phone],
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => '手机号不能为空',
        ];
    }
}
