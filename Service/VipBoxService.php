<?php
class VipBoxService
{
    public function getVipBox() {
        $oVipBox = VipBox::getById(Controller::$uId);        
        $ret = $oVipBox->getVipBox();        
        $ret['Error'] = Error::SUCCESS;
        return $ret;        
    }   
    
    public function vbOpenBox($params) {
        if(!Event::checkEventCondition(EventType::EventActive))
            return Common::returnError(Error::EVENT_EXPIRED);        
        $PriceType = "ZMoney";        
        $ByZMoney = intval($params['ByZMoney']); // 0 or 1
        $oVipBox = VipBox::getById(Controller::$uId);
        $VBBonusConf = Common::getConfig('VipBox_Bonus');
        $RequireNumKey = intval($VBBonusConf['Price']['Num']);
        if($ByZMoney == 1) {
            $NumOpen = intval($oVipBox->getNumOpen());
            if($NumOpen <0) $NumOpen = 0;
            $NumOpen++;        
            $OpenConf = Common::getConfig('VipBox_Item');
            if($NumOpen > count($OpenConf)) {
               $NumOpen = count($OpenConf); 
            }                        
            $cost = intval($OpenConf[$NumOpen][$PriceType]);
            if($cost <= 0 ) {
               return array('Error' => Error::PARAM); 
            }        
            $info = '1:vbOpenBox_'.$NumOpen.':1';
            $oUser = User::getById(Controller::$uId);
            $zMoneyDiff = $oUser->ZMoney;
            if (!$oUser->addZingXu(-$cost,$info))    
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
            $oVipBox->setNumOpen($NumOpen);    
        } else {
            
            if(!$oVipBox->useNumKey($RequireNumKey)) {
                return array('Error' => Error::NOT_ENOUGH_ITEM);
            }            
        }    
        
        $Gift = $VBBonusConf['Gift'];
        
        $BoxId = Common::pickIndex($Gift);        
        $VBBonusDetailConf = Common::getConfig('VipBox_BonusDetail');        
        $GiftDetail = $VBBonusDetailConf[$BoxId];        
        $gift = Common::pickItem($GiftDetail);   
        $Color = intval($gift['Color']);
        
        $QuotaVipMax = intval(DataRunTime::get('QuotaVipMax',true));
        if($QuotaVipMax >= 100) {
            if($Color == 6) {
               $gift['Color'] = 5;             
               $Color = 5;
            }            
        }                
        if($Color == 6) {
            DataRunTime::set('QuotaVipMax',($QuotaVipMax + 1),true);
        }
        
        // create in store
        $Item = Common::addsaveGiftConfig(array($gift),0,SourceEquipment::EVENT);
               
        if ($ByZMoney == 1) {
            $oUser->save();
        }        
        $oVipBox->save();        
        // Write log
        
        if ($ByZMoney == 1) {
            $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
            if($zMoneyDiff != 0) {
                Zf_log::write_act_log(Controller::$uId,0,23,'vbOpenZMoney', 0, $zMoneyDiff, 'vbOpenBox_'.$NumOpen, $NumOpen, 0,0, 1);     
            } 
        }
        
        Zf_log::write_act_log(Controller::$uId,0,20,'vbOpenKey', 0, 0, $gift['ItemType'], 0, $gift['Rank'], $gift['Color'], 1);    
                
        // Return Info
        $ret = array();
        $ret['Error'] = Error::SUCCESS;
        $ret['Item'] = $Item;                
        $ret['QuotaVipMax'] = $QuotaVipMax;                
        return $ret;                    
    }
     
}  
?>
