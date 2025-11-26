<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\AddressRepository;

class AddressController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->middleware('CheckUserLogin');
    }

    public function popupCreate(Request $request)
    {
        $citys = DB::table('city')->where('pid', 0)->get()->toArray();
        return view(Config('common.view.tpl_folder') . '.address.popupCreate', compact('citys'));
    }
}
