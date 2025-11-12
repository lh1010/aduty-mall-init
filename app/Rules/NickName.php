<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NickName implements Rule
{
    public function passes($attribute, $value)
    {
        if (mb_strlen($value) > 8) return false;

        // 待扩展其他规则
        // ...

        return true;
    }

    public function message()
    {
        return '昵称长度不能超过8位';
    }
}
