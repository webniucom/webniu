<?php

namespace plugin\webniu\app\admin\controller;

use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use plugin\webniu\app\model\Upload;
use plugin\webniu\app\common\Upload as UploadCommon;
use plugin\webniu\app\model\UploadsGroup;
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
        $this->model = new Upload;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return $this->json(200, '无资源');
    }

    /**
     * 浏览附件
     * @return Response
     * @throws Throwable
     */
    public function attachment(): Response
    {
        return $this->json(200, '无资源');
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
        $category = $request->post('category');
        $ids = $this->deleteInput($request);
        Upload::whereIn('id', $ids)->update(['category' => $category]);
        return $this->json(200);
    }

    /**
     * 添加附件
     * @param Request $request
     * @return Response
     * @throws Exception|Throwable
     */
    public function insert(Request $request): Response
    {
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            return $this->json(400, '未找到文件');
        }
        $data = $this->base($request, '/upload/files/' . date('Ymd'), true);
        $upload = new Upload;
        $upload->admin_id = admin_id();
        $upload->name = $data['name'];
        $upload->url          = $data['url'] ?? '';
        $upload->storage      = 'local';
        $upload->file_size    = $data['size'] ?? 0;
        $upload->mime_type    = $data['mime_type'] ?? '';
        $upload->image_width  = $data['image_width'] ?? ($data['image_with'] ?? 0);
        $upload->image_height = $data['image_height'] ?? 0;
        $upload->ext          = $data['ext'] ?? '';
        $upload->category = $request->post('category');
        $upload->save();
        return $this->json(200, '上传成功', [
            'url' => $data['url'],
            'mime_type' => $data['mime_type'],
            'name' => $data['name'],
            'size' => $data['size'],
        ]);
    }

    /**
     * 分组查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupselect(Request $request): Response
    {
        $this->model = new UploadsGroup;
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        $query = $query->orderBy('sort', 'asc');
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 通用格式化
     * @param $items
     * @param $total
     * @return Response
     */
    protected function formatNormal($items, $total): Response
    {
        return json(['code' => 200, 'msg' => 'success', 'data' => [
            'records' => $items,
            'total' => $total
        ]]);
    }

    /**
     * 分组插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupinsert(Request $request): Response
    {
        $this->model = new UploadsGroup;
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            return $this->json(200, '插入成功', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 分组更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupupdate(Request $request): Response
    {
        $this->model = new UploadsGroup;
        if ($request->method() === 'POST') {
            if ($dragsort = $request->post('dragsort', false)) {
                if ($dragsort) {
                    foreach ($dragsort as $item) {
                        $this->model->where('id', $item['id'])->update(['sort' => $item['sort']]);
                    }
                }
                return $this->json(200, '排序成功');
            };
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            return $this->json(200, '更新成功');
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 分组删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupdelete(Request $request): Response
    {
        $this->model = new UploadsGroup;
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        return $this->json(200, '删除成功');
    }

    /**
     * 上传文件
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function file(Request $request): Response
    {
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            return $this->json(1, '未找到文件');
        }
        $img_exts = [
            'jpg',
            'jpeg',
            'png',
            'gif'
        ];
        if (in_array($file->getUploadExtension(), $img_exts)) {
            return $this->image($request);
        }
        $data = $this->base($request, '/upload/files/' . date('Ymd'));
        return $this->json(200, '上传成功', [
            'url' => $data['url'],
            'name' => $data['name'],
            'size' => $data['size'],
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
        $data = $this->base($request, '/upload/img/' . date('Ymd'));
        $realpath = $data['realpath'];
        try {
            $img = Image::make($realpath);
            $max_height = 1170;
            $max_width = 1170;
            $width = $img->width();
            $height = $img->height();
            $ratio = 1;
            if ($height > $max_height || $width > $max_width) {
                $ratio = $width > $height ? $max_width / $width : $max_height / $height;
            }
            $img->resize($width * $ratio, $height * $ratio)->save($realpath);
        } catch (Exception $e) {
            unlink($realpath);
            return json([
                'code' => 500,
                'msg' => '处理图片发生错误'
            ]);
        }
        return json([
            'code' => 200,
            'msg' => '上传成功',
            'data' => [
                'url' => $data['url'],
                'name' => $data['name'],
                'size' => $data['size'],
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
        $file = current($request->file());
        if ($file && $file->isValid()) {
            $ext = strtolower($file->getUploadExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                return json(['code' => 2, 'msg' => '仅支持 jpg jpeg gif png格式']);
            }
            $image = Image::make($file);
            $width = $image->width();
            $height = $image->height();
            $size = min($width, $height);
            $relative_path = 'upload/avatar/' . date('Ym');
            $real_path = base_path() . "/plugin/admin/public/$relative_path";
            if (!is_dir($real_path)) {
                mkdir($real_path, 0777, true);
            }
            $name = bin2hex(pack('Nn', time(), random_int(1, 65535)));
            $ext = $file->getUploadExtension();

            $image->crop($size, $size)->resize(300, 300);
            $path = base_path() . "/plugin/admin/public/$relative_path/$name.lg.$ext";
            $image->save($path);

            $image->resize(120, 120);
            $path = base_path() . "/plugin/admin/public/$relative_path/$name.md.$ext";
            $image->save($path);

            $image->resize(60, 60);
            $path = base_path() . "/plugin/admin/public/$relative_path/$name.$ext";
            $image->save($path);

            $image->resize(30, 30);
            $path = base_path() . "/plugin/admin/public/$relative_path/$name.sm.$ext";
            $image->save($path);

            return json([
                'code' => 200,
                'msg' => '上传成功',
                'data' => [
                    'url' => "/app/admin/$relative_path/$name.md.$ext"
                ]
            ]);
        }
        return json(['code' => 400, 'msg' => 'file not found']);
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
        if (($res = json_decode($result->rawBody())) && $res->code === 200) {
            foreach ($file_list as $file) {
                echo $file . PHP_EOL;
                @unlink($file);
            }
        }
        return $result;
    }

    protected function base(Request $request, $relative_dir ,$edit = false,): array
    {
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            throw new BusinessException('未找到上传文件', 400);
        }

        $ext = strtolower($file->getUploadExtension() ?: '');
        $is_image = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        if ($is_image && $edit) {
            $tmp_dir = sys_get_temp_dir() . '/webniu_upload_tmp';
            if (!is_dir($tmp_dir)) {
                mkdir($tmp_dir, 0777, true);
            }

            $tmp_path = $tmp_dir . '/' . bin2hex(random_bytes(16)) . '.' . $ext;
            $file->move($tmp_path);

            try {
                $this->processImage($tmp_path);
                $result = UploadCommon::saveByPath($tmp_path, $relative_dir);
                @unlink($tmp_path);
                return $result;
            } catch (Exception $e) {
                @unlink($tmp_path);
                throw $e;
            }
        }

        return UploadCommon::save($file, $relative_dir);
    }

    /**
     * 处理图片（压缩 + 水印）
     * @param string $image_path
     * @return void
     * @throws Exception
     */
    protected function processImage(string $image_path): void
    {
        $waterMark = options('waterMark')['waterMark'] ?? [];
        $img = Image::make($image_path);
        if (!empty($waterMark['pic_thumb_type'])) {
            $maxWidth = (int)($waterMark['pic_thumb_width'] ?? 800);
            $percent = (float)($waterMark['pic_thumb_percent'] ?? 0.5);
            $width = $img->width();
            $height = $img->height();

            if ($width > $maxWidth) {
                $ratio = $maxWidth / $width;
                $img->resize($width * $ratio, $height * $ratio, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } elseif ($percent > 0 && $percent < 1) {
                $img->resize($width * $percent, $height * $percent, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
        }

        if (!empty($waterMark['pic_mark_type'])) {
            $style = $waterMark['pic_mark_style'] ?? '1';
            $position = $this->convertPosition($waterMark['pic_mark_weizhi'] ?? 'middle-right');

            if ($style == '0') {
                $text = $waterMark['pic_thumb_text'] ?? '';
                if (!empty($text)) {
                    $size = (int)($waterMark['pic_thumb_size'] ?? 24);
                    $color = $waterMark['pic_thumb_color'] ?? '#000000';

                    $font_path = base_path() . '/plugin/webniu/public/font/iconfont.ttf';

                    $x = 20;
                    $y = 20;
                    $align = 'right';
                    $valign = 'bottom';

                    switch ($position) {
                        case 'top-left':
                            $align = 'left';
                            $valign = 'top';
                            break;
                        case 'top-right':
                        case 'top':
                            $x = (int)($img->width() - 20);
                            $y = 20;
                            $align = 'right';
                            $valign = 'top';
                            break;
                        case 'left':
                            $align = 'left';
                            $valign = 'center';
                            $y = (int)($img->height() / 2);
                            break;
                        case 'right':
                            $x = (int)($img->width() - 20);
                            $align = 'right';
                            $valign = 'center';
                            $y = (int)($img->height() / 2);
                            break;
                        case 'center':
                            $x = (int)($img->width() / 2);
                            $y = (int)($img->height() / 2);
                            $align = 'center';
                            $valign = 'center';
                            break;
                        case 'bottom-left':
                            $y = (int)($img->height() - 20);
                            $align = 'left';
                            $valign = 'bottom';
                            break;
                        case 'bottom':
                            $x = (int)($img->width() / 2);
                            $y = (int)($img->height() - 20);
                            $align = 'center';
                            $valign = 'bottom';
                            break;
                        case 'bottom-right':
                        default:
                            $x = (int)($img->width() - 20);
                            $y = (int)($img->height() - 20);
                            $align = 'right';
                            $valign = 'bottom';
                            break;
                    }

                    $img->text($text, $x, $y, function ($font) use ($size, $color, $font_path, $align, $valign) {
                        $font->size($size);
                        $font->color($color);
                        $font->align($align);
                        $font->valign($valign);
                        // if ($font_path && is_file($font_path)) {
                        //     $font->file($font_path);
                        // }
                    });
                }
            } else {
                $markImg = $waterMark['pic_thumb_img'] ?? '';
                if (!empty($markImg)) {
                    $markPath = str_replace('/app/webniu/', base_path() . '/plugin/webniu/public/', $markImg);
                    if (is_file($markPath)) {
                        $img->insert($markPath, $position, 20, 20);
                    }
                }
            }
        }

        $img->save($image_path);
    }

    /**
     * 转换位置配置为 Intervention Image 位置常量
     * @param string $position
     * @return string
     */
    protected function convertPosition(string $position): string
    {
        $map = [
            'top-left'     => 'top-left',
            'top'          => 'top',
            'top-right'    => 'top-right',
            'left'         => 'left',
            'middle-right' => 'right',
            'right'        => 'right',
            'bottom-left'  => 'bottom-left',
            'bottom'       => 'bottom',
            'bottom-right' => 'bottom-right',
            'center'       => 'center',
        ];
        return $map[$position] ?? 'bottom-right';
    }

}