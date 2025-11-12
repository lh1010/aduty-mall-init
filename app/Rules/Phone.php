<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Phone implements Rule
{
    public function passes($attribute, $value)
    {
        $chars = "/^((\(\d{2,3}\))|(\d{3}\-))?1(3|4|5|6|7|8|9)\d{9}$/";
        if (preg_match($chars, $value)) return true;
    }

    public function message()
    {
        return '手机号格式错误';
    }
}
