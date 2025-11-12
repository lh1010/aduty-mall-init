<?php

namespace App\Repositorys\Admin;

use URL;
use App\Repositorys\Admin\PageRepository;

class FileRepository
{
    /**
     * 获取图片空间
     * @param string $params['dir']
     * @param int $params['page']
     */
    public function getImagesPaginate($params = [])
    {
        $page = 1;
        $page_size = 14;
        $prev = '';
        $data = [];
        $dirs = [];
        $files = [];
        $folder = $default_folder = str_replace('\\', '/', base_path() . '/public/storage/images/');
        $current_url = URL::current() . '?token=' . time();
        if (isset($params['use_ident'])) $current_url .= '&use_ident=' . $params['use_ident'];

        if (isset($params['dir'])) {
            $folder = $folder . $params['dir'] . '/';
            if ($position = strrpos($params['dir'], '/')) {
                $prev = $current_url . '&dir=' . urlencode(substr($params['dir'], 0, $position));
            } else {
                $prev = $current_url;
            }
        }
        if (isset($params['page'])) $page = $params['page'];

        $dirs = glob($folder.'*', GLOB_ONLYDIR);
        $files = glob($folder.'*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);
        usort($dirs, function($a, $b) {return filemtime($b) - filemtime($a);});
        usort($files, function($a, $b) {return filemtime($b) - filemtime($a);});
        $dirs_files = array_merge($dirs, $files);
        $dirs_files_total = count($dirs_files);
        $dirs_files = array_splice($dirs_files, ($page - 1) * $page_size, $page_size);
        foreach ($dirs_files as $key => $value) {
            $path = '';
            if (is_dir($value)) {
                $type = 'dir';
                $path = str_replace($default_folder, '', $value);
                $url = $current_url . '&dir='.urlencode($path);
            } else {
                $type = 'file';
                $path = str_replace($default_folder, '', $value);
                //$url = Config('app.url') . '/storage/images/' . $path;
                $url = '/storage/images/' . $path;
            }
            $temp_array = explode('/', $value);
            $name = $temp_array[(count($temp_array) - 1)];
            if (preg_match_all("/\[aduty([\d]+)aduty\]/", $name, $array)) $name = str_replace($array[0], '', $name);
            $data['items'][] = [
                'path' => $path,
                'url' => $url,
                'name' => $name,
                'type' => $type
            ];
        }

        $pagination = new PageRepository();
        $pagination->total = $dirs_files_total;
        $pagination->page = $page;
        $pagination->limit = $page_size;
        $pagination->url = setUrlParams(['page' => '{page}']);
        $data['pagination'] = $pagination;
        $data['prev'] = $prev;
        return $data;
    }
}
