<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 价格规则
 * 支持 0.00
 */
class Price implements Rule
{
    public function passes($attribute, $value)
    {
        $preg = "/^[1-9]+\d*(.\d{1,2})?$|^\d+.\d{1,2}$/";
        if (preg_match($preg, $value)) return true;
    }

    public function message()
    {
        return '金额格式错误';
    }
}
