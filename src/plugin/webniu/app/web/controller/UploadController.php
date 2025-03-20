<?php

namespace plugin\webniu\app\web\controller;

use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use plugin\webniu\app\model\Upload as model_Upload;
use plugin\webniu\app\common\Upload\Upload; 
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 附件管理
 */
class UploadController extends Crud
{
    /**
     * @var Upload
     */
    protected $model = null;

    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new model_Upload;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('upload/index');
    }

    /**
     * 浏览附件
     * @return Response
     * @throws Throwable
     */
    public function attachment(): Response
    {
        return raw_view('upload/attachment');
    }

    /**
     * 查询附件
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['ext']) && is_string($where['ext'])) {
            $where['ext'] = ['in', explode(',', $where['ext'])];
        }
        if (!empty($where['name']) && is_string($where['name'])) {
            $where['name'] = ['like', "%{$where['name']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        $query = $query->orderBy('id','desc');
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 更新附件
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return raw_view('upload/update');
        }
        return parent::update($request);
    }

    /**
     * 添加附件
     * @param Request $request
     * @return Response
     * @throws Exception|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return raw_view('upload/insert');
        }
        $config     = options(['pic','atta']); 
        $pic        = $config['pic']; 
        $atta       = $config['atta'];
        [$Fileres]  = Upload::uploadFile($atta,function($image) use ($pic){
            if($pic['pic_thumb_type']){//图片压缩
                $width  = $image->width();
                $height = $image->height();
                if($width >= $pic['pic_thumb_width']){
                    $percent    = $pic['pic_thumb_percent'];
                    $newWidth   = $width * $percent;
                    $newHeight  = $height * $percent;
                    $image->resize($newWidth, $newHeight);
                };
            }
            if($pic['pic_mark_type']){//增加水印
                if($pic['pic_mark_style']=='0'){
                    $image_with  = $image->width();
                    $image_height = $image->height(); 
                    $text       = $pic['pic_thumb_text']; // 水印文字内容
                    $fontPath   = base_path().'/plugin/webniu/public/component/layui/font/font.ttf'; // 字体文件路径
                    $fontSize   = $pic['pic_thumb_size']; // 字体大小
                    $position   = 'bottom';
                    $color      = $pic['pic_thumb_color'];
                    $image->text($text,$image_with-120,$image_height-35, function ($font) use ($fontPath, $fontSize,$color,$position) {
                        $font->file($fontPath);
                        $font->size($fontSize);
                        $font->valign($position);
                        $font->color($color);
                    });
                }else{
                    $position   = $pic['pic_mark_weizhi'];
                    $shuiyin    = $pic['pic_thumb_img'];//需要本地资源
                    $shuiyin    = str_replace('/app/webniu/',base_path().'/plugin/webniu/public/',$pic['pic_thumb_img']);
                    $image->insert($shuiyin,$position,10,10);
                } 
            }
            return $image;
        });  
        return $this->json(0, '上传成功', [
            'url'   => $Fileres['url'],
            'name'  => $Fileres['origin_name'],
            'size'  => $Fileres['size'],
        ]);
    }

    /**
     * 上传文件
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function file(Request $request): Response
    {
        $config             = options(['atta']);
        [$Fileres]            = Upload::uploadFile($config['atta']);
         
        return $this->json(0, '上传成功', [
            'url'   => $Fileres['url'],
            'name'  => $Fileres['origin_name'],
            'size'  => $Fileres['size'],
        ]);
    }

    /**
     * 上传图片
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function image(Request $request): Response
    {
        $config     = options(['atta']);
        [$Fileres]  = Upload::uploadFile($config['atta'],function($image){
            //图片压缩
            $max_height = 1170;
            $max_width  = 1170;
            $width      = $image->width();
            $height     = $image->height();
            $ratio      = 1;
            if($height > $max_height || $width > $max_width) {
                $ratio  = $width > $height ? $max_width / $width : $max_height / $height;
            }
            return $image->resize($width*$ratio, $height*$ratio);
        });
        return json( [
            'code'  => 0,
            'msg'   => '上传成功',
            'data'  => [
                'url'   => $Fileres['url'],
                'name'  => $Fileres['origin_name'],
                'size'  => $Fileres['size'],
            ]
        ]);
    }

    /**
     * 上传头像
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function avatar(Request $request): Response
    {
        //加载配置
        $config             = options(['atta']);
        [$Fileres]            = Upload::uploadFile($config['atta']); 
        return json( [
            'code'  => 0,
            'msg'   => '上传成功',
            'data'  => [
                'url'   => $Fileres['url'],
                'name'  => $Fileres['origin_name'],
                'size'  => $Fileres['size'],
            ]
        ]);
    }

    /**
     * 删除附件
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $primary_key = $this->model->getKeyName();
        $files = $this->model->whereIn($primary_key, $ids)->get()->toArray();
        $file_list = array_map(function ($item) {
            $path = $item['url'];
            if (preg_match("#^/app/webniu#", $path)) {
                $admin_public_path = config('plugin.webniu.app.public_path') ?: base_path() . "/plugin/webniu/public";
                return $admin_public_path . str_replace("/app/webniu", "", $item['url']);
            }
            return null;
        }, $files);
        $file_list = array_filter($file_list, function ($item) {
            return !empty($item);
        });
        $result = parent::delete($request);
        if (($res = json_decode($result->rawBody())) && $res->code === 0) {
            foreach ($file_list as $file) {
                @unlink($file);
            }
        }
        return $result;
    }

    /**
     * 获取上传数据
     * @param Request $request
     * @param $relative_dir
     * @return array
     * @throws BusinessException|\Random\RandomException
     */
    protected function base(Request $request, $relative_dir): array
    {
        $relative_dir = ltrim($relative_dir, '\\/');
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            throw new BusinessException('未找到上传文件', 400);
        }

        $admin_public_path = rtrim(config('plugin.webniu.app.public_path', ''), '\\/');
        $base_dir = $admin_public_path ? $admin_public_path . DIRECTORY_SEPARATOR : base_path() . '/plugin/webniu/public/';
        $full_dir = $base_dir . $relative_dir;
        if (!is_dir($full_dir)) {
            mkdir($full_dir, 0777, true);
        }

        $ext = $file->getUploadExtension() ?: null;
        $mime_type = $file->getUploadMimeType();
        $file_name = $file->getUploadName();
        $file_size = $file->getSize();

        if (!$ext && $file_name === 'blob') {
            [$___image, $ext] = explode('/', $mime_type);
            unset($___image);
        }

        $ext = strtolower($ext);
        $ext_forbidden_map = ['php', 'php3', 'php5', 'css', 'js', 'html', 'htm', 'asp', 'jsp'];
        if (in_array($ext, $ext_forbidden_map)) {
            throw new BusinessException('不支持该格式的文件上传', 400);
        }

        $relative_path = $relative_dir . '/' . bin2hex(pack('Nn', time(), random_int(1, 65535))) . ".$ext";
        $full_path = $base_dir . $relative_path;
        $file->move($full_path);
        $image_with = $image_height = 0;
        if ($img_info = getimagesize($full_path)) {
            [$image_with, $image_height] = $img_info;
            $mime_type = $img_info['mime'];
        }
        return [
            'url' => "/app/webniu/$relative_path",
            'name' => $file_name,
            'realpath' => $full_path,
            'size' => $file_size,
            'mime_type' => $mime_type,
            'image_with' => $image_with,
            'image_height' => $image_height,
            'ext' => $ext,
        ];
    }

}
