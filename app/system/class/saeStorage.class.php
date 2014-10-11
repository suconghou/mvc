<?php
/**
* SAE KV存储,文件存储中心
* 规则,小于4M存入KV,否则存入STORAGE
* 取出,首先在kv里查找,没有的话,到storage里查找,也没有则输出默认
* 关于后缀名,STOR必须带上
*/
class sae_storage
{
    private static $kv;
    private static $stor;
    private static $size=4194304; //超过多少字节存入到storage中,默认为4M,KV最大存储4M

    function __construct()
    {
        self::$kv = new SaeKV();
        self::$kv->init();
        self::$stor=new SaeStorage();
    }
    public function get($key)
    {
        return self::$kv->get($key);
    }

    public function set($key,$value)
    {
        return self::$kv->set($key,$value);
    }

    private function kv_push($key,$gzdata)
    {
        return $this->set($key,$gzdata);
    }
    private function stor_push($key,$orgindata,$type)
    {
        $dir=substr($key,0,1);
        $filepath=$dir.'/'.$key.'.'.$type;
        $attr = array('encoding'=>'gzip');
        return self::$stor->write('storage',$filepath,$orgindata,-1,$attr,true);
    }

    private function stor_dump($key,$type)
    {
        $dir=substr($key,0,1);
        $filepath=$dir.'/'.$key.'.'.$type;
        if(self::$stor->fileExists('storage',$filepath))
        {
            $url=self::$stor->getUrl('storage',$filepath);
            redirect($url); ///调用了重定向
        }
        else //KV中没有,storage中也没有找到
        {
            return null;
        }

    }
    private function kv_delete($key)
    {
         return self::$kv->delete($key);
    }
    private function stor_delete($key)
    {
        $dir=substr($key,0,1);
        if(self::$stor->fileExists('storage',$dir.'/'.$key))
        {
            return self::$stor->delete('storage',$dir.'/'.$key);    
        }
    }

    //对外接口,存入
    public function push($bindata,$type)
    {
         $gz=gzcompress($bindata);
         $key=md5($gz);
         if(strlen($gz)>=self::$size)
         {
            return $this->stor_push($key,$bindata,$type)?$key:false;
         }
         else//采用KV 存储, 不需要指定的扩展名
         {
            return $this->kv_push($key,$gz)?$key:false;
         }

    }
    //取出,在KV的直接输出,在Stor的重定向
    public function dump($key,$type)
    {
        $gz=$this->get($key);
        if($gz)//在KV中命中
        {
            
            $bindata=$gz?gzuncompress($gz):null;
           
            return $bindata;
        }
        else///在STOR中查找
        {
            return $this->stor_dump($key,$type);
        }


    }
    //删除
    public function delete($key)
    {
        $this->kv_delete($key);
        $this->stor_delete($key);
    }
    /**
     * 由文件内容获得扩展名
     */
    public function getType($contents,$userType)
    {
        $bin=substr($contents, 0, 2);
        $strInfo =@unpack("c2chars", $bin);
        $typeCode=intval($strInfo['chars1'].$strInfo['chars2']);
        $types=array(
                    '8297'=>'rar',
                    '8075'=>'zip',
                    '55122'=>'7z',
                    '255216'=>'jpg',
                    '13780'=>'png',
                    '7173'=>'gif',
                    '6677'=>'bmp',
                    '7784'=>'midi',
                    '7790'=>'exe',
                    '7368'=>'mp3',
                    '7076'=>'flv',
                    '8381'=>'db',
                    '4838'=>'wmv',
                    '3780'=>'pdf',
                    '2669'=>'mkv' 
                    );
        //Fix
        if($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40')
        {
            return 'jpg';
        }
        if($strInfo['chars1']=='-119' && $strInfo['chars2']=='80')
        {
            return 'png';
        }
        return isset($types[$typeCode])?$types[$typeCode]:$userType;
    }
    /**
     * 由扩展得到mime
     */
    public function mime($ext,$hash)
    {
        switch ($ext)
        {
            case 'jar': $mime = "application/java-archive"; break;
            case 'zip': $mime = "application/zip"; break;
            case 'jpeg': $mime = "image/jpeg"; break;
            case 'jpg': $mime = "image/jpg"; break;
            case 'jad': $mime = "text/vnd.sun.j2me.app-descriptor"; break;
            case "gif": $mime = "image/gif"; break;
            case "png": $mime = "image/png"; break;
            case "pdf": $mime = "application/pdf"; break;
            case "txt": $mime = "text/plain"; break;
            case "doc": $mime = "application/msword"; break;
            case "ppt": $mime = "application/vnd.ms-powerpoint"; break;
            case "wbmp": $mime = "image/vnd.wap.wbmp"; break;
            case "wmlc": $mime = "application/vnd.wap.wmlc"; break;
            case "mp4s": $mime = "application/mp4"; break;
            case "ogg": $mime = "application/ogg"; break;
            case "pls": $mime = "application/pls+xml"; break;
            case "asf": $mime = "application/vnd.ms-asf"; break;
            case "swf": $mime = "application/x-shockwave-flash"; break;
            case "mp4": $mime = "video/mp4"; break;
            case "m4a": $mime = "audio/mp4"; break;
            case "m4p": $mime = "audio/mp4"; break;
            case "mp4a": $mime = "audio/mp4"; break;
            case "mp3": $mime = "audio/mpeg"; break;
            case "m3a": $mime = "audio/mpeg"; break;
            case "m2a": $mime = "audio/mpeg"; break;
            case "mp2a": $mime = "audio/mpeg"; break;
            case "mp2": $mime = "audio/mpeg"; break;
            case "mpga": $mime = "audio/mpeg"; break;
            case "wav": $mime = "audio/wav"; break;
            case "m3u": $mime = "audio/x-mpegurl"; break;
            case "bmp": $mime = "image/bmp"; break;
            case "ico": $mime = "image/x-icon"; break;
            case "3gp": $mime = "video/3gpp"; break;
            case "3g2": $mime = "video/3gpp2"; break;
            case "mp4v": $mime = "video/mp4"; break;
            case "mpg4": $mime = "video/mp4"; break;
            case "m2v": $mime = "video/mpeg"; break;
            case "m1v": $mime = "video/mpeg"; break;
            case "mpe": $mime = "video/mpeg"; break;
            case "mpeg": $mime = "video/mpeg"; break;
            case "mpg": $mime = "video/mpeg"; break;
            case "mov": $mime = "video/quicktime"; break;
            case "qt": $mime = "video/quicktime"; break;
            case "avi": $mime = "video/x-msvideo"; break;
            case "midi": $mime = "audio/midi"; break;
            case "mid": $mime = "audio/mid"; break;
            case "amr": $mime = "audio/amr"; break;
            default: $mime = "application/force-download";
        }
        if(!in_array($ext, array('jpg','gif','png','jpeg','mp4','swf','flv'))) //浏览器不能打开,弹出下载提示
        {
             $filename=$hash.'.'.$ext;
             header('Content-Disposition: attachment; filename='.$filename);
        }
        header('Content-Type: '.$mime);

    }
    /**
     * 输出接口,需指定key,type
     * 云存储输出处理,输入hash 与文件扩展名
     * 检测此hash,在KV中命中,直接返回二进制
     * 若没有在KV中,则自动转到storage中,按用户给定的hash与type查找,命中即会重定向,否则null
     * 即KV中命中,要自己检测header输出
     * 
     */
    public function outputHandler($key,$type)
    {
        C(600);//http缓存10小时
        $hash=$key;//取得md5
        $userType=$type;
        $ret=$this->dump($hash,$userType); //命中stor会重定向,命中kv,会返回二进制,否则返回null
        if($ret) //在KV中命中了,检测文件,输出文件头
        {
            $type=$this->getType($ret,$userType); ///自动检测扩展,检测不到使用用户输入的
            $this->mime($type,$hash);
            echo $ret;
        }
        else ///KV中没有命中,转到storage中也没有命中
        {
            echo '404';
        }

    }
    /**
     * 上传接口
     * 上传文件到SAE, 注意上传的文件表单名为file ,并且不能切割上传
     */
    public function uploadHandler()
    {
        header('Access-Control-Allow-Origin:*');
        define('MAXSIZE',1024*1024*10); ///最大能够上传的 10M
        if(!isset($_FILES['file']))
        {
            $data['code']=-2;
            $data['msg']='no file upload ';
            echo json_encode($data);
        }
        else if($_FILES['file']['size']>MAXSIZE)
        {
            $data['code']=-3;
            $data['msg']='文件超过最大限定!';
            echo json_encode($data);
        }
        else
        {
            $arr=explode('.', $_FILES['file']['name']);
            $default= end($arr); ///上传时的扩展名
            $contents=file_get_contents($_FILES['file']['tmp_name']);
            $type=$this->getType($contents,$default); ///获取文件类型,获取不到采用上传时的类型
            $hash=$this->push($contents,$type); ////装载入数据仓库
            if($hash)
            {
                $data['code']=0;
                $data['msg']=$hash.'.'.$type;
            }
            else
            {
                $data['code']=-1;
                $data['msg']='storage error !';
            }
            echo json_encode($data);

        }
    }
}