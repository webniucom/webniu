<?php

namespace plugin\webniu\app\common\Upload\storage;

use plugin\webniu\app\model\Ftp;
use support\exception\BusinessException;

class FtpAdapter extends Adapter
{
    
    //FTP 上传接口
    public static function uploadFile(array $options = [],$processFunction = null): array
    {
        $obj        = new self($options);
        $config     = $options['config'];
        $result     = [];
        $L          = DIRECTORY_SEPARATOR; 
        $base_path  = base_path();
         
        $base_path  = $base_path.$L.'plugin'.$L;
        $plugin_path= request()->plugin.$L.'public'.$L;
        $dirname    = $obj->config['dirname'].$L.date($obj->config['path_style']).$L.'files'.$L;
        $basePath   = $base_path.$plugin_path.$dirname;
        $uri        = $L.'app/'.request()->plugin.$L.$dirname;
        $dirPath    = 'plugin'.$L.$plugin_path.$dirname;
         
        if (!$obj->createDir($basePath)) {
            throw new BusinessException('文件夹创建失败，请核查是否有对应权限。');
        }
        try { 
            foreach ($obj->files as $key => $file) {
                $uniqueId       = hash_file($obj->algo, $file->getPathname()); //加密后的名称
                $saveFilename   = $obj->config['origin_name'] ? $file->getUploadName() : $uniqueId.'.'.$file->getUploadExtension();
                $savePath       = $basePath.$saveFilename;
                $file_info  = [];
                if (in_array($file->getUploadExtension(),['jpg','jpeg','png','gif'])) {
                    if (!class_exists(\Intervention\Image\ImageManagerStatic::class)) {
                        throw new \Exception('图片处理器未安装');
                    }
                    $image = \Intervention\Image\ImageManagerStatic::make($file);
                    if(is_callable($processFunction)){ 
                        $image = $processFunction($image);
                    }
                    $file_info['width'] = $image->width();
                    $file_info['height']= $image->height();
                    $file_info['size']  = $image->filesize(); 
                    $base_dir           = $savePath;
                    $image->save($base_dir);
                    $file_info['is_image']  = true;
                }else{
                    $file_info['width']     = '0';
                    $file_info['height']    = '0';
                    $file_info['size']      = $file->getSize();
                    $base_dir               = $file->getPathname();
                    $file_info['is_image']  = false;
                }
                $temp = [
                    'key'           => $key,//指针
                    'origin_name'   => $file->getUploadName(),//原名称
                    'name'          => $uniqueId.$file->getUploadExtension(),//加密名称
                    'save_dir'      => '',//路径
                    'save_path'     => '',//保存路径
                    'url'           => $config['domain'].$dirname.$saveFilename,//外链
                    'unique_id'     => $uniqueId,
                    'size'          => $file_info['size'],
                    'mime_type'     => $file->getUploadMineType(),
                    'extension'     => $file->getUploadExtension(),
                    'image_width'   => $file_info['width'],
                    'image_height'  => $file_info['height'],
                    'no_archive'    => $obj->config['no_archive']
                ];
                 
                //执行上传云端
                $ftp    = new Ftp();
                $ftp->connect($config['ip'],$config['port']);
                $ftp->login($config['username'],$config['password']);
                if($config['pasv']=='1'){
                    $ftp->pasv(true);
                }else{
                    $ftp->pasv(false);
                }
                $ftp->makeDir($dirname);
                $ftp->upload($dirname.$saveFilename,$base_dir);
                $ftp->disconnect();
               
                if($file_info['is_image']){
                    unlink($savePath);
                }
                 
                array_push($result, $temp);
            }
        } catch (Throwable $exception) {
            throw new BusinessException($exception->getMessage());
        }

        return $result;
    }

     
}
