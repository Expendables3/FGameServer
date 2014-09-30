<?php
/**
 * Description of LakeService
 *
 * @author myFish team kakaka
 */
class CheatService
{
	/**
	 * Cheat thoi gian cho ca
	 * Time = minute !
	 */
	public function cheatTime($UserId, $Time){
		if (empty($UserId))
		$UserId = Controller::$uId;

		$oUser = User::getById($UserId);
		if(!is_object($oUser))
		{
			return array('Error' => 'Not exist user !');
		}
  
		$ooLake = Lake::getById($UserId, 1);
		$arraylake = $ooLake->getAll($UserId);
		foreach($arraylake as $LakeId => $olake)
		{
			if(!is_object($olake))
			{
				continue ;
			}

			$Arr_Fish = $olake->FishList;
			foreach($Arr_Fish as $oFish)
			{
				if(!is_object($oFish))
				{
					continue;
				}
                if ($oFish->FishType==FishType::SOLDIER)
                {
                    $oFish->OriginalStartTime -= $Time*60 ;
                    $oFish->StartTime -= $Time*60 ;
                    $oFish->LastHealthTime -=$Time*60;                                
                }
                else
                {
                   $oFish->StartTime -= $Time*60 ;
                   $oFish->LastBirthTime -= $Time*60 ; 
                }				
			}
			$olake->save();
		}
	  
		return array('SÃ¡ÂºÂ·c, chÃ†Â¡i cheat hÃ¡ÂºÂ£, ko Ã„â€˜c Ã„Æ’n gian 8-x !');
			
	}

	/**
	 * Cheat action cho DailyQuestNew
	 */

	public function cheatDailyQuest($taskId, $num=0, $unlock=false){

		$oQuest = Quest::getById(Controller::$uId);
		if (isset($unlock)){
			$oQuest->UnlockQuest2 = $unlock;
		} else {
			if (empty($taskId))
			return array('Error' => 'Sai param, con gÃƒÂ  >:)');
			 
			$idQuest = $oQuest->getCurrentQuest();
			if (!isset($oQuest->DailyInfo[$idQuest][$taskId])){
				return array('Error' => 'NhÃƒÂ¬n lÃ¡ÂºÂ¡i taskId Ã„â€˜i kÃ†Â°ng !');
			}	else {
				$oQuest->DailyInfo[$idQuest][$taskId]['Num'] = $num;
			}
				
				
		}
		 
		$oQuest->save();
		return array('RÃ¡Â»â€œi, test Ã„â€˜i !');

	}


  
  
	/**
	 *
	 * TÃ¡ÂºÂ¡o ra cÃƒÂ¡ mÃ¡Â»â€ºi
	 * option : 1='Money', 2='Exp' , 3='MixFish', 4='Time', 5='MixSpecial'
	 * optionRate : 1,2,3
	 */
   
	  
  
  /**
  * Tao ca trong kho
  * $FishTypeId : fish type id
  * $fishType : 0-normal 1-special 2-rare
  * $numOption : so luong option cua ca
  */
  
  public function createFish($FishTypeId, $fishType = 0, $numOption){
  
    $UserId = Controller::$uId;
    $oStore = Store::getById($UserId);

    if(empty($FishTypeId))
      $FishTypeId = rand(1,30);
    
    $oUser = User::getById($UserId);
    
    $autoId     = $oUser->getAutoId();
    $sex        = rand(0,1);
    $color      = rand(1,2);
    
    $opt = array(OptionFish::MONEY,OptionFish::MIXFISH,OptionFish::TIME,OptionFish::EXP,OptionFish::SPECIAL);
        
    $arrSpecial = array(1=>1,4=>4);
    $arrRare = array(0=>0,2=>2,3=>3);
    
    if ($fishType==1)
    {
        $listOpt = array_rand($arrSpecial,$numOption);
    }
    else if($fishType==2)
    {
        $listOpt = array_rand($arrRare,$numOption);
    }
    
    $arrOpt = array();
    if (count($listOpt)==1)
      $arrOpt[0] = $listOpt;
    else $arrOpt = $listOpt;
    
    $rateOption = array();
    
    foreach($arrOpt as $index => $valueOpt)
    {
        $rateOption[$opt[$valueOpt]] = rand(1,10);
    }
    
    if($fishType == FishType::NORMAL_FISH)
    {
        $oFish = new Fish($autoId,$FishTypeId,$sex,$color);      
    }
    else if($fishType == FishType::SPECIAL_FISH)
    {
        $oFish = new SpecialFish($autoId,$FishTypeId,$sex,$rateOption,$color);
    }
    else if($fishType == FishType::RARE_FISH)
    {
        $oFish = new RareFish($autoId,$FishTypeId,$sex,$rateOption,$color);
    }

    $oStore->addFish($oFish->Id,$oFish);
         
    $oUser->save();
    $oStore->save();
    
    return array('Done !')        ;
  }
  
  
  /**
  * Thay doi thong tin ca linh
  */
  public function cheatSoldierProfile($UserId, $SoldierId, $LakeId, $Rank, $RankPoint, $Damage, $Defence, $Critical, $Element, $Health, $Vitality,  $SoldierType, $DefendFail)
  {
      if (empty($UserId))   
        $usId = Controller::$uId;
      else $usId = $UserId;
      $oLake = Lake::getById($usId, intval($LakeId));
      if (!is_object($oLake))
        return 'LakeId ???';
      $oSoldier = $oLake->getFish(intval($SoldierId));
      if (!is_object($oSoldier))
        return 'Not fish';
      if ($oSoldier->FishType != FishType::SOLDIER)
        return 'Not soldier !';
      
      if (!empty($Rank))
        $oSoldier->Rank = intval($Rank);
        
      if (!empty($RankPoint))
        $oSoldier->RankPoint = intval($RankPoint);
        
      if (!empty($Damage))
        $oSoldier->Damage = intval($Damage);
        
      if (!empty($Element))
        $oSoldier->Element = intval($Element);
      
      if (!empty($Vitality))
        $oSoldier->Vitality = intval($Vitality);
        
      if (!empty($Defence))
        $oSoldier->Defence = intval($Defence);
        
      if (!empty($Critical))
        $oSoldier->Critical = intval($Critical);
        
      if (!empty($Health)){
          if ($Health==-1)
            $Health = 0;
          $oSoldier->Health = intval($Health); 
      }
      
      if (!empty($DefendFail)){
          $oSoldier->NumDefendFail = intval($DefendFail);    
          $oSoldier->LastTimeDefendFail = $_SERVER['REQUEST_TIME'];    
      }
        
        
        
      if (!empty($SoldierType))
        $oSoldier->SoldierType = intval($SoldierType);
      
      $oLake->save();
      
      return 'Okay';
  }

  
  
  /**
  * Cheat diem mastery skill lai ca
  * $type : 0-Level, 1-Money, 2-Rare, 3-Special
  */
  
  public function addMastery($type, $level, $mastery){

      $arrSkill = array(Skill::Level,Skill::Money,Skill::Rare,Skill::Special);
            
      $user = User::getById(Controller::$uId);

      $user->Skill[$arrSkill[$type]]['Level'] = intval($level);
      $user->Skill[$arrSkill[$type]]['Mastery'] = intval($mastery);
      
      $user->save();
       
      return 'Okay';
      
  }
  

  public function resetEnergyBox(){
      
      
      $oUserPro = UserProfile::getById(Controller::$uId);
      foreach($oUserPro->ActionInfo['EnergyBox'] as $id => $value)
        unset($oUserPro->ActionInfo['EnergyBox'][$id]);
      $oUserPro->save();
      return 'Okay';
  }
  
    /**
    * reset User
    * 
    */
	public function resetUser(){
		
        $All = true;
         
		if ($User==true || $All==true){
			User::del(Controller::$uId);

		}

    if ($UserProfile==true || $All==true){
      UserProfile::del(Controller::$uId);
    }

		if ($Decoration==true || $All==true){
			Common :: loadModel('Decoration') ;
			Decoration::del(Controller::$uId,1);
			Decoration::del(Controller::$uId,2);
  	}
		if ($Lake==true || $All==true){
			Common :: loadModel('Lake') ;
			Lake::del(Controller::$uId,1);
			Lake::del(Controller::$uId,2);
			Lake::del(Controller::$uId,3);

		}

		if ($Store==true || $All==true){
			Common :: loadModel('Store') ;
			Store::del(Controller::$uId);
  		}
		if ($Quest==true || $All==true){
 			$oUser = User::getById(Controller::$uId);
      Quest::del(Controller::$uId) ;
        }
        if ($All==true){
            DataProvider::delete(Controller::$uId,'MiniGame');
            DataProvider::delete(Controller::$uId,'Event');
            DataProvider::delete(Controller::$uId,'FishWorld');
            DataProvider::delete(Controller::$uId,'TrainingGround');
            DataProvider::delete(Controller::$uId, 'OccupyingProfile')    ;
            DataProvider::delete(Controller :: $uId,'Friends','FriendIds') ;
            DataProvider::delete(Controller :: $uId, 'Friends','Friends') ; 
            DataProvider::delete(Controller::$uId, 'Ingredients');
            DataProvider::delete(Controller::$uId, 'FishTournament');                        
            DataProvider::delete(Controller::$uId, 'Store');
            DataProvider::delete(Controller::$uId, 'ItemCode');
            DataProvider::delete(Controller::$uId, 'SmashEgg');            
            DataProvider::delete(Controller::$uId, 'KeepLogin');
            
            
		}

		if ($Diary==true || $All==true){
			DataProvider::delete(Controller::$uId,'Diary');
			$oDiary = new Diary(Controller::$uId);
			$oDiary->save();
		}

		if ($MailBox==true || $All==true){
			DataProvider::delete(Controller::$uId,'MailBox');
		}
		if ($GiftBox==true || $All==true){
			DataProvider::delete(Controller::$uId,'GiftBox');
		}


		if ($GiftReceiver==true || $GiftSender==true || $UserProfile==true || $All==true){
      
      UserProfile::del(Controller::$uId);
			//$userPro = new UserProfile(Controller::$uId);
			//$userPro->save();
		}
        
        if($All==true)
        {
            $oWorld = FishWorld::getById(Controller::$uId);
            if(is_object($oWorld))
            {
                $oWorld->SeaList    = array();
                $oWorld->SeaNum     = 0 ;
                $oWorld->save();
            }
            
            PowerTinhQuest::del(Controller::$uId);
            StoreEquipment::del(Controller::$uId);
        }
        
       if($Event == true || $All== true)
       {
           DataProvider::delete(Controller::$uId, 'Event');
       }
       
       // Remove NPC
       $NPCId = NPC::NPC_SIGN.Controller::$uId;
        DataProvider::delete($NPCId, 'User')  ;
        DataProvider::delete($NPCId, 'StoreEquipment')  ;        
        DataProvider::delete($NPCId, 'Lake')  ;
        DataProvider::delete($NPCId, 'Decoration')  ;
        DataProvider::delete($NPCId, 'UserProfile') ;             
    
		return array('LÃƒÂ m lÃ¡ÂºÂ¡i tÃ¡Â»Â« Ã„â€˜Ã¡ÂºÂ§u nhÃƒÂ© !');
	}



	//public function resetGift($UserId, $Receiver, $Sender){
	//      if (empty($UserId))
	//      $UserId = Controller::$uId;
	//      ('UserProfile');
	//      $userPro = UserProfile::getById($UserId);
	//      if ($Receiver==true){
	//          $userPro->ActionInfo['Receivers'] =  array() ;
	//      }
	//      if ($Sender==true){
	//          $userPro->ActionInfo['Senders'] =  array() ;
	//      }
	//      $userPro->save();
	//
	//      return array('Okay men !');
	//  }

	/**
	 * Thay doi thuoc tinh user
	 *
	 */

	public function addUserProfile($UserId, $Money, $ZMoney, $Diamond, $Exp, $Energy, $Food, $DataVersion,$Level,$firstaddXu){

		if (empty($UserId))
		$UserId = Controller::$uId;

		('User');
		$oUser = User::getById($UserId);
		$oUser->addMoney($Money);
		$info = 'SaveBonus';
		$oUser->addZingXu($ZMoney, $info);
		$oUser->addExp($Exp);
        $oUser->addDiamond($Diamond);
		$oUser->addEnergy($Energy);
		$oUser->addFood($Food);
        $oUser->setDataVersion($DataVersion);
        if($firstaddXu == 0)
        {
            $oUser->FirstAddXuGift = array() ;
        }
        $oUser->FirstAddXu = $firstaddXu ;
        
        if(!empty($Level))
            $oUser->Level = $Level ;
		$oUser->save();
		return array('Ã„ï¿½ÃƒÂ£ cheat xong ! ' . $oUser->getDataVersion());


	}

    /**
    * Mo khoa tat ca ca trong shop
    * 
    */
	public function unlockAllFish($idFish){
		('UserProfile');
		$oUserPro = UserProfile::getById(Controller::$uId);
		if(!is_object($oUserPro)) return array("chua khoi tao user");
		if (empty($idFish))
		{
			for($i = 3;$i<100;$i++)
			$oUserPro->ActionInfo['UnlockFishList'][$i] = UnlockType::Mix ;
		}
		else
		{
			$oUserPro->ActionInfo['UnlockFishList'][$idFish] = UnlockType::Mix ;
			$oFish = Common::getConfig('Fish',$idFish);
			$oUserPro->updateMaxFishUnlock($oFish['LevelRequire']);
		}

		$oUserPro->save();
		return array('Ä?Ã£ cheat xong !');


	}
  

    /**
    * Get DailyQuest
    * 
    */
	public function getDailyQuest(){


		$oUser = User::getById(Controller::$uId);
		$oNewQuest = Quest::getById(Controller::$uId);
		if (!is_object($oNewQuest)){
			$oNewQuest = new Quest(Controller::$uId,$oUser->Level);
		}
		$oNewQuest->save();
		$arr = array();
		$arr['Task'] = $oNewQuest->DailyInfo;
		$arr['Current'] = $oNewQuest->CurrentQuest;
		$arr['Unlock'] = $oNewQuest->UnlockQuest2;
		$arr['LastUpdate'] = $oNewQuest->LastUpdateDaily;


		$ar = array(0=>'aa',1=>'bb',2=>'cc',3=>'dd');



		return $arr;

	}



	/**
	 * Reset kha nang lai tat ca ca trong ho
	 *
	 */

	public function resetMateFish(){

		('User');
		$oUser = User::getById(Controller::$uId);
		if (!is_object($oUser))
		return "User chua khoi tao";

		('Lake');
		$ooLake = Lake::getById(Controller::$uId, 1);
		$arraylake = $ooLake->getAll(Controller::$uId);
		foreach($arraylake as $LakeId => $olake)
		{
			if(!is_object($olake))
			{
				continue ;
			}

			$Arr_Fish = $olake->FishList;
			foreach($Arr_Fish as $oFish)
			{
				if(!is_object($oFish))
				{
					continue;
				}
				$oFish->LastBirthTime = 0 ;

			}
			$olake->save();
		}

		return "Okay !";
	}


	/**
	 *  add Material
	 *
	 */

	public function addMaterial($UserId, $Material1=0, $Material2=0, $Material3=0, $Material4=0, $Material5=0, $Material6=0, $Material7=0, $Material8=0, $Material9=0, $Material10=0,$Material11=0,$Material12=0, $Material13=0, $Material14=0,
    $Material101=0,$Material102=0,$Material103=0,$Material104=0,$Material105=0,$Material106=0,$Material107=0,$Material108=0,$Material109=0,$Material110=0,$Material111=0,$Material112=0, $Material113=0, $Material114=0){

		if (empty($UserId))
		$UserId = Controller::$uId;
    $arrMat = array(1,2,3,4,5,6,7,8,9,10,11,12,13, 14, 101,102,103,104,105,106,107,108,109,110,111,112, 113, 114);
		$oStore = Store::getById($UserId);
		$arr = array();
		foreach ($arrMat as $index => $i){
			$mater = 'Material';
			$num1 = $mater.$i;
			$num = $$num1;
			$oStore->addItem('Material',$i, $num);
			$arr[$i] = $num;
		}


		$oStore->save();
		return array('Ã„ï¿½Ã¡ÂºÂ¡i gia !!!');
	}

	/**
	 * add energy item
	 */

	public function addEnergyItem($UserId, $Item1=0, $Item2=0, $Item3=0, $Item4=0, $Item5=0, $Item6=0){

		if (empty($UserId))
		$UserId = Controller::$uId;

		$oStore = Store::getById($UserId);
		$arr = array();
		for ($i=1; $i<=6; $i++){
			$mater = 'Item';
			$num1 = $mater.$i;
			$num = $$num1;
			$oStore->addItem('EnergyItem',$i, $num);
			$arr[$i] = $num;
		}


		$oStore->save();
		return array('Ã„ï¿½ÃƒÂ£ cheat xong !');
	}

    
    /**
    * add Thuoc hoi sinh 
    * 
    */
    public function addRebornMedicine($Medicine1=0, $Medicine2=0, $Medicine3=0, $Medicine4=0, $Medicine5 = 0){

        $oStore = Store::getById(Controller::$uId);
        $arr = array();
        for ($i=1; $i<=5; $i++){
            $mater = 'Medicine';
            $num1 = $mater.$i;
            $num = $$num1;
            $oStore->addItem('RebornMedicine',$i, $num);
            $arr[$i] = $num;
        }


        $oStore->save();
        return array('Da cheat xong!');
    }
    
   /**
    * add tui than ky 
    */
    /*
    public function addMagicBag($Bag1=0, $Bag2=0, $Bag3=0, $Bag20=0, $Bag21=0, $Bag22=0, $Bag40=0, $Bag41=0, $Bag42=0){

        $oStore = Store::getById(Controller::$uId);
        $listBag = array(1,2,3,20,21,22,40,41,42);
        $arr = array();
        foreach ($listBag as $v => $i){
            $mater = 'Bag';
            $num1 = $mater.$i;
            $num = $$num1;
            $oStore->addItem('MagicBag',$i, $num);
            $arr[$i] = $num;
        }


        $oStore->save();
        return $arr;
    }*/
    
    
	/**
	 * add Giay phep
	 */

	public function addLicense($UserId,$Num = 1){

		if (empty($UserId))
		$UserId = Controller::$uId;

		('Store');
		$oStore = Store::getById($UserId);
		if($Num < 1 )
		{
			return array('sai du lieu');
		}
		$oStore->addItem('License',1, $Num);
		$oStore->save();
    
		return array('Ã„ï¿½ÃƒÂ£ cheat xong !');
	}
  

  /**
  * reset qua tang hang ngay
  *  
  */
  public function resetGift()
  {
      $oUserPro = UserProfile::getById(Controller::$uId);
      $oUserPro->ActionInfo['Receivers'] = array();
      $oUserPro->ActionInfo['Senders'] = array();
      $oUserPro->save();
      return 'Okay';
  }
  
  
  
	/**
    *  Them item vao kho
	 * $ItemType = {OceanAnimal,OceanTree,Other, Arrow,PearFlower,...}
	 *
	 */

	public function addItems($UserId, $ItemType, $ItemId, $Num= 1)
    {
	    if (empty($UserId))
	    $UserId = Controller::$uId;

	    $oStore = Store::getById($UserId);
        $oUser = User::getById($UserId)  ;
        
        $conf = Common::getConfig($ItemType,$ItemId);
        if(!is_array($conf))
        {
            return false  ;
        }
              
        if(in_array($ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
        {
          $oItem = new Item($oUser->getAutoId(),$ItemType,$ItemId);
          $oStore->addOther($ItemType,$oItem->Id,$oItem);   
        }
        else if(in_array($ItemType,array(Type::PearFlower),true))
        {
            $conf_PearFlower = Common::getConfig(Type::PearFlower,$ItemId);
            if(!is_array($conf_PearFlower))
                return array('sai loai Item');
                        
            $oItem = new Item($oUser->getAutoId(),$ItemType,$ItemId);
            $oItem->setOption($conf_PearFlower['Buff']);
            $oStore->addOther($ItemType,$oItem->Id,$oItem);   
        }
        else if(BuffItem::checkExist($ItemType))
        {
             $oStore->addBuffItem($ItemType,$ItemId,$Num);
        }
        else if($ItemType == Type::BirthDayItem)
        {
            $oStore->addEventItem(EventType::BirthDay,$ItemType,$ItemId,$Num);
        }
        else
        {
            $oStore->addItem($ItemType,$ItemId,$Num);
        }
        
        $oStore->save();
        $oUser->save();
		return array('cheat ok  !!!');

	}

	/**
	 *  cheat so vet ban trong ho
	 */

	public function cheatDirtyLake($UserId, $LakeId, $NumDirty){
		if (empty($UserId))
		$UserId = Controller::$uId;

		$oLake = Lake::getById($UserId, $LakeId);
		$oLake->CleanAmount -= $NumDirty;
		$oLake->save();
		return array(' Okay men !');


	}

    /**
    * reset kho
    * 
    */
	public function resetStore(){

		$userId = Controller::$uId;
		$oStore = Store::getById($userId);
		$oStore->Items = array();
		$oStore->Fish = array();
        $oStore->AllOther = array();
        $oStore->Gem = array();
        $oStore->BuffItem = array();
        $oStore->Equipment = array();
        $oStore->EventItem = array();
        $oStore->Quartz = array();
		$oStore->save();
		return "Ukie !";

	}
    
    /**
    *  Cheat tan, ngoc
    *  Element = 1 -> 5
    *  Level = 0 -> 10
    *  Day = (-6) -> 7
    */
    
    public function cheatGem($Element, $Level, $Day, $Num, $AddLastUpdateTime)
    {
        $oStore = Store::getById(Controller::$uId);
        if (empty($Level))
            $Level = 0;
        if (!empty($Num))
            $oStore->addGem(intval($Element),intval($Level),intval($Day),intval($Num));
        if (!empty($AddLastUpdateTime))
            $oStore->Gem['LastUpdateTime'] += $AddLastUpdateTime;
        else $oStore->Gem['LastUpdateTime'] = $_SERVER['REQUEST_TIME'];
        $oStore->save();
        return true ;
    }

    /**
    * Them item vao kho
    */
	public function addStoreItem($ItemType,$ItemId, $Num)
    {
        $oStore = Store::getById(Controller::$uId);
        if($ItemType == Type::BirthDayItem)
        {
            $oStore->addEventItem(EventType::BirthDay,$ItemType,$ItemId,$Num);
        }
        else if($ItemType == Type::IceCreamItem)
        {
            $oStore->addEventItem(EventType::IceCream,$ItemType,$ItemId,$Num);
        } 
        else if($ItemType == Type::Island_Item)
        {
            $oStore->addEventItem(EventType::TreasureIsland,$ItemType,$ItemId,$Num);
        }
        
        else if($ItemType == Type::Event_8_3_Flower)
        {
            $oStore->addEventItem(EventType::Event_8_3_Flower,$ItemType,$ItemId,$Num);
        }
        else if($ItemType == Type::Arrow || $ItemType == Type::VipMedal)
        {
            $oStore->addEventItem(EventType::PearFlower,$ItemType,$ItemId,$Num);
        }
        else if($ItemType == Type::HalItem)
        {
            $oStore->addEventItem(EventType::Halloween, $ItemType, $ItemId, $Num);
        }
        else
        {
            $oStore->Items[$ItemType][$ItemId] += $Num;
        }
        $oStore->save();
        return 'okay';
    }
    
    
    /**
    * reset so lan tan cong ho nha ban
    */
    public function resetAttackTimes()
    {
    
        $oUserPro = UserProfile::getById(Controller::$uId);
        $oUserPro->Attack = array();
        $oUserPro->save();   
        
        return 'Okay';
        
    }
       
    
    
    /**
    * reset ngoc dang luyen
    * 
    */
    public function resetUpgrading()
    {
        $oStore = Store::getById(Controller::$uId);
        $oStore->UpgradingGem = array();
        $oStore->save();
    }
	

    /**
    * reset dailyquest
    * 
    */
	public function resetDailyQuest(){

		$nQuest = Quest::getById(Controller::$uId);
		$nQuest->LastUpdateDaily = 0;
    $nQuest->ResetTimes = 0;
		$nQuest->save();
		return "Okay";


	}

    /**
    * cheat ca Super trong kho
    */
	public function createSparta($isSwat, $isBatman, $isSpiderman, $isFirework,$isIronman){

		$oUser = User::getById(Controller::$uId);

      $opt = $oUser->createSparta();
    
    
		$oStore = Store::getById(Controller::$uId);
    if($isSwat==true)
    {
            $oStore->addOther(Type::Swat, $opt->Id,$opt);
    }
    else if ($isBatman==true)
    {
        $opt->Option = array(); 
        $opt->Option[OptionFish::TIME] = 15;
        $oStore->addOther(Type::Batman, $opt->Id,$opt);
    }
    else if ($isSpiderman==true)
    {

        $opt->Option = array();
        $opt->Option[OptionFish::TIME] = rand(11,15);
        $ran1 = rand(0,100);
        $ran2 = rand(0,100);
        if ($ran1>=50)
        {
            $opt->Option[OptionFish::EXP] = rand(12,16);
            if ($ran2 >=50)
                $opt->Option[OptionFish::GOLD] = rand(12,16);
        }
        $oStore->addOther(Type::Spiderman, $opt->Id,$opt);    
    }
    else if ($isFirework==true)
    {

        $opt->Option = array();
        $opt->Option[OptionFish::TIME] = rand(11,15);
        $ran1 = rand(0,100);
        $ran2 = rand(0,100);
        if ($ran1>=50)
        {
            $opt->Option[OptionFish::EXP] = rand(12,16);
            if ($ran2 >=50)
                $opt->Option[OptionFish::MONEY] = rand(12,16);
        }
        $oStore->addOther(Type::Firework, $opt->Id,$opt);    
    }
    else if ($isIronman==true)
    {

        $opt->Option = array();
        $opt->Option[OptionFish::TIME] = rand(20,25);
        $opt->Option[OptionFish::EXP] = rand(25,35);
        $opt->Option[OptionFish::MONEY] = rand(25,35);
        
        $oStore->addOther(Type::Ironman, $opt->Id,$opt);    
    }
    else
    {
        $oStore->addOther(Type::Sparta, $opt->Id,$opt);  
    }
		
		$oStore->save();
		$oUser->save();
		return array(1 => $opt);
	}

    /**
    * xoa ca super trong ho
    */
    public function deleteSparta($isSwat, $lakeId){
        if (empty($lakeId))
            return 'LakeId ???';
        if (!empty($isSwat))
        {
            $oDeco = Decoration::getById(Controller::$uId,$lakeId);
            //return $oDeco->SpecialItem['Swat'];
            foreach ($oDeco->SpecialItem['Swat'] as $id => $oSwat)
            {
                unset($oDeco->SpecialItem['Swat'][$id]);
            }
            $oDeco->save();
        }
        return true;
    }
  
  
  /*
  public function cheatTimeSparta($isSwat, $isBatman, $isSpiderman, $Time)
  {
    $oUser = User::getById(Controller::$uId);
    $oStore = Store::getById(Controller::$uId);
    if($isSwat==true)
    {
        $opt->Exp = 5000;
        $opt->Money = 150000;
        $oStore->addOther(Type::Swat, $opt->Id,$opt);
    }
    else if ($isBatman==true)
    {
        $opt->Exp = 7777;
        $opt->Money = 200000;
        $opt->Option = array();
        $opt->Option[OptionFish::TIME] = 15;
        $oStore->addOther(Type::Batman, $opt->Id,$opt);
    }
    else if ($isSpiderman==true)
    {
        $opt->Exp = 7777;
        $opt->Money = 200000;
        $opt->Option = array();
        $opt->Option[OptionFish::TIME] = 15;
        $oStore->addOther(Type::Spiderman, $opt->Id,$opt);
    }
    else
    {
        $oStore->addOther(Type::Sparta, $opt->Id,$opt);  
    }
    
    $oStore->save();
    $oUser->save();
    return array(1 => $opt);  
  }
  */
  
	/**
    * Cheat thoi gian cho tat ca ca super theo loai  
	 * LakeId  = 1-2, Time = second
	 */
	public function cheatTimeForAllSparta($isBatman, $isSwat, $isSpiderman, $isFirework, $LakeId,$Time){
		if($LakeId < 1 || $LakeId >3 || $Time < 1)
		{
			return false ;
		}
		$oUser = User::getById(Controller::$uId);
		if (!is_object($oUser))
			return false ;
		$oLake = Lake::getById(Controller::$uId, $LakeId);
		if (!is_object($oLake))
			return false ;
		$oDecorate = Decoration::getById(Controller::$uId, $LakeId);
		if (!is_object($oDecorate))
			return false ;

      
    $typeFish = Type::Sparta;
    if ($isBatman)
      $typeFish = Type::Batman;
    else if ($isSwat)
      $typeFish = Type::Swat;
    else if ($isSpiderman)
        $typeFish = Type::Spiderman;
    else if ($isFirework)
        $typeFish = Type::Firework;
      
      
		if (!empty($oDecorate->SpecialItem[$typeFish]))
		{
			foreach ($oDecorate->SpecialItem[$typeFish] as $id => $oSparta)
			{
				if (!is_object($oSparta))
				{
					unset($oDecorate->SpecialItem[$typeFish][$id]);
					continue;
				}
				if (!$oSparta->isExpried)
				{
					continue ;
				}
				$oDecorate->SpecialItem[$typeFish][$id]->StartTime -= $Time;
                $oDecorate->SpecialItem[$typeFish][$id]->LastTimeGetGift -= $Time;
			}
		}
    
    
		$oDecorate->save();
		
		return "ca ".$typeFish . " da duoc tang them ".$Time." s" ;
	}
    
  /**
  * Cheat ky nang ghep ngu thach
  */
  public function cheatMaterialSkill($SkillLevel, $SkillPoint){
    $userPro = UserProfile::getById(Controller::$uId);
    if(!empty($SkillLevel))
      $userPro->MatLevel = $SkillLevel;
    if(!empty($SkillPoint)) 
      $userPro->MatPoint = $SkillPoint;
    $userPro->save();
    return 'Okay';
  }
  
  /**
  * Cheat thoi gian cho do trang tri
     * LakeId  = 1-2, Time = second
     */
    public function cheatTimeForDeco($LakeId,$Time)
    {
        if($LakeId < 1 || $LakeId >3 || $Time < 1)
        {
            return false ;
        }
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
            return false ;
        $oLake = Lake::getById(Controller::$uId, $LakeId);
        if (!is_object($oLake))
            return false ;
        $oDecorate = Decoration::getById(Controller::$uId, $LakeId);
        if (!is_object($oDecorate))
            return false ;
                  
        if (!empty($oDecorate->ItemList))
        {
            foreach ($oDecorate->ItemList as $id => $oItem)
            {
                if (!is_object($oItem))
                {
                    continue;
                }
                $oDecorate->ItemList[$id]->ExpiredTime -= $Time;
                /*if($oDecorate->ItemList[$id]->ExpiredTime < $_SERVER["REQUEST_TIME"])
                    $oDecorate->ItemList[$id]->ExpiredTime = $_SERVER["REQUEST_TIME"]- 3600 ;    */
            }
        }

        $oDecorate->save();
        
        return "da duoc tang them ".$Time."s" ;
    }
  
  /**
  * cheat dailybonus
  * 
  */
    public function cheatDailyBonus(){
        $oUserPro = UserProfile::getById(Controller::$uId);
        $oUserPro->ActionInfo['DayGift'][1] = array();
        $oUserPro->ActionInfo['DayGift'][1]['Level'] = 1;
        $oUserPro->ActionInfo['DayGift'][1][1] = array();
        $oUserPro->ActionInfo['DayGift'][1][1][Type::ItemType] = Type::Money;
        $oUserPro->ActionInfo['DayGift'][1][1][Type::Num] = 1;
        $oUserPro->ActionInfo['DayGift'][1][1]['BonusId'] = 5;
        $oUserPro->save();
    }
  
  
   

  /**
  * reset dailybonus
  * 
  * @param int $Time
  * @return mixed
  */
  public function resetDailyBonus($Time)
  {
      $Time = intval($Time);
      if ($Time<1 || $Time>5)
        return 'Sai tham so !';
      $userPro = UserProfile::getById(Controller::$uId);
      $userPro->ActionInfo['DayGift'][$Time] = array();
      
        $oUser = User::getById(Controller::$uId);
        $conf_Bonus = $userPro->day_getConfig($oUser->Level,$Time);
        $BonusId = $userPro->day_ranBonus($Time,1) ;
        $userPro->day_saveTempBonus($Time,1,$conf_Bonus,$BonusId);
    
      
      $userPro->save();
      
      
    
  }
  
  
  /** Upgrade Skill 
  *  SkillType : 1 = Money , 2 = Level ,3 = Special , 4 = Rare
  */
  /*
  public function UpgradeSkill($SkillType, $Level)
  {
     switch ($SkillType)
     {
       case 1:
          $SkillType = Skill::Money ;
        break ;
       case 2:
          $SkillType = Skill::Level ;
        break ;
       case 3:
          $SkillType = Skill::Special ;
        break ;
       case 4:
          $SkillType = Skill::Rare ;
        break ;
       default :
        return 'ko co loai skill nay ' ;
     }
     $SkillConfig = Common::getConfig($SkillType,$Level);
     if(!is_array($SkillConfig))
     {
       return ' level ban dien vao sai ' ;
     }
     $oUser = User::getById(Controller::$uId);       
     if($oUser->Level < $SkillConfig['LevelRequire'])
     {
        return ' level ban ko du de nang cap skill nay ' ;       
     }
     $curentLevel = $oUser->Skill[$SkillType]['Level'];
     if ($Level <= $curentLevel)
     {
       return ' ko upgrade nguoc ' ;
     }
     $oUser->Skill[$SkillType]['Level'] = $curentLevel + 1;
     $oUser->Skill[$SkillType]['Mastery'] = 0 ;
     $oUser->save();
     
     return ' Upgrade Thank cong ! ' ;  
  }
  */
  

  
  /**
  * cheat cac thuoc tinh trong userprofiles
  * @return mixed
  */
  public function zUserProfiles($NumFill,$AddTimeTakePicture, $NumPictureTime, $LastTimeAction){
      if(!empty($NumFill))
      {
          $oUserPro = UserProfile::getById(Controller::$uId);
          $oUserPro->NumFill = intval($NumFill);
          $oUserPro->save();
      }
      if (!empty($AddTimeTakePicture))
      {
          $oUserPro = UserProfile::getById(Controller::$uId);
          $oUserPro->LastPictureTime += $AddTimeTakePicture;
          $oUserPro->save();
      }
      
      if (!empty($NumPictureTime))
      {
          $oUserPro = UserProfile::getById(Controller::$uId);
          $oUserPro->NumTakePicture  = $NumPictureTime;
          $oUserPro->save();
      }
      
      if (!empty($LastTimeAction))
      {
          $oUserPro = UserProfile::getById(Controller::$uId);
          $oUserPro->LastActionTime  = 0;
          $oUserPro->save();
      }
      
      
      
      
      return 'Okay';
  }
  
 

  /**
  * Cheat may nang luong
  */
  public function cheatEnergyMachine($Machine, $NumPetrol) {
      if(!empty($Machine))
      {
          $oUser = User::getById(Controller::$uId);
          $oGift = array();
          $oGift[0][Type::ItemType] = 'EnergyMachine';
          $oGift[0][Type::ItemId] = 1;
          $oGift[0][Type::Num] = 1;
          $oUser->saveBonus($oGift);
          $oUser->save();
      }
      
      if(!empty($NumPetrol))
      {
          $oUser = User::getById(Controller::$uId);
          $oGift = array();
          $oGift[0][Type::ItemType] = 'Petrol';
          $oGift[0][Type::ItemId] = 1;
          $oGift[0][Type::Num] = $NumPetrol;
          $oUser->saveBonus($oGift);
          $oUser->save();
      }
      
      return 'Okay';
    
  }
  
  
  
  /**
  * cheat thuoc viagra
  * 
  */
  public function cheatViagra($Num){
      $oStore = Store::getById(Controller::$uId);
      if (!empty($Num) && $Num>0)
      {
          $oStore->addItem('Viagra',1,$Num);
      }
      $oStore->save();
      return 'Okay';
  }
  
  /**
  * cheat time for EnergyMachine
  * time = giay
  * 
  */
  public function timeforMachine($time){
      if($time <1 and $time >100000000)
      {
        return 'ban nhap so qua nho or qua to';
      }
      $oUser = User::getById(Controller::$uId);
      if (empty($oUser))
      {
          return 'ban chua dang nhap' ;
      }
      $oMachine = $oUser->SpecialItem['EnergyMachine'] ;
      if(!is_object($oMachine))
      {
        return 'ban ko co may' ;
      }
      $oMachine->StartTime -= $time ;
      if($oMachine->StartTime < 0)
        $oMachine->StartTime = 0 ;
        
      $oUser->SpecialItem['EnergyMachine'] = $oMachine ;
      $oUser->save() ;
      return 'Okay';
  }
  
  
  /**
  * reset Daily Energy
  * @param mixed $NumReceived
  */
  public function resetDailyEnergy($NumReceived)
  {
      $oUserPro = UserProfile::getById(Controller::$uId);
      $oUserPro->ActionInfo['DailyEnergy'] = array();
      $oUserPro->ActionInfo['LastTimeDailyEnergy'] = 0;
      
      for($i = 0; $i<$NumReceived; $i++)
      {
        $oUserPro->ActionInfo['DailyEnergy'][rand(1,10000)] = true; 
        $oUserPro->ActionInfo['LastTimeDailyEnergy'] = $_SERVER['REQUEST_TIME']; 
      }
      
      $oUserPro->save();
      
  }
  
  
  
  /**
  * Cheat cong thuc lai
  * $Type : Draft,Paper, GoatSkin, Blessing 
  * $Id   : 1-> 7
  * @return 
  */
  public function cheatMixFormula($Type,$Id,$Num = 1)
  {
    if(!FormulaType::checkExist($Type)|| $Id >7 || $Id < 1)
    {
      return array('thong tin nhap vao sai');
    }
    
    $oStore = Store::getById(Controller::$uId);
    $oStore->addItem($Type,$Id,$Num) ;
    
    $oStore->save();
    
    return array('cheat thanh cong ! ') ;
    
  }
  /**
  *  them cac Item buff khi chien dau 
  *  $Type : 1= Samurai ,2 = Resistance,3 =  BuffExp , 4 = BuffMoney , 5 = BuffRank, 6 =  StoreRank, 7 = Ginseng, 8= RecoverHealthSoldier
  *  $id = 1 
  */
  public function addBuffItem($Type,$Id,$Num)
  {
    if($Type < 1 || $Type > 8) return false ;
    
    $oStore = Store::getById(Controller::$uId);
    switch ($Type)
    {
      case 1 :
        $BuffType = 'Samurai' ;
        break ;
      case 2 :
        $BuffType = 'Resistance' ;
        break ;
      case 3 :
        $BuffType = 'BuffExp' ;
        break ;
      case 4 :
        $BuffType = 'BuffMoney' ;
        break ;
      case 5 :
        $BuffType = 'BuffRank' ;
        break ;
      case 6 :
        $BuffType = 'StoreRank' ;
        break ;
      case 7 :
        $BuffType = 'Ginseng' ;
        break ;
      case 8 :
        $BuffType = 'RecoverHealthSoldier' ;
        break ;   
      default :
        $BuffType = 'Samurai' ;
        break ;
    }
    
    if(!$oStore->addBuffItem($BuffType,$Id,$Num))
      return false ;
    $oStore->save() ;
    return true ;
  }
  /**
  * Cheat ca linh 
  * chi tiet muon ra con ca linh nao can xem file config
  * $recipeType :  1  = 'Draft', 2 = 'Paper', 3 = 'GoatSkin', 4 = 'Blessing' , 9 = 'Rent';
  * $RecipeId  : 1 -> 5 
  * 
  */
  
  public function addSoldier($RecipeType,$RecipeId)
  {
    $SoldierType =  SoldierType::MATE ; 
    switch ($RecipeType)
    {
      case 1:
         $RecipeType = 'Draft';
         break ;
      case 2:
        $RecipeType = 'Paper';
        break ;
      case 3:
      $RecipeType = 'GoatSkin';
        break ;
      case 4:
        $RecipeType = 'Blessing';
        break ;
      case 9: 
        $RecipeType = 'Rent'; 
        $SoldierType =  SoldierType::BUYSHOP ;
        break ;
      default :
        break ;
    }
    if(!FormulaType::checkExist($RecipeType)||$RecipeId >5 || $RecipeId < 1)
      return false ;
      
    $oStore = Store::getById(Controller::$uId);        
    $oStore->createSoldierByRecipe($RecipeType,$RecipeId,$SoldierType,$Num = 1); 
    $oStore->save();
    return true ;
    
  }
  /**
  * Cheat thoi gian noi bong bong tien
  * $Iime = giay;
  * $Lake = Id cua ho (1 || 2)
  */
  function cheatTimeForPocket($Time,$LakeId)
  {
     $oLake = Lake::getById(Controller::$uId,$LakeId) ;
     if(!is_object($oLake))
     {
       return false ;
     }
     foreach($oLake->FishList as $id => $oFish)
     {
          if(!is_object($oFish))
            continue ;
          $oFish->PocketStartTime -= $Time ;
     }
     $oLake->save() ;
     
     return 'ok';      
  }
  
  
	/*
	 public function checkRandom(){
	 $arr = array(2=> 3000,3=> 1860,4=> 1300,5=> 1750,6=> 1050,8=> 250 ,7=> 660,9=> 90 ,10=> 40);

	 $total = 0;
	 foreach($arr as $val)
	 $total += $val;

	 $numRandom = 1000;
	 $arr2 = array();
	 for ($i=0; $i<$numRandom; $i++){
	 $aa = Common::randomIndex($arr);
	 $arr2[$aa]++;
	 }
	 $result = array();

	 foreach($arr as $id => $value){
	 $result[$id]['PerBase'] = 100*$arr[$id]/$total;
	 $result[$id]['PerRandom'] = 100*$arr2[$id]/$numRandom;
	 }

	 $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
	 $Expired = date("Ymd", mktime(0, 0, 0, 27, 4, 2011));

	 $re = array();
	 $re[0] = mktime(0, 0, 0, 26, 4, 2011);
	 $re[1] = $_SERVER['REQUEST_TIME'];


	 $aa = 10;
	 $t = 0;
	 if ($aa>5){
	 $aa = 1;
	 $t++;
	 }
	 else {
	 return 10;
	 }


	 return $t;
	 }
	 */
   
   
   /**                                                                     $
   * $ItemType: 1-Tăng lực, 2-Miễn kháng, 3-BuffExp, 4-BuffMoney, 5-BuffRank, 6-Giữ rank
   * $ItemId: 1,2,3 khi dùng thuốc tăng lực
   */
    /*
    public function addBuffItem($ItemType, $ItemId, $Num)
    {
        $arrBuff = array(BuffItem::Samurai,BuffItem::Resistance,BuffItem::BuffExp, BuffItem::BuffMoney, BuffItem::BuffRank, BuffItem::StoreRank);
        if (empty($ItemId))
            $ItemId = 1;
        $conf_buff = Common::getConfig('BuffItem');
        if (!is_array($conf_buff[$arrBuff[$ItemType-1]][$ItemId]))  
            return 'Wrong param !!!';
        $oStore = Store::getById(Controller::$uId);
        $oStore->addBuffItem($arrBuff[$ItemType-1],$ItemId,$Num);
        $oStore->save();
    }
    */
     /**
     * Cheat event ca linh
     * WinNum : la so tran thang
     * StarNum : la so sao dang co
     */
   /* public function cheatSoldierEvent($WinNum = 0,$StarNum = 0 )
    {
      $oUserPro = UserProfile::getById(Controller::$uId);
      if(!is_object($oUserPro) || !is_array($oUserPro->Event['SoldierEvent'])) return false ;
      
      if($WinNum < 0 || $StarNum < 0) return false ;
      
      $oUserPro->Event['SoldierEvent']['WinTotal']   += $WinNum ;
      $oUserPro->Event['SoldierEvent']['LuckyStar']  += $StarNum ;  
      $oUserPro->save() ;
      return true ;
    }*/

   /**
   *  cheat Equipment in store
   *  if edit equipment, fill 'id' vs 'type'
   *  element: 1-Kim, 2-Moc, 3-Tho, 4-Thuy, 5-Hoa
   *  type = 'Armor', 'Helmet', 'Weapon', 'Bracelet', 'Necklace', 'Ring', 'Belt'
   *  rank = 101,102...
   *  color = 1-Trang, 2-Xanh, 3-Vang
   *  expTime = second
   */
    public function editEquipment($soldierId, $soldierElement, $id, $element=1, $type='Armor', $rank, $color=1, $damage=5, $defence=3,$critical=1, $dura, $vital)
    {
        $oUser = User::getById(Controller::$uId);
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        if (empty($id))
            $id = $oUser->getAutoId();
        else $oldEquip = $oStoreEquip->getEquipment($soldierId,$type,$id);
        
        if (!empty($id)){
            if (empty($rank))
                $rank = $oldEquip->Rank;
            if (empty($element))
                $element = $oldEquip->Element;
            if (empty($color))
                $color = $oldEquip->Color;
            if (empty($damage))
                $damage = $oldEquip->Damage;
            if (empty($defence))
                $defence = $oldEquip->Defence;
            if (empty($critical))
                $critical = $oldEquip->Critical;
            if (empty($dura))
                $dura = $oldEquip->Durability;
            if (empty($vital))
                $vital = $oldEquip->Vitality;
        }
        if (empty($element))
            $element = 1;
        if (empty($soldierElement))
            $soldierElement = 1;
        if (!in_array($type,array('Armor','Helmet','Weapon','Bracelet','Necklace','Ring','Belt')))
            return 'Wrong param !';
        
        $oEquip = new Equipment($id,intval($element),$type,intval($rank),intval($color),intval($damage),intval($defence),$critical,$dura, intval($vital),SourceEquipment::FISHWORLD);
        $oStoreEquip->addEquipment($soldierId,$soldierElement,$oEquip);
        $oUser->save();
        $oStoreEquip->save();
        return 'Okay';
    }
    
    public function subDurability($soldierId, $equipType, $equipId, $Dura)
    {
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        $oStoreEquip->SoldierList[$soldierId]['Equipment'][$equipType][$equipId]->Durability = $Dura;
        $oStoreEquip->save();
    }
    
    /**
    *  type = 'Armor', 'Helmet', 'Weapon', 'Bracelet', 'Necklace', 'Ring', 'Belt'
    *  rank = 101,102...
    *  color = 1-Trang, 2-Xanh, 3-Vang
    */
    
    public function cheatEquipment($type='Armor', $rank, $color=1)
    {
        $rank = intval($rank);
        $color = intval($color);
        $conf_equip = Common::getConfig('Wars_'.$type);
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        $oEq = $conf_equip[$rank][$color];
        if (!is_array($oEq)) 
            return 'Error param';
        $newEquip = new Equipment($oUser->getAutoId(),$oEq['Element'],$type,$rank,$color,rand($oEq['Damage']['Min'],$oEq['Damage']['Max']), rand($oEq['Defence']['Min'],$oEq['Defence']['Max']),
                rand($oEq['Critical']['Min'],$oEq['Critical']['Max']),$oEq['Durability'],$oEq['Vitality'],SourceEquipment::SHOP);
        $oStore->addEquipment($newEquip->Type, $newEquip->Id, $newEquip);    
        $oUser->save();
        $oStore->save();
        return 'Okay';
    }
    
    
    /**
    * de cheat giam Durablity or Time cua tat ca cac trang bi trong kho
    * Num : la so luong can giam 
    */
     public function downAllInfoEquipment($Durablity = 0 , $time = 0 )
    {
        if($Durablity == 0 &&  $time == 0 ) return array ('gia tri chuyen vao ko duoc <=0' );
        $oStore = Store::getById(Controller::$uId);
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oStore))
            return array('chua ton tai kho');
        foreach ($oStore->Equipment as $type => $item_arr )
        {
              foreach ($item_arr as $id => $oEquipment)
              {
                  if(!is_object($oEquipment)) continue ;
                  if($Durablity > 0)
                  {
                      $oEquipment->Durability -= $Durablity ;
                      if($oEquipment->Durability <0)
                        $oEquipment->Durability = 0 ;
                  }
                  if($time > 0 )
                  {
                      $oEquipment->StartTime -= $time ;
                      if($oEquipment->StartTime < 0)
                        $oEquipment->StartTime = 0 ;
                  }
                   
              }
        }
       
        $oStore->save();
        return 'Okay';
    }
    /**
    * cheat cho phan qua tang tan thu 
    * Time : cheat tang thoi gian cho doi
    * Gave : cheat so lan da nhan qua roi 
    */
    public function cheatNewUserBag($Time,$Gave)
    {
        if(empty($Time) && empty($Gave))
            return false ;
        $oUserPro = UserProfile::getById(Controller::$uId);
        if(!is_object($oUserPro))   
            return false ;
        if(!empty($Time))
        {
           $oUserPro->ActionInfo['NewUserGiftBag']['LastGetGiftTime'] -= $Time ; 
        }
        if(!empty($Gave))
        {
           $oUserPro->ActionInfo['NewUserGiftBag']['Gave'] = $Gave; 
        }
        
        $oUserPro->save();
        return true ;
        
    }
    
    /**
    * reset me cung
    * 
    */
    public function resetMazeMap()
    {
        $oEvent = Event::getById(Controller::$uId);
        
        $oEvent->EventList['PearFlower'] = array();
        
        $oEvent->save();
        return true ;
        
    }
    
    /**
    * cheat nam cham
    */
    public function cheatMagnet($NumUseLeft, $FreeUse, $ResetLastTimeUse){
        $oUser = User::getById(Controller::$uId);
        $oMagnet = $oUser->SpecialItem['Magnet'];
        if (!empty($NumUseLeft))
            $oMagnet->NumUseLeft = $NumUseLeft;
        if (!empty($FreeUse))
            $oMagnet->FreeUse = $FreeUse;
        if (!empty($ResetLastTimeUse))
            $oMagnet->LastTimeUse = 0;
        $oUser->SpecialItem['Magnet'] = $oMagnet;
        $oUser->save();
            
    }
   
   /**
   * reset so binh nang luong su dung trong ngay
   * 
   */
   public function resetMaxEnergyUse()   
   {
       $oUserPro = UserProfile::getById(Controller::$uId);
       $oUserPro->resetMaxEnergyUse();
       $oUserPro->save();
       return 'Okay';
   }
   
   
   /**
   * add item collection
   * ItemId: 
   * const TEM_KIM = 1;
   * const TEM_MOC = 2;
   * const TEM_THO = 3;
   * const TEM_THUY = 4;
   * const TEM_HOA = 5;
    
   *  const BONG_BIEN = 6;
   * const VIT_PHAO = 7;
   * const VAN_TRUOT = 8;
   * const TOM_THAN = 9;
   * const CUA_THAN = 10;
    
   * const AM_NUOC = 11;
   * const VO_LON = 12;
   * const XUONG_CA = 13;
   * const GIAY_CU = 14;
   * const DO_CO = 15;
   * 
   * const TRUNG_THAN = 16;
   * 
   * const KIM_VAY = 17;
   * const MOC_VAY = 18;
   * const THUY_VAY = 19;
   * const HOA_VAY = 20;
   * const THO_VAY = 21;
   * 
   * const KIM_LE = 22;
   * const MOC_LE = 23;
   * const THUY_LE = 24;
   * const HOA_LE = 25;
   * const THO_LE = 26;
   * const KIM_RAU = 27;
   * const MOC_RAU = 28;
   * const THUY_RAU = 29;
   * const HOA_RAU = 30;
   * const THO_RAU = 31;
   * const KIM_NHAN = 32;
   * const MOC_NHAN = 33;
   * const THUY_NHAN = 34;
   * const HOA_NHAN = 35;
   * const THO_NHAN = 36
   * const KIM_COT = 37;
   * const MOC_COT = 38;
   * const THO_COT = 39;
   * const THUY_COT = 40;
   * const HOA_COT = 41;
   * 
   */
   public function addItemCollection($ItemId, $Num,$isAll = false)
   {
       $oStore = Store::getById(Controller::$uId);
       if($isAll)
       {
           for($i = 1;$i<=41;$i++)
           {
            $oStore->addItem(Type::ItemCollection,intval($i),1000);
           }
       }
       else
       {
           if (intval($ItemId) <=0 || intval($ItemId)>41 || intval($Num) <=0)
            return 'Error param !';
           $oStore->addItem(Type::ItemCollection,intval($ItemId),intval($Num));
       }
       $oStore->save();
       return array('Okay !');
   }
   /**
   * cheat vi tri tren map
	* reset so lan su dung tonardo
	* reset max lan nhan hoa cap 5 
   */
   public function cheatEventPearFlower($x,$y,$is_resetTonardo = false,$resetMaxPearFlower = false,$fillMap = false )
   {       
       $oEvent = Event::getById(Controller::$uId);
       $PearFlower = $oEvent->getEvent('PearFlower');
       $oPearF = $PearFlower['Object'] ;
       if(!is_object($oPearF))
            return 'object null' ;
            
       $map_conf = Common::getConfig('Map',$oPearF->MapId);
       
       if(!empty($x) && !empty($y))
       {
           if(!isset($map_conf[$y][$x])) 
                return 'positon not Exist';
           
           $oPearF->Position['Y']  = $y;
           $oPearF->Position['X']  = $x;
       }
       
       if($is_resetTonardo)
       {
           $oPearF->TeleportNum = 0 ;
       }
       if($resetMaxPearFlower)
       {
           $PearFlower['MaxPearFlower'] = 0 ;
       }
       if($fillMap)
       {
           foreach ($map_conf as $y => $arr_x)
           {
              foreach ($arr_x as $x => $GiftId)
              {
                    if(!in_array($GiftId,array(1,2,3,4),true))
                    {
                        $Postion['Y']= $y ;
                        $Postion['X']= $x ;
                        $oPearF->saveHistory($Postion);
                    }
              } 
           }
       }
       
       $oEvent->save();
       return true ;
   }
   
   /**
   * cheat cho event sinh nhat
   * 
   *  $resetGiftInLight : reset cac lan nhan qua trong den than 
   *  $CandleId     : Id cua ngon nen
   *  $BurnLastTime : thoi gian bat nen  (yeu cau co $CandleId)
   *  $BlowNum      : so lan thoi nen (yeu cau co $CandleId)
   */
   public function BirthDayEvent($resetGiftInLight = false,$CandleId,$BurnLastTime,$BlowNum)
   {
       if(!Event::checkEventCondition(EventType::BirthDay))
            return 'ko trong thoi gian event';
       $oEvent = Event::getById(Controller::$uId);
       
       if($resetGiftInLight)
       {
           //$oEvent->EventList[EventType::BirthDay]['Light']['Gave']         = array();
           $oEvent->EventList[EventType::BirthDay]['Light']['LastTimeReset']= 0;
       }
       else if(!empty($CandleId) && (!empty($BurnLastTime)|| !empty($BlowNum)))
       {
           $oEvent->EventList[EventType::BirthDay]['Candle'][$CandleId]['BurnLastTime'] -= $BurnLastTime ;
           $oEvent->EventList[EventType::BirthDay]['Candle'][$CandleId]['BlowNum']      = $BlowNum ;
       }
       else
       {
           return 'thieu tham so';
       }
       $oEvent->save();
       return 'ok';
       
   }
   /**
   * cheat cho FishWorld
   */
   public function cheatFishWorld($is_reset = false,$IdseaUnlock = 0,$roundId = 0,$joinNum = 0)
   {
       $oWorld = FishWorld::getById(Controller::$uId);
       if($is_reset)
       {
           $oWorld->SeaList = array();
           $oWorld->SeaNum = 0;
           
       }
       if($IdseaUnlock != 0  && $roundId == 0 && $joinNum == 0)
       {
            $oWorld->addSea($IdseaUnlock) ;
            $oSea = $oWorld->getSea($IdseaUnlock) ;
       }
       if($roundId != 0)
       {
           $oSea = $oWorld->getSea($IdseaUnlock);
           if(!is_object($oSea)) return array('ban chua unlock bien nay') ;
           
           if($oSea->RoundNum > $roundId || $roundId < 1 || $roundId > 4)
           {
                return array(' sai RoundId') ; 
           }
           $oSea->createMonsterList($IdseaUnlock);
           
           foreach($oSea->Monster as $idRound => $arr )
           {
                 if($idRound < $roundId)
                    $oSea->Monster[$idRound] = array();
           }
           if($oSea->SeaId == SeaType::SEA_4 && $roundId > SeaRound::ID_ROUND_2)
           {
               unset($oSea->currentMonster);
           }
           $oSea->RoundNum = $roundId ; 
           $oWorld->ErrorFlag = 1 ;     
       }
       
       if($joinNum > 0 && $joinNum < 12)
       {
           $oSea = $oWorld->getSea($IdseaUnlock);
           if(!is_object($oSea)) return array('ban chua unlock bien nay') ;
           $oSea->JoinNum = $joinNum ;
       }
       

       $oWorld->save();
       
       return true ;
   }
   /**
   * thuc hien cheat mau cua quai
   * 
   * @param mixed $IdSea
   * @param mixed $RoundNum
   * @param mixed $IdMonster
   * @param mixed $Vitality
   * @return mixed
   */
   public function cheatMonster($IdSea,$RoundNum,$IdMonster,$Vitality,$damage)
   {
       $oWorld = FishWorld::getById(Controller::$uId);
       if(!is_object($oWorld)) return array('ban chua khoi tao the gioi ca') ;
       if(empty($IdSea))
        $IdSea = 1 ;
       
       $oSea = $oWorld->getSea($IdSea);
       if(!is_object($oSea)) return array('ban chua unlock bien nay') ;     
       
       if(empty($RoundNum)) $RoundNum = $oSea->RoundNum ;  
       
       if(empty($IdMonster)) return array('ban chua chon quai') ;          
       $oMonster = $oSea->getMonster($IdMonster,$RoundNum);   
       
       if(!is_object($oMonster)) return array('quai nay da chet') ;  
       if(!empty($Vitality))   
            $oMonster->Vitality = $Vitality ;
       if(!empty($damage))   
            $oMonster->Damage = $damage ;
       $oWorld->save();
       
       return true ;
   }
   
   /**
   * cheat all equipment in store 
   */
   public function cheatAllEquipment()
   {
       $oStore = Store::getById(Controller::$uId);
       $listEquip = Common::getConfig('Param','SoldierEquipment','MaxEquipment');
       $oUser = User::getById(Controller::$uId);
       
       $arrEquip = array(
        SoldierEquipment::Bracelet => array(1,2,3),
        SoldierEquipment::Belt => array(1,2,3),
        SoldierEquipment::Necklace => array(1,2,3),
        SoldierEquipment::Ring => array(1,2,3),
        SoldierEquipment::Armor => array(101,102,103,201,202,203,301,302,303,401,402,403,501,502,503),
        SoldierEquipment::Helmet => array(101,102,103,201,202,203,301,302,303,401,402,403,501,502,503),
        SoldierEquipment::Weapon => array(101,102,103,201,202,203,301,302,303,401,402,403,501,502,503),
       );
       
       /*
       foreach($listEquip as $name => $maxItem)
       {
           $conf_equip = Common::getConfig('Wars_'.$name);
           foreach($conf_equip as $rank => $oRank)
           {
               foreach($oRank as $color => $oEq)
               {
                   if ($oEq['UnlockType'] == 1)
                   {
                       $newEquip = new Equipment($oUser->getAutoId(),$oEq['Element'],$name,$rank,$color,rand($oEq['Damage']['Min'],$oEq['Damage']['Max']),
                            rand($oEq['Critical']['Min'],$oEq['Critical']['Max']),$oEq['Durability'],$oEq['Vitality'],SourceEquipment::SHOP);
                       $oStore->addEquipment($newEquip->Type, $newEquip->Id, $newEquip);
                   }
               }
           }
       }
       */
       
       foreach($arrEquip as $name => $leq)
       {
           $conf_equip = Common::getConfig('Wars_'.$name);
           foreach($leq as $index => $rank)
           {
                
                for($color = 1; $color <=4; $color++)
                {
                    $oEq = $conf_equip[$rank][$color]; 
                    $newEquip = new Equipment($oUser->getAutoId(),$oEq['Element'],$name,$rank,$color,rand($oEq['Damage']['Min'],$oEq['Damage']['Max']), rand($oEq['Defence']['Min'],$oEq['Defence']['Max']),
                            rand($oEq['Critical']['Min'],$oEq['Critical']['Max']),$oEq['Durability'],$oEq['Vitality'],SourceEquipment::SHOP);
                    $oStore->addEquipment($newEquip->Type, $newEquip->Id, $newEquip);        
                }
                
           }
       }
       
       
       $oUser->save();
       $oStore->save();
       return 'Okay';
   }
   
   
  /**
   * Cheat enchant level equipment
   * EnchantLevel : level equipment
   * Rank : Cap do
   * Color : 1-Binh thuong, 2-Dac biet, 3-Quy hiem
   */
   public function cheatEnchantEquipment($EnchantLevel, $Rank, $Color)
   {        
       if (intval($Rank) < 0 || intval($Rank) > 5)
         $Rank = 0;
         
       $EnchantLevel = intval($EnchantLevel);
       $Rank = intval($Rank);
       $Color = intval($Color);
       if ($EnchantLevel < 0 || $EnchantLevel > 9)
         return 'Enchant Level = 0->9';
       $oStore = Store::getById(Controller::$uId);
       $oUser = User::getById(Controller::$uId);
       
              
       $RankEq = intval($Rank);
       $itemAll = array(SoldierEquipment::Bracelet, SoldierEquipment::Belt, SoldierEquipment::Necklace, SoldierEquipment::Ring);
       $itemElement = array(SoldierEquipment::Armor, SoldierEquipment::Helmet, SoldierEquipment::Weapon);
       $arrEquip = array();
       foreach($itemAll as $itemName)
       {
           $arrEquip[$itemName][0] = $RankEq;
       }
       foreach($itemElement as $itemName)
       {
           for($i=1;$i<=5; $i++)
            $arrEquip[$itemName][$i] = $i*100+$RankEq;
       }
              
       foreach($arrEquip as $name => $leq)
       {
           $conf_equip = Common::getConfig('Wars_'.$name);
           foreach($leq as $index => $rank)
           {
                
                for($color = $Color; $color <=$Color; $color++)
                {
                    $oEq = $conf_equip[$rank][$color]; 
                    $newEquip = new Equipment($oUser->getAutoId(),$oEq['Element'],$name,$rank,$color,rand($oEq['Damage']['Min'],$oEq['Damage']['Max']), rand($oEq['Defence']['Min'],$oEq['Defence']['Max']),
                            rand($oEq['Critical']['Min'],$oEq['Critical']['Max']),$oEq['Durability'],$oEq['Vitality'],SourceEquipment::EVENT);
                    for ($i=1; $i<=$EnchantLevel; $i++)
                        $newEquip->enchant(101,true);
                    $oStore->addEquipment($newEquip->Type, $newEquip->Id, $newEquip);        
                }
                
           }
       }
       
       
       $oUser->save();
       $oStore->save();
       return 'Okay';
   }
   
   
   /**
   * put your comment there...
   * @MoneyTime: So lan nhan tien
   */
   public function cheatLimitBonusSoldier($SoldierId, $LakeId, $Exp, $Money, $Rank, $MoneyTime)
   {
       $oLake = Lake::getById(Controller::$uId, $LakeId);
       if (!is_object($oLake))
        return 'LakeId ???';
       $oSoldier = $oLake->getFish($SoldierId);
       if (!is_object($oSoldier))
        return 'SoldierId ???';
       $oSoldier->Bonus = array();
       $oSoldier->Bonus[0]['ItemType'] = Type::Exp;
       $oSoldier->Bonus[0]['ItemId'] = 1;
       $oSoldier->Bonus[0]['Num'] = intval($Exp);
       $oSoldier->Bonus[1]['ItemType'] = Type::Money;
       $oSoldier->Bonus[1]['ItemId'] = 1;
       $oSoldier->Bonus[1]['Num'] = intval($Money);
       $oSoldier->Bonus[2]['ItemType'] = Type::Rank;
       $oSoldier->Bonus[2]['ItemId'] = 1;
       $oSoldier->Bonus[2]['Num'] = intval($Rank);
       
       $oSoldier->Diary = array();
       $oSoldier->Diary[0]['UserId'] = 1;
       
       $oSoldier->numBonusMoney = 1;
       
       $oLake->FishList[$SoldierId] = $oSoldier;
       $oLake->save();
       return 'Okay';
   }
   
   /**
   * cheat for Lucky Machine
   * $Type = (1=>Money,2=>Mask,3=>EnergyItem,4=>RankPointBottle,5=>Exp)
   * $LevelGift  = 1-> 6
   * $TicketType =  '1=>Ticket' or '2=>LockTicket';
   */
   public function luckyMachine($Type = 1,$LevelGift = 1,$TicketType = 1)
   {
       $arr_Type = array(1=>'Money',2=>'Mask',3=>'EnergyItem',4=>'RankPointBottle',5=>'Exp');
       $arr_Ticket = array(1=>'Ticket',2=>'LockTicket');
        $oMGame = MiniGame::getById(Controller::$uId);
        if(!is_object($oMGame))
        return array('Error' => Error :: OBJECT_NULL);

        if(!isset($oMGame->GameList[GameType::LuckyMachine]))
        return array('Error' => Error :: OBJECT_NULL);
        $oMGame->GameList[GameType::LuckyMachine]['GiftArr']['ItemType'] = $arr_Type[$Type] ;
        $oMGame->GameList[GameType::LuckyMachine]['GiftArr']['LevelGift']= $LevelGift;
        $oMGame->GameList[GameType::LuckyMachine]['GiftArr']['TicketType']= $arr_Ticket[$TicketType];
        $oMGame->save();
        
        return 'cheat ok';
   }
   
   /**
   * Cheat event ZingPhone
   * 
   */
   public function ZP_cheatMatch($userId, $WeekId, $Win, $Lose, $isReset)
   {
       if (empty($userId))
            $uId = Controller::$uId;
          else $uId = $userId;
       $oUserPro = UserProfile::getById($uId);
       $oUserPro->ActionInfo['OutGameFW']['ByWeek'][$WeekId]['Win'] += intval($Win);
       $oUserPro->ActionInfo['OutGameFW']['Win'] += intval($Win);
       $oUserPro->ActionInfo['OutGameFW']['ByWeek'][$WeekId]['Lose'] += intval($Lose);
       $oUserPro->ActionInfo['OutGameFW']['Lose'] += intval($Lose);
       
       if ($isReset)
       {
           $oUserPro->ActionInfo['OutGameFW'] = array();
       }
       
       $oUserPro->save();  
       return 'Okay' ;
   }
   
   /**
   * put your comment there...
   * 
   */
   public function ZP_cheatTop($isMonth, $idWeek, $UserId1, $UserId2, $UserId3, $UserId4, $isReset, $isGetAll)
   { 
          $data = array();

          for($i=1;$i<=4;$i++)
          {
              $name = 'UserId'.$i;
              if (!empty($$name))
              {
                  $ooUser = User::getById($$name);
                  if (is_object($ooUser))
                  {
                      $data[$i-1]['uId'] = $$name;
                      $data[$i-1]['Name'] = $ooUser->getUserName();
                      $data[$i-1]['AvatarLink'] = $ooUser->AvatarPic;
                      $data[$i-1]['total'] = rand(1000,2000);
                      $data[$i-1]['win'] = rand(500,999);      
                  }
                  else
                  {
                      $data[$i-1]['uId'] = $$name;
                      $data[$i-1]['Name'] = 'aaa';
                      $data[$i-1]['AvatarLink'] = 'bbb';
                      $data[$i-1]['total'] = rand(1000,2000);
                      $data[$i-1]['win'] = rand(500,999);          
                  }
                  
              }
          }
          
          $key = 'EventInGameFW_Month';
          if (!$isMonth)
            $key = 'EventInGameFW_'.$idWeek;
          
          if ($isReset)
          {
              $oUserPro = UserProfile::getById(Controller::$uId);
              $oUserPro->ActionInfo['OutGameFW']['Reward']['Month'] = false;
              $oUserPro->ActionInfo['OutGameFW']['Reward'][1] = false;
              $oUserPro->ActionInfo['OutGameFW']['Reward'][2] = false;
              $oUserPro->ActionInfo['OutGameFW']['Reward'][3] = false;
              $oUserPro->ActionInfo['OutGameFW']['Reward'][4] = false;
              $oUserPro->save();
              
              DataProvider::getMemcache()->set('EventInGameFW_Month',array());   
              DataProvider::getMemcache()->set('EventInGameFW_1',array());   
              DataProvider::getMemcache()->set('EventInGameFW_2',array());   
              DataProvider::getMemcache()->set('EventInGameFW_3',array());   
              DataProvider::getMemcache()->set('EventInGameFW_4',array());   
              
              DataProvider::getMemcache()->set('LastTimeUpdateEventFW',0);
          }
          DataProvider::getMemcache()->set($key,$data);
          
          if ($isGetAll)
          {

                try
                {
                    $listTopMonth = Common::queryMySql('EventInGameFW','select * from topEventFW_byMonth order by win DESC, (win/total) DESC, total DESC limit 500','CommonEvent');    
                    $dataMonth = array();
                    $i = 0;
                    while($row = mysql_fetch_array($listTopMonth ,MYSQL_ASSOC))
                    {
                        $oUser = User::getById(intval($row['uId']));
                        if (is_object($oUser))
                        {
                            $dataMonth[$i]['uId'] = intval($row['uId']);
                            $dataMonth[$i]['win'] = intval($row['win']);
                            $dataMonth[$i]['total'] = intval($row['total']);
                            if (Common::getSysConfig('debug'))
                                $dataMonth[$i]['Name'] = $oUser->Name;
                            else $dataMonth[$i]['Name'] = $oUser->getUserName();
                            $dataMonth[$i]['AvatarLink'] = $oUser->AvatarPic;
                            $i++;    
                        }
                    }
                    DataProvider::getMemcache()->set('EventInGameFW_Month',$dataMonth); 
                    DataProvider::getMemcache()->set('LastTimeUpdateEventFW',$_SERVER['REQUEST_TIME']); 
                } catch (Exception $ee)
                {
                          
                }
                
                for ($indexWeek = 1; $indexWeek<=4; $indexWeek++)
                {
                    // get top week
                    try
                    {
                        $listTopWeek = Common::queryMySql('EventInGameFW','select * from topEventFW_byWeek where week_id = ' .$indexWeek. ' order by win DESC, (win/total) DESC, total DESC limit 500','CommonEvent');
                        $dataWeek = array();
                        $i = 0;
                        while($row = mysql_fetch_array($listTopWeek ,MYSQL_ASSOC))
                        {
                            $oUser = User::getById(intval($row['uId']));
                            if (is_object($oUser))
                            {
                                $dataWeek[$i]['uId'] = intval($row['uId']);
                                $dataWeek[$i]['win'] = intval($row['win']);
                                $dataWeek[$i]['total'] = intval($row['total']);
                                if (Common::getSysConfig('debug'))
                                    $dataWeek[$i]['Name'] = $oUser->Name;
                                else $dataWeek[$i]['Name'] = $oUser->getUserName();
                                $dataWeek[$i]['AvatarLink'] = $oUser->AvatarPic;
                                $i++;    
                            }
                        }

                        DataProvider::getMemcache()->set('EventInGameFW_'.$indexWeek,$dataWeek);
                        DataProvider::getMemcache()->set('LastTimeUpdateEventFW',$_SERVER['REQUEST_TIME']);    
                    } catch (Exception $ee)
                    {
                        
                    }
                }
          }
          
          return 'Okay';               
   }
   
   public function ZP_cheatLuckyUser($UserId1, $UserId2, $UserId3)
   {
          $todayLucky = array();

          for($i=1;$i<=3;$i++)
          {
              $name = 'UserId'.$i;
              if (!empty($$name))
              {
                  $ooUser = User::getById($$name);
                  if (is_object($ooUser))
                  {
                      $todayLucky[$i-1]['uId'] = $$name;
                      $todayLucky[$i-1]['Name'] = $ooUser->getUserName();
                      $todayLucky[$i-1]['AvatarLink'] = $ooUser->AvatarPic;
                      $oSoldier = Lake::selectSoldier($userId);
                      $todayLucky[$i-1]['maxSoldier'] = $oSoldier['Soldier'];            
                  }
                  else
                  {
                      $todayLucky[$i-1]['uId'] = $$name;
                      $todayLucky[$i-1]['Name'] = 'aaa';
                      $todayLucky[$i-1]['AvatarLink'] = 'bbb';
                      $oSoldier = Lake::selectSoldier(284847);
                      $todayLucky[$i-1]['maxSoldier'] = $oSoldier['Soldier'];            
                  }
                  
              }
          }
          
          DataProvider::getMemcache()->set('LuckyUserEvent_Today',$todayLucky);
          DataProvider::getMemcache()->set('LuckyUserEvent_PreviousDay',$todayLucky);
          return 'Okay';
   }
   
   public function cheatTimeForMessage($time)
   {
       $oMailBox = MailBox::getById(Controller::$uId);
       if(empty($oMailBox->list))
            return 'there is not Mail ' ;
       foreach($oMailBox->list as $id => $oMail)
       {
           if(!is_object($oMail)) continue ;
           $oMail->FromTime -= $time ; 
       }
       
       $oMailBox->save() ;
       
       $oSytemMailBox = SystemMail::getById(Controller::$uId);
       if(empty($oSytemMailBox->ListMailOwner))
            return 'there is not Mail ' ;
       foreach($oSytemMailBox->ListMailOwner as $id => $oMail)
       {
           if(!is_object($oMail)) continue ;
           $oMail->FromTime -= $time ; 
       }
       $oSytemMailBox->save() ;
   }
   /**
   * reset all date: ingredient, skills, buy gold
   * 
   */
   public function resetAllIngredients()
   {
   		$oIngre = Ingredients::getById(Controller::$uId);
   		if (!empty($oIngre)) {
   			DataProvider::delete(Controller::$uId, 'Ingredients');
   		}
   		$skills = Common::getConfig('Param', 'CraftingEquipSkills');
		$oIngredients = Ingredients::init(Controller::$uId, $skills);
        $oIngredients->save();
		
   		return 'Okie';
   }
   /**
    * Type: Iron, Jade, PowerTinh, SixColorTinh, SoulRock <$Rank>
    * Rank: 1,2,3, ...
    */
   public function cheatIngredient($Type, $Rank, $Num)
   {
   		$oIngredients = Ingredients::getById(Controller::$uId);
   		if (empty($oIngredients)) {
   			$skills = Common::getConfig('Param', 'CraftingEquipSkills');
			$oIngredients = Ingredients::init(Controller::$uId, $skills);
   		}
   		
   		switch ($Type) {
   			case 'SoulRock':
   				if(empty($oIngredients->SoulRock[$Rank]))
					$oIngredients->SoulRock[$Rank] = $Num;
				else $oIngredients->SoulRock[$Rank] += $Num;
   			break;
   			
   			default:
   				$oIngredients->$Type += $Num;
   			break;
   		}
   		$oIngredients->save();
   		return 'Okie'; 
   }
   
   /**
   * Hon Thach Rank = 1, 2, 3
   * 
   * @param mixed $Num
   * @return mixed
   */
   
   public function Ingredient_cheatAll($Num)
   {
        $oIngredients = Ingredients::getById(Controller::$uId);
       if (empty($oIngredients)) {
           $skills = Common::getConfig('Param', 'CraftingEquipSkills');
        $oIngredients = Ingredients::init(Controller::$uId, $skills);
       }
       
       $Types = array('Iron', 'Jade', 'PowerTinh', 'SixColorTinh', 'SoulRock') ;
       $RankMax = 3;
       foreach($Types as $Type)
       {
           switch ($Type) {
               case 'SoulRock':
                    for($Rank = 1; $Rank <= $RankMax; $Rank ++)
                    {
                        if(empty($oIngredients->SoulRock[$Rank]))
                        $oIngredients->SoulRock[$Rank] = $Num;
                        else $oIngredients->SoulRock[$Rank] += $Num;     
                    }                   
               break;
               
               default:
                   $oIngredients->$Type += $Num;
               break;
           }
       }
       
       $oIngredients->save();
       return 'Magic';
   }
   /**
   * max = 5 1 ngay
   */
   public function resetBuyGold()
   {
        $oIngredients = Ingredients::getById(Controller::$uId);
        $oIngredients->LastDateGoldBuyPower = '';
        $oIngredients->save();
        
        return 'Okie'; 
   }
   
   /**
   *    Skill = 'Armor', 'Weapon', 'Helmet', 'Jewel', 'Magic'
   */
   public function cheatCraftingSkill($Skill, $Level = 1, $Exp = 0)
   {
       $oIngredients = Ingredients::getById(Controller::$uId);
       $oIngredients->CraftingSkills[$Skill]['Level'] = $Level;
       $oIngredients->CraftingSkills[$Skill]['Exp'] = $Exp;
       $oIngredients->save();
       
       return 'Okie'; 
   }
   
   /**
   * cheat cho Event
   * 
   *  $Level     : so level cua cay
   *  $CareNum  : so lan cham soc
   *  $timeCare : giam di $timeCare thoi gian 
   *  $SpeedNum : so lan tua
   */
   public function coral_cheatTree($Level = 0 , $CareNum = 0 ,$timeCare = 0 ,$SpeedNum = 0)
   {
       /*if(!Event::checkEventCondition('Event_8_3_Flower'))
       {
           return array('ko ton tai event');
       }*/
       $oEvent = Event::getById(Controller::$uId);
       if(!is_object($oEvent))
            return array('object null');
       if($Level > 0)
       {
           $oEvent->EventList['Event_8_3_Flower']['Level'] = intval($Level) ;
           if($oEvent->EventList['Event_8_3_Flower']['Level']> 7)
                $oEvent->EventList['Event_8_3_Flower']['Level'] = 7 ;
            $oEvent->EventList['Event_8_3_Flower']['CareNum']       = 0;
            $oEvent->EventList['Event_8_3_Flower']['SpeedUpNum']    = 0;
            $oEvent->EventList['Event_8_3_Flower']['LastCareTime']  = $_SERVER['REQUEST_TIME'];
       }
       
       if($CareNum >0)
       {
           $oEvent->EventList['Event_8_3_Flower']['CareNum'] = intval($CareNum);
           $oEvent->EventList['Event_8_3_Flower']['LastCareTime']  = $_SERVER['REQUEST_TIME'];
       }
       
       if($timeCare > 0)
       {
           $oEvent->EventList['Event_8_3_Flower']['LastCareTime'] -= intval($timeCare);
           if($oEvent->EventList['Event_8_3_Flower']['LastCareTime']< 0 )
                $oEvent->EventList['Event_8_3_Flower']['LastCareTime'] = 0 ;
       }
       
       if($SpeedNum >= 0)
       {
           $oEvent->EventList['Event_8_3_Flower']['SpeedUpNum'] = intval($SpeedNum);
       }
       
       if($OpenBoxNum >0)
       {
           $oEvent->EventList['Event_8_3_Flower']['OpenBoxNum'] = $OpenBoxNum ;
           try
            {
                $oUser = User::getById(Controller::$uId);
                $Top = DataProvider::getMemBase()->get('MaxOpenBox_Event_8_3');
                $Max = intval($Top['Num']);
                if($oEvent->EventList['Event_8_3_Flower']['OpenBoxNum'] > $Max)
                {
                    $Top['Num'] = $oEvent->EventList['Event_8_3_Flower']['OpenBoxNum'] ;
                    $Top['uId'] = Controller::$uId ;
                    $Top['Name'] = $oUser->getUserName() ;
                    
                    DataProvider::getMemBase()->set('MaxOpenBox_Event_8_3',$Top);
                } 
            }
            catch(Exception $e)
            {

            }
           
       }
       if($resetBonus)
       {
           $oEvent->EventList['Event_8_3_Flower']['Bonus']= array();
       }
       
       $oEvent->save();
       return 'Ok';
       
       
   }
      
   public function resetMarket()
   {
       
       Market::del(Controller::$uId);
        
       
       $preAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
       DataRunTime::dec('Market_'.Controller::$uId,$preAutoMarket,true);
       
       $listPageType = array('Material','SuperFish','Armor','Helmet','Weapon','Ring','Bracelet','Necklace','Belt','Soldier','Other');      
       foreach($listPageType as $id => $pageType)
       {
           PageManagement::del($pageType); 
           $preAutoPM = DataRunTime::get('PageManagement_'.$pageType,true);
           DataRunTime::dec('PageManagement_'.$pageType,$preAutoPM,true);
           for ($i=1;$i<=50;$i++)
           {
               $preAt = DataRunTime::get('Page_'.$pageType.'_'.$i,true);
               DataRunTime::dec('Page_'.$pageType.'_'.$i,intval($preAt),true);
               Page::del($pageType,$i);
           }
       }
       return 'Okay';
   }
   
   public function cheatMagicPotion($idQuest, $randomQuest, $num, $done, $gotgift)
   {
        $idQuest = intval($idQuest);
        if ($idQuest > 3 || $idQuest < 1)
            return 'Error param';
        $oEvent = EventMagicPotion::getById(Controller::$uId)                 ;
        $oEvent->HerbQuest[$idQuest]['IdQuest'] = intval($randomQuest);
        $oEvent->HerbQuest[$idQuest]['Num'] = intval($num);
        $oEvent->HerbQuest[$idQuest]['Done'] = intval($done);
        if ($gotgift)
            $oEvent->HerbQuest[$idQuest]['GotGift'] = true;
        else $oEvent->HerbQuest[$idQuest]['GotGift'] = false;
        $oEvent->save();
        return 'Okay';
   }
   
   public function resetBoss()
   {
        $oUserPro = UserProfile::getById(Controller::$uId)    ;
        $oUserPro->ActionInfo['MagicPotion']['LastTimeAttackBoss'] = 0;
        $oUserPro->save();
   }
   
   public function resetQuestList()
   {
        $oEvent = EventMagicPotion::getById(Controller::$uId);
        $oEvent->LastTimeGenerate = 0;
        $oEvent->save();
   }
   
   public function cheatExchangeItem($ItemId, $Num, $isReset)
   {
        $oUserPro = UserProfile::getById(Controller::$uId);
        $oUserPro->ActionInfo['MagicPotion']['UseHerbPotion'][intval($ItemId)] = intval($Num);
        if ($isReset)
            $oUserPro->ActionInfo['MagicPotion']['LastTimeUse'] = 0;
        $oUserPro->save();    
        return 'Okay';
   }
   
   public function cheatPointReceived($Num)
   {
       $oPower = PowerTinhQuest::getById(Controller::$uId);
       $oPower->PointReceived = intval($Num);
       $oPower->save();
   }
   
   public function resetPowerTinh()
   {
       $oPwer = PowerTinhQuest::getById(Controller::$uId);
       $oPwer->LastTimeAccess = 0;
       $oPwer->save();
   }
   
   public function cheatAllGem($Num, $day)
   {
       $oStore = Store::getById(Controller::$uId);
       for ($i = 0; $i<=20; $i++)
       {
           for($ele = 1; $ele <=5; $ele++)
           {
               $oStore->addGem($ele,$i,intval($day),intval($Num));
           }
       }
       $oStore->save();
       return 'Okay';
   }
   /**
   * Add/Edit Seal
   * Add: Rank = 1, 2; Color = 3,4 (quy, than), 
   * Edit: Id, Level = 1, ... (so tinh an unlocked)
   * 
   */
   public function cheatSeal($id, $rank, $color, $level)
   {
        if(empty($rank)&& empty($color) && empty($id) && empty($level))
            return 'Empty Param';
       $id = intval($id);
       $rank = intval($rank);
       $color = intval($color);
       $level = intval($level);
            
        $oStore = Store::getById(Controller::$uId);
        $oUser = User::getById(Controller::$uId);
        if(empty($id))
        {
            $id = $oUser->getAutoId();
            $oUser->save();       
        }    
        else 
        {
            $oldSeal = $oStore->getEquipment('Seal', $id);
        }
        if(empty($rank))
            $rank = $oldSeal->Rank;
        if(empty($color))
            $color = $oldSeal->Color;
        $type = 'Seal';
        $oSeal = new Seal($type, $id, $rank, $color);                
        if(!empty($level) && ($level > 0))
        {
            $sealConf = Common::getConfig('Wars_Seal', $rank, $color);
            $sealConf = $sealConf[$level];
            $oSeal->updateSealOption($level, $sealConf); 
        }                   
        $oStore->addEquipment('Seal', $id, $oSeal);
        $oStore->save();
        
        return 'Okie';
   }  
   

   public function cheatLuckyMachine($resetInfoUser= false , $resetInfoServer = false , $PlayNum_2 = 0 , $PlayNum_10 = 0)
   {
       $oMGame = MiniGame::getById(Controller::$uId);
       if($resetInfoUser)
       {
           $oMGame->GameList[GameType::LuckyMachine]['Limit'] = array();
       }
       if($resetInfoServer)
       {
           $Gift6 = DataRunTime::get('LuckyMachine_Gift6');
           $VipWeapon = DataRunTime::get('LuckyMachine_VipWeapon');
           DataRunTime::dec('LuckyMachine_Gift6',$Gift6);
           DataRunTime::dec('LuckyMachine_VipWeapon',$VipWeapon);
       }
       
       if($PlayNum_2)
       {$oMGame->GameList[GameType::LuckyMachine]['Limit']['Play_Limit_2'] +=$PlayNum_2 ;}
       if($PlayNum_10)
       {
           $oMGame->GameList[GameType::LuckyMachine]['Limit']['Play_Limit_10'] +=$PlayNum_10 ;
       }
       
       $oMGame->save();
       
       return 'OK' ;
   }
   
   public function cheatMeridian($soldierId, $meridianPoint, $meridianRank, $meridianPosition)
   {
      $userId = Controller::$uId;
      $oStoreEquip = StoreEquipment::getById($userId);
      if(!empty($soldierId))
      {                                                                         
           $oStoreEquip->listMeridian[$soldierId]['meridianPoint'] = $meridianPoint;   
           $oStoreEquip->listMeridian[$soldierId]['meridianRank'] = $meridianRank;         
           $oStoreEquip->listMeridian[$soldierId]['meridianPosition'] = $meridianPosition;  
          if(empty($oStoreEquip->listMeridian[$soldierId]['meridianRank']))
          {
              $oStoreEquip->listMeridian[$soldierId]['meridianRank'] = 1;
          }
          $oStoreEquip->save();                                                                                                                               
      }
      else
      {
            $arrSoldier = Lake::getAllSoldier($userId,true,true,true);
           foreach($arrSoldier as $lakeId => $soldierArr) 
           {
               foreach($soldierArr as $name => $value)   
               {
                     $oStoreEquip->listMeridian[$name]['meridianPoint'] = $meridianPoint;   
                     $oStoreEquip->listMeridian[$name]['meridianRank'] = $meridianRank;         
                     $oStoreEquip->listMeridian[$name]['meridianPosition'] = $meridianPosition;  
                     if(empty($oStoreEquip->listMeridian[$name]['meridianRank']))
                     {
                          $oStoreEquip->listMeridian[$name]['meridianRank'] = 1;
                     }
               }
           }
           $oStoreEquip->save();    
      }
      
      return 'OK';
   }
   
   //training ground
   // reset all
   // reset tua
   // 
   public function TrainingGround($resetAll = false,$resetTrainingTimeOfFish = false)  
   {
       if($resetAll)
       {
           DataProvider::delete(Controller::$uId,'TrainingGround');
           
           $oTrain = TrainingGround::getById(Controller::$uId);
            $oTrain->save() ;
       }
       
       if($resetTrainingTimeOfFish)
       {
           $oTrain = TrainingGround::getById(Controller::$uId);
           
           $oTrain->Room['FishTimeList'] =  array()  ;
           
           $oTrain->save();  
       }
       
   }
   // cheat IceCream Event
   public function IceCream_Event($ResetAll = false , $ResetHeavyRain = false)
   {
       $oEvent = Event::getById(Controller::$uId);
       
       if(!isset($oEvent->EventList[EventType::IceCream]))
            return array('Object Null');
       if($ResetAll)
       {
           $oEvent->resetEvent(EventType::IceCream);
           
           unset($oEvent->EventList[EventType::IceCream][8]);          
           unset($oEvent->EventList[EventType::IceCream][9]);          
           unset($oEvent->EventList[EventType::IceCream][10]);          
           unset($oEvent->EventList[EventType::IceCream][11]);          
           unset($oEvent->EventList[EventType::IceCream][12]);          
           unset($oEvent->EventList[EventType::IceCream][13]);          
           unset($oEvent->EventList[EventType::IceCream][14]);          
       }
       else if($ResetHeavyRain)
       {
           $oEvent->EventList[EventType::IceCream]['Rain']['HeavyRain'] = 10;
       }
       else
       {
           echo 'nhap thieu thong tin'; 
           return ;
       }
       
       $oEvent->save();
       
       return array('Ok');
       
       
   }
   /**
   * Event Euro
   * Team1, Team2: POL,GRE,RUS,CZE,NER,DEN,GER,POR,SPA,ITA,IRE,CRO,FRA,ENG,UKR,SWE
   * MatchType: BOARD(vong bang), QUAD, SEMI, FINAL
   * Star : 1,2,3
   * TimeBeginFromNow: thoi gian tran dau bat dau, tinh tu hien tai
   * TimeBetBeforeBegin: khoang thoi gian cuoc
   * EMPTY, set DEFAULT ('VIE', 'GER', 'BOARD', 3, 30, 60)
   */
   public function euro_addNewMatch($Team1, $Team2, $MatchType, $Star, $TimeBeginFromNow, $TimeBetFromNow)
   {
        $Team1 = (empty($Team1)) ? 'ITA' : $Team1;    
        $Team2 = (empty($Team2)) ? 'GER' : $Team2;
        $MatchType = (empty($MatchType)) ? 'BOARD' : $MatchType;
        $Star = (empty($Star)) ? 3: $Star;
        $TimeBeginFromNow = (empty($TimeBeginFromNow)) ? 300: $TimeBeginFromNow;
        $TimeBetFromNow = (empty($TimeBetFromNow)) ? 0: $TimeBetFromNow;   
        
        $Fixture = DataProvider::get('share','EuroFixture');
        
        $betTimeEnd = time() + $TimeBeginFromNow;
        $betTimeBegin = time() + $TimeBetFromNow;
        $endMatch = $betTimeEnd + 6*60*60;
        $newmatch = array(
            'Team1' => $Team1,
            'Team2' => $Team2,
            'Goal' => array(),
            'Result' => 0,
            'Penalty' => false,
            'MatchTimeBegin' => $betTimeEnd,
            'BetTimeBegin' => $betTimeBegin,
            'BetTimeEnd' =>  $betTimeEnd,
            'MatchType' => $MatchType,
            'Star' => $Star,
            'UpdateResultTime' => $endMatch,
            'BetStat' => ($MatchType == 'BOARD') ? array(1 => 0, 2 => 0, 3 => 0) : array(1 => 0, 3 => 0) 
        );
        $index = count($Fixture);
        $Fixture[$index + 1] = $newmatch;
        DataProvider::set('share','EuroFixture', $Fixture);
        return "That's magic";        
   }
   /**
   * reset Euro Data 
   * 
   * @param mixed $Data : EuroFixture, EuroTop, UserEuro,
   */
   public function euro_resetData($Data)
   {
       switch($Data)
       {
           case 'EuroFixture':
                DataProvider::delete('share', 'EuroFixture');
                DataProvider::delete('share', 'EuroInfo');
                break;
           case 'EuroTop':
                DataProvider::delete('share', 'EuroTop', 'Order');
                DataProvider::delete('share', 'EuroTop', 'Profile');
                break; 
           case 'UserEuro':
                  $oEvent = Event::getById(Controller::$uId);
                  if(is_object($oEvent)) 
                  {
                    unset($oEvent->EventList['EventEuro']);
                    $oEvent->save();
                  }
                break;
       }
       return "That's magic";
   }
   
   /**
   * put your comment there...
   * 
   * @param mixed $MatchId
   * @param mixed $GoalTeam1
   * @param mixed $GoalTeam2
   * @param mixed $Penalty : true/false
   * @return mixed
   */
   public function euro_updateResultMatch($MatchId, $GoalTeam1, $GoalTeam2, $Penalty)
   {
       $Fixture = DataProvider::get('share','EuroFixture');
       if(!isset($Fixture[$MatchId]))
            return 'Fail MatchId';
       
       $GoalTeam1 = (empty($GoalTeam1)) ? 0 : $GoalTeam1;    
       $GoalTeam2 = (empty($GoalTeam2)) ? 0 : $GoalTeam2;
       $Penalty = (empty($Penalty)) ? false : $Penalty;
       
       if($GoalTeam1 == $GoalTeam2)
            $result = 2;
       elseif($GoalTeam1 > $GoalTeam2)
            $result = 1;
       else $result = 3;
       
       if(($Fixture[$MatchId]['MatchType'] != 'BOARD') && ($result == 2))
       {
           return 'Error. Tran dau ko cho phep ti so hoa';
       }
       
       $Fixture[$MatchId]['Result'] = $result;
       $Fixture[$MatchId]['Penalty'] =  (boolean)$Penalty;
       $Fixture[$MatchId]['Goal'] = array($GoalTeam1, $GoalTeam2);
       
       DataProvider::set('share','EuroFixture', $Fixture);
       
       $EuroInfo = DataProvider::get('share','EuroInfo');
        $EuroInfo['LastMatchUpdateResult'] = $MatchId;
        DataProvider::set('share','EuroInfo', $EuroInfo);
       //$matches = array_keys($Fixture);
//       var_dump($matches);
       
       return "That's magic";
   }
   
   /**
   * for admin add Top
   * 
   */
   public function euro_addTopUser($AdminCode, $Order, $Uid, $Medal, $RightBetNum, $BetNum, $LastBettedMatch, $LastBet)   
   {
      
      if($AdminCode != 18322500)
        return 'Enter denied. Please contact Admin' ;
        
      $TopProfile = DataProvider::get('share','EuroTop','Profile');
      $TopOrder = DataProvider::get('share','EuroTop','Order');
      $oUser = User::getById($Uid);
      if(!is_object($oUser))
            return 'Not exist';
      $TopOrder[$Order] = $Uid;
      $TopProfile[$Uid] = array(
        'Order' => $Order,
        'Medal' => $Medal,
        'RightNum' => $RightBetNum,
        'BetNum' => $BetNum,
        'LastBetMatch' => $LastBettedMatch,
        'LastBet' => $LastBet,        
      );
      
      DataProvider::set('share','EuroTop', $TopOrder,'Order');
      DataProvider::set('share','EuroTop',$TopProfile,'Profile') ;
      
      return 'Magic';       
   }
   
   /**
   * 
   * 
   * @param mixed $Achieved: 1..5 : 1st, 2nd, 3rd, 4-100th, > 200 medal
   */
   public function euro_addEuroTopAchieved($Achieved)
   {
		$oEvent = Event::getById(Controller::$uId);

        $oEvent->EventList[EventType::EURO]['GotAchieved'] = false;
        $Achieved = (empty($Achieved)) ? 6 : $Achieved;

        $oEvent->EventList[EventType::EURO]['EuroAchieved'] = $Achieved;
        $oEvent->save();
        
        return 'Magic';
   }
   
   public function euro_addLimitVIPBall($Num)
   {
       $Num = (empty($Num)) ? 30 : $Num;
       $oEvent = Event::getById(Controller::$uId);
       $Num = $oEvent->euro_addLimitVipBall($Num);
       $oEvent->save();
       return 'Magic'.$Num;
   }
   
   /**
   * reset & reload
   * 
   */
   public function NPC_deleteNPC()
   {
        $NPCId = NPC::NPC_SIGN.Controller::$uId;
        DataProvider::delete($NPCId, 'User')  ;
        DataProvider::delete($NPCId, 'StoreEquipment')  ;
        DataProvider::delete($NPCId, 'Lake')  ;
        DataProvider::delete($NPCId, 'Decoration')  ;
        DataProvider::delete($NPCId, 'UserProfile')  ;
        
        return 'Magic';
   }
   
   public function NPC_patternDecorations()
   {
       $oDeco1 = Decoration::getById(Controller::$uId, 1); 
       $oDeco2 = Decoration::getById(Controller::$uId, 2);
       $file = ROOT_DIR.'/web/NPCDeco1.csv';
       $lstIndex = array('ItemType', 'ItemId', 'X','Y','Z');
       if(!empty($oDeco1->ItemList))
            foreach($oDeco1->ItemList as $id => $item)
            {
                foreach($lstIndex as $index)
                {
                    $data[$index] = $item[$index];
                }
               $isOk = file_put_contents($file, $data, FILE_APPEND); 
               file_put_contents($file, '\n', FILE_APPEND); 
            }
               
       if($isOk)
            return 'Magic';
       else return 'Fail';
   }
   
   
   /**
   * edit thong tin boss
   * 
   */
   public function SB_EditBoss($Vitality)
   {
       $oServerBoss = ServerBoss::getById(Controller::$uId);
       if(!is_object($oServerBoss))
       {
           return 'ban chua tao doi tuong ServerBoss';
       }
       $result = $oServerBoss->getCurrentDetailKey();
       if(empty($result))
            return 'ko co boss';
       $query = sprintf('UPDATE BossServer_Boss SET VitalityTotal=%d where Date = to_days("%s") and Time = %d and BossId = %d',intval($Vitality),$result['Day'],$result['Time'],$result['BossId']);       
       try
        {
            $Data = Common::queryMySql('ServerBoss',$query,'');     
            if(!$Data)
                return array('Error'=> Error::PARAM);
        }
        catch(Exception $e)
        {
            echo 'access false';
        }
        
       return '<br>ok' ;     
   }
   /**
   * them server boss
   */
   public function SB_createBoss($day,$time,$bossid,$Vitality)
   {
       $oServerBoss = ServerBoss::getById(Controller::$uId);
       if(!is_object($oServerBoss))
       {
           return 'ban chua tao doi tuong ServerBoss';
       }
      $aa=  $oServerBoss->sql_createBoss($day,$time,$bossid,$Vitality);   
       echo '<pre>'; 
       var_dump($aa);
       echo '</pre>';
       return '<br>ok' ;     
   }
   /**
   * get top User
   */
   public function SB_getTopUser($day,$time,$bossid)
   {
       $oServerBoss = ServerBoss::getById(Controller::$uId);
       
       $aa=  $oServerBoss->sql_getTopUser($day,$time,$bossid);   
       echo '<pre>'; 
       var_dump($aa);
       echo '</pre>';
       return '<br>ok' ;     
   }
  
   public function SB_getInfoUser()
   {
       
        $oSeverBoss = ServerBoss::getById(Controller::$uId);  
        
        $result = array();
        $result['UserInfo'] = $oSeverBoss->BossList ;
        foreach($oSeverBoss->BossList as $keyBoss => $arr_info)
        {
            $result[$keyBoss]['Boss_Membase'] = DataRunTime::get('BossInfo'.$keyBoss,true); 
            $result[$keyBoss]['LastHit']      = DataRunTime::get('LastHit'.$keyBoss,true); 
            $result[$keyBoss]['TopUser']      = DataRunTime::get('TopUser'.$keyBoss,true); 
        }

        echo '<pre>';
        var_dump($result);
        echo '</pre>';
        return '0k' ; 
       
   }
   
   /**
   * xoa thong tin severboss
   * 
   * $is_boss_Database : xoa boss trong mysql
   * $is_boss_Membase   : xoa thong tin in cache
   * $userInfo      : xoa thong tin user
   * $attackBoss_Database   : xoa thong tin danh danh boss trong mysql
   */
   public function SB_deleteInfo($is_boss_Database = false,$is_boss_Membase = false,$userInfo = false,$attackBoss_Database = false ,$all = false)
   {
       $oSeverBoss = ServerBoss::getById(Controller::$uId);  
       
       foreach($oSeverBoss->BossList as $keyBoss => $arr_info)
       {
           if(empty($keyBoss))
                continue ;
           $result = $oSeverBoss->getCurrentDetailKey($keyBoss);
           
            if($is_boss_Database||$all)
            {               
                 $query = sprintf('DELETE FROM BossServer_Attack WHERE Date = to_days("%s") and %d = Time and %d = BossId ;',$result['Day'],$result['Time'],$result['BossId']);
                try
                {
                  $Data = Common::queryMySql('ServerBoss',$query,'');     
                  if(!$Data)
                    return array('loi query');
                }
                catch(Exception $e)
                {
                  
                }
            }
       
        
           if($is_boss_Membase||$all)
           {
                $bossInfo = array();
                DataRunTime::set('BossInfo'.$keyBoss,$bossInfo,true);
                DataRunTime::set('LastHit'.$keyBoss,$bossInfo,true);
                DataRunTime::set('TopUser'.$keyBoss,$bossInfo,true);
           }
           if($userInfo||$all)
           {
                $oSeverBoss = ServerBoss::getById(Controller::$uId);
                $oSeverBoss->BossList = array();
                $oSeverBoss->save();
           }
           
           if($attackBoss_Database || $all)
           {
               $query = sprintf('DELETE FROM BossServer_Attack WHERE Date = to_days("%s") and %d = Time and %d = BossId ;',$result['Day'],$result['Time'],$result['BossId']);
                try
                {
                  $Data = Common::queryMySql('ServerBoss',$query,'');     
                  if(!$Data)
                    return array('loi query');
                }
                catch(Exception $e)
                {
                  
                }
           }
       }
       
       return 'ok';
       
       
       
   }
   
  /**
  * reset Occupy Profile
  *  
  */
   
   public function Occ_resetOccupyProfile()
   {               
        $oStore = Store::getById(Controller::$uId);
        $oStore->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_DEFAULT] = 0;
        $oStore->save();
        
        DataProvider::delete(Controller::$uId, 'OccupyingProfile')    ;
             
        return 'Magic';
   }
   
   public function Occ_resetInitialRankBoard()
   {    
        $sql = 'delete from Occupy_OccupyingBigBoard';
        $res = Common::queryMySql(OccupyFea::CODE, $sql);
        if(!$res)
            return 'Fail';    
        $sql = 'delete from Occupy_OccupiedBigBoardBak';
        $res = Common::queryMySql(OccupyFea::CODE, $sql);
        if(!$res)
            return 'Fail';    
        DataProvider::delete('share','Top10OccupierCache');    
        DataProvider::getMemBase()->delete('Total_OccupyRankInitial_DataRunTime'); 
        return 'Magic';
   }
   
   /**
   * Set Rank for a new user to join Occupy
   * 
   * @param mixed $Rank
   * @return int
   */
   
   public function Occ_setInitialRank($Rank)
   {
        if(empty($Rank) || ($Rank < 2))
            {
                $currRank = DataRunTime::get('OccupyRankInitial', true);                
            }
        else
        {
            $currRank = $Rank - 1;
            DataRunTime::set('OccupyRankInitial', $currRank, true);
        }
        $nextRank = $currRank + 1; 
        
        return 'Next Rank : '.$nextRank;            
   }
   
   /**
   * for gift Token
   * back Past Num days
   * 
   */
   public function Occ_resetNewDate($Num)
   {
        $Num = (empty($Num)) ? 1 : $Num;
        $oProfile = OccupyingProfile::getByUid(Controller::$uId);
        if(!empty($oProfile))
            {
                $oProfile->LastGiftToken = date('Ydm', $_SERVER['REQUEST_TIME'] - $Num*24*60*60);
                $oProfile->save();
            }
        else return 'Join Occupy First !'    ;
        return 'Magic';
   }
   
   /**
   * Number of remain for occupying
   * 
   * @param mixed $Num
   * @return mixed
   */
   public function Occ_occupyCount($Num)
   {
       $Num = (empty($Num)) ? 1 : $Num;
       $oProfile = OccupyingProfile::getByUid(Controller::$uId);
        if(!empty($oProfile))
            {
                $oProfile->RemainOccupyCount = $Num;
                $oProfile->save();
            }
        else return 'Join Occupy First !'    ;
        return 'Magic';
   }
   
   public function Occ_useToken($Num)
   {
       $oProfile = OccupyingProfile::getByUid(Controller::$uId);
       if(!empty($oProfile))
            {
                $oStore = Store::getById(Controller::$uId);
                if(!$oStore->useItem(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_DEFAULT, $Num))
                    return 'Lack of Token';
                 $oProfile->addItems(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_GIFT, -$Num);
                 $oStore->save();
                 $oProfile->save();
            }
        else return 'Join Occupy First !'    ;
        return 'Magic';
   }
   
   public function Occ_init1000()
   {
        for($i = 1; $i <= 1000; $i++)
        {
            $initArr[] = "({$i}, {$i})";
        }
        $sql = 'insert ignore into Occupy_OccupyingBigBoard(Rank, Uid) VALUES '. implode(',', $initArr);
        $res = Common::queryMySqli(OccupyFea::CODE, $sql);
        return $res;
   }
   
   public function createPassword($password)
   {
       $oUser = User :: getById(Controller::$uId) ;                                   
       $oUser->passwordState = PasswordState::IS_LOCK;     
       $md5Password = md5($password);
       $oUser->setMd5Password($md5Password);
       $oUser->save();
       return 'ok';
   }
   
   public function deletePassword()
   {       
       $oUser = User :: getById(Controller::$uId) ;                                   
       $oUser->passwordState = PasswordState::NO_PASSWORD;     
       $md5Password = "";
       $oUser->setMd5Password($md5Password);
       $oUser->save();
       return 'ok';
   }
   
   /**
   * complete to Series, to Quest
   * after cheated, refresh User to new Series Quest. if not enough level require, please cheat level
   * 
   */
   public function MQ_completeSeriesQuest($SeriesId, $QuestId, $InMainQuest = true)
   {
       if(empty($SeriesId))
            return 'No Input. Stay current Quest';
       $InMainQuest = (empty($InMainQuest)) ? true : $InMainQuest ;
       $oQuest = Quest::getById(Controller::$uId);
                 
       $seriesdone = false;
       
       if(!empty($QuestId))
       {
           if(empty($oQuest->QuestInfo[$SeriesId][$QuestId]))
                return 'Quest not existed';         
           if(!empty($oQuest->QuestInfo[$SeriesId][$QuestId+1]))
           {
               $oQuest->QuestInfo[$SeriesId][$QuestId]['Status'] = TRUE;
               $oQuest->QuestInfo[$SeriesId]['Step'] = $QuestId + 1;  
           }              
           else
           {
               $seriesdone = true;
           }
       }
       else $seriesdone = true;
       if($seriesdone)
       {
           for($i = 1; $i <= $SeriesId; $i++)           
                $oQuest->QuestInfo[$i]['Status'] = TRUE;
           if($InMainQuest)
                $oQuest->MainQuestSeriesId = $SeriesId;
       }       
       $oQuest->save();
       
       return 'SeriesId:'.$SeriesId.', MainQuest:'.$oQuest->MainQuestSeriesId .'.Complete';
   }
   
   /**
   * get Desire Series, Quest, without included conditions for done it
   * 
   * @param mixed $SeriesId
   * @param mixed $QuestId
   * @param mixed $InMainQuest
   * @return mixed
   */
   public function MQ_getSeriesQuest($SeriesId, $QuestId, $InMainQuest = true)
   {
        if(empty($SeriesId))
            return 'No Input. Stay current Quest';
       $InMainQuest = (empty($InMainQuest)) ? true : $InMainQuest ;
       $oQuest = Quest::getById(Controller::$uId);
       
       // reach SeriesId
       for($i = 1; $i < $SeriesId; $i++)           
            $oQuest->QuestInfo[$i]['Status'] = TRUE;
       if($InMainQuest)
            $oQuest->MainQuestSeriesId = $SeriesId;
       
       $oQuest->QuestInfo[$SeriesId]['Status'] = FALSE ;
        $oQuest->QuestInfo[$SeriesId]['Id'] = $SeriesId ;
        
        $oQuest->QuestInfo[$SeriesId]['Active'] = TRUE ;        
        $QuestConfig = & Common::getConfig('SeriesQuest', $SeriesId, 'Quest') ;
        foreach($QuestConfig as $idq => $quest)
        {
              if(!is_array($quest)|| !isset($idq))
              {
                    continue;
              }
              $oQuest->QuestInfo[$SeriesId][$idq]['Status'] = FALSE ;
              $oQuest->QuestInfo[$SeriesId][$idq]['Id'] = $idq ;
              // neu ton tai Task
              if(is_array($quest['TaskList']))
              {
                foreach($quest['TaskList'] as $idt => $task)
                {
                 $oQuest->QuestInfo[$SeriesId][$idq][$idt]['Status'] = FALSE ;
                 $oQuest->QuestInfo[$SeriesId][$idq][$idt]['Num'] = 0 ;
                 $oQuest->QuestInfo[$SeriesId][$idq][$idt]['Id'] = $idt ;
                }
              }
        }
        // reach Quest
        if(!empty($QuestId))
            $step = $QuestId;
        else $step = 1;               
        $oQuest->QuestInfo[$SeriesId]['Step'] = $step ;
        
        $oQuest->save();
        
        return 'Magic' ;      
   }
   
   public function ItemCodeManager($reset)
   {
       if($reset)
        {
            $oItemcode = ItemCode::getById(Controller::$uId);
            if(!is_object($oItemcode))
                return 'chua co doi tuong';
            $oItemcode->ItemCode['Code'] = array();
            $oItemcode->ItemCode['ConfigId'] = array();     
              
            $result['Error'] = Error::SUCCESS;
        }
        
        $oItemcode->save();
        
        return 'Ok';
   }
   /*public function ItemCodeManager($reset)
   {
       if($reset)
        {
            $oItemcode = ItemCode::getById(Controller::$uId);
            if(!is_object($oItemcode))
                return 'chua co doi tuong';
            $oItemcode->ItemCode['Code'] = array();
            $oItemcode->ItemCode['ConfigId'] = array();
            DataProvider::set(-111,'ConfigItemCode',array());
        }
        
        $oItemcode->save();
        
        return 'Ok';
   }*/
   
   public function hal_resetEvent()
   {
       $oEvent = Event::getById(Controller::$uId);
       unset($oEvent->EventList[EventType::Halloween]);
       $oEvent->resetEvent(EventType::Halloween);
       $oEvent->save();
       
       return 'Magic';
   }
   
   public function hal_setBabyChipPos($X, $Y)
   {
       $oEvent = Event::getById(Controller::$uId);
       $data = $oEvent->EventList[EventType::Halloween];
       $oHalScene = $data['HalScene'];
       if(isset($oHalScene->Map[$X][$Y]))
       {
            $oHalScene->BabyChip = array($X, $Y);
            $oHalScene->Map[$X][$Y][0] = 0;
       }
            
       else return 'Out of Map';
       
       $oEvent->EventList[EventType::Halloween] = $data;
       
       $oEvent->save();
       
       return 'Magic';
   }
   /**
   * put your comment there...
   * 
   * @param mixed $X
   * @param mixed $Y
   * @param mixed $State: 2 = freze, 1 = lock, 0 = unlock
   * @param mixed $ItemId: 1-6:item thuong, 7-9:item quy, 10-12:than, 15:ruong thuong, 16:ruong than
   * @param mixed $ActionId: 1: bi gheo, 0: binh thuong
   * @return mixed
   */
   public function hal_setMapItem($X, $Y, $State, $ItemId, $ActionId)
   {
       $oEvent = Event::getById(Controller::$uId);
       $data = $oEvent->EventList[EventType::Halloween];
       $oHalScene = $data['HalScene'];
       if(isset($oHalScene->Map[$X][$Y]))
       {
           $State = (empty($State)) ? 1 : $State;
           $ItemId = (empty($ItemId)) ? rand(1,6) : $ItemId;
           $ActionId = (empty($ActionId)) ? 0: $ActionId;
           
           $oHalScene->Map[$X][$Y] = array($State, $ItemId, $ActionId);
       }
            
       else return 'Out of Map';
       
       $oEvent->EventList[EventType::Halloween] = $data;
       
       $oEvent->save();
       
       return 'Magic';
   }
   
   public function hal_cheatHadKey()
   {
      $oEvent = Event::getById(Controller::$uId);
      $data = $oEvent->EventList[EventType::Halloween];
      $data['FindKey'] = EventHal2012::NUM_ORD_GIFT - 1;
      $oEvent->EventList[EventType::Halloween] = $data;
       
      $oEvent->save();
       
      return 'Magic';
   }
   public function hal_RemainCountJoin($num)
   {
        $oEvent = Event::getById(Controller::$uId);
      $data = $oEvent->EventList[EventType::Halloween];
      $data['RemainPlayCount'] = $num;
      $oEvent->EventList[EventType::Halloween] = $data;
       
      $oEvent->save();
       
      return 'Magic';
   }
   /**
   * Them va bo so lan su dung
   * 
   * @param mixed $Proper: Magnetic, Protector, Speeduper
   */
   public function moon_changeNumUse($Proper, $Num)
   {
       $oEvent = Event::getById(Controller::$uId);
       if(isset($oEvent->EventList[EventType::MidMoon]))
       {
           $Proper = 'NumUse_'.$Proper;
           $oLantern = $oEvent->EventList[EventType::MidMoon]['Lantern'];
           $oLantern->addChar($Proper, $Num) ;
           $oEvent->EventList[EventType::MidMoon]['Lantern'] = $oLantern ;
       }else return 'Not Exist Event';
       
       $oEvent->save();
       
       return "Magic";
   }
   
   public function moon_setMapItem($X, $gId, $Y, $ItemId)
   {
       $mapItemConf = Common::getConfig('MidMoon_GroupGiftMap');
       if(!isset($mapItemConf[$gId][$Y][$ItemId]))
            return 'Not Exist Item in Config';
        $oEvent = Event::getById(Controller::$uId);
        if(isset($oEvent->EventList[EventType::MidMoon]))
        {          
            $pos = array(
                'Y' => $Y,
                'YConf' => $Y,                    
                'gId' => $gId,
                'ItemId' => $ItemId,
            );
           $oEvent->EventList[EventType::MidMoon]['Map'][$X] = $pos;
           
        }else return 'Not Exist Event';
       
       $oEvent->save();
       
       return "Magic"; 
   }
   
      public function moon_setLantern($X, $Healthy)
   {
       $X = (empty($X)) ? false : $X;
       $Healthy = (empty($Healthy)) ? false : $Healthy;
       $oEvent = Event::getById(Controller::$uId);
        if(isset($oEvent->EventList[EventType::MidMoon]))
        {       
            $oLantern = $oEvent->EventList[EventType::MidMoon]['Lantern'] ;  
           if($X)
           {
               
                $oLantern->X = $X;
                $oLantern->XL = $X - 2;               
           } 
                
           if($Healthy) 
                $oLantern->Healthy = $Healthy;     
           
        }else return 'Not Exist Event';
       $oEvent->EventList[EventType::MidMoon]['Lantern'] = $oLantern;
       $oEvent->save();
       
       return "Magic"; 
   }
   
   public function moon_checkMap()
   {
      $oEvent = Event::getById(Controller::$uId);
      if(isset($oEvent->EventList[EventType::MidMoon]))
        {          
           $Map = $oEvent->EventList[EventType::MidMoon]['Map'] ; 
           $mapItemConf = Common::getConfig('MidMoon_GroupGiftMap');
           $i = 0;
           foreach($Map as $step => $item)
           {
               if(!isset($mapItemConf[$item['gId']][$item['YConf']][$item['ItemId']]['ItemId']))
               {
                    Debug::log('step:'.$step.":".$mapItemConf[$item['gId']][$item['YConf']][$item['ItemId']]['ItemType']);                    
               }
               $i ++;                    
           }
           
        }else return 'Not Exist Event';
      
       return "Magic. {$i}";
   }
    /**
     event halloween: EventType = CollectPattern
     
     *
     */

    public function cheatEventItem($EventType, $ItemType, $ItemId, $Num= 1)
    {
        $UserId = Controller::$uId;

        $oStore = Store::getById($UserId);
        $oUser = User::getById($UserId)  ;
        
        $EventType = trim($EventType);
        $ItemType = trim($ItemType);
        $ItemId = trim($ItemId);
        if($EventType == 'CollectPattern')
        {
            $oEvent = Event::getById(Controller::$uId);
            $oEvent->colp_addItem($ItemType, $ItemId, $Num);
            $oEvent->save();
        }
        else
            $oStore->addEventItem($EventType, $ItemType, $ItemId, $Num);
        
        $oStore->save();
        $oUser->save();           
        return array('cheat ok  !!!');

    }

    /**
    * Event Halloween
    * 
    * @param mixed $EventType: CollectPattern
    * @param mixed $Num
    * @return mixed
    */
    public function cheatEventAllItem($EventType = 'CollectPattern', $Num= 1)
    {
        $UserId = Controller::$uId;
        if(empty($EventType))
            return 'Empty';
                        
        $EventType = trim($EventType);
        $oEvent = Event::getById(Controller::$uId);
        if($EventType == 'CollectPattern')
        {
            for($i=1; $i<=5; $i++){
                $oEvent->colp_addItem('ColPItem', $i, $Num);
            }      
            
            for($i=1; $i<=4; $i++){
                $oEvent->colp_addItem('ColPGGift', $i, $Num);
            }      
        }
        $oEvent->save();
        
        return array('cheat ok  !!!');

    }
    
    /**
    * Event: CollectPattern,Event_8_3_Flower
    * 
    */
    public function cheatEventDelete($EventType)
    {
        if(empty($EventType))
            return 'Empty';
        $EventType = trim($EventType);
        $oEvent = Event::getById(Controller::$uId);
        unset($oEvent->EventList[$EventType]);
        $oEvent->resetEvent();
        $oEvent->save();
        
        return 'Magic';
    }
    
    // cheat fish skill
    /**
    * reset all user's skill information
    */    
    public function skillReset()
    {
        $skillMan = SkillManager::init(Controller::$uId);
        $skillMan->save();
        return array('reset skill of '.Controller::$uId.' done');
    }
    
    /**
    * Example: 3_1, 3_3
    */
    public function cheatSlot($listSlot)
    {
        $skillMan = SkillManager::getById(Controller::$uId);
        if(empty($skillMan))
        {
            return array('Error' => Error::OBJECT_NULL);
        }
        
        $confMap = Common::getConfig('Skill_Map', $skillMan->currentMapId);
        if(empty($confMap)) return array('Error::OBJECT_NULL ' => Error::OBJECT_NULL);
        
        $arr = preg_split('/,/', $listSlot);
        for($i = 0; $i < count($arr); $i++)
        {
            $arr[$i] = trim($arr[$i]);
            $x = intval(substr($arr[$i], 0, strpos($arr[$i], '_')));
            $y = intval(substr($arr[$i], strpos($arr[$i], '_') + 1, strlen($arr[$i])));
            
            $posToPush['x'] = $x;
            $posToPush['y'] = $y;
            $posToPush['opened'] = true;
            array_push($skillMan->openedSlot, $posToPush);
        }
        
        $skillMan->save();
        return $posToPush;
    }
    
    /**
    * delete last slot in openedSlot
    */
    public function deleteLastSlot()
    {
        $skillMan = SkillManager::getById(Controller::$uId);
        if(empty($skillMan))
        {
            return array('Error::OBJECT_NULL ' => Error::OBJECT_NULL);
        }
        array_pop($skillMan->openedSlot);
        $skillMan->save();
        
        return $skillMan->openedSlot;
    }
    /**
    * Hoan thanh 1 map nao do, gui vao mapId
    * Example: 1
    */
    public function finishMap($mapId)
    {
        $skillMan = SkillManager::getById(Controller::$uId);
        if(empty($skillMan))
        {
            return array('Error::OBJECT_NULL ' => Error::OBJECT_NULL);
        }
        $confMap = Common::getConfig('Skill_Map');
        if($confMap[$mapId + 1] != null)
        {
            $skillMan->currentMapId = $mapId + 1;            
        }
        else
        {
             $skillMan->currentMapId = count($confMap) + 1;
        }
        $skillMan->save();
        
        return 'Current mapId = '.$skillMan->currentMapId;
    }
    
    /**
    * Add 1 skill nao do
    */
    public function addSkill($id, $level, $exp)
    {               
        $id = intval($id);
        $level = intval($level);
        $exp = intval($exp);
        
        if($id <= 0) return 'Error::PARAM '.Error::PARAM;
        
        $skillMan = SkillManager::getById(Controller::$uId);
        if(empty($skillMan))
        {
            return array('Error::OBJECT_NULL ' => Error::OBJECT_NULL);
        }
        if($skillMan->getSkillById($id))
        {
            if($level > 0)
            {
                $skillMan->skillList[$id]->Level = $level;
            }
            if($exp > 0)
            {
                $skillMan->skillList[$id]->Exp = $exp;                    
            }
        }
        else
        {
            $skill = FishSkill::init($id);
            if($level > 0)
            {
                $skill->Level = $level;
            }
            if($exp > 0)
            {
                $skill->Exp = $exp;                    
            }
            $skillMan->skillList[$id] = $skill ;
            //array_push(, $skill);
        }

        $skillMan->save();
        return $skillMan->skillList; 
    }
    
    
    /**
    * thay doi thong so he thong uy danh
    * $level
    * $point
    * $resetquest
    * $doneAllQuest
    */
    public function updateReputation($level = 0 ,$point = 0,$resetquest = false,$questId = null,$doneNum = 0,$doneAllQuest = false)
    {
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return 'object null';
        if($level)
            $oUser->ReputationLevel = intval($level) ;
        if($point)
            $oUser->ReputationPoint = intval($point) ; 
        if($resetquest)   
            $oUser->initReputationQuest();
        if(!empty($questId))
        {   if(isset($oUser->ReputationQuest[$questId]))
                $oUser->ReputationQuest[$questId]['Num'] = intval($doneNum) ;       
        }
        if($doneAllQuest)   
        {
            foreach($oUser->ReputationQuest as $index => $arr)
            {
                if(!is_array($arr))
                    continue;
                $oUser->ReputationQuest[$index]['Num']       = 100;
            }
        }
        $oUser->save();
    }
    
    public function addAccumulationPoint($point){        
        $oAccumulationPoint =  AccumulationPoint::getById(Controller::$uId);
        $oAccumulationPoint->setPoint($point);        
        $oAccumulationPoint->save();
        echo $oAccumulationPoint->getPoint();
       return 'Cheat OK';
    }
    
    public function cheatFirstAddXu(){
        $oUser = User::getById(Controller::$uId);
        $oUser->FirstAddXuGift = array();
        for($i=1; $i<=6; $i++){
           $oUser->FirstAddXuGift[$i] = $i;     
        }
        $oUser->save();
        echo "<pre>";
        print_r($oUser->FirstAddXuGift);
        return 'Cheat OK';
    }
    
    public function cheatResetFirstAddXu(){
        $oUser = User::getById(Controller::$uId);
        $oUser->FirstAddXuGift = array();        
        $oUser->save();
        echo "<pre>";
        print_r($oUser->FirstAddXuGift);
        return 'Cheat OK';
    }
    /**
    * setVersionData    
    * $version is data version need set, ex: 58
    */
    public function setDataVersion($version){
        $oUser = User::getById(Controller::$uId);
        $oUser->setDataVersion($version);
        $oUser->save();
        return 'Cheat OK';
    }
    /**
    * set number change option via old method
    * 
    * @param mixed $Id
    * @param mixed $Num
    */
    public function setNumberChangeOption($Id, $Num){
        $oHammerMan = HammerMan::getById(Controller::$uId);
        $oHammerMan->makeOption[$Id] = $Num;
        $oHammerMan->save();
        echo "<pre>";
        print_r($oHammerMan->makeOption);
        return 'Cheat OK';                            
    }
    /**
    * cheat Quartz 
    *  $QuartzType = QWhite, QGreen, QYellow, QPurple
    *  $QuartzId = 1,2,3,...
    */
    public function cheatQuartz($QuartzType, $QuartzId){
        $Option = array("OptionDamage"=>0,"Damage"=>0,"OptionDefence"=>0,"Defence"=>0,"OptionCritical"=>0,"Critical"=>0,"OptionVitality"=>0,"Vitality"=>0);
        $ItemConfig = Common::getConfig("SmashEgg_Quartz",$QuartzType,$QuartzId);
        $ItemConfig = array_merge($Option, $ItemConfig);
        
        $oUser = User::getById(Controller::$uId);                
        $oStore = Store::getById(Controller::$uId); 
                
        $Id = $oUser->getAutoId();                       
        $oQuartz = new Quartz($Id, $QuartzId, $QuartzType,$ItemConfig);        
        $oQuartz->Author = array('Id' => Controller::$uId, 'Name'=>$oUser->Name);
        $oStore->addQuartz($QuartzType, $Id, $oQuartz);        
        // call saved 
        $oStore->save();
        $oUser->save();
        return "Cheat OK";
    }
    
    /**
    * reset num Hammer is 0
    *  
    */
    public function resetHammer(){
       $oSmashEgg = SmashEgg::getById(Controller::$uId);
       $oSmashEgg->resetHammer();
       $oSmashEgg->save();
       return "Cheat OK";
    }
    
    
    public function resetQuartzInStore(){
       $oStore = Store::getById(Controller::$uId);
       $oStore->Quartz = array();
       $oStore->save();
       return "Cheat OK";
    }
    
    /**
    *  cheat all Quartz
    * 
    */
    public function cheatQuartzAll(){
        $oUser = User::getById(Controller::$uId);                
        $oStore = Store::getById(Controller::$uId); 
        $QuartzTypes = Common::getConfig('General', 'QuartzTypes');
        foreach($QuartzTypes as $QuartzType){  
            $MaxLevel = 5;
            if($QuartzType =="QGreen") {
                $MaxLevel = 10;
            } else if($QuartzType =="QYellow") {
                $MaxLevel = 15;
            } else if($QuartzType =="QPurple") {
                $MaxLevel = 20;
            }                      
            $Option = array("OptionDamage"=>0,"Damage"=>0,"OptionDefence"=>0,"Defence"=>0,"OptionCritical"=>0,"Critical"=>0,"OptionVitality"=>0,"Vitality"=>0);
            for($QuartzId =1; $QuartzId<=10; $QuartzId++) {
                for($i=1; $i<=$MaxLevel; $i++) {                                                                            
                    $ItemConfig = array_merge($Option, $ItemConfig);
                    $Id = $oUser->getAutoId();                       
                    $oQuartz = new Quartz($Id, $QuartzId, $QuartzType);                            
                    $oQuartz->Level = $i;
                    $oStore->addQuartz($QuartzType, $Id, $oQuartz);        
                    // call saved 
                    $oUser->save();
                }
            }
        }            
        $oStore->save();    
        return "Cheat OK";        
    }    
    /**    
    *  cheatQuartzAllNum
    */
    public function cheatQuartzAllNum($Num){
        $oUser = User::getById(Controller::$uId);                
        $oStore = Store::getById(Controller::$uId); 
        $QuartzTypes = Common::getConfig('General', 'QuartzTypes');
        foreach($QuartzTypes as $QuartzType){  
            $Option = array("OptionDamage"=>0,"Damage"=>0,"OptionDefence"=>0,"Defence"=>0,"OptionCritical"=>0,"Critical"=>0,"OptionVitality"=>0,"Vitality"=>0);
            for($QuartzId =1; $QuartzId<=10; $QuartzId++) {
                for($i=1; $i<=$Num; $i++) {                                                                            
                    $ItemConfig = array_merge($Option, $ItemConfig);
                    $Id = $oUser->getAutoId();                       
                    $oQuartz = new Quartz($Id, $QuartzId, $QuartzType);                                                
                    $oStore->addQuartz($QuartzType, $Id, $oQuartz);        
                    // call saved 
                    $oUser->save();
                }
            }
        }            
        $oStore->save();    
        return "Cheat OK";        
    }    

    /**
    * cheat Egg 
    *  $EggType = WhiteEgg, GreenEgg, YellowEgg, PurpleEgg    
    */
    public function cheatEgg($EggType){
        
        $now = $_SERVER['REQUEST_TIME'];
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        switch($EggType)
        {
            case 'WhiteEgg':
                //
                $UpdateEgg = array('Time'=>$now - 7*24*60*60, 'SmashNum'=>0);
                $oSmashEgg->updateEgg($EggType, $UpdateEgg);                                        
                break;            
            case 'GreenEgg':                    
            case 'YellowEgg':
            case 'PurpleEgg':
                $UpdateEgg = array('Time'=>$now - 7*24*60*60, 'SmashNum'=>0);
                $oSmashEgg->updateEgg($EggType, $UpdateEgg);
                break;
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }                       
        $oSmashEgg->save();
        return "Cheat OK";
        
    }
    
    public function cheatQuartzEquipment($QuartzType) {
        $oUser = User::getById(Controller::$uId);                
        $oStore = Store::getById(Controller::$uId);         
        $QuartzId = rand(1,5);                
        $Id = $oUser->getAutoId();
        $oQuartz = new Quartz($Id, $QuartzId, $QuartzType);
        $oStore->Equipment[$QuartzType][$Id] = $oQuartz;
        // call saved 
        $oStore->save();
        $oUser->save();
        
    }
    
    public function equip_addIdEquip($AutoId, $Element, $Type, $Rank, $Color)
    {
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        
        $AutoId = (empty($AutoId)) ? $oUser->getAutoId() : $AutoId;
        $oEquip = Common::randomEquipment($AutoId, $Rank, $Color, SourceEquipment::EVENT, $Type, "", $Element);
        $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);
        
        $oUser->save();
        $oStore->save();
        
        return 'Magic';
    }
    
    public function removeSoldierSlot($SoldierId) {
        $oSmashEgg = SmashEgg::getById(Controller::$uId); 
        $oSmashEgg->removeSoldierSlot($SoldierId);
        $oSmashEgg->save();
        return "DONE";
    }
    
    public function cheatResetMakeEquip(){
        $oHammerMan = HammerMan::getById(Controller::$uId);
        $oHammerMan->Percent = array();
        $oHammerMan->save();
        return "Cheat OK";
        
    }
    
    /**
    * cheatBullet
    * $BulletType = Bullet
    * $BulletId = 1,2,3
    */    
    public function cheatBullet($BulletType, $BulletId, $Num, $IsAdd=1){
        $oStore = Store::getById(Controller::$uId);
        if($IsAdd) {
            $oStore->addEventItem(EventType::Noel,trim($BulletType), $BulletId, intval($Num));    
        } else {
            $oStore->useEventItem(EventType::Noel,trim($BulletType), $BulletId, intval($Num));    
        }        
        $oStore->save();        
        return 'Set Bullet OK';        
    }
    
    /**
    * cheatNoelItem
    * $ItemType = NoelItem, ..
    * $ItemId = 1,2,3
    */    
    public function cheatNoelItem($ItemType, $ItemId, $Num){
        $oStore = Store::getById(Controller::$uId);
        $oStore->addEventItem(EventType::Noel,trim($ItemType), $ItemId, intval($Num));
        $oStore->save();        
        return 'Set Bullet OK';        
    }
    
    //resetBoardGame
    public function resetBoardGame(){
        DataProvider::delete(Controller::$uId, 'Noel');
        $oEvent = Event::getById(Controller::$uId);
        $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = -1;
        $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"] = -1;
        $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"] = 0;
        $oEvent->save();        
        return 'Reset Board Game OK';        
    }
    
    //gotoBoard
    public function gotoBoard($BoardId){
        $Now = $_SERVER['REQUEST_TIME']; 
        $oNoel = Noel::getById(Controller::$uId);
        $oNoel->setCurrentBoardId($BoardId);
        $oNoel->setIsPassBoard();        
        $oNoel->save();

        $oEvent = Event::getById(Controller::$uId);  
        $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = $Now - 5*60;
        $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"] = $Now - 2*60;
        $oEvent->save();
        
        return 'Reset Board Game OK';        
    }
    
    public function unsetStatus($dayIndex) {
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $oKeepLogin->unsetStatus($dayIndex);
        $oKeepLogin->save();
        return 'DONE';        
    }
    
    public function resetTreeNoel() {
        $oEvent = Event::getById(Controller::$uId);
        unset($oEvent->EventList[EventType::Noel]);
        $oEvent->save();
        return 'DONE';                
    }
    
    public function cheatTree($Level, $CareNum, $SpeedUpNum, $LastCareTime){
        $oEvent = Event::getById(Controller::$uId);
        $oEvent->EventList[EventType::Noel]["Level"] = $Level;
        $oEvent->EventList[EventType::Noel]["CareNum"] = $CareNum;
        $oEvent->EventList[EventType::Noel]["SpeedUpNum"] = $SpeedUpNum;
        $oEvent->EventList[EventType::Noel]["LastCareTime"] = $LastCareTime;
        
        $oEvent->save();
        return "Done";            
    }
    
    public function resetKeepLogin(){
        DataProvider::delete(Controller::$uId,'KeepLogin');
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $oKeepLogin->updateKeepLogin();
        $oKeepLogin->save();
        
        return "Done";
    }
    
    public function setNumPlay($Num){
        DataProvider::delete(Controller::$uId, 'Noel');
        $oEvent = Event::getById(Controller::$uId);
        $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"] = $Num;
        $oEvent->save();        
        return 'Set Num Play OK';        
    }
    
    public function addNumKey($Num) {
        $oVipBox = VipBox::getById(Controller::$uId);
        $oVipBox->addNumKey($Num);
        $oVipBox->save();
        return "DONE";
    }
    
    
    public function sys_showTime()
    {
        return date('Y-m-d H:i:s', time());
    }
    
}
?>
