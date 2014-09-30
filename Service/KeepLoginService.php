<?php

/**
*  Written by: thedg25
*  Date: 2012/06/12
*  Desc: API for feature receive gift from keep login  
*/

class KeepLoginService {
    
    //getKeepLogin
    public function getKeepLogin(){        
        //$this->checkErrorFirstLog();
        $oKeepLogin = KeepLogin::getById(Controller::$uId);        
        //$time = mktime(1,1,1,12,11,2012);        
        //$oKeepLogin->setLastLoginTime($time);        
        $NumCanLogin = $oKeepLogin->getNumCanLogin();
        $NumCurrentLogin = $oKeepLogin->getNumCurrentLogin();
        if($NumCanLogin ==1 || $NumCurrentLogin ==0) { // The first day of event then call updateKeepLogin every time open form (no error if event actived after 0h) 
            $oKeepLogin->updateKeepLogin();    
        }                
        $KeepLogin = $oKeepLogin->getKeepLogin();
        $Ret = array();
        $Ret['Error'] = Error::SUCCESS;
        $Ret['KeepLogin'] = $KeepLogin;        
        return $Ret;
    }
    
    //restoreKeepLogin
    public function restoreKeepLogin($params){
        if(!Event::checkEventCondition(EventType::KeepLogin))
            return Common::returnError(Error::EVENT_EXPIRED);
        $PriceType = "ZMoney";
        $DayIndex = intval($params["DayIndex"]);
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $NumCurrentLogin = $oKeepLogin->getNumCurrentLogin();                
        $restoreEndIndex = $oKeepLogin->getNumCanLogin();
        if($DayIndex>$restoreEndIndex){
            return array('Error' => Error ::NOT_ENOUGH_CONDITION);
        }          
        if($DayIndex != $NumCurrentLogin+1) {
            return array('Error' => Error ::PARAM);
        }      
        // get price
        $KeepLoginGift = Common::getConfig("KeepLogin_Gift");        
        $cost = intval($KeepLoginGift[$DayIndex]["Price"][$PriceType]); 
        if($cost <=0) {
            return array('Error' => Error ::NOT_LOAD_CONFIG);
        }
        $oUser = User::getById(Controller::$uId);
        // get coin before buy        
        $zMoneyDiff = $oUser->ZMoney;
        $Num = 1;
                
        switch($PriceType)
        {
            case Type::ZMoney:
                
                $info = '1:Restore:'.$Num;
                if (!$oUser->addZingXu(-$cost,$info))    
                    return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                break;            
            case Type::Diamond:
                if (!$oUser->addDiamond(-$cost, DiamondLog::restoreKeepLogin))                    
                    return array('Error' => Error::NOT_ENOUGH_DIAMOND);
                break;
            
             default:
                return array('Error' => Error::TYPE_INVALID);
                break;       
        }       
        // Update store
        //if pass condition pay then update
        $oKeepLogin->setNumCurrentLogin($DayIndex);        
        $oKeepLogin->setStatus($DayIndex,1);
        
        $oKeepLogin->save();
        $oUser->save();

        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;        
        if($zMoneyDiff !=0 ){            
            Zf_log::write_act_log(Controller::$uId,0,23,'restoreKeepLogin',0,$zMoneyDiff,'Restore', 1, $DayIndex, 0, $Num);    
        }        
        
        $KeepLogin = $oKeepLogin->getKeepLogin();
        $Ret = array();
        $Ret['Error'] = Error::SUCCESS;
        $Ret['KeepLogin'] = $KeepLogin;        
        return $Ret;                
    }
    // receiveGift
    // Json Input: {"DayIndex":1}
    public function receiveGift($params){
         if(!Event::checkEventCondition(EventType::KeepLogin))
            return Common::returnError(Error::EVENT_EXPIRED);        
        $DayIndex = intval($params["DayIndex"]);
        // check valid 
        if($DayIndex <=0) {
            return  array("Error" => Error :: PARAM);
        }
        // check condition
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $NumCurrentLogin = $oKeepLogin->getNumCurrentLogin();
        if($DayIndex > $NumCurrentLogin) {
            return  array("Error" => Error :: NOT_ENOUGH_CONDITION);
        }
        $Status = intval($oKeepLogin->getStatus($DayIndex));
        if($Status != 1) {
            return  array("Error" => Error :: NOT_ENOUGH_CONDITION);
        }
        //
        $oKeepLogin->setStatus($DayIndex,2);
        
        $GiftConfig = Common::getConfig("KeepLogin_Gift",$DayIndex);
        $Gifts = $GiftConfig["Gift"];
        $RetBonus = Common::addsaveGiftConfig($Gifts,'',SourceEquipment::EVENT);
        
        $oKeepLogin->save();
        Zf_log::write_act_log(Controller::$uId,0,20,'receiveGift',0,0, 0, 1, 0, 0, $DayIndex);    
        $Ret = array();
        $Ret['Error'] = Error::SUCCESS; 
        $Ret['RetBonus'] = $RetBonus;                
        return $Ret;                        
    }
    
    
    public function test(){
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $Today   =  $_SERVER['REQUEST_TIME'];
        $Eventconf =  Common::getConfig('Event', EventType::KeepLogin);        
        $Begin     =  $Eventconf['BeginTime'];
        $Expired   =  $Eventconf['ExpireTime'];        
        $NumCanLogin = $oKeepLogin->getNumberDays($Begin,$Today);
        return $NumCanLogin;        
    }
    
    public function checkErrorFirstLog() {
        $Now = $_SERVER['REQUEST_TIME'];                
        $DateNow = date('Y-m-d',$Now);        
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        if($DateNow == '2012-12-25') {
            if($oKeepLogin->getStatus(1)==0) { // chua khoi tao
                $oKeepLogin->setStatus(1,1); // duoc phep nhan
            }
            if($oKeepLogin->getStatus(2)==0) { // chua khoi tao
                $oKeepLogin->setStatus(2,1); // duoc phep nhan
            }
            $oKeepLogin->setNumCurrentLogin(2);                        
        } else if($DateNow == '2012-12-26') {
            $NumCurrentLogin = $oKeepLogin->getNumCurrentLogin();
            if($NumCurrentLogin == 2) { // login 2 ngay
                if($oKeepLogin->getStatus(1)==0) { // chua khoi tao
                    $oKeepLogin->setStatus(1,1); // duoc phep nhan
                }
                if($oKeepLogin->getStatus(2)==0) { // chua khoi tao
                    $oKeepLogin->setStatus(2,1); // duoc phep nhan
                }
                if($oKeepLogin->getStatus(3)==0) { // chua khoi tao
                    $oKeepLogin->setStatus(3,1); // duoc phep nhan
                }
                $oKeepLogin->setNumCurrentLogin(3);
            }
        } 
        $oKeepLogin->save();
    }    
    
    
}  

?>
