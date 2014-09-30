<?php

/**
 * @author : Toan Nhon
 * @created 30-Jun-2010 8:25:39 AM
 */

class User 
{
	public $ZMoney;
	public $Id;
    public $ChargeXu    = 0 ;
    public $PromoXu     = 0 ;
	private $TotalZMoney;
    private $UserName  ;
	
	public function getById($uId)
	{
		GLOBAL $CONFIG_DATA;
		$key = $CONFIG_DATA['appKey'].'_'."{$uId}".'___'.'User';
		return DataProvider::get($key);
	}
	
	public function save()
	{
		GLOBAL $CONFIG_DATA;
		$key = $CONFIG_DATA['appKey'].'_'."{$this->Id}".'___'.'User';
		return DataProvider::set($key,$this);
	}
	
	public function addZingXu($xu)
	{
	  $this->ZMoney = $xu ;
	}
	
	public function addTotalZMoney($xu)
	{
	  $this->TotalZMoney = $xu ;
	}
    
    public function saveAddXuInfo($xu)
    {
        $fail = false;
        try
        {
            GLOBAL $CONFIG_DATA;
            $key = $CONFIG_DATA['appKey'].'_'."{$this->Id}".'___'.'AddXuInfo';
            $arr = DataProvider::get($key);
            $arr[]= $xu ;
            DataProvider::set($key,$arr);
        }
        catch(Exception $e)
        {
            $fail = true;
        }
        if($fail)
            return false ;
        return true ;
    }
    public function saveSnapShot($xu)
    {
        $this->ChargeXu += $xu ;
        Zf_log::write_snapshot_log($this->UserName,$this->ChargeXu,$this->PromoXu,$this->Id,'napxu',$xu);
    }
	
}

class DataProvider
{
    static private $oMemBase ;
    static private $Config ;


  static public function init()
  {
      GLOBAL $CONFIG_DATA;
      self::$Config =& $CONFIG_DATA ;
      $option =& self::$Config['Buckets'];
      self::$oMemBase = new Memcache();
      foreach (self::$Config['Servers'] as $server)
      {
         self::$oMemBase->addServer($server[0], $option['Membase'][0], $option['Membase'][1],$server[1]);
      }
  }

  static function get($key)
  {
    return self::$oMemBase->get($key);
  }

  static function set($key,$data)
  {
	return self::$oMemBase->set($key,$data,MEMCACHE_COMPRESSED);
  }

}

DataProvider::init();