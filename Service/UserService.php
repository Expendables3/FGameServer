<?php

/**
 * User Service
 * @author Toan Nobita
 * 2/9/2010
 */

class UserService extends Controller
{

	/**
	 * Init Game
	 * param UserId = uId cua ban be , neu load cua minh thi de rong
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function run($param)
	{
  		$UserId = $param['UserId'] ;
		$LakeId = $param['LakeId'] ;
        if(empty($LakeId)) $LakeId = 1 ;
		$oUser = User :: getById(Controller::$uId) ;
        
		if (!is_object($oUser))
		{
            /*$LogData = DataProvider::get(Controller::$uId,'Link');
            if(!empty($LogData))
            {
                //Zf_log::write_act_log_new(Controller::$uId,0,0,'SourceLink',0,0,0,0,$LogData['Web'],$LogData['Banner']);
            }*/

            return $this->create();    
		}
        
        if (Common::getSysConfig('userTest') && (empty($oUser->Name) || ($oUser->Name=='Unknow') || ($oUser->Name=='Undefine')))
        { 
            $conf_Name = Common::getConfig('DevName'); 
            $oUser->Name = $conf_Name[Controller::$uId]['displayname'];
            $oUser->save();
        }
		if($oUser->isUpdate())
		{
			$this->updateData($oUser->Id);
            $oUser->updateDataVersion();
			$oUser->save() ;
		}
        
		$runArr = array() ;
        $oFishTour = FishTournament::getById(Controller::$uId);
        if($oFishTour != null && $oFishTour->LastJoinTime != null && $oFishTour->LastJoinTime != 0)
        {
            $timeRemain = 1800 - ($_SERVER['REQUEST_TIME'] - $oFishTour->LastJoinTime);
            if($timeRemain > 0)
            {
                $runArr['Error'] = Error::USER_IN_TOURNAMENT;
                $runArr['timeRemain'] = $timeRemain;
                return $runArr;
            }
            else
            {
                $oFishTour->LastJoinTime = 0;
                $oFishTour->save();
            }
        }          
        
		if(!empty($UserId) && $UserId != Controller::$uId) // load friend game
		{
			if(!$oUser->isFriend($UserId))
			return  array('Error' => Error::NOT_FRIEND) ;

			$oFriend = User :: getById($UserId) ;
            
            if (Common::getSysConfig('userTest') && (empty($oFriend->Name) || ($oFriend->Name=='Unknow') || ($oFriend->Name=='Undefine')))
            {
                $conf_Name = Common::getConfig('DevName');
                
                $oFriend->Name = $conf_Name[$UserId]['displayname'];      
                $oFriend->save();
            }
			if(($oFriend->Id != NPC::NPC_SIGN.self::$uId) && $oFriend->isUpdate())
			{
				$this->updateData($oFriend->Id);
                $oFriend->updateDataVersion();
				$oFriend->save() ;
			}
            if (Common::getSysConfig('userTest'))
            {
                $oFriend->AvatarPic = '';
                $oFriend->save();    
            }
			$runArr['User'] = $oFriend ;
  		}
		else
		{   
			$UserId = Controller :: $uId; 
     
            // khoi tao va update event moi
            $oEvent = Event::getById($UserId);
            if(!is_object($oEvent))
            {
                $oEvent = new Event($UserId);
                $oEvent->save();
            }
            else
            {
                $newEvent = $oEvent->createEvent();                
                if(!empty($newEvent))
                {
                    foreach($newEvent as $index => $keyEvent)
                    {
                        $oEvent->resetEvent($keyEvent);
                    }
                    $oEvent->save();
                }
            }
            
            $oEvent->updateTimeFireFish(); //event Ban ca
            
            // thuc hien tang thuong cho user khi nap xu            
            $oUser->promoForAddXu();
            
            $oUser->updateFirstTimeOfDay();
            

             //Reset ve trang thai no password khi het time cracking password
            if($oUser->passwordState == PasswordState::IS_CRACKING)
            {
                //Debug::log('distance'.($oUser->timeStartCrackingPassword).'param'.Common::getConfig('Param', 'TimeCrackingPassword'));
                if(($_SERVER['REQUEST_TIME'] - $oUser->timeStartCrackingPassword) >= Common::getConfig('Param', 'Password', 'TimeCrackingPassword'))
                {
                    $oUser->passwordState = PasswordState::NO_PASSWORD;
                    $oUser->setMd5Password("");
                }
            }
            else
            //Mo khoa tai khoan khi het thoi gian block
            if($oUser->passwordState == PasswordState::IS_BLOCKED)
            {
                if(($_SERVER['REQUEST_TIME'] - $oUser->timeStartBlock) >= Common::getConfig('Param', 'Password', 'TimeBlockingPassword'))
                {
                    $oUser->passwordState = PasswordState::IS_LOCK;
                    $oUser->remainTimesInput  = Common::getConfig('Param', 'Password', 'MaxTimesInput');
                }
            }
            
            $oQuest = Quest::getById(Controller::$uId);
            $runArr['QuestList'] = $oQuest->getQuestActive();
            $runArr['QuestInfo']['ElementMainQuest'] = $oQuest->ElementMainQuest;
            
            $runArr['DailyQuest'] = $oQuest->newDailyTask($oUser->Level,true) ;
            
            
            // update Gem
            $oStore = Store::getById(Controller::$uId);
            $oStore->updateGem();
            $oStore->save();
            
            $equipmentInSoldier = StoreEquipment::getById(Controller::$uId);
            Store::logEquipment($oStore->Equipment, $equipmentInSoldier->SoldierList,$oStore->Quartz);
            
            $runArr['Store']['StoreList'] = $oStore; 
            $oUserPro = UserProfile::getById(Controller::$uId);
 
            // update avatar
            $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
            $LastDay = date('Ymd',$oUserPro->LastUpdateAvatar);
            if ($Today!=$LastDay)
            {
                unset($oUserPro->Avatar);
                $oUserPro->LastUpdateAvatar = $_SERVER['REQUEST_TIME'];
            } 
            
            $oUserPro->ActionInfo['ReceivedGiftTournament'] = true ;
            
            // update market key
            $currentAutoMarket = DataRunTime::get('Market_'.$UserId,true);
            if ($currentAutoMarket != 0)
            {
                DataRunTime::dec('Market_'.$UserId,intval($currentAutoMarket),true);                                         
            }
            
            
            // update magnet item
            $oMagnet = $oUser->SpecialItem['Magnet'];



            // thiet dat lai trang thai ko o FishWorld              
            $oWorld = FishWorld::getById(Controller::$uId); 
            if(is_object($oWorld))
            {
                $oWorld->IsInWorld = false ;
                $oWorld->save();
            }
            
           
            $oMagnet->update();
            $oUser->SpecialItem['Magnet'] = $oMagnet;  
            $oUser->save();
            
            
           // $runArr['ServerBoss'] = Common::callService('ServerBossService','getInfoSeverBoss');
            
            $oUserPro->save();
            $runArr['UserProfile'] = $oUserPro;
            
            // len ti vi      
            $runArr['WinBossUser'] = DataProvider::getMemcache()->get('WinBoss');
            $runArr['UserGetVipMax'] = DataProvider::getMemcache()->get('LuckyMinigame_UserGetVipMax');
            
            $runArr['LastTimeKillBoss'] = DataProvider::getMemcache()->get('LastTimeGetGift');
            
            $runArr['SB_TopUser'] = DataProvider::getMemcache()->get('SB_TopUser');
            $runArr['SB_TopUser_Boss'] = DataProvider::getMemcache()->get('SB_TopUser_Boss');  
            $runArr['SB_LastHit'] = DataProvider::getMemcache()->get('SB_LastHit'); 
            $runArr['SB_LastHit_Boss'] = DataProvider::getMemcache()->get('SB_LastHit_Boss');    
            //------------
           	
            $oSysMail = SystemMail::getById($UserId);
            $oSysMail->updateMailFormSystem();
            $oSysMail->save();
            $runArr['SystemMail'] = $oSysMail ;


			$runArr['User'] = $oUser ;
            $runArr['Username'] = $oUser->getUserName();

            
            $oMGame = MiniGame::getById(Controller::$uId);   
            if(is_object($oMGame))
            {
               $oMGame->addNewGame(); 
               $oMGame->save();
               $runArr['Minigame'] = $oMGame ;      
            }
            
             
            $oPwer = PowerTinhQuest::getById(Controller::$uId);        
            $runArr['PowerTinhQuest'] = $oPwer->getQuest();
            
            // update if market down suddenly
            if (Common::getSysConfig(isDownMarket))
                $this->getAllItemBack();
            
            // Occupying Rank
            $oProfile = OccupyingProfile::getByUid($UserId);
            if(empty($oProfile))
                $rank = OccupyFea::NOT_ATTEND;
            else
            {
                if($oProfile->CurrRank > OccupyFea::RANK_END_BOARD)
                    $rank =  OccupyFea::RANK_END_BOARD + 1;
                else
                {
                     $timeCoolDown = Common::getConfig('Param', 'Occupy', 'CoolDown');
                     $timeCoolDown = $timeCoolDown['SystemRefresh'];
                     $isGot = Common::checkCoolDown($timeCoolDown, 'LastSystemRefresh', $oProfile);
                     if($isGot)
                     {
                        $rank = OccupyingProfile::getRank(Controller::$uId);
                        Common::setCoolDown('LastSystemRefresh', $oProfile); 
                     }
                        
                     else $rank = false; 
                     if(!$rank)
                        $rank = $oProfile->CurrRank;                    
                }
                    
            }
            $runArr['OccupyingRank'] = $rank;
            
            // snapshot A1
            if(date('Ymd',$oUser->LastSnapshotA1) < date('Ymd', $_SERVER['REQUEST_TIME']))
            {
                //$res = Common::snapshot_a1();
                if($res)
                {
                    $oUser->LastSnapshotA1 =  $_SERVER['REQUEST_TIME'];
                    $oUser->save();
                }
            }     
		}   	

		//---------------------
        
        
        // return ve Event
        $oEvent = Event::getById($UserId);
        if(is_object($oEvent))
        { try
            {             
                if(is_array($oEvent->getEvent('Event_8_3_Flower')))
                {
                    $Top = DataProvider::getMemBase()->get('MaxOpenBox_Event_8_3');
                    $oEvent->EventList['Event_8_3_Flower']['TopUser']= $Top['uId'] ;
                }
            }
            catch(Exception $e)
            {
            }           
            $runArr['EventList'] = $oEvent->EventList;
            
        }      

		$runArr['Lake1'] = Lake :: getById($UserId,$LakeId) ;

        $oDeco =  Decoration :: getById($UserId, $LakeId);
	 	$runArr['Item'] = $oDeco->ItemList ;
        $runArr['SpecialItem'] = $oDeco->SpecialItem ;
	 	$infoSeal = DataProvider::getMemcache()->get('SealExchangeHerbMedal');
        if (!$infoSeal)
            $infoSeal = array();
         $runArr['Notification']['Seal'] = $infoSeal; 
		$runArr['SystemTime'] =  $_SERVER['REQUEST_TIME'] ;

		$runArr['Error'] = Error::SUCCESS ;
        //Debug::log('sadasd'.$oUser->passwordState) ;
		// log load finish
		$oSelf = $oUser ;
		Zf_log::write_act_log(Controller::$uId,$UserId,10,'run',0,0,$oUser->Money, $oUser->ZMoney, $oUser->Level, $oUser->Exp,$oUser->Diamond,'game',0,0,$oUser->Exp,$oUser->Level);
        
		return $runArr ;
	}
    
	/**
	 * Refresh list ban be
	 * @param none
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function refreshFriend($param)
	{
		$refresh = $param['Refresh'] ; 
		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return  array('Error' => Error::NO_REGIS) ;
		}
		if($refresh && !Common::getSysConfig('userTest')) $oUser->updateInfo();
		$runArr = array();
		$runArr['FriendList'] = $oUser->getFriends($refresh);
		$oUser->save();
		$runArr['Error'] = Error::SUCCESS ;
		//log
        
		$friendsNum = count($runArr['FriendList']);
		//Zf_log::write_act_log(Controller::$uId,0,10,'refreshFriend',0,0,$friendsNum);

		return $runArr ;
	}

	/**
	 * Tao moi user
	 * @param $avatarType Loai Avatar cua nguoi choi
	 * author : Toan Nobita
	 * 9/9/2010
	 */

	public function create()
	{
		$avatarType = $param['AvatarType'];
        if(!isset($avatarType)||$avatarType < 0)
        {
            $avatarType = 1  ;
        }
        
        if (!Controller :: $uId)
        {
            return array('Error' => Error::LOGIN) ;
        }
        
        $oUser = User :: getById(Controller::$uId );
        if (is_object($oUser))
        {
            return  array('Error' => Error::CREADTED) ;
        }
        $createdTime = $_SERVER['REQUEST_TIME'];
        $oUser = new User(Controller :: $uId, $avatarType, $createdTime) ;
        if (!$oUser->updateInfo())
        {
            return array('Error' => Error::NO_FOUND) ;
        } 
        
        $oUser->updateDataVersion() ;
        
        $oUser->save();   

        $oUserPro = new UserProfile(Controller::$uId) ;
        $oUserPro->save();    
        
        $runArr['UserProfile'] = $oUserPro;

        $arrStore = Store::init(Controller :: $uId);

        $oLake1  = Lake::init(Controller :: $uId,1);
	    $oDeco = Decoration::init(Controller :: $uId,1);
        
        // khoi tao quest 
        $oQuest = new Quest(Controller::$uId,$oUser->Level);
        $oQuest->save();  
        // update lan dau vao game
        $oUser->updateFirstTimeOfDay();  
		$oUser->save();
        
        $oEvent = new Event(Controller::$uId);
        $oEvent->save();
        $runArr['EventList'] = $oEvent->EventList;         
               
        $runArr['User'] = $oUser ;
        $runArr['Lake1'] = $oLake1 ;
        $runArr['Item'] = $oDeco->ItemList ;
        $runArr['Store']['StoreList'] = $arrStore ;

        $runArr['SystemTime'] =  $_SERVER['REQUEST_TIME'] ;
        $runArr['QuestList'] = $oQuest->getQuestActive();
        $runArr['DailyQuest'] = $oQuest->DailyInfo ;
        
        // load Minigame
        $oMGame = MiniGame::getById(Controller::$uId);
        $runArr['MiniGame'] = $oMGame;

        $oPwer = PowerTinhQuest::getById(Controller::$uId);        
        $runArr['PowerTinhQuest'] = $oPwer->getQuest();
        
        $runArr['Error'] = Error::SUCCESS ;
        
        StaticCache::forceAddAll();
        // create NPC
        $this->createNPC(self::$uId);
                
        // log
        Zf_log::write_act_log(Controller::$uId,0,10,'CreateNewUser',0,0); 

		return $runArr ;
	}

	public function levelUp()
	{
		
		$oUser = User :: getById(Controller::$uId);

		if (!is_object($oUser))
		{
			return  array('Error' => Error::NO_REGIS) ;
		}
		// tinh chenh lech
		$moneyDiff = $oUser->Money ;
		$zMoneyDiff = $oUser->ZMoney;
		$oldLevel = $oUser->Level ;
		
		$level = $oUser->levelUp();
	
		if($level == false)
		return  array('Error' => Error::NOT_ENOUGH_EXP ) ;
		
        $arr_Gift = Common::getConfig('LevelUpUser',$level);
        
		$runArr['Level'] = $level ;
		$runArr['Exp']   = $oUser->Exp ;
		$runArr['Error'] = Error::SUCCESS ;

		$oUser->saveBonus($arr_Gift);
        $oUser->save();
        	
        // khoi tao quest moi
        $oQuest = Quest::getById(Controller::$uId);
        if(!is_object($oQuest))
        {
            $oQuest = new Quest(Controller::$uId,$oUser->Level);
            $oQuest->save();
        }
        else
        {
            $new = $oQuest->checkNewQuest($oUser->Level,false,false);
        // check new series quest
            $oQuest->save();
        }
        
        // khoi tao va update event moi
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oEvent))
        {
            $oEvent = new Event(Controller::$uId);
        }
        else
        {
            $newEvent = $oEvent->createEvent();
            if(!empty($newEvent))
            {
                foreach($newEvent as $index => $keyEvent)
                {
                    $oEvent->resetEvent($keyEvent);
                }
            }
        }
        if($oUser->Level == 7)
            $oEvent->updateGiftAtLevelUp();
        
        // he thong danh tieng
        if (Event::checkEventCondition('ReputationQuest'))
        {
            if(empty($oUser->ReputationQuest))
                $oUser->initReputationQuest();
        }
        
        $oEvent->save();
        
        $runArr['EventList'] = $oEvent->EventList;
        //------
        
        $oUserPro = UserProfile::getById(Controller::$uId);
        // reset lai tui qua tan thu
        $oUserPro->resetNewUserGiftBag();
        $oUserPro->save();
        
        //tinh chenh lech
        $moneyDiff = $oUser->Money - $moneyDiff;
		$zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
		        
		Zf_log::write_act_log(Controller::$uId,0,10,'levelUp',$moneyDiff,$zMoneyDiff,$runArr['Level'],$oldLevel,0,0,0,'game', 0, 0, $oUser->Exp, $oUser->Level);

        //if($new) $runArr['QuestList'] = $oQuest->getQuestActive(); 
 		return $runArr ;
 	}
 	
 	
 	public function chooseAvatar($param){
 		$newType = $param['AvatarType'];
 		$oUser = User :: getById(Controller:: $uId) ;

 		if (!is_object($oUser))
 		{
 		    return  array('Error' => Error::NO_REGIS) ;
 		}
 		
 		if (!$oUser->updateAvatar($newType))
 			return array('Error' => Error::PARAM);
 		$oUser->save();
 		return array('Error'=>Error::SUCCESS);
 	}
 	
    
    
	private function updateData($uId = null)
	{  
    // update thuoc tinh MaxEnergyUse  va coop zing farm
      $oUser = User::getById($uId);
      $oUserPro = UserProfile::getById($uId);
      // update seriquest cho client
      // khoi tao quest moi
      $oQuest = Quest::getById($uId);
      if(!is_object($oQuest))
      {
          $oQuest = new Quest($uId,$oUser->Level);
      }
      else
      {
          $new = $oQuest->checkNewQuest($oUser->Level,false,false);
          // check new series quest
      }
      $oQuest->save();

        if ($oUser->getDataVersion() <=13)
        {
             //update dailybonus
             if ($oUser->DataVersion >=8)
             {
                $oUserPro->updateNumonline(1);
                $oUserPro->day_updateDayGift();
                $oUserPro->save();
             } 
        }

        if ($oUser->getDataVersion() <=14)      
        {
             //update all buff
             $numLake = $oUser->LakeNumb;
             for($nl = 1; $nl <= $numLake; $nl++)
             {
                $oLake = Lake::getById($uId, $nl);
                $oLake->updateAllBuff();     
                
                 $oDeco = Decoration::getById($uId,$nl) ;
                 foreach($oDeco->SpecialItem['Sparta'] as $id => $oSparta)
                 if($oSparta->isExpried) 
                 {
                    $oLake->buffToLake($oSparta->Option,true);  
                 }
                 
                 foreach($oDeco->SpecialItem['Batman'] as $id => $oBatman)
                 if($oBatman->isExpried) 
                 {
                   $oLake->buffToLake($oBatman->Option,true);  
                 }
                $oLake->save(); 
             }
             
             // update max energy use
             $oUserPro->MaxEnergyUse[2] = 100;
             $oUserPro->save();
                             
        }

        if ($oUser->getDataVersion() <=15)      
        {
             // update exp and gold for batman
             $numLake = $oUser->LakeNumb;
             for($nl = 1; $nl <= $numLake; $nl++)
             {
                 //$oLake = Lake::getById($uId, $nl);     
                 $oDeco = Decoration::getById($uId,$nl) ;
                 foreach($oDeco->SpecialItem['Batman'] as $id => $oBatman)
                 {
                    if($oBatman->isExpried) 
                    {
                      $oBatman->Exp = 7777;
                      $oBatman->Money = 200000;
                    }
                 }
                $oDeco->save(); 
             }
        }

        if ($oUser->getDataVersion() <=17)
        {
           
            $conf = Common::getConfig('UserLevel') ;
            $ExpNext = $conf[$oUser->Level]['NextExp']; 
            
            if($oUser->Level == 50 && $oUser->Exp >= $ExpNext )
            {
               $this->levelUp();
               $oUser->Exp = 0 ;
               $oUser->save();
            }
            else if ($oUser->Level >=51)
            {
                $oUser->Level = 51;
                $oUser->Exp = 0 ;
                $oUser->save();
            }
            
        }


		/*if ($oUser->getDataVersion() <=18)
        {
            $conf_black = Common::getConfig('BlacklistUser');
            if ($oUser->Level==50 && !in_array($oUser->Id, $conf_black)){
                $oUser->Level = 51;
                $oUser->save();
            }
        }*/
        if($oUser->getDataVersion() <=19) 
        {
          $oUserPro->resetDailyBonus();
          $oUserPro->save();
        }
        
        if($oUser->getDataVersion() <=20) 
        {
          // update lai Icon truoc kia
          
          // update lai Money Pocket cua ca
          // update exp and gold for batman
           $confFish = Common::getConfig('Fish');
           $numLake = $oUser->LakeNumb;
           for($nl = 1; $nl <= $numLake; $nl++)
           {
               $oLake = Lake::getById($uId, $nl);  
               foreach($oLake->FishList as $id => $oFish)
               {
                    if(!is_object($oFish))
                      continue ;
                    $oFish->ThiefList = array();
                    $oFish->PocketStartTime =$_SERVER['REQUEST_TIME'];
                    $oFish->TotalBalloon = $confFish[$oFish->FishTypeId]['TotalBalloon'];
               }
               $oLake->save() ;      
           }
        }

        if($oUser->getDataVersion() <=22) 
        {
           // update lai so lan mua ca super 
           $oUserPro->createActionOfDay();   
           $oUserPro->save();            
        }
        
        if ($oUser->getDataVersion() <=23)   
        {
            
        }
        
        if($oUser->getDataVersion() <=24)
        {
            // chuyen doi do trang tri trong kho thanh mot doi tuong
           $oStore = Store::getById($uId);
           if(is_array($oStore->Items))
           {
               foreach($oStore->Items as $ItemType => $object)
               {
                  if(in_array($ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
                  {
                      foreach($object as $ItemId => $Num)
                      {
                         for($i = 1;$i <= $Num; $i++)
                         {
                             $oItem = new Item($oUser->getAutoId(),$ItemType,$ItemId);
                             $oStore->addOther($ItemType,$oItem->Id,$oItem);   
                         }
                      }
                      unset($oStore->Items[$ItemType]);
                      
                  }
              
               }
           }
           $oUser->save();
           $oStore->save(); 
           
           //them thuoc tinh ngay het han cho do trang tri ngoai be
           $numLake = $oUser->LakeNumb;
            for($nl = 1; $nl <= $numLake; $nl++)
            { 
                 $oDeco = Decoration::getById($uId,$nl) ;
                 foreach($oDeco->ItemList as $id => $oItem)
                 {
                     if(in_array($oItem->ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
                      {
                          $conf = Common::getConfig($oItem->ItemType,$oItem->ItemId);
                          if(!is_array($conf))
                            unset($oDeco->ItemList[$id]); 
                          $oItem->ExpiredTime =  $_SERVER['REQUEST_TIME'] + $conf['TimeUse'];                         
                      }
                 }
                 $oDeco->save(); 
            }
            // update lai seriquest so nhap cho user
            $oQuest = Quest::getById($uId);
            
            if(isset($oQuest->QuestInfo[2]))
                unset($oQuest->QuestInfo[2]);
                  
            $oQuest->rollbackSeriesquest($oUser->Level,1,6);
            $oQuest->save();
            
            // reset lai tui qua tan thu
            $oUserPro->resetNewUserGiftBag();
            $oUserPro->save();

            
            
            // use Firework
            
            $oStore = Store::getById($uId);
            foreach($oStore->AllOther['Firework'] as $id => $oFirework)
            {
                $param = array();
                $param['LakeId'] = 1;
                $param['ItemList'] = array();
                $param['ItemList'][0]['Id'] = $id;
                $param['ItemList'][0]['ItemType'] = 'Firework';
                
                $re = Common::callService('StoreService','useItem',$param);
            }
            $oStore->save();
        }
        
        if($oUser->getDataVersion() <=25)
        {
            // chuyen doi do trang tri trong kho thanh mot doi tuong
            $oStore = Store::getById($uId);
            foreach($oStore->Fish as $Id => $ojectFish)
            {
                if(!is_object($ojectFish))
                    continue ;
                if(!isset($ojectFish->TotalBalloon))
                {
                    if($ojectFish->FishType == FishType::NORMAL_FISH)
                    {
                        $oFish = new Fish($ojectFish->Id,$ojectFish->FishTypeId,$ojectFish->Sex,$ojectFish->ColorLevel);      
                    }
                    else if($ojectFish->FishType == FishType::SPECIAL_FISH)
                    {
                        $oFish = new SpecialFish($ojectFish->Id,$ojectFish->FishTypeId,$ojectFish->Sex,$ojectFish->RateOption,$ojectFish->ColorLevel);
                    }
                    else if($ojectFish->FishType == FishType::RARE_FISH)
                    {
                        $oFish = new RareFish($ojectFish->Id,$ojectFish->FishTypeId,$ojectFish->Sex,$ojectFish->RateOption,$ojectFish->ColorLevel);
                    }
                    
                    unset($oStore->Fish[$Id]);
                    $oStore->addFish($oFish->Id,$oFish);
                }
            }
            $oStore->save();
             
        }
        
        if($oUser->getDataVersion() <=26)  
        {   
            $conf_freeUse = Common::getConfig('Param','Magnet','FreeUse');
            $oUser->SpecialItem[Type::Magnet] = new Magnet($oUser->getAutoId(),1,$conf_freeUse);
            for($i=1;$i <= $oUser->LakeNumb; $i++)
            {
                $oDeco = Decoration::getById($uId,$i);
                if(!is_object($oDeco))
                    continue ;
                $itemBG = new Item($oUser->getAutoId(),Type::BackGround,1);
                $oDeco->addItem($itemBG->Id,$itemBG); 
                $oDeco->save();
            }
           
            $oUser->save();     
        }
        
        if($oUser->getDataVersion() <=30)  
        {
            if($oUser->Level > 75)
            {
                // store
                $oStore = Store::getById($uId);
                foreach($oStore->Fish as $key => $oFish )
                {
                    if(!is_object($oFish))
                        continue ;
                    if($oFish->FishTypeId >79)
                        $oFish->FishTypeId = 79 ;
                }
                $oStore->save();
                
                // lake
                $numLake = $oUser->LakeNumb;
                for($nl = 1; $nl <= $numLake; $nl++)
                {
                    $oLake = Lake::getById($uId, $nl);
                    foreach($oLake->FishList as $key => $oFish )
                    {
                        if(!is_object($oFish))
                            continue ;
                        if($oFish->FishTypeId >79)
                            $oFish->FishTypeId = 79 ;
                    }
                    $oLake->save();
                }
            }
            
        }
        
        if($oUser->getDataVersion() <=32)  
        {
            $listElement = Lake::getListElement($uId);
            if (count($listElement) >=3 )
            {
                // update equipment
                $oStore = Store::getById($uId);
                
                for($i=1; $i<=$oUser->LakeNumb; $i++)
                {
                    $oLake = Lake::getById($uId,$i);
                    foreach($oLake->FishList as $id => $oFish)
                    {
                        if ($oFish->FishType == FishType::SOLDIER)
                        {
                            // store back equipment 
                            foreach($oFish->Equipment as $indexType => $listType)
                            {
                                foreach($listType as $idEquip => $oEquip)
                                {
                                    $oFish->deleteEquipment($oEquip->Type,$oEquip->Id);
                                    $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);
                                }
                            }   
                         
                            // update fish to store
                            $oStore->Fish[$id] = $oFish;
                            unset($oLake->FishList[$id]);
                        }
                    }
                    $oLake->save();    
                }
                $oStore->save();

            }         
        }
        
        // update new attack time in lake
        if($oUser->getDataVersion() <=34)  
        {
            $oUserPro->Attack = array(); 
            $oUserPro->save();     
        }
        
        // update item used or not
        if ($oUser->getDataVersion() <= 35)
        {
            // update in soldier
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {
                        foreach($oFish->Equipment as $oType => $listEq)
                        {
                            foreach($listEq as $idf => $oEq)
                            {
                                $oEq->IsUsed = true;
                                $oLake->FishList[$oFish->Id]->Equipment[$oType][$idf] = $oEq;
                            }
                        }
                        
                    }
                }
                $oLake->save();    
            }
            
            // update in store
            $oStore = Store::getById($uId);
            foreach($oStore->Equipment as $oType =>$listEquip)
            {
                foreach($listEquip as $id => $oEquip)
                {
                    $conf_equip = Common::getConfig('Wars_'.$oEquip->Type, $oEquip->Rank, $oEquip->Color);
                    if ($oEquip->Durability < $conf_equip['Durability'])
                        $oEquip->IsUsed = true;
                    else $oEquip->IsUsed = false;
                    $oStore->Equipment[$oEquip->Type][$id] = $oEquip;
                }
            }
            
            $oStore->save();
            
        }
        
        if ($oUser->getDataVersion() <= 36)
        {
                //update all buff
            $numLake = $oUser->LakeNumb;
            for($nl = 1; $nl <= $numLake; $nl++)
            {
                $oLake = Lake::getById($uId, $nl);
                $oLake->Option = array (
                    OptionFish::MONEY => 0 ,
                    OptionFish::EXP    => 0 ,
                    OptionFish::TIME => 0 ,
                    );
                $oLake->updateAllBuff();  
                   
                $oDeco = Decoration::getById($uId,$nl) ;

                if (!empty($oDeco->SpecialItem))
                {
                     foreach ($oDeco->SpecialItem as $Type => $Arr_object) 
                     {
                         foreach ($Arr_object as $id => $oSparta) 
                         {
                           if(!is_object($oSparta))
                           {
                              continue ;
                           }
                           if($oSparta->isExpried)
                           {
                               $oLake->buffToLake($oSparta->Option,true);
                           }
                         }
                     }
                } 
                 
                $oLake->save(); 
            }
            //----------------------
        }
        
        // xoa cac Arrow cua event truoc 
        // reset lai event 8-3
        if ($oUser->getDataVersion() <= 37)
        {
            $oStore = Store::getById($uId);
            $oStore->Items['Arrow'] = array();
            $oStore->save();
            $oEvent = Event::getById($uId);
            if(is_array($oEvent->EventList['Event_8_3_Flower']))
            {
                $oEvent->EventList['Event_8_3_Flower']['Level']         = 1 ;
                $oEvent->EventList['Event_8_3_Flower']['CareNum']       = 0 ;
                $oEvent->EventList['Event_8_3_Flower']['SpeedUpNum']    = 0 ;
                $oEvent->EventList['Event_8_3_Flower']['LastCareTime']  = 0 ;
                $oEvent->save();
            }
            
        }
        
        // update equipment index
        if ($oUser->getDataVersion() <= 38)
        {
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {
                        $oLake->FishList[$id]->updateBonusEquipment();
                    }
                }
                $oLake->save();    
            }   
        }
        
        
        // separate equipment key
        if ($oUser->getDataVersion() <= 39)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {
                        foreach($oFish->Equipment as $indexType => $listType)
                        {
                            foreach($listType as $idEquip => $oEquip)
                            {     
                                $oStoreEquip->addEquipment($id,$oFish->Element,$oEquip);
                                Zf_log::write_equipment_log(Controller::$uId, 0, 20,'equipmentKey', 0, 0, $oEquip);
                            }
                        }
                        
                        //update JadeSeal
                        if(!empty($oStoreEquip->SoldierList[$id]['Equipment'][Type::JadeSeal]))
                        {
                            $oStoreEquip->disableAllLevelJadeSeal($id);
                            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
                            $totalEquip = 0;
                            foreach ($oStoreEquip->SoldierList[$id]['Equipment'] as $type => $arrEquip)
                            {
                                if(($type == Type::Mask) || ($type == Type::JadeSeal))
                                    continue;
                                $totalEquip += count($arrEquip);                
                            }
                            if($totalEquip == $fullsetConf)            
                                $oStoreEquip->updateBonusFromJadeSeal($id);
                        }
                        // end update JadeSeal
                    }
                }
                $oLake->save();
            }   
            $oStoreEquip->save();
            
            // update Store JadeSeal
            $oStore = Store::getById($uId);
            if(!empty($oStore->Equipment[Type::JadeSeal]))
            {
                foreach($oStore->Equipment[Type::JadeSeal] as $id => $oSeal)
                    $oSeal->disableAllLevelJadeSeal();
                $oStore->save();    
            }
            // end update Store JadeSeal  
        }
        
        // clear Old Item , that is expired
        if ($oUser->getDataVersion() <= 40)
        {
            $oStore = Store::getById($uId);
            unset($oStore->Items['Icon']);
            unset($oStore->Items['Key']);
            unset($oStore->Items['IconND']);
            unset($oStore->Items['Arrow']);
            unset($oStore->Items['MazeKey']);
            unset($oStore->Items['IconChristmas']);
            unset($oStore->Items['Sock']);
            unset($oStore->Items['SockExchange']);
            unset($oStore->Items['Event_8_3_Flower']);
            unset($oStore->Items['VipMedal']);
            unset($oStore->Items['Herb']);
            unset($oStore->Items['HerbPotion']);
            unset($oStore->Items['HerbMedal']);
            
            unset($oStore->AllOther['PearFlower']);
            unset($oStore->EventItem['BirthDay']['BirthDayItem']);
            
            $num = $oStore->Items[Type::Ticket][1];
            Zf_log::write_act_log($uId,0,20,'XoNum',0,0,$num);
            if($num <= 100)
                $oStore->Items[Type::Ticket][1] = 0 ;
            else
                $oStore->Items[Type::Ticket][1] = $num - 100 ;
			$oStore->save();
            
            $oMiniGame = MiniGame::getById($uId);
            if(is_object($oMiniGame))
            {
                $oMiniGame->addNewGame('LuckyMachine') ;
                $oMiniGame->save();
            }
            
        }
        
        if($oUser->getDataVersion() <= 41)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {
                        if(!empty($oStoreEquip->SoldierList[$id]['Equipment'][Type::JadeSeal]))
                        {
                            $disableJadeSeal = false;
                             foreach($oStoreEquip->SoldierList[$id]['Equipment'] as $eType => $oType)
                             {
                                  if($eType == SoldierEquipment::Mask) continue;
                                  foreach ($oType as $oEquip)
                                  {
                                      if($oEquip->isExpired() && !$disableJadeSeal)
                                      {      
                                            $oStoreEquip->disableAllLevelJadeSeal($id);
                                            $disableJadeSeal = true;
                                    }
                                  }
                             }
                        }
                    }
                }
                $oLake->save(); 
            }

            $oStoreEquip->save(); 
            
        }
        
        
        
        // Update all index equipment
        if ($oUser->getDataVersion() <= 42)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {
                        //update JadeSeal
                        if(!empty($oStoreEquip->SoldierList[$id]['Equipment'][Type::JadeSeal]))
                        {
                            $oStoreEquip->disableAllLevelJadeSeal($id);
                            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
                            $totalEquip = 0;
                            foreach ($oStoreEquip->SoldierList[$id]['Equipment'] as $type => $arrEquip)
                            {
                                if(($type == Type::Mask) || ($type == Type::JadeSeal))
                                    continue;
                                $totalEquip += count($arrEquip);                
                            }
                            if($totalEquip == $fullsetConf)            
                                $oStoreEquip->updateBonusFromJadeSeal($id);
                        }
                        // end update JadeSeal
                    }
                }
                $oLake->save();
            }   
            $oStoreEquip->save();          
        }
        
        if ($oUser->getDataVersion() <= 43)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {                    
                        //update JadeSeal
                        if(!empty($oStoreEquip->SoldierList[$id]['Equipment'][Type::JadeSeal]))
                        {
                            $oStoreEquip->disableAllLevelJadeSeal($id);
                            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
                            $totalEquip = 0;
                            foreach ($oStoreEquip->SoldierList[$id]['Equipment'] as $type => $arrEquip)
                            {
                                if(($type == Type::Mask) || ($type == Type::JadeSeal))
                                    continue;
                                $totalEquip += count($arrEquip);                
                            }
                            if($totalEquip == $fullsetConf)            
                                $oStoreEquip->updateBonusFromJadeSeal($id);
                        }
                        // end update JadeSeal
                    }
                }
                $oLake->save();
            }   
            $oStoreEquip->save();
            
            // update Store JadeSeal
            $oStore = Store::getById($uId);
            if(!empty($oStore->Equipment[Type::JadeSeal]))
            {
                foreach($oStore->Equipment[Type::JadeSeal] as $id => $oSeal)
                    $oSeal->disableAllLevelJadeSeal();
                $oStore->save();    
            }
            // end update Store JadeSeal  
        }
        
        //Update chi so ngu mach moi
        if ($oUser->getDataVersion() <= 44)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($h=1; $h<=$oUser->LakeNumb; $h++)
            {
                $oLake = Lake::getById($uId,$h);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {                             
                        if(!empty($oStoreEquip->listMeridian[$id]))
                        {
                             //Cap nhat chi so ca linh              
                             $oStoreEquip->listMeridian[$id]['Damage'] = 0;                                          
                             $oStoreEquip->listMeridian[$id]['Defence'] = 0;                                          
                             $oStoreEquip->listMeridian[$id]['Vitality'] = 0;                                          
                             $oStoreEquip->listMeridian[$id]['Critical'] = 0;                                          
                             for($i = 1; $i <= $oStoreEquip->listMeridian[$id]['meridianRank']; $i++)
                             {
                                $configActive = Common::getConfig('ActiveMeridian', $oFish->Element, $i);
                                $maxPosition = 10;
                                if($i == $oStoreEquip->listMeridian[$id]['meridianRank'])
                                {
                                    $maxPosition = $oStoreEquip->listMeridian[$id]['meridianPosition'];
                                }
                                for($j = 1; $j <= $maxPosition; $j++)
                                {                                           
                                    foreach($configActive[$j] as $index=>$value)
                                    { 
                                        $oStoreEquip->listMeridian[$id][$index] += $value;  
                                    }
                                }
                             }
                        }                           
                    }
                }                  
            }   
            $oStoreEquip->save();
        }
        
        if ($oUser->getDataVersion() <= 45)
        {
            $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {                    
                        //update JadeSeal
                        if(!empty($oStoreEquip->SoldierList[$id]['Equipment'][Type::JadeSeal]))
                        {
                            $oStoreEquip->disableAllLevelJadeSeal($id);
                            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
                            $totalEquip = 0;
                            foreach ($oStoreEquip->SoldierList[$id]['Equipment'] as $type => $arrEquip)
                            {
                                if(($type == Type::Mask) || ($type == Type::JadeSeal))
                                    continue;
                                $totalEquip += count($arrEquip);                
                            }
                            if($totalEquip == $fullsetConf)            
                                $oStoreEquip->updateBonusFromJadeSeal($id);
                        }
                        // end update JadeSeal
                    }
                }
                $oLake->save();
            }   
            $oStoreEquip->save();                    
        }
        //update lai thong so cua do cap 3        
        if ($oUser->getDataVersion() <= 46)        
        {
            UpdateDataVersion::update_46($uId);
        }
        //delete old data of 8-3 event
        if ($oUser->getDataVersion() <= 47)        
        {
            UpdateDataVersion::update_47($uId);
        }    
        //delete old data of 8-3 event
        if ($oUser->getDataVersion() <= 48)        
        {
            UpdateDataVersion::update_48($uId);
        }	
        // reset OccupyProfile
        if ($oUser->getDataVersion() <= 49)        
        {
            $oProfile = OccupyingProfile::getByUid($uId);
            if(is_object($oProfile))
            {
                DataProvider::delete($uId, 'OccupyingProfile');
                $oStore = Store::getById($uId);
                $oStore->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_DEFAULT] = 0;
                $oStore->save();
            }
            
            // add NPC
            $this->createNPC($uId);
            $oQuest = Quest::getById($uId);
            $oQuest->QuestInfo = array();
            $oQuest->MainQuestSeriesId = 0;
            $oQuest->ElementMainQuest = 1;
                
            $oQuest->checkNewQuest($oUser->Level, false, false, false);
            $oQuest->save();                
        }
        
        if($oUser->getDataVersion() <= 50)
        {
            UpdateDataVersion::udpate_50($uId);
        }
        // dong bo xu cho viec snapshot xu
        if($oUser->getDataVersion() <= 51)
        {
            //UpdateDataVersion::udpate_51($uId);
        }
        // dong bo xu cho viec snapshot xu
        if($oUser->getDataVersion() <= 55)
        {
            UpdateDataVersion::udpate_55($uId);
        }
        
        if($oUser->getDataVersion() < 57)
        {
            UpdateDataVersion::update_resetEvent($uId);
        }
        
        // fix bug the same id
        if($oUser->getDataVersion() < 58)
        {
            UpdateDataVersion::update_57($uId);
        }
        // convert number change option from mem to property of equipment 
        if($oUser->getDataVersion() < 59) {
            UpdateDataVersion::update_58($uId);
        }
        if($oUser->getDataVersion() < 60) {
            UpdateDataVersion::update_59($uId);
        }
        if($oUser->getDataVersion() < 61) {
            UpdateDataVersion::update_resetEvent($uId);
            UpdateDataVersion::update_60($uId);
        }

        if($oUser->getDataVersion() < 62) {            
            UpdateDataVersion::update_61($uId);
        }
        
        if($oUser->getDataVersion() < 63) {            
            UpdateDataVersion::update_62($uId);
        }

        if($oUser->getDataVersion() < 64) {            
            UpdateDataVersion::update_63($uId);
        }
        
        if($oUser->getDataVersion() < 65) {            
            UpdateDataVersion::update_60($uId);
        }
        
        if($oUser->getDataVersion() < 66) {            
            UpdateDataVersion::update_65($uId);
        }
        
        if($oUser->getDataVersion() < 67) {            
            UpdateDataVersion::update_66($uId);
        }
        
        if($oUser->getDataVersion() < 68) {            
            UpdateDataVersion::update_67($uId);
        }
        
        if($oUser->getDataVersion() < 69) {            
            UpdateDataVersion::update_68($uId);
        }
        if($oUser->getDataVersion() < 70) 
		{            
            UpdateDataVersion::update_68($uId);
        }
        if($oUser->getDataVersion() < 72) 
        {            
            UpdateDataVersion::update_69($uId);
        }
        
 		if($oUser->getDataVersion() < 73) 
        {            
            UpdateDataVersion::update_72($uId);
        }
        
        if($oUser->getDataVersion() < 74) 
        {            
            UpdateDataVersion::update_73($uId);
        }        
        if($oUser->getDataVersion() < 75)         {            
            UpdateDataVersion::update_74($uId);
        }     
        
        if($oUser->getDataVersion() < 76)         
        {            
            UpdateDataVersion::update_75($uId);
        }
        
        if($oUser->getDataVersion() < 77)         
        {            
            UpdateDataVersion::update_76($uId);
        }  
           
    }
        
    private function postOnWall_User()
    {
        // tao Feed Wall khi khoi tao User
        $param = array();
        $param['TypeFeed']  = 'create';
        $param['UserMsg']   ='À há! Trại cá myFish của tớ nè! Bạn có chưa?';
        $this->postOnWall($param);    
    }
    
    public function updateG(){
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
            return array('Error' => Error::OBJECT_NULL);
        $oUser->promoForAddXu();
        $oUser->save();
        return array('Error' => Error::SUCCESS, 'ZMoney' => $oUser->ZMoney);
    }
    
    // lay thong tin cua User 
    public function getPayInfo(){
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
            return array('Error' => Error::OBJECT_NULL);
        else 
        {
            $result = array();
            $result['Error']=Error::SUCCESS;
            $result['PayInfo'] = array();
            $result['PayInfo']['FirstAddXu'] = $oUser->FirstAddXu;
            $result['PayInfo']['FirstAddXuGift'] = $oUser->FirstAddXuGift;
            return $result;
        }
    }
    
    // use Itemcode
    public function useItemCode($param)
    {
        $Code       = $param['Code'];
        $Element    = intval($param['Element']);
        
        if(!is_string($Code)|| $Code == '')
            return array('Error' => Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
            return array('Error' => Error::NO_REGIS);
        $oItemCode = ItemCode::getById(Controller::$uId);
        
        // decode
        $arr = $oItemCode->decodeItemCode($Code,$Element);
        if($arr['Error'] != Error::SUCCESS)
        {
            //log
            Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,'false');
            return array('Error' => $arr['Error']);
        }
        $oItemCode->save();
        
        $oUser->save();
        
        return $arr;
    }
    /*
    // use Itemcode
    public function useItemCode2222($param)
    {
        $Code = $param['Code'];
        
        if(!is_string($Code)|| $Code == '')
            return array('Error' => Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
            return array('Error' => Error::NO_REGIS);
        $oItemCode = ItemCode::getById(Controller::$uId);
        
        // decode
        $arr = $oItemCode->decodeItemCode($Code);
        if($arr['Error'] != Error::SUCCESS)
        {
            //log
            Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,'false');
            return array('Error' => $arr['Error']);
        }
            

        $oItemCode->save();
        
        $oUser->save();
        
        return $arr;
    }
    */
    // get back all item if market down
    private function getAllItemBack()
    {
        $this->resetMarket();
        $oMarket = Market::getById(Controller::$uId);
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);  
        foreach($oMarket->ItemList as $id => $oItem)
        {
            $index = $id;
            $autoId = $oItem['AutoId'];
            // if sold item, add diamond
            if ($oMarket->ItemList[$index]['isSold'])
            {
                $conf_fee = Common::getConfig('Param','Market','Fee');    
                $oCurrency = array();
                $oCurrency[Type::ItemType] = Type::Diamond;
                $oCurrency[Type::ItemId] = 1;
                $oCurrency[Type::Num] = $oItem['PriceTag'][Type::Diamond] - ceil($conf_fee*$oItem['PriceTag'][Type::Diamond]);    
                $oUser->saveBonus(array($oCurrency));  
                $oMarket->removeItem($index);
                Zf_log::write_act_log(Controller::$uId,0,23,'getDiamondMarketDown',0,0,$oCurrency[Type::Num]);
            }
            else
            {
                $pageId = $oMarket->ItemList[$index]['PageId'];
                $position = $oMarket->ItemList[$index]['Position'];
                $pageType = $oMarket->ItemList[$index]['PageType'];    

                $oObject = $oMarket->ItemList[$index]['Object'];
                $objectType = $oMarket->ItemList[$index]['Type'];
                
                
                //update item to store
                $isStoreBack = true;
                switch($objectType)
                {
                    case Type::Material:
                        if (!$oStore->addItem($oObject->ItemType, $oObject->ItemId, $oObject->Num))
                            $isStoreBack = false;
                        break;
                    
                    case Type::Sparta:
                    case Type::Swat:
                    case Type::Batman:
                    case Type::Spiderman:
                    case Type::Superman:
                    case Type::Ironman:
                        if (!$oStore->addOther($objectType, $oObject->Id, $oObject))
                            $isStoreBack = false;
                            
                        break;
                        
                    case SoldierEquipment::Armor:
                    case SoldierEquipment::Belt:
                    case SoldierEquipment::Bracelet:
                    case SoldierEquipment::Helmet:
                    case SoldierEquipment::Necklace:
                    case SoldierEquipment::Ring:
                    case SoldierEquipment::Weapon:
                        $oStore->addEquipment($oObject->Type, $oObject->Id, $oObject);
                        break;          
                        
                    case Type::Soldier:
                        $oStore->addFish($oObject->Id, $oObject); 
                        break;
                    default :
                        break;
                }
                if ($isStoreBack)
                {
                    $oMarket->removeItem($index);
                    Zf_log::write_market_log(Controller::$uId,0,23,'getAllItemBack',0,0,$pageId,0, $objectType, $oObject);
                }   
            }
                
        }
        
        $oMarket->save(); 
        $oStore->save();
        $oUser->save();
     
    }
    
    private function resetMarket()
    {
        $oLastTime = DataProvider::getMemBase()->get('ResetMarket');
        if ($oLastTime != true)
        {
            $listPageType = array('Material','SuperFish','Armor','Helmet','Weapon','Ring','Bracelet','Necklace','Belt','Soldier','Other');      
            foreach($listPageType as $id => $pageType)
            {
               for ($i=1;$i<=20;$i++)
               {
                   Page::del($pageType,$i);
                   $preAt = DataRunTime::get('Page_'.$pageType.'_'.$i,true);
                   DataRunTime::dec('Page_'.$pageType.'_'.$i,$preAt,true);
               }
               PageManagement::del($pageType);
               $preAutoPM = DataRunTime::get('PageManagement_'.$pageType,true);
               DataRunTime::dec('PageManagement_'.$pageType,$preAutoPM,true); 
            }
            
            DataProvider::getMemBase()->set('ResetMarket',true);
            DataProvider::getMemBase()->set('ResetMarketTime',$_SERVER['REQUEST_TIME']);
        }
        
        
    }
    
    public function buyFeatureLock($param)
    {
        $type = $param['Type'];
        if(empty($type) || ($type != 'ZMoney' && $type != 'Diamond'))
        {                                      
            return array('Error' => Error::ACTION_NOT_AVAILABLE); 
        }
        $oUser = User :: getById(Controller::$uId) ;   
        $cost = Common::getConfig('Param','Password','Cost'); 
        $cost = $cost[$type];

         if($oUser->passwordState == null|| $oUser->passwordState == PasswordState::IS_UNAVIABLE)        
         { 
            if($type == 'ZMoney')
            {
                $info = '1:featureLock:1';
                if($oUser->addZingXu(-$cost, $info))
                {
                    $oUser->passwordState = PasswordState::NO_PASSWORD; 
                }
                else
                {
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                }
            }
            else
            if($type == 'Diamond' && $oUser->Diamond >= $cost)
            {
                if($oUser->addDiamond(-$cost,DiamondLog::buyFeatureLock))
                {
                    $oUser->passwordState = PasswordState::NO_PASSWORD;     
                }
                else
                {
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                }
            }
            else
            {
                //Debug::log('second '.$type.', '.$cost.' zmoney = '.$oUser->getTotalZMoney());
                return array('Error' => Error::ACTION_NOT_AVAILABLE);    
            }
            
            $oUser->save();
            switch($type)
            {
                case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'featureLockZMoney', 0, -$cost) ;
                break;
                case Type::Diamond:
                    Zf_log::write_act_log(Controller::$uId, 0, 20, 'featureLockDiamond', 0, 0, -$cost) ;
                break;
            }
            return array('Error' => Error::SUCCESS);  
         }
         else
         {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);  
         }
    }
    
    public function createPassword($param)
    {
        $oUser = User :: getById(Controller::$uId) ;                                                                          
        if($oUser->passwordState != PasswordState::NO_PASSWORD)
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }
        $md5Password = $param['Md5Password'];
        if(empty($md5Password) || $md5Password == "")
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }
        $oUser->passwordState = PasswordState::IS_LOCK;
        $oUser->setMd5Password($md5Password);
        $oUser->remainTimesInput = Common::getConfig('Param', 'Password', 'MaxTimesInput');
        $oUser->save();
        return array('Error' => Error::SUCCESS);
    }
    
    public function signIn($param)
    {
        $oUser = User :: getById(Controller::$uId) ;   
        if($oUser->passwordState == PasswordState::IS_LOCK || $oUser->passwordState == PasswordState::IS_CRACKING || $oUser->passwordState == PasswordState::IS_BLOCKED)
        {
            //Neu dang bi blocked
            if($oUser->passwordState == PasswordState::IS_BLOCKED) 
            {
                //Neu het thoi gian blocked
                if(($_SERVER['REQUEST_TIME'] - $oUser->timeStartBlock) >= Common::getConfig('Param', 'Password', 'TimeBlockingPassword'))
                {                                                                                                                                   
                    $oUser->passwordState = PasswordState::IS_LOCK;
                    $oUser->remainTimesInput  = Common::getConfig('Param', 'Password', 'MaxTimesInput');
                }
                else
                {
                    return array('Error' => Error::SIGN_ERROR);
                }
            }
            $md5Mixture = $param['Md5Mixture'];
            $timeStamp = $param['timeStamp'];
            
            if(empty($md5Mixture))
            {                               
                 return array('Error' => Error::SIGN_ERROR);   
            }
            //Kiem tra timeStamp
            if(($timeStamp - 60) >  $_SERVER['REQUEST_TIME'] || ($timeStamp + 60) < $_SERVER['REQUEST_TIME'])
            {
                $oUser->remainTimesInput--;
                if($oUser->remainTimesInput <= 0)
                {
                    $oUser->passwordState =  PasswordState::IS_BLOCKED;
                    $oUser->timeStartBlock = $_SERVER['REQUEST_TIME'];
                }
                $oUser->save();
                 return array('Error' => Error::SIGN_ERROR);   
            }
            //Xac nhan mat khau
            $confirmValue =  $oUser->getMd5Password().$timeStamp;
            $md5Confirm = md5($confirmValue);
            if($md5Mixture != $md5Confirm)
            {
                $oUser->remainTimesInput--;
                if($oUser->remainTimesInput <= 0)
                {
                    $oUser->passwordState =  PasswordState::IS_BLOCKED;
                    $oUser->timeStartBlock = $_SERVER['REQUEST_TIME'];
                }
                $oUser->save();
                 return array('Error' => Error::SIGN_ERROR);
            }  
            $oUser->remainTimesInput = Common::getConfig('Param', 'Password', 'MaxTimesInput');
            $oUser->passwordState = PasswordState::IS_UNlOCK;
            $oUser->save();
            return array('Error' => Error::SUCCESS);
        }
        else
        {
            return array('Error' => Error::SIGN_ERROR);
        }
    }
    
    public function changePassword($param)
    {
         $oUser = User :: getById(Controller::$uId) ;
        if($oUser->passwordState == PasswordState::IS_LOCK || $oUser->passwordState == PasswordState::IS_UNlOCK)
        {          
            $md5OldPassword = $param['Md5OldPassword'];
            $md5NewPassword = $param['Md5NewPassword'];
            $timeStamp = $param['timeStamp'];
            
            if(($timeStamp - 60) >  $_SERVER['REQUEST_TIME'] || ($timeStamp + 60) < $_SERVER['REQUEST_TIME'])
            {
                $oUser->remainTimesInput--;
                if($oUser->remainTimesInput <= 0)
                {
                    $oUser->passwordState =  PasswordState::IS_BLOCKED;
                    $oUser->timeStartBlock = $_SERVER['REQUEST_TIME'];
                }
                $oUser->save();                         
                 return array('Error' => Error::SIGN_ERROR);   
            }
            
            if(empty($md5OldPassword) || empty($md5NewPassword))
            {                 
                 return array('Error' => Error::SIGN_ERROR);   
            }
            
            //Kiem tra mat khau cu
            $confirmValue =  $oUser->getMd5Password().$timeStamp;
            $md5Confirm = md5($confirmValue);
            if($md5OldPassword != $md5Confirm)
            {
                $oUser->remainTimesInput--;
                if($oUser->remainTimesInput <= 0)
                {
                    $oUser->passwordState =  PasswordState::IS_BLOCKED;
                    $oUser->timeStartBlock = $_SERVER['REQUEST_TIME'];
                }
                $oUser->save();
                 return array('Error' => Error::SIGN_ERROR);
            }                                                        
            $oUser->remainTimesInput = Common::getConfig('Param', 'Password', 'MaxTimesInput');
            $oUser->setMd5Password($md5NewPassword);
            $oUser->save();
            return array('Error' => Error::SUCCESS);
        }
        else
        {
             return array('Error' => Error::SIGN_ERROR);  
        }
    }
    
    public function crackPassword()
    {
        $oUser = User :: getById(Controller::$uId) ;
        if($oUser->passwordState == PasswordState::IS_LOCK || $oUser->passwordState == PasswordState::IS_UNlOCK)
        {            
            $oUser->timeStartCrackingPassword = $_SERVER['REQUEST_TIME'];
            $oUser->passwordState = PasswordState::IS_CRACKING;
            $oUser->save();
            return array('Error' => Error::SUCCESS);
        }
        else
        {
           return array('Error' => Error::ACTION_NOT_AVAILABLE); 
        }
    }
    
    public function cancelCrackPassword()
    {
        $oUser = User :: getById(Controller::$uId) ;
        if(empty($oUser->passwordState) || $oUser->passwordState != PasswordState::IS_CRACKING)
        {
             return array('Error' => Error::ACTION_NOT_AVAILABLE);        
        }
        
        $oUser->timeStartCrackingPassword = 0;
        $oUser->passwordState = PasswordState::IS_LOCK;
        $oUser->save();
        return array('Error' => Error::SUCCESS);
    }
    
    public function createNPC($uId)
    {
       // create NPC as User
        $NPCId = NPC::NPC_SIGN.$uId;
       
        $avatarType = 1  ;
        $createdTime = $_SERVER['REQUEST_TIME'];
        $oUser = new User($NPCId, $avatarType, $createdTime) ; 
        $oUserProfile = new UserProfile($NPCId);
        
        $oLake1  = Lake::init($NPCId,1);
        $oLake2  = Lake::init($NPCId,2);
        
       // update NPC info       
       $oUser->updateDataVersion() ;
       $oUser->LakeNumb = 2;
       $npcInfoConf = Common::getConfig('NPCInfo');
       foreach ($npcInfoConf as $char => $value)
       {
          if(property_exists($oUser, $char))
            $oUser->$char = $value;
       }
       $oUser->AvatarPic = Common::getSysConfig('flashDir').'Octopus.png';
       // create NPC lake              

       // add Fish
       $oLake1->FishList = array();
       $oLake2->FishList = array();
       
       $npcFishesConf = Common::getConfig('NPCFishes');
       foreach($npcFishesConf as $id => $fish)
       {
           $lake = 'oLake'.$fish['LakeId'];
           if(isset($fish['Level']))  $fish['RateOption'] = Fish::randOption($fish['FishType'],$fish['Level']); 
           
           switch($fish['FishType'])
           {
               case FishType::NORMAL_FISH:
                $oFish = new Fish($fish['Id'],$fish['FishTypeId'],$fish['Sex'],$fish['ColorLevel']);
                break;
               case FishType::RARE_FISH:
                $oFish = new RareFish($fish['Id'],$fish['FishTypeId'],$fish['Sex'],$fish['RateOption'],$fish['ColorLevel']); 
                break;
               case FishType::SPECIAL_FISH:
                $oFish = new SpecialFish($fish['Id'],$fish['FishTypeId'],$fish['Sex'],$fish['RateOption'],$fish['ColorLevel']); 
                break;
           }
                      
           $fishConf = Common::getConfig('Fish',$fish['FishTypeId']);
           $startTime = $_SERVER['REQUEST_TIME'] - $fishConf['MatureTime']* 3600 ;
           $feedCount = $fishConf['MatureTime']* 3600 / $fishConf['EatedSpeed'];
           
           $oFish->StartTime = $startTime;
           $oFish->FeedAmount = $feedCount;
          ${$lake}->addFish($oFish);
       }
       //end add Fish
       
       // create NPC Lake Decoration 
       $oDeco1 = new Decoration($NPCId,1);
       $oDeco2 = new Decoration($NPCId,2);
       $NPCDecorationsConfig = Common::getConfig('NPCDecorations'); 
       foreach($NPCDecorationsConfig as $lakeId => $decoTypes)
       {
           $oDeco = 'oDeco'.$lakeId;
           foreach($decoTypes as $type => $items)
           {
                foreach($items as $id => $item)
                {
                    $oItem = new Item($id, $item['ItemType'], $item['ItemId']);
                    $oItem->ExpiredTime += 155520000;
                    $oItem->setState($item);
                    //Debug::log(${$oDeco}->$type);
                    ${$oDeco}->{$type}[$id] = $oItem;
                }
           }            
       }
       
       // Fish Soldier
       $arraySoldierEquips  = array();
       
       // add Solider
       $npcSoldierConf = Common::getConfig('NPCSoldiers');
       foreach($npcSoldierConf as $id => $soldier)
       {
           $lake = 'oLake'.$soldier['LakeId'];
           $RecipeType = $soldier['RecipeType'];
           $RecipeId = $soldier['RecipeId'];
           
           if(!FormulaType::checkExist($RecipeType))
                continue;
            $conf = Common::getConfig(Type::MixFormula,$RecipeType,$RecipeId);
            $DamConf = Common::getConfig('Damage',$RecipeType,$RecipeId);  
          $autoId       =  $oUser->getAutoId();
          $FishTypeId   =  $conf['FishTypeId'];
          $Rank         =  $conf['Rank']; 
//          if($soldier['Health'] > 0)
                $LifeTime     =  155520000;     // 5 year healthy
//          else $LifeTime = 0;
          
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
              
          $oSoldier = new SoldierFish($autoId,$FishTypeId,$Rank,$LifeTime,$arrIndex['Damage'],$arrIndex['Defence'],$arrIndex['Critical'],$arrIndex['Vitality'], $Elements,$Recipe);   
          $oSoldier->addRankPoint($soldier['RankPoint']);
          $oSoldier->Health = $soldier['Health'];
//          $oSoldier->Status = ($soldier['Health'] > 0) ? SoldierStatus::HEALTHY : SoldierStatus::CLINICAL;           
           $oSoldier->Status = SoldierStatus::HEALTHY;
          
          ${$lake}->addFish($oSoldier);
                        
          $arraySoldierEquips[$id] = $autoId;                                          
       }
       // end add Soldiers
       
       // add Soldiers Equipment
       $oStoreEquip = StoreEquipment::getById($NPCId);
       $npcEquipmentConf = Common::getConfig('NPCEquipments');
       foreach($npcEquipmentConf as $soldierId => $equips)
       {
           if(!isset($arraySoldierEquips[$soldierId])) continue;
           $lake = 'oLake'.$npcSoldierConf[$soldierId]['LakeId'];
           $SoldierId = $arraySoldierEquips[$soldierId];
           $oSoldier = ${$lake}->getFish($SoldierId);
           if(!is_object($oSoldier) || ($oSoldier->FishType!=FishType::SOLDIER))  continue;

           foreach($equips as $equip)
           {
               $Type = $equip['ItemType'];
               $Rank = $equip['Rank'];
               $Color = $equip['Color'];
                // create Equipment
               if($Type == Type::JadeSeal)
               {
                    $oEquip = new Seal($Type,$oUser->getAutoId(), $Rank, $Color);
               }
               else
               {                   
                   $conf_equip = Common::getConfig('Wars_'.$Type);
                   $conf_equip = $conf_equip[$Rank][$Color];
                   $oEquip = new Equipment($oUser->getAutoId(),$conf_equip['Element'],$Type,$Rank,$Color,rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'], SourceEquipment::NPC); 
                   $oEquip->EnchantLevel = $equip['EnchantLevel'];
               }

               // store Equipment
               $oResult = $oStoreEquip->addEquipment($SoldierId, $oSoldier->Element, $oEquip);                    
               if(!$oResult) continue; 
           }
           //update JadeSeal
            if(!empty($oStoreEquip->SoldierList[$SoldierId]['Equipment'][Type::JadeSeal]))
            {
                $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
                $totalEquip = 0;
                foreach ($oStoreEquip->SoldierList[$SoldierId]['Equipment'] as $type => $arrEquip)
                {
                    if(($type == Type::Mask) || ($type == Type::JadeSeal))
                        continue;
                    $totalEquip += count($arrEquip);                
                }
                if($totalEquip == $fullsetConf)            
                    $oStoreEquip->updateBonusFromJadeSeal($SoldierId);
            }
            // end update JadeSeal

       }
       // add Soldiers Meridians
       $activeMeridianConf = Common::getConfig('ActiveMeridian');
       $npcMeridian = Common::getConfig('NPCMeridians');
       foreach($npcMeridian as $soldierId => $meridianConf)
       {
           if(!isset($arraySoldierEquips[$soldierId])) continue;
           $SoldierId = $arraySoldierEquips[$soldierId];
           foreach($meridianConf as $index => $value)
           {
               $oStoreEquip->listMeridian[$SoldierId][$index] = $value;
           }
           $soldierMeridianConf = $activeMeridianConf[$npcSoldierConf[$soldierId]['RecipeId']];
           $soldierIndex = array('Damage'=>0, 'Defence' => 0, 'Vitality'=>0,'Critical' => 0);
           for($i=1;$i<=$meridianConf['meridianRank']; $i++)
           {
               for($j=1;$j<=$meridianConf['meridianPosition']; $j++)
               {
                   $conf = $soldierMeridianConf[$i][$j];
                   foreach($conf as $index => $value)
                   {
                       $soldierIndex[$index] +=$value;
                   }
               }     
           }
           $oStoreEquip->listMeridian[$SoldierId] = array_merge($oStoreEquip->listMeridian[$SoldierId], $soldierIndex);
       }       
         
       $oStoreEquip->save();
       $oLake1->save();
       $oLake2->save();
       $oDeco1->save();
       $oDeco2->save(); 
       $oUserProfile->save();
       $oUser->save();       
       
       return array();
    }
    
    // tang qua khi lan dau tien add xu choUser
    public function getFirstAddXuGift($param)
    {
        $Element = intval($param['Element']);
        $GiftType = intval($param['GiftType']);
        
        if($Element > 5 || $Element < 0 || !in_array($GiftType,array(1,100,200,600,1200,2000),true))
            return array('Error'=> Error::PARAM); 
            
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=> Error::NO_REGIS);
        if($oUser->FirstAddXu <=0 || isset($oUser->FirstAddXuGift[$GiftType]))
            return array('Error'=> Error::OVER_NUMBER);
        // lay qua 
        $conf  = Common::getConfig('FirstAddXuGift');
        if(empty($conf))
            return array('Error'=> Error::NOT_LOAD_CONFIG); 
        $level = 0;
        foreach($conf as $levelxu =>$arrgift)
        {
            if($oUser->FirstAddXu >= intval($levelxu))
                $level = $levelxu ;
        }
        
        if($level == 0 || $GiftType > $level )
            return array('Error'=> Error::NOT_ENOUGH_LEVEL); 
        
            
        $gift = array();
        $gift = Common::addsaveGiftConfig($conf[$GiftType],$Element,SourceEquipment::GIVE);
        
        //$oUser->FirstAddXu = -1;
        $oUser->FirstAddXuGift[$GiftType] = true;
        
        // save Gift
        $oUser->save();
        Zf_log::write_act_log(Controller::$uId,0,20,'getFirstAddXuGift',0,0,$Element,$level);
        
        $gift['Error']= Error::SUCCESS ;
        return $gift;
    }
    
    // quick complete ReputationQuest
    public function doneReputationQuest($param)
    {
        $questId = $param['QuestId'];
        
        if(empty($questId))
            return array('Error'=> Error::PARAM); 
        if (!Event::checkEventCondition('ReputationQuest'))
            return array('Error'=> Error::EVENT_EXPIRED);
            
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=> Error::NO_REGIS);
        
        // check xem quest da xong chua
        $conf_quest = Common::getConfig('ReputationInfo',$oUser->ReputationLevel);
        
        if(!isset($oUser->ReputationQuest[$questId]))
            return array('Error'=> Error::OBJECT_NULL);        
        if ($conf_quest[$questId]['Num'] <= $oUser->ReputationQuest[$questId]['Num'])
            return array('Error'=> Error::QUEST_DONE);
        
        // kiem tra tien 
        $info = "$questId:doneReputationQuest:1" ;
        $Xu = intval($conf_quest[$questId]['ZMoney']);
        if(!$oUser->addZingXu(-$Xu,$info))
            return array('Error'=> Error::NOT_ENOUGH_ZINGXU);
        
        $oUser->ReputationQuest[$questId]['Num'] += $conf_quest[$questId]['Num'] - $oUser->ReputationQuest[$questId]['Num'];
        
        
        $oUser->save();
        
        //log
        Zf_log::write_act_log(Controller::$uId,0,23,'doneReputationQuest',0,-$Xu,$questId,$oUser->ReputationLevel,$oUser->ReputationPoint);
        return array('Error'=>Error::SUCCESS);
        
    }
    
    // get Gift of Reputation Quest
    public function getGiftReputation($param)
    {
        $questId = $param['QuestId'];
        
        if(empty($questId))
            return array('Error'=> Error::PARAM); 
            
        if (!Event::checkEventCondition('ReputationQuest'))
            return array('Error'=> Error::EVENT_EXPIRED);
                
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=> Error::NO_REGIS);
        if($oUser->ReputationQuest[$questId]['isGetGift'])
            return array('Error'=> Error::GOT_GIFT); 
        // check xem quest da xong chua
        $conf_quest = Common::getConfig('ReputationInfo',$oUser->ReputationLevel);
        
        if(!isset($oUser->ReputationQuest[$questId]))
            return array('Error'=> Error::OBJECT_NULL);
        if ($conf_quest[$questId]['Num'] > $oUser->ReputationQuest[$questId]['Num'])
            return array('Error'=> Error::QUEST_NOT_COMPLETE);
        $AddPoint = intval($conf_quest[$questId]['AddPoint']);
        
        // nhan 2 trong 5 ngay
        
        $conf = Common::getParam('ReputationAward');
        if($_SERVER['REQUEST_TIME'] >= $conf['FromTime'] and $_SERVER['REQUEST_TIME'] <= $conf['ToTime'] )
        {
            $AddPoint = intval($AddPoint*$conf['Rate']); 
        }
        
        $oUser->ReputationQuest[$questId]['isGetGift'] = 1 ; // danh dau da nhan qua 
        $oUser->updateReputation($AddPoint);
        $result = array();
        $result['Level'] = $oUser->ReputationLevel ;
        $result['Point'] = $oUser->ReputationPoint ;
        $result['Quest'] = $oUser->ReputationQuest ;
        $result['Error'] = Error::SUCCESS;
        
        $oUser->save();
         //log
        Zf_log::write_act_log(Controller::$uId,0,20,'getGiftReputation',0,0,$questId,$oUser->ReputationLevel,$oUser->ReputationPoint);
        return $result ;       
    }
    
    
    
}
