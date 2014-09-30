<?php
class SmashEggService {
    // getSmashEgg info 
    public function getSmashEgg($params){
        $FriendId = intval($params["FriendId"]);        
        $this->convertEquipToQuartz();
        $this->syncSlot();
        $UserId = Controller::$uId;
        if($FriendId>0) {
           $UserId = $FriendId; 
        }
        $oSmashEgg = SmashEgg::getById($UserId);
        $ret = array();        
        $ret['SmashEgg'] = $oSmashEgg->getSmashEggInfo();         
        $ret['NumView6Star'] = intval(DataRunTime::get('NumView6Star',true));        
        $ret['Error'] = Error::SUCCESS;        
        return $ret;                                    
    }
    // buyHammer 
    
    public function buyHammer($param){
        $HammerType =  $param["HammerType"];
        $HammerId =  intval($param["HammerId"]);
        $Num = intval($param["Num"]);
        $PriceType = "ZMoney";
        
        if(empty($HammerType) || $HammerId !=1 ){
            return array('Error' => Error::PARAM);
        }
        
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        // get coin before buy
        $zMoneyDiff = $oUser->ZMoney;
        
        // get price info 
        $priceInfo = Common::getConfig("SmashEgg_Hammer", $HammerType, $HammerId);
        
        if(!isset($priceInfo[$PriceType]) || intval($priceInfo[$PriceType])<=0 ) {
            return array('Error' => Error ::NOT_LOAD_CONFIG);
        }
        $cost = intval($priceInfo[$PriceType])*$Num;
        if($cost <=0 ) {
            return array('Error' => Error::PARAM);
        }
        
        switch($PriceType)
        {
            case Type::ZMoney:
                $info = '1:'.$HammerType.':'.$Num;
                if (!$oUser->addZingXu(-$cost,$info))    
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                break;
            
            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::buyHammer))                    
                    return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;
            
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        // Update store
        $FristNumHammerInStore = $oSmashEgg->getHammer($HammerType,$HammerId);        
        $oSmashEgg->addHammer($HammerType,$HammerId,$Num);

        if($HammerType =="HammerPurple") {
            $IsPurpleHammer = $oSmashEgg->getIsPurpleHammer();            
            if($IsPurpleHammer <=0){
                $oSmashEgg->setIsPurpleHammer(1);
                $EggType = "PurpleEgg";
                $arrBonus = Common::getConfig("SmashEgg_Bonus", $EggType);
                $TotalRate = 0;                
                $PurpleRate = 0;
                foreach($arrBonus as $bn){                        
                    if($bn['QuartzType'] =="QPurple"){
                       $PurpleRate = $bn['Rate']; 
                    }
                    $TotalRate += $bn['Rate'];
                }
                $NumSmashMax = floor(($TotalRate/$PurpleRate)/3); 
                
                $Egg = $oSmashEgg->getEgg($EggType);               
                $Egg["SmashNum"]  = $NumSmashMax + 10; 
                $oSmashEgg->updateEgg($EggType, $Egg);                        
            }            
        }
        
        $oUser->save();
        $oSmashEgg->save();

        $LastNumHammerInStore = $oSmashEgg->getHammer($HammerType,$HammerId);                
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        if($zMoneyDiff !=0 ){
            Zf_log::write_act_log(Controller::$uId,0,23,'buyHammer',0,$zMoneyDiff,$HammerType, $HammerId, $FristNumHammerInStore,$LastNumHammerInStore, $Num);    
        }                
        
        $arr_result = array();        
        $arr_result['ZMoney'] = $oUser->ZMoney ;
        $arr_result['Error'] = Error :: SUCCESS ;                 
        
        return $arr_result;        
    }
    
    // smashEgg 
    // desc : smash egg action
    // json input: {"EggType":"WhiteEgg","IsFree":1}
    public function smashEgg($param) {
        // get params
        $EggType = $param["EggType"];
        $Num = $param["Num"];
        
        // check 
        if(empty($EggType) || intval($Num) < 1 ) {
            return array('Error' => Error :: PARAM);
        }
        if(!in_array($EggType, array('WhiteEgg','GreenEgg','YellowEgg','PurpleEgg'))) {
            return array('Error' => Error :: PARAM);
        }
        
        if($Num>100) $Num = 100;
        $oStore = Store::getById(Controller::$uId);
        $AllQuartz = $oStore->getAllQuartz();
        if(count($AllQuartz) > 500) {
            return array('Error' => Error :: NOT_ENOUGH_CONDITION); 
        }
        
        $retBonus = array();
        $Bonus = array();        
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        
        $EggHammer = Common::getConfig("SmashEgg_EggHammer",$EggType); 
        
        $FristNumHammerInStore = $oSmashEgg->getHammer($EggHammer["HammerType"],$EggHammer["HammerId"]);        
        if(!$oSmashEgg->useHammer($EggHammer["HammerType"],$EggHammer["HammerId"],$Num) ) {
            return array('Error' => Error :: NOT_ENOUGH_CONDITION);
        }                           
        
        $strBonusLog = "";
        for ($i =0; $i < $Num; $i++) {
            $Bonus = $this->getBonus($EggType);          
            foreach($Bonus as $bn){
                $strBonusLog = $strBonusLog. $bn["QuartzType"].":".$bn["QuartzId"].",";
                $bn = $this->addQuartz($bn["QuartzType"], $bn["QuartzId"]);
                array_push($retBonus,$bn);            
            }
        }
        $oSmashEgg->save();
        // write log
        $strBonusLog = substr($strBonusLog,0,-1);
        $LastNumHammerInStore = $oSmashEgg->getHammer($EggHammer["HammerType"],$EggHammer["HammerId"]);        
        Zf_log::write_act_log(Controller::$uId,0,20,'smashEgg',0,0, $EggType, $EggHammer["HammerType"], $FristNumHammerInStore, $LastNumHammerInStore,$strBonusLog);    
        // return info
        $arr_result = array();        
        $arr_result['Bonus'] = $retBonus;         
        $arr_result['NumPurpleHammerInEvent'] = $oSmashEgg->getNumPurpleHammerInEvent();
        $arr_result['NumView6Star'] = intval(DataRunTime::get('NumView6Star',true));          
        $arr_result['Error'] = Error :: SUCCESS ;        
        return $arr_result;                
    }    
    // getBonus
    //public function getBonus($param){
        //$EggType = $param["EggType"];      
     public function getBonus($EggType){           
        $mQuartzType = "QWhite";
        switch($EggType)
        {
            case 'WhiteEgg':
                $mQuartzType = "QWhite";                        
                break;            
            case 'GreenEgg':
                $mQuartzType = "QGreen";
                break;
            case 'YellowEgg':
                $mQuartzType = "QYellow";
                break;
            case 'PurpleEgg':
                $mQuartzType = "QPurple";
                break;                            
             default:
                $mQuartzType = "QWhite";
                break;       
        }               
        $arrBonus = Common::getConfig("SmashEgg_Bonus",$EggType); 
        $Bonus = Array();
        $bPur = array();
        $IsPurple = false;
        for($i=1; $i< 4; $i++) {        
            $bn = Common::pickItem($arrBonus);
            if($EggType =="PurpleEgg" && $bn["QuartzType"] =="QVIP")
            {
               $arrQuartz = $this->getPurpleRateCheckEvent('QVIP');
               if(empty($arrQuartz))
               {
                   unset($arrBonus['PurpleEgg'][4]);
                   $bn = Common::pickItem($arrBonus);
               } 
            }                     
            if($EggType =="PurpleEgg" && $bn["QuartzType"] =="QPurple"){
                $arrQuartz = $this->getPurpleRateCheckEvent('QPurple');    
            } else {
                $arrQuartz = Common::getConfig("SmashEgg_Quartz",$bn["QuartzType"]);    
            }
            $Id = Common::pickIndex($arrQuartz);
            if($EggType =="PurpleEgg" && ($bn["QuartzType"] =="QPurple" || $bn["QuartzType"] =="QVIP")){
               $IsPurple = true; 
                $bPur = array('QuartzType'=>$bn["QuartzType"],'QuartzId'=>$Id);               
            } else {
                $b = array('QuartzType'=>$bn["QuartzType"],'QuartzId'=>$Id);
                array_push($Bonus,$b);                    
            }
        }    
        if($IsPurple == true) {
            array_push($Bonus,$bPur);                    
        }
        if($EggType =="PurpleEgg") {
            $oSmashEgg = SmashEgg::getById(Controller::$uId);
            $Egg = $oSmashEgg->getEgg($EggType);

            $IsEvent = false;
            if(Event::checkEventCondition(EventType::EventActive)) {
                $IsEvent = true;
            }
            $NumPurpleHammerInEvent = 0;
            if($IsEvent) {
                $NumPurpleHammerInEvent = $oSmashEgg->getNumPurpleHammerInEvent();
                $NumPurpleHammerInEvent++;
                $oSmashEgg->setNumPurpleHammerInEvent($NumPurpleHammerInEvent);                    
            }
            
            if($IsPurple) {                                                
                $Egg['SmashNum'] = 0;
                
                
                $oSmashEgg->updateEgg($EggType,$Egg);                            
            } else {
                $Egg['SmashNum'] += 1; // 
                                
                $arrBonus = Common::getConfig("SmashEgg_Bonus",$EggType);
                $TotalRate = 0;                
                $PurpleRate = 0;
                foreach($arrBonus as $bn){                        
                    if($bn['QuartzType'] =="QPurple"){
                       $PurpleRate = $bn['Rate']; 
                    }
                    $TotalRate += $bn['Rate'];
                }
                $NumSmashMax = floor(($TotalRate/$PurpleRate)/3);                
                
                if($Egg['SmashNum'] >= $NumSmashMax) {                                    
                    $arrQuartz = $this->getPurpleRateCheckEvent();//Common::getConfig("SmashEgg_Quartz","QPurple");                        
                    $Id = Common::pickIndex($arrQuartz);
                    if($Egg['SmashNum']>$NumSmashMax+5) {
                        $b = array('QuartzType'=>"QPurple",'QuartzId'=>rand(1,3));    
                    } else {
                        $b = array('QuartzType'=>"QPurple",'QuartzId'=>$Id);    
                    }    
                    $Bonus[2] = $b; 
                    $Egg['SmashNum'] = 0;                     
                }

                $oSmashEgg->updateEgg($EggType,$Egg);
            }    
            if($IsEvent) {                         
                // check quota tong
                $b = $Bonus[2];                    
                $NumView6Star = intval(DataRunTime::get('NumView6Star', true));
                if($NumView6Star == 0) {
//                    if($b['QuartzId'] == 13) $b['QuartzId'] = rand(1,10);
                    if(($b['QuartzType'] == "QVIP") && ($b['QuartzId'] == 13))
                        {
                            $b['QuartzType'] = 'QPurple';
                            $b['QuartzId'] = rand(1,10);
                        }
                } else {                        
                    // check quota 
                    $QuotaInfo = $this->getQuotaInfo();
                    $Quota =  $QuotaInfo["Quota"];
                    $Num =  $QuotaInfo["Num"]; 
                    $Hour = $QuotaInfo["Hour"]; 
                    if($Num >= $Quota) { // het quota theo mui gio
                       /* if($b['QuartzId'] == 13) {
                            $b['QuartzId'] = rand(1, 10);                                        
                        }*/ 
                        if(($b['QuartzType'] == "QVIP") && ($b['QuartzId'] == 13))
                        {
                            $b['QuartzType'] = 'QPurple';
                            $b['QuartzId'] = rand(1,10);
                        }                                                               
                    }                        
//                    if($NumPurpleHammerInEvent > 0 && ($NumPurpleHammerInEvent % 1000) == 0 ) {
//                        $b = array('QuartzType'=>"QPurple",'QuartzId'=>13);    
//                    }
                    if($NumPurpleHammerInEvent > 0 && ($NumPurpleHammerInEvent % 1001) == 0 ) {
                        $b = array('QuartzType'=>"QVIP",'QuartzId'=>13);    
                    }
                                                
                }
                if(($b['QuartzType'] == "QVIP") && ($b['QuartzId'] == 13)) {
                    //
                    $NumView6Star = $NumView6Star - 1;
                    DataRunTime::set('NumView6Star', $NumView6Star, true);
                    $Num6Star =  intval(DataRunTime::get('Num6Star', true)) + 1; 
                    DataRunTime::set('Num6Star', $Num6Star, true);
                    $Num = intval(DataRunTime::get('Num_'.$Hour, true)) + 1;
                    DataRunTime::set('Num_'.$Hour, $Num, true);                                
                }                                                                                  
                $Bonus[2] = $b;                     
            }                
            
            $oSmashEgg->save();            
        }
        
        return $Bonus;        
    }
    
    public function getPurpleRateCheckEvent($QType = 'QPurple') {
        $arrQuartz = Common::getConfig("SmashEgg_Quartz",$QType);
        if($QType == 'QPurple')
        {            
            unset($arrQuartz[11]);    
            unset($arrQuartz[12]);                 
            unset($arrQuartz[13]);    
        }elseif($QType == 'QVIP')                    
        {            
            unset($arrQuartz[10]);                
            if(Event::checkEventCondition(EventType::EventActive)) {
                return $arrQuartz;
            }
            else
            {
               unset($arrQuartz[13]);                
            }       
        }       
        
        return $arrQuartz;  
    }
    
    public function getQuotaInfo() {
        $CurrentHour = date("H");
        $Quota = 0;
        $Num = 0;
        $Hour = 6;
        if($CurrentHour>=0 && $CurrentHour <6) {
            $Quota = intval(DataRunTime::get('Quota_6', true));    
            $Num =  intval(DataRunTime::get('Num_6', true));    
            $Hour = 6;
        }
        if($CurrentHour>=6 && $CurrentHour <12) {
            $Quota = intval(DataRunTime::get('Quota_12', true));    
            $Num =  intval(DataRunTime::get('Num_12', true));    
            $Hour = 12;
        }

        if($CurrentHour>=12 && $CurrentHour <18) {
            $Quota = intval(DataRunTime::get('Quota_18', true));    
            $Num =  intval(DataRunTime::get('Num_18', true));    
            $Hour = 18;
        }
        
        if($CurrentHour>=18 && $CurrentHour <=24) {
            $Quota = intval(DataRunTime::get('Quota_24', true));    
            $Num =  intval(DataRunTime::get('Num_24', true));    
            $Hour = 24;
        }
        return array("Quota"=>$Quota, "Num"=>$Num, "Hour"=>$Hour);
    }
    
    // addQuartz in store
    public function addQuartz($QuartzType, $QuartzId) {
        
        $oUser = User::getById(Controller::$uId);                
        $oStore = Store::getById(Controller::$uId); 
                
        $Id = $oUser->getAutoId();
        $oQuartz = new Quartz($Id, $QuartzId, $QuartzType);
        $oStore->addQuartz($QuartzType, $Id, $oQuartz);
        // call saved 
        $oStore->save();
        $oUser->save();
        //--- 
        return $oQuartz;      
    }    
    
    //upgrade Quartz
    // desc: upgrade Quartz
    // json input:  {"SoliderId":105,"QuartzType":"QWhite","Id":143,"Quartzs":[{"Id":161,"QuartzType":"QWhite"}]}
    public function upgradeQuartz($param){
        // get param info
        $SoldierId = intval($param["SoldierId"]);
        $QuartzType = $param["QuartzType"];
        $Id = intval($param["Id"]);
        $Quartzs = $param["Quartzs"]; // array id of input id        
        
        $MapQuartzType = array('QWhite'=>1,'QGreen'=>2,'QYellow'=>3,'QPurple'=>4, 'QVIP' => 5);           
        $MapTarget = $MapQuartzType[$QuartzType];
        
        // check valid data
        if(empty($QuartzType) || $SoldierId<0 || $Id <=0 || !is_array($Quartzs) || count($Quartzs)<1 ) {             
            return array('Error' => Error::PARAM);                        
        }        
        //
        $oStore = Store::getById(Controller::$uId);
        // get Quartz info from Store Equipment
        $oStoreEquipment = StoreEquipment::getById(Controller::$uId);        
        if($SoldierId >0) {
            $oQuartz = $oStoreEquipment->getQuartz($SoldierId,$QuartzType,$Id);    
        } else {
            $oQuartz =  $oStore->getQuartz($QuartzType,$Id);
        }
        if(!is_object($oQuartz)) {
            return array('Error' => Error ::OBJECT_NULL);
        }
        // get Index of Soldier        
        $OldObjIndex = $oQuartz->getIndex();  
        //
        $Level = intval($oQuartz->Level);
        $NewLevel = $Level+1; 
        // check input condition
        //
        $LevelInfo = Common::getConfig("SmashEgg_QuartzLevel",$QuartzType);
        
        $MaxLevel = count($LevelInfo);
        if($Level == $MaxLevel) {
            return array('Error' => Error ::ACTION_NOT_AVAILABLE);
        }
        
        if(!is_array($LevelInfo)) {
           return array('Error' => Error :: NOT_LOAD_CONFIG); 
        }
        $strQuartzLog = "";
        $TotalPoint = 0;
        foreach($Quartzs as $QuartzInfo){
            $iQuartzType = $QuartzInfo["QuartzType"];            
            $iId = $QuartzInfo["Id"];
            // check Quartz Type
            if($MapQuartzType[$iQuartzType] > $MapTarget) {                
                return array('Error' => Error ::PARAM);                 
            }
            // get info Quartz in store
            $Quartz = $oStore->getQuartz($iQuartzType, $iId);
            // check exist in store
            if(!is_object($Quartz)) {
               return array('Error' => Error :: OBJECT_NULL);  
            }
            // check level condition 
            $iLevel = intval($Quartz->Level);  
            /*          
            if( $MapQuartzType[$iQuartzType] == $MapTarget && $iLevel >= $NewLevel) {                
                return array('Error' => Error ::PARAM); 
            }
            */
            $LevelInfoIn = Common::getConfig("SmashEgg_QuartzLevel",$iQuartzType);
            $Point = intval($LevelInfoIn[$iLevel]["Point"]);            
                        
            if($Point <= 0){                
                return array('Error' => Error ::PARAM); 
            }
            // if pass all condition 
            $TotalPoint += $Point;
            if( !$oStore->removeQuartz($iQuartzType, $iId) ) {
                return array('Error' => Error :: ACTION_NOT_AVAILABLE);
            }           
            $strQuartzLog = $strQuartzLog. $iQuartzType.":".$Quartz->ItemId.":".$iLevel.",";              
        }
        //
        $PointUpgrade = $TotalPoint;
        // Calulator New Level                
        $TotalPoint += $oQuartz->Point;             
                        
        $RequirePoint = $LevelInfo[$NewLevel]["RequirePoint"];
        if($TotalPoint < $RequirePoint) { // chua du de len cap
            $oQuartz->Point = $TotalPoint;
            $NewLevel = $Level;
        } else {
            $NewLevel = $Level;
            while(true) {
                $NewLevel++;
                $RequirePoint = $LevelInfo[$NewLevel]["RequirePoint"];   
                if($TotalPoint >= $RequirePoint){
                    $TotalPoint -=  $RequirePoint;
                    if($NewLevel >= $MaxLevel) break;                    
                } else {
                   $NewLevel = $NewLevel-1;                     
                   break;
                }  
            }            
            $oQuartz->Point = $TotalPoint;
        }           
        
        if($NewLevel == $MaxLevel) {
            $oQuartz->Point = 0;    
        }
                
        if($NewLevel > $Level) {
            // if pass here then update info 
            $oQuartz->Level = $NewLevel;
            // update Quartz to Soldier
            if($SoldierId >0 ) {
                if(!$oStoreEquipment->addQuartz($SoldierId, $oQuartz)) {
                   return array('Error' => Error :: ACTION_NOT_AVAILABLE); 
                }       
                // update Old Index of Soldier
                $oStoreEquipment->addBonusEquipment($SoldierId, $OldObjIndex,false);                     
            } else {
                $oStore->addQuartz($QuartzType, $Id, $oQuartz);
            }
        }
        //
        $UnitPrice = Common::getConfig("SmashEgg_QuartzLevel","Price");
        $UnitPrice = intval($UnitPrice["HeSo"]);
        $cost = $PointUpgrade*$UnitPrice;        
        if($cost<=0) {
            return array('Error' => Error ::PARAM);   
        }
        $oUser = User::getById(Controller::$uId);
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        if (!$oUser->addMoney(-$cost,''))
            return array('Error' => Error::NOT_ENOUGH_MONEY);            
                
        
        //save info changed        
        $oStore->save();
        $oStoreEquipment->save();
        $oUser->save();
        //---
        // write log  
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        $moneyDiff = $oUser->Money - $moneyDiff;
        if($zMoneyDiff !=0 || $moneyDiff ){
            $strQuartzLog = substr($strQuartzLog,0,-1);      
            Zf_log::write_act_log(Controller::$uId,0,23,'upgradeQuartz',$moneyDiff,$zMoneyDiff, $QuartzType, $Id, $NewLevel, $Level, $strQuartzLog);            
        }                        
        // return info                 
        $arr_result = array();        
        $arr_result['oQuartz'] = $oQuartz;           
        $arr_result['Error'] = Error :: SUCCESS ;
        return $arr_result;
    } 
        
    // addQuartzToSoldier
    // desc: add quartz for Soldier
    // json input: {"SoldierId":102,"QuartzType":"QWhite","Id":102,"SlotId":1}
    
    public function addQuartzToSoldier($param){        
        // get param info 
        $SoldierId = intval($param["SoldierId"]);        
        $QuartzType = $param["QuartzType"];
        $Id = intval($param["Id"]);        
        $SlotId = intval($param["SlotId"]);
                
        // check valid data
        if(empty($QuartzType) || $SoldierId<=0 || $Id <=0 ) {
            return array('Error' => Error::PARAM);                        
        }        
        // find valid slot to add Quartz
        $oUser = User::getById(Controller::$uId);
        
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        
        $oStoreEquipment = StoreEquipment::getById(Controller::$uId);
        
        $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
        $oSoldier = null;        
        for($i=1; $i<=$oUser->LakeNumb; $i++) {
            if(isset($arrSoldier[$i][$SoldierId])) {
                $oSoldier = $arrSoldier[$i][$SoldierId];
                break;    
            }
        }
        if(!is_object($oSoldier)) {
            return array('Error' => Error::OBJECT_NULL); 
        }
        
        $SoldierLevel = intval($oSoldier->Rank);
        
        //$userLevel = intval($oUser->getLevel());
        
        $RequireLevelInfo = Common::getConfig("SmashEgg_Slot");
        if($SlotId <= 0 ) {
            $SlotId = 0;
            //
            for($i=1; $i<=8; $i++){
                $RequireLevel = intval($RequireLevelInfo[$i]['RequireLevel']);            
                if($RequireLevel > $SoldierLevel) break;           
                // if pass condition level
                if($oSmashEgg->checkSlot($SoldierId,$i)) {
                    $SlotId = $i; 
                    break;
                }      
                if($SlotId >0) break;
            }
            
            if($SlotId <=0) {
               return array('Error' => Error :: NOT_ENOUGH_CONDITION); 
            }                            
        } else {
            if($SlotId > 8) {
                return array('Error' => Error::PARAM);
            }
            $RequireLevel = intval($RequireLevelInfo[$SlotId]['RequireLevel']);            
            if($RequireLevel > $SoldierLevel) {
               return array('Error' => Error :: NOT_ENOUGH_CONDITION);      
            }             
            if( !$oSmashEgg->checkSlot($SoldierId,$SlotId) ){
               return array('Error' => Error::OBJECT_NULL); 
            }
        }
        
        $oSmashEgg->addSlot($SoldierId,$SlotId,$QuartzType,$Id);

        // get Quartz info 
        $oStore = Store::getById(Controller::$uId);
        
        // get Quartz info from store
        $oQuartz = $oStore->getQuartz($QuartzType,$Id);
        
        // check exist
        if(!is_object($oQuartz)) {
            return array('Error' => Error::EXIST);
        }             
        // remove out store   
        if( !$oStore->removeQuartz($QuartzType,$Id) ) {
            return array('Error' => Error :: OBJECT_NULL);
        }
        // add Quartz for Soldier

        if( !$oStoreEquipment->addQuartz($SoldierId,$oQuartz)) {
            return array('Error' => Error ::EXPIRED);
        }        
        //if pass here then save info changed        
        $oStore->save();
        $oStoreEquipment->save(); 
        $oSmashEgg->save();                               
        // write log        
        Zf_log::write_act_log(Controller::$uId,0,20,'addQuartzToSoldier', 0, 0, $SoldierId, $QuartzType, $Id, $SlotId, 1);                    
        // return info
        $arr_result = array();        
        $arr_result['oQuartz'] = $oQuartz;       
        $arr_result['SlotId'] = $SlotId;       
        $arr_result['Error'] = Error :: SUCCESS ;
        return $arr_result;                
    } 
       
    // removeQuartzFromSoldier
    // desc: remove Quartz out Sotre Equipment 
    //  json input: {"SoldierId":102,"SlotId":1,"QuartzType":"QWhite","Id":103} 
    public function removeQuartzFromSoldier($param){        
        // get param info 
        $SoldierId = intval($param["SoldierId"]);
        $SlotId = intval($param["SlotId"]);
                       
        // check at the position has exist an Quartz
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        $QuartzInfo = $oSmashEgg->getSlot($SoldierId, $SlotId);        
        $QuartzType = trim($QuartzInfo["QuartzType"]);
        $Id = intval($QuartzInfo["Id"]);            
        
        
        
        if(!$oSmashEgg->removeSlot($SoldierId,$SlotId)) {
            return array('Error' => Error :: PARAM);
        }
        //---                
        // get Quartz info from Store Equipment
        $oStoreEquipment = StoreEquipment::getById(Controller::$uId);
               
        $oQuartz = $oStoreEquipment->getQuartz($SoldierId,$QuartzType,$Id);
        
        if(!is_object($oQuartz)) {
            return array('Error' => Error :: OBJECT_NULL);
        }       
        
        // remove Quartz out Store Equipment
        $oStoreEquipment->deleteQuartz($SoldierId,$QuartzType,$Id);
                
        // change property and add Store
        $oQuartz->IsUsed = false;
        $oStore = Store::getById(Controller::$uId); 
        $oStore->addQuartz($QuartzType, $Id, $oQuartz);        
        // save info changed
        $oStoreEquipment->save();
        $oStore->save();      
        $oSmashEgg->save();  
             
        if($oSmashEgg->countTotalSlot($SoldierId)<= 0)
        {   
            $arr = array('QYellow','QWhite','QGreen','QPurple','QVIP') ;
            foreach($arr as $Type)
            { 
                if(empty($oStoreEquipment->SoldierList[$SoldierId]['Equipment'][$Type]))
                    continue ;
                foreach($oStoreEquipment->SoldierList[$SoldierId]['Equipment'][$Type] as $Index => $objectQuartz)     
                {
                        if(!empty($Index)&& is_object($objectQuartz))
                        {
                             //xoa do di 
                             $oStoreEquipment->deleteQuartz($SoldierId,$Type,$Index);    
                              // luu vao kho 
                             $objectQuartz->IsUsed = false;  
                             $oStore->addQuartz($Type, $Index, $objectQuartz);        
                             $oStore->save();      
                        }
                        else
                        {
                            unset($oStoreEquipment->SoldierList[$SoldierId]['Equipment'][$Type][$Index]) ;
                        }
                }
            }
            $oStoreEquipment->save();
            $oStore->save();      
        }

        // write log        
        Zf_log::write_act_log(Controller::$uId,0,20,'removeQuartzFromSoldier', 0, 0, $SoldierId, $QuartzType, $Id, $SlotId, 1);                    
        // return info 
        $arr_result = array();        
        $arr_result['oQuartz'] = $oQuartz;           
        $arr_result['Error'] = Error :: SUCCESS ;
        return $arr_result;                                    
        
    }        
    //
    
    public function syncSlot(){
        $oUser = User::getById(Controller::$uId);
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        $oStoreEquipment = StoreEquipment::getById(Controller::$uId);
               
        $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
        $arrSoldierId = array();        
        for($i=1; $i<=$oUser->LakeNumb; $i++) {            
            $FishList = $arrSoldier[$i];
            foreach($FishList as $SoldierId => $oFish) {                
                $Slots = $oSmashEgg->getSoldierSlot($SoldierId);
                foreach($Slots as $SlotId => $slot){
                    $QuartzType = $slot["QuartzType"];
                    $Id = $slot["Id"];
                    $oQuartz = $oStoreEquipment->getQuartz($SoldierId,$QuartzType,$Id);
                    if(!is_object($oQuartz)) {
                        $oSmashEgg->removeSlot($SoldierId,$SlotId);                        
                    }                                        
                }                
            }            
        }     
        $oSmashEgg->save();           
    }    

    // receive Bonus
    public function receiveBonus($param){        
        // get params
        $EggType = trim($param["EggType"]);
        $now = $_SERVER['REQUEST_TIME'];
        
        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        //get Egg info
        $EggInfo = $oSmashEgg->getSmashEggInfo();        
        $arrEgg = $EggInfo["Egg"];
        // get Limit info
        $TimeEggFree = Common::getConfig("SmashEgg_EggHammer"); 
        
        $Egg = $arrEgg[$EggType];
        $Time = trim($Egg["Time"]); 
        // check time condition
        if( $Time !=0 && ($Time + $TimeEggFree[$EggType]["Time"] > $now) ){
            return array('Error' => Error::NOT_ENOUGH_CONDITION);                   
        }    
        $HammerType = "";
        $HammerId = 1;
        $Num = 1;        
                
        switch($EggType)
        {
            case 'WhiteEgg':
                $HammerType = "HammerWhite";        
                break;            
            case 'GreenEgg':     
                $HammerType = "HammerGreen";        
                break;
            case 'YellowEgg':
                $HammerType = "HammerYellow";
                break;        
            case 'PurpleEgg':
                $HammerType = "HammerPurple";            
                break;
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        
        
        $oSmashEgg->addHammer($HammerType,$HammerId,$Num);
        $Egg["Time"] = $now;    
        $oSmashEgg->updateEgg($EggType, $Egg);
        $Bonus = array("HammerType"=>$HammerType, "HammerId"=>$HammerId,"Num"=>$Num);        
        // save info changed
        $oSmashEgg->save();     
        // write log        
        Zf_log::write_act_log(Controller::$uId,0,20,'receiveBonus',0,0, $HammerType, $HammerId, $Num, 0, 1);    
        // return info
        $arr_result = array();        
        $arr_result['Bonus'] = $Bonus;           
        $arr_result['Time'] = $now;                   
        $arr_result['Error'] = Error :: SUCCESS ;
        return $arr_result;
    }
    
    public function convertEquipToQuartz(){
        $Types = Common::getConfig('General', 'QuartzTypes'); 
        $oStore = Store::getById(Controller::$uId);
        $Equipments = $oStore->Equipment;        
        foreach($Types as $Type) {
            if(is_array($Equipments[$Type]) && count($Equipments[$Type]) >0 ){
                $Quartzs = $Equipments[$Type];
                foreach($Quartzs as $oQuartz) {
                    $QuartzType = $oQuartz->Type;
                    $Id = $oQuartz->Id;                                    
                    $oStore->addQuartz($QuartzType, $Id, $oQuartz);                         
                }
                $oStore->Equipment[$Type] = array();
            }            
        }
        $oStore->save();
    }

    public function buyDiscountHammer($param){        
        $Index =  intval($param["Index"]);
        $PriceType = "ZMoney";        
        // check time event 
        if(!Event::checkEventCondition(EventType::EventActive))
            return Common::returnError(Error::EVENT_EXPIRED);                          
        if($Index <=0 ){
            return array('Error' => Error::PARAM);
        }        
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        $oSmashEgg = SmashEgg::getById(Controller::$uId);        
        $zMoneyDiff = $oUser->ZMoney;        
        
        // get price info 
        $packInfo = Common::getConfig("SmashEgg_Discount", $Index);
        
        if(!isset($packInfo[$PriceType]) || intval($packInfo[$PriceType])<=0 ) {
            return array('Error' => Error ::NOT_LOAD_CONFIG);
        }
        $cost = intval($packInfo[$PriceType]);
        if($cost <=0 ) {
            return array('Error' => Error::PARAM);
        }
        $HammerType = $packInfo["ItemType"];
        $HammerId   = intval($packInfo["ItemId"]);
        $Num        = intval($packInfo["Num"]);
        
        switch($PriceType)
        {
            case Type::ZMoney:
                $info = $Index.':'.$HammerType.':'.$Num;
                if (!$oUser->addZingXu(-$cost,$info))    
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                break;                        
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        // Update store
        $FristNumHammerInStore = $oSmashEgg->getHammer($HammerType,$HammerId);        
        $oSmashEgg->addHammer($HammerType,$HammerId,$Num);
                
        $oUser->save();
        $oSmashEgg->save();

        $LastNumHammerInStore = $oSmashEgg->getHammer($HammerType,$HammerId);                
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        if($zMoneyDiff !=0 ){
            Zf_log::write_act_log(Controller::$uId,0,23,'buyDiscountHammer',0,$zMoneyDiff,$HammerType, $Index, $FristNumHammerInStore,$LastNumHammerInStore, $Num);    
        }                
        
        $arr_result = array();        
        $arr_result['ZMoney'] = $oUser->ZMoney ;
        $arr_result['Error'] = Error :: SUCCESS ;                         
        return $arr_result;        
    }
    
    public function updateFirstTimeOfDay() {
        $DateNow = date('Ymd');
        $DateKey = DataRunTime::get('DateKey', true);
        if($DateNow == $DateKey) {
            DataRunTime::set('DateKey', $DateNow, true);
            DataRunTime::set('Num_6', 0, true);
            DataRunTime::set('Num_12', 0, true);
            DataRunTime::set('Num_18', 0, true);
            DataRunTime::set('Num_24', 0, true);
        }
        return $DateNow;
    }
        
    
} 
?>
