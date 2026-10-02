<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\AdverRepository;
use App\Repositorys\ProductRepository;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        return redirect('/web/download');
    }

    public function download(Request $request)
    {
        return view('web.home.download');
    }
}
