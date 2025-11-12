<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Phone;

class updateUserContact extends FormRequest
{
    public function rules()
    {
        return [
            'phone' => [new Phone]
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
