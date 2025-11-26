<?php

include '../../../../../../vendor/autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;

$config_common = include '../../../../../../config/readfile/common.php';

include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

$res = $up->getFileInfo();
if ($config_common) {
    $config_common = json_decode($config_common, 1);
    if ($config_common['oss']['status']) {
        // 上传文件路径
        $file_path = $res['url'];
        // 项目根目录 public目录 据实际情况可修改
        $public_path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));

        $accessKeyId = $config_common['oss']['accessKeyId'];
        $accessKeySecret = $config_common['oss']['accessKeySecret'];
        $endpoint = $config_common['oss']['endpoint'];
        $bucket = $config_common['oss']['bucket'];
        $object = substr($file_path, 1);

        $file_local_path = str_replace('\\', '/', $public_path  . $file_path);

        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $object, $file_local_path);
        } catch (OssException $e) {
            // 待写错误日志...
            var_dump($e->getMessage());
            exit();
        }
    }
}

/* 返回数据 */
return json_encode($res);
