<?php

/**
 * Item Model
 * @author Toan Nobita
 * 2/9/2010
 */

class Decoration extends Model
{
    public $ItemList = array();
    public $SpecialItem = array();
	public function __construct($uId, $lakeId)
	{
     parent :: __construct($uId,$lakeId) ;
	}
	
	/*
	 * ham them Item dac biet
	 * 13/5/2011
	 * AnhBV
	 */
	public function addSpecialItem($Type,$id,$oObject)
	{
		if ($id < 1  || !is_object($oObject)||empty($Type)|| !is_string($Type)) 
      		return false ;
		$this->SpecialItem[$Type][$id] = $oObject ;
		return true ;
	}
	
	// get doi tuong
	public function getSpecialItem($Type,$id)
	{
		if (empty($Type)|| !is_string($Type)) 
      		return false ;
      	if ($id > 0 && isset($this->SpecialItem[$Type][$id]))
      	{
      		return $this->SpecialItem[$Type][$id] ;
      	}
		return false ;

	}
	// xoa doi tuong
	public function delSpecialItem($Type,$id)
	{
		if ($id < 1 ||empty($Type)|| !is_string($Type)) 
      		return false ;
		unset($this->SpecialItem[$Type][$id]);
		return true ;
	}
    
	// update thoi gian lai cho ca sparta
	public function updateTimeSparta($Type,$id)
	{
    $spartaF = Common::getParam('SpartaFamily');
		if ($id < 1 ||empty($Type)|| !is_string($Type) || !in_array($Type,$spartaF)) 
      		return false ;
		if(!isset($this->SpecialItem[$Type][$id]))
			return false ;
		$this->SpecialItem[$Type][$id]->StartTime = $_SERVER['REQUEST_TIME'];
		return true ;
	}
	
	//update lai thuoc tinh isExpired cho ca sparta
	public Static function updateExpired($ownerId)
	{

        $oOwner = User::getById($ownerId);
        if(!is_object($oOwner)) 
          return false ;
        for ($i = 1; $i<= $oOwner->LakeNumb; $i++)
        { 
          $oLake = Lake::getById($ownerId, $i);
          if (!is_object($oLake))
           continue ;
          $oDecorate = self::getById($ownerId, $i);
          if (!is_object($oDecorate))
           continue ;

          if (!empty($oDecorate->SpecialItem))
          {
             $arr_buff = array(OptionFish::MONEY => 0,OptionFish::EXP => 0,OptionFish::TIME => 0); 
             foreach ($oDecorate->SpecialItem as $Type => $Arr_object) 
             {
                 foreach ($Arr_object as $id => $oSparta) 
                 {
                   if(!is_object($oSparta))
                   {
                      continue ;
                   }
                   if($oSparta->isExpried)
                   {
                     if(!$oSparta->expire())
                     {
                       continue ; // chua het han
                     }
                     
                     if(!$oSparta->isExpried)
                     {       
                         foreach ($oSparta->Option as $key => $value)
                         {
                            $arr_buff[$key] += $value ;
                         }          
                     }
                   } 
                 }
             }
             $oLake->buffToLake($arr_buff, false); 
           }
        $oLake->save();
        $oDecorate->save();
        }
 		return true ;
	}
  
	
	

    /**
	 * Init Item
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */

    public function addItem($autoId,$ItemObject)
    {
    	if ($autoId < 1  || !is_object($ItemObject)) 
      		return false ;
        $this->ItemList[$autoId] = $ItemObject ;
        return true;
    }
    
	public function delItem($Id)
	{
		unset($this->ItemList[$Id]);
	}
	
    public function saveItem($Id,$x,$y,$z)
    {
    	if(isset($this->ItemList[$Id]))
    	{
	        $this->ItemList[$Id]->X = $x;
	        $this->ItemList[$Id]->Y = $y;
	        $this->ItemList[$Id]->Z = $z;
	        return true ;
    	}
    	return false ;
    }
    
    /**
    * get item 
    * 
    * @param mixed $id
    */
    
    public function getItem($id){
        if (isset($this->ItemList[$id])){
            return $this->ItemList[$id];    
        } 
        else {
            return false;
        }
    }
    /**
    * ham thuc hien viec add default item cho Decoration
    * 
    * @param mixed $id
    * @param mixed $ItemType
    * @param mixed $ItemId
    */
    public function createDefaultItem($id,$ItemType,$ItemId) 
    {
        $oItem = new Item($id,$ItemType,$ItemId);
        $this->ItemList[$id] = $oItem ;
    }
    
     
	public static function init($uId, $lakeId)
	{
		$InitItemConfig = Common :: getConfig('User','Item') ;
        $oDeco = new Decoration($uId,$lakeId);
		foreach ($InitItemConfig as $id => $InitItem)
		{
			$oItem = new Item($id,$InitItem['ItemType'],$InitItem['ItemId']);
			$oItem->setState($InitItem);
			$oDeco->ItemList[$id] = $oItem ;
		}
        $oDeco->save();
		return $oDeco ;
	}

	public static function getById($uId, $lakeId)
	{
        return DataProvider::get($uId,'Decoration',$lakeId);
	}  

	public static function del($uId, $lakeId)
	{
	    return DataProvider::delete($uId,'Decoration',$lakeId);
	}

}
