<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use Image;
use App\Repositorys\OssRepository;

class UploadController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin');
    }

    public function index(Request $request)
    {
        if (!$request->hasFile('file')) return jsonFailed('不存在上传文件');
        $source = $request->input('source', '');
        $type = $request->input('type', 'image');
        $loginUser = getLoginUser();
        $file = $request->file('file');

        // 文件大小
        if (Config('common.upload.max_size') > 0) {
            $fileSize  = formatFileSize($file->getSize());
            if ($fileSize >= (Config('common.upload.max_size') * 1024)) {
                return jsonFailed('文件大小不能超过' . Config('common.upload.max_size') . 'M');
            }
        }

        $original_name = $file->getClientOriginalName();
        if ($request->original_name) $original_name = $request->original_name;

        // 文件类型
        $ext = $file->extension() ? $file->extension() : null;
        if (!$ext) {
            $array = explode('.', $original_name);
            $ext = $array[count($array) - 1];
        }
        $ext = $ext ? $ext : 'png';
        if (!empty(Config('common.upload.file_types'))) {
            if (!in_array($ext, Config('common.upload.file_types'))) return jsonFailed('不支持上传的文件类型');
        }

        $file_name = md5(time() . rand(1000, 9999)) . '.' . $ext;
        //$file_name = '[aduty]' . $original_name . '[aduty]' . md5(time() . rand(1000, 9999)) . '.' . $ext;
        $dir_path = Config('common.upload.path') . '/upload/' . date('Ymd') . '/';
        $file->move($dir_path, $file_name);
        $file_path = $dir_path . $file_name;
        $file_absolute_path = '/' .  $file_path;
        $file_url = fileView($file_absolute_path);

        //app(OssRepository::class)->uploadFile($file_absolute_path);
        return jsonSuccess(['path' => $file_absolute_path, 'url' => $file_url, 'original_name' => $original_name]);
    }
}
