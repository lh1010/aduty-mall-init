<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\OrderRepository;

class CartController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->middleware('CheckUserLogin');
    }

    public function index(Request $request)
    {
        $user = $request->get('user');
        $data = app(OrderRepository::class)->getCartData($user);
        return view(Config('common.view.tpl_folder') . '.cart.index', $data);
    }
}
