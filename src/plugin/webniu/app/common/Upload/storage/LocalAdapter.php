<?php

namespace plugin\webniu\app\common\Upload\storage;

use support\exception\BusinessException;

class LocalAdapter extends Adapter
{
    //本地上传
    public static function uploadFile(array $options = [],$processFunction = null): array
    {
        $obj        = new self($options);
        $result     = [];
        $base_path  = base_path();
        $L          = DIRECTORY_SEPARATOR;   
        if($obj->config['diy_save_dir']){
            $base_path  = $base_path.$L.'public'.$obj->config['diy_save_dir'];
            $uri        = $obj->config['diy_save_dir'];
            $dirPath    = 'public'.$obj->config['diy_save_dir'];
            $basePath   = $base_path;
        }else{
            $base_path  = $base_path.$L.'plugin'.$L;
            $plugin_path= request()->plugin.$L.'public'.$L;
            $dirname    = $obj->config['dirname'].$L.date($obj->config['path_style']).$L.'files'.$L;
            $basePath   = $base_path.$plugin_path.$dirname;
            $uri        = $L.'app/'.request()->plugin.$L.$dirname;
            $dirPath    = 'plugin'.$L.$plugin_path.$dirname;
        } 
        if (!$obj->createDir($basePath)) {
            throw new StorageException('文件夹创建失败，请核查是否有对应权限。');
        }
         
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
                $image->save($savePath);
                $file_info['is_image']  = true;
            }else{
                $file_info['width']     = '0';
                $file_info['height']    = '0';
                $file_info['size']      = $file->getSize();
                $file_info['is_image']  = false;
            }
            $temp = [
                'key'           => $key,//指针
                'origin_name'   => $file->getUploadName(),//原名称
                'name'          => $uniqueId.$file->getUploadExtension(),//加密名称
                'save_dir'      => $dirPath.$saveFilename,//路径
                'save_path'     => $savePath,//保存路径
                'url'           => $uri.$saveFilename,//外链
                'unique_id'     => $uniqueId,
                'size'          => $file_info['size'],
                'mime_type'     => $file->getUploadMineType(),
                'extension'     => $file->getUploadExtension(),
                'image_width'   => $file_info['width'],
                'image_height'  => $file_info['height'],
                'no_archive'    => $obj->config['no_archive']
            ];
            if(!$file_info['is_image']){
                $file->move($savePath);
            }
            array_push($result, $temp);
        }
         
        return $result;
    }

     
}
