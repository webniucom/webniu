<?php

namespace plugin\webniu\app\Bootstrap;

use Webman\Bootstrap;
use Workerman\Protocols\Http;
use Workerman\Worker;

/**
 * 修正 Windows 下 workerman 上传临时目录
 *
 * php.ini 的 upload_tmp_dir=c:/wamp64/tmp 在常驻 worker 进程内
 * 执行 tempnam() 时会触发 "file created in the system's temporary
 * directory" 警告（webman 将警告转为异常导致上传失败）。
 * 这里把上传临时目录显式指向项目 runtime 下可写目录。
 */
class UploadTmpDir implements Bootstrap
{
    /**
     * @param Worker|null $worker
     * @return void
     */
    public static function start(?Worker $worker)
    {
        $dir = runtime_path('upload_temp');
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            Http::uploadTmpDir($dir);
        }
    }
}
