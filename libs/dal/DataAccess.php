<?php
/*
* Description:
* @author: hungnm2
* Date: 
*/
require_once (dirname(__FILE__).'/ConfigData.php');

interface DataAcessIf
{
  static function get($bucket,$key);
  static function set($bucket, $key, $data,$isCompressed = false,$timeout = 0);
  static function replace($bucket,$key,$data,$isCompressed = false,$timeout = 0);
  static function decrement($bucket,$key,$data);
  static function increment($bucket,$key,$data);
  static function delete($bucket,$key,$timeout = 0);
  static function flush($bucket);
  static function isBucket($bucket);
}

class DataAccess implements DataAcessIf
{
  static private $isReady = false;
  static private $buckets;
  static private $membaseObj;
  
  static private function init()
  {
    self::$membaseObj = new Memcache();
    global $CONFIG_DATA;
    self::$buckets = & $CONFIG_DATA['buckets'];
    foreach ($CONFIG_DATA['servers'] as $server)
    {
      foreach ($CONFIG_DATA['buckets'] as $bucket)
      {
        $res = self::$membaseObj->addServer($server, $bucket[0], $bucket[1], $bucket[2]);
      }
    }
    self::$isReady = true;
  }

  static function get($bucket, $key)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->get($key);
  }

  static function set($bucket, $key, $data, $isCompressed = false, $timeout = 0)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)||($data == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->set($key, $data, $isCompressed, $timeout);
  }

  static function replace($bucket,$key,$data,$isCompressed = false,$timeout = 0)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)||($data == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->set($key, $data);
  }

  static function decrement($bucket,$key,$data)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)||($data == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->decrement($key, $data);
  }

  static function increment($bucket,$key,$data)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)||($data == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->increment($key, $data);
  }

  static function delete($bucket,$key,$timeout = 0)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)||($key == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->delete($key, $timeout);
  }

  static function flush($bucket)
  {
    if (!self::$isReady){self::init();}
    if (($bucket == NULL)) return 'Param Invalid';
    if (!self::isBucket($bucket)) return 'Bucket Name Invalid';
    return self::$membaseObj->flush($bucket);
  }

  static function isBucket($bucket)
  {
    foreach (self::$buckets as $key => $value)
    {
      if ($bucket == $key) return true;
    }
    return false;
  }
}