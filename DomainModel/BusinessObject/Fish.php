<?php

/**
 * Enum trang thai cua ca
 * @author Toan Nobita
 * 2/9/2010
 */
class FishStatus
{
	const SICK = 0 ;
	const HUNGRY = 1 ;
	const HEALTHY = 2 ;
};



/**
 * Enum cac thoi ki cua ca
 * @author Toan Nobita
 * 2/9/2010
 */
class FishPeriod
{
	const ZERO          = 0 ;
	const ONE           = 1 ;
	const TWO           = 2 ;
	const THREE         = 3 ;
	const MATURE        = 4 ;  // giai doan gan truong thanh
	const OVER_MATURE   = 5; // truong thanh
};

/**
 * Fish Model
 * @author Toan Nobita
 * 2/9/2010
 */
class Fish
{
	public $Sex ;
	public $FeedAmount = 0 ;
	public $LastBirthTime ;
	public $LastPeriodCare = FishPeriod :: ZERO ;        //Giai doan co lan cham soc cuoi cung
	public $FishTypeId ;
	public $StartTime ;
	public $OriginalStartTime;
	public $ThiefList = array();
	public $ColorLevel = 0 ;
	public $ColorEdit  ='';
	public $FishType;        //0 1 2
	public $Level ;            // tuoi cua ca
    public $TotalBalloon ;

	public $Id;
    public $ViagraUsed = 0;
    public $LastTimeViagra = 0;
    public $PocketStartTime = 0 ;
    public $MoneyAttacked = 0;
    public $Material = array();
  
	protected $LakeKey = '' ; // Key de lay object Lake tuong ung
  


	function __construct($Id, $fishTypeId = 1, $sex = 1,$color = 0)
	{
		$this->LastBirthTime = 0 ;
		$this->FishTypeId = $fishTypeId ;
		$this->Sex = $sex ;
		$this->ColorLevel = $color ;
		$this->StartTime = $_SERVER['REQUEST_TIME'] ;
		$this->OriginalStartTime = $_SERVER['REQUEST_TIME'] ;
        $this->PocketStartTime = $_SERVER['REQUEST_TIME'] ;  
		$this->Id = $Id ;

		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		$this->Level = $conf['LevelRequire'] ;
		$this->TotalBalloon = $this->createTotalBalloon($fishTypeId);
		$this->FishType = FishType::NORMAL_FISH ;
	}

	public function updateLakeKey($key)
	{
		$this->LakeKey = $key ;
	}

	public function getLake()
	{
		return StaticCache::get($this->LakeKey);
	}

	/*
	 * cham soc ca ban be
	 * @author ToanTN
	 * 13/09/2010
	 */
	public function careFriendFish()
	{

		$growingPeriod = $this->getGrowingPeriod();
		if ($this->LastPeriodCare >= $growingPeriod)
		{
			 
			return FALSE;
		}
		if ($growingPeriod >= FishPeriod::OVER_MATURE || $growingPeriod <= FishPeriod::ZERO)
		{
			return FALSE;
		}
		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		$PeriodTime = ($conf['MatureTime']/4)*3600 ;
		$conf_param = & Common :: getParam();

		$bonusTime = round($PeriodTime/$conf_param[PARAM::TimeCare]);
		$this->StartTime -= $bonusTime;
		$this->FeedAmount +=  $bonusTime/$conf['EatedSpeed'];
		$this->LastPeriodCare = $growingPeriod;
		return TRUE;
	}

	/**
	 * get Magic cho cac thuoc tinh
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	function __get($property)
	{
		$method = "get{$property}" ;
		if (method_exists($this, $method))
		return $this->$method() ;
		$prop = "$property}" ;
		if (property_exists($this, $prop))
		return $this->$prop ;
	}
  
  
  
  public function canBirth(){
    if ($this->ViagraUsed>1)
      return false;
    if(!$this->isBirthInDay())
      return false;
    return true;
  }

	/**
	 * Xem xem Ca co sinh san duoc lan dau hay ko
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	public function isBirthInDay()
	{
		if (date('dmY', $this->LastBirthTime) === date('dmY', $_SERVER['REQUEST_TIME']))
		{
			return false ;
		}
		if ($this->getHealthStatus() == FishStatus::HUNGRY )
		{
			return false ;
		}
		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		$nLevel = $this->Level - $conf['LevelRequire'];
		if ($this->getGrowingPeriod() < FishPeriod :: OVER_MATURE && $nLevel <= 0 )
		{
			return false ;
		}
		return true ;
	}

    
    /**
    *  Kiem tra xem ca da truong thanh hay chua, bao gom ca quy, dac biet
    * @author hieupt
    * 
    */
    
    
    public function isGrowth()
    {
        $conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
        $nLevel = $this->Level - $conf['LevelRequire'];
        if ($this->getGrowingPeriod() < FishPeriod :: OVER_MATURE && $nLevel <= 0 )
        {
            return false ;
        }
        return true ;
    }

	/**
	 *  Lay Tien cua nguoi choi
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	public function getMoney()
	{
		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		return $conf['Money'];
	}

	/**
	 * Thuc hien sinh san
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	public function birth()
	{
		if ($this->isBirthInDay())
		{
			$this->LastBirthTime = time() ;
            if (date('dmY', $this->LastTimeViagra) != date('dmY', $_SERVER['REQUEST_TIME']))
            {
                $this->LastTimeViagra = 0;
                $this->ViagraUsed = 0;
            }
			return true ;
		}
		else
		return false ;
	}


	/**
	 * Dinh luong gia tri cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	public function getValue()
	{
		$conf = & Common::getConfig('Fish',$this->FishTypeId);
		$Period = $this->getGrowingPeriod();
		if($Period > FishPeriod :: OVER_MATURE )
		{
			$Period_Price = FishPeriod :: OVER_MATURE ;
		}
		else if ($Period <= FishPeriod ::ONE)
		{
			$Period_Price = FishPeriod :: ONE ;
		}
		else
		{
			$Period_Price = $Period;
		}
		$value = 0;
		
		$Age = $this->Level - $conf['LevelRequire'] ;
		
		if ($Age < 1 && $Period_Price < FishPeriod::OVER_MATURE)
		{
			return $this->getCurrentPocketNum()*$conf['StealOnce'];
		}
		$value = $conf['TrustPrice']  ;
    
		$value *= ( 1 + $this->getLake()->getOption(OptionFish::MONEY)/100);
		
    $value += $this->getCurrentPocketNum()*$conf['StealOnce'];
    $value -= $this->MoneyAttacked;     
				
		return round($value) ;
	}
	
	

	/**
	 * Lay Exp cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */
	public function getExp()
	{
		$conf = & Common::getConfig('Fish',$this->FishTypeId);

		if ($this->getGrowingPeriod() < FishPeriod :: OVER_MATURE)
		{
			$exp = 0;
		}
		else
		{
			$exp = $conf['Exp'];
		}

		// chi co ca dac biet va quy thi TotalLevel moi > 0
		$TotalLevel = $this->Level - $conf['LevelRequire'] ;
		if ($TotalLevel > 0)
		{
			$exp = $conf['Exp'];
		}

		$exp *= ( 1 + $this->getLake()->getOption(OptionFish::EXP)/100);
		return $exp ;
	}
	
	/**
	 * Tiem thuoc tang truong cho ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	/*public function stim($TypeId)
	{
		$StimConfig = Common :: getConfig('Medicine', $TypeId) ;
		if (empty ($StimConfig))
		{
			return false ;
		}
		$growPeriod = $this->GrowingPeriod ;
		if(($growPeriod==0)&& $TypeId>4)
		{ // dang giai doan trung va loai thuoc nay chi danh cho
			return false ; // su dung thuoc khong phu hop
		}
		if(($growPeriod>0)&& $TypeId<=4)
		{ // dang giai doan trung va loai thuoc nay chi danh cho
			return false ; // su dung thuoc khong phu hop
		}
		if ($growPeriod > FishPeriod :: MATURE)
		return false ;
		if ($growPeriod === $this->LastPeriodStim)
		return false ;
		$this->LastPeriodStim = $growPeriod ;
		$this->StartTime -= $StimConfig['MedicineTime'];
		return true ;
	} */


	/**
	 * cho ca an
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public function eat($amount)
	{
		if ($amount <= 0)
		return 0 ;

		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;

		$hungryTime = $this->getHungryTime();

		if($hungryTime > $conf['FullFeedAmount']*$conf['EatedSpeed'])
		{
			$hungryTime = $conf['FullFeedAmount']*$conf['EatedSpeed'] ;
		}

		if ($hungryTime < 0)
		{
            $this->StartTime += - $hungryTime ;  
            $EatedMax =  $conf['FullFeedAmount'] ;    	
		}
		else
		$EatedMax = $conf['FullFeedAmount'] - $hungryTime / $conf['EatedSpeed'] ;

		$feedEated = ($EatedMax > $amount ? $amount : $EatedMax) ;
		$this->FeedAmount += $feedEated ;
		return $feedEated ;
	}


	/**
	 * Lay muc do doi cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public function getHungryTime()
	{
		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		$hungryTime = $this->StartTime + $this->FeedAmount * $conf['EatedSpeed'] - $_SERVER['REQUEST_TIME'];
		return $hungryTime ;
	}

	/**
	 * Kiem tra tinh trang tang truong cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public function getHealthStatus()
	{
		$hungryTime = $this->getHungryTime();
		if ($hungryTime < 0)
		return FishStatus :: HUNGRY ;
		return FishStatus :: HEALTHY ;
	}

	/**
	 * Lay thoi ki tang truong cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public function getGrowingPeriod()
	{
		$conf = & Common :: getConfig('Fish',$this->FishTypeId) ;
		$lifeTime = $this->getLifeTime();
		$GrowPeriod = Common :: computePeriod($lifeTime,$conf['Growing']) ;
		if(($GrowPeriod <= 0)) return FishPeriod::ONE ;

		return $GrowPeriod   ;
	}


	// ham lay id ca theo level cua ca
	public function getFishIdByLevel()
	{
		$conf = & Common :: getConfig('Fish');
		$curLevel = $this->Level ;
		$TempFishId = $this->FishTypeId ;
		foreach ($conf as $Fishid => $info)
		{
			if ($info['LevelRequire'] <= $curLevel)
			{
				$TempFishId =  $Fishid ;
			}
		}
		return $TempFishId ;
	}

	/**
	 * Lay thoi ki tang truong cua ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function getLifeTime()
	{
		$conf = & Common :: getConfig('Fish', $this->FishTypeId) ;
		$hungryTime = $this->StartTime + $this->FeedAmount * $conf['EatedSpeed'] - $_SERVER['REQUEST_TIME'];
		if($hungryTime < 0 )
		$lifeTime = ($_SERVER['REQUEST_TIME'] - ($this->StartTime - $hungryTime )) ;
		else
		$lifeTime = $_SERVER['REQUEST_TIME'] - $this->StartTime ;
		$oLake = $this->getLake() ;
		$lifeTime *= 1/(1 - $oLake->getOption(OptionFish::TIME)/100);
		return $lifeTime;
	}

	/**
	 * Thiet lap trang thai tang truong khoi tao cho ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public function setState($InitFish)
	{
		$this->FishTypeId = $InitFish['FishTypeId'];
		$this->Sex = $InitFish['Sex'];
		$this->StartTime = $InitFish['StartTime'];
		$this->FeedAmount = $InitFish['FeedAmount'];
		$this->LastBirthTime = $InitFish['LastBirthTime'];
		$this->LastPeriodStim = $InitFish['LastPeriodStim'];
		$this->LastPeriodCare = $InitFish['LastPeriodStim'];
		$this->OriginalStartTime = $InitFish['OriginalStartTime'];

	}

	/**
	 * Init khoi tao ca
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


	public static function init()
	{
		$InitFishConfig = Common :: getConfig('User','Fish') ;
		$arrFish = array() ;
		foreach ($InitFishConfig as $id =>$InitFish)
		{
			$oFish = new Fish($id,$InitFish['FishTypeId'],$InitFish['Sex'],ColorType::EMPTY_COLOR);
			$oFish->setState($InitFish) ;
			$arrFish[$id] = $oFish ;
		}
		return $arrFish ;
	}

	/**
	 *  Data Mapper
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */


  public static function getById($uId, $lakeId, $fishId)
	{
		$oLake = Lake::getById($uId,$lakeId);
		return $oLake->getFish($fishId);
	}
	 
   
  // nhat tien
  public function collectMoney()
  {
    $PocketTime = Common::getParam('PocketTime');      
    $conf = Common::getConfig('Fish',$this->FishTypeId);
     
    if ($this->TotalBalloon - 1 < 0 )
      return array('Error' => Error::CANT_STEAL_MONEY);

    $this->TotalBalloon -= 1;

    
    $this->PocketStartTime += $PocketTime ;   
    return array('Error'  =>  0);
     
  }
  
  // lay so tien hien tai dang roi ra 
  public function getCurrentPocketNum()
  {
      $configFish = Common::getConfig(Type::Fish,$this->FishTypeId);
      $PocketTime = Common::getParam('PocketTime');      
      $MaxSteal  =  Common::getConfig(Type::Fish,$this->FishTypeId,'MaxSteal');  
      $StealOnce  =  Common::getConfig(Type::Fish,$this->FishTypeId,'StealOnce');
      //$CurrentTimes = min(intval(($_SERVER['REQUEST_TIME']- $this->PocketStartTime)/$PocketTime),$this->TotalBalloon);
      $CurrentTimes = intval(($_SERVER['REQUEST_TIME']- $this->PocketStartTime)/$PocketTime) ; 
      if($CurrentTimes > $this->TotalBalloon )
      {
         $subtime = $CurrentTimes - $this->TotalBalloon ;
         $this->PocketStartTime += $subtime*$PocketTime ;
         $CurrentTimes = $this->TotalBalloon ;  
      }

      $MaxPick = round($MaxSteal/$StealOnce) ; 
      if($CurrentTimes > $MaxPick )
      {
          $sub = $CurrentTimes - $MaxPick ;
          $sub -= 1 ;          
          $this->PocketStartTime += $sub*$PocketTime ;
          $CurrentTimes = $MaxPick ; 
      }    
      return $CurrentTimes ; 
  }

  
	public function createTotalBalloon($fishTypeId)
	{
		$conf = & Common::getConfig('Fish',$fishTypeId);
		return $conf['TotalBalloon'] ;
	}
    
  
    // mateFish

    static function mateFish($uLevel, $minFish, $maxFish, $bonus)
    {

        $conf_mix = Common::getConfig('MixFish');
        $bonus['percentOverLevel'] += $conf_mix[$minFish->FishTypeId]['RateOverMix'];

        // bonus special && rate
        if ($minFish->FishType == FishType::SPECIAL_FISH )
        {

            $bonus['percentSpecial'] += $conf_mix[$minFish->FishTypeId]['RSpecial_Special'];
            $bonus['percentRare'] += $conf_mix[$minFish->FishTypeId]['RSpecial_Rare'];
            $bonus['percentOverLevel'] += $conf_mix[$minFish->FishTypeId]['BonusSpecialOver'];
      
            // bonus min fish
            $bonus['percentRare'] += $minFish->Option['MixFish'];
        }
        else if ($minFish->FishType == FishType::RARE_FISH )
        {

            $bonus['percentSpecial'] += $conf_mix[$minFish->FishTypeId]['RRare_Special'];
            $bonus['percentRare'] += $conf_mix[$minFish->FishTypeId]['RRare_Rare'];
            $bonus['percentOverLevel'] += $conf_mix[$minFish->FishTypeId]['BonusRareOver'];
      
            // bonus min fish
            $bonus['percentRare'] += $minFish->Option['MixFish'];
        }

        if ($maxFish->FishType == FishType::SPECIAL_FISH )
        {

            $bonus['percentSpecial'] += $conf_mix[$maxFish->FishTypeId]['RSpecial_Special'];
            $bonus['percentRare'] += $conf_mix[$maxFish->FishTypeId]['RSpecial_Rare'];
            $bonus['percentOverLevel'] += $conf_mix[$maxFish->FishTypeId]['BonusSpecialOver'];
        }
        else if ($maxFish->FishType == FishType::RARE_FISH )
        {

            $bonus['percentSpecial'] += $conf_mix[$maxFish->FishTypeId]['RRare_Special'];
            $bonus['percentRare'] += $conf_mix[$maxFish->FishTypeId]['RRare_Rare'];
            $bonus['percentOverLevel'] += $conf_mix[$maxFish->FishTypeId]['BonusRareOver'];
        }
   
        // ----------------------- get base bonus over level

        $bonus['percentOverLevel'] += self::getBaseOverLevel($minFish->Level, $uLevel);

        // check max over level
        if ($bonus['percentOverLevel'] > $conf_mix[$minFish->FishTypeId]['MaxOverLevel'])
        $bonus['percentOverLevel'] = $conf_mix[$minFish->FishTypeId]['MaxOverLevel'];

        $result = array();

        //----------------------- random fish type
        $ran = rand(1,10000)/100;
         
        if ($ran <= $bonus['percentRare'])
            $TypeNewFish = FishType::RARE_FISH;
        else if ($ran <= ($bonus['percentRare']+ $bonus['percentSpecial']))
            $TypeNewFish = FishType::SPECIAL_FISH;
        else 
            $TypeNewFish = FishType::NORMAL_FISH;

        // --------------------- random over level
        $ran = rand(1,10000)/100;
        if ($ran <= $bonus['percentOverLevel'])
        {
            $ran2 = rand(1,100);
            if ($ran2 <= $conf_mix[$minFish->FishTypeId]['OverLevel1'])
            $NewFishLevel = $minFish->Level + 1;
            else $NewFishLevel = $minFish->Level + 2;
        }
        else 
        {
            $NewFishLevel = $minFish->Level;
        }
        
        $NewFishLevel = min($NewFishLevel,$uLevel + Common :: getParam(PARAM::MaxLevelOverUser));
 
        $result['TypeFish'] = $TypeNewFish;
        $result['Level'] = $NewFishLevel;
        $result['Percent'] = $bonus;

        return $result;

    }
    
          // get base over level when mate fish
      public static function getBaseOverLevel($LevelFish, $LevelUser)
      {
          if ($LevelFish <= 17)
          {
              $bonusLevel = ($LevelUser-$LevelFish)*10;
              if ($bonusLevel>50)
                  $bonusLevel = 50;
              return $bonusLevel;  
          }
          else 
          {
              if ($LevelFish>$LevelUser)
              {
                  $bonusLevel = ($LevelUser-$LevelFish)*2;
                  return $bonusLevel;
              }
              else
              {
                  return 0;
              }
          }
      }
      
    public static function randOption($type = 2,$level= -1 ,$FishTypeId = -1 )
    {
        if(($level < 1)&&($FishTypeId < 1))
        {
            return array();
        }
        else if($FishTypeId >0)
        {
            $conf_Fish = Common::getConfig('Fish',$FishTypeId);
            if(empty($conf_Fish))
            {
                return array() ;
            }
            $level = $conf_Fish['LevelRequire'];
        }

        if($type == FishType::RARE_FISH) 
            $type = 'RareOption';
        else if($type == FishType::SPECIAL_FISH)
            $type = 'SpecialOption';
        else
        {
            return array();
        }
   
        $OptionConf = Common::getConfig($type);
        $indexLevel = 0 ;
        foreach($OptionConf as $index => $op)
        {
            if($level >= $op['Level'][0] && $level <= $op['Level'][1])
            {
              $indexLevel = $index ;
              break;  
            }
        }
        if($type == 'SpecialOption')
            return $OptionConf[$indexLevel]['Option'];
            
        $opIndex = Common::randomIndex($OptionConf[$indexLevel]['Rate']);
        
        return $OptionConf[$indexLevel][$opIndex]; 
         
    }
    
    public static function getIdByLevel($level)
    {
      // chan ko lai ra ca qua cao vi chua co content
      $level = $level>86 ? 86:$level ;
      //=========
      $result = array ();
      $result['TypeId'] = Common::getconfig('FishLevelIndex',$level); 
      $result['Level'] =  Common::getConfig('Fish',$result['TypeId'],'LevelRequire');
      return $result ;
             
    }
    
    public static function getLevelIndex($level = 1)
    {
        $OptionConf = Common::getConfig('RateOfMaterial');
        $indexLevel = 0 ;
        $arrIndex = array_keys($OptionConf);
        $num = count($arrIndex);
        for($i = $num-1 ; $i >= 0 ; $i -- )
        {
            if($level >= $arrIndex[$i])
            {
              $indexLevel = $arrIndex[$i] ;
              break;  
            }
        }
        return  $indexLevel ; 
    }
    
    public function useViagra(){
        if (date('dmY', $this->LastBirthTime) != date('dmY', $_SERVER['REQUEST_TIME']))
            return false ; 
        if ($this->ViagraUsed>=1)
            return false;
        $this->ViagraUsed++;
        $this->LastTimeViagra = $_SERVER['REQUEST_TIME'];
        $this->LastBirthTime=0;
        return true;
    }
    
    // Soldier attack this fish
    public function beingAttacked($oSoldier)
    {
        $arrBonus = array();
       
        // check fish is growth
        if ($this->isGrowth()) 
        {
            //Debug::log(' oFish = ' . $this->Id);
            // check special / rare fish
            if ($this->FishType!=0)
            {
                $rann = rand(1,100);
                $ran_mater = Common::getConfig('General','BattleRate','Material');
                if ($rann <=$ran_mater)
                {
                    $conf_bonus = Common::getConfig('General');
                    $idMaterial = Common::randomIndex($conf_bonus['BattleBonus'][$oSoldier->Rank][Type::Material]);
                    $arrBonus[1][Type::ItemType] = Type::Material;
                    $arrBonus[1][Type::ItemId] = $idMaterial;
                    $arrBonus[1][Type::Num] = 1;  
                }
                 
            }  
            
            // Get money attack
            $moneyLost = $this->getAttackMoney($oSoldier);
            $this->MoneyAttacked += $moneyLost;
            
            $arrBonus[0][Type::ItemType] = Type::Money;
            $arrBonus[0][Type::ItemId] = 1;
            $arrBonus[0][Type::Num] = $moneyLost;
            Debug::log('Money lost = '. $moneyLost);
        }
        
        
        
        
        return $arrBonus;
    }
    
    public function getAttackMoney($oSoldier)
    {
        $conf_fish = Common::getConfig('Fish',$this->FishTypeId);
        $conf_max = Common::getParam('MaxAttackFish');
        $maxTake = ceil($conf_fish['TrustPrice']*$conf_max/100);
        $conf_rank = Common::getConfig('RankPoint', $oSoldier->Rank);
        
        $moneyLost = 0;
        if ($this->MoneyAttacked < $maxTake)
        {
            $moneyL = ceil($oSoldier->Damage/1000*$conf_rank['Rate']*$conf_fish['TrustPrice']);
            $moneyLost = (($maxTake - $this->MoneyAttacked - $moneyL)>=0) ? $moneyL : ($maxTake-$this->MoneyAttacked);
        }
        
        return $moneyLost;    
    }

    
    // check cac dieu kien khi lai ca co bi kip 
    public static function checkConditionFish($oFish1,$oFish2,$Fish_Conf_1,$Fish_Conf_2)
    {
        // check FishTypeId
        $flag = 1 ;        
        if($oFish1->FishTypeId != $Fish_Conf_1['FishTypeId'] || $oFish2->FishTypeId != $Fish_Conf_2['FishTypeId'])
        {
            if ($oFish1->FishTypeId != $Fish_Conf_2['FishTypeId'] || $oFish2->FishTypeId != $Fish_Conf_1['FishTypeId'])
            {
               return array('Error' => Error :: ID_INVALID) ;          
            }
            $flag = 2 ;        
        }
        // check FishType
        if($flag == 1)
        {
          if($oFish1->FishType != $Fish_Conf_1['FishType'] || $oFish2->FishType != $Fish_Conf_2['FishType'])
          {
              return array('Error' => Error :: TYPE_INVALID) ;
          }
          
          /*
          // check option 1
          if($Fish_Conf_1['FishType']!= FishType::NORMAL_FISH)
          {
            foreach($Fish_Conf_1['Option'] as $key => $value)
            {
                if(empty($value)) continue ;
              if(!(isset($oFish1->RateOption[$key])&& $oFish1->RateOption[$key] == $value))
              {
                return array('Error' => Error :: DIFF_OPTION) ;   
              }
            }
          }
          // check option 2 
          if($Fish_Conf_2['FishType']!= FishType::NORMAL_FISH)
          {
            foreach($Fish_Conf_2['Option'] as $key => $value)
            {
                if(empty($value)) continue ;
              if(!(isset($oFish2->RateOption[$key])&& $oFish2->RateOption[$key] == $value))
              {
                return array('Error' => Error :: DIFF_OPTION) ;   
              }
            }
          }
          
          */
          
        }
        else
        {
          // check FishType 
          if ($oFish1->FishType != $Fish_Conf_2['FishType'] || $oFish2->FishType != $Fish_Conf_1['FishType'])
          {
              return array('Error' => Error :: TYPE_INVALID) ;          
          }
          
          /*
          
          // check option 1
          if($Fish_Conf_2['FishType']!= FishType::NORMAL_FISH)
          {
            foreach($Fish_Conf_2['Option'] as $key => $value)
            {
                if(empty($value)) continue ;
              if(!(isset($oFish1->RateOption[$key])&& $oFish1->RateOption[$key] == $value))
              {
                return array('Error' => Error :: DIFF_OPTION) ;   
              }
            }
          }
          
          // check option 2 
          if($Fish_Conf_1['FishType']!= FishType::NORMAL_FISH)
          {
            foreach($Fish_Conf_1['Option'] as $key => $value)
            {
                if(empty($value)) continue ;
              if(!(isset($oFish2->RateOption[$key])&& $oFish2->RateOption[$key] == $value))
              {
                return array('Error' => Error :: DIFF_OPTION) ;   
              }
            }
          }
          
          */
          
        }
        
        return array('Error' => Error :: SUCCESS) ;
    }   
    
    public function resetGrowTime()
    {
        $this->StartTime        = $_SERVER['REQUEST_TIME'] ;
        $this->PocketStartTime  = $_SERVER['REQUEST_TIME'] ;
        $this->OriginalStartTime  = $_SERVER['REQUEST_TIME'] ;
    }  
      
    public function addMaterial($MaterialId)
    {
        $this->Material[] = $MaterialId ;
    }
    
    public function setproperty($property_Name,$Value)
    {
        if(isset($this->$property_Name))
        {
            $this->$property_Name = $Value ;
        }
    }
    
    public function getUserId()
    {
        //$oFish->updateLakeKey(Model::$appKey.'_'.$desUser.'_'.$mid.'__Lake');
        $arrKey = explode('_',$this->LakeKey);
        return $arrKey[1];
    }
    
}

