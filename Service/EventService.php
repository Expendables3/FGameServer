<?php

/**
 * Description of Event Service
 * recreate, reduce file size. old api: EventServiceBak
 * @author taint
 * 
 *  10/09/2012
 */
class EventService
{     
    /*
     public function colp_buyColItem($params)
     {
         $ItemType = $params['ItemType'];
         $ItemId  = $params['ItemId'];
         $Num = $params['Num'];
         $PriceType = $params['PriceType'];
         
         if(!is_int($Num) || ($Num < 0))   
            return Common::returnError(Error::PARAM);
            
         $cost = Common::getConfig('ColP_BuyItem', $ItemType, $ItemId);
         $cost = $cost['Price'][$PriceType];
         if(!isset($cost))
            return Common::returnError(Error::PARAM);
         $oUser = User::getById(Controller::$uId);
         switch($PriceType)
         {
             case 'ZMoney':
                $info = $ItemId.':'.$ItemType.':'.$Num;
                if(!$oUser->addZingxu(-$cost*$Num, $info))
                    return Common::returnError(Error::NOT_ENOUGH_ZINGXU);
                break;
             default:
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                break;
         }
         $oEvent = Event::getById(Controller::$uId);
         
         switch($ItemType)
         {
             case 'ColPItem':
                $oEvent->colp_addItem($ItemType, $ItemId, $Num);
                break;
             case 'Collection':
                $items = Common::getConfig('ColP_ExchangeGift', $ItemType, $ItemId);
                $items = $items['Require'];
                foreach($items as $item)
                {
                    $oEvent->colp_addItem($item['ItemType'], $item['ItemId'], $item['Num']*$Num);
                }
                break;
             default:
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
         }
         
         $oUser->save();
         $oEvent->save();
                          
         $runArr['Error'] = Error::SUCCESS;
         $runArr['ZMoney'] = $oUser->ZMoney;
         
         // log
         switch($PriceType)
           {
               case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'buyCollectPattern', 0, -$cost*$Num, $ItemType, $ItemId, 0, 0, $Num) ;
                    break;
           }
        //end log
         
         
         return $runArr;         
     }
     
     public function colp_exchangeCollection($params)
     {
         if(!Event::checkEventCondition(EventType::CollectPattern))
            return Common::returnError(Error::EVENT_EXPIRED);
            
         $ItemType = $params['ItemType'];
         $ItemId  = $params['ItemId'];
         $Num = $params['Num'];
         if($ItemType != 'Collection')
            return Common::returnError(Error::PARAM);
         if(!is_int($Num) || ($Num < 0))   
            return Common::returnError(Error::PARAM);
            
         $exchangeConf = Common::getConfig('ColP_ExchangeGift', $ItemType, $ItemId);
         $items = $exchangeConf['Require'];
         if(empty($items))
            return Common::returnError(Error::PARAM);
            
         $oEvent = Event::getById(Controller::$uId);
         
         foreach($items as $item)
         {
             if(!$oEvent->colp_useItem($item['ItemType'], $item['ItemId'], $item['Num']*$Num))
                return Common::returnError(Error::NOT_ENOUGH_ITEM);
         }
         
         $giftsConf = array();
         for($i = 1; $i <= $Num; $i++)
         {
            foreach($exchangeConf['Gift'] as $id=>$gift)
             {
                 if($gift['Rate'] == 100)
                 {                     
                     $giftsConf[] = $gift; 
                 }
                    
                 else
                    $itemRate[$id] = $gift['Rate'];
             }
             if(isset($itemRate))
             {
                 $id = Common::randomIndex($itemRate);                 
                 $giftsConf[] = $exchangeConf['Gift'][$id];             
             }                
         }
         $giftsConf = Common::groupItems($giftsConf);
         $gifts = Common::addsaveGiftConfig($giftsConf,"", SourceEquipment::EVENT);
         // exchange, add point
         $oEvent->colp_addPointGift($Num);
         
         $oEvent->save();
         
         $runArr['Error'] = Error::SUCCESS;
         $runArr['Gifts'] = $gifts;
                           
         return $runArr;
     }
     
     public function colp_chooseGift($params)
     {
         if(!Event::checkEventCondition(EventType::CollectPattern))
            return Common::returnError(Error::EVENT_EXPIRED);
            
         $ItemType = $params['ItemType'];
         $ItemId  = $params['ItemId'];
         $Num = $params['Num'];
         $Ans = $params['Ans'];
         
         if($ItemType != 'ColPGGift')
            return Common::returnError(Error::PARAM);
         if(!is_int($Num) || ($Num < 0) || !is_int($Ans) || ($Ans < 0))   
            return Common::returnError(Error::PARAM);
         $oEvent = Event::getById(Controller::$uId);
         
         $real = $oEvent->colp_addExchangeTimes($ItemType, $ItemId, $Num);
         if(is_bool($real) && !$real)
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
         
         if(!$oEvent->colp_useItem($ItemType, $ItemId, $real))
                return Common::returnError(Error::NOT_ENOUGH_ITEM);
                
         $exchangeConf = Common::getConfig('ColP_ExchangeGift', $ItemType, $ItemId);
         $giftConf = $exchangeConf[$Ans];
         foreach($giftConf as &$gift)
         {             
            $gift['Num'] *= $real;
         }
         $gifts = Common::addsaveGiftConfig($giftConf, "", SourceEquipment::EVENT);
         
         $oEvent->save();
         
         $runArr['Error'] = Error::SUCCESS;
         $runArr['Gifts'] = $gifts;
                           
         return $runArr;
     }
     
     public function colp_comboGGift($params)
     {
         if(!Event::checkEventCondition(EventType::CollectPattern))
            return Common::returnError(Error::EVENT_EXPIRED);
            
         $Num = $params['Num'];
         $Ans = $params['Ans'];
         
         if(!is_int($Num) || ($Num < 0) || !is_int($Ans) || ($Ans < 0))   
            return Common::returnError(Error::PARAM);
            
         $exchangeConf = Common::getConfig('ColP_ExchangeGift', 'Combo', 1);
         $items = $exchangeConf['Require'];
         if(empty($items))
            return Common::returnError(Error::PARAM);
            
         $oEvent = Event::getById(Controller::$uId);
         
         $real = $oEvent->colp_addExchangeTimes('Combo', 1, $Num);
         if(is_bool($real) && !$real)
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
            
         foreach($items as $item)
         {
             if(!$oEvent->colp_useItem($item['ItemType'], $item['ItemId'], $item['Num']*$real))
                return Common::returnError(Error::NOT_ENOUGH_ITEM);
         }
         $giftConf = array();
         $giftConf[] = $exchangeConf['Gift'][$Ans];
         
         foreach($giftConf as &$gift)
         {
             $gift['Num'] *= $real;
         }

         $gifts = Common::addsaveGiftConfig($giftConf, "", SourceEquipment::EVENT);
         
         $oEvent->save();
         
         $runArr['Error'] = Error::SUCCESS;
         $runArr['Gifts'] = $gifts;
         
         // log
         Zf_log::write_act_log(Controller::$uId, 0, 20, 'comboCollectPattern', 0, 0, 'Combo', 1) ;
                           
         return $runArr;
     }
     
     public function colp_exchangePointGift($params)
     {
         if(!Event::checkEventCondition(EventType::CollectPattern))
            return Common::returnError(Error::EVENT_EXPIRED);
            
         $mileStone = $params['MileStone'];
         $oEvent = Event::getById(Controller::$uId);
         $pointGift = $oEvent->colp_getPointGift();
         $exchangePoint = Common::getConfig('ColP_ExchangeGift', 'PointGift');
         
         if(!is_array($mileStone))
            return Common::returnError(Error::PARAM);
         foreach($mileStone as $stone)
         {
            if(($pointGift['Point'] < $stone) || (in_array($stone, $pointGift['Got'])))
                return Common::returnError(Error::PARAM);
            if(empty($exchangePoint[$stone]))
                return Common::returnError(Error::PARAM);                               
         }
         $giftConf = array();
         
         foreach($mileStone as $stone)
         {
             $pointGift['Got'][] = $stone;
             $giftConf = array_merge($exchangePoint[$stone], $giftConf);
         }
         $oEvent->colp_setPointGift($pointGift);
         
         $gifts = Common::addsaveGiftConfig($giftConf, "", SourceEquipment::EVENT);
         
         $oEvent->save();
         
         $runArr['Error'] = Error::SUCCESS;
         $runArr['Gifts'] = $gifts;
         
         //log
         foreach($mileStone as $stone)
         {
            Zf_log::write_act_log(Controller::$uId, 0, 20, 'pointgiftColP', 0, 0, $stone);    
         }         
         
         return $runArr;
     }
        
     // cham soc cay hoa trong event Noel
    public function careFlower()
    {
        $oUser = User::getById(Controller::$uId);
        
        // kiem tra xem event nay co ton tai hay ko ?
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error' => Error::NO_REGIS);
        if(!Event::checkEventCondition(EventType::Event_8_3_Flower))
            return array('Error' => Error::ARRAY_NULL);
            
        // kiem tra xem da du dieu kien cham soc hay chua ?
        if(!$oEvent->e_checkCareFlower())
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }
        
        // create gift for user
        $level = $oEvent->EventList[EventType::Event_8_3_Flower]['Level'];
        $boxconf = Common::getConfig('CoralTree',$level);
        
        $arr['Bonus'] = array();
        $arr['Bonus'] = $boxconf['Bonus'] ;
        $oUser->saveBonus($arr['Bonus']);  
        
        // luckybonus
        $arr['LuckyBonus'] = array();
        foreach($boxconf['Flower'] as $id =>$gift )
        {
            $rand = mt_rand(1,100);
            if($rand <= $gift['Rate'] )
            {
                $arr['LuckyBonus'][$id] = $gift ;
                if(is_array($gift['Num']))
                {
                    $idd = array_rand($gift['Num'],1);
                    $arr['LuckyBonus'][$id]['Num'] = $gift['Num'][$idd] ;
                } 
            }
        }
        $oUser->saveBonus($arr['LuckyBonus']);
        
        // update level of Tree and last  care Time 
        $oEvent->e_updateLevelFlower();

        $oEvent->save();
        $oUser->save();
        
        //return
        $arr['Error'] = Error::SUCCESS ;
        
        return $arr ;
        
    }
      
    public function speedUp()
    {
        $oUser = User::getById(Controller::$uId);
        
        // kiem tra xem event nay co ton tai hay ko ?
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error' => Error::NO_REGIS);
        if(!Event::checkEventCondition(EventType::Event_8_3_Flower))
            return array('Error' => Error::ARRAY_NULL);
        
        // kiem tra dieu kien tang toc
        if(!$oEvent->e_checkSpeedUp())
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }  
        
        // kiem tra tien 
        $level = $oEvent->EventList[EventType::Event_8_3_Flower]['Level'];
        $ZMoney = Common::getConfig('CoralTree',$level,'ZMoney');
        $info = '1:SpeedUpTree:1' ;
        if(!$oUser->addZingXu(-$ZMoney,$info))
             return array('Error' => Error::NOT_ENOUGH_ZINGXU);       
                
        $oEvent->e_updateSpeedUp();
        $oEvent->save();
        $oUser->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'SpeedUp',0,-$ZMoney,$level);
        
        return array('Error' => Error::SUCCESS);       
    }
     */
     
    //#NOEL2012
    /**
    * BEGIN EVENT NOEL 2012
    */
    /**
    *  written by: thedg25
    *  date: 2012/11/21
    *  desc: buy Item, buy via package 
    *  json ex: {"ItemType":"Package","ItemId":1,"Num":1} 
    */    
    public function buyCandy($param){
        $ItemType =  $param["ItemType"];
        $ItemId =  intval($param["ItemId"]);
        $Num = intval($param["Num"]);
        $PriceType = "ZMoney";

        // check time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);                  
        
        if(empty($ItemType) || $ItemId <=0 ){
            return array('Error' => Error::PARAM);
        }
        // check buy package if is package then Num =1     
        if($ItemType == 'Package' && $Num <=0 ) {            
            return array('Error' => Error::PARAM);    
        }
        
        
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

        $oStore = Store::getById(Controller::$uId);
        // get coin before buy
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        
        // get price info 
        $priceInfo = Common::getConfig("Noel_Candy", $ItemType, $ItemId);
        
        if(!isset($priceInfo[$PriceType]) || intval($priceInfo[$PriceType])<=0 ) {
            return array('Error' => Error ::NOT_LOAD_CONFIG);
        }
        $cost = intval($priceInfo[$PriceType])*$Num;
        
        switch($PriceType)
        {
            case Type::Money:
                if (!$oUser->addMoney(-$cost,''))
                    return array('Error' => Error::NOT_ENOUGH_MONEY);            
                break;            
            case Type::ZMoney:
                $info = '1:'.$ItemType.':'.$Num;
                if (!$oUser->addZingXu(-$cost,$info))    
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                break;
            
            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::buyCandy))                    
                    return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;
            
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        // Update store
        
        if($ItemType != 'Package') {
            $oStore->addEventItem(EventType::Noel,$ItemType,$ItemId,$Num);    
        } else {
            
            $Items = Common::getConfig('Noel_Make','Make');
            $Items = $Items[$ItemId]["Require"];            
            
            foreach($Items as $Item){
                $oStore->addEventItem(EventType::Noel,$Item[Type::ItemType],$Item[Type::ItemId],$Num*$Item[Type::Num]);    
            }
        }
        
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        $moneyDiff = $oUser->Money - $moneyDiff;
        if($zMoneyDiff !=0 || $moneyDiff !=0 ){
            Zf_log::write_act_log(Controller::$uId,0,23,'buyCandy',$moneyDiff,$zMoneyDiff,$ItemType, $ItemId, 0,0, $Num);    
        }        
        
        $oUser->save();
        $oStore->save();
        
        $arr_result = array();        
        $arr_result['ZMoney'] = $oUser->ZMoney;        
        $arr_result['Error'] = Error :: SUCCESS;
        return $arr_result;
    }
        /**
    *  written by: thedg25
    *  date: 2012/11/21
    *  desc: make bullet from candys
    *  json format: {"ItemType":"Make","Num":1} 
    */    
    public function makeBullet($params){
        //check condition time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);
        // get input param info    
        $ItemType = $params['ItemType']; 
        $ItemId = $params['ItemId']; 
                
        $Num = intval($params['Num']);
        // check valid input 
        if($ItemType != 'Make')
            return Common::returnError(Error::PARAM);
        if($Num < 0)   
            return Common::returnError(Error::PARAM);        
        // get config info
        $makeConf = Common::getConfig('Noel_Make', $ItemType);
        $makeConf = $makeConf[$ItemId]; 
        $NumMake =  intval($makeConf['NumMake']['Num']);
        
        $items = $makeConf['Require'];
        if(empty($items))
            return Common::returnError(Error::NOT_LOAD_CONFIG);
        
        // check number items in store, to make enought?
        
        
        $oStore = Store::getById(Controller::$uId);
        foreach($items as $item) {
            if(!$oStore->useEventItem(EventType::Noel, $item['ItemType'], $item['ItemId'], $item['Num']*$Num))
                return Common::returnError(Error::NOT_ENOUGH_ITEM);
        }
        
        
        // random item maked output     
        $makeItems = array();    
        
        for($i = 1; $i <= $Num; $i++)
        {            
            for($j=1; $j<=$NumMake; $j++){
                $makeItems[] = Common::pickItem($makeConf['Gift']);    
            }            
        }        
        $makeItems = Common::groupItems($makeItems);        
        
        foreach($makeItems as $item) {
            $oStore->addEventItem(EventType::Noel, $item['ItemType'], $item['ItemId'], $item['Num']);                            
        }

        $oStore->save();
            
        foreach($makeItems as $item) {
            Zf_log::write_act_log(Controller::$uId,0,20,'makeBullet',$moneyDiff,$zMoneyDiff, $item['ItemType'], $item['ItemId'], 0, 0, $item['Num']);
        }        
        $runArr = array();
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Items'] = $makeItems;                       
        return $runArr;        
    }
    
    
    
    /**
    *   Written by: thedg25
    *   Date: 2012/11/24
    *   Init board game and return client
    *   Json format: {"BoardId":1}
    */    
    public function getBoardGame($Params) {
        // get params info        
        $IsPlayNow = $Params["IsPlayNow"];
        $PriceType = "ZMoney";
        
        $Now = $_SERVER['REQUEST_TIME']; 
        // check valid input info
        $oUser = User::getById(Controller::$uId);
        $zMoneyDiff = $oUser->ZMoney;
        
        $oUser = User::getById(Controller::$uId);
        $oNoel = Noel::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId);  
        $oEvent->updateTimeFireFish();
        $oEvent->save();
        $StartTime = $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"];
        $FinishTime = $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"];
        $NumPlay = $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"];
        
                      
        $CurrentBoardId = intval($oNoel->getCurrentBoardId());
                
        $BoardGame = $oNoel->getBoardGame($CurrentBoardId);                
        $BoardConfig = Common::getConfig("Noel_BoardConfig","BoardGame");
        $WaitTime = $BoardConfig["WaitTime"];
        $LimitDay = $BoardConfig["LimitDay"];        
        if($NumPlay>$LimitDay) {
            return array('Error' => Error::EXPIRED);
        }
                    
        if( $StartTime ==-1 ) { // start or finish one play game
            if( ($FinishTime + $WaitTime) <= $Now ) {
                $BoardId = 1;
                $BoardGame = $oNoel->intBoardGame($BoardId); 
                $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = $Now;  
                $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"] = $NumPlay+1; 
                $oNoel->resetRequestKey();
            } else {   // enought waiting time 
                if($IsPlayNow==true) {
                    $cost = $BoardConfig[$PriceType];
                    $info = '1:PlayNow:1';
                    if (!$oUser->addZingXu(-$cost,$info)) {
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                    }                                
                    $BoardId = 1;
                    $BoardGame = $oNoel->intBoardGame($BoardId); 
                    $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = $Now;                                            
                    $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"] = $NumPlay+1;                       
                    $oNoel->resetRequestKey();
                } else {
                    return array('Error' => Error :: WAIT_ENOUGHT_TIME);     
                }                    
            }               
        } else {
                            
            $IsPassBoard = $oNoel->getIsPassBoard();  
            if($IsPassBoard == false ){ // check pass board                
                $BoardId = $CurrentBoardId;                         
            } else {                    
                $BoardId = $CurrentBoardId+1;                        
                $BoardGame = $oNoel->intBoardGame($BoardId);                                        
            }                              
        }                        
        
        // getBoardGame        
        if(!is_array($BoardGame)){
            return array('Error' => Error :: PARAM); 
        }
        $oNoel->setCurrentBoardId($BoardId);
                
        $oNoel->save(); 
        $oEvent->save();
        if($IsPlayNow) {
            $oUser->save();
            $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
            if($zMoneyDiff !=0 ){                
                Zf_log::write_act_log(Controller::$uId,0,23,'playNow',0,$zMoneyDiff, 'PlayNow', 1, 0,0, 1);    
            }                    
        }   
                
        $runArr['Error'] = Error::SUCCESS;
        $runArr['BoardGame'] = $BoardGame; 
        $runArr['BoardId'] = $BoardId;         
        $runArr['NumFishDie'] = $oNoel->getNumFishDie();                  
        $runArr['NumPlay'] = $oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"];         
        $runArr['RetBonus'] = $oNoel->getRetBonus();
        $RequestKey = $oNoel->getRequestKey();
        $MaxKey = $RequestKey[0];        
        foreach($RequestKey as $Key){            
            if($MaxKey < $Key) $MaxKey = $Key;
        }        
        $runArr['MaxKey'] = $MaxKey;  
        $runArr['RequestKey'] = $RequestKey;  
        return $runArr;
    }
    
    public function buyBulletGold($params){
        // check time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);                          
        $BulletType = BulletType::BulletGold;
        $BulletId = 1;
        $BulletNum = intval($params["Num"]);
        $PriceType = "Money";
        //check valid 
        if($BulletNum<=0) {
            return array('Error' => Error::PARAM);
        }        
        $oUser = User::getById(Controller::$uId);   
        $zMoneyDiff = $oUser->ZMoney;   
        $moneyDiff = $oUser->Money;   
           
        $oStore = Store::getById(Controller::$uId);
        $BulletConfig = Common::getConfig("Noel_Bullet");                
        $cost = $BulletNum*intval($BulletConfig[$BulletType][$BulletId]["Money"]);
        if (!$oUser->addMoney(-$cost,'')) {
            return array('Error' => Error::NOT_ENOUGH_MONEY);            
        }                  
        $oStore->addEventItem(EventType::Noel,$BulletType,$BulletId,$BulletNum);
        
        $oStore->save();
        $oUser->save();
        $moneyDiff = $oUser->Money - $moneyDiff; 
        Zf_log::write_act_log(Controller::$uId,0,23,'buyBulletGold',$moneyDiff,0,$BulletType, $BulletId, 0, 0, $BulletNum);            
                
        $runArr['Error'] = Error::SUCCESS;        
        return $runArr;        
    }
    
    /**
    *   Written by: thedg25
    *   Date: 2012/11/24
    *   Update number Bullet of User
    *   Json format: {"ItemType":"BulletSweet","ItemId":1,"Num":1} 
    */
    public function updateBullet($params){
        // get params info
        $ItemType = $params["ItemType"];
        $ItemId = intval($params["ItemId"]);
        $Num = intval($params["Num"]);
        // check valid input info
        if(empty($ItemType) || $ItemId<0 || $Num<=0 ) {
            return array('Error' => Error::PARAM);                  
        }        
        $oStore = Store::getById(Controller::$uId);
        if(!$oStore->useEventItem(EventType::Noel, $ItemType, $ItemId, $Num) ){
            return array('Error' => Error::NOT_ENOUGH_CONDITION); 
        }
        $oStore->save();
        $runArr['Error'] = Error::SUCCESS;        
        return $runArr;
    }
    
    public function checkKeyValid($RequestKey, $Key){
        $cnt = count($RequestKey);
        $IsExist = false;
        for($i=0; $i< $cnt; $i++) {
            if($RequestKey[$i] == $Key) {
                $IsExist = true;
                break;
            }
        }
        if($IsExist) return false;
        return true;        
    }

    /**
    *   Written by: thedg25
    *   Date: 2012/11/27
    *   Fire Gun 
    *   Json format: {"BoardId":4,"IsAuto":0,"FireInfo":[{"BulletType":"Bullet","BulletId":1,"Num":2,"Fishs":[{"Id":1,"Num":1},{"Id":3,"Num":2}]},{"BulletType":"Bullet","BulletId":2,"Num":2,"Fishs":[{"Id":1,"Num":1},{"Id":3,"Num":2}]}]}
    */    
    public function fireGun($params){
                
        // get Param info 
        $Key = intval($params["Key"]);
        $BoardId = intval($params["BoardId"]);
        $FireInfo = $params["FireInfo"];                
        $Now = $_SERVER['REQUEST_TIME'];        
        // check time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);                  
        // check valid
        $oNoel = Noel::getById(Controller::$uId);        
        $CurrentBoardId = intval($oNoel->getCurrentBoardId());                
        if($BoardId != $CurrentBoardId) {
           return array('Error' => Error::PARAM);  
        }
        
        //        
        $RequestKey = $oNoel->getRequestKey();
        if(!$this->checkKeyValid($RequestKey, $Key)) {
            return array('Error' => Error::PARAM);  
        } else {
            $Index = rand(0,count($RequestKey)-1);
            $RequestKey[$Index] = $Key;
            $oNoel->setRequestKey($Index, $Key);
        }        
        
        $BoardGame = $oNoel->getBoardGame($BoardId);

        $oEvent = Event::getById(Controller::$uId);  
        $oEvent->updateTimeFireFish();
        $StartTime = $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"];
        if($StartTime ==-1){
            return array('Error' => Error::WAIT_ENOUGHT_TIME);  
        }                        
        
        if($BoardId==5) {
            $FinishTime = $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"];
            $TimeConfig = Common::getConfig("Noel_BoardConfig",$BoardId);
            $TimeConfig = $TimeConfig["Time"];
            if( ($FinishTime+$TimeConfig) < $Now ) {
                return array('Error' => Error::OVER_TIME_ALLOW);  
            }            
        }
        //
        $BulletConfig = Common::getConfig("Noel_Bullet");                
        //
        $oUser = User::getById(Controller::$uId);   
        $zMoneyDiff = $oUser->ZMoney;   
        $moneyDiff = $oUser->Money;   
           
        $oStore = Store::getById(Controller::$uId);
        $RetBonus = array();                
        
        $strLogs = array();
        $arrBlood = array();
       
        foreach ($FireInfo as $Fire) 
        {       
       
            $BulletType = $Fire["BulletType"];
            $BulletId = $Fire["BulletId"];
            $BulletNum = intval($Fire["Num"]);
            $IsG = intval($Fire["IsG"]);
            
            if($BulletNum <=0 ){
                return array('Error' => Error::PARAM);  
            }    
            
            $Blood = $BulletConfig[$BulletType][$BulletId]["Blood"];            
            if($BulletType == BulletType::Bullet && $BulletId == 2) {
                $BulletNum = $BulletNum/3;
            }
            
            $strLine = array('BulletType'=>$BulletType, 'BulletId'=>$BulletId, 'Num'=>$BulletNum,'ZMoneyChange'=>0,'MoneyChange'=>0);
            
            // update Bullet in store            
            switch($BulletType)
            {    
                
                case BulletType::Bullet:                    
                    
                    if($IsG) {
                        $cost = $BulletNum*intval($BulletConfig[$BulletType][$BulletId]["ZMoney"]);
                        if($cost <= 0) {
                            return array('Error' => Error::NOT_LOAD_CONFIG); 
                        }
                        $info = '1:'.$BulletType.':'.$BulletNum;                        
                        if (!$oUser->addZingXu(-$cost,$info)) {
                            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                        }          
                        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff; 
                        $strLine['ZMoneyChange'] = $zMoneyDiff;                                                       
                        array_push($strLogs, $strLine);
                        $zMoneyDiff = $oUser->ZMoney;                                      
                        break;                        
                    } else {
                        if(!$oStore->useEventItem(EventType::Noel, $BulletType, $BulletId, $BulletNum) ){
                            return array('Error' => Error::NOT_ENOUGH_CONDITION); 
                        }        
                        array_push($strLogs, $strLine);                                            
                    }
                    break;                 
                case BulletType::BulletGold:
                    /*
                    $cost = $BulletNum*intval($BulletConfig[$BulletType][$BulletId]["Money"]);
                    if (!$oUser->addMoney(-$cost,'')) {
                        return array('Error' => Error::NOT_ENOUGH_MONEY);            
                    }                  
                    $moneyDiff = $oUser->Money - $moneyDiff; 
                    $strLine['MoneyChange'] = $moneyDiff;                                                       
                    array_push($strLogs, $strLine);
                    $zMoneyDiff = $oUser->ZMoney;                                      
                    */
                    if(!$oStore->useEventItem(EventType::Noel, $BulletType, $BulletId, $BulletNum) ){
                        return array('Error' => Error::NOT_ENOUGH_CONDITION); 
                    }        
                    array_push($strLogs, $strLine);                    
                    break;            
                case BulletType::Bomb:
                case BulletType::RainIce:
                    $cost = $BulletNum*intval($BulletConfig[$BulletType][$BulletId]["ZMoney"]);
                    if($cost <= 0) {
                        return array('Error' => Error::NOT_LOAD_CONFIG); 
                    }                
                    $info = '1:'.$BulletType.':'.$BulletNum;
                    if (!$oUser->addZingXu(-$cost,$info)) {
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                    }                   
                    $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff; 
                    $strLine['ZMoneyChange'] = $zMoneyDiff;                                                       
                    array_push($strLogs, $strLine);
                    $zMoneyDiff = $oUser->ZMoney;                                      
                    break;                                                 
                 default:
                    return array('Error' => Error::TYPE_INVALID);
                    break;       
            }       
            
            // update Fishs
            $FishList = $BoardGame["FishList"];                        
            $Fishs = $Fire["Fishs"];                        
            
            if(is_array($Fishs) && count($Fishs)>0) {
                //
                $coe = 1;
                if($BulletType == BulletType::Bullet) {
                    if($BulletId ==2) {
                        $coe = 3;    
                    } else if($BulletId ==3) {
                        $coe = 5;    
                    }                
                } else if($BulletType == BulletType::Bomb || $BulletType == BulletType::RainIce ) {
                    $coe = 100;
                }
                $NumFishMax = $coe*$BulletNum;
                $NumFish = 0;
                                
                foreach($Fishs as $Fish) {
                    $Id = $Fish["Id"];
                    $Num = $Fish["Num"];
                    $FishType = $FishList[$Id]["FishType"];
                    $FishId = $FishList[$Id]["FishId"];
                    
                    // check exist and Num valid
                    $NumFish = $NumFish + $Num;   
                    
                    if($NumFish > $NumFishMax) {
                        return array('Error' => Error::OVER_LIMIT);
                    }
                    //
                    if($BulletType == 'Bullet' && $BulletId==2) {
                        if($Num > 3*$BulletNum ) {
                            return array('Error' => Error::TYPE_INVALID);
                        }                                                                         
                    } else {
                        if($Num > $BulletNum ) {
                            return array('Error' =>Error::TYPE_INVALID);
                        }                         
                        
                    }                                        
                    // get current Blood of Fish 
                    $FishBlood = $oNoel->getBlood($Id);
                    // Fish is die can not fire 
                    $IsDie = false;
                    if($FishBlood <=0) {
                        $IsDie = true;
                    }
                    // update blood to fish
                    
                    if($Num*$Blood >= $FishBlood) { // Fish die
                        $oNoel->removeFish($Id);
                        // get Bonus                        
                        if($IsDie == false) {
                            $FishGift = $this->getFishBonus($FishType,$FishId, $FishList[$Id]["BonusIndex"]);                        
                            $RetBonus = array_merge($RetBonus,$FishGift);
                            array_push($arrBlood,array($Id,-1));                            
                        }                        
                    } else {
                        $FishBlood = $FishBlood -$Num*$Blood; 
                        $oNoel->setBlood($Id, $FishBlood);
                        array_push($arrBlood,array($Id,$Num*$Blood));
                    }                        
                                 
                }     
                
            }                        
        }  // End of foreach ($FireInfo as $Fire) {         

        // check is pass board
        $NumFishDie = $oNoel->getNumFishDie();
        $NoelBoardConfig = Common::getConfig("Noel_BoardConfig",$BoardId);
        $NumFishRequire = $NoelBoardConfig["NumFishRequire"];
        
        if($BoardId < 4 && $NumFishDie >= $NumFishRequire) {
            $oNoel->setIsPassBoard();        
        }                
        $IsPassBoard = $oNoel->getIsPassBoard();
        if($BoardId == 4 && $IsPassBoard== true) {
            $oEvent = Event::getById(Controller::$uId);            
            $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"] = $Now;            
            $oEvent->save();            
        }
        
        if($BoardId == 5 && $IsPassBoard== true) {
            $oEvent = Event::getById(Controller::$uId);            
            $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = -1;            
            $oEvent->save();                        
        }
        
        // add gifts in store
        
        if($BoardId == 5) {
          $RetBonus = Common::addsaveGiftConfig($RetBonus,'',SourceEquipment::EVENT);  
        } else {            
            $RetBonusBefore = $oNoel->getRetBonus();            
            
            $RetBonusCurrent = Common::addsaveGiftConfig($RetBonus,'',SourceEquipment::EVENT,false);
            
            if(is_array($RetBonusBefore)) {                
                $NormalItems = array_merge($RetBonusBefore["Normal"], $RetBonusCurrent["Normal"]);                
                $NormalItems = Common::groupItems($NormalItems);
                $EquipItems = array_merge($RetBonusBefore["Equipment"], $RetBonusCurrent["Equipment"]);                                
                $RetBonus = array('Normal' => $NormalItems, 'Equipment' => $EquipItems);                                                 
            } else {
                $RetBonus = $RetBonusCurrent;
            }            
            
            $oNoel->setRetBonus($RetBonus);
            if($IsPassBoard== true) {
                Common::addsaveGifted($RetBonus);
                $oNoel->setRetBonus(array('Normal'=>array(),'Equipment'=>array()));
            }             
        }                
                
        $oStore->save();
        $oNoel->save();
        $oUser->save();
        
        
        if( is_array($strLogs) && count($strLogs)>0 ){            
            foreach($strLogs as $strLine){
                if($strLine['ZMoneyChange'] !=0 || $strLine['MoneyChange'] !=0 ) {
                    Zf_log::write_act_log(Controller::$uId,0,23,'fireGun',$strLine['MoneyChange'],$strLine['ZMoneyChange'],$strLine['BulletType'], $strLine['BulletId'], $BoardId, $NumFishDie, $strLine['Num']);            
                } else {
                    Zf_log::write_act_log(Controller::$uId,0,20,'fireGunNormal',0,0,$strLine['BulletType'], $strLine['BulletId'], $BoardId, $NumFishDie, $strLine['Num']);            
                }                
            }            
        }                
        //---                                
        $runArr['IsPassBoard'] = $IsPassBoard;        
        $runArr['Error'] = Error::SUCCESS;   
        $runArr['arrBlood'] = $arrBlood;           
        $runArr['RetBonus'] = $RetBonus;
        $MaxKey = $RequestKey[0];        
        foreach($RequestKey as $Key){            
            if($MaxKey < $Key) $MaxKey = $Key;
        }        
        $runArr['MaxKey'] = $MaxKey;               
        $runArr['RequestKey'] = $oNoel->getRequestKey();               
        return $runArr;
    }
    
    public function receiveBonus(){
        // check time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);                  
        $oUser = User::getById(Controller::$uId);
        $oNoel = Noel::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId);  
        $oEvent->updateTimeFireFish();
        $StartTime = $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"];
        $RetBonus = $oNoel->getRetBonus();
        if($StartTime == -1) {
            $oNoel->setRetBonus(array());
            $oNoel->save();
            return array('Error' => Error::OVER_TIME_ALLOW);
        }        
        $RetBonus = Common::addsaveGiftConfig($RetBonus,'',SourceEquipment::EVENT);
        $oNoel->setRetBonus(array());
        $oNoel->save();
        return $RetBonus;        
                            
    }

    //public function getFishBonus($params) {
        //$FishType = $params["FishType"];       
        //$FishId = $params["FishId"];         
        //$BonusIndex = 2;        
        
    public function getFishBonus($FishType,$FishId,$BonusIndex) {
        $Gifts = Common::getConfig("Noel_Bonus","Fish",$FishType);
        $Gifts = $Gifts[$FishId];
        $Arr_Gift = array();
        foreach($Gifts as $Gift) {                                                      
            $Arr_Gift[] = $Gift;                                            
        }        
        $NoelGift = Common::getConfig("Noel_Bonus","NoelItem",$FishType);
        $NoelGift = $NoelGift[$FishId];
        if($FishType != 'FishBoss' && $FishType != 'FishFast' ) {            
            $Gift = $NoelGift[$BonusIndex];   
            unset($Gift["Rate"]);  
            if($Gift['ItemType'] != 'None' && $BonusIndex>0) {
                $Arr_Gift[] = $Gift;    
            }                       
        } else {
            foreach($NoelGift as $Gift) {
                unset($Gift["Rate"]);             
                $Arr_Gift[] = $Gift;                            
            }
        }
        
        
        return $Arr_Gift; 
    }
    
    /**
    *   Written by: thedg25
    *   Date: 2012/12/4
    *   Function Name: noelExchangeGift 
    *   Desc: exchange gifts from Item Noel received when shoot fish
    */
    public function noelExchangeGift($params) {
        // check time event
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);
        $NoelItemType = $params["ItemType"];
        $NoelItemId = intval($params["ItemId"]);
        $Num = intval($params["Num"]);
        $Index = intval($params["Index"]);
        // check param valid 
        if(empty($NoelItemType) || $NoelItemId <= 0 || $Num <=0 || $Index <=0 ) {
             return array('Error' => Error::TYPE_INVALID);
        }
        $oStore = Store::getById(Controller::$uId);
        // check enought Item
        if(!$oStore->useEventItem(EventType::Noel, $NoelItemType, $NoelItemId, $Num) ){
            return array('Error' => Error::NOT_ENOUGH_ITEM); 
        }                        
        //
        $RetBonus = array();
        $exchangeConf = Common::getConfig('Noel_Bonus', 'NoelGift', $NoelItemId);
        
        $BonusConf = $exchangeConf[$Index];
        
        $giftConf =array();
        foreach($BonusConf as &$bonus)
        {             
            $bonus['Num'] = $bonus['Num']*$Num;
            $giftConf[] = $bonus;
        }                
        $RetBonus = Common::addsaveGiftConfig($giftConf, "", SourceEquipment::EVENT);
        
        $oStore->save();
        $LastNum = $oStore->getEventItem(EventType::Noel,$NoelItemType,$NoelItemId);
        Zf_log::write_act_log(Controller::$uId,0,20,'noelExchangeGift',0, 0, $NoelItemType, $NoelItemId, $Index, 0, $Num);            
                        
        $runArr['Bonus'] = $RetBonus;
        $runArr['Error'] = Error::SUCCESS;
        return $runArr;
    }
        
     
     // cham soc cay hoa trong event Noel
    public function careFlower()
    {
        $oUser = User::getById(Controller::$uId);
        
        // kiem tra xem event nay co ton tai hay ko ?
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error' => Error::NO_REGIS);
        if(!Event::checkEventCondition(EventType::Noel))
            return array('Error' => Error::ARRAY_NULL);
            
        // kiem tra xem da du dieu kien cham soc hay chua ?
        if(!$oEvent->e_checkCareFlower())
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }
        
        // create gift for user
        $level = $oEvent->EventList[EventType::Noel]['Level'];
        $boxconf = Common::getConfig('Noel_Tree',$level);
        
        $arr['Bonus'] = array();
        $arr['Bonus'] = $boxconf['Bonus'] ;
        $oUser->saveBonus($arr['Bonus']);  
        
        // luckybonus
        $arr['LuckyBonus'] = array();
        foreach($boxconf['Flower'] as $id =>$gift )
        {
            $rand = mt_rand(1,100);
            if($rand <= $gift['Rate'] )
            {
                $arr['LuckyBonus'][$id] = $gift ;
                if(is_array($gift['Num']))
                {
                    $idd = array_rand($gift['Num'],1);
                    $arr['LuckyBonus'][$id]['Num'] = $gift['Num'][$idd] ;
                } 
            }
        }
        $oUser->saveBonus($arr['LuckyBonus']);
        
        // update level of Tree and last  care Time 
        $oEvent->e_updateLevelFlower();

        $oEvent->save();
        $oUser->save();
        
        //return
        $arr['Error'] = Error::SUCCESS ;
        
        return $arr ;
        
    }
      
    public function speedUp()
    {
        $oUser = User::getById(Controller::$uId);
        
        // kiem tra xem event nay co ton tai hay ko ?
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error' => Error::NO_REGIS);
        if(!Event::checkEventCondition(EventType::Noel))
            return array('Error' => Error::ARRAY_NULL);
        
        // kiem tra dieu kien tang toc
        if(!$oEvent->e_checkSpeedUp())
        {
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        }  
        
        // kiem tra tien 
        $level = $oEvent->EventList[EventType::Noel]['Level'];
        $ZMoney = Common::getConfig('Noel_Tree',$level,'ZMoney');
        $info = '1:SpeedUp:1' ;
        if(!$oUser->addZingXu(-$ZMoney,$info))
             return array('Error' => Error::NOT_ENOUGH_ZINGXU);       
                
        $oEvent->e_updateSpeedUp();
        $oEvent->save();
        $oUser->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'SpeedUp',0,-$ZMoney,$level);
        
        return array('Error' => Error::SUCCESS);       
    }
    
    public function fastComplete($params) {
        // check time event 
        if(!Event::checkEventCondition(EventType::Noel))
            return Common::returnError(Error::EVENT_EXPIRED);                          
        $Type = $params["Type"];
        $Now = $_SERVER['REQUEST_TIME']; 
        
        $NoelBonus = Common::getConfig("Noel_Bonus");
        if($Type == "N") {
            $NoelBonus = $NoelBonus["NoelAutoN"];        
        } else if($Type == "S"){
            $NoelBonus = $NoelBonus["NoelAutoS"];
        } else {
            array('Error' => Error::PARAM);       
        }
        $oEvent = Event::getById(Controller::$uId);          
        
        $StartTime = $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"];
        
        if($StartTime ==-1){
            return array('Error' => Error::WAIT_ENOUGHT_TIME);  
        }        

        $cost = $NoelBonus["Price"]["ZMoney"];        
        $oUser = User::getById(Controller::$uId);
        $zMoneyDiff = $oUser->ZMoney;   
        $info = '1:'."fastComplete_" . $Type . ':1';
        if (!$oUser->addZingXu(-$cost,$info))    
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);

        $oNoel = Noel::getById(Controller::$uId);
        
        $Gift = $NoelBonus["Gift"];
        
        $RetBonus = Common::addsaveGiftConfig($Gift,'',SourceEquipment::EVENT);

        // update Time
        //$TimeConfig = Common::getConfig("Noel_BoardConfig",5);
        //$TimeConfig = intval($TimeConfig["Time"]);
        
        
        $oEvent->EventList[EventType::Noel]["FireFish"]["StartTime"] = -1;
        $oEvent->EventList[EventType::Noel]["FireFish"]["FinishTime"] = $Now;// - $TimeConfig-2;
        //$oEvent->EventList[EventType::Noel]["FireFish"]["NumPlay"] += 1;
        
        $oNoel->setCurrentBoardId(0);
        
        $oEvent->save();
        $oNoel->save();
        $oUser->save();
        
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        if($zMoneyDiff !=0){
            Zf_log::write_act_log(Controller::$uId,0,23,'fastComplete', 0, $zMoneyDiff, "fastComplete_" . $Type, 0, 0,0, 1);    
        }        

        $arr['Error'] = Error::SUCCESS;
        $arr['RetBonus'] = $RetBonus;        
        return $arr ;        
    }
    /**
    * END OF EVENT NOEL 2012
    */
    
     // mua ban cac loai Item trong cac Event
    public function buyItemInEvent($param)
    {
        $Item = $param['Item'];

        if (empty($Item))
        {
            return array('Error' => Error :: PARAM) ;
        }

        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS);
        }

        $oStore = Store::getById(Controller::$uId);

        // luu thong so cu~
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        
        if(!Event::checkEventCondition($Item['Event']))
        {
             return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        // chan so luong mua max Item cua event kem
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oEvent))
             return array('Error' => Error :: OBJECT_NULL) ;   
        if($Item['ItemType']== Type::IceCreamItem)
        {
            if(!$oEvent->ice_checkMaxItemCanBuy(intval($Item['ItemId']),1))
                return array('Error' => Error :: OVER_NUMBER); 
        }
        
        if(in_array($Item['ItemType'],array('Event_8_3_Flower'),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::BirthDayItem),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::IceCreamItem),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::Island_Item),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else
        {
            return array('Error' => Error :: TYPE_INVALID) ;   
        }

        if(!is_array($DetailConfig))
        {
            return  array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }

        // kiem tra loai Unlock
        $UnlockType = $DetailConfig['UnlockType'];
        if($UnlockType == 5 || $UnlockType== 6)
        {
        return array('Error' => Error ::TYPE_INVALID ) ;
        }

        // kiem tra tien va level cua nguoi choi
        if($oUser->Level < $DetailConfig['LevelRequire'])
        {
            return  array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
        }

        if($Item['PriceType'] != 'Money')
        {
            $info = $Item['ItemId'].':'.$Item['ItemType'].':1' ;
            if (!$oUser->addZingXu(-$DetailConfig['ZMoney'],$info))
                return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
            // cong diem kinh nghiem
            if(!empty($DetailConfig['Exp']))
                $oUser->addExp($DetailConfig['Exp']);
        }
        else
        {
            if(!$oUser->addMoney(-$DetailConfig['Money'],'buyItemInEvent'))
            {
                return  array('Error' => Error :: NOT_ENOUGH_MONEY) ;
            }
        }

        // cac loai khac thi cat het vao trong kho
        $oStore->addEventItem($Item['Event'],$Item['ItemType'], $Item['ItemId'], 1);       
        
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[$Item['ItemType']]))
        {
            $TypeItemId = $conf_log[$Item['ItemType']];
        }
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;

        Zf_log::write_act_log(Controller::$uId,0,23,'BuyInEvent',$moneyDiff,$zMoneyDiff,$TypeItemId, $Item['ItemId'], 0,0,1);

        $oUser->save();
        $oStore->save();  
        $oEvent->save();
        $arr_result = array();
        $arr_result['Exp'] = $oUser->Exp ;
        $arr_result['Error'] = Error :: SUCCESS ;

        return $arr_result;
    } 
     // mua ban cac loai Item trong cac Event  bang kim cuong
    public function buyItemWithDiamond($param)
    {
        $Item = $param['Item'];

        if (empty($Item))
        {
            return array('Error' => Error :: PARAM) ;
        }

        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS);
        }

        $oStore = Store::getById(Controller::$uId);

        // luu thong so cu~
        $DiamondDiff = $oUser->Diamond;
        
        if(!Event::checkEventCondition($Item['Event']))
        {
             return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oEvent))
             return array('Error' => Error :: OBJECT_NULL) ;   
        if($Item['ItemType']== Type::IceCreamItem)
        {
            if(!$oEvent->ice_checkMaxItemCanBuy(intval($Item['ItemId']),1))
                return array('Error' => Error :: OVER_NUMBER); 
        }
            
        
        if(in_array($Item['ItemType'],array('Event_8_3_Flower'),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::BirthDayItem),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::IceCreamItem),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else if(in_array($Item['ItemType'],array(Type::Island_Item),true))
        {
            $OtherConfig = & Common::getConfig($Item['ItemType']);
            $DetailConfig = $OtherConfig[$Item['ItemId']];
        }
        else
        {
            return array('Error' => Error :: TYPE_INVALID) ;   
        }

        if(!is_array($DetailConfig))
        {
            return  array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }

        // kiem tra loai Unlock
        $UnlockType = $DetailConfig['UnlockType'];
        if($UnlockType == 5 || $UnlockType== 6)
        {
            return array('Error' => Error ::TYPE_INVALID ) ;
        }

        // kiem tra tien va level cua nguoi choi
        if($oUser->Level < $DetailConfig['LevelRequire'])
        {
            return  array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
        }
     
        if (!$oUser->addDiamond(-$DetailConfig['Diamond'],DiamondLog::buyItemWithDiamond))
            return  array('Error' => Error :: NOT_ENOUGH_DIAMOND);
        

        // cac loai khac thi cat het vao trong kho
        $oStore->addEventItem($Item['Event'],$Item['ItemType'], $Item['ItemId'], 1);       
        
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[$Item['ItemType']]))
        {
            $TypeItemId = $conf_log[$Item['ItemType']];
        }
        $DiamondDiff = $oUser->Diamond - $DiamondDiff; 

        Zf_log::write_act_log(Controller::$uId,0,23,'BuyInEvent',0,0,$TypeItemId, $Item['ItemId'],$DiamondDiff,$oUser->Diamond,1);

        $oUser->save();
        $oStore->save(); 
        $oEvent->save() ; 
        $arr_result = array();
        $arr_result['Error'] = Error :: SUCCESS ;

        return $arr_result;
    }
    
    
    
    
      
   Public function is_JoinIsland()
   {
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS);
        }

        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }

        $oEvent = Event::getById(Controller::$uId);
        if(!isset($oEvent->EventList[EventType::TreasureIsland]))
        {
            return array('Error' => Error :: OBJECT_NULL);   
        }

        // kiem tra xem co phai la lan dau tien trong ngay ko
        $LastDay = date('Ymd',$oEvent->EventList[EventType::TreasureIsland]['LastJoinTime']) ;
        $NowDay = date('Ymd',$_SERVER['REQUEST_TIME']);
        
        $result = array();
        
        if($NowDay != $LastDay) 
        {
            Debug::log('ngay moi');
            if(!empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
            {
                // qua an ui
                $result['RemainGift'] = $oEvent->island_GetRemainGift();
            }
            // xoa thong tim map cu va tao map moi 
            $oEvent->island_ResetMap();
            $Map = $oEvent->island_JoinIsland();
        }
        else
        {
            if(!empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
            {
                return array('Error' => Error :: NOT_ACTION_MORE);    
            }
            
            // check so lan vao trong ngay 
            $conf_param = Common::getParam('TreasureIsland');
            if($oEvent->EventList[EventType::TreasureIsland]['JoinNum'] >= $conf_param['MaxJoinNum'])
                return array('Error' => Error::OVER_NUMBER);
            
            // tao map moi 
            $Map = $oEvent->island_JoinIsland();
        }
        
        if($Map === false)
            return array('Error' => Error::ACTION_NOT_AVAILABLE);

        if(!empty($result['RemainGift']))
        {
            $oUser->saveBonus($result['RemainGift']);
            $oUser->save();
        }
        $result['Error'] = Error::SUCCESS;
        $result['Map'] = $Map;
        //$result['GiftMap'] = $oEvent->HideParam;
        $oEvent->save();
        
        // log 
        Zf_log::write_act_log(Controller::$uId,0,20,'is_JoinIsland',0,0,$oEvent->EventList[EventType::TreasureIsland]['JoinNum']);
        return $result ;
   }
   
   public function is_Dig($param)
   {
        $H = $param['H'];
        $C = $param['C'];
        if($H < 1 || $H > 8 || $C < 1 || $C > 8)
            return array('Error' => Error::PARAM);
            
        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        $oEvent = Event::getById(Controller::$uId);
        // kiem tra xem map co ton tai hay ko
        if(empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
        {
            return array('Error' => Error ::ARRAY_NULL);   
        } 
        
        // kiem tra xem toa do dao da dao chua ?
        $status = $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] ;
        
        if($status <= 0)
        {
            return array('Error' => Error ::CELL_ERROR);   
        }
        
        // kiem tra so xeng can dao
        $conf = Common::getConfig('Island_StateMap');
        if(empty($conf[$status]))
            return array('Error' => Error ::NOT_LOAD_CONFIG);  
        
        $numRequire = intval($conf[$status]['ShovelRequire']);
        
        $oStore = Store::getById(Controller::$uId);
        
        if(!$oStore->useEventItem(EventType::TreasureIsland,Type::Island_Item,15,$numRequire))
        {
            return array('Error' => Error ::NOT_ENOUGH_ITEM);  
        }
        
        $result['GiftId'] = 0 ;
        if($status == LandState::LAND) // dat binh thuong
        {
            $result['GiftId'] = $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap'][$H][$C];
            $oEvent->EventList[EventType::TreasureIsland]['TempGift'][$H][$C] = $result['GiftId'];     
            $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] = LandState::DIGED;
        }
        else
        {
            $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] = LandState::LAND;
        }
        
        $oEvent->save();
        $oStore->save();
        $result['Error'] = Error::SUCCESS ;
        
        Zf_log::write_act_log(Controller::$uId,0,20,'is_Dig',0,0,$H,$C,$oStore->EventItem[EventType::TreasureIsland][Type::Island_Item][15]);
        
        return $result ;
        
       
   }
   
   // nhat qua or mo ruong bac ruong vang,
   public function is_CollectGift($param)
   {
        $H = $param['H'];
        $C = $param['C'];
        
        if($H < 1 || $H > 8 || $C < 1 || $C > 8)
            return array('Error' => Error::PARAM);
            
        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        $oEvent = Event::getById(Controller::$uId);
        // kiem tra xem map co ton tai hay ko
        if(empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
        {
            return array('Error' => Error ::ARRAY_NULL);   
        } 
        
        // kiem tra xem trang thai o dat co phai da dao hay ko
        if($oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] != LandState::DIGED)
        {
            return array('Error' => Error ::CELL_ERROR); 
        }
        $giftId = $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap'][$H][$C];
        
        $conf_giftMap = Common::getConfig('Island_GiftMap',$giftId);
        if(empty($conf_giftMap))
            return array('Error' => Error ::NOT_LOAD_CONFIG); 
        
        $oStore = Store::getById(Controller::$uId);
        
        $result = array();
        $result['Gift'] = array();
        $result['ExitGift'] = array();
        
        if($conf_giftMap['ItemType']== Type::Island_Item && $conf_giftMap['ItemId']== 13 ) // thoat khoi map
        {
            return array('Error' => Error ::CELL_ERROR);  
        }
        else if($conf_giftMap['ItemType']== Type::Island_Item && ($conf_giftMap['ItemId']== 10 || $conf_giftMap['ItemId']== 11 || $conf_giftMap['ItemId']== 12)) // cac Item dac biet
        {
            return array('Error' => Error ::CELL_ERROR);  
        }
        else
        {
        
            // ruong vang , ruong bac
            if($conf_giftMap['ItemType']== Type::Island_Item && ($conf_giftMap['ItemId']== 3 ||$conf_giftMap['ItemId'] == 4))
            {
                // kiem tra so luong chia khoa co du de mo ruong ko
                $conf_Treasure = Common::getConfig('IsLand_Treasure',$conf_giftMap['ItemId']);
                            
                if(!$oStore->useEventItem(EventType::TreasureIsland,Type::Island_Item,$conf_Treasure['KeyRequire']['ItemId'],$conf_Treasure['KeyRequire']['Num']))
                {
                    return array('Error' => Error ::NOT_ENOUGH_ITEM);  
                }
                
                $rand_Gift = array() ;
                foreach($conf_Treasure['Gift'] as $index1 => $arrGift)
                {
                    if(empty($arrGift))    
                        continue ;
                    if(rand(1,100)> $arrGift['Rate'])
                        continue ;
                    unset($arrGift['Rate']); 
                    $arrGift['Element'] = rand(1,5);
                    $result['Gift'][]= $arrGift ; 
                    
                }
            }
            else
            {
                unset($conf_giftMap['Rate']);
                $result['Gift'][] = $conf_giftMap ;
            }
            // luu qua vao temp trap
            //$oEvent->EventList[EventType::TreasureIsland]['Treasure'][]= $result['Gift'];
            $result['Gift'] = $oEvent->island_saveGiftTempTrap($result['Gift']);
            // chuyen trang thai o dat
            $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] = LandState::GOTGIFT ;
            // chuyen trang thai qua tren map
            $oEvent->EventList[EventType::TreasureIsland]['TempGift'][$H][$C] = 0;
        }
        
        $oEvent->save();
        $oStore->save();
        $result['Error']= Error::SUCCESS ;
        
        Zf_log::write_act_log(Controller::$uId,0,20,'is_CollectGift',0,0,$giftId);
        
        return $result;
   }
   
   // roi khoi dao 
   public function is_exitIsland()
   {
        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }

        $oEvent = Event::getById(Controller::$uId);
        // kiem tra xem map co ton tai hay ko
        if(empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
        {
            return array('Error' => Error ::ARRAY_NULL);   
        } 

        $num = 0 ;
        for($i = 1 ; $i <= 9 ;$i++)
        {
            for($j = 1 ; $j <= 9 ;$j++)
            {
                $status = $oEvent->EventList[EventType::TreasureIsland]['Map'][$i][$j] ;
                if( $status != LandState::GOTGIFT && $status != LandState::WATER )
                {
                    $num++;
                }
            }
        }
               
        $result['ExitGift'] = array();
        if($num == 0 ) // thoat khoi map
        {
            $result['ExitGift'] = $oEvent->island_saveGiftIntoStore($oEvent->EventList[EventType::TreasureIsland]['Treasure']);
            $oEvent->EventList[EventType::TreasureIsland]['Map']          = array();  // map
            $oEvent->EventList[EventType::TreasureIsland]['MapId']        = 0;  // map
            $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap']    = array();  // qua tren map
            $oEvent->EventList[EventType::TreasureIsland]['Treasure']     = array();
            $oEvent->EventList[EventType::TreasureIsland]['TempGift']     = array();
        }
        $result['Error']= Error::SUCCESS ; 
        $oEvent->save();   
        Zf_log::write_act_log(Controller::$uId,0,20,'is_exitIsland');     
        return $result ;
   }
   
   public function getInfoEvent($param)
   {
        $NameEvent = $param['NameEvent'];
        if(empty($NameEvent))
            return array('Error' => Error ::PARAM); 

        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oEvent))
            return array('Error' => Error :: OBJECT_NULL);
            
        $result['Event'] = $oEvent->EventList[$NameEvent] ;
        $result['HideParam'] = $oEvent->HideParam[$NameEvent] ;
        $result['Error'] = Error::SUCCESS ;
        return $result ;
       
   }
   
  public function is_GoodAndBad($param)
   {
        $H = $param['H'];
        $C = $param['C'];
        if($H < 1 || $H > 8 || $C < 1 || $C > 8)
            return array('Error' => Error::PARAM);
            
        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        $oEvent = Event::getById(Controller::$uId);
        // kiem tra xem map co ton tai hay ko
        if(empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
        {
            return array('Error' => Error ::ARRAY_NULL);   
        } 
        
        // kiem tra xem toa do dao da dao chua ?
        $status = $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] ;
        
        // kiem tra xem trang thai o dat co phai da dao hay ko
        if($status != LandState::DIGED)
        {
            return array('Error' => Error ::CELL_ERROR); 
        }
        
        $giftId = $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap'][$H][$C];
        
        $conf_giftMap = Common::getConfig('Island_GiftMap',$giftId);
        if(empty($conf_giftMap))
            return array('Error' => Error ::NOT_LOAD_CONFIG); 
            
                
        if($conf_giftMap['ItemType']!= Type::Island_Item ||($conf_giftMap['ItemId']!= 10 && $conf_giftMap['ItemId']!= 11 && $conf_giftMap['ItemId']!= 12)) // cac Item dac biet
        {
            return array('Error' => Error ::TYPE_INVALID);  
        }
        $Result = array();
        $Result['LuckyPosition'] = array();
        $Result['LostGift'] = array();
        $Result['Map'] = array();
        // thien than nho 
        if($conf_giftMap['ItemId']== 10)
        {
            $Result['LuckyPosition'] = $oEvent->island_FindTreasure(); // tim or tao 1 o duong vang
            
            if(!empty($Result['LuckyPosition']['H']))
            {
                $conf_Treasure = Common::getConfig('IsLand_Treasure',4);
                        
                $rand_Gift = array() ;
                foreach($conf_Treasure['Gift'] as $index1 => $arrGift)
                {
                    if(empty($arrGift))    
                        continue ;
                    $rand_Gift[$index1] = $arrGift['Rate'];
                }

                $Id_gift = Common::randomIndex($rand_Gift);
                $conf_Treasure['Gift'][$Id_gift]['Element'] = rand(1,5);
                $Result['LuckyGift'] = $conf_Treasure['Gift'][$Id_gift];          
                $Result['LuckyGift']['Rate'] = 100 ;
                
                // luu qua vao temp trap
                $Result['LuckyGift'] = $oEvent->island_saveGiftTempTrap(array($Result['LuckyGift']));
                
                //$oEvent->EventList[EventType::TreasureIsland]['Treasure'][]= $Result['LuckyGift'];
                // chuyen trang thai o dat
                $oEvent->EventList[EventType::TreasureIsland]['Map'][$Result['LuckyPosition']['H']][$Result['LuckyPosition']['C']] = LandState::GOTGIFT ;
            }
            
        }
        else if($conf_giftMap['ItemId']== 11) // mua da
        {
            $oEvent->island_RockRain();
        }
        else if($conf_giftMap['ItemId']== 12) // dua roi vao dau 
        {
            $Result['LostGift'] = $oEvent->island_Coconut();
        }
        // chuyen trang thai o dat
        $oEvent->EventList[EventType::TreasureIsland]['Map'][$H][$C] = LandState::GOTGIFT ;
        // chuyen trang thai qua tren map
        $oEvent->EventList[EventType::TreasureIsland]['TempGift'][$H][$C] = 0;
           
        if($conf_giftMap['ItemId']== 11) // mua da
        {
            $Result['Map'] = $oEvent->EventList[EventType::TreasureIsland]['Map'] ;   
        }
         
        $oEvent->save();
        $Result['Error'] = Error::SUCCESS ;
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'is_GoodAndBad',0,0,$giftId);
        
        return $Result; 
   }
   
   
   // tu dong dao map
   public function is_AutoDig($param)
   {
        $Id = intval($param['Id']);
        
        if($Id != 1 && $Id != 2)
        return array('Error' => Error ::PARAM);  

        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }

        $oEvent = Event::getById(Controller::$uId);
        // kiem tra xem map co ton tai hay ko
        if(empty($oEvent->EventList[EventType::TreasureIsland]['Map']))
        {
            return array('Error' => Error ::ARRAY_NULL);   
        } 
        $conf_gift = Common::getConfig('IsLand_AutoDig',$Id);
        if(empty($conf_gift))
            return array('Error' => Error ::NOT_LOAD_CONFIG);    
        // kiem tra tien 
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error' => Error ::NO_REGIS);  
        
        //
        $oldMoney = $oUser->Money;
        $oldZMoney = $oUser->ZMoney;
   
        $info = '1:is_AutoDig:'.$Id ;
        if (!$oUser->addZingXu(-$conf_gift['ZMoney'],$info))
            return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
        
        $result = array();
        $result['Gift'] = $oEvent->island_getGift($conf_gift['Gift']);
        // save gift
        //$this->saveGift($result['Gift']['SpecialGift']);
        //$this->saveGift($result['Gift']['NormalGift']);   
        $oEvent->island_saveGiftIntoStore($result['Gift']['NormalGift']);
        $oEvent->island_saveGiftIntoStore($result['Gift']['SpecialGift']);
        // reset map 
        $oEvent->EventList[EventType::TreasureIsland]['Map']          = array();  // map
        $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap']    = array();  // qua tren map
        $oEvent->EventList[EventType::TreasureIsland]['Treasure']     = array();  // qua nhan duoc tam thoi 
        $oEvent->EventList[EventType::TreasureIsland]['TempGift']     = array(); //qua tam thoi lam tren map

        $oEvent->save();
        $oUser->save();

        // log
        $diffMoney = $oUser->Money - $oldMoney;
        $diffZMoney = $oUser->ZMoney - $oldZMoney;
        Zf_log::write_act_log(Controller::$uId,0,23,'is_AutoDig',$diffMoney,$diffZMoney,$Id);
        
        $result['Error']= Error::SUCCESS ;
        return $result;
        
   }
   
    // tu doi huy chuong tai phu
   public function is_ChangeMedal($param)
   {
        $Id = intval($param['Id']);
        
        if($Id < 1 || $Id > 3)
            return array('Error' => Error ::PARAM);  
        
        $oEvent = Event::getById(Controller::$uId);

        $conf_gift = Common::getConfig('Island_GiftMedal',$Id);
        if(empty($conf_gift))
            return array('Error' => Error ::NOT_LOAD_CONFIG);    
        // kiem tra so luong huy chuong 
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        if(!is_object($oStore))
            return array('Error' => Error ::NO_REGIS);  
            
        if(!$oStore->useEventItem(EventType::TreasureIsland,Type::Island_Item,14,$conf_gift['MedalRequire']))
            return array('Error' => Error ::NOT_ENOUGH_ITEM);      
        
        $result = array();
        $result['Gift'] = $oEvent->island_getGift($conf_gift['Gift']);
        // save gift
        //$this->saveGift($result['Gift']['SpecialGift']);
        //$this->saveGift($result['Gift']['NormalGift']);       
        $oEvent->island_saveGiftIntoStore($result['Gift']['NormalGift']);
        $oEvent->island_saveGiftIntoStore($result['Gift']['SpecialGift']);
        $oEvent->save();
        $oUser->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'is_ChangeMedal',0,0,$Id,$oStore->EventItem[EventType::TreasureIsland][Type::Island_Item][14]);
        
        $result['Error']= Error::SUCCESS ;
        return $result;
        
   }
   
   
    // tu doi bo suu tap 
  /* public function is_ChangeCollection($param)
   {
        $Id = intval($param['Id']);
        
        if($Id < 1 || $Id > 3)
            return array('Error' => Error ::PARAM);  
            
        if(!Event::checkEventCondition(EventType::TreasureIsland))
        {
            return array('Error' => Error :: EVENT_EXPIRED) ;   
        }
        
        $oEvent = Event::getById(Controller::$uId);

        $conf_gift = Common::getConfig('IsLand_Collection',$Id);
        if(empty($conf_gift))
            return array('Error' => Error ::NOT_LOAD_CONFIG);    
        // kiem tra so luong bo suu tap
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error' => Error ::NO_REGIS);  
            
        foreach($conf_gift['Input'] as $index => $arr)
        {
            if(empty($arr))
                continue ;
            if(!$oStore->useEventItem(EventType::TreasureIsland,Type::Island_Item,$arr['ItemId'],$arr['Num']))
                return array('Error' => Error ::NOT_ENOUGH_ITEM);      
        }  
        
        $result = array();
        $result['Gift'] = $oEvent->island_getGift($conf_gift['Gift']);
        // save gift
        //$this->saveGift($result['Gift']['SpecialGift']);
        //$this->saveGift($result['Gift']['NormalGift']);       
        $oEvent->island_saveGiftIntoStore($result['Gift']['NormalGift']);
        $oEvent->island_saveGiftIntoStore($result['Gift']['SpecialGift']);  
        $oEvent->save();
        $oUser->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'is_ChangeCollection',0,0,$Id);
        
        $result['Error']= Error::SUCCESS ;
        return $result;
        
   }
         */
   private function saveGift($GiftList)
   {
        if(empty($GiftList)|| !is_array($GiftList))
            return false ;
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        foreach($GiftList as $index => $Gift)
        {
            if(is_object($Gift)) // object
            {
                if(SoldierEquipment::checkExist($Gift->Type))
                {
                    $oStore->addEquipment($Gift->Type, $Gift->Id,$Gift);
                    Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0,$Gift); 
                }
            }
            if(is_array($Gift)&&!empty($Gift))
            {
                $oUser->saveBonus(array($Gift));
            }
        }
        
        $oUser->save();
        $oStore->save();
       
   }
   
   public function hal_getInfo()
    {
        if(!Event::checkEventCondition(EventType::Halloween))
            return Common::returnError(Error::EXPIRED);
        $oEvent = Event::getById(Controller::$uId);
        if(!isset($oEvent->EventList[EventType::Halloween]))
           $oEvent->hal_init();
        $updateF = $oEvent->hal_updateFirstDate(); 
        if($updateF)
        {            
            if(!$updateF['unlock'])
            {
               $gotgift = false;
               $gift = array('Exp' => 10000);         
               $oUser = User::getById(Controller::$uId);
               $oUser->addExp(10000);
               $oUser->save();
            }            
            else
            {                
               $gotgift = true;
               $gift = array(); 
            }
            $genMap = $this->hal_genMap();
            $oEvent->hal_createNewMap($genMap['map'], $genMap['start']);            
        }
        else
        {            
            if($oEvent->hal_checkIsCreateNewMap())
            {                
               $genMap = $this->hal_genMap();
               $oEvent->hal_createNewMap($genMap['map'], $genMap['start']); 
            }
            else
            {
               if($oEvent->EventList[EventType::Halloween]['KiddingSTime'] > 0)
               {
                   $oEvent->hal_setKidding(0);
                   $oEvent->hal_kidded();
               }
               elseif(($oEvent->EventList[EventType::Halloween]['RemainPlayCount'] == 0) && ($oEvent->EventList[EventType::Halloween]['UnlockMap']))
                    return Common::returnError(Error::ACTION_NOT_AVAILABLE); 
            }
            $gotgift = true;
            $gift = array();   
        }
        
        $oEvent->save();
        
        $runArr['GotGiftEngage'] = $gotgift;
        $runArr['Gift'] = $gift;
        $runArr['Hal12'] = $oEvent->EventList[EventType::Halloween];
        $runArr['Error'] = Error::SUCCESS;
        
        return $runArr;  
    }
    
    public function hal_stepAStep($params)    
    {
        $X = $params['X'];
        $Y = $params['Y'];
        
        if(!Event::checkEventCondition(EventType::Halloween))
            return Common::returnError(Error::EXPIRED);
        $oEvent = Event::getById(Controller::$uId);
        $Step = $oEvent->hal_checkAvailableStep($X, $Y);
        if(!$Step)
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
        
        $oStore = Store::getById(Controller::$uId);
            
        $MapItemIdConf = Common::getConfig('Hal2012_MapItemId', $Step[1]);
        if($Step[1] != EventHal2012::ORD_CHEST)
        {
            $ItemType = $MapItemIdConf['ItemType'];
            $ItemId = $MapItemIdConf['ItemId'];
            if(($Step[1] == EventHal2012::GOD_CHEST) || ($Step[1] == EventHal2012::ENDMAP_STEP))
            {
               $ItemType = 'HalItem'; $ItemId = 13;
            }
            if(!(($Step[1] == EventHal2012::ENDMAP_STEP) && ($oEvent->EventList[EventType::Halloween]['HadKey'])))
                if(!$oStore->useEventItem(EventType::Halloween, $ItemType, $ItemId, 1))
                    return Common::returnError(Error::NOT_ENOUGH_ITEM);                    
        }
        
        $state = $oEvent->hal_changeStateItem($X, $Y);
        
        if(!$state)
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
            
        $regard = array();
        $ghostRequire = array();
        $gift = array();
        
        if($state['unlocked'])
        {
            $regard = $oEvent->hal_unlockMap();
            $regard['Normal'] = Common::groupItems($regard['Normal']);
            Common::addsaveGifted($regard);            
        }                
        elseif($state['changed'] == 0)
        {
            if(($Step[2] == EventHal2012::KIDDING_STATE))
            {
                $ghostRequires = Common::getConfig('Hal2012_GhostGift') ;
                $itemRate = array();
                foreach($ghostRequires as $itemId => $item)
                {
                    $itemRate[$itemId] = $item['Rate'];
                }
                $ghostRequire = Common::randomIndex($itemRate);
                $oEvent->hal_setKidding($_SERVER['REQUEST_TIME'], $ghostRequire);                                
                            
            }else
            {
                $GiftConf = Common::getConfig('Hal2012_GroupGift', $MapItemIdConf['GroupGiftId']);
                $keyGift = false;
                switch($MapItemIdConf['GroupGiftId'])
                {
                    case EventHal2012::ORD_GIFT:                    
                    case EventHal2012::RATE_GIFT:
                    case EventHal2012::GOD_GIFT:                    
                        $itemRate = array();
                        foreach($GiftConf as $itemId => $item)
                        {
                            $itemRate[$itemId] = $item['Rate'];
                        }
                        $itemId = Common::randomIndex($itemRate);
                        
                        $gift[$itemId] = $GiftConf[$itemId];
                        break;
                    case EventHal2012::ORD_CHEST:
                        if(!$oEvent->hal_haveKey())
                        {
                            $itemRate = array();
                            foreach($GiftConf as $itemId => $item)
                            {
                                $itemRate[$itemId] = $item['Rate'];
                            }
                            $itemId = Common::randomIndex($itemRate);
                            
                            $gift[$itemId] = $GiftConf[$itemId];
                        }
                        else
                        {
                            $gift = Common::getConfig('Hal2012_GroupGift', 13);
                            $keyGift = true;
                        }
                                                        
                        break;
                    case EventHal2012::GOD_CHEST:
                        $gift = $GiftConf;
                        break; 
                    default:
                        $gift = array();
                        break;
                }
                $gift = Common::addsaveGiftConfig($gift, "", SourceEquipment::EVENT, false); 
                if(!$keyGift)       // ko nem khoa vao trong kho
                {                    
                    $oEvent->hal_addRegard($gift, $oEvent);
                }                    
            } 
        }
                
        $oEvent->save();
        $oStore->save();
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Gift'] = $gift;
        $runArr['Reward'] = $regard;
        $runArr['GhostRequire'] = $ghostRequire;
        
        $oUser = User::getById(Controller::$uId); 
        $runArr['AutoId'] = $oUser->AutoId;                       
        
        return $runArr;
    }
    
    public function hal_buyItem($params)
    {
        if(!Event::checkEventCondition(EventType::Halloween))
            return Common::returnError(Error::EXPIRED);        
        
        $ItemType = $params['ItemType'];
        $ItemId = $params['ItemId'];
        $Num = $params['Num'];
        $PriceType = $params['PriceType'];
        
        if(!in_array($ItemType, array('BuyPack', 'BuyKey')) || !is_int($Num) || ($Num <= 0))
            return Common::returnError(Error::PARAM);
        $buyConf = Common::getConfig('Param', 'Halloween', $ItemType) ;
        $cost = $buyConf[$PriceType];    
        if(!isset($cost))
            return Common::returnError(Error::PARAM);
           
        $oUser = User::getById(Controller::$uId);
        switch($PriceType)
        {
            case Type::ZMoney:
                $info = '1:'.$ItemType.':'. $Num;
                if(!$oUser->addZingXu(-$cost*$Num,$info))
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                
                break;
/*            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::RebornLantern))                    
                return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;*/
            default:
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);                
        }
            
        $oStore = Store::getById(Controller::$uId);
        switch($ItemType)
        {
            case 'BuyPack':
                $PackConf = Common::getConfig('Hal2012_RatePackItem', 1);
                $Total = $buyConf['Total'];
                $itemRate = array();
                foreach($PackConf as $itemId => $item)
                {
                    $itemRate[$itemId] = $item['Rate'];
                }
                for($ax = 1; $ax <= $Num; $ax ++)
                    for($i = 1; $i <= $Total; $i++)
                    {
                        $itemId = Common::randomIndex($itemRate);
                        $item = $PackConf[$itemId];
                        $num = $Pack[$item['ItemId']];
                        $num = (empty($num)) ? 1 : ($num + 1);
                        $Pack[$item['ItemId']] = $num;
                    }
                
                $GotPack = array();
                foreach($Pack as $itemid => $num)
                {
                    $GotPack[] = array(
                        'ItemType' => 'HalItem',
                        'ItemId' => $itemid,
                        'Num' => $num
                    );
                    if(!$oStore->addEventItem(EventType::Halloween, 'HalItem', $itemid, $num))
                        return array('Error' => Error::ACTION_NOT_AVAILABLE);
                }                
                break;
            case 'BuyKey':
                if(!$oStore->addEventItem(EventType::Halloween, 'HalItem', 13, $Num))
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                break;
        }
        
        $oUser->save();
        $oStore->save();
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Pack'] = $GotPack;
        $runArr['ZMoney'] = $oUser->ZMoney;
        $runArr['Diamond'] = $oUser->Diamond;
        
        // log
         switch($PriceType)
           {
               case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'buyHalItem', 0, -$cost*$Num, $ItemType, $ItemId) ;
                    break;
           }
        //end log
        
        return $runArr;        
    }
    
    public function hal_speedupJoin($params)
    {   
        if(!Event::checkEventCondition(EventType::Halloween))
            return Common::returnError(Error::EXPIRED);
        
        $PriceType = $params['PriceType'];    
        
        $oEvent = Event::getById(Controller::$uId);
        if(!$oEvent->EventList[EventType::Halloween]['UnlockMap'])
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
        
        if(($oEvent->EventList[EventType::Halloween]['RemainPlayCount'] == 0))
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
            
        $cost = Common::getConfig('Param', 'Halloween', 'SpeedupJoin') ;
        $cost = $cost[$PriceType];
        if(!isset($cost))
            return Common::returnError(Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);    
        switch($PriceType)
        {
            case Type::ZMoney:
                $info = '1:SpeedupMap:1';
                if(!$oUser->addZingXu(-$cost,$info))
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                
                break;
/*            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::RebornLantern))                    
                return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;*/
            default:
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                
        }
        
        $genMap = $this->hal_genMap();
        $oEvent->hal_createNewMap($genMap['map'], $genMap['start']);
        
        $oUser->save();
        $oEvent->save();
        
        // log
        switch($PriceType)
           {
               case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'speedupJoinHalMap', 0, -$cost, 'SpeedupMap', 1) ;
                    break;
           }             
       //endlog
        
        return Common::returnError(Error::SUCCESS);              
    }
    
    public function hal_autoUnlockMap($params)
    {
        if(!Event::checkEventCondition(EventType::Halloween))
            return Common::returnError(Error::EXPIRED);
        
        $PriceType = $params['PriceType'];
        $TypeAuto  = $params['TypeAuto'];
        if(($TypeAuto != 1) && ($TypeAuto != 2))
            return Common::returnError(Error::PARAM);    
        
        $oEvent = Event::getById(Controller::$uId);
        if(($oEvent->EventList[EventType::Halloween]['RemainPlayCount'] == 0) && ($oEvent->EventList[EventType::Halloween]['UnlockMap']))
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
        $cost = Common::getConfig('Param', 'Halloween', 'AutoUnlock') ;
        $cost = $cost[$TypeAuto][$PriceType];
        if(!isset($cost))
            return Common::returnError(Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);    
        switch($PriceType)
        {
            case Type::ZMoney:
                $info = $TypeAuto.':AutoUnlockHalMap:1';
                if(!$oUser->addZingXu(-$cost,$info))
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                
                break;
/*            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::RebornLantern))                    
                return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;*/
            default:
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                
        }
        
        $oEvent->hal_unlockMap(false);
        $autoGiftConf = Common::getConfig('Hal2012_AutoMap', $TypeAuto);
        $giftConf = array();
        foreach($autoGiftConf as $gid => $groupgift)
        {
            foreach($groupgift as $gift)
            {
                $luck = rand(1,100);
                if($luck > $gift['Rate']) continue;
                if(is_array($gift['Num']))
                {
                    $index = array_rand($gift['Num'],1);
                    $gift['Num'] = $gift['Num'][$index] ;
                }
                unset($gift['Rate']) ;
                $giftConf[] = $gift;
            }
        }
        $gift = Common::addsaveGiftConfig($giftConf, "", SourceEquipment::EVENT);
               
        $oEvent->save();    
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Reward'] = $gift;
        
        // log
         switch($PriceType)
           {
               case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'AutoUnlockHalMap', 0, -$cost, 'AutoUnlockHalMap', 1) ;
                    break;
           }             
        //end log
        return $runArr;
    }
    
    public function hal_chooseKidding($params)
    {
        $Ans = $params['Ans'];
        if(($Ans != 1) && ($Ans != 2) && ($Ans != 3))
            return Common::returnError(Error::PARAM);
        $PriceType = $params['PriceType'];
        $oEvent = Event::getById(Controller::$uId);
        $freezeArr = array();
        if($Ans == 1)
        {
            $cost = Common::getConfig('Param', 'Halloween', 'MakeSweet') ;
            $cost = $cost[$PriceType];
            if(!isset($cost))
            return Common::returnError(Error::PARAM);
            
            $oUser = User::getById(Controller::$uId);    
            switch($PriceType)
            {
                case Type::ZMoney:
                    $info = '1:MakeSweetHal:1';
                    if(!$oUser->addZingXu(-$cost,$info))
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                    
                    break;
                default:
                    return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                    
            }
            $oEvent->hal_setKidding(0);
            $oUser->save();
        }
        elseif($Ans == 2)
        {
            $oEvent->hal_setKidding(0);
            $freezeArr = $oEvent->hal_kidded(); 
        }
        else
        {               
            $oStore = Store::getById(Controller::$uId);            
            $ghostRe = $oEvent->EventList[EventType::Halloween]['GhostRequire'];
            $ghostRequire =  Common::getConfig('Hal2012_GhostGift', $ghostRe) ;
            switch($ghostRequire['ItemType'])
            {
                case Type::Money:
                    $oUser = User::getById(Controller::$uId);
                    if(!$oUser->addMoney(-$ghostRequire['Num']))
                        return Common::returnError(Error::NOT_ENOUGH_MONEY);
                    $oUser->save();
                    break;
                    
                case 'HalItem':
                    if(!$oStore->useEventItem(EventType::Halloween, $ghostRequire['ItemType'], $ghostRequire['ItemId'], $ghostRequire['Num']))
                        return Common::returnError(Error::NOT_ENOUGH_ITEM);
                    break;
                default:
                    if( ! $oStore->useItem($ghostRequire['ItemType'], $ghostRequire['ItemId'], $ghostRequire['Num']))
                        return Common::returnError(Error::NOT_ENOUGH_ITEM);
                    break;
            }          
            
            $oEvent->hal_setKidding(0);
            $oStore->save();
        }

        $oEvent->save();
        
        if($Ans == 1)
        {
            // log
         switch($PriceType)
           {
               case Type::ZMoney:
                    Zf_log::write_act_log(Controller::$uId, 0, 23, 'MakeSweetHal', 0, -$cost, 'MakeSweetHal', 1) ;
                    break;
           }             
        //end log
        }
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['Freeze'] = $freezeArr;
        
        return $runArr;        
    }
    
    public function hal_exchangeMedal($param)
    {
        $Id = intval($param['Id']);
        // added check expire event
        $endEvent = Common::getConfig('Event', 'Hal2012', 'ExpireTime');
        $endExchange = $endEvent + 7*24*3600;
        if($_SERVER['REQUEST_TIME'] > $endExchange)
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
        
        if(($Id < 1) || ($Id > 3))
            return array('Error' => Error ::PARAM);  
        
        $oEvent = Event::getById(Controller::$uId);

        $conf_gift = Common::getConfig('Hal2012_GiftMedal',$Id);
        if(empty($conf_gift))
            return array('Error' => Error ::NOT_LOAD_CONFIG);    
        // check number medals        
        $oStore = Store::getById(Controller::$uId);
       
        if(!$oStore->useEventItem(EventType::Halloween, 'Medal',1, $conf_gift['MedalRequire']))
            return array('Error' => Error ::NOT_ENOUGH_ITEM);      
        // get gifts info and assign info return
        $gift = Common::addsaveGiftConfig($conf_gift['Gift'], '', SourceEquipment::EVENT);
        
        $oStore->save();        
        // update log
        Zf_log::write_act_log(Controller::$uId,0,20,'exchangeHalMedal',0,0,$Id,$oStore->EventItem[EventType::Halloween]['Medal'][1]);
        
        $runArr['Error']= Error::SUCCESS ;
        $runArr['Gift'] = $gift;
        return $runArr;
    }
        
    private function hal_genMapNormal()
    {
        $mapNormal = array();
        for($i=0; $i<10; $i++){
            if($i==0){ // first row
                for($j=0; $j<10; $j++){                
                    if($j==0){
                        $mapNormal[$i][$j] = rand(1,9);    
                    } else {
                        $array_index = array(1,2,3,4,5,6,7,8,9);
                        $array_index = array_values(array_diff($array_index,array($mapNormal[$i][$j-1])));
                        $mapNormal[$i][$j] = $array_index[array_rand($array_index)];                            
                    }                  
                }                      
            } else { // row n
                for($j=0; $j<10; $j++){
                    if($j==0){
                        $array_index = array(1,2,3,4,5,6,7,8,9);
                        $array_index = array_values(array_diff($array_index,array($mapNormal[$i-1][$j],$mapNormal[$i-1][$j+1])));
                        $mapNormal[$i][$j] = $array_index[array_rand($array_index)];                                                    
                    } else if($j==9){
                        $array_index = array(1,2,3,4,5,6,7,8,9);
                        $array_index = array_values(array_diff($array_index,array($mapNormal[$i-1][$j],$mapNormal[$i-1][$j-1],$mapNormal[$i][$j-1])));
                        $mapNormal[$i][$j] = $array_index[array_rand($array_index)];                                                                            
                    } else {
                        $array_index = array(1,2,3,4,5,6,7,8,9);
                        $array_index = array_values(array_diff($array_index,array($mapNormal[$i-1][$j],$mapNormal[$i-1][$j-1],$mapNormal[$i][$j-1],$mapNormal[$i-1][$j+1])));
                        $mapNormal[$i][$j] = $array_index[array_rand($array_index)];                                                                                                    
                    }
                }
            }
        }
        return $mapNormal;
    }
    
    private function hal_genMap()
    {
        $mapNormal = $this->hal_genMapNormal();        
        // get random map
        $mapId = rand(1,10); // after rand(1,20)
        $mapInfo = Common::getConfig('Hal2012_Map',$mapId);        
        $start = array(9,9); 
        if(empty($mapInfo))
            return array('Error' => Error ::NOT_LOAD_CONFIG); 
        // replace config map
        for($i=0; $i<10; $i++)
        { // loop rows
            for($j=0; $j<10; $j++) { //loop cols
                if(is_array($mapInfo[$i][$j])){
                    $cell = $mapInfo[$i][$j];
                    array_splice($cell,0,0,1);
                    if($cell[1]==1){ // assign from mapNormal
                        //array_push($cell);
                        $cell[1] = $mapNormal[$i][$j];                        
                    }                  
                    $mapInfo[$i][$j] = $cell;
                } else {
                    if ($mapInfo[$i][$j]==0){
                       $cell = array(0,$mapInfo[$i][$j],0);
                       $mapInfo[$i][$j] = $cell; 
                       $start = array($i,$j);                       
                    }
                     else {
                        $cell = array(1,$mapInfo[$i][$j],0);
                        $mapInfo[$i][$j] = $cell;         
                    }                    
                }
                
            }    
        }
        
        // gen Ghost
        $numGhost = rand(1,EventHal2012::MAX_GHOST);
        while(($count <= $numGhost))
        {
            $x = rand(0,9);
            $y = rand(0,9);
            if(($mapInfo[$x][$y][0] == 1) && ($mapInfo[$x][$y][1] != 31) && ($mapInfo[$x][$y][1] != 15) && ($mapInfo[$x][$y][1] != 16))
            {
                $mapInfo[$x][$y][2] = 1;
                $count ++;
            }
        }
       
       return array('map'=>$mapInfo,'start'=>$start);
    }

   
   
    
      
    
    
}   
?>