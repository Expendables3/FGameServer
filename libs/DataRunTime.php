<?php
class DataRunTime
{
    private static $Cache = array('Sparta' => false ,
    'myFish' => false ,
    'Swat'=> false , 
    'Mermaid' => false,
    'Superman' => false,
    'IronMan'=>false,
    'Ring'=>false,
     'OccupiedBak' => false,
    );
    static public function get($id = 'Sparta', $isTotal = false)
    {
        if ($isTotal)
            $key = 'Total'.'_'.$id.'_'.'DataRunTime';
        else
            $key = date('Ymd', $_SERVER['REQUEST_TIME']).'_'.$id.'_'.'DataRunTime';
        if(self::$Cache[$id])
        {
            $num = DataProvider::getMemcache()->get($key);
            if($num === false )
                DataProvider::getMemcache()->set($key,0);  
        }
        else 
        {
            $num = DataProvider::getMemBase()->get($key);
            if($num === false )
                DataProvider::getMemBase()->set($key,0);  
        }
        return $num ;
    }

    static public function inc($id,$num = 1, $isTotal = false)
    {
        if ($isTotal)
            $key = 'Total'.'_'.$id.'_'.'DataRunTime';
        else
            $key = date('Ymd', $_SERVER['REQUEST_TIME']).'_'.$id.'_'.'DataRunTime';
        if(self::$Cache[$id])
        {
            $cur = DataProvider::getMemcache()->get($key);
            if($cur === false )
                DataProvider::getMemcache()->set($key,0);
            $total = DataProvider::getMemcache()->increment($key,$num);
        }
        else 
        {
            $cur = DataProvider::getMemBase()->get($key);
            if($cur === false )
                DataProvider::getMemBase()->set($key,0);
            $total = DataProvider::getMemBase()->increment($key,$num); 
        }
        return $total ;  
    }


    static public function dec($id,$num = 1, $isTotal = false)
    {
        if ($isTotal)
            $key = 'Total'.'_'.$id.'_'.'DataRunTime';
        else
            $key = date('Ymd', $_SERVER['REQUEST_TIME']).'_'.$id.'_'.'DataRunTime';
        if(self::$Cache[$id])
        {
            $total = DataProvider::getMemcache()->decrement($key,$num);
        }
        else 
        {
            $total = DataProvider::getMemBase()->decrement($key,$num); 
        }
        return $total ;  
    }

    static public function set($id,$value = 0, $isTotal = false)
    {
        if ($isTotal)
            $key = 'Total'.'_'.$id.'_'.'DataRunTime';
        else
            $key = date('Ymd', $_SERVER['REQUEST_TIME']).'_'.$id.'_'.'DataRunTime';
        if(self::$Cache[$id])
        {
            $inital = DataProvider::getMemcache()->set($key,$value);
        }
        else 
        {
            $inital = DataProvider::getMemBase()->set($key,$value); 
        }
        return $inital ;  
    }
    
    static public function getDataTime($id,$time)
    {
        $key = $time.'_'.$id.'_'.'DataRunTime';
        if(self::$Cache[$id])
            $data = DataProvider::getMemcache()->get($key);
        else
            $data = DataProvider::getMemBase()->get($key);
        return $data;
    }
    
    static public function setDataTime($id,$time, $data, $expire = 0)
    {
        $key = $time.'_'.$id.'_'.'DataRunTime';
        $compress = is_bool($data) || is_int($data) || is_float($data) ? false : MEMCACHE_COMPRESSED ;
        if(self::$Cache[$id])
            $res = DataProvider::getMemcache()->set($key, $data,$compress,$expire);
        else
            $res = DataProvider::getMemBase()->set($key, $data,$compress, $expire);
        return $res;        
    }
}
