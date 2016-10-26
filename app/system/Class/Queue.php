<?php
/** 
 *
 * PHP队列
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 头(消费)[shift] * * * * 队列 * * * *  尾(生产)[push]  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * $queue=S('class/Queue')->select('myqueue');
 * $queue->push();
 * $data=$queue->shift();
 * 
 * 
 * 
 */
class Queue
{
    
    private static $data;
    
    private static $name;
    
    private static $cache;
    
    
    public function __construct($driver=true)
    {
        self::$cache=S('Class/Cache',$driver);
        if($driver=='redis')
        {
            //使用redis队列功能
        }
    }
    
    //初始化一个队列进行操作
    function select($name)
    {
        self::$name=$name;
        self::getData();
        return $this;
    }
    
    //从尾部弹出
    function pop()
    {
        $ret=array_pop(self::$data);
        self::saveData();
        return $ret;
    }
    
    //加入队列尾部
    function push($data)
    {
        $ret=array_push(self::$data,$data);
        self::saveData();
        return $ret;
    }
    
    //从开头弹出
    function shift()
    {
        $ret=array_shift(self::$data);
        self::saveData();
        return $ret;
    }
    
    //加入到开头
    function unshift($data)
    {
        $ret=array_unshift(self::$data,$data);
        self::saveData();
        return $ret;
    }
    
    function first()
    {
        return reset(self::$data);
    }
    
    function last()
    {
        return end(self::$data);
    }
    
    function length()
    {
        return count(self::$data);
    }
    
    function clear()
    {
        self::$data=array();
        return self::saveData();
    }
    
    private static function getData()
    {
        $data=self::$cache->get(self::$name);
        return self::$data=$data?$data:array();
    }
    
    private static function saveData()
    {
        return self::$cache->set(self::$name,self::$data);
    }
}
