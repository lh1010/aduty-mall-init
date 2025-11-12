<?php

namespace App\Repositorys;

use DB;

class AdverRepository
{
    public function getAdver($code)
    {
        $adver = DB::table('adver')->where(['status' => 1, 'code' => $code])->first();
        if (empty($adver)) return $adver;
        $values = DB::table('adver_value')->where('adver_id', $adver->id)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        foreach ($values as $key => $value) {
            $values[$key]->image = fileView($value->image);
        }
        $adver->values = $values;
        return $adver;
    }
}
