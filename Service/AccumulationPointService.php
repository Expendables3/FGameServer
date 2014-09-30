<?php
class AccumulationPointService
{
    // The function getAccumulationPoint
    // Return info Point, startTime, endTime 
    public function getAccumulationPoint(){
        //
        $oUser = User::getById(Controller::$uId);        
        $conf = Common::getConfig('Param','AccumulationPoint');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        
        if($now < $StartTime || $now > $EndTime ){  // 
            $oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);
            $oAccumulationPoint->setPoint(0);
            $oAccumulationPoint->save();
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            

        //$oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);
        //$oAccumulationPoint->setPoint(500);
        //$oAccumulationPoint->save();
        
        $oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);
                
        $Point = $oAccumulationPoint->getPoint();
                
        $ret = array();
        $ret['AccumulationPoint'] = array('Point'=>$Point,
                                          'StartTime' => $StartTime,
                                          'EndTime' => $EndTime
                                        );     
        $ret['Error'] = Error::SUCCESS;                                                                                
        return $ret;            
    }
    
    //  The function exchangeAccumulationPoint 
    // exchange point to items
    public function exchangeAccumulationPoint($params){
        $Id = intval($params['Id']);
        $Element = intval($params['Element']);
        //check Time condition
        $conf = Common::getConfig('Param','AccumulationPoint');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];

        if($now < $StartTime || $now > $EndTime ){  //
            $oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);
            $oAccumulationPoint->setPoint(0);
            $oAccumulationPoint->save();         
           return array('Error' => Error ::NOT_ENOUGH_CONDITION);    
        }            
         
        
        $AccumulationPointData = Common::getConfig('Accumulation_Point');        
        if($Id<=0 || !isset($AccumulationPointData[$Id])) {
            return array('Error' => Error ::PARAM);    
        }        
        $ItemConfig = $AccumulationPointData[$Id];
        if(!is_array($ItemConfig))
        {
            return array('Error' => Error :: NOT_LOAD_CONFIG);
        }                        
        $PointRequire = $ItemConfig['Point'];
        
        $oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);
        $FirstPoint = $oAccumulationPoint->getPoint();
        //---
        if(!$oAccumulationPoint->usePoint($PointRequire)){
            return array('Error' => Error::NOT_ENOUGH_CONDITION);    
        }
        
        $ret = array();        
        
        if($ItemConfig["ItemType"] == 'Gem'){
            $itemId = $ItemConfig["ItemId"];
            $bonus = array();
            $ItemConfig = array("ItemType"=>"Gem", "ItemId"=>$itemId, "Element"=>1, "Day"=>7,"Num"=>1);
            for($i=1; $i<=5; $i++){
                $ItemConfig["Element"] = $i;
                array_push($bonus,$ItemConfig);
            }            
            $retGifts =  Common::addsaveGiftConfig($bonus,$Element,SourceEquipment::GIVE);            
            //$ItemConfig = $retGifts;            
            
        } else if($ItemConfig["ItemType"] == 'GiftBox') {
            $GiftId = $ItemConfig["ItemId"];
            $Gifts = Common::getConfig('Accumulation_Gift',$GiftId);
            $bonus = array();
            $Types = array(SoldierEquipment::Armor, SoldierEquipment::Helmet,SoldierEquipment::Weapon);
            foreach ($Gifts as $Gift) {
                if($Element >= 1 && $Element <= 5){
                    $Gift['Element'] = $Element;    
                }                
                array_push($bonus,$Gift);                 
            }
            //$oUser->saveBonus($bonus);
            $retGifts =  Common::addsaveGiftConfig($bonus,$Element,SourceEquipment::GIVE);            
            //$ItemConfig = $retGifts;            
        } else {
            $retGifts =  Common::addsaveGiftConfig(array($ItemConfig),$Element,SourceEquipment::GIVE);            
            //$ItemConfig = $retGifts;
        }  
                        
        $oAccumulationPoint->save();
        $ret['Point'] = $oAccumulationPoint->getPoint();
        $ret['Bonus'] = $retGifts;
        $ret['Error'] = Error::SUCCESS;    
        $LastPoint = $oAccumulationPoint->getPoint();
        Zf_log::write_act_log(Controller::$uId, 0, 20, 'exchangeAccumulationPoint', 0, 0, $ItemConfig[Type::ItemType], $Id, $PointRequire, $FirstPoint, $LastPoint);
        return $ret;            
    }
    
    
        
}
?>
