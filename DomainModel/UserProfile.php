<?php
  /**
  * @author : ToanTN
  * @version : 0.1
  * 
  */

  Class UserProfile extends Model
  {
      public $NewMail = false ;
      public $NewGift = false ;
      public $NewDailyQuest = false;
      public $ActionInfo = array();
      public $FeedInfo = array();   
      private $NumOnline = 0 ;
           
      public $MaxFishLevelUnlock = 1 ;
      public $SlotUnlock = 3;
     // public $BetaLevel = 0 ;
      public $Event = array();
      public $MaxEnergyUse ;
      
      public $MatLevel = 1;
      public $MatPoint = 0;
      
      public $NumFill=1;
      public $LastFillEnergy = 0;
      public $LastPictureTime = 0;
      public $NumTakePicture = 0;
      
      public $Avatar = array();
      public $Attack = array();
      public $LastUpdateAvatar = 0;
      
      public $EnchantSlot = 8;              // slot unlocked
      public $BonusAttackTime = array();          // bonus time attack
      
      public $limitOnDay = array(); 
      public $limitAllLife = array(); 

      public function __construct($uId)
      {      
        $this->ActionInfo['Receivers'] =  array() ;
        $this->ActionInfo['Senders'] =  array() ;
        $this->ActionInfo['EnergyBox'] =  array() ;  
        $this->ActionInfo['UnlockFishList'] =  array() ;
        $this->ActionInfo['DayGift'] = array();
        $this->ActionInfo['NewUserGiftBag'] = array('Gave'=>0,'LastGetGiftTime' => 0); // tui qua tan thu 
        $this->MaxEnergyUse = array();
        $this->resetMaxEnergyUse();
        parent :: __construct($uId) ;                
      }

      // kiem tra gioi han trong ngay 
      public function checkOverInLimitOnDayList($type,$Max)
      {
          if(empty($type)||$Max <=0)
            return false ;
          if(!isset($this->limitOnDay[$type]))
            return true;
          if($this->limitOnDay[$type] >= $Max)
            return false;
          return true ;
      }
      // update  gioi han trong ngay   
      public function updateInLimitOnDdayList($type,$num)
      {
          if(!empty($type))
            $this->limitOnDay[$type] += $num ;
          
          return $this->limitOnDay[$type] ;
      }
      // reset gioi han trong ngay   
      public function resetLimitOnDayList()
      {
          foreach($this->limitOnDay as $type => $num )
          {
              $this->limitOnDay[$type] = 0 ;
          }
      }
      
      // kiem tra gioi han trong cuoc song
      public function checkLimitAllLife($type,$Max)
      {
          if(empty($type)||$Max <=0)
            return false ;
          if(!isset($this->limitAllLife[$type]))
            return true;
          if($this->limitAllLife[$type] >= $Max)
            return false;
          return true ;
      }
      public function updateLimitAllLife($type,$num)
      {
          if(!empty($type))
            $this->limitAllLife[$type] += $num ;
          
          return $this->limitAllLife[$type] ;
      }
      
      public function resetLimitAllLife($type = null)
      {
          if($type != null)
          {
              if(isset($this->limitAllLife[$type]))
                $this->limitAllLife[$type] = 0 ;    
              return true;
          }
          
          foreach($this->limitAllLife as $type => $num )
          {
              $this->limitAllLife[$type] = 0 ;
          }
          return true ;
      }
      
      
      
 
      public function updateFirstTimeOfDay()
      {
        $this->ActionInfo['Receivers'] =  array() ;
        $this->ActionInfo['Senders'] =  array() ;
        $this->ActionInfo['EnergyBox'] =  array() ;
        
        $this->resetLimitOndayList();
        
        $this->day_updateDayGift();      
        $this->resetMaxEnergyUse(); 
        
        $this->createActionOfDay();
      }
      
      
    // ham thuc hien viec reset lai so luong binh nang luong duoc dung cua user
    public function resetMaxEnergyUse()
    {
    	$conf_EnergyItem = Common::getConfig(Type::EnergyItem);
        $arr = array();
        foreach ($conf_EnergyItem as $ItemId => $ItemInfo)
        {
        	if (empty($ItemInfo['MaxUse'])) continue ;
        	$arr[$ItemId] = $ItemInfo['MaxUse'];
        }
        $this->MaxEnergyUse = $arr ;
    }
    
   // ham thuc hien update lai so luong binh nang luong duoc dung cua user
   public function updateMaxEnergyUse($EnergyItemId,$Num)
    {
    	if (!isset($this->MaxEnergyUse[$EnergyItemId]))
    	{
    		return true ;
    	}
    	if ( $Num > $this->MaxEnergyUse[$EnergyItemId] || $Num < 1)
    	{
    		return FALSE;
    	}
    	$this->MaxEnergyUse[$EnergyItemId] -= $Num ;
    	return true ;
    }
      
    // ham thuc hien viec random qua tang hang ngay cho user
    public function bonusEngeryBox($uId)
    {
       if(isset($this->ActionInfo['EnergyBox'][$uId]))
        return false;
       if(count($this->ActionInfo['EnergyBox']) > Common::getParam(PARAM::MaxEnergyBonus) )
        return false;
       $this->ActionInfo['EnergyBox'][$uId] = true ;
       return true ; 
    }
    
    /**
    * get Daily energy
    */
    public function getDailyEnergy($uId)
    {
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        $LastDay = date('Ymd',$this->ActionInfo['LastTimeDailyEnergy']);
        if ($Today != $LastDay)
            $this->ActionInfo['DailyEnergy'] = array();
            
        if (isset($this->ActionInfo['DailyEnergy'][$uId]))   
            return false;
        
        $arrBonus = array();    
        $conf_max = Common::getConfig('Param','DailyEnergy');
        if (count($this->ActionInfo['DailyEnergy']) >= $conf_max['MaxTimesDailyEnergy'])
            return false;
        else if (count($this->ActionInfo['DailyEnergy']) == $conf_max['MaxTimesDailyEnergy']-1)
        {
            $arrBonus[0] = $conf_max['Special'];       
        }
        
        $this->ActionInfo['DailyEnergy'][$uId] = true;
        $this->ActionInfo['LastTimeDailyEnergy'] = $_SERVER['REQUEST_TIME'];

        $countBonus = count($arrBonus);
        $arrBonus[$countBonus] = $conf_max['Exp'];
        $arrBonus[++$countBonus] = $conf_max['Energy'];

        return $arrBonus;
    }
          
    public function addSenders($id)
      {
         $this->ActionInfo['Senders'][$id] = true ;  
      }
    
    public function updateDayOnline($online = false)
      {
        if($online)  $this->NumOnline ++ ;
        else $this->NumOnline = 1;
        
        // vuot qua 5 ngay 
        if($this->NumOnline > 5)
          $this->NumOnline = 1;
      }
      
    public function checkGiftList($ReceiveList = array())
      {
        foreach($ReceiveList as $id)
         if(isset($this->ActionInfo['Receivers'][$id]))
           return false ;
        $total = count($this->ActionInfo['Receivers'])  + count($ReceiveList);
        if($total > Common::getParam(PARAM::NumFriendGift))
            return false ;
        return true ;  
      }
      
    public function saveReceiverGift($ReceiveList)
      { 
        foreach($ReceiveList as $id)
            $this->ActionInfo['Receivers'][$id] = true ;
      }
      
    public function updateBirthFish($FishTypeId)
    {
       $this->ActionInfo['UnlockFishList'][$FishTypeId] = UnlockType::Mix ;
    }

    public function updateMaxFishUnlock($Level){
    	if ($Level > $this->MaxFishLevelUnlock){
    		$this->MaxFishLevelUnlock = $Level;
    	}
    }
    
    public function updateFishUnlock($FishTypeId)
    {
      $this->ActionInfo['UnlockFishList'][$FishTypeId] = UnlockType::ZMoney ; 
    }
    
    public function updateSlotUnlock(){
      $this->SlotUnlock++;
    }
    
    
    public function checkUnlockFish($id)
    {
        if(!isset($this->ActionInfo['UnlockFishList'][$id]))
            return false ;
        return $this->ActionInfo['UnlockFishList'][$id];
    }
    
    public static function getById($uId)
    {
        return DataProvider :: get($uId,__CLASS__) ;
    }


    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }
    
    
    // phan su ly tam phan qua tang hang ngay 
  
    public function day_saveGift($Day)         
    {
      if(!isset($this->ActionInfo['DayGift'][$Day]) || empty($this->ActionInfo['DayGift'][$Day]))
        return false ; 
      $oUser = User::getById($this->uId);  
      
      $node = $this->day_getLastChooseTimes($Day);
      if(empty($node['Bonus']))
      {
        return false ;
      }
      
      $oStore = Store::getById($this->uId); 
      
      if(in_array($node['Bonus']['ItemType'],array(Type::Exp,Type::Material,Type::EnergyItem,Type::Money,Type::Diamond,Type::RankPointBottle),true))
      {
         $arr = array();
         $arr[] = $node['Bonus'] ; 
         $oUser->saveBonus($arr);
      }
      else if(in_array($node['Bonus']['ItemType'],array(Type::RecoverHealthSoldier,BuffItem::Samurai,BuffItem::Resistance),true)) 
      {
           $oStore->addBuffItem($node['Bonus']['ItemType'],$node['Bonus']['ItemId'],$node['Bonus']['Num']);
      }
      else if(in_array($node['Bonus']['ItemType'],array(SoldierEquipment::Bracelet),true))
      {
          $conf_bracelet = Common::getConfig('Wars_Bracelet',$node['Bonus']['ItemId'],$node['Bonus']['Color']) ;
          $dam      = rand($conf_bracelet['Damage']['Min'],$conf_bracelet['Damage']['Max']);
          $defence  = rand($conf_bracelet['Defence']['Min'],$conf_bracelet['Defence']['Max']);
          $Critical = rand($conf_bracelet['Critical']['Min'],$conf_bracelet['Critical']['Max']);
          $oEquip = new Equipment($oUser->getAutoId(),Elements::NEUTRALITY,'Bracelet',$node['Bonus']['ItemId'],$node['Bonus']['Color'],$dam,$defence,$Critical,$conf_bracelet['Durability'],$conf_bracelet['Vitality'],SourceEquipment::DAILYGIFT);
          
          $oStore->addEquipment(SoldierEquipment::Bracelet, $oEquip->Id, $oEquip);
      }
      else if($node['Bonus']['ItemType'] == Type::BabyFish)
      { 
          $Id = $oUser->getAutoId() ;
          $FishTypeId = $node['Bonus']['ItemId'] ;
          $sex = rand(0,1) ;
          
          if($node['Bonus']['FishType'] == FishType::NORMAL_FISH)
          {
              $oFish = new Fish($Id,$FishTypeId,$sex);
          }
          else if($node['Bonus']['FishType'] == FishType::SPECIAL_FISH)
          {
              $option = Fish::randOption(FishType::SPECIAL_FISH,-1,$FishTypeId);
              $oFish = new SpecialFish($Id,$FishTypeId,$sex,$option,rand(0,1));
          }
          else if($node['Bonus']['FishType'] == FishType::RARE_FISH)
          {
              $option = Fish::randOption(FishType::RARE_FISH,-1,$FishTypeId);
              $oFish = new RareFish($Id,$FishTypeId,$sex,$option,rand(0,1));
          }
          $oStore->addFish($oFish->Id,$oFish) ;
          
      }
      else if($node['Bonus']['ItemType'] == Type::Ironman)
      {
        $autoId = $oUser->getAutoId() ;
        $option = array (OptionFish::MONEY=>rand(25,35),OptionFish::EXP=>rand(25,35),OptionFish::TIME=>rand(20,25));
        $oFish = new Sparta($autoId,$option,7,Type::Ironman);

        $oStore->addOther(Type::Ironman,$oFish->Id,$oFish);        
        
        $this->Event['DailyQuest']['Ironman'] += 1; 
      }
      else
        return false ;
          
      $oStore->save(); 
      
      $this->ActionInfo['DayGift'][$Day] = array(); 
               
      return true ;
    }
    
    public function day_chooseAgain($Day) 
    {
       if(!isset($this->ActionInfo['DayGift'][$Day]) || empty($this->ActionInfo['DayGift'][$Day]))
        return false ;
        
       // kiem tra xem da mo het 4 lan chua
       if(isset($this->ActionInfo['DayGift'][$Day][4]))
        return false ; 
       
       // kiem tra tien user
       $oUser = User::getById($this->uId); 
       $lastLevel = $this->ActionInfo['DayGift'][$Day]['Level'];
       $conf_Bonus = $this->day_getConfig($lastLevel,$Day) ;
       if(!is_array($conf_Bonus))
       {
         return false ;
       }
       $lastNode = $this->day_getLastChooseTimes($Day);
       $lastTimes = $lastNode['LastChoose'] ;
       if($lastTimes == 1)
        {
           $Zmoney = $conf_Bonus['FirstTime'] ;      
        }
       else if ($lastTimes == 2)  
       {
         $Zmoney = $conf_Bonus['SecondTime'] ;      
       }
       else if ($lastTimes == 3)  
       {
         $Zmoney = $conf_Bonus['ThirdTime'] ;      
       }
       else return false ;
       $Info = $lastTimes.':chooseAgain_DayBonus:1'; 
       if ($Zmoney > 0 )
       {
         if (!$oUser->addZingXu(-$Zmoney,$Info))
         {
            return false ;
         }
       }
       //-------------------
       //random ra bonus  
       $BonusId = intval($this->day_ranBonus($Day,$lastTimes+1));
       if($BonusId < 1 || $BonusId > 6)
          return false ;    
       if(!$this->day_saveTempBonus($Day,$lastTimes+1,$conf_Bonus,$BonusId))
       {
         return false ;
       }
       $oUser->save();
       $arr_result = array();
      
       $arr_result[Type::ItemType] =   $this->ActionInfo['DayGift'][$Day][$lastTimes+1][Type::ItemType];  
       $arr_result[Type::ItemId] =   $this->ActionInfo['DayGift'][$Day][$lastTimes+1][Type::ItemId];  
       $arr_result[Type::Num] =   $this->ActionInfo['DayGift'][$Day][$lastTimes+1][Type::Num]; 
       $arr_result['BonusId']  =  $BonusId ;
       if($Day == 4) 
        $arr_result['FishType'] =   $this->ActionInfo['DayGift'][$Day][$lastTimes+1]['FishType'];  
        
       return $arr_result ;
    }
    
    // ham thuc hien viec save bonus hang ngay vao UserProfile
    public function day_saveTempBonus($Day,$ChooseTimes,$conf_bonus,$BonusId)
    {
      if($Day <1 ||$Day >5 ||$ChooseTimes < 1 || $ChooseTimes > 4 || !is_array($conf_bonus))
        return false ;
      
      $oUser = User::getById($this->uId); 
      
      // luu lai level khi tao qua
      if(!isset($this->ActionInfo['DayGift'][$Day]['Level']))  
      {
         $this->ActionInfo['DayGift'][$Day]['Level'] = ($oUser->Level > 300)? 300 : $oUser->Level  ;
      }
                
      if($conf_bonus[$BonusId]['ItemType'] == Type::BabyFish && empty($conf_bonus[$BonusId]['ItemId']) )
      {
        $FishTypeId = Common::getConfig('FishIdFollowLevel',$this->ActionInfo['DayGift'][$Day]['Level']);
        $FishTypeId = $FishTypeId < 1? 1: $FishTypeId ;
        
        //chat khong cho ra qua con ca dien
        if($FishTypeId > 79)
          $FishTypeId = 79  ;
          
        $conf_bonus[$BonusId]['ItemId'] = $FishTypeId;
      }
      $conf_bonus[$BonusId]['BonusId'] = $BonusId ; 
      $this->ActionInfo['DayGift'][$Day][$ChooseTimes]= $conf_bonus[$BonusId] ;
      
      return true ;         
    }
        
    // lay lan chon cuoi cung
    public function day_getLastChooseTimes($Day)
    {
      $nodeLast = array('LastChoose'=> 0 , 'Bonus' => array());
      foreach ($this->ActionInfo['DayGift'][$Day] as $times => $value)
      {
        if($times == 'Level') continue ;
        if($nodeLast['LastChoose'] < $times)
         {
           $nodeLast['LastChoose']  = $times ;
           $nodeLast['Bonus']       = $value ;
         } 
      }            
      return $nodeLast ;
    }
    
    // get config cua day gift
    public function day_getConfig($Level,$Day)
    {
      $conf_dayGift = Common::getConfig('DayGift');
      $arr_Level = Common::getParam('Level_DailyBonus');
      $L = 0 ;
      for($i =0 ; $i < count($arr_Level);$i++)
      {
        if ($Level >= $arr_Level[$i])
        {
          $L +=1;
        }
      }
      if($L>count($arr_Level)) $L = count($arr_Level)-1 ;
      if($L<1) $L = 1 ;

      return $conf_dayGift[$L][$Day];
      
    }
        
    public function day_ranBonus($Day,$ChooseTimes = 1)
    {
    
        $oUser = User::getById($this->uId);
        if(!is_object($oUser))
          return false ;
          
        $arrRan = array(1=>0,2=>0,3=>0,4=>0,5=>0,6=>0);
        if($ChooseTimes==1)
            $arrRan = Common::getConfig('General','RateDayGift',1);
        else if ($ChooseTimes>=2)
        {
            $delIndex = array();
            foreach($this->ActionInfo['DayGift'][$Day] as $time => $value)
            {
                $delIndex[] = $this->ActionInfo['DayGift'][$Day][$time]['BonusId'];
                unset($arrRan[$this->ActionInfo['DayGift'][$Day][$time]['BonusId']]); 
            }
                        
            
            $conf_per = Common::getConfig('General','RateDayGift',$ChooseTimes);
            if ($ChooseTimes==2)
            {
                if($oUser->Level>21)                                         
                  $conf_per = $conf_per[1];
                else $conf_per = $conf_per[0];
            }
                       
            $count = 1;
            foreach($arrRan as $index => $value)
            {
                $arrRan[$index] = $conf_per[$count];
                $count++;
            } 
            // gioi han so luong swat
            $SwatNumb = DataRunTime::get(Type::Ironman);
            
            if($this->Event['DailyQuest']['Ironman']>= Common::getConfig('General','RateSwat','UserLimit') || $SwatNumb >= Common::getConfig('General','RateSwat','Total'))
            {
               $arrRan[6] = 0 ;
            }
            
        }
        $giftId = Common::randomIndex($arrRan);         
        return $giftId ;
          
      
    }
    
    
    
    //update daily qift
    public function day_updateDayGift()
    {  
        if($this->NumOnline == 1)
        {
           $this->ActionInfo['DayGift'] = array();   
        }

        $oUser = User::getById($this->uId);
        $conf_Bonus = $this->day_getConfig($oUser->Level,$this->NumOnline);

        $BonusId = $this->day_ranBonus($this->NumOnline,1) ;
        $this->day_saveTempBonus($this->NumOnline,1,$conf_Bonus,$BonusId);

    }
    public function updateNumonline($day)
    {
      if($day < 1 )return false ; 
      $this->NumOnline = $day ;
    }
    
     
 	public function resetDailyBonus()
    {
      foreach( $this->ActionInfo['DayGift'] as $Day => $DayBonus)
      {
          if(empty($DayBonus))
            continue ;
          foreach($DayBonus as $key => $Bonus)
          {
             if($key == 'Level') continue ;
             unset($this->ActionInfo['DayGift'][$Day][$key]);
             $BonusId = intval($this->day_ranBonus($Day,$key));
             if($BonusId < 1 || $BonusId > 6)
                continue ;    
             $conf_Bonus = $this->day_getConfig($this->ActionInfo['DayGift'][$Day]['Level'],$Day) ;    
             if(!$this->day_saveTempBonus($Day,$key,$conf_Bonus,$BonusId))
             {
               continue ;
             }
          }
      }
    }    
    /**
    * anhBv
    * ham thuc hien viec khoi tao cac action voi so lan thuc hien co gioi han 
    * 
    */
    public function createActionOfDay()
    {
      $conf = Common::getConfig('General','ActionOnday');
      foreach($conf as $key => $detail)
      {
        foreach ($detail as $key2 => $value)
        {
           $this->ActionInfo[$key][$key2] = $value ;
        }
      }
            
    }
    
    /**
    * anhBv
    * ham thuc hien viec check va update cac action thuc hien hay chua
    * 
    */
    public function updateActionTimes($Type,$Detail,$Num = 1)
    {
      if(empty($Type)||empty($Detail) || !isset($this->ActionInfo[$Type][$Detail]))
      {
          return false ; 
      }
      if($this->ActionInfo[$Type][$Detail] < $Num)
      {
        return false ;
      }
      $this->ActionInfo[$Type][$Detail] -= $Num ;
      
      return true ;      
    }
    
    /**
    * AnhBV
    * ham thuc hien viec reset tui qua tan thu 
    */
    public function resetNewUserGiftBag()
    {
      $this->ActionInfo['NewUserGiftBag'] = array('Gave'=>0,'LastGetGiftTime' => $_SERVER['REQUEST_TIME']); 
    }
    
    public function updateNewUserGiftBag()
    {
      $this->ActionInfo['NewUserGiftBag']['Gave'] +=1 ;
      $this->ActionInfo['NewUserGiftBag']['LastGetGiftTime'] = $_SERVER['REQUEST_TIME'] ;
    }
    
    public function unlockEnchantSlot() 
    {
        $this->EnchantSlot++;
    }
     
 
  }  
