<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Schema;

class GenCode extends Command
{
    /**
     * --type controller|repository
     * --file file name
     * --route 示例值:Api|Admin|Web...
     */
    protected $signature = 'command:genCode {--type=} {--file=} {--route=}';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * genCode
     * 生成相关常用文件
     * php artisan command:genCode --type=controller --file=UserController --route=Api
     */
    public function handle()
    {
        $this->table_name = $this->option('table_name', '');
        $this->table_columns = Schema::getColumnListing($this->table_name);
        if (empty($this->table_columns)) dd('不存在的数据库表: '. $this->table_name);

        if ($this->option('route')) $this->route = $this->option('route');

        $this->genController();
        echo "Controller创建成功: " . $this->table_name . "\n";

        $this->genRepository();
        echo "Repository创建成功: " . $this->table_name . "\n";
    }

    // 创建控制器
    public function genController()
    {

    }
}
