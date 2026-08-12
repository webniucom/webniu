<?php

namespace plugin\webniu\app\common;

use GuzzleHttp\Client;
use support\exception\BusinessException;
use Webman\Http\UploadFile;

/**
 * 统一上传类
 * 支持 Local(本地)、Ftp、Qiniu(七牛)、Oss(阿里云)、Cos(腾讯云) 五种存储方式
 * 存储类型由 attachMent.storage_type 决定：0=Local 1=Ftp 2=Qiniu 3=Oss 4=Cos
 */
class Upload
{
    /** 存储类型：本地 */
    const STORAGE_LOCAL = 0;
    /** 存储类型：FTP */
    const STORAGE_FTP   = 1;
    /** 存储类型：七牛云 */
    const STORAGE_QINIU = 2;
    /** 存储类型：阿里云OSS */
    const STORAGE_OSS   = 3;
    /** 存储类型：腾讯云COS */
    const STORAGE_COS   = 4;

    /** 存储类型到名称的映射 */
    const STORAGE_MAP = [
        self::STORAGE_LOCAL => 'Local',
        self::STORAGE_FTP   => 'Ftp',
        self::STORAGE_QINIU => 'Qiniu',
        self::STORAGE_OSS   => 'Oss',
        self::STORAGE_COS   => 'Cos',
    ];

    /**
     * 保存上传文件（统一入口）
     * @param UploadFile $file 上传文件对象
     * @param string|null $relative_dir 相对目录（为空时按配置 dirname/path_style 自动生成）
     * @param string|null $filename 自定义文件名（不含扩展名，为空时自动生成）
     * @param array|null $customConfig 自定义配置（为空时读取 attachMent）
     * @return array ['url','name','realpath','size','mime_type','image_width','image_height','ext','storage_type']
     * @throws BusinessException
     */
    public static function save(UploadFile $file, ?string $relative_dir = null, ?string $filename = null, ?array $customConfig = null): array
    {
        if (!$file || !$file->isValid()) {
            throw new BusinessException('未找到上传文件', 400);
        }

        $config = self::getConfig($customConfig);
        // 校验文件
        self::validate($file, $config);
        $ext    = strtolower($file->getUploadExtension() ?: '');
        $name   = $file->getUploadName();
        $size   = $file->getSize();
        $mime   = $file->getUploadMimeType();

        // blob 类型自动推断扩展名
        if (!$ext && $name === 'blob') {
            [$___image, $ext] = explode('/', $mime);
            unset($___image);
            $ext = strtolower($ext);
        }

        // 生成相对路径
        $relative_dir = $relative_dir ?: '/' . $config['dirname'] . '/' . self::buildPathStyle($config['path_style']);
        $filename     = $filename ?: self::generateFilename($ext);
        $relative_path = trim($relative_dir, '\\/') . '/' . $filename;

        // 路由到对应驱动
        switch ((int)$config['storage_type']) {
            case self::STORAGE_FTP:
                $result = self::saveToFtp($file, $relative_path, $config);
                break;
            case self::STORAGE_QINIU:
                $result = self::saveToQiniu($file, $relative_path, $config);
                break;
            case self::STORAGE_OSS:
                $result = self::saveToOss($file, $relative_path, $config);
                break;
            case self::STORAGE_COS:
                $result = self::saveToCos($file, $relative_path, $config);
                break;
            case self::STORAGE_LOCAL:
            default:
                $result = self::saveToLocal($file, $relative_path, $config);
                break;
        }

        // 补充公共信息
        $result['name']         = $result['name'] ?? $name;
        $result['size']         = $result['size'] ?? $size;
        $result['mime_type']    = $result['mime_type'] ?? $mime;
        $result['ext']          = $result['ext'] ?? $ext;
        $result['storage_type'] = (int)$config['storage_type'];

        // 获取图片尺寸
        $dimensions = self::getImageSize($file);
        $result['image_width']  = $dimensions[0];
        $result['image_height'] = $dimensions[1];

        return $result;
    }

    /**
      * 通过文件路径保存文件（用于本地处理后上传到云存储）
     * @param string $file_path 本地文件路径
     * @param string|null $relative_dir 相对目录
     * @param array|null $customConfig 自定义配置
     * @return array
     * @throws BusinessException
     */
    public static function saveByPath(string $file_path, ?string $relative_dir = null, ?array $customConfig = null): array
    {
        if (!is_file($file_path)) {
            throw new BusinessException('文件不存在', 400);
        }

        $config = self::getConfig($customConfig);
        $ext    = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $name   = basename($file_path);
        $size   = filesize($file_path);
        $mime   = mime_content_type($file_path) ?: 'application/octet-stream';

        // 生成相对路径
        $relative_dir = $relative_dir ?: '/' . $config['dirname'] . '/' . self::buildPathStyle($config['path_style']);
        $filename       = self::generateFilename($ext);
        $relative_path  = trim($relative_dir, '\\/') . '/' . $filename;

        // 路由到对应驱动
        switch ((int)$config['storage_type']) {
            case self::STORAGE_FTP:
                $result = self::saveToFtp($file_path, $relative_path, $config);
                break;
            case self::STORAGE_QINIU:
                $result = self::saveToQiniu($file_path, $relative_path, $config);
                break;
            case self::STORAGE_OSS:
                $result = self::saveToOss($file_path, $relative_path, $config);
                break;
            case self::STORAGE_COS:
                $result = self::saveToCos($file_path, $relative_path, $config);
                break;
            case self::STORAGE_LOCAL:
            default:
                $result = self::saveToLocal($file_path, $relative_path, $config);
                break;
        }

        $result['name']         = $result['name'] ?? $name;
        $result['size']         = $result['size'] ?? $size;
        $result['mime_type']    = $result['mime_type'] ?? $mime;
        $result['ext']          = $result['ext'] ?? $ext;
        $result['storage_type'] = (int)$config['storage_type'];

        $dimensions = self::getImageSizeByPath($file_path);
        $result['image_width']  = $dimensions[0];
        $result['image_height'] = $dimensions[1];

        return $result;
    }

    /**
     * 通过文件路径获取图片尺寸
     * @param string $file_path
     * @return array [width, height]
     */
    protected static function getImageSizeByPath(string $file_path): array
    {
        $info = @getimagesize($file_path);
        if ($info) {
            return [$info[0], $info[1]];
        }
        return [0, 0];
    }

    /**
     * 删除文件（根据存储类型路由）
     * @param string $key 文件标识（本地为相对路径，云存储为 object key）
     * @param array|null $customConfig 自定义配置（为空时读取 attachMent）
     * @return bool
     */
    public static function delete(string $key, ?array $customConfig = null): bool
    {
        $config = self::getConfig($customConfig);
        switch ((int)$config['storage_type']) {
            case self::STORAGE_FTP:
                return self::deleteFromFtp($key, $config);
            case self::STORAGE_QINIU:
                return self::deleteFromQiniu($key, $config);
            case self::STORAGE_OSS:
                return self::deleteFromOss($key, $config);
            case self::STORAGE_COS:
                return self::deleteFromCos($key, $config);
            case self::STORAGE_LOCAL:
            default:
                return self::deleteFromLocal($key, $config);
        }
    }

    /**
     * 校验文件（扩展名、大小）
     * @param UploadFile $file
     * @param array|null $customConfig 自定义配置（为空时读取 attachMent）
     * @throws BusinessException
     */
    public static function validate(UploadFile $file, ?array $customConfig = null): void
    {
        $config = self::getConfig($customConfig);
        // 扩展名校验
        $ext = strtolower($file->getUploadExtension() ?: '');
        $exclude = self::parseList($config['exclude'] ?? '');
        $include = self::parseList($config['include'] ?? '');

        if ($ext && in_array($ext, $exclude)) {
            throw new BusinessException('不支持该格式的文件上传', 400);
        }
        if (!empty($include) && !in_array($ext, $include)) {
            throw new BusinessException('不支持该格式的文件上传', 400);
        }

        // 单文件大小校验（MB）
        $single_limit = (float)($config['single_limit'] ?? 0);
        if ($single_limit > 0) {
            $limit_bytes = $single_limit * 1024 * 1024;
            if ($file->getSize() > $limit_bytes) {
                throw new BusinessException("文件大小超过限制（{$single_limit}MB）", 400);
            }
        }
    }

    /**
     * 获取上传配置
     * @param array|null $customConfig 自定义配置（传入则直接使用，不查库）
     * @return array
     */
    public static function getConfig(?array $customConfig = null): array
    {
        // 如果外部传入了自定义配置，直接使用，不走数据库
        if ($customConfig !== null) {
            return self::fillDefaultConfig($customConfig);
        }

        $opts = options('attachMent');
        $config = $opts['attachMent'] ?? [];
        return self::fillDefaultConfig($config);
    }

    /**
     * 填充默认配置项
     * @param array $config
     * @return array
     */
    protected static function fillDefaultConfig(array $config): array
    {
        $config['storage_type'] = $config['storage_type'] ?? 0;
        $config['exclude']      = $config['exclude'] ?? 'php,php3,php5,css,js,html,htm,asp,jsp,exe';
        $config['include']      = $config['include'] ?? '';
        $config['dirname']      = $config['dirname'] ?? 'upload';
        $config['path_style']   = $config['path_style'] ?? 'Ymd';
        $config['single_limit'] = $config['single_limit'] ?? 0;
        $config['total_limit']  = $config['total_limit'] ?? 0;
        $config['nums']         = $config['nums'] ?? 0;
        // 默认关闭 SSL 验证（与 pear.config.json 默认值一致，避免 Windows 缺少 CA 证书报错）
        $config['verify_ssl']   = $config['verify_ssl'] ?? 0;
        // 可选：指定 CA 证书路径（生产环境推荐配置）
        $config['ca_cert']      = $config['ca_cert'] ?? '';
        return $config;
    }

    /**
     * 获取 Guzzle 客户端配置
     * @param array $config
     * @param int $timeout 超时时间（秒）
     * @return array
     */
    protected static function getGuzzleOptions(array $config, int $timeout = 60): array
    {
        $options = [
            'timeout'     => $timeout,
            'http_errors' => false
        ];
        // verify_ssl=false：关闭 SSL 证书验证（仅建议开发环境临时使用）
        if (isset($config['verify_ssl']) && $config['verify_ssl'] == 0) {
            $options['verify'] = false;
        } elseif (!empty($config['ca_cert'])) {
            // 指定了 CA 证书路径时使用该证书验证
            $options['verify'] = $config['ca_cert'];
        };
        return $options;
    }

    // ===================== 本地存储 =====================

    /**
     * 保存到本地
     * @param UploadFile|string $file 上传文件对象或本地文件路径
     * @param string $relative_path 相对路径
     * @param array $config
     * @return array
     * @throws BusinessException
     */
    protected static function saveToLocal($file, string $relative_path, array $config): array
    {
        $base_dir = rtrim(config('plugin.webniu.app.public_path', ''), '\\/');
        if ($base_dir) {
            $base_dir .= DIRECTORY_SEPARATOR;
        } else {
            $base_dir = base_path() . '/plugin/webniu/public/';
        }

        $full_path = $base_dir . $relative_path;
        $dir = dirname($full_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($file instanceof UploadFile) {
            $file->move($full_path);
        } else {
            copy($file, $full_path);
        }

        return [
            'url'      => '/app/webniu/' . $relative_path,
            'realpath' => $full_path,
        ];
    }

    /**
     * 删除本地文件
     * @param string $key 相对路径或 url
     * @param array $config
     * @return bool
     */
    protected static function deleteFromLocal(string $key, array $config): bool
    {
        // 兼容传入 url 或纯路径
        if (strpos($key, '/app/webniu/') === 0) {
            $key = str_replace('/app/webniu/', '', $key);
        }
        $base_dir = rtrim(config('plugin.webniu.app.public_path', ''), '\\/');
        if ($base_dir) {
            $base_dir .= DIRECTORY_SEPARATOR;
        } else {
            $base_dir = base_path() . '/plugin/webniu/public/';
        }
        $full_path = $base_dir . $key;
        return is_file($full_path) ? @unlink($full_path) : false;
    }

    // ===================== FTP 存储 =====================

    /**
     * 保存到 FTP
     * @param UploadFile|string $file 上传文件对象或本地文件路径
     * @param string $relative_path
     * @param array $config
     * @return array
     * @throws BusinessException
     */
    protected static function saveToFtp($file, string $relative_path, array $config): array
    {
        if (!function_exists('ftp_connect')) {
            throw new BusinessException('服务器未安装 FTP 扩展', 500);
        }

        $ip       = $config['ftp_ip'] ?? '';
        $port     = (int)($config['ftp_port'] ?? 21);
        $user     = $config['ftp_username'] ?? '';
        $pass     = $config['ftp_password'] ?? '';
        $pasv     = (bool)($config['ftp_pasv'] ?? false);
        $domain   = rtrim($config['ftp_domain'] ?? '', '/');

        if (!$ip || !$user) {
            throw new BusinessException('FTP 配置不完整', 500);
        }

        $conn = @\ftp_connect($ip, $port, 30);
        if (!$conn) {
            throw new BusinessException('FTP 连接失败', 500);
        }
        if (!@\ftp_login($conn, $user, $pass)) {
            @\ftp_close($conn);
            throw new BusinessException('FTP 登录失败', 500);
        }
        if ($pasv) {
            @\ftp_pasv($conn, true);
        }

        // 递归创建目录
        self::ftpMkdirRecursive($conn, dirname($relative_path));

        // 上传文件
        $tmp_path = $file instanceof UploadFile ? $file->getPathname() : $file;
        $ftp_binary = defined('FTP_BINARY') ? FTP_BINARY : 2;
        if (!@\ftp_put($conn, $relative_path, $tmp_path, $ftp_binary)) {
            @\ftp_close($conn);
            throw new BusinessException('FTP 上传失败', 500);
        }

        @\ftp_close($conn);

        $url = $domain ? $domain . '/' . $relative_path : $relative_path;

        return [
            'url'      => $url,
            'realpath' => $relative_path,
        ];
    }

    /**
     * FTP 递归创建目录
     * @param mixed $conn FTP 连接（PHP 8.1+ 为 FTP\Connection 对象）
     * @param string $path 目录路径
     */
    protected static function ftpMkdirRecursive($conn, string $path): void
    {
        $path = trim($path, '/');
        if ($path === '' || @\ftp_chdir($conn, $path)) {
            return;
        }
        $parent = dirname($path);
        if ($parent !== '.' && $parent !== '') {
            self::ftpMkdirRecursive($conn, $parent);
        }
        @\ftp_mkdir($conn, $path);
    }

    /**
     * 从 FTP 删除文件
     * @param string $key
     * @param array $config
     * @return bool
     */
    protected static function deleteFromFtp(string $key, array $config): bool
    {
        if (!function_exists('ftp_connect')) {
            return false;
        }
        $conn = @\ftp_connect($config['ftp_ip'] ?? '', (int)($config['ftp_port'] ?? 21), 30);
        if (!$conn) {
            return false;
        }
        if (!@\ftp_login($conn, $config['ftp_username'] ?? '', $config['ftp_password'] ?? '')) {
            @\ftp_close($conn);
            return false;
        }
        if (!empty($config['ftp_pasv'])) {
            @\ftp_pasv($conn, true);
        }
        $result = @\ftp_delete($conn, $key);
        @\ftp_close($conn);
        return $result;
    }

    // ===================== 七牛云存储 =====================

    /**
     * 保存到七牛云
     * @param UploadFile|string $file 上传文件对象或本地文件路径
     * @param string $relative_path object key
     * @param array $config
     * @return array
     * @throws BusinessException
     */
    protected static function saveToQiniu($file, string $relative_path, array $config): array
    {
        $accessKey = $config['qiniu_accesskey'] ?? '';
        $secretKey = $config['qiniu_secretkey'] ?? '';
        $bucket    = $config['qiniu_bucket'] ?? '';
        $domain    = rtrim($config['qiniu_domain'] ?? '', '/');

        if (!$accessKey || !$secretKey || !$bucket) {
            throw new BusinessException('七牛云配置不完整', 500);
        }

        // 生成上传凭证
        $token = self::buildQiniuToken($accessKey, $secretKey, $bucket, $relative_path);

        // 通过表单方式上传
        $client = new Client(self::getGuzzleOptions($config, 60));
        $file_path = $file instanceof UploadFile ? $file->getPathname() : $file;
        $file_name = $file instanceof UploadFile ? $file->getUploadName() : basename($file_path);
        $response = $client->post('https://up-z1.qiniup.com', [
            'multipart' => [
                ['name' => 'token', 'contents' => $token],
                ['name' => 'key', 'contents' => $relative_path],
                [
                    'name'     => 'file',
                    'contents' => fopen($file_path, 'r'),
                    'filename' => $file_name,
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new BusinessException('七牛云上传失败：' . $response->getBody()->getContents(), 500);
        }

        $url = $domain ? $domain . '/' . $relative_path : $relative_path;

        return [
            'url'      => $url,
            'realpath' => $relative_path,
        ];
    }

    /**
     * 构建七牛上传凭证
     * @param string $accessKey
     * @param string $secretKey
     * @param string $bucket
     * @param string $key
     * @return string
     */
    protected static function buildQiniuToken(string $accessKey, string $secretKey, string $bucket, string $key): string
    {
        $policy = [
            'scope'    => $bucket . ':' . $key,
            'deadline' => time() + 3600,
        ];
        $encodedPolicy = self::urlsafeBase64Encode(json_encode($policy));
        $sign           = hash_hmac('sha1', $encodedPolicy, $secretKey, true);
        $encodedSign    = self::urlsafeBase64Encode($sign);
        return $accessKey . ':' . $encodedSign . ':' . $encodedPolicy;
    }

    /**
     * 从七牛云删除文件
     * @param string $key
     * @param array $config
     * @return bool
     */
    protected static function deleteFromQiniu(string $key, array $config): bool
    {
        $accessKey = $config['qiniu_accesskey'] ?? '';
        $secretKey = $config['qiniu_secretkey'] ?? '';
        $bucket    = $config['qiniu_bucket'] ?? '';

        $entry  = self::urlsafeBase64Encode($bucket . ':' . $key);
        $url    = 'https://rs.qiniuapi.com/delete/' . $entry;
        $token  = self::buildQiniuAccessToken($accessKey, $secretKey, 'POST', $url);

        $client = new Client(self::getGuzzleOptions($config, 30));
        $response = $client->post($url, [
            'headers' => ['Authorization' => 'QBox ' . $token],
        ]);
        return $response->getStatusCode() === 200;
    }

    /**
     * 构建七牛管理操作 AccessToken
     * @param string $accessKey
     * @param string $secretKey
     * @param string $method
     * @param string $url
     * @param string $body
     * @return string
     */
    protected static function buildQiniuAccessToken(string $accessKey, string $secretKey, string $method, string $url, string $body = ''): string
    {
        $parsed = parse_url($url);
        $path   = $parsed['path'] ?? '';
        $query  = $parsed['query'] ?? '';
        $signingStr = $method . ' ' . $path;
        if ($query) {
            $signingStr .= '?' . $query;
        }
        $signingStr .= "\nHost: " . ($parsed['host'] ?? '');
        if (!empty($parsed['port']) && $parsed['port'] != 80 && $parsed['port'] != 443) {
            $signingStr .= ':' . $parsed['port'];
        }
        $signingStr .= "\n";
        if ($body) {
            $signingStr .= "Content-Type: application/x-www-form-urlencoded\n";
            $signingStr .= "\n" . $body;
        }
        $sign = hash_hmac('sha1', $signingStr, $secretKey, true);
        return $accessKey . ':' . self::urlsafeBase64Encode($sign);
    }

    // ===================== 阿里云 OSS 存储 =====================

    /**
     * 保存到阿里云 OSS
     * @param UploadFile|string $file 上传文件对象或本地文件路径
     * @param string $relative_path object key
     * @param array $config
     * @return array
     * @throws BusinessException
     */
    protected static function saveToOss($file, string $relative_path, array $config): array
    {
        $accessKeyId     = $config['oss_accesskeyid'] ?? '';
        $accessKeySecret = $config['oss_accesskeysecret'] ?? '';
        $bucket          = $config['oss_bucket'] ?? '';
        $endpoint        = trim($config['oss_endpoint'] ?? '', '/');
        $domain          = rtrim($config['oss_domain'] ?? '', '/');
        $local           = (int)($config['oss_local'] ?? 0); // 0=外网 1=内网

        if (!$accessKeyId || !$accessKeySecret || !$bucket || !$endpoint) {
            throw new BusinessException('OSS 配置不完整', 500);
        }

        // 内网 endpoint 替换
        if ($local === 1 && strpos($endpoint, '-internal') === false) {
            $endpoint = preg_replace('/^(oss-[a-z0-9-]+)/', '$1-internal', $endpoint);
        }

        $host  = $bucket . '.' . $endpoint;
        $url   = 'https://' . $host . '/' . $relative_path;
        $file_path = $file instanceof UploadFile ? $file->getPathname() : $file;
        $mime  = $file instanceof UploadFile ? ($file->getUploadMimeType() ?: 'application/octet-stream') : (mime_content_type($file_path) ?: 'application/octet-stream');
        $date  = gmdate('D, d M Y H:i:s T');

        // OSS v1 签名
        $stringToSign = "PUT\n\n" . $mime . "\n" . $date . "\n/" . $bucket . '/' . $relative_path;
        $signature    = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret, true));

        $client = new Client(self::getGuzzleOptions($config, 60));
        $response = $client->put($url, [
            'headers' => [
                'Authorization' => 'OSS ' . $accessKeyId . ':' . $signature,
                'Content-Type'  => $mime,
                'Date'          => $date,
            ],
            'body'    => fopen($file_path, 'r'),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new BusinessException('OSS 上传失败：' . $response->getBody()->getContents(), 500);
        }

        $resultUrl = $domain ? $domain . '/' . $relative_path : $url;

        return [
            'url'      => $resultUrl,
            'realpath' => $relative_path,
        ];
    }

    /**
     * 从 OSS 删除文件
     * @param string $key
     * @param array $config
     * @return bool
     */
    protected static function deleteFromOss(string $key, array $config): bool
    {
        $accessKeyId     = $config['oss_accesskeyid'] ?? '';
        $accessKeySecret = $config['oss_accesskeysecret'] ?? '';
        $bucket          = $config['oss_bucket'] ?? '';
        $endpoint        = trim($config['oss_endpoint'] ?? '', '/');

        $host = $bucket . '.' . $endpoint;
        $url  = 'https://' . $host . '/' . $key;
        $date = gmdate('D, d M Y H:i:s T');

        $stringToSign = "DELETE\n\n\n" . $date . "\n/" . $bucket . '/' . $key;
        $signature    = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret, true));

        $client = new Client(self::getGuzzleOptions($config, 30));
        $response = $client->delete($url, [
            'headers' => [
                'Authorization' => 'OSS ' . $accessKeyId . ':' . $signature,
                'Date'          => $date,
            ],
        ]);
        return $response->getStatusCode() === 204 || $response->getStatusCode() === 200;
    }

    // ===================== 腾讯云 COS 存储 =====================

    /**
     * 保存到腾讯云 COS
     * @param UploadFile|string $file 上传文件对象或本地文件路径
     * @param string $relative_path object key
     * @param array $config
     * @return array
     * @throws BusinessException
     */
    protected static function saveToCos($file, string $relative_path, array $config): array
    {
        $secretId  = $config['cos_secretid'] ?? '';
        $secretKey = $config['cos_secretkey'] ?? '';
        $bucket    = $config['cos_bucket'] ?? '';
        $region    = $config['cos_region'] ?? '';
        $domain    = rtrim($config['cos_domain'] ?? '', '/');

        if (!$secretId || !$secretKey || !$bucket || !$region) {
            throw new BusinessException('COS 配置不完整', 500);
        }

        $host = $bucket . '.cos.' . $region . '.myqcloud.com';
        $url  = 'https://' . $host . '/' . $relative_path;
        $file_path = $file instanceof UploadFile ? $file->getPathname() : $file;
        $mime = $file instanceof UploadFile ? ($file->getUploadMimeType() ?: 'application/octet-stream') : (mime_content_type($file_path) ?: 'application/octet-stream');

        // COS v5 签名（sha1）
        $authorization = self::buildCosAuthorization($secretId, $secretKey, 'put', $relative_path, $host);

        $client = new Client(self::getGuzzleOptions($config, 60));
        $response = $client->put($url, [
            'headers' => [
                'Authorization' => $authorization,
                'Content-Type'  => $mime,
                'Host'          => $host,
            ],
            'body'    => fopen($file_path, 'r'),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new BusinessException('COS 上传失败：' . $response->getBody()->getContents(), 500);
        }

        $resultUrl = $domain ? $domain . '/' . $relative_path : $url;

        return [
            'url'      => $resultUrl,
            'realpath' => $relative_path,
        ];
    }

    /**
     * 构建 COS v5 签名
     * @param string $secretId
     * @param string $secretKey
     * @param string $method HTTP 方法
     * @param string $key object key
     * @param string $host
     * @return string Authorization 头
     */
    protected static function buildCosAuthorization(string $secretId, string $secretKey, string $method, string $key, string $host): string
    {
        // key-time
        $now      = time();
        $keyTime  = $now . ';' . ($now + 600);

        // 1. SignKey = hmac_sha1(secretKey, keyTime)
        $signKey = hash_hmac('sha1', $keyTime, $secretKey);

        // 2. FormatString
        $formatString = strtolower($method) . "\n" . '/' . $key . "\n\n" . 'host=' . $host . "\n";

        // 3. StringToSign
        $stringToSign = 'sha1' . "\n" . $keyTime . "\n" . sha1($formatString) . "\n";

        // 4. Signature
        $signature = hash_hmac('sha1', $stringToSign, $signKey);

        // 5. Authorization
        return 'q-sign-algorithm=sha1&q-ak=' . $secretId
            . '&q-sign-time=' . $keyTime
            . '&q-key-time=' . $keyTime
            . '&q-header-list=host&q-url-param-list=&q-signature=' . $signature;
    }

    /**
     * 从 COS 删除文件
     * @param string $key
     * @param array $config
     * @return bool
     */
    protected static function deleteFromCos(string $key, array $config): bool
    {
        $secretId  = $config['cos_secretid'] ?? '';
        $secretKey = $config['cos_secretkey'] ?? '';
        $bucket    = $config['cos_bucket'] ?? '';
        $region    = $config['cos_region'] ?? '';

        $host = $bucket . '.cos.' . $region . '.myqcloud.com';
        $url  = 'https://' . $host . '/' . $key;
        $authorization = self::buildCosAuthorization($secretId, $secretKey, 'delete', $key, $host);

        $client = new Client(self::getGuzzleOptions($config, 30));
        $response = $client->delete($url, [
            'headers' => [
                'Authorization' => $authorization,
                'Host'          => $host,
            ],
        ]);
        return $response->getStatusCode() === 204 || $response->getStatusCode() === 200;
    }

    // ===================== 工具方法 =====================

    /**
     * 根据路径风格生成日期目录
     * @param string $style 风格，如 Ymd / Ym / Y/m/d
     * @return string
     */
    protected static function buildPathStyle(string $style): string
    {
        if (!$style) {
            $style = 'Ymd';
        }
        return date($style);
    }

    /**
     * 生成唯一文件名
     * @param string $ext 扩展名
     * @return string
     * @throws \Random\RandomException
     */
    protected static function generateFilename(string $ext): string
    {
        $name = bin2hex(pack('Nn', time(), random_int(1, 65535)));
        return $ext ? $name . '.' . $ext : $name;
    }

    /**
     * 获取图片尺寸
     * @param UploadFile $file
     * @return array [width, height]
     */
    protected static function getImageSize(UploadFile $file): array
    {
        $info = @getimagesize($file->getPathname());
        if ($info) {
            return [$info[0], $info[1]];
        }
        return [0, 0];
    }

    /**
     * 解析逗号分隔的列表为小写数组
     * @param string $str
     * @return array
     */
    protected static function parseList(string $str): array
    {
        if ($str === '') {
            return [];
        }
        return array_map('trim', array_map('strtolower', explode(',', $str)));
    }

    /**
     * URL 安全的 Base64 编码（七牛专用）
     * @param string $data
     * @return string
     */
    protected static function urlsafeBase64Encode(string $data): string
    {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($data));
    }
}
