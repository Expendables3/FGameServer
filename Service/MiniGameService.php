<?php

/**
 * Description of MiniGameService
 *
 * @author hungnm2
 */

 class Result
{
    const ENERGY_ITEM       = 1 ;
    const FISH              = 2 ;
	const MATERIAL          = 3 ;
	const MONEY             = 4 ;
    const FAIL             	= 5 ;
}

class MiniGameService 
{
    
    public function getGiftDay($param)
    {
        $Day = intval($param['Day']);
        if($Day < 1 || $Day > 5)
        {
            return array('Error' => Error :: PARAM) ;
        }
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $oUserProfile =  UserProfile::getById(Controller::$uId);
        // thong so truoc khi thay doi
        $money_log = $oUser->Money ;
        $zmoney_log = $oUser->ZMoney ;

        if(!$oUserProfile->day_saveGift($Day))
        {
           return array('Error' => Error :: PARAM) ;        
        }
        $oUserProfile->save();
        $oUser->save();
        
        // thong so truoc khi thay doi
       $difmoney = $oUser->Money - $money_log;
       $difzmoney = $oUser->ZMoney - $zmoney_log;
       
        Zf_log::write_act_log(Controller::$uId,0,30,'getGiftDay',$difmoney,$difzmoney);
        return array('Error' => Error :: SUCCESS) ;  

    }
    
    //--------------------------------------------------------------------------- 
    public function chooseAgain($param)
    {
        $Day = intval($param['Day']);
        if($Day < 1 || $Day > 5)
        {
            return array('Error' => Error :: PARAM) ;
        }
        
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $oUserProfile =  UserProfile::getById(Controller::$uId);
        
        // thong so truoc khi thay doi
        $money_log = $oUser->Money ;
        $zmoney_log = $oUser->ZMoney ;
        
        $arr_Bonus = $oUserProfile->day_chooseAgain($Day);
        if(!is_array($arr_Bonus))
        {
            return array('Error' => Error :: PARAM) ; 
        }
        $oUserProfile->save();
        $oUser->save();
        
        // thong so truoc khi thay doi
       $difmoney = $oUser->Money - $money_log;
       $difzmoney = $oUser->ZMoney - $zmoney_log;
       
        Zf_log::write_act_log(Controller::$uId,0,23,'chooseAgainDailyBonus',$difmoney,$difzmoney);
        
        $result = array();
        $result['Bonus'] = $arr_Bonus ;
        $result['Error'] = Error :: SUCCESS ;        

        return  $result;  

    }

     /*
     * Mini game cau ca
     * UserID   Unique ID cua nguoi so huu ho duoc dung de cau ca
     * Slot     Slot quy dinh % nhan thuong cua user
     * $giftLevel  Muc phan thuong tuong ung voi Slot
     * $giftType  Loai phan thuong: food, deco, money, fish...
     *
     */
     /**
	 * @created 2-10-2010
	 * @Description : ham thuc hien viec cau ca
	 */

     public function fishing($param)
     {
        $UserId    = $param['UserId'];
        $LakeId    = $param['LakeId'];
        // kiem tra du lieu vao
        if (empty ($UserId)|| empty ($LakeId)|| ($LakeId < 1) || ($LakeId > 3))
        {
            return array('Error' => Error :: PARAM) ;
        }
        if($UserId == Controller::$uId) // ko cau ca o nha minh
        {
          return array('Error' => Error :: PARAM) ;
        }

        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        // kiem tra ban be cua user
        if(!$oUser->isFriend($UserId))
        {
          return  array('Error' => Error :: NOT_FRIEND) ;
        }

        // kiem tra thanh nang luong cua nguoi choi
        $EnergyConfig = Common::getConfig('Energy');
        if(empty($EnergyConfig['fishing']))
        {
           return  array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }
        
        if (!$oUser->addEnergy(-$EnergyConfig['fishing']))
		{
		    return array('Error' => Error :: NOT_ENOUGH_ENERGY) ;
		}
		// thong so truoc khi thay doi
        $money = $oUser->Money ;
        $zmoney = $oUser->ZMoney ;
        
		$conf_fishing = Common::getConfig('Fishing');
        
        $arr = array();
        foreach ($conf_fishing as $id => $oBonus)
        {
        	$arr[$id] = $oBonus['Rate'];
        }
        
        $arr_return = array();
        $arr_return['ItemType']= '';
        $arr_return['ItemId']= null;
        $arr_return['Num']= 0;
       	$bonusId = Common::randomIndex($arr);
        
        switch($conf_fishing[$bonusId]['ItemType'])
        {
          case Type::Fish:
          {	
          	 $oLake = Lake::getById($UserId,$LakeId);	
          	 $arr_FishTypeId = array();
             if ($oLake->getFishCount()==0){
                $arr_return['ItemType']= '';
                $arr_return['ItemId']= null;
                $arr_return['Num']= 0;
             }
             else {
                $arr_Fish = $oLake->FishList;
               foreach ($arr_Fish as $id => $ofish)
               {
                   if (in_array($ofish->FishType,array(0,1,2)))
                        $arr_FishTypeId[$ofish->FishTypeId] = TRUE;
               }
               $arr_return['ItemType']= Type::Fish;
               $arr_return['ItemId']= array_rand($arr_FishTypeId);
               $arr_return['Num']= 1;  
               }
             
             break;

          }
          
          case Type::ItemCollection :
             $arr_return['ItemType'] = $conf_fishing[$bonusId]['ItemType'];
             $arr_return['ItemId'] = $conf_fishing[$bonusId]['ItemId'];
             $arr_return['Num'] = $conf_fishing[$bonusId]['Num'];
             break;   
             
             
          case Type::Nothing :
          {
            $arr_return['ItemType']= '';
            $arr_return['ItemId']= null;
            $arr_return['Num']= 0;
            break;
          }

        }
        $arr_gift[] = $arr_return ;
        $oUser->saveBonus($arr_gift);
        $oUser->save();
        $arr_return['Error'] = Error ::SUCCESS ;

        // log
        // thong so truoc khi thay doi
     	$difmoney = $oUser->Money - $money;
     	$difzmoney = $oUser->ZMoney - $zmoney;
        Zf_log::write_act_log(Controller::$uId, $UserId, 30, 'fishing', $difmoney, $difzmoney);
        
        return $arr_return ;
     }
     
          /**
     * quay so may man
     * 
     */
     public function playLuckyMachine($param)
     {
         $TicketType    = intval($param['TicketType']);
         if(!in_array($TicketType,array(1,10,100),true))
            return array('Error' => Error :: PARAM) ; 
         
         $oUser = User::getById(Controller::$uId);         
         if(!is_object($oUser))
            return array('Error' => Error :: NO_REGIS);
         if(!MiniGame::checkMinigameCondition(GameType::LuckyMachine))   
         {
             return array('Error' => Error :: EVENT_EXPIRED) ; 
         }
         
         $oMGame = MiniGame::getById(Controller::$uId);
         if(!is_object($oMGame))
         {
             $oMGame = new MiniGame(Controller::$uId);
             $oMGame->save();
         }
               
         if(!isset($oMGame->GameList[GameType::LuckyMachine]))
            return array('Error' => Error :: OBJECT_NULL);
                  
         $oStore = Store::getById(Controller::$uId);
         
         $arr = array();
         $arr['Equipment'] = array();
             
         // gioi han so lan quay cua user trong ngay
/*         $key = 'Play_Limit_'.$TicketType ;
         $Play_Limit = Common::getConfig('Event','LuckyMachine');
         $Play_Limit = $Play_Limit[$key] ;
         $User_Play_Limit = $oMGame->GameList[GameType::LuckyMachine]['Limit'][$key] ;
         if($User_Play_Limit >= $Play_Limit)
            return array('Error' => Error :: OVER_NUMBER);  */
         //---------
         
         // kiem tra ve
         if(!$oStore->useEventItem(GameType::LuckyMachine,Type::Ticket,1,$TicketType))
            return array('Error' => Error :: NOT_ENOUGH_ITEM);
         
         $M_GiftContent = Common::getConfig('M_GiftContent');
         foreach($M_GiftContent as $Id => $value)
         {
             $arr_rand[$Id] = $value['Rate'];
         }
         
         $isVip = false ;
         if($TicketType == 100)
         {
              $oUserPro = UserProfile::getById(Controller::$uId);
              $oUserPro->updateLimitAllLife('LuckyMachine_'.$TicketType,1);
              if(!$oUserPro->checkLimitAllLife('LuckyMachine_'.$TicketType,60) )
              {
                  $isVip = true ;
              }
              $oUserPro->save();
         }
        
         
        $arr_Gift = array();  
        for($i = 1 ; $i<= $TicketType;$i++)
        { 
            do
            {
                $GiftId = Common::randomIndex($arr_rand); 
            }
            while( isset($M_GiftContent[$GiftId]['LimitNum']) && $oMGame->GameList[GameType::LuckyMachine]['Limit']["Gift_".$GiftId] >= $M_GiftContent[$GiftId]['LimitNum']);
            
            if($isVip)
            {
                $oUserPro = UserProfile::getById(Controller::$uId);    
                $GiftId = array_rand(array(5=>50,17=>50),1);
                $isVip = false ;
                $oUserPro->resetLimitAllLife('LuckyMachine_'.$TicketType) ; 
                $oUserPro->save();                 
            }
            $arr_Gift[$i] = $M_GiftContent[$GiftId] ; 
            
            if(in_array($GiftId,array(5,16,17),true))
                DataProvider::getMemcache()->set('LuckyMinigame_UserGetVipMax',$oUser->getUserName(),0,3600*24*30);    
            
            $oMGame->GameList[GameType::LuckyMachine]['Limit']["Gift_".$GiftId] +=1 ; 
        }

         //$oMGame->GameList[GameType::LuckyMachine]['Limit'][$key] +=1 ;
         
         $GiftList = Common::addsaveGiftConfig($arr_Gift,rand(1,5),SourceEquipment::LUCKYMACHINE);
         
         //save
         $oStore->save();
         $oMGame->save();
         
         //log

         Zf_log::write_act_log(Controller::$uId,0,20,'LuckyMachine',0,0,$TicketType);
         
         $arr['GiftId'] = $GiftId ;
         $arr['GiftList'] = $GiftList ;
         $arr['Error'] = Error::SUCCESS ;
         return $arr ;
         
     }
   
     /**
     * quay so may man
     * 
     */
     
     public function miniGame_BuyItem($param)
     {
        $ItemType  = $param['ItemType'];
        $ItemId    = $param['ItemId'];
        $Num       = intval($param['Num']);

        if(empty($ItemType)|| empty($ItemId)|| $Num < 1)
            return array('Error'=>Error::PARAM);

        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);   

        if(!is_object($oUser) || !is_object($oStore))
            return array('Error' => Error :: OBJECT_NULL);
            
        $Conf = Common::getConfig($ItemType,$ItemId);
        if(empty($Conf))
            return array('Error' => Error :: NOT_LOAD_CONFIG);
        
        // kiem tra loai Unlock
        if($Conf['UnlockType'] == 5 || $Conf['UnlockType']== 6)
            return array('Error' => Error ::TYPE_INVALID ) ;
        // luu thong so cu~
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        // kiem tra tien
        $info = '1:buyItem:1';
        if(!$oUser->addZingXu(-$Conf['ZMoney']*$Num,$info))
            return array('Error' => Error ::NOT_ENOUGH_ZINGXU ) ;
        
        if($ItemType == Type::Ticket)
            $oStore->addEventItem(GameType::LuckyMachine,$ItemType,1,intval($Conf['Num']*$Num));
               
        $oUser->save();
        $oStore->save();
        // log
        $conf_log = Common::getConfig('LogConfig');
        $TypeItemId = $conf_log[$ItemType];
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;

        Zf_log::write_act_log(Controller::$uId,0,23,'buyOther',$moneyDiff,$zMoneyDiff,$TypeItemId,$ItemId, 0,0,$Num);
        
        return array('Error' => Error ::SUCCESS ) ;
     }
     
     public function getPowerTinhQuest()
     {
         $oPower = PowerTinhQuest::getById(Controller::$uId);         
         return array('Error' => Error::SUCCESS, 'Quest' => $oPower->getQuest());
     }
     
     public function exchangePowerTinh($param)
     {
         $idExchangeItem = intval($param['IdExchangeItem']);
         $oPowerTinhQuest = PowerTinhQuest::getById(Controller::$uId);        
         
         $conf_exchange = Common::getConfig('PowerTinhQuest_Reward');
         if (!is_array($conf_exchange[$idExchangeItem]))
            return array('Error' => Error::PARAM);
         if (!$oPowerTinhQuest->usePoint($conf_exchange[$idExchangeItem]['Point']))
            return array('Error' => Error::NOT_ENOUGH_POWERTINH_POINT);
         $oUser = User::getById(Controller::$uId);
         $oUser->saveBonus($conf_exchange[$idExchangeItem]['Reward']);
         $oPowerTinhQuest->save();
         $oUser->save();
         
         Zf_log::write_act_log(Controller::$uId,0,20,'exchangePowerTinhItem',0,0, $idSeal); 
         return array('Error' => Error::SUCCESS);         
     }
}
     
?>
