<?php

namespace App\Http\Controllers\Admin\FreeAccess;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;

class CommonController extends BaseController
{
    public function no_auth()
    {
        dd('无访问权限');
    }
}
