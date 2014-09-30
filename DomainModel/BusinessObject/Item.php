<?php

/**
 * Item Model
 * @author Toan Nobita
 * 2/9/2010
 */

class Item 
{
    public $Id ;
	public $ItemType ;
	public $ItemId ;
	public $X ;
	public $Y ;
	public $Z ;
    public $ExpiredTime;
    public $Option;
    


	public function __construct($objectId, $ItemType = Type::Other, $ItemId = 1,$x = 0, $y = 0, $z = 0)
	{
		$this->ItemType = $ItemType ;
		$this->ItemId = $ItemId ;
		$this->X = $x ;
		$this->Y = $y ;
		$this->Z = $z ;     
        $this->Id =  $objectId ;
        $conf = Common::getConfig($ItemType,$ItemId); 
        $this->ExpiredTime =  $_SERVER['REQUEST_TIME'] + $conf['TimeUse'];
        $this->Option = array();
	}

    /**
	 * Thiet lap trang thai khoi tao
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function setState($InitItem)
	{
		$this->ItemType = $InitItem['ItemType'];
		$this->ItemId = $InitItem['ItemId'];
		$this->X = $InitItem['X'];
		$this->Y = $InitItem['Y'];
		$this->Z = $InitItem['Z'];
	}
    
    public function checkExpired()
    {
        if($this->ExpiredTime < $_SERVER['REQUEST_TIME'] )
        {
            return true; // het han
        }
        return false ;
    }
    
    public function updateExpiredTime($time)
    {
        if($time < 1)
            return false ;
        if($_SERVER['REQUEST_TIME'] > $this->ExpiredTime )
        {    
            $this->ExpiredTime = $_SERVER['REQUEST_TIME']+ $time;
        }
        else
        {
            $this->ExpiredTime = $_SERVER['REQUEST_TIME']+ $time + ($this->ExpiredTime - $_SERVER['REQUEST_TIME']);
        }
    }
    
    public function setOption($Option)
    {
        $this->Option = $Option ;
    }
    
	   
}
