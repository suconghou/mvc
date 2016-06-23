<?php

/**
* 网易音乐
* @version 0.1
* @author sucongohu
* @link https://github.com/suconghou/mvc
*/
class M163
{
    const api='http://music.163.com';
    private static  $headers=array('User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36','Accept'=>'*/*');
    private static $playlist;
    private static $played;

    /**
     * 歌单内选一首随机
     */
    public static function index(Array $playlist)
    {
        $all=self::getPlayData($playlist);
        $all=array_column($all,null,'id');
        $playId=self::getPlaySong($all);
        if($playId)
        {
            return self::response($all[$playId]);
        }
        return self::response(['code'=>404,'msg'=>'no more']);
    }


    private static function getPlayData(Array $playlist)
    {
        if(isset($_GET['clear']))
        {
            isset($_SESSION)||session_start();
            $_SESSION['played']=[];
            return self::resetPlayData($playlist);
        }
        if(is_file(self::cache()))
        {
            return json_decode(file_get_contents(self::cache()),true);
        }
        return self::resetPlayData($playlist);
    }

    private static function resetPlayData(Array $playlist)
    {
        $data=self::getAllSongs($playlist);
        file_put_contents(self::cache(),json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        return $data;
    }

    private static function cache()
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'music.db';
    }

    private static function getAllSongs(Array $playlist)
    {
        foreach($playlist as &$item)
        {
            $item=self::api."/api/playlist/detail?id={$item}";
        }
        $datas=self::get(array_unique($playlist));
        $datas=is_array($datas)?$datas:array($datas);
        foreach($datas as &$data)
        {
            $data=json_decode($data,true);
            if($errno=json_last_error())
            {
                throw new Exception(json_last_error_msg(),$errno);
            }
        }
        $items=array();
        foreach ($datas as $data)
        {
            if($data['code']==200)
            {
                $tracks=$data['result']['tracks'];
                $items=array_merge($items,$tracks);
            }
        }
        foreach ($items as $key => &$item)
        {
            $cover=$item['album']['picUrl'];
            $title=$item['name'];
            $artist=$item['artists'][0]['name'];
            $album=$item['album']['name'];
            $mp3=$item['mp3Url'];
            $one=['code'=>0,'id'=>$item['id'],'cover'=>$cover,'title'=>$title,'artist'=>$artist,'album'=>$album,'mp3'=>$mp3];
            $item=$one;
        }
        return $items;
    }

    private static function getPlaySong(Array $allMap)
    {
        isset($_SESSION)||session_start();
        $_SESSION['played']=isset($_SESSION['played'])?$_SESSION['played']:[];
        $ids=array_keys($allMap);
        $notPlayed=array_values(array_diff($ids,$_SESSION['played']));
        if(empty($notPlayed))
        {
            return false;
        }
        $randId=$notPlayed[rand(0,count($notPlayed)-1)];
        array_push($_SESSION['played'],$randId);
        return $randId;
    }

    private static function response($data)
    {
        if(isset($_SERVER['HTTP_REFERER']))
        {
            $parts=parse_url($_SERVER['HTTP_REFERER']);
            $host=$parts['scheme'].'://'.$parts['host'];
            if(!empty($parts['port']))
            {
                $host.=':'.$parts['port'];
            }
        }
        else
        {
            $host='*';
        }
        header('Access-Control-Allow-Origin: '.$host,true);
        header('Access-Control-Allow-Credentials:true',true);
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept',true);
        header('Content-Type:text/json; charset=UTF-8',true);
        exit(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    public static function getPlayListInfo($listId)
    {
        $json=self::get(self::api."/api/playlist/detail?id={$listId}");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    public static function getMusicInfo($musicId)
    {
        $json=self::get(self::api."/api/song/detail/?id={$musicId}&ids=%5B{$musicId}%5D");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    public static function getMusicLyric($musicId)
    {
        $json=self::get(self::api."/api/song/lyric?os=pc&id={$musicId}&lv=-1&kv=-1&tv=-1");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    public static function getArtistAlbum($artistId,$limit=50)
    {
        $json=self::get(self::api."/api/artist/albums/{$artistId}?limit={$limit}");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    public static function getAlbumInfo($albumId)
    {
        $json=self::get(self::api."/api/album/{$albumId}");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    public static function getMvInfo($id,$type='mp4')
    {
        $json=self::get(self::api."/api/mv/detail?id={$id}&type={$type}");
        $data=json_decode($json,true);
        if($errno=json_last_error())
        {
            throw new Exception(json_last_error_msg(),$errno);
        }
        return $data;
    }

    //$type 搜索单曲(1)，歌手(100)，专辑(10)，歌单(1000)，用户(1002)
    private static function searchMusic($word,$type=1,$page=1,$pageSize=20)
    {
        $offset=intval(max($page-1,0)*$pageSize);
        $data=array('s' => $word, 'offset' => '0', 'limit' => '20', 'type' => $type,'total'=>true);
        $json=self::get(self::api."/api/search/get/web",http_build_query($data));

    }


    private static function get($url,$data=null,$timeout=8)
    {
        self::$headers=array_merge(self::$headers,array('Cookie'=>'appver=1.5.0.75771','Referer'=>'http://music.163.com/'));
        return self::http($url,$timeout,$data);
    }

    /**
     * 支持单线程,多线程 http get/post 请求
     */
    private static function http($urls,$timeout=8,$data=null)
    {
        if(!is_array($urls))
        {
            $ch=curl_init($urls);
            curl_setopt_array($ch,array(CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
            $data&&curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data));
            $content=curl_exec($ch);
            curl_close($ch);
            return $content;
        }
        else
        {
            $mh=curl_multi_init();
            foreach ($urls as &$url)
            {
                $ch=curl_init($url);
                curl_setopt_array($ch,array(CURLOPT_HTTPHEADER=>self::$headers,CURLOPT_FOLLOWLOCATION=>1,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>$timeout,CURLOPT_CONNECTTIMEOUT=>$timeout));
                $data&&curl_setopt_array($ch,array(CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$data));
                curl_multi_add_handle($mh,$ch);
                $url=$ch;
            }
            $runing=0;
            do
            {
                curl_multi_exec($mh,$runing);
                curl_multi_select($mh);
            }
            while($runing>0);
            foreach($urls as &$ch)
            {
                $content=curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh,$ch);
                curl_close($ch);
                $ch=$content;
            }
            curl_multi_close($mh);
            $content=count($urls)>1?$urls:reset($urls);
            return $content;
        }
    }


}

