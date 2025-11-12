<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Phone;

class update extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required'],
            'phone' => ['required', new Phone],
            'province_id' => ['required'],
            'city_id' => ['required'],
            'district_id' => ['required'],
            'detailed_address' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '姓名不能为空',
            'phone.required' => '手机号不能为空',
            'province_id.required' => '请选择省份',
            'city_id.required' => '请选择城市',
            'district_id.required' => '请选择区域',
            'detailed_address.required' => '详细地址不能为空',
        ];
    }
}
