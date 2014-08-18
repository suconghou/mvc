<?php
/**
* FTP类
*/
class ftp
{
    private static $host;
    private static $port=21;
    private static $user;
    private static $pass;
    private static $conn=FALSE;
    private static $error;
    private static $passive=TRUE;
    
    function __construct($arr)
    {
        if(is_array($arr))
        {
            self::$host=isset($arr['host'])?$arr['host']:'localhost';
            self::$port=isset($arr['port'])?$arr['port']:21;
            self::$user=isset($arr['user'])?$arr['user']:'root';
            self::$pass=isset($arr['pass'])?$arr['pass']:123456;
        }
        
    }
    private function init()
    {


    }
    /**
     * 连接
     */
    function connect()
    {
        if(FALSE===(self::$conn=ftp_connect(self::$host,self::$port)))
        {
            self::$error='connect '.self::$host.':'.$self::$port.' failed !';
            return flase;
        }
        if(!$this->login())
        {
            self::$error='user '.$self::$user.' @ '.self::$pass.' login failed !';
            return false;
        }
        if(self::$passive === TRUE) 
        {
            ftp_pasv(self::$conn, TRUE);
        }
        return true;
    }

    private function login()
    {

    }

    public function chgdir($dir='')
    {

    }
    public function mkdir()
    {

    }
    public function upload()
    {

    } 
    public function download()
    {

    } 
    public function rename()
    {

    }
    public function delFile()
    {

    }
    public function delDir()
    {

    }
    public function chmod()
    {

    }
    public function fileList()
    {

    }
    public function close()
    {
        
    }
    /**
     * 获取出错时的信息
     */
    public  function lastError()
    {
        return self::$error;
    }
    function backup()
    {

    }
}