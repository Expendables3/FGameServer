<?php

/**
 * User Model
 * @author Toan Nobita
 * 2/9/2010
 */
/*
 $Name ;                    // ten user
 $AvatarPic ;               // avatar user
 $Exp ;                     // diem kinh nghiem
 $Level = 1 ;               // level
 $NewMessage = FALSE ;      // thu moi
 $NewGift = FALSE ;         // qua moi
 $NewDailyQuest = TRUE ;    // daily quest moi
 $Money ;                   // tien
 $ZMoney ;                  // zing xu
 $AvatarType ;              // loai avatar (boy or Girl)
 $Energy ;                  // nang luong
 $LastEnergyTime ;          // lan cuoi update nang luong
 $LastGiftTime = 0 ;        // thoi gian cua lan gui qua cuoi cung cua user
 $AutoId = 10 ;             // AutoId
 $LakeNumb = 1 ;            // so luong ho nuoi ca
 $FoodCount ;               // tong so luong thuc an dang co
 $NumReceiver = 0 ;         // so luong nguoi da duoc nhan qua trong ngay cua User
 $LastGetGiftDay = 0;       // time lan cuoi nhan qua hang ngay  cung xac dinh thoi gian login luon
 $NumOnline = 0 ;           // so ngay online lien tuc
 $TotalZMoney = 0 ;			// 
 $DataVersion = 0 ;
 $MaxFishLevelUnlock = 1 ;
 $SlotUnlock = 4;
 */

class User extends Model
{
	static public $agent = false ; // xac dinh truong hop khan cap
    
    public $Id ;
    public $Name ;
	public $AvatarPic ;
	public $Exp ;
	public $Level = 1 ;
    
	public $Money ;
	public $ZMoney ;
    public $ChargeXu    = 0 ;
    public $PromoXu     = 0 ;
    public $ZMoneyLocked = 0;
    public $Diamond = 0;
    
	public $AvatarType ;
    
	public $Energy ;
  	public $bonusEnergy = 0;
    public $bonusMachine = 0;
    
	public $LastEnergyTime ;
    
	public $AutoId = 100 ;
    public $BoatType = 0;
    public $EnergyBoxTime = 0 ;  
    
	public $LakeNumb = 1 ;
	public $FoodCount ;
    public $EventVersion = 0 ;
    //public $FairyDrop = 0;
    
    public $SpecialItem = array();

    public $BattleStat = array();       // Battle statistic

     
	private $TotalZMoney = 0 ;
	private $DataVersion = 0 ;
    public $LastDayLogin = 0 ;
    private $UserName  ;
    private $AddXuFlag = 0 ;    // de xac dinh xem da xu ly toi muc nao 
    public $FirstAddXu = 0 ; // xac dinh trang thai nap xu lan dau , 0 la chua nap , 1 la da nap , 2 la da nhan qua
    public $FirstAddXuGift = array(); // xac dinh xem qua da nhan cua viec nap xu lan dau
    
    public $Migrated = 0;
    public $CreatedTime = 0;        //with User after 09/04/2012
    
    private $md5Password = "";       
    public $passwordState = PasswordState::IS_UNAVIABLE;
    public $timeStartCrackingPassword = 0;
    public $remainTimesInput = 5;
    public $timeStartBlock = 0;
    
    public $ReputationLevel = 1 ; // cap do danh tieng
    public $ReputationPoint = 0 ; // diem danh tieng
    public $ReputationQuest = array();// quest danh tieng
    
    // khoi tao skill lai ca
    public $Skill = array(
                            Skill::Money => array('Level' => 1,
                                                  'Mastery'=>0),
                            Skill::Level => array('Level' => 1,
                                                  'Mastery'=>0),
                            Skill::Special => array('Level' => 1,
                                                  'Mastery'=>0),
                            Skill::Rare => array('Level' => 1,
                                                  'Mastery'=>0),
                            );
    public $LastSnapshotA1 = 0;
    
	function __construct($uId,$avatarType, $createdTime)
	{
		$this->Name = $name ;
		$this->AvatarType = $avatarType ;
		$conf = & Common :: getConfig('User','User') ;
		$this->Level = $conf['Level'];
		$this->Exp = $conf['Exp'];
		$this->Money = $conf['Money'];
		$this->ZMoney = $conf['ZMoney'];
		$this->Energy = $conf['Energy'];
		$this->FoodCount = $conf['FoodCount'];
		$this->LastEnergyTime = 0 ;
		$this->AutoId = $conf['AutoId'];
		$this->LakeNumb = $conf['LakeNumb'];
        $this->Id = $uId ; 
        $this->CreatedTime = $createdTime;
         
		parent :: __construct($uId) ;
        
        $conf_freeUse = Common::getConfig('Param','Magnet','FreeUse');
        $this->SpecialItem[Type::Magnet] = new Magnet($this->getAutoId(),1,$conf_freeUse);
        $this->LastSnapshotA1 = 0;
	}


	public function isUpdate()
	{
		$ver = intval(Common::getSysConfig('dataVersion'));
		if($this->DataVersion < $ver )
		{
			return true ;
		}
		return false ;
	}
    
    public function updateDataVersion()
    {
        $this->DataVersion = Common::getSysConfig('dataVersion')  ; 
    }
        
    public function openEnergyBox()
    {  
        if($_SERVER['REQUEST_TIME'] > $this->EnergyBoxTime + 24*3600 )
        {
            $this->Energy += Common::getParam(PARAM::MyEnergyBonus);
            $this->EnergyBoxTime = $_SERVER['REQUEST_TIME'] ;
            return true ;
        }
        return false ;
    }
    
    public function checkEnergyBox()
    {  
        if($_SERVER['REQUEST_TIME'] > $this->EnergyBoxTime + 24*3600 )
          return false ;
        return true ;
    }

	// tang full nang luong sau 1 ngay
	public function updateFirstTimeOfDay()
	{
	   $today = date('Ymd', $_SERVER['REQUEST_TIME']) ;
       $lastDayLogin = date('Ymd', $this->LastDayLogin) ;
       if($today != $lastDayLogin)
        {
			$this->Energy = Common::getConfig('MaxEnergy',$this->Level)+$this->bonusEnergy;
            
			//Common::loadLib('Billing');
			//$this->ZMoney = Billing::balance($this->Id);
           
            $oUserProfile = UserProfile::getById($this->Id);
            $yesterday = date('Ymd',$_SERVER['REQUEST_TIME']- 24*60*60) ;

            $oUserProfile = UserProfile::getById($this->Id);         
            $oUserProfile->updateDayOnline($lastDayLogin == $yesterday) ;  
              
            $oUserProfile->updateFirstTimeOfDay();
              
            $oUserProfile->save();
         
            $oStore = Store::getById($this->Id);
            $oStore->updateFirstTimeOfDay();
            $oStore->save() ;
            
            $oQuest = Quest::getById($this->Id);
            $oQuest->updateFirstTimeLogin();
            $oQuest->save();

            $oMinigame = MiniGame::getById($this->Id);
            if(is_object($oMinigame))
            {
                $oMinigame->updateFirstTimeOfDay();
                $oMinigame->save();
            }
           
            $oEvent = Event::getById($this->Id);
            if(is_object($oEvent))
            {
                $oEvent->updateFirstTimeOfDay();            
                $oEvent->save();
            }
                        
            Lake::updateFirstTimeOfDay($this);
          
            $oTrain = TrainingGround::getById(Controller::$uId);
            $oTrain->updateFirstTimeOfDay();
            $oTrain->save();
                         

            $this->getBalance();
            
            // he thong danh tieng
            if (Event::checkEventCondition('ReputationQuest'))
            {
                $this->subtractReputationPoint();
                $this->initReputationQuest();
            }
            //-----------
            // #NOEL2012
            $oKeepLogin = KeepLogin::getById(Controller::$uId);
            $oKeepLogin->updateKeepLogin();
            //---          
            
            $oSmashEgg = SmashEgg::getById(Controller::$uId);
            $oSmashEgg->updateFirstTimeOfDay();  
            
            $this->LastDayLogin = $_SERVER['REQUEST_TIME'];
			$this->save();
            return true ;
		}
       return false ;
	}

	public function unlockLake()
	{
		$this->LakeNumb++;
	}

	public function addTotalZMoney($total)
	{
		$this->TotalZMoney = $total ;
	}
    
    public function getTotalZMoney()
    {
        return $this->TotalZMoney;
    }

	public function levelUp()
	{
		$conf = & Common::getConfig('UserLevel');
		$levelTotal = count($conf) ;
        
        //$levelTotal = Common::getParam('LevelLimit');
        
		if($this->Level >= $levelTotal )
		{
			return false ;
		}
      
        $ExpNext = $conf[$this->Level]['NextExp'];
        
		if($this->Exp >= $ExpNext )
		{
			$this->Level ++ ;
            $this->Exp -= $ExpNext; 
			//$this->updateSlotUnlock();
			$this->Energy = Common::getConfig('MaxEnergy',$this->Level) + $this->bonusEnergy;
			return $this->Level ;
		}
		return false ;
	}


	public function addMoney($money,$Source ='')
	{
        $oFishTour = FishTournament::getById(Controller::$uId);
        if($oFishTour != null && $oFishTour->LastJoinTime != null && $oFishTour->LastJoinTime != 0)
        {
            return false;
        }
        
        if($money == 0) return false ;
		if ($this->Money + $money >= 0)
		{
      
            if ($money > 0)
            {
             $oActionQuest = ActionQuest::getInstance();
             $oActionQuest->add('Money', $money);
            }
      
            $this->Money += $money ;
            
            //if(!empty($Source))
            //    Zf_log::write_act_log($this->Id,0,23,'LogMoney',$money,0,$this->Money,$Source);
                
            return true ;
		}
		else
		return false ;
	}
    
    public function addDiamond($amount,$TypeLog = '')
    {
        if ($amount==0)
            return false;
        if ($this->Diamond + $amount >=0)
        {
            $this->Diamond += $amount;
            if(!empty($TypeLog))
                Zf_log::write_act_log(Controller::$uId,0,20,'DiamondLog',0,0,$amount,$TypeLog,$this->Diamond);
            return true;
        }
        else return false;
    }
    
    
    public function getMaxEnergy()
    {
        $conf_MaxEnergy = Common::getConfig('MaxEnergy');  
        $maxEnergy = $conf_MaxEnergy[$this->Level] + $this->bonusEnergy;   
        return $maxEnergy;
    }
 
	public function addZingXu($xu,$Info = '::')
	{   
        $oFishTour = FishTournament::getById(Controller::$uId);
        if($oFishTour != null && $oFishTour->LastJoinTime != null && $oFishTour->LastJoinTime != 0)
        {
            return false;
        }
        
        if($xu == 0) return false ; 
		if($this->ZMoney + $xu < 0 ) return false ;
        
        if(Common::getSysConfig('paymentDev'))
        {
            $this->ZMoney += $xu ;
            return true ;  
        }
        
        $money = -1 ;
        try
        {
            Common::loadLib('Billing');     
            // Pay Xu
            if($xu < 0 )
            {
                $money = Billing::purchase($this->Id,$this->UserName,-$xu,$Info);
                
                $total =  $this->ChargeXu + $this->PromoXu ;
                $spentCharge = 0;
                $spentPromo = 0;
                if($total + $xu >= 0)
                {
                    $subXu = $this->ChargeXu + $xu ;
                    if($subXu >=0)
                    {
                        $this->ChargeXu += $xu ;
                        $spentCharge = -$xu;
                        $spentPromo = 0;
                    }
                        
                    else
                    {
                        $this->ChargeXu = 0 ;
                        $this->PromoXu += $subXu ;
                        $spentCharge = $subXu - $xu;
                        $spentPromo = -$subXu;
                    }
                }
                // write log snapshot 
				$Item		=	explode(':',$Info);
				$ItemQua	=	$Item[2];
				$ItemName	=	$Item[1];
				$ItemID		=	$Item[0];
                Zf_log::write_snapshot_log($this->UserName, round($this->ChargeXu), round($this->PromoXu), -$spentCharge, -$spentPromo, $ItemName.'_'.$ItemID, 'spent', -$xu/$ItemQua, $this->Id);
                
            }
            else if($Info == 'SaveBonus')  // Promo Xu Bonus Level
            {
                $money = Billing::promo($this->Id,$this->UserName,$xu,1);
                $this->PromoXu += $xu ;
                Zf_log::write_snapshot_log($this->UserName, round($this->ChargeXu), round($this->PromoXu), 0, $xu, 'Level', 'promo', 0, $this->Id);
            }
            else if($Info == 'BetaBonus')  // Promo Xu Bonus Beta
            {
               $money = Billing::promo($this->Id,$this->UserName,$xu,2);   
            }  
        }
        catch(Exception $fault)
        {
          $money = -1 ;  
        }
        
        if($money < 0 ) return false ;
        
        self::$agent = true ;  // mark to save All 
         
        $this->ZMoney = $money ;
        
        $this->save() ;
        return true ;
	}
    
    // thuc hien lay ve so xu user nap gan day
    public function addXuInfo()
    {
        $fail = false;
        try
        {
            $arr = DataProvider::get($this->Id,'AddXuInfo');
        }
        catch(Exception $e)
        {
            $fail = true;
        }
        if($fail)
            return false ;
        $totalxu = 0 ;
        $flag = $this->AddXuFlag ;
        for($i = $flag ; $i < count($arr);$i++)
        {
            if(empty($arr[$i]))
                continue ;
            $totalxu += $arr[$i];
            $this->AddXuFlag = $i+1 ;
        }
        return $totalxu ;
    }
    // tang thuong cho viec add xu
    public function promoForAddXu()
    {
        $oAccumulationPoint = AccumulationPoint::getById($this->Id);
        $oAccumulationPoint->checkTime();
        
        $totalxu = $this->addXuInfo();
        if($totalxu <= 0)
            return false ;
        // call function update accumulation Point for User$oAccumulationPoint = AccumulationPoint::getById($this->Id);        
        $oAccumulationPoint->updatePoint($totalxu);        
        //---                                
        // tang 20% gia tri the nap
        $conf = Common::getConfig('General','PromoXu');
        $BeginTime = $conf['BeginTime'];
        $ExpireTime = $conf['ExpireTime'];
        $promoRate = $conf['PromoXuRate'];
        $now = $_SERVER['REQUEST_TIME'];
        if($now < $BeginTime || $now > $ExpireTime )
            return false ;
        $promoXu = round($totalxu*$promoRate/100);
        $result = $this->addZingXu($promoXu,'SaveBonus');
        
        if($result)
            Zf_log::write_act_log($this->Id,0,10,'PromoXu',0,$promoXu,$this->Level);
            
        return true ;
    }
	public function getLevel()
	{
		return $this->Level;
	}

	public function addExp($exp)
	{
		if ($exp > 0) 
        {
            $this->Exp += $exp ;
        }
	}
    
    /**
    * Get Limited Exp count down
    * 
    */
    
    public function getMaxExp()
    {
        $maxEnergy = 0;
        $oMachine = $this->SpecialItem['EnergyMachine'];
        if (is_object($oMachine)) 
        {
            $conf_param = Common::getConfig('EnergyMachine');
            $maxEnergy += $conf_param[$oMachine->Type]['Buff'];     
        }
        
        $conf_maxE = Common::getConfig('MaxEnergy');
        $maxEnergy += $conf_maxE[$this->Level];
           
        return $maxEnergy;
    }
    
    public function getBalance()
    { 
        if(Common::getSysConfig('debug'))
        { 
          return $this->ZMoney ;  
        }
        
        $money = -1 ;
        try
        {
          Common::loadLib('Billing'); 
          $money = Billing::balance($this->Id,$this->UserName);  
        }
        catch(Exception $fault)
        {
          $money = -1 ;  
        }
        
        if($money < 0 ) return $this->ZMoney ;
        $this->ZMoney = $money ;
        $this->save();
        return $money ;
    }

	/**
	 * Tinh toan muc nang luong hien tai va add nang luong cho User
	 * @param $ener Nang luong muon them vao
	 * author : Hungnm2 , edited by Toan Nobita
	 * 9/9/2010
	 */

	public function addEnergy($ener)
	{
    // happy day
    $happydayEner = Common::bonusHappyWeekDay('energy');
    if($happydayEner && (-$happydayEner > $ener))
        $ener = -$happydayEner;
        
		$conf_param = & Common::getParam() ;
		$conf_MaxEnergy = & Common::getConfig('MaxEnergy');
		
		$TIME_UNIT = $conf_param[PARAM::TimeRecoverEnergy] ;
		$enerBonus =  floor(($_SERVER['REQUEST_TIME'] - $this->LastEnergyTime)/$TIME_UNIT );
		$this->Energy += $enerBonus ;
		$this->LastEnergyTime += $enerBonus*$TIME_UNIT ;

		if ($this->Energy >= ($conf_MaxEnergy[$this->Level]+$this->bonusEnergy))
		{
			$this->Energy = $conf_MaxEnergy[$this->Level] +$this->bonusEnergy;
			$this->LastEnergyTime = $_SERVER['REQUEST_TIME'];
		}
        
        
        if ($this->Energy + $this->bonusMachine + $ener < 0)
        {
            return false;
        }
        else 
        {
            if ($ener<0)
            {
                $sub = $this->bonusMachine+$ener;
                if ($sub<0)
                {
                    $this->bonusMachine = 0;
                    $this->Energy += $sub;
                }
                else
                {
                    $this->bonusMachine+=$ener;
                }
            }
            else
                $this->Energy += $ener ;
        }
        
        
        
		if ($this->Energy > $conf_MaxEnergy[$this->Level]+$this->bonusEnergy )
		{
			$this->Energy = $conf_MaxEnergy[$this->Level]+$this->bonusEnergy ;
		}
    
        if ($ener < 0){
            $ooAct = ActionQuest::getInstance();
            $ooAct->add('Energy', abs($ener));
        }
    
		return true ;
	}
    
    public function addBonusMachine($ener)
    {
        $this->updateEnergy();
        $conf_MaxEnergy = Common::getConfig('MaxEnergy');
        
        $this->Energy += $ener;
        $enerLeft = $this->Energy - ($conf_MaxEnergy[$this->Level]+$this->bonusEnergy);
        if ($enerLeft > 0)
        {
            $this->Energy -= $enerLeft;
            $this->bonusMachine += $enerLeft;
        }

        $conf_bonusMachine = Common::getParam('MaxBonusMachine');
        if ($this->bonusMachine > $conf_bonusMachine)
            $this->bonusMachine = $conf_bonusMachine;
        if ($this->bonusMachine < 0)
            $this->bonusMachine = 0;
    }
    
    public function getCurrentEnergy(){
        $conf_param = & Common::getParam() ;
        
        $TIME_UNIT = $conf_param[PARAM::TimeRecoverEnergy] ;
        $enerBonus =  floor(($_SERVER['REQUEST_TIME'] - $this->LastEnergyTime)/$TIME_UNIT );
        
        return ($this->Energy + $enerBonus) ;
    }
    
    // include $this->Energy + $this->bonusMachine
    public function getRealEnergy()
    {
        return $this->getCurrentEnergy() + $this->bonusMachine;
    }
    
    public function updateEnergy()
    {
        $conf_MaxEnergy = Common::getConfig('MaxEnergy'); 
        $curEner = $this->getCurrentEnergy();
        if ($curEner >= ($conf_MaxEnergy[$this->Level]+$this->bonusEnergy))
        {
            $curEner = $conf_MaxEnergy[$this->Level] +$this->bonusEnergy;
        }
        $this->Energy = $curEner;
        $this->LastEnergyTime = $_SERVER['REQUEST_TIME'];
    }

    
    

	public function addFood($food)
	{
		if($this->FoodCount + $food < 0 ) return false ;
        $this->FoodCount += $food ;
	}

   /* 
    public function addFairyDrop($num)
    {
        if ($this->FairyDrop+$num < 0)
            return false;
        $this->FairyDrop += $num;
        return true;
    }
    */
	/**
	 * UserAction
	 * @author ToaTN
	 * 7/11/2010
	 * Decription : thuc hien viec luu bonus nhan duoc cho user
	 */
	public function saveBonus($gif_list)
	{
		if(!is_array($gif_list)) return false ;

		$oStore = Store::getById($this->Id); 
        
		foreach ($gif_list as $key => $Gift )
		{
      
			if(!is_array($Gift) || empty($Gift))
			{
				continue ;
			} 
      
              // Quest
              $ooAct = ActionQuest::getInstance();   
              if ($Gift[Type::ItemType] ==Type::Fish){
                $ooAct->add('NumFish', 1); 
              }
              else if ($Gift[Type::ItemType] ==Type::Material){
                $ooAct->add('NumMaterial', $Gift[Type::Num]);  
              }
      
			switch ($Gift[Type::ItemType])
			{
				case Type::Money :
					$this->addMoney($Gift[Type::Num],'saveBonus');
					break;
				case Type::ZMoney :
					$info = 'SaveBonus';
					$this->addZingXu($Gift[Type::Num], $info);
					break;
                case Type::Diamond :
                    $this->addDiamond($Gift[Type::Num],DiamondLog::SaveBonusInUser);
                    break;
				case Type::Exp :
					$this->addExp($Gift[Type::Num]);
					break;
				case Type::Energy :
					$this->addEnergy($Gift[Type::Num]);
					break;
                case Type::BonusEnergy:
                    $this->addBonusMachine($Gift[Type::Num]);
                    break;
				case Type::Food:
					$num = Common::getConfig('Food',$Gift[Type::ItemId],'Num');
					$this->addFood($num);
					break;
        case Type::FishGift :
        case Type::Fish:     
        case Type::Material:
        case Type::License:
        case Type::Medicine:
        case Type::Viagra:
        case Type::Petrol: 
        case Type::MagicBag:
        case Type::DragonBall:
        case Type::RebornMedicine:
        case FormulaType::Draft:
        case FormulaType::Blessing:
        case FormulaType::GoatSkin:
        case FormulaType::Paper:
        case Type::EnergyItem :
        case Type::ItemCollection:
        case Type::RankPointBottle:
        case Type::GodCharm:
        case Type::Herb:
        case Type::HerbPotion:
        case Type::HerbMedal: 
        case Type::OccupyToken:
        case Type::VipMedal:
                $oStore->addItem($Gift[Type::ItemType], $Gift[Type::ItemId], $Gift[Type::Num]);
                $oStore->save();
                break;
        
        case Type::Other:
        case Type::OceanTree:
        case Type::OceanAnimal:
                $oItem = new Item($this->getAutoId(),$Gift[Type::ItemType], $Gift[Type::ItemId]);
                $oStore->addOther($Gift[Type::ItemType],$oItem->Id,$oItem); 
                $oStore->save();
                break ;  
        
        case Type::Ginseng:
        case Type::RecoverHealthSoldier:
        case BuffItem::Samurai:
        case BuffItem::BuffExp:
        case BuffItem::BuffMoney:
        case BuffItem::BuffRank:
        case BuffItem::Resistance:
        case BuffItem::StoreRank:
        case BuffItem::Dice:
                $oStore->addBuffItem($Gift[Type::ItemType], $Gift[Type::ItemId], $Gift[Type::Num]);
                $oStore->save();
                break ;
        case Type::BabyFish :
                $AutoId = $this->getAutoId();
                
                if(intval($Gift['FishType']) == FishType::NORMAL_FISH)
                {
                    $oFish = new Fish($AutoId,$Gift[Type::ItemId],$Gift['Sex'],$Gift['ColorLevel']);      
                }
                else if(intval($Gift['FishType']) == FishType::SPECIAL_FISH)
                {
                    $oFish = new SpecialFish($AutoId,$Gift[Type::ItemId],$Gift['Sex'],$Gift['RateOption'],$Gift['ColorLevel']);
                }
                else if(intval($Gift['FishType']) == FishType::RARE_FISH)
                {
                    $oFish = new RareFish($AutoId,$Gift[Type::ItemId],$Gift['Sex'],$Gift['RateOption'],$Gift['ColorLevel']);
                }

                $oStore->addFish($AutoId,$oFish);
                $oStore->save();
                break;
                
        case Type::Gem :
                if(intval($Gift['Day']) <= 0)
                    $Gift['Day'] = 7 ;
                if(empty($Gift['Element']))
                    $Gift['Element'] = rand(1,5);
                $oStore->addGem($Gift['Element'],$Gift[Type::ItemId],$Gift['Day'],$Gift['Num']);
                $oStore->save();                
                break;
                        
        case Type::Sparta :            
        case Type::Superman :
        case Type::Firework :
        case Type::Swat:
        case Type::Ironman:
        case Type::Spiderman:
        case Type::Batman:
            $conf = Common::getConfig('SuperFish',$Gift[Type::ItemType]); 
            if(empty($conf))
                continue ;
            $autoId = $this->getAutoId() ;
            $objectFish = new Sparta($autoId,$conf['Option'],$conf['Expired'],$Gift[Type::ItemType]);
            $oStore->addOther($Gift[Type::ItemType],$objectFish->Id,$objectFish); 
            $oStore->save();                
            break;
        
        case Type::EnergyMachine:     
            $autoId = $this->getAutoId();
            $oMachine = new EnergyMachine($autoId);
            $this->SpecialItem['EnergyMachine'] = $oMachine;
            
            break;
            
       case Type::Soldier :
            $oStore = Store::getById(Controller::$uId);
            $Gift['Element'] = (empty($Gift['Element'])) ? ((empty($Gift['ItemId'])) ? rand(1,5) : $Gift['ItemId']) : $Gift['Element'];
            $Gift['RecipeType'] = (empty($Gift['RecipeType'])) ? ((empty($Gift['SoldierType']) ? FormulaType::Draft : $Gift['SoldierType'])) : $Gift['RecipeType'];
            $oStore->createSoldierByRecipe($Gift['RecipeType'],$Gift['Element'],SoldierType::MATE,$Gift['Num']);
            $oStore->save();
            break;
            
       case SoldierEquipment::Armor:
       case SoldierEquipment::Belt:
       case SoldierEquipment::Bracelet:
       case SoldierEquipment::Helmet:
       case SoldierEquipment::Necklace:
       case SoldierEquipment::Ring:
       case SoldierEquipment::Weapon:
            $oStore = Store::getById(Controller::$uId);
            if(isset($Gift['Source']))
                $source = $Gift['Source'];
            else $source = SourceEquipment::EVENT;
            
            if(isset($Gift['Element']))
                $Element = $Gift['Element'];
            else 
                $Element = rand(1,5);
            
            for ($i=0; $i< $Gift['Num']; $i++)
            {
                
                $oEquip = Common::randomEquipment($this->getAutoId(),$Gift['Rank'],$Gift['Color'],$source,$Gift[Type::ItemType],0,$Element);
                $oStore->addEquipment($Gift[Type::ItemType],$oEquip->Id,$oEquip);    
            }
            
            $oStore->save();
            break;
      
      case SoldierEquipment::Seal:
            $oSeal = new Seal(SoldierEquipment::Seal,$this->getAutoId(),$Gift['Rank'],$Gift['Color']);
            $oStore->addEquipment(SoldierEquipment::Seal,$oSeal->Id, $oSeal);
            $oStore->save();
            break;      
       case Type::RandomEquipment:   

            $arrEquip = array(SoldierEquipment::Armor,SoldierEquipment::Belt,SoldierEquipment::Bracelet,SoldierEquipment::Helmet,SoldierEquipment::Necklace,SoldierEquipment::Ring,SoldierEquipment::Weapon);
            $Gift[Type::ItemType] = $arrEquip[array_rand($arrEquip)];
            $arrGift = array($Gift);
            $arrGift = Equipment::mappingLevelToRankEquipment($arrGift,rand(1,5));
            $Gift = $arrGift[0];
            $conf_equip = Common::getConfig('Wars_'.$Gift[Type::ItemType]);
            $conf_equip = $conf_equip[$Gift['Rank']][$Gift['Color']];
            if(isset($Gift['Source']))
                $source = $Gift['Source'];
            else $source = SourceEquipment::EVENT;
            
            
            for ($i=0; $i< $Gift['Num']; $i++)
            {
                $oEquip = new Equipment($this->getAutoId(),$conf_equip['Element'],$Gift[Type::ItemType],$Gift['Rank'],$Gift['Color'],rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'], $source);
                $oStore->addEquipment($Gift[Type::ItemType],$oEquip->Id,$oEquip);    
            }
            break;
            
      case   Type::PowerTinh:
      case   Type::Jade:
      case   Type::Iron:
      case   Type::SixColorTinh:
            $oIng = Ingredients::getById(Controller::$uId);
            $oIng->addIngredient($Gift[Type::ItemType],$Gift['Num']);
            $oIng->save();
            break ;
      case Type::SoulRock :
            $oIng = Ingredients::getById(Controller::$uId);
            $oIng->addIngredient($Gift[Type::ItemType],$Gift['Num'],$Gift['ItemId']);
            $oIng->save();
            break ;      
      // Event Halloween
      case 'HalItem':            
           $oStore = Store::getById(Controller::$uId); 
           $oStore->addEventItem(EventType::Halloween, $Gift[Type::ItemType], $Gift['ItemId'], $Gift['Num']);
           $oStore->save();
           break;
      // Event Pattern
      case 'ColPGGift':
      case 'ColPItem':
            $oEvent = Event::getById(Controller::$uId);
            $oEvent->colp_addItem($Gift[Type::ItemType], $Gift['ItemId'], $Gift['Num']);
            $oEvent->save();
            break;
      case 'HammerWhite':
      case 'HammerGreen':
      case 'HammerYellow':
      case 'HammerPurple':
           $oSmashEgg = SmashEgg::getById(Controller::$uId);
           $oSmashEgg->addHammer($Gift[Type::ItemType], $Gift['ItemId'], $Gift['Num']);
           $oSmashEgg->save();                
           break;             

      //#NOEL2012      
       case 'Candy':
       case 'NoelItem':
           $oStore = Store::getById(Controller::$uId); 
           $oStore->addEventItem(EventType::Noel, $Gift[Type::ItemType], $Gift['ItemId'], $Gift['Num']);
           $oStore->save();
           break; 
       // event dao giau vang
        case Type::Island_Item:           
            $oStore->addEventItem(EventType::TreasureIsland,$Gift[Type::ItemType],$Gift[Type::ItemId],$Gift[Type::Num]);
            $oStore->save();
            break;
        case Type::Ticket:
            $oStore->addEventItem(GameType::LuckyMachine,$Gift[Type::ItemType],1, $Gift[Type::Num]);
            $oStore->save();
            break ;
        case Type::Arrow :
            $oStore->addEventItem(EventType::PearFlower,$Gift[Type::ItemType],$Gift[Type::ItemId], $Gift[Type::Num]);
            $oStore->save();
            break;
             
       case 'QWhite':            
       case 'QYellow':            
       case 'QGreen':            
       case 'QPurple':
       case 'QVIP':            
            $oStore = Store::getById(Controller::$uId);     
            $Id = $this->getAutoId();
            $oQuartz = new Quartz($Id, $Gift['ItemId'], $Gift['Type']);
            $oQuartz->Level = !empty($Gift['Level'])? intval($Gift['Level']):1 ;
            $oStore->addQuartz($Gift['Type'], $Id, $oQuartz);
            $oStore->save();
            break ;
       case 'VipTag':
            $oVipBox = VipBox::getById(Controller::$uId);
            $Gift['Num'] = empty($Gift['Num'])? 1: intval($Gift['Num']);
            $oVipBox->addNumKey($Gift['Num']);
            $oVipBox->save();
            break;
       case type::BackGround :
            $Id = $this->getAutoId();  
            $oItem = new Item($Id, $Gift['ItemType'],$Gift['ItemId']) ;
            $oStore->addOther($Gift['ItemType'],$Id,$oItem);            
       
            break;
            
	   default :
		    {
			    continue ;
		    }
			}
		}

		return TRUE ;
	}
  
  public function createSparta(){
      $conf_sparta = Common::getConfig('General','Sparta');
      $rateSparta = $conf_sparta['NumOption'];
      $numOpt = Common::randomIndex($rateSparta);
      $Option = array();
      switch($numOpt){
          case 1 :
            $buff = $conf_sparta['Buff'];
            $opt = array(OptionFish::MONEY=>1, OptionFish::EXP=>1);
            $ranBuff = Common::randomIndex($buff);
            $ranOpt = Common::randomIndex($opt);
            $Option[$ranOpt] = $ranBuff;
            break;
          case 2 :
            $Option[OptionFish::MONEY] = rand(12,15);
            $Option[OptionFish::EXP] = rand(12,15);
            break;
          default :
            $Option[OptionFish::MONEY] = rand(8,11);
            $Option[OptionFish::EXP] = rand(8,11);
            $Option[OptionFish::TIME] = rand(8,11);
            break;
      }
      $expr = $conf_sparta['Expired'];
      $exx = Common::randomIndex($expr);
      $autoId = $this->getAutoId();
      $oSparta = new Sparta($autoId,$Option,$exx);
      return $oSparta;
  }
	public function getAutoId()
	{
		return $this->AutoId++;
	}
    
    public function randomActionGift($FishTypeId)
    {
        $conf_f = Common::getConfig('Fish',$FishTypeId);
        if(!is_array($conf_f))
            return array() ;
        $arr_level = array(50,20,1);
        $level_Fish = 1 ;
        foreach ($arr_level as $key => $value)
        {
            if($conf_f['LevelRequire'] > $value )
            {
                $level_Fish = $value ;
                break ;
            }
        }
        $conf_actionGift = Common::getConfig('ActionGift',$level_Fish);
        foreach ($conf_actionGift as $id => $oGift){
                $arrGift[$id] = $oGift['Rate'];
        }
        $idGift = Common::randomIndex($arrGift);

        if(empty($conf_actionGift[$idGift]['ItemType']))
            return array() ;
            
        $conf_actionGift[$idGift]['Rate'] = 0 ;
        return $conf_actionGift[$idGift];            
    }
    
    

	/**
	 * Update thong tin user tu Zing me
	 * @param
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function updateInfo()
	{
         
		try
        {
           
            if (Common::getSysConfig('userTest'))
            {
                $conf_Name = Common::getConfig('DevName'); 
                $userInfo = array();
                $userInfo[0]['displayname'] = $conf_Name[$this->Id]['displayname'];
                $userInfo[0]['tinyurl'] = '';
                $userInfo[0]['username'] = 'test';    
            }
            else
            {
                $userInfo = ZingApi :: getUserInfo(array($this->Id)) ;       
            }
            
          
        }
        catch(Exception $fault) 
        {
            
            $userInfo = array();
            $userInfo[0]['displayname'] = 'Unknow';
            $userInfo[0]['tinyurl'] = '';
            $userInfo[0]['username'] = 'test';
        }
	
        $userInfo = $userInfo[0];
		$this->Name = $userInfo['displayname'];
		$this->AvatarPic = $userInfo['tinyurl'] ;
    $this->UserName = $userInfo['username'] ;  
		return true ;
	}


	/**
	 * Tra ve danh sach thong tin cua ban ve
	 * @param $refesh xac dinh xem co refresh lai hay ko
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function getFriends($refesh = false)
	{
        if (!$refesh)
        {
            $FriendList = DataProvider :: get(Controller :: $uId, 'Friends','Friends') ;        //get from memcache
            if (!empty($FriendList))
            {
                return $FriendList ;                                                            //has friend_list in memcache
            }
            $FriendIds = DataProvider :: get(Controller :: $uId,'Friends','FriendIds') ;        //get friend_id_list from memcache
        }
        if (empty ($FriendIds))
        {
            if(!Common::getSysConfig('userTest'))
            {
                $FriendIds = ZingApi ::  getFriendList() ;                                      //get friend_id_list from portal
            }
                
            //$FriendIds = $FriendIds['uid'] ;
             if(Common::getSysConfig('userTest')) {
                 $FriendIds = Common::getConfig('UserTest');                                    //get friend_id_list from UserTest
             }
              
              
              //ensure NPC always as Friends
              $FriendIds[] = NPC::NPC_SIGN.$this->Id;
              // end ensure
              
            if (empty ($FriendIds))
            {
                return false ;
            }
            else
            {
                DataProvider :: set(Controller :: $uId,'Friends', $FriendIds,'FriendIds') ;
            }
                
        }

        $FriendList = DataProvider :: get(Model::makeKeys($FriendIds),'User') ;
        
        $arrFriend = array();

		foreach($FriendList as & $oFriend)
		{
		    if(is_object($oFriend))
            {
              //if(empty($oFriend->AvatarPic))
                //continue ;               
                $friend = new stdClass() ;
                $friend->AvatarPic = Common::getSysConfig('userTest') ? '' : $oFriend->AvatarPic ;
                
			    $friend->Id = $oFriend->Id ;
			    $friend->Name = $oFriend->Name ;
			    $friend->Level = $oFriend->Level ;
                $friend->Exp = $oFriend->Exp ;
			    $friend->LastDayLogin = $oFriend->LastDayLogin ;
                
                if (Common::getSysConfig('userTest') && (empty($friend->Name) || ($friend->Name=='Unknow') || ($friend->Name=='Undefine')))
                {
                    $conf_Name = Common::getConfig('DevName'); 
                    $friend->Name = $conf_Name[Controller::$uId]['displayname'];
                }
                 
              $oFriend = User::getById($friend->Id); 
                if (is_object($oFriend))
			        $arrFriend[] = $friend ;
            }
		}
        
		DataProvider :: set(Controller :: $uId,'Friends', $arrFriend,'Friends') ;
        return $arrFriend ;
	}

	                                               /**
     * Kiem tra xem co phai la ban be hay ko
     * @param $friendId la uId cua ban be
     * author : Toan Nobita
     * 9/9/2010
     */

    public function isFriend($friendId)
    {    
        //return true ;
        $FriendIds = DataProvider :: get(Controller :: $uId, 'Friends','FriendIds') ;
        if (empty ($FriendIds))
        {
           if(!Common::getSysConfig('userTest'))
                $FriendIds = ZingApi ::  getFriendList() ;
            //$FriendIds = $FriendIds['uid'] ;

            if (empty ($FriendIds))
                return false ;
            DataProvider :: set(Controller :: $uId,'Friends', $FriendIds,'FriendIds') ;
        }
        if (in_array($friendId,$FriendIds))
                return true ;
        return false ;
    }
    
	public function updateAvatar($avaType)
    {		
        $this->AvatarType = $avaType%2;
        return true;		
	}
    
    public function updateSlotUnlock()
    {
        $userPro = UserProfile::getById($this->Id);
        $conf_slot = Common::getConfig('LevelUnlockSlot');
        if(!isset($conf_slot[$userPro->SlotUnlock+1]))
        {
            return false ;
        }
        $levelNeed = $conf_slot[$userPro->SlotUnlock+1]['LevelRequire'];
        if ($this->Level >= $levelNeed)
        {
			
	        $userPro->SlotUnlock++;
	        $userPro->save();   	
        }
        return true;
    } 
    
   // phan xu ly skill lai ca  
      public function upgradeSkill($skill)
      {
        $this->Skill[$skill]['Level'] ++ ;
        $this->Skill[$skill]['Mastery'] = 0 ; 
      }
      
      public function getSkillEffect($skill)
      {
        $SkillConfig = Common::getConfig($skill,$this->Skill[$skill]['Level']);  
        $effect['Energy'] = intval($SkillConfig['SpendEnergy']);
        $effect['Buff'] = floatval($SkillConfig['Buff']);
        return $effect ;    
      }
      
      public function getSkillBonus($skill,$overUser,$overFish,$fishtype)
      {
        if($overUser > 5 ) $overUser = 5 ;
        if($overFish > 2 ) $overFish = 2 ;
        if($overFish < 0 ) $overFish = 0 ;
        if($fishtype > 2 ) $fishtype = 2 ;
        
        $overUser = 'Level'.$overUser;
        $overFish = 'Over'.$overFish;
        $fishtype = 'FishType'.$fishtype;
        
        $bonus = array();
        $SkillConfig = Common::getConfig('MixFishBonus',$skill);
        switch($skill)
        {
            case Skill::Money :
                    $bonus['Mastery'] = $SkillConfig['Mastery'][$overUser];
                    $bonus['Exp'] = $SkillConfig['Exp'];
                    break;
            case Skill::Level :
                    $bonus['Mastery'] = $SkillConfig['Mastery'][$overUser][$overFish];
                    $bonus['Exp'] = $SkillConfig['Exp'][$overFish];
                    break;
            case Skill::Special :
                    $bonus['Mastery'] = $SkillConfig['Mastery'][$overUser][$fishtype];
                    $bonus['Exp'] = $SkillConfig['Exp'][$fishtype];
                    break;
            case Skill::Rare :
                    $bonus['Mastery'] = $SkillConfig['Mastery'][$overUser][$fishtype];
                    $bonus['Exp'] = $SkillConfig['Exp'][$fishtype];
                    break;
        }
        $bonus['Mastery'] = intval($bonus['Mastery']);
        $bonus['Exp'] = intval($bonus['Exp']);
        return $bonus ; 
      }
      
      public function bonusMastery($skill,$num)
      {
        if($num < 1)
          return false ;
        $this->Skill[$skill]['Mastery'] += intval($num); 
      }
       
      
    public function updateFromMarket()       
    {
        $oMarket = Market::getById(Controller::$uId);
        
        //$preAuto = DataRunTime::get('Market_'.Controller::$uId,true);
        DataRunTime::inc('Market_'.Controller::$uId,1,true);
        
        //$oCurrency = $oMarket->getCurrencyReceive();
        // ?????????????? check expired item
        $currentAuto = DataRunTime::get('Market_'.Controller::$uId,true);
        if ($currentAuto == 1)
        {
            $this->saveBonus($oCurrency);
            //$oMarket->deleteCurrencyReceive();
            $oMarket->save();    
        }
        DataRunTime::dec('Market_'.Controller::$uId,1,true);
    }
    
    // update diem danh tieng
    public function updateReputation($Num)
    {
        if($Num == 0)
            return false;
        $conf = common::getConfig('ReputationInfo');  
          
        $this->ReputationPoint += $Num;
        
        if($Num < 0 ) // tru diem uy danh
        {
            if($this->ReputationPoint < 0)
            {
                if($this->ReputationLevel > 1)
                {
                    $this->ReputationLevel -= 1 ;
                    $this->ReputationPoint = round($conf[$this->ReputationLevel]['RequirePoint'] + $this->ReputationPoint);
                }
                else
                {
                    $this->ReputationPoint = 0 ;
                }                   
            }
        }
        else
        {
            $maxLevel = count($conf);
            if($this->ReputationLevel < $maxLevel)
            {
                if($this->ReputationPoint >= $conf[$this->ReputationLevel]['RequirePoint'])
                {
                    $this->ReputationLevel += 1 ;
                    // thuong diem khi len cap
                    $this->ReputationPoint = $conf[$this->ReputationLevel]['AddPoint'];
                    
                    // khoi tao quest moi
                    $this->initReputationQuest();
                }
            }
        }
        
        return true ;
    }
    
    // tru diem danh tieng khi qua ngay
    public function subtractReputationPoint()  
    {
        if(empty($this->LastDayLogin))
            return false;
        
        $conf = common::getConfig('ReputationInfo');                   
        $subDay =  Common::getDayDifferent($this->LastDayLogin,$_SERVER['REQUEST_TIME']);
        
        for($i = 0; $i < $subDay;$i++)
        {
            $num = intval($conf[$this->ReputationLevel]['SubtractPoint']); 
            if(empty($num))
                continue ;
            $this->updateReputation(-$num);
        }
        return true ;
    }
    
    public function initReputationQuest()
    {
        $conf_quest = Common::getConfig('ReputationInfo',$this->ReputationLevel);
        
        $this->ReputationQuest = array();
        foreach($conf_quest as $index => $arr)
        {
            if(!is_array($arr) || $index == 'SubtractPoint' || $index == 'RequirePoint')
                continue;
            $this->ReputationQuest[$index]['Action']    = $arr['Action'];
            $this->ReputationQuest[$index]['Num']       = 0;
            $this->ReputationQuest[$index]['isGetGift'] = 0;
        }
        
    }
      
    public function updateReputationQuest($ouput ,$method,$input = array())
    {        
        //$oUser = User::getById(Controller::$uId);
        $conf_quest = Common::getConfig('ReputationInfo',$this->ReputationLevel);
        
        foreach($this->ReputationQuest as $id => $oQuest)
        {
            if ($method == $oQuest['Action'])
            {   Debug::log('Quest1');
                if ($conf_quest[$id]['Num'] > $oQuest['Num'])
                {Debug::log('Quest2');
                    $isAdd = 1;
                    // kiem tra dau vao 
                    if(!empty($conf_quest[$id]['InputParam']))
                    {
                        foreach($conf_quest[$id]['InputParam'] as $index => $conf_param)
                        {
                            $ParamName = $conf_param['Name'] ;
                            if(!isset($input[$ParamName]))
                            {
                                Debug::log('Quest2_0_1');   
                                $isAdd = 0;
                                break ;
                            }
                            if($input[$ParamName] != $conf_param['Num'])
                            {
                                Debug::log('Quest2_1_1');   
                                $isAdd = 0;
                                break ;
                            }                           
                        }
                    }
                    Debug::log('Quest3');
                    // kiem tra dau ra
                    if(!empty($conf_quest[$id]['OutputParam']))
                    {
                        foreach($conf_quest[$id]['OutputParam'] as $index => $conf_param)
                        {
                            $ParamName = $conf_param['Name'] ;
                            if(!isset($ouput[$ParamName]))
                            {
                                $isAdd = 0;
                                break ;
                            }
                                
                            if($ouput[$ParamName] != $conf_param['Num'])
                            {
                                $isAdd = 0;
                                break ;
                            }                           
                        }
                    }
                    Debug::log('update'.$isAdd);
                    $this->ReputationQuest[$id]['Num'] += $isAdd;
                }    
            }
        }
        
    }
    
    
     
     ////////////////////////
    
    
    public function getUserName(){
        return $this->UserName;
    }
    
    public function getDataVersion()
    {
        return $this->DataVersion;
    }
    
    public function setDataVersion($DataVersion)
    {
        if ($DataVersion >0)
            $this->DataVersion = $DataVersion;
    }
       

	public static function getById($uId)
	{   
		return DataProvider :: get($uId,__CLASS__) ;
	}


	public static function del($uId)
	{
		return DataProvider :: delete($uId,__CLASS__);
	}
    
    public function setMd5Password($md5Password)
    {
        $this->md5Password = $md5Password;
    }
    
    public function getMd5Password()
    {
        return $this->md5Password;
    }
	
	public function saveGiftFlag($xu)
    {	
			$this->FirstAddXu += $xu;
    }
    public function getFirstAddXu(){
        return intval($FirstAddXu);
    }
}
?>
