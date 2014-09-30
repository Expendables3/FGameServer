<?php

/**
 * Store
 * @author Toan Nobita
 * 28/2/2010
 */

class Store extends Model
{
	public $Items = array();    // ItemType => ItemId ==> Num
	public $Fish = array();		// object BabyFish
	public $AllOther = array();		// tat ca cac loai linh kinh khac
    public $BuffItem = array();
    public $Gem = array();      // thanh = 0, ngoc [Element] => [GemId] => [Day] => [Num] 
    public $UpgradingGem = array();     // list ngoc dang luyen
    public $Equipment = array();
    public $Quartz = array(); // List quartz user receive from smash egg    
    
    public $EventItem = array();
    
	public function __construct($uId)
	{
         $this->Gem = array();
		 parent :: __construct($uId) ;
	}
	
	/*
	 * ham them do linh tinh vao kho
	 * 12/5/2011
	 * AnhBV
	 */
	public function addOther($Type,$id,$oOther)
	{
		if ($id < 1  || !is_object($oOther)||empty($Type)|| !is_string($Type)) 
      		return false ;
		$this->AllOther[$Type][$id] = $oOther ;
		return true ;
	}
	
	/*
	 * ham get do linh tinh
	 * 12/5/2011
	 * AnhBV
	 */
	public function getOther($Type,$id = NULL)
	{
		if (empty($Type)|| !is_string($Type)) 
      		return false ;
      	if (empty($id))
      	{
      		return $this->AllOther[$Type] ;
      	}
      	else if (isset($this->AllOther[$Type][$id]))
      		return $this->AllOther[$Type][$id];
      	return false ;	
	}
	
	/*
	 * su dung do linh tinh
	 * 12/5/2011
	 * AnhBV
	 */
	public function useOther($Type,$id)
	{
		if (empty($Type)|| !is_string($Type)|| $id < 1) 
      		return false ;
		if (!isset($this->AllOther[$Type][$id]))
      		return false ;	
      	unset($this->AllOther[$Type][$id]);
      	return true ;
	}
  
  /**
   * ham them mot Buff Item vao kho
   * 27/7/2010
   */
  public function addBuffItem($itemType,$ItemId,$num)
  {
    if(!BuffItem::checkExist($itemType)) 
      return false ;
    if ($num <=0 ||empty($ItemId)) 
      return false ;
    $this->BuffItem[$itemType][$ItemId] += $num ;
    return true ;
  }
  
  /**
   * ham su dung mot Buff Item trong kho
   * 27/7/2010
   */
  public function useBuffItem($itemType,$ItemId,$num)
  {
    if(!BuffItem::checkExist($itemType)) 
      return false ;
    $num = intval($num);
    if($num < 1 ) return false ;
    if($this->BuffItem[$itemType][$ItemId] < $num )
      return false ;
    $this->BuffItem[$itemType][$ItemId] -= $num ;
    if ($this->BuffItem[$itemType][$ItemId] <=0)
      unset($this->BuffItem[$itemType][$ItemId]);

    return true ;
  }
  
   /**
  * get buff items trong kho
  * 
  */
  public function getBuffItem($itemType, $itemId){
      if (isset($this->BuffItem[$itemType][$itemId])){
        return $this->BuffItem[$itemType][$itemId];    
      } 
      else {
        return false;
      }
  }
  
  /**
     * ham them mot Item vao kho
     * 28/2/2010
     */
    public function addItem($itemType,$ItemId,$num)
    {
        if ($num <=0 || empty($itemType)||empty($ItemId)) 
            return false ;
        $this->Items[$itemType][$ItemId] += $num ;
        return true ;
    }

	/**
	 * su dung mot Item  trong kho
	 * 28/2/2010
	 */
	public function useItem($itemType,$ItemId,$num)
	{
		$num = intval($num);
		if($num < 1 ) return false ;
    	if($this->Items[$itemType][$ItemId] < $num )
		    return false ;
		$this->Items[$itemType][$ItemId] -= $num ;
    	if ($this->Items[$itemType][$ItemId] <=0){
        	unset($this->Items[$itemType][$ItemId]);
    	}
		return true ;

	}
  
  /**
  * get items trong kho
  * 
  */
  public function getItem($itemType, $itemId){
      if (isset($this->Items[$itemType][$itemId])){
        return $this->Items[$itemType][$itemId];    
      } 
      else {
        return -1;
      }
  }
  
  public function getFish($idFish){
      if (isset($this->Fish[$idFish])){
        return $this->Fish[$idFish];    
      } 
      else {
        return false;
      }
    
  }   

	/**
    * AnhBV
    * ham them ca giong vao kho
    * 28/2/2010
    */

    public function addFish($id,$oFish)
    {
        if ($id < 1  || !is_object($oFish)) 
              return false ;
        $this->Fish[$id] = $oFish ;
        return true ;
    }
    
	public function useFish($index)
	{
		if (!isset($this->Fish[$index]))
			return false ;
		unset($this->Fish[$index]);
		return true ;
	}
    
    // them Item cua Event vao trong kho
    public function addEventItem($EventType,$ItemType,$ItemId,$Num)
    {
        if ($Num <=0 ||empty($EventType)|| empty($ItemType)||empty($ItemId)) 
            return false ;
        $this->EventItem[$EventType][$ItemType][$ItemId] += $Num ;
        return true ;
    }
    
    // su dung Item cua Event vao trong kho
    public function useEventItem($EventType,$ItemType,$ItemId,$Num)
    {
        if(intval($Num) < 1 ||empty($EventType)|| empty($ItemType)||empty($ItemId) ) 
            return false ;
        if($this->EventItem[$EventType][$ItemType][$ItemId] < $Num )
            return false ;
        $this->EventItem[$EventType][$ItemType][$ItemId] -= $Num ;
        
        if ($this->EventItem[$EventType][$ItemType][$ItemId] <=0)
        {
            unset($this->EventItem[$EventType][$ItemType][$ItemId]);
        }
        return true ;
    }
  
  public function createSoldierByRecipe($RecipeType,$RecipeId,$SoldierType = SoldierType::MATE ,$Num = 1)
  {
    if(!FormulaType::checkExist($RecipeType)|| $Num < 1)
      return false ;
    $conf = Common::getConfig(Type::MixFormula,$RecipeType,$RecipeId);
    $DamConf = Common::getConfig('Damage',$RecipeType,$RecipeId);  
    $oUser = User::getById(Controller::$uId);
    for($i = 1 ; $i <= $Num ; $i++)
    {
      $autoId       =  $oUser->getAutoId();
      $FishTypeId   =  $conf['FishTypeId'];
      $Rank         =  $conf['Rank']; 
      $LifeTime     =  $conf['LifeTime']; 
      
      $conf_rankpoint = Common::getConfig('RankPoint');
      $arrIndex = array();
      $arrList = Common::getParam('SoldierIndex');
      foreach($arrList as $name)
      {
          $arrIndex[$name] = rand($DamConf[$name]['Min'],$DamConf[$name]['Max']);
          for($i=2; $i<=$Rank; $i++)
          {
              $arrIndex[$name] += ceil($arrIndex[$name]*$conf_rankpoint[$i]['Rate'.$name]);
          }
      }
      $Elements     =  $conf['Elements']; 
      $Recipe       =  array(Type::ItemType=>$RecipeType,Type::ItemId=>$RecipeId);    
      $this->Fish[$autoId] = new SoldierFish($autoId,$FishTypeId,$Rank,$LifeTime,$arrIndex['Damage'],$arrIndex['Defence'],$arrIndex['Critical'],$arrIndex['Vitality'], $Elements,$Recipe,$SoldierType);   
    }
    $oUser->save();
    $this->save();
    return true;
  } 
    
    
    
    public static function getById($uId)
    {
        $data = DataProvider :: get($uId,__CLASS__) ;
        if(!is_object($data))
        {
            $newObject = new Store($uId);
            return  $newObject ;
        }
        return $data ;
    }

    
    

    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }

	public static function init($uId)
	{
		$Conf = Common :: getConfig('User','Store');
		
        $oStore = new Store($uId);
        
        $oUser = User::getById($uId);
		foreach ($Conf as $key => $value)
		{
			if ($value['ItemType'] == Type::BabyFish)
			{
			}
            else if(in_array($value['ItemType'],array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
            {
                  $oItem = new Item($oUser->getAutoId(),$value['ItemType'],$value['ItemId']);
                  $oStore->addOther($value['ItemType'],$oItem->Id,$oItem); 
            }
			else
			{
				$oStore->addItem($value['ItemType'], $value['ItemId'], $value['Num']);
			}
			 
		}
		$oStore->save();
        $oUser->save();
		return $oStore ;

	}
    
    // ham thuc hien viec luu tru qua tang vao kho trong event mua thu 
    public function saveRewardsToStore($Rewards)
    {
        if(empty($Rewards))
            return false ;
                    
        if(!empty($Rewards['SpecialGift']))
        { 
            foreach($Rewards['SpecialGift'] as $id => $object)
            {
                if(!is_object($object))
                    continue ;
                $this->addEquipment($object->Type,$object->Id,$object);
                // log
                $conf_Id = Common::getConfig('LogConfig',$object->Type); 
                Zf_log::write_act_log(Controller::$uId,0,20,'giftFinishMap',0,0,$conf_Id,$object->Rank,$object->Color);    
              
            }
        }
        
        
        if(!empty($Rewards['NormalGift']))
        { 
            $oUser = User::getById(Controller::$uId);                
            $oUser->saveBonus($Rewards['NormalGift']);             
        }
    }
    /**
    * check vs update gem if over days
    * @author hieupt
    * modify: 26/09/2011
    */
    
    public function updateGem()
    {        
        $dayDiff = Common::getDayDifferent($this->Gem['LastUpdateTime'],$_SERVER['REQUEST_TIME']);
        
        if ($dayDiff > 0)
        {            
            $conf_gem = Common::getConfig('Gem');
            $numGem = count($conf_gem)-1;
            $conf_day = Common::getConfig('Param');
            $minDay = -$conf_day['ExpiredGemDay']; 
            
            $newGem = array();
            if(is_array($this->Gem['ListGem']))
                foreach($this->Gem['ListGem'] as $idElement => $olGem)
                {
                    for($gemId=0; $gemId<=$numGem; $gemId++)
                    {                    
                        for ($j=$minDay+1; $j<= $conf_day['NumGemDay']-$dayDiff; $j++)
                        {
                            if (isset($this->Gem['ListGem'][$idElement][$gemId][$j+$dayDiff]))
                                $newGem[$idElement][$gemId][$j] = $this->Gem['ListGem'][$idElement][$gemId][$j+$dayDiff];
                        }
                    }
                } 
            
            $this->Gem['ListGem'] = $newGem;
            $this->Gem['LastUpdateTime'] = $_SERVER['REQUEST_TIME'];
        }    
    }
    
    // update lan dau tien dang nhap cua Store 
    public function updateFirstTimeOfDay()
    {
        // xoa item trong kho khi da het han  
        $this->delItemAtExpired();
        /*
        //thuong ticket sau moi lan dang nhap          
        if(MiniGame::checkMinigameCondition(GameType::LuckyMachine))
        {
            $Eventconf = Common::getConfig('Event',GameType::LuckyMachine);  
            $numTicket = $Eventconf['GiveTicket2'];
            $this->addEventItem(GameType::LuckyMachine,Type::Ticket,1,intval($numTicket));  
        }
*/

        // count Equipment in store
        $totalEquipment = 0;
        $specialEquipment = 0;
        foreach($this->Equipment as $eType => $listType)
        {
            $totalEquipment += count($listType);
            foreach($listType as $id => $oEquip)
            {
                if($id == null || empty($id) || empty ($oEquip))
                    unset($this->Equipment[$eType][$id]);
                    
                $incE = 1;
                if ($oEquip->Color==1 && ($oEquip->Rank % 100 ==1))
                    $incE = 0;
                $specialEquipment += $incE;
            }
        }
        Zf_log::write_act_log(Controller::$uId,0,10,'equipmentAmount',0,0,$totalEquipment,$specialEquipment); 
        
            
    }
    
    // xoa do trang tri khi het han 
    public function delItemAtExpired()
    {
        foreach($this->AllOther as $type => $arr_Item)
        {
            if(in_array($type,array(Type::Other,Type::OceanAnimal,Type::OceanTree)))
            {
                foreach($arr_Item as $index => $object)
                {
                    if(!is_object($object))
                        continue ;
                    $subTime = $_SERVER['REQUEST_TIME']- $object->ExpiredTime ;             
                    if ($subTime >= 7*24*3600) // da het han qua 7 ngay
                    {
                        unset($this->AllOther[$type][$index]);
                    }
                }
            }
        }
        
    }
    
    /**
    * store Gems
    * structure: Element => GenId => Day : Num
    */
    public function addGem($element, $gemId, $day, $num)
    {
        $this->updateGem();
        if ($this->Gem['ListGem'][$element][$gemId][$day] + $num < 0)
            return false;
        
        $this->Gem['ListGem'][$element][$gemId][$day] += $num;
        if ($this->Gem['ListGem'][$element][$gemId][$day]<=0)
            unset($this->Gem['ListGem'][$element][$gemId][$day]);
            
        if (count($this->Gem['ListGem'][$element][$gemId]) <= 0)
            unset($this->Gem['ListGem'][$element][$gemId]);
            
        if (count($this->Gem['ListGem'][$element]) <= 0)
            unset($this->Gem['ListGem'][$element]);
        return true;
    }
    
    /**
    * Chi su dung de tru` gem
    * 
    * @param mixed $element
    * @param mixed $gemId
    * @param mixed $num
    */
    public function removeGemById($element, $gemId, $num)
    {
        $this->updateGem();
        $remain = 0;
        for($i = 1; $i <= 7; $i++)
        {
            if ($this->Gem['ListGem'][$element][$gemId][$i] >= $num)
            {                
                $this->Gem['ListGem'][$element][$gemId][$i] -= $num;
                $num = 0;
                Debug::log('Gem '.$gemId.' remaindays '.$i.' remain '.$this->Gem['ListGem'][$element][$gemId][$i]);
                break;
            }
            else
            {
                if($this->Gem['ListGem'][$element][$gemId][$i] > 0)
                {
                    $num = $num - $this->Gem['ListGem'][$element][$gemId][$i];
                    $this->Gem['ListGem'][$element][$gemId][$i] = 0;
                    Debug::log('Gem '.$gemId.' remaindays '.$i.' remain '.$this->Gem['ListGem'][$element][$gemId][$i]);
                    Debug::log(' num remain = '.$num);
                }
            }
        }
        
        if($num == 0)
        {
            for($day = 1; $day <= 7; $day++)
            {
                Debug::log(' gem '.$element. ' level '.$gemId.' remaindays '.$day.' has '.$this->Gem['ListGem'][$element][$gemId][$day].' object');
                if ($this->Gem['ListGem'][$element][$gemId][$day]<=0)
                {
                    unset($this->Gem['ListGem'][$element][$gemId][$day]);
                }
                if (count($this->Gem['ListGem'][$element][$gemId]) <= 0)
                {
                    unset($this->Gem['ListGem'][$element][$gemId]);
                }
                if (count($this->Gem['ListGem'][$element]) <= 0)
                {
                    unset($this->Gem['ListGem'][$element]);
                }
            }
                
            return true;
        }
            
        return false;
    }
    
    /**
    * get Gem upgraded
    */
    public function getGem($idUpgrading)
    {
        $oGem = $this->UpgradingGem[$idUpgrading];
        // check finished upgrade
        if ($oGem['LevelDone'] > $this->getCurLevelUpgrading($idUpgrading))
            return false;
        
        $element = 1;
        // Get element
        foreach($oGem['ListGem'] as $id => $aGem)
        {
            $element = $aGem['Element'];
        }
        $this->addGem($element,$oGem['LevelDone'],$oGem['Day'],1);
        
        unset($this->UpgradingGem[$idUpgrading]);
        return true;
    }
    
    /**
    * take action upgrade gem
    */
    public function upgradeGem($Element, $GemList, $MaxDay, $minLevel, $LevelDone, $slotId)
    {           
        if (isset($this->UpgradingGem[$slotId]))
            return false;
        
        $this->UpgradingGem[$slotId] = array();
        $this->UpgradingGem[$slotId]['LevelDone'] = $LevelDone;
        $this->UpgradingGem[$slotId]['Time'] = $this->calculateTimeUpgradeGem($GemList,$minLevel,$LevelDone);
        $this->UpgradingGem[$slotId]['StartTime'] = $_SERVER['REQUEST_TIME'];
        $this->UpgradingGem[$slotId]['ListGem'] = $GemList;
        $this->UpgradingGem[$slotId]['Day'] = $MaxDay;
        
        return true;
    }
   
    /**
    * Calculate time upgrade from level min to level done
    */
    public function calculateTimeUpgradeGem($GemList,$minLevel,$LevelDone)
    {

        $conf_gem = Common::getConfig('Gem');
        $time = 0;
        for ($i = $minLevel; $i < $LevelDone; $i++)
            $time += $conf_gem[$i]['TimeUpgrade'];
        
        return $time;
    }
    
    /**
    * Cancel upgrade action
    */
    public function cancelUpgrade($idUpgrading)
    {
        // need check pass day
        $curLevel = $this->getCurLevelUpgrading($idUpgrading);
        if ($curLevel >= $this->UpgradingGem[$idUpgrading]['LevelDone'])    
            return false;
        // get day different
        //$NumDay =  floor((time()-$this->UpgradingGem[$idUpgrading]['StartTime'])/(24*60*60));
        $NumDay = Common::getDayDifferent($this->UpgradingGem[$idUpgrading]['StartTime'],time());
        foreach($this->UpgradingGem[$idUpgrading]['ListGem'] as $id => $oGem)
        {
            $this->addGem($oGem['Element'],$oGem['GemId'],$oGem['Day']-$NumDay,$oGem['Num']);
        }
        unset($this->UpgradingGem[$idUpgrading]);                
        return true;
    }
    
    /**
    * quick upgrade
    */
    
    public function quickUpgrade($idUpgrading, $minGemId)
    {
        $oGem = $this->UpgradingGem[$idUpgrading];
        $time = $this->calculateTimeUpgradeGem($oGem['ListGem'],$minGemId, $oGem['LevelDone']);
        $this->UpgradingGem[$idUpgrading]['StartTime'] -= $time - ($_SERVER['REQUEST_TIME']-$this->UpgradingGem[$idUpgrading]['StartTime']);
        return true;
    }
    
    /**
    * get cost upgrade from Curlevel to Curlevel+NumLevel in Money vs ZMoney
    */
    public function getCostUpgrade($CurLevel,$NumLevel)
    {
        $cost = array();
        $conf_gem = Common::getConfig('Gem');
        for($i=0;$i<$NumLevel;$i++)
        {
            $cost['Money'] += $conf_gem[$CurLevel+$i]['MoneyQuickUpgrade'];        
            $cost['ZMoney'] += $conf_gem[$CurLevel+$i]['ZMoneyQuickUpgrade'];          
        }
        return $cost;
    }
    
    /**
    * get cur level upgrading
    */
    public function getCurLevelUpgrading($idUpgrading)
    {
        $passedTime = $_SERVER['REQUEST_TIME'] - $this->UpgradingGem[$idUpgrading]['StartTime'];    
        foreach($this->UpgradingGem[$idUpgrading]['ListGem'] as $id =>$oGem)
            $CurLevel = $this->UpgradingGem[$idUpgrading]['ListGem'][$id]['GemId'];    
        $conf_gem = Common::getConfig('Gem');
        // decrease time by every level 
        do {
            $passedTime -= $conf_gem[$CurLevel]['TimeUpgrade'];                
            if ($passedTime >= 0)
                $CurLevel++;
        } while ($passedTime > 0 && $CurLevel<=10 && $CurLevel<$this->UpgradingGem[$idUpgrading]['LevelDone']);
        return $CurLevel;
    }
    
    /**
    * add equipment 
    */
    public function addEquipment($equipType, $id, $oEquip)
    {
        if(empty($equipType)||empty($id)|| !is_object($oEquip))
            return false ;
            
        $this->Equipment[$equipType][$id] = $oEquip;
        
        return true ;
    }
    
    /**
    * Remove equipment 
    */
    public function removeEquipment($equipType, $id)
    {
        if(!isset($this->Equipment[$equipType][$id])) 
            return false;
        unset($this->Equipment[$equipType][$id]);
        return true;
    }
    
    public function getEquipment($equipType, $id)
    {
        return $this->Equipment[$equipType][$id];
    }
    
    public function getAllEquipment(){
        return $this->Equipment;
    }
    
    public static function logEquipment($listEquipmentStore, $equipmentSoldier, $listQuartzStore=array())
    {                
        $LastTimeUpdate = DataProvider::getMemcache()->get(Controller::$uId.'_LastTimeLogEquipment');
        $conf_deltaTime = Common::getConfig('Param','DeltaTimeLogEquipment');
        if ($LastTimeUpdate + $conf_deltaTime < $_SERVER['REQUEST_TIME'])
        {            
            $Types = Common::getConfig('General', 'QuartzTypes');
            // log equipment in soldier
            foreach($equipmentSoldier as $idS => $oList)
            {
                foreach($oList['Equipment'] as $type => $listType)
                {
                    foreach($listType as $id => $oEquip)
                    {                        
                        
                        if(in_array($oEquip->Type,$Types)){                               
                             Zf_log::write_quartz_log(Controller::$uId, 0, 19,'logEquipment', 0, 0, $oEquip);  
                        } else {         
                            
                            if (!($oEquip->Color==1 && ($oEquip->Rank % 100 ==1))) {                                
                                Zf_log::write_equipment_log(Controller::$uId, 0, 19,'logEquipment', 0, 0, $oEquip);                             
                            }
                                
                        }
                    }
                }    
            }
            
            // log equipment in store
            foreach($listEquipmentStore as $type => $listType)
            {
                foreach($listType as $id => $oEquip)
                {
                    if (!($oEquip->Color==1 && ($oEquip->Rank % 100 ==1))) {                        
                        Zf_log::write_equipment_log(Controller::$uId, 0, 19,'logEquipment', 0, 0, $oEquip); 
                    }
                        
                }
            }            
            //log quartz in store
            if(count($listQuartzStore)>0) {
                foreach($listQuartzStore as $type => $listType)
                {
                    foreach($listType as $id => $oEquip)
                    {                                        
                        Zf_log::write_quartz_log(Controller::$uId, 0, 19,'logEquipment', 0, 0, $oEquip); 
                    }
                }                
            }
            
            $LastTimeUpdate = DataProvider::getMemcache()->set(Controller::$uId.'_LastTimeLogEquipment',$_SERVER['REQUEST_TIME']);    
        }
        
    }

      public function getEventItem($EventType, $itemType, $itemId){
          if (isset($this->EventItem[$EventType][$itemType][$itemId])){
            return $this->EventItem[$EventType][$itemType][$itemId];    
          } 
          else {
            return -1;
          }
      }

    /**
    *  LIST FUNCTION OF QUARTZ READING
    */
    
    // addQuartz in store
    public function addQuartz($quartzType, $id, $oQuartz)
    {
        if(empty($quartzType)|| empty($id)|| !is_object($oQuartz))
            return false ;
        $this->Quartz[$quartzType][$id] = $oQuartz;
    }
    
    // removeQuartz out store 
    public function removeQuartz($quartzType, $id)
    {
        if(!isset($this->Quartz[$quartzType][$id])) 
            return false;
        unset($this->Quartz[$quartzType][$id]);
        return true;
    }
    
    // getQuartz - get detail info of Quartz
    public function getQuartz($quartzType, $id)
    {
        return $this->Quartz[$quartzType][$id];
    }
    
    // getAllQuartz - get info all quartz
    public function getAllQuartz(){
        return $this->Quartz;
    }
    
    /**
    * END OF QUARTZ READING    
    */
  
    
}
