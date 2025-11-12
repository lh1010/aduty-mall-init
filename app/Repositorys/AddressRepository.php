<?php

namespace App\Repositorys;

use DB;

class AddressRepository
{
    public function setStoreUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['user_id'])) $data['user_id'] = $params['user_id'];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['phone'])) $data['phone'] = $params['phone'];
        if (isset($params['province_id'])) $data['province_id'] = $params['province_id'];
        if (isset($params['province_name'])) $data['province_name'] = $params['province_name'];
        if (isset($params['city_id'])) $data['city_id'] = $params['city_id'];
        if (isset($params['city_name'])) $data['city_name'] = $params['city_name'];
        if (isset($params['district_id'])) $data['district_id'] = $params['district_id'];
        if (isset($params['district_name'])) $data['district_name'] = $params['district_name'];
        if (isset($params['detailed_address'])) $data['detailed_address'] = $params['detailed_address'];
        $data['default'] = isset($params['default']) && is_numeric($params['default']) ? $params['default'] : 0;
        return $data;
    }
}
