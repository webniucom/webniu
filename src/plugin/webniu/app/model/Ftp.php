<?php
namespace plugin\webniu\app\model;

use support\exception\BusinessException;

/**
 * 字典相关
 */
class Ftp
{
    /**
     * @var resource 连接句柄
     */
    private $conn;

    /**
     * 链接FTP服务器
     *
     * @param string $host FTP服务器地址
     * @param int $port FTP服务器端口，默认为21
     * @return bool
     */
    public function connect($host, $port = 21)
    {
        $conn = ftp_connect($host, $port);
        if (!$conn) {
            return false;
        }
        $this->conn = $conn;
        return true;
    }

    /**
     * 登录FTP服务器
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool
     */
    public function login($username, $password)
    {
        $result = ftp_login($this->conn, $username, $password);
                ftp_pasv($this->conn,TRUE);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 登录FTP服务器
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @return bool
     */
    public function pasv($pasv)
    {
        if($pasv){
            ftp_pasv($this->conn,$pasv);
        }else{
            ftp_pasv($this->conn,false);
        }
        return true;
    }

    /**
     * 断开FTP链接
     *
     * @return bool
     */
    public function disconnect()
    {
        ftp_close($this->conn);
        $this->conn = null;
        return true;
    }

    /**
     * 上传文件到FTP服务器
     *
     * @param string $remoteFile 远程文件路径
     * @param string $localFile 本地文件路径
     * @return bool
     */
    public function upload($remoteFile, $localFile)
    {
        $result = ftp_put($this->conn, $remoteFile, $localFile, FTP_BINARY);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 下载文件到本地
     *
     * @param string $remoteFile 远程文件路径
     * @param string $localFile 本地文件路径
     * @return bool
     */
    public function download($remoteFile, $localFile)
    {
        $result = ftp_get($this->conn, $localFile, $remoteFile, FTP_BINARY);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 拷贝文件
     *
     * @param string $originalFile 原文件路径
     * @param string $targetFile 目标文件路径
     * @return bool
     */
    public function copy($originalFile, $targetFile)
    {
        $result = ftp_exec($this->conn, "CPFR $originalFile");
        if (!$result) {
            return false;
        }
        $result = ftp_exec($this->conn, "CPTO $targetFile");
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 移动文件
     *
     * @param string $originalFile 原文件路径
     * @param string $targetFile 目标文件路径
     * @return bool
     */
    public function move($originalFile, $targetFile)
    {
        if (!$this->copy($originalFile, $targetFile)) {
            return false;
        }
        if (!$this->delete($originalFile)) {
            return false;
        }
        return true;
    }

    /**
     * 删除文件
     *
     * @param string $remoteFile 远程文件路径
     * @return bool
     */
    public function delete($remoteFile)
    {
        $result = ftp_delete($this->conn, $remoteFile);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * 创建目录
     *
     * @param string $remotePath 远程目录路径
     * @return bool
     */
    public function makeDir($remotePath)
    {
        $this->createRemoteDirectory($remotePath);
        return true;
    }

    public function createRemoteDirectory($directory) {  
       
        // 创建目录路径  
        $directories = explode('/', $directory);  
        $base_directory = '/';  
        foreach ($directories as $dir) {  
            $base_directory .= $dir . '/';
            if(ftp_nlist($this->conn, $base_directory)===false){
                ftp_mkdir($this->conn, $base_directory);
            } 
        }  
        return true;  
         
    }  
}
