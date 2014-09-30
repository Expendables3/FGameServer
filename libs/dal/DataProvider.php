<?php

require 'config_data.php' ;

/**
* @author : Toan Nhon
* @created 30-Jun-2010 8:25:39 AM
*/

class DataProvider
{
    static private $Config;

    static private $oMemCache;

    static private $oMemBase ;

    static public $StaticCache = true;

    static public function init()
    {
        GLOBAL $CONFIG_DATA;
        self::$Config =& $CONFIG_DATA ;

        $option =& self::$Config['Buckets'];
        self::$oMemBase = new Memcache();
        self::$oMemCache = new Memcache();
        // random server connect
        $connected = false;
        $serverLst = self::$Config['Servers'];
        do
        {
            $index =  array_rand($serverLst);
            $server =  $serverLst[$index];

            $MemBaseOk = self::$oMemBase->connect($server[0],$option['Membase'][0]);
            $MemCacheOk = self::$oMemCache->connect($server[0], $option['Memcached'][0]);

            if(!$MemBaseOk || !$MemCacheOk)
                unset($serverLst[$index]);     // delete this server
            else $connected = true;

        } while ((!$connected) && (count($serverLst) > 0));

        if(!$connected)
            die('Chua lay duoc du lieu cua nguoi dung') ;
    }

    static function warm($key = 'null')
    {
        die('Config DataProvider Wrong key : '.$key) ;  
    }

    static function get($uId,$keyWord,$id = '',$exKey = '')
    { 
        if(!isset(self::$Config['Key'][$keyWord]))  self::warm($keyWord) ; 
        $opKey =& self::$Config['Key'][$keyWord] ;

        if(empty($uId)) self::warm() ;

        if(!is_array($uId))
        {
            $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";
            if(self::$StaticCache)
                if(StaticCache::check($key))
                    return  StaticCache::get($key);
        }
        else $key = $uId ;   
        if(($opKey['Cache'] === true ))
        {
            $data = self::$oMemCache->get($key);
        }
        else
            $data = self::$oMemBase->get($key);

        if(!is_array($uId))
        {
            if(self::$StaticCache)
                if(is_object($data))
                    StaticCache::$data[$key] =& $data ; 
        }

        return $data;
    }

    static function set($uId,$keyWord,$data,$id = '',$exKey = '')
    {
        if(!isset(self::$Config['Key'][$keyWord]))  self::warm($keyWord) ; 
        $opKey =& self::$Config['Key'][$keyWord] ;
        if(empty($uId)) return false ;

        $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";
        $compress = is_bool($data) || is_int($data) || is_float($data) ? false : MEMCACHE_COMPRESSED ;

        if(($opKey['Cache'] === true ))
        {
            return self::$oMemCache->set($key,$data,$compress,$opKey['Expire']);
        }
        else return self::$oMemBase->set($key,$data,$compress,$opKey['Expire']);
    }

    static function delete($uId,$keyWord,$id = '',$exKey = '')
    {
        if(!isset(self::$Config['Key'][$keyWord]))  self::warm($keyWord) ; 
        $opKey =& self::$Config['Key'][$keyWord] ;

        if(empty($uId)) return false ;

        $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";

        if(($opKey['Cache'] === true ))
        {
            $data = self::$oMemCache->delete($key);
        }
        else
            $data = self::$oMemBase->delete($key);
        return $data ;

    }

    static function add($uId,$keyWord,$data,$id = '',$exKey = '')
    {

        if(!isset(self::$Config['Key'][$keyWord]))  self::warm($keyWord) ; 
        $opKey =& self::$Config['Key'][$keyWord] ;
        if(empty($uId)) return false ;

        $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";
        $compress = is_bool($data) || is_int($data) || is_float($data) ? false : MEMCACHE_COMPRESSED ;

        if(($opKey['Cache'] === true ))
        {
            return self::$oMemCache->add($key,$data,$compress,$opKey['Expire']);
        }
        else return self::$oMemBase->add($key,$data,$compress, $opKey['Expire']);
    }

    static function setPure($keyWord,$data)
    {
        $compress = is_bool($data) || is_int($data) || is_float($data) ? false : MEMCACHE_COMPRESSED ;
        return self::$oMemBase->set($keyWord,$data,$compress);
    }

    static function getPure($keyWord)
    {
        return self::$oMemBase->get($keyWord);
    }

    static function getMemcache()
    {
        return  self::$oMemCache ;
    }

    static function getMemBase()
    {
        return  self::$oMemBase ;  
    }
}

DataProvider::init();
