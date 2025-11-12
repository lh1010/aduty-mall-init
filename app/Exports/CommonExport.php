<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
# 表头
use Maatwebsite\Excel\Concerns\WithHeadings;
# 自动适应单元格宽
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CommonExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $headers;
    protected $lists;

    public function __construct(array $headers, array $lists)
    {
        $this->headers = $headers;
        $this->lists = $lists;
    }

    public function array(): array
    {
        return $this->lists;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}