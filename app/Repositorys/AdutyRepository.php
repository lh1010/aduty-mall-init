<?php

namespace App\Repositorys;

use DB;

class AdutyRepository
{
    /**
     * 生成常规相关文件
     * @param string $tablename
     * @param string $route
     */
    public function genCode($tablename, $route, $params = [])
    {
        $this->genController($tablename, $route);
    }

    // 创建控制器
    public function genController($tablename, $route)
    {
        $controller_path = app_path() . 'Http/Controllers/';
        if (!empty($route)) $controller_path .= $route . '/';
        $controller_path = str_replace('\\', '/', $controller_path);
        dd($controller_path);
    }
}
