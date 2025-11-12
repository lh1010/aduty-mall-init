<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use DB;
use Image;
use App\Repositorys\OssRepository;

class UploadController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckAdminLogin');
    }

    public function index(Request $request)
    {
        $file = false;
        if ($request->hasFile('file')) $file = $request->file('file');
        if (!$file) return jsonFailed('不存在上传文件');
        $ext = $file->extension() ? $file->extension() : 'png';
        $file_name = md5(time() . rand(1000, 9999)) . '.' . $ext;
        $dir_path = Config('common.upload.path') . '/upload/' . date('Ymd') . '/';
        $file->move($dir_path, $file_name);
        $file_path = $dir_path . $file_name;
        $file_absolute_path = '/' .  $file_path;
        $file_url = fileView($file_absolute_path);

        app(OssRepository::class)->uploadFile($file_absolute_path);
        //DB::table('image')->insert(['path' => $file_absolute_path]);
        return jsonSuccess(['path' => $file_absolute_path, 'url' => $file_url]);
    }

    public function editormd(Request $request)
    {
        $file = false;
        if ($request->hasFile('editormd-image-file')) $file = $request->file('editormd-image-file');
        if (!$file) return jsonFailed('不存在上传文件');
        $ext = $file->extension() ? $file->extension() : 'png';
        $file_name = md5(time() . rand(1000, 9999)) . '.' . $ext;
        $dir_path = Config('common.upload.path') . '/upload/' . date('Ymd') . '/';
        $file->move($dir_path, $file_name);
        $file_path = $dir_path . $file_name;
        $file_absolute_path = '/' .  $file_path;
        $file_url = fileView($file_absolute_path);

        app(OssRepository::class)->uploadFile($file_absolute_path);
        //DB::table('image')->insert(['path' => $file_absolute_path]);
        return response()->json(['success' => 1, 'url' => $file_absolute_path, 'message' => '']);
    }
}
