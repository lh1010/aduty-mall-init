<?php

namespace App\Http\Controllers\AdminApi;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB;

class BaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('CheckAdminApiLogin');
    }
}
