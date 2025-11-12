<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Email implements Rule
{
    public function passes($attribute, $value)
    {
        // 规则待写
        // ...

        return true;
    }

    public function message()
    {
        return '邮箱格式错误';
    }
}
