<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use DB;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        return redirect('/download');
        return view(Config('common.view.tpl_folder') . '.home.index');
    }

    public function download(Request $request)
    {
        return view(Config('common.view.tpl_folder') . '.home.download');
    }
}
