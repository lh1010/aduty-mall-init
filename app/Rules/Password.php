<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Password implements Rule
{
    public function passes($attribute, $value)
    {
        if (strlen($value) < 6) return false;

        // 待扩展其他规则
        // ...

        return true;
    }

    public function message()
    {
        return '密码长度不能低于6位';
    }
}
