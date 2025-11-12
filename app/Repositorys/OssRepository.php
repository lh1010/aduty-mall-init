<?php

/**
 * composer require aliyuncs/oss-sdk-php ~2.4
 * https://help.aliyun.com/document_detail/85580.html
 */

namespace App\Repositorys;

use OSS\OssClient;
use OSS\Core\OssException;

class OssRepository
{
    /**
     * aliyun oss upload file
     * $file_path example:'/images/demo.png'
     */
    public function uploadFile($file_path)
    {
        $accessKeyId = Config('common.oss.accessKeyId');
        $accessKeySecret = Config('common.oss.accessKeySecret');
        $endpoint = Config('common.oss.endpoint');
        $bucket = Config('common.oss.bucket');
        $object = substr($file_path, 1);
        $file_local_path = str_replace('\\', '/', public_path()  . $file_path);
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->uploadFile($bucket, $object, $file_local_path);
        } catch (OssException $e) {
            $log = 'type: uploadFile' . "\n";
            $log .= 'file_path: ' . $file_path . "\n";
            $log .= 'FAILED: ' . $e->getMessage();
            logWrite($log, 'oss');
        }
        return true;
    }

    /**
     * aliyun oss delete file
     * $file_path example:'/images/demo.png'
     */
    public function deleteObject($file_path)
    {
        $accessKeyId = Config('common.oss.accessKeyId');
        $accessKeySecret = Config('common.oss.accessKeySecret');
        $endpoint = Config('common.oss.endpoint');
        $bucket = Config('common.oss.bucket');
        $object = substr($file_path, 1);
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->deleteObject($bucket, $object);
        } catch (OssException $e) {
            $log = 'type: deleteObject' . "\n";
            $log .= 'file_path: ' . $file_path . "\n";
            $log .= 'FAILED: ' . $e->getMessage();
            print_r($log);
            logWrite($log, 'oss');
        }
    }
}
