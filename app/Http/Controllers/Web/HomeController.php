<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\CommonRepository;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        $data = app(CommonRepository::class)->getIndexData_pc();
        $data['page_index_ident'] = 'index';
        return view(Config('common.view.tpl_folder') . '.home.index', $data);
    }

    public function download(Request $request)
    {
        return view(Config('common.view.tpl_folder') . '.home.download');
    }
}
