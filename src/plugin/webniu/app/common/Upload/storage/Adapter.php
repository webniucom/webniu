<?php

namespace plugin\webniu\app\common\Upload\storage;
 
use Webman\Http\UploadFile;
use support\exception\BusinessException;

class Adapter
{
     
    protected $model = null;

    /**
     * @var bool
     */
    public $_isFileUpload;

    /**
     * @var string
     */
    public $dirSeparator = DIRECTORY_SEPARATOR;

    /**
     * 文件存储对象
     */
    protected $files;

    /**
     * 被允许的文件类型列表.
     */
    protected $includes;

    /**
     * 不被允许的文件类型列表.
     */
    protected $excludes;

    /**
     * 单个文件的最大字节数.
     */
    protected $singleLimit;

    /**
     * 多个文件的最大数量.
     */
    protected $totalLimit;

    /**
     * 文件上传的最大数量.
     */
    protected $nums;

    /**
     * 当前存储配置.
     *
     * @var array
     */
    protected $config;

    
    protected $algo = 'md5';

    public function __construct(array $config = [])
    {
        //初始化数据
        $this->files        = request()->file();
        $this->includes     = explode(",",$config['include']);
        $this->excludes     = explode(",",$config['exclude']);
        $this->singleLimit  = $config['single_limit']*1024*1024;
        $this->totalLimit   = $config['total_limit']*1024*1024;
        $this->nums         = $config['nums'];
        $this->config       = $config;
        $this->config['origin_name']    = isset($config['origin_name'])?$config['origin_name']:false; 
        $this->config['diy_save_dir']   = isset($config['diy_save_dir'])?$config['diy_save_dir']:false; 
        $this->config['no_archive']     = isset($config['no_archive'])?$config['no_archive']:true; 
        $this->verify();
    }
     
    /**
     * @desc: 文件验证
     */
    protected function verify()
    {
        if (!$this->files) {
            throw new BusinessException('未找到符合条件的文件资源');
        }
        foreach ($this->files as $file) {
            if (!$file->isValid()) {
                throw new BusinessException('未选择文件或者无效的文件');
            }
        }
        $this->allowedFile();
        $this->allowedFileSize();
    }

    /**
     * @desc: 获取文件大小
     */
    protected function getSize(UploadFile $file): int
    {
        return $file->getSize();
    }

    /**
     * @desc: 允许上传文件
     */
    protected function allowedFile(): bool
    {
        if ((!empty($this->includes) && !empty($this->excludes)) || !empty($this->includes)) {
            foreach ($this->files as $file) {
                $fileName = $file->getUploadName();
                if (!strpos($fileName, '.') || !in_array(substr($fileName, strripos($fileName, '.') + 1), $this->includes)) {
                    throw new BusinessException($file->getUploadName().'，文件扩展名不合法');
                }
            }
        } elseif (!empty($this->excludes) && empty($this->includes)) {
            foreach ($this->files as $file) {
                $fileName = $file->getUploadName();
                if (!strpos($fileName, '.') || in_array(substr($fileName, strripos($fileName, '.') + 1), $this->excludes)) {
                    throw new BusinessException($file->getUploadName().'，文件扩展名不合法');
                }
            }
        }

        return true;
    }

    /**
     * @desc: 允许上传文件大小
     */
    protected function allowedFileSize()
    {
        $fileCount = count($this->files);
        if ($fileCount > $this->config['nums']) {
            throw new BusinessException('文件数量过多，超出系统文件数量限制');
        }
        $totalSize = 0;
        foreach ($this->files as $k => $file) {
            $fileSize = $this->getSize($this->files[$k]);
            if ($fileSize > $this->singleLimit) {
                throw new BusinessException($file->getUploadName().'，单文件大小已超出系统限制：'.$this->singleLimit);
            }
            $totalSize += $fileSize;
        }
        if ($totalSize > $this->totalLimit) {
            throw new BusinessException('总文件大小已超出系统最大限制：'.$this->totalLimit);
        }
    }

    /**
     * @desc: createDir
     */
    protected function createDir(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        $parent = dirname($path);
        if (!is_dir($parent)) {
            if (!$this->createDir($parent)) {
                return false;
            }
        }

        return mkdir($path);
    }
 
}