<?php
class HammerManService
{
    public function getHammerMan(){
        $infoArr = array();    
        // HammerMan Info
        $HammerManInfo = array();
        $oIng = Ingredients::getById(Controller::$uId);
        $HammerManInfo['PowerTinh'] = $oIng->getPowerTinh();
        $oHammer = HammerMan::getById(Controller::$uId);        
        $HammerManInfo['Percent'] = $oHammer->getPercent();
        $HammerManInfo['TotalPercent'] = $oHammer->getTotalPercent();
        
        $HammerManInfo['MakeOption'] = $oHammer->getMakeOption();
        //check exist, enchant, valid
        //$this->checkValidItem();
        $HammerManInfo['TempBonus'] = $oHammer->getTempBonus();
        $HammerManInfo['TempEquipment'] = $oHammer->getEquip();
        
        $infoArr['HammerMan'] = $HammerManInfo;         
        $infoArr['LimitChangeOption'] = Common::getConfig('Param','LimitChangeOption');    
        $infoArr['NumberPointChangeItem'] = Common::getConfig('Param','NumberPointChangeItem');    
        $infoArr['Error'] = Error::SUCCESS;     
        return $infoArr;        
    }
    // check items is exist, buy, enchant, ..
    public function checkValidItem(){
         $oHammer = HammerMan::getById(Controller::$uId);
         $TempBonus = $oHammer->getTempBonus();
         $ItemId = $TempBonus['ItemId'];
         $ItemType = $TempBonus['ItemType'];
         $EnchantLevel = $TempBonus['EnchantLevel'];
         $bonus = $TempBonus['bonus'];
         
         // check exist
         $oStore = Store::getById(Controller::$uId);         
         
         if(!isset($oStore->Equipment[$ItemType][$ItemId])){    // has sale
            $oHammer->setTempBonus(array());  
            $oHammer->save();
         }
         
         // check enchant
         $equipment = $oStore->getEquipment($ItemType, $ItemId);         
         $EnchantLevelCur = intval($equipment->EnchantLevel);
         $bonusCur = $equipment->bonus;
         if($EnchantLevelCur != $EnchantLevel){ // has Enchant
            $oHammer->setTempBonus(array());  
            $oHammer->save();                
         }
         // check valid
         if(count($bonus) != count($bonusCur) ){
            $oHammer->setTempBonus(array());  
            $oHammer->save();             
         }         
    }         
    
    public function splitEquip($params){        
        $input = $params['Input'];        
        // check time condition
        $conf = Common::getConfig('Param','HammerManTime');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];

        if($now < $StartTime || $now > $EndTime ){  // 
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            
        $oHammer = HammerMan::getById(Controller::$uId);                        
        $totalPercentFirst = $oHammer->getTotalPercent();
        $totalPercent = 0;
        $lookupData =  Common::getConfig('HammerMan_Lookup');        
        
        foreach($input as $item){
            if(intval($item['Rank'])>100){
                $itemRank = intval($item['Rank'])%100;    
            } else {
                $itemRank = intval($item['Rank']);    
            }                        
            $itemColor = intval($item['Color']); 
            if(!isset($lookupData[$itemRank][$itemColor]['Percent']) ){
                return array('Error' => Error ::PARAM);    
            }           
            $itemPercent = intval($lookupData[$itemRank][$itemColor]['Percent']); 
            $totalPercent += $itemPercent; 
        }
        
        $oStore = Store::getById(Controller::$uId);
        $arrEquipmentDelete = array();
        
        foreach($input as $item){            
            $iItemType = $item['Type'];
            $iItemId = intval($item['Id']); 
            // save Equipment delete to array
            $arrEquipmentDelete[] = $oStore->getEquipment($iItemType, $iItemId);            
            // delete Equipment
            if(!$oStore->removeEquipment($iItemType, $iItemId))
               return array('Error' => Error::ACTION_NOT_AVAILABLE);                
        }            
        $oHammer->addTotalPercent($totalPercent);
        $oHammer->save();
        $oStore->save();

        // write log delete Equipment
        foreach($arrEquipmentDelete as $oEquipment){
            Zf_log::write_equipment_log(Controller::$uId, 0, 20,'deleteEquipment', 0, 0, $oEquipment); 
        }        
        
        $totalPercentLast = $oHammer->getTotalPercent();
        
        Zf_log::write_act_log(Controller::$uId, 0, 20, 'splitEquip', 0, 0, 0, 0, $totalPercentFirst, $totalPercentLast, $totalPercent);             
                
        $arr['Error'] = Error::SUCCESS;
        $arr['TotalPercent'] = $totalPercentLast;        
        //---
        return $arr;                                 
    }
    
    // The function makeEquip to make equipment level 3 from equipment have level is 1,2
    // @params: PriceType, target, input
    // return list equipments has created if success
    public function makeEquip($params){
        //  
        $PriceType = $params['PriceType'];
        $targetNum = intval($params['Num']);        
        $target = $params['Target'];               
        $targetRank = intval($target['Rank']);
        $targetElement = intval($target['Element']);
        
        
        // check time condition
        $conf = Common::getConfig('Param','HammerManTime');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        
        if($now < $StartTime || $now > $EndTime ){  // 
            return array('Error' => Error ::EXPIRED);    
        }            
                
                            
        // check valid rank 
        if($targetRank < 3) {
            return array('Error' => Error ::PARAM);
        }
        
        $targetColor = intval($target['Color']);
        
        
        if($targetColor !=4){ // is not god 
            return array('Error' => Error ::PARAM);
        }                
        $targetItemType = $target['Type'];                
        
        
        
        $MakeInfo =  Common::getConfig('HammerMan_Finish');
        if($targetRank > 100){
            $tRank = $targetRank %100;    
        } else {
            $tRank = $targetRank;    
        }                        
        
        $MakeInfo = $MakeInfo[$tRank][$targetColor];        
        
        $RequirePoint = $MakeInfo['Point']*$targetNum;
        $totalCost = $MakeInfo[$PriceType];
        
        $oHammer = HammerMan::getById(Controller::$uId);        
        if(!$oHammer->useTotalPercent($RequirePoint)) {
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);     
        }                                   
        
        $oUser = User::getById(Controller::$uId);
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        // update money of user into buffer
        switch($PriceType)
        {
            case Type::Money:
                if (!$oUser->addMoney(-$totalCost,'extendEquipment'))
                    return array('Error' => Error::NOT_ENOUGH_MONEY);            
                break;
            case Type::ZMoney:
                $info = '1:'.$targetItemType.':1';
                if (!$oUser->addZingXu(-$totalCost,$info))    
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                break;            
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        // Transaction is success, update items        
        $oStore = Store::getById(Controller::$uId);        
        $arr = array();
         for($i=1; $i<=$targetNum; $i++) {
                $numOpt = rand(4,5);
                $autoId =  $oUser->getAutoId();        
                //return $autoId." ".$targetRank." ". $targetColor ." ". SourceEquipment::CRAFT ." ". $targetItemType ."  0 ". $targetElement." 0";        
                $oEquipment = Common::randomEquipment($autoId, $targetRank, $targetColor,SourceEquipment::CRAFT, $targetItemType, 0, $targetElement,$numOpt);   //$HammerManOption[$targetColor]
                $oEquipment->Author = array('Id' => Controller::$uId, 'Name'=>$oUser->Name);                
                $oStore->addEquipment($oEquipment->Type, $oEquipment->Id, $oEquipment);
                //                                
                $arr['Equipment'][] = $oEquipment ;     
                $oUser->save();
         }                
        // update Percent
        $arr['Percent'] = $oHammer->Percent;        
        //---
        $oStore->save();        
        $oHammer->save();
        $oUser->save();
        // Update log 
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;        
        //log         
        if($moneyDiff != 0 || $zMoneyDiff!= 0){             
            Zf_log::write_act_log(Controller::$uId, 0, 23, 'makeEquip', $moneyDiff, $zMoneyDiff, $targetItemType."_".$targetRank."_".$targetColor."_".$targetElement, 0, $RequirePoint, $oHammer->getTotalPercent(), $targetNum);
        }
       
        if($targetNum >0){
            // write log save Equipment
            foreach($arr['Equipment'] as $oEquipment) {
                Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquipment);
            }           
        }               
        $arr['Error'] = Error::SUCCESS;
        $arr['ZMoney'] = $oUser->ZMoney;
        $arr['Money'] = $oUser->Money;        
        //---
        return $arr;         
    }
   
    //

     public function makeOption($params){
        //               
        //$oIng = Ingredients::getById(Controller::$uId);
        //$oIng->addPowerTinh(100000);
        //$oIng->save();                
        $ItemType = $params['ItemType'];
        $ItemId = $params['ItemId'];
        
        // check time condition
        $conf = Common::getConfig('Param','HammerManTime');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        
        if($now < $StartTime || $now > $EndTime ){  // 
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            
        
        // get item info
        $oStore = Store::getById(Controller::$uId);       
        $equipment = $oStore->getEquipment($ItemType, $ItemId);
        if(!is_object($equipment)){
            return array('Error' => Error::EXIST);
        }  
        $Rank = intval($equipment->Rank);
        $Color = intval($equipment->Color);
        $EnchantLevel = intval($equipment->EnchantLevel);
        if($Color<2) { // Do thuong 
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);
        }
        //
        $oHammer = HammerMan::getById(Controller::$uId);                
        
        if(!isset($equipment->NumChangeOption)){
            //                               
            $equipment->NumChangeOption = 0; 
        }   
        $NumChangeOption = $equipment->NumChangeOption;
             
        $LimitChangeOption = Common::getConfig('Param','LimitChangeOption');            
        $limitNumber = $LimitChangeOption[$Color];
        if(intval($NumChangeOption) >= $limitNumber){
            return array('Error' => Error ::PARAM);
        } 
        
        //
        $iRank = ($Rank<100)? $Rank: $Rank%100;
        $iColor = $Color; 
        //
        $dataLookup = Common::getConfig('HammerMan_Option');
        $PowerRequire =  $dataLookup[$iRank][$iColor]['Power'];
        // Check valid Power Tinh luc
        $oIng = Ingredients::getById(Controller::$uId);
        $PowerTinh = $oIng->getPowerTinh();
        
        if($PowerTinh < $PowerRequire) {
            return array('Error' => Error ::NOT_ENOUGH_CONDITION); 
        }
        $oIng->usePowerTinh($PowerRequire);        
        $oIng->save();
        
        //
        $bonus = $equipment->bonus;
        
        $NumOpt = count($bonus);        
        $bonusOpt = Equipment::getBonusEquipment($ItemType,$Rank, $Color, $NumOpt);
        foreach($bonusOpt as $key => $ebonus){
            $property = key($ebonus);
            $addTotal = $this->getAddFromEnchantLevel($ItemType, $ItemId, $property);
            $ebonus[$property] += $addTotal;
            $bonusOpt[$key] = $ebonus;                
        }        
        
        $oHammer = HammerMan::getById(Controller::$uId);
        $tempBonus = array('ItemId'=>$ItemId,
                           'ItemType'=>$ItemType,
                           'EnchantLevel'=>$EnchantLevel,
                           'bonus'=>$bonusOpt
                          );
                          
        
        
        $oHammer->setTempBonus($tempBonus);   
        $equipment->NumChangeOption = $equipment->NumChangeOption + 1;
        $oHammer->setEquip($equipment);                
        
        $oHammer->save();
                
        $oStore->removeEquipment($ItemType,$ItemId);
        $oStore->save();        
        
        $ret = array();
        $ret['Error'] = Error::SUCCESS;
        $ret['NewBonus'] = $bonusOpt;
        $ret['TempEquipment'] = $equipment;
        // Write log remove Equipment 
        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'deleteEquipment', 0, 0, $equipment);
        //$bonusOpt = $oHammer->getMakeOption();
        Zf_log::write_act_log(Controller::$uId, 0, 20, 'makeOption', 0, 0, $ItemType, $ItemId, 0, 0,$PowerRequire);
        return $ret;     
     }
     
     public function saveOption($params){
        //
        $ItemType = $params['ItemType'];
        $ItemId = intval($params['ItemId']);

        // check time condition
        $conf = Common::getConfig('Param','HammerManTime');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        
        if($now < $StartTime || $now > $EndTime ){  // 
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            
        
        $oHammer = HammerMan::getById(Controller::$uId);        
        $tempBonus = $oHammer->getTempBonus();
        if(empty($tempBonus)){
            return array('Error' => Error ::PARAM);
        }
        $Id = $tempBonus['ItemId'];
        $bonus = $tempBonus['bonus'];
        if($Id != $ItemId){
            return array('Error' => Error ::PARAM); 
        }
        
        $oStore = Store::getById(Controller::$uId);       
        $oEquip = $oHammer->getEquip();//$oStore->getEquipment($ItemType, $ItemId);                             
        if(count($oEquip->bonus) != count($bonus)){
            return array('Error' => Error ::PARAM);
        }
        $oEquip->bonus = $bonus;
        $oStore->addEquipment($ItemType, $ItemId, $oEquip);
        $oStore->save();
        $oHammer->setTempBonus(array()); 
        //$oHammer->addMakeOption($ItemId,1);
        $oHammer->setEquip(null);
        $oHammer->save();  
        
        $ret = Array();
        $ret['Error'] = Error::SUCCESS;
        $ret['Equipment'] = $oEquip;
        Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip);
        Zf_log::write_act_log(Controller::$uId, 0, 20, 'saveOption', 0, 0, $ItemType, $ItemId, 0, 0, 0);
        return  $ret;   
     }
     
     public function cancelOption($params){
        $ItemType = $params['ItemType'];
        $ItemId = intval($params['ItemId']);
        // check time condition
        $conf = Common::getConfig('Param','HammerManTime');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        
        if($now < $StartTime || $now > $EndTime ){  // 
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            
        
        $oHammer = HammerMan::getById(Controller::$uId);        
        $tempBonus = $oHammer->getTempBonus();
        if(empty($tempBonus)){
            //return array('Error' => Error ::PARAM);
        }
        $oStore = Store::getById(Controller::$uId);       
        $oEquip = $oHammer->getEquip();//$oStore->getEquipment($ItemType, $ItemId);                             
        $oStore->addEquipment($ItemType, $ItemId, $oEquip);
        $oStore->save();
        
        $oHammer->setTempBonus(array());
        $oHammer->setEquip(null);
        $oHammer->save();    
        $ret = Array();
        $ret['Error'] = Error::SUCCESS;
        Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip);
        Zf_log::write_act_log(Controller::$uId, 0, 20, 'cancelOption', 0, 0, $ItemType, $ItemId, 0, 0, 0);
        return  $ret;                 
     }
     
     public function getAddFromEnchantLevel($ItemType, $ItemId, $property){
        if($property=='') return 0;
        $oStore = Store::getById(Controller::$uId);
        $oEquipment = $oStore->Equipment[$ItemType][$ItemId];
        $conf_major = Common::getConfig('Param','SoldierEquipment','Major');
        if (in_array($ItemType,$conf_major))
            $conf_equip = Common::getConfig('EnchantEquipment_Minor',round($oEquipment->Rank%100),$oEquipment->Color);
        else $conf_equip = Common::getConfig('EnchantEquipment_Minor',$oEquipment->Rank,$oEquipment->Color);
        $enchantLevel = $oEquipment->EnchantLevel;
        if($enchantLevel<1) return 0;
        $totalAdd = 0; 
        for($i=1; $i<=$enchantLevel; $i++){
            $totalAdd += intval($conf_equip[$i][$property]);         
        }
        return $totalAdd;             
     } 
         
    
}      
?>
