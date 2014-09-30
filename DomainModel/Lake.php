<?php

/**
 * Lake
 * @author Toan Nobita
 * 2/9/2010
 */

class Lake extends Model
{
	public $CleanAmount ;       // int - so lan da lau ho
	public $StartTime ;			// int - thoi gian bat dau unlock ho, co thay doi
    public $StarTimeOriginal;   // int - thoi gian bat dau unlock ho, khong doi
  	public $Level ;				// int - level hien tai cua bo
    public $Option ;			// cac tinh nang buff cho ca ho
    public $FishList = array() ;            // Danh sach ca trong ho
    public $Id ;
    public $Grave = array();
    public $Attack = array();

	function __construct($uId, $lakeId)
	{
		$this->CleanAmount = 0 ;
		$this->StartTime = $_SERVER['REQUEST_TIME'] ;
        $this->StarTimeOriginal = $_SERVER['REQUEST_TIME'] ;
		$this->Level = 1 ;
		$this->Option = array (
				OptionFish::MONEY => 0 ,
				OptionFish::EXP	=> 0 ,
				OptionFish::TIME => 0 ,
			);
        $this->Id =  $lakeId ;
		parent :: __construct($uId,$lakeId);
	}

	public function delFish($Id)
     {
       if(!isset($this->FishList[$Id])) return false ;
       
       // Xoa Option cho Ho`
       $this->updateLakeBuff($Id, false);
       
       // Xoa Fish khoi ho 
       unset($this->FishList[$Id]);
       return true ;  
     }
     
     public function addFish($oFish)
     {
       $oFish->updateLakeKey($this->getMasterKey());
       
       // Add fish vao  ho 
       $this->FishList[$oFish->Id] = $oFish ;
       
       // Add Option cho Ho`
       $this->updateLakeBuff($oFish->Id, true);
       
       
       return true ;  
     }
	
    function getFishCount()
    {
        $count = 0;
        foreach($this->FishList as $id => $oFish){
            if (in_array($oFish->FishType, array(0,1,2)))
                $count++;
        }
            
        return $count;
    }
    
    // get num of soldier in lake
    function getSoldierCount()
    {
        $count = 0;
        foreach($this->FishList as $id => $oFish)
            if ($oFish->FishType == FishType::SOLDIER)
                $count++;
        return $count;
    }
    
    
  public function updateAllBuff()
    {
      $this->Option = array (
                OptionFish::MONEY => 0 ,
                OptionFish::EXP    => 0 ,
                OptionFish::TIME => 0 ,
            );
      foreach($this->FishList as $id => $oFish)
      {
          $this->updateLakeBuff($id,true);
      } 
    }
	
	
	// update buff in lake
	function updateLakeBuff($fishId, $intoLake){

		$oFish = $this->FishList[$fishId];
		
		if ($oFish->FishType == FishType::RARE_FISH){
			
			foreach ($oFish->RateOption as $type => $percent){
				if ($type != OptionFish::MIXFISH){
					if (!$intoLake){
						$newPercent = $this->Option[$type] - $percent;
					}
					else {
						$newPercent = $this->Option[$type] + $percent;	
					}
					if ($newPercent < 0 ) 
						$newPercent = 0 ;
					$this->Option[$type] = $newPercent;
				}
			}

		} 
		else{
			return false;
		} 
		
		return true ;
	}
	
// update buff in lake
	function buffToLake($option, $intoLake)
	{
		if(!is_array($option))	
			return FALSE ;				
		foreach ($option as $type => $percent)
		{
			if ($percent <= 0)
			{
				continue ;
			}
			if (in_array($type, array(OptionFish::MONEY,OptionFish::TIME,OptionFish::EXP),true))
			{
				if (!$intoLake)
				{
					$newPercent = $this->Option[$type] - $percent;
				}
				else {
					$newPercent = $this->Option[$type] + $percent;	
				}
				if ($newPercent < 0 ) 
					$newPercent = 0 ;
				$this->Option[$type] = $newPercent;
			}
			else 
				return false ;
		}
	
		return true ;
	}
	
	
       
    // Lay Option tuong ung duoc buff cua ho hien tai
    
    function getOption($op)
    {
      
      	$conf_param = Common::getParam();
        $maxOp = array(
        OptionFish::EXP => PARAM::MaxExp,
        OptionFish::MONEY => PARAM::MaxMoney,
        OptionFish::TIME => PARAM::MaxTime,
        );
        
      	if ($this->Option[$op] > $conf_param[$maxOp[$op]]){
      		return Common::getParam($maxOp[$op]);
      	} 
      	else if ($this->Option[$op] < 0){
      		return 0;
      	}
      	else{
      		return $this->Option[$op];
      	}
      	 	
    }
   
	function isAddedFish($oFish)
	{
        if ($oFish->FishType==FishType::SOLDIER)
        {
            $conf_max = Common::getConfig('Param','MaxSoldier');
            if ($this->getSoldierCount() >= $conf_max)
                return false;
            else return true;
        }
        else {
            $conf_Lake = Common::getConfig('Lake',$this->Id) ;
            $emtySpace = $conf_Lake[$this->Level]['TotalFish'] - $this->getFishCount() ;
            if ($emtySpace > 0)
                return true ;
            else
                return false ;    
        }
	}


    // kiem tra do ban cua be
    public function getCurrentDirty()
	{

	        $DirtyConfig =  Common::getConfig('Lake');
            $DirtyConfig = $DirtyConfig[$this->Id];
            $DirtyConfig = $DirtyConfig[$this->Level];
            $conf_param = & Common :: getParam();
            $TWO = $DirtyConfig['DirtyTime'];
            $currentTime = time();
            $cleanAmounts   = $this->CleanAmount;
            $startTime      = $this->StartTime;
            $CurrentDirty   =  abs(intval(($currentTime - $startTime)/$TWO) - $cleanAmounts );
            $Max_Dirty = $conf_param[PARAM::MaxDirty]; // =10
            if ($CurrentDirty > $Max_Dirty)
            {
                $this->StartTime += ($CurrentDirty-$Max_Dirty)*$TWO;
                $this-> updateStartTime($this->StartTime);
                $CurrentDirty = $Max_Dirty;
            }
            return $CurrentDirty;
	 }

    public function updateCleanAmount($CleanTimes)
	{
        if($CleanTimes>0)
        {
          $this->CleanAmount += $CleanTimes ;
          return true;
        }
        return false ;

	}
  
    public function updateStartTime($startTime)
	{
        if($startTime>0)
        {
          $this->StartTime = $startTime ;
          return true;
        }
        return false ;

	}

    public function updateLevel()
	{
        $this->Level++;

	}
    
    public function getFish($id)
    {
        if(isset($this->FishList[$id]))
         return $this->FishList[$id];
        else return false ;
    }
    
    // kiem tra va su dung giay phep mo rong ho 
    public static function checkLicense($Num)
    {
    	if ($Num > 0)
        {
	        ('Store');
	        $oStore = Store::getById(Controller::$uId);	        
	        $nLicense = $oStore->getItem(Type::License,1) ;
	        
	    	if ($nLicense < 1 || $nLicense < $Num)
	        {
	            // khong du giay phep
	          	return false;
	        }
	        $oStore->useItem(Type::License,1,$Num);
	        $oStore->save();
	        
        }
        return true ;
    }
    
    // get all lake
    public function getAll($userId = 0){
    	if ($userId == 0){
    		$userId = Controller::$uId;
    	}
    	$oUser = User::getById($userId);
    	$arrLake = array();
	    for ($i = 0;$i < $oUser->LakeNumb ;$i++)
	    {
		    $arrLake[$i]  =  self::getById($userId,$i+1) ;        
	    }	
    	return $arrLake;	
    }
    
    /**
    * Get all soldier in lake
    * @param mixed $isChild =true : return include not mature soldier
    * @param mixed $isDied =true : include died soldier
    * @param mixed $noHealth =true : include no health soldier
    * @author hieupt
    * 19/08/2011
    */
    public static function getAllSoldier($userId = 0, $isChild = true, $isDied = true, $noHealth = true)
    {
        if ($userId==0)
            $userId = Controller::$uId;
        
        $oUser = User::getById($userId);
        $arrSoldier = array();
        for($i=1; $i<=$oUser->LakeNumb; $i++)
        {
            $oFLake = Lake::getById($userId,$i);
            foreach($oFLake->FishList as $id => $oFish)
            if ($oFish->FishType==FishType::SOLDIER)
            {     
                if (!($oFish->Status==SoldierStatus::HEALTHY || $isDied)){
                    continue;
                }   

                $conf_rank = Common::getConfig('RankPoint',$oFish->Rank);
                if (!($oFish->getCurrentHealth() >= $conf_rank['AttackPoint'] || $noHealth)){
                    continue;
                }
                    
  
                $arrSoldier[$i][$id] = $oFish;
            }
        }
        // reuturn array-> LakeId -> SoldierId
        return $arrSoldier;
    }
    
    /**
    * Select max damage soldier in lake
    * @author hieupt
    * 19/08/2011
    */
  
    public static function selectSoldier($userId = 0)
    {
        if ($userId==0)
            $userId = Controller::$uId;
        $listSoldier = Lake::getAllSoldier($userId,false,false,false);
        $selecSoldier = 0;
        $maxDamage = 0;
        $idL = 1;
        $maxRank = 0;
        $maxRankPoint = 0;
        $maxId = 0;
        
        // select soldier : Rank -> RankPoint -> Id
        foreach($listSoldier as $idLake => $listSo)
            foreach($listSo as $id => $oSoldier)
            {
                
                if ($oSoldier->Rank > $maxRank)
                {
                    $maxRank = $oSoldier->Rank;
                    $maxRankPoint = $oSoldier->RankPoint;
                    $maxId = $id;
                    $selecSoldier = $id;
                    $idL = $idLake;
                }
                else if ($oSoldier->Rank == $maxRank)
                {
                    if ($oSoldier->RankPoint > $maxRankPoint)
                    {
                        $maxRank = $oSoldier->Rank;
                        $maxRankPoint = $oSoldier->RankPoint;
                        $maxId = $id;
                        $selecSoldier = $id;
                        $idL = $idLake;    
                    }
                    else if ($oSoldier->RankPoint == $maxRankPoint)
                    {
                        if ($id > $maxId)
                        {
                            $maxRank = $oSoldier->Rank;
                            $maxRankPoint = $oSoldier->RankPoint;
                            $maxId = $id;
                            $selecSoldier = $id;
                            $idL = $idLake;    
                        }
                    }                   
                }
                    
            }
        

        $arrRe = array();
        $arrRe['Soldier'] = $listSoldier[$idL][$selecSoldier];
        $arrRe['LakeId'] = $idL;
        $arrRe['SoldierId'] = $selecSoldier;
        $arrRe['AllSoldier'] = $listSoldier;
        
        if ($selecSoldier==0)
            return false;
        else return $arrRe;
    }
    
    /**
    * Get not soldier fish in lake
    * $isChild=true : include not mature fish
    * @author hieupt
    * 19/08/2011
    */
    public function getNotSoldierFish($isChild=true)
    {
        $arrFish = array();
        foreach($this->FishList as $id => $oFish)
        if ($oFish->FishType != FishType::SOLDIER)
        {
            if (!($oFish->isGrowth() || $isChild))
                continue;
            $arrFish[$id] = $oFish;
        }
        return $arrFish;
    }
    
    /**
    * Take action attack lake
    * @author hieupt
    * 19/08/2011
    */
    public function takeAttackLake($oSoldier)
    {
        $value = array();
        foreach($this->FishList as $id => $oFish){
            if (in_array($oFish->FishType,array(0,1,2)))
            {
                $bonusAtt = $oFish->beingAttacked($oSoldier);
                $value[Type::Money] += $bonusAtt[0][Type::Num];                
            }    
        }

        // return base exp get
        $value[Type::Exp] = ceil(Common::getParam('ExpGetAttack')/100*ceil($oSoldier->Damage/2));
        
        return $value;
    }
    
    
    
    /**
    * Get money can take
    * @author hieupt
    * 24/08/2011
    */
    public function getMoneyAttack($oSoldier)
    {
        $value = 0;
        foreach($this->FishList as $id => $oFish){
            if (in_array($oFish->FishType,array(0,1,2)) && $oFish->isGrowth())
            {
                $value += $oFish->getAttackMoney($oSoldier);             
            }    
        }

        return $value;
    }
    
    
    /**
    * Get money can attack of all fish in lake
    * @author hieupt
    * 19/08/2011
    */
    public function getMoneyLeft()
    {
         $listF = array();
         $conf_max = Common::getParam('MaxAttackFish'); 
         $conf_fish = Common::getConfig('Fish');
          
         foreach($this->FishList as $id => $oFish)
         {
            $maxTake = ceil($conf_fish[$oFish->FishTypeId]['TrustPrice']*$conf_max/100);
            $listF[$id]  = $maxTake - $oFish->MoneyAttacked; 
         }
         
         return $listF;
    }
    
    /**
    * Select random fish to attack
    * @author hieupt
    * 19/08/2011
    */
    public function selectVictim()
    {
        $listFish = $this->getMoneyLeft();
        $idFish = Common::randomIndex($listFish,true);
        return $idFish;
    }
    
    
    
    
    public static function getById($uId, $lakeId)
    {
        return DataProvider::get($uId,'Lake',$lakeId) ;
    }

    public static function del($uId, $lakeId)
    {                                      
        return DataProvider :: delete($uId,'Lake',$lakeId) ;
    }

    public static function init($uId,$LakeId)
    {
        $Conf = Common :: getConfig('User','Lake',$LakeId) ;
        if(!is_array($Conf))
        {
          return false;
        }
        $oLakeNew = new Lake($uId,$LakeId);
        
        $oLakeNew->CleanAmount      = $Conf['CleanAmount'] ;
		$oLakeNew->StartTime        = $Conf['StartTime'] ;
        $oLakeNew->StarTimeOriginal = $Conf['StarTimeOriginal'] ;
		$oLakeNew->Level            = $Conf['Level'] ;
        
        $arrFish = Fish::init();
        foreach($arrFish as $oFish)
           $oLakeNew->addFish($oFish) ; 
           
        $oLakeNew->save();
        
        return $oLakeNew;
    }
    
    
    public static function getListElement($userId)
    {
        $list = array();
        
        $oUser = User::getById($userId);
        for($i=1; $i<=$oUser->LakeNumb; $i++)
        {
            $oLake = Lake::getById($userId,$i);
            foreach($oLake->FishList as $id => $oFish)
            {
                if ($oFish->FishType == FishType::SOLDIER)
                {
                    if (!isset($list[$oFish->Element]))
                        $list[$oFish->Element] = 1;  
                }
            }    
        }
   
        return $list;
    }
    
    public function canSoldierIntoLake($idElement)
    {
        $listEle = $this->getListElement(Controller::$uId);
        $conf_maxEle = Common::getConfig('Param','MaxElementOneLake');
        if (!isset($listEle[$idElement]))
            $listEle[$idElement] = 1;   
        
        // if more than 2 elements in all lake
        if (count($listEle) > $conf_maxEle)
            return false;
        
        // if isset conflict couple of elements in all lake
        $conf_confict = Common::getConfig('Param','Elements','Conflict');
        foreach($conf_confict as $id => $cid)
            if (isset($listEle[$id]) && isset($listEle[$cid]))
                return false;
        
        return true;
    }
    
    public static function updateFirstTimeOfDay($oUser)
    {    
        //log so luong ca linh trong ho 
        $LakeNum =  $oUser->LakeNumb ;
        $isLog = Common::getConfig('User','LogFishWarNum');
        if($isLog)
        {
             $numFish = 0 ;
             for($nl = 1; $nl <= $LakeNum; $nl++)
             {
                 $oLake = self::getById($oUser->Id,$nl);
                 if(!is_object($oLake)) continue ;
                 foreach($oLake->FishList as $id => $oFish)
                 {
                     if(is_object($oFish) && $oFish->FishType == FishType::SOLDIER)
                     {
                         //if($oFish->updateStatus()== SoldierStatus::HEALTHY)
                            $numFish++ ;
                     }
                 }
             }
             
             if($numFish>0)
             {
                Zf_log::write_act_log(Controller::$uId,0,20,'CountFishWarNum',0,0,$numFish,$oUser->Level);
             }
        }
        //===================
    }
    
    /**
    * count all soldier a user having
    * 2012-06-22 
    * @param mixed $userId
    */
    public static function getHavingSoldiersCount($userId)
    {
        if ($userId==0)
            $userId = Controller::$uId;
        
        $oUser = User::getById($userId);
        $count = 0;
        for($i=1; $i<=$oUser->LakeNumb; $i++)
        {
            $oFLake = Lake::getById($userId,$i);
            foreach($oFLake->FishList as $id => $oFish)
            if ($oFish->FishType==FishType::SOLDIER)
            {     
                if (!($oFish->Status==SoldierStatus::HEALTHY || $isDied)){
                    continue;
                }   

                $conf_rank = Common::getConfig('RankPoint',$oFish->Rank);
                if (!($oFish->getCurrentHealth() >= $conf_rank['AttackPoint'] || $noHealth)){
                    continue;
                }
                    
                $count ++;
            }
        }
        
        return $count; 
    }
    
    public static function selectSoldierId($userId)
    {
        if ($userId==0)
            $userId = Controller::$uId;
        $listSoldier = Lake::getAllSoldier($userId,false,false,false);
        $selecSoldier = 0;
        $maxDamage = 0;
        $idL = 1;
        $maxRank = 0;
        $maxRankPoint = 0;
        $maxId = 0;
        
        // select soldier : Rank -> RankPoint -> Id
        foreach($listSoldier as $idLake => $listSo)
            foreach($listSo as $id => $oSoldier)
            {
                
                if ($oSoldier->Rank > $maxRank)
                {
                    $maxRank = $oSoldier->Rank;
                    $maxRankPoint = $oSoldier->RankPoint;
                    $maxId = $id;
                    $selecSoldier = $id;
                    $idL = $idLake;
                }
                else if ($oSoldier->Rank == $maxRank)
                {
                    if ($oSoldier->RankPoint > $maxRankPoint)
                    {
                        $maxRank = $oSoldier->Rank;
                        $maxRankPoint = $oSoldier->RankPoint;
                        $maxId = $id;
                        $selecSoldier = $id;
                        $idL = $idLake;    
                    }
                    else if ($oSoldier->RankPoint == $maxRankPoint)
                    {
                        if ($id > $maxId)
                        {
                            $maxRank = $oSoldier->Rank;
                            $maxRankPoint = $oSoldier->RankPoint;
                            $maxId = $id;
                            $selecSoldier = $id;
                            $idL = $idLake;    
                        }
                    }                   
                }
                    
            }

        if ($selecSoldier==0)
            return false;
        else return array('Id' => $selecSoldier, 'LakeId' => $idL);
    }

}

?>
