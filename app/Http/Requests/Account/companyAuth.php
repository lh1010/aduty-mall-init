<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class companyAuth extends FormRequest
{
    public function rules()
    {
        return [
            'company_name' => ['required'],
            'social_credit_code' => ['required'],
            'business_license' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'company_name.required' => '企业全称不能为空',
            'social_credit_code.required' => '信用代码不能为空',
            'business_license.required' => '营业执照不能为空',
        ];
    }
}
