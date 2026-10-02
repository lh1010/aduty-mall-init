<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    public function passes($attribute, $value)
    {
        if (strlen($value) < 6 || strlen($value) > 20) return false;
        // 待扩展其他规则
        // ...
        return true;
    }

    public function message()
    {
        return '密码长度必须在6到20位之间';
    }
}
