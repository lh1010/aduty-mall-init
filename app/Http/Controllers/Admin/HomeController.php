<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    public function index()
    {
        return view('admin.home.index');
    }

    public function welcome()
    {
        return view('admin.home.welcome');
    }
}
