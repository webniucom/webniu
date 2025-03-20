<?php

namespace plugin\webniu\app\common\Upload;

use support\exception\BusinessException;
use plugin\webniu\app\model\Upload as Mupload; 

class Upload
{

    /**
     * 上传文件入口
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public static function uploadFile(array $options,$processFunction = null)
    {
        if(empty($options)){
            throw new BusinessException('Config不合规');
        } 
        $options['Local']   = [];
        $storage_type       = ['Local','Ftp','Qiniu','Oss','Cos']; 
        $storage_type       = $storage_type[$options['storage_type']];
        $atta               = $options; 
        $atta['adapter']    = '\\plugin\\webniu\\app\\common\\Upload\\storage\\'.$storage_type.'Adapter';
        $atta['config']     = $options[$storage_type];
        return self::insert($atta['adapter']::uploadFile($atta,$processFunction));
    }
    
    /**
     * 执行统计
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public static function insert($data){
        $obj            = new Mupload;
        $obj->admin_id  = admin_id();
        $obj->category  = 0;
        foreach($data as $k=>$v){
            $obj->name          =   $v['name'];
            $obj->url           =   $v['url'];
            $obj->origin_name   =   $v['origin_name'];
            $obj->unique_id     =   $v['unique_id'];
            $obj->save_path     =   $v['save_path'];
            $obj->file_size     =   $v['size'];
            $obj->mime_type     =   $v['mime_type'];
            $obj->image_width   =   $v['image_width'];
            $obj->image_height  =   $v['image_height'];
            $obj->ext           =   $v['extension'];
            if($v['no_archive']){
                $obj->save();
            }
        }
        return $data; 
    }
    
};