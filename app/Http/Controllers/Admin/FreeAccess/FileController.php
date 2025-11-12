<?php

namespace App\Http\Controllers\Admin\FreeAccess;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use App\Repositorys\Admin\FileRepository;
use Illuminate\Support\Facades\Storage;
use DB;
use URL;

class FileController extends BaseController
{
    private $upload_path = 'lh101';

    public function manager(Request $request)
    {
        $params = $request->all();
        $folder = $request->input('folder', '');
        $query = DB::table('file');
        $query->where('file.parent_path', $folder);
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('file.name', 'like', '%' . $params['k'] . '%');
        }
        if (isset($params['order']) && !empty($params['order'])) {
            if ($params['order'] == '按名称') {
                $query->orderBy('file.name', 'asc');
            }
            if ($params['order'] == '按时间') {
                $query->orderBy('file.created_at', 'desc');
            }
        }
        $query->orderBy('file.created_at', 'desc');
        $res = $query->paginate(14);

        $current_url = URL::current() . '?token=' . time();
        foreach ($res as $key => $value) {
            if ($value->type == 'folder') {
                $url = $current_url . '&folder=' . urlencode($value->path);
            } else {
                $url = '/' . $value->path;
            }
            $res[$key]->url = $url;
        }

        // 上一页
        $prev = '';
        if (!empty($folder)) {
            $prev = 'javascript:history.go(-1)';
        }

        return view('admin.freeAccess.file.manager', compact('res', 'prev'));
    }

    public function uploads(Request $request)
    {
        try {
            $folder = $request->input('folder', '');
            $path =  !empty($folder) ? $folder : $this->upload_path;
            $files = $request->file('files');
            foreach ($files as $file) {
                $original_name = $file->getClientOriginalName();
                $file_name = substr($original_name, 0, strripos($original_name, '.')) . '[aduty' . time() . 'aduty]' . strrchr($original_name, '.');
                $file->move($path, $file_name);
                DB::table('file')->insert([
                    'type' => 'file',
                    'name' => $original_name,
                    'full_name' => $file_name,
                    'path' => $path . '/' . $file_name,
                    'parent_path' => $folder,
                ]);
            }
            return jsonSuccess();
        } catch (\Throwable $th) {
            return jsonFailed('上传失败' . $th->getMessage());
        }
    }

    /**
     * 创建文件夹
     * @param String $new_folder 新文件夹
     * @param String $folder 上级文件夹
     */
    public function createFolder(Request $request)
    {
        $new_folder = $request->input('new_folder', '');
        $folder = $request->input('folder', '');
        if (empty($new_folder)) return jsonFailed('文件夹名不能为空');
        $path =  !empty($folder) ? $folder : $this->upload_path;
        $path .= '/' . $new_folder;
        if (is_dir($path)) return jsonFailed('文件夹已存在');
        try {
            mkdir($path, 0777, true);
            DB::table('file')->insert([
                'type' => 'folder',
                'name' => $new_folder,
                'full_name' => $new_folder,
                'path' => $path,
                'parent_path' => $folder,
            ]);
            return jsonSuccess();
        } catch (\Throwable $th) {
            return jsonFailed('创建失败，请检查是否存在特殊字符' . $th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            foreach ($request->filepaths as $key => $value) {
                $path = $value['path'];

                deleteFile($path);
                DB::table('file')->where('path', $path)->delete();
            }
            return jsonSuccess();
        } catch (\Throwable $th) {
            return jsonFailed($th->getMessage());
        }
    }
}
