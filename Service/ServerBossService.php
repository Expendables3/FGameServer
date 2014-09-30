<?php

/**
 * @author AnhBV
 * @version 1.0
 * @created 21-06-2012
 * @Description : thuc hien viec xu ly danh sever boss
 */
 
class ServerBossService
{
    /**
    * create new boss and create new info for user
    * 
    * @param mixed $param
    * @return mixed
    */
    public function  joinRoom($param)
    {
        $BossId = intval($param['BossId']);
        if($BossId < 1 || $BossId > 3)
            return array('Error'=>Error::PARAM);
        
        if(!ServerBoss::checkEventCondition())
            return array('Error'=>Error::EVENT_EXPIRED);
        
        // kiem tra thoi gian join vao boss          
        //Debug::log($conf['BeginTime'].'__'.$conf['EndTime'].'_'.$checkTime);
        $Time = ServerBoss::checkTime();
        if(!$Time)  
            return array('Error'=>Error::EXPIRED); 
            
        $conf = Common::getConfig('ServerBossInfo');
        if(empty($conf))
            return array('Error'=>Error::NOT_LOAD_CONFIG); 
            
        
        $oSeverBoss = ServerBoss::getById(Controller::$uId);
        
        if(!Common::checkCoolDown(1,'JoinLastTime',$oSeverBoss))
            return array('Error'=>Error::NOT_ENOUGH_TIME); 
        
        // kiem tra xem total damage co du dieu kien ko
        $TotalDam = $oSeverBoss->getTotalDamage() ;
        $Id_Boss = 1;
        foreach($conf as $id =>$infoboss)
        {
            if( $TotalDam >= $infoboss['DamageRequire'])
                $Id_Boss = $id;
        }
        Debug::log($TotalDam.'_$Id_Boss'.$Id_Boss.'_$BossId'.$BossId);
        if($Id_Boss != $BossId)
            return array("Error"=>Error::NOT_ENOUGH_LEVEL);
        //$BossId = $Id_Boss ;
        
                
        // reset neu phong join vao ko phai la phong cu
        $keyBoss = date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.$BossId ; 
        
        $result = array()  ;
        $k = false ;
        
        if(!empty($oSeverBoss->BossList))
        {
            $currkey = $oSeverBoss->getCurrentDetailKey();
            if(  $currkey['Day'] != date('Y_m_d',$_SERVER['REQUEST_TIME']) && $currkey['Time'] != $Time)
            {
                //Debug::log('khac ngay');
                $result = $this->getInfoSeverBoss($oSeverBoss);
                if($result['Error'] != Error::SUCCESS)
                    return array('Error' => $result['Error']); 
                $k = true ;
            }else if(  $currkey['Day'] == date('Y_m_d',$_SERVER['REQUEST_TIME']) && $currkey['Time'] == $Time && $currkey['BossId'] == $BossId)
            {
                //Debug::log('cung ngay');
                $result = $this->getInfoSeverBoss($oSeverBoss);
                if($result['Error'] != Error::SUCCESS)
                    return array('Error' => $result['Error']); 
                $k = true ;
            }
            
        }

        
        if(!isset($oSeverBoss->BossList[$keyBoss])) // neu chua vao phong nay lan nao
        {
            // xoa tat ca du lieu
            $oSeverBoss->BossList = array();
            if(!empty($Time) && !empty($BossId))
            {
                // tao boss moi cho user
                if(!$oSeverBoss->addBoss($keyBoss)) 
                {
                    return array('Error'=>Error::CANT_CREATE_BOSS);    
                }
                // tao so lan tung xuc xac free
                $freeDice = Common::getParam('ServerBoss','FreeDice');
                $oSeverBoss->BossList[$keyBoss]['FreeDice'] = $freeDice ;
                $oSeverBoss->BossList[$keyBoss]['FreeSpecialDice'] = $freeDice ;
            }
            
            
            $result1 = $this->getInfoSeverBoss($oSeverBoss);
            if($result1['Error'] != Error::SUCCESS)
                return array('Error' => $result1['Error']); 
            
            if($k)
            {
                $result1['GiftList']= $result['GiftList'];
            }
            $result = $result1 ;
            
        }
        
        $oSeverBoss->save();
        //log
        
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_joinRoom',0,0,$keyBoss,$TotalDam);
           
        //$arr['BossId'] = $Id_Boss;    
        
        $result['Error'] = Error::SUCCESS; 
        $result['BossId']= $Id_Boss;
        //Tra them du lieu ca trong ho
        $oFriend = User::getById(Controller::$uId);              
        $arrSoldier = Lake::getAllSoldier($userId,true,true,true);
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        $SoldierData = array('SoldierList' => $arrSoldier, 'EquipmentList' => $oStoreEquip->SoldierList, 'MeridianList'=>$oStoreEquip->listMeridian);
        $result['SoldierData'] = $SoldierData;
                        
        return $result ; 
 
    }
    
    // random Dice 
    public function randomDice($param) 
    {
        $Type = $param['Type'];
        $PriceType = $param['PriceType'];
        if(empty($Type))
            return array('Error'=>Error::PARAM);   
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);   
            
        $Time = ServerBoss::checkTime();
        if(!$Time)  
            return array('Error'=>Error::EXPIRED); 
            
        $oSeverBoss = ServerBoss::getById(Controller::$uId);
        
                
        // kiem tra thoi gian join vao boss
        $Current = $oSeverBoss->getCurrentDetailKey();
        if(empty($Current)) // kiem tra keyboss ton tai hay ko  
            return array('Error'=>Error::BOSS_NOT_EXIST);
            
        $Time = $Current['Time'];
        $BossId = $Current['BossId'];
        $keyBoss = $Current['KeyBoss'];
        
        $conf = Common::getConfig('ServerBossDice');
        if(empty($conf))
            return array('Error'=>Error::NOT_LOAD_CONFIG);
        
            
        if($Type == 1) // loai thuong
        {
            // check 
            if($oSeverBoss->BossList[$keyBoss]['SpecialDice'] != 0)
                return array('Error'=>Error::NOT_ACTION_MORE);        
            if($oSeverBoss->BossList[$keyBoss]['FreeDice'] <= 0)
            {
                if($PriceType == 'Money')
                {
                     $DiceMoney = $conf[$Type]['Money'];
                     if(!$oUser->addMoney(-$DiceMoney))
                        return array('Error'=>Error::NOT_ENOUGH_MONEY);  
                    
                }
                else
                {
                     $info = '1:ServerBoss_RandomDice:1'; 
                     $DiceXu = $conf[$Type]['ZMoney'];  
                     if(!$oUser->addZingXu(-$DiceXu,$info))
                        return array('Error'=>Error::NOT_ENOUGH_ZINGXU);  
                }
               
            }
            
            // thuc hien random dice
            $DiceNum = rand(1,6); 
            $oSeverBoss->BossList[$keyBoss]['Dice'] = $DiceNum ;
            $oSeverBoss->BossList[$keyBoss]['FreeDice'] -= 1 ;
        }
        else   // loai dac biet
        {
            if($oSeverBoss->BossList[$keyBoss]['Dice'] != 0)
                return array('Error'=>Error::NOT_ACTION_MORE);
                
            if($oSeverBoss->BossList[$keyBoss]['FreeSpecialDice'] <= 0)
            {
                     $info = '2:ServerBoss_RandomDice:2'; 
                     $DiceXu = $conf[$Type]['ZMoney'];  
                     if(!$oUser->addZingXu(-$DiceXu,$info))
                        return array('Error'=>Error::NOT_ENOUGH_ZINGXU);               
            }
            
            // thuc hien random dice
            $DiceNum = rand(1,6); 
            $oSeverBoss->BossList[$keyBoss]['SpecialDice'] = $DiceNum ;
            $oSeverBoss->BossList[$keyBoss]['FreeSpecialDice'] -= 1 ;
        }
        
         
        
        $result = array();
        $result['DiceNum'] = $DiceNum ;
        $result['Error'] = Error::SUCCESS ;
 
        $oSeverBoss->save();
        $oUser->save();
        
        //log
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_randomDice',-$DiceMoney,-$DiceXu,$keyBoss,$DiceNum,$Type);
        return $result ;    
    }
    
    
    // get all info of BossSever
    
    public function getInfoSeverBoss($oSeverBoss)
    {
        if(!is_object($oSeverBoss))
            $oSeverBoss = ServerBoss::getById(Controller::$uId);  
        
        if(empty($oSeverBoss->BossList))
            return array('Error'=>Error::BOSS_NOT_EXIST);

        $result = array();
        $result['UserInfo'] = array();
        foreach($oSeverBoss->BossList as $keyBoss => $info_arr)
        {
            
            $result['KeyBoss']  = $keyBoss;

            $bossInfo = DataRunTime::get('BossInfo'.$keyBoss,true); 
            if(is_array($bossInfo) && !empty($bossInfo))
                $result['Vitality']     = $bossInfo['Vitality']; 

            $BossLastHit = DataRunTime::get('LastHit'.$keyBoss,true); 
            if($BossLastHit)
                $result['LastHit']     = $BossLastHit; 
            else
                $result['LastHit'] = 0 ;
                
            $mb_TopUser = DataRunTime::get('TopUser'.$keyBoss,true); 
            
            if(is_array($mb_TopUser) && !empty($mb_TopUser))
                $result['TopUser']      = $mb_TopUser ;
            else
                $result['TopUser']      = array() ;
                
            // update lai vi tri user
            
            foreach($result['TopUser']  as $order =>$arrtop)
            {
                if($arrtop['UserId'] == Controller::$uId)
                {
                    //Debug::log('gan lai vi tri'.$order); 
                    $oSeverBoss->BossList[$keyBoss]['Position'] = $order ;
                    //Debug::log('vi tri'.$oSeverBoss->BossList[$keyBoss]['Position']); 
                    break;
                }
                else
                {
                    $oSeverBoss->BossList[$keyBoss]['Position'] = -1 ;//if = -1  => over 100
                }
            }
             $result['Position'] = $oSeverBoss->BossList[$keyBoss]['Position'] ;     
            //          
           
                        
            if($bossInfo['IsDie'] || !ServerBoss::checkTime($keyBoss)) // boss da chet OR  thua boss
            {
                if(!$oSeverBoss->BossList[$keyBoss]['IsGetBonus'])
                {
                    if($oSeverBoss->BossList[$keyBoss]['DamTotal'] >0 )
                    {
                         $result['GiftList'] = $oSeverBoss->updateGiftFromConfig($keyBoss);   
                    }
                    $oSeverBoss->BossList[$keyBoss]['IsGetBonus'] = true ;
                    $result['IsGetBonus'] = true ;
                    // back up top user
                    $oSeverBoss->backupDatabaseTopUser($keyBoss);
                                        
                    // len ti vi
                    
                    $ex = explode('_',$keyBoss);
                    $BossId  = intval($ex[4]);   
                     
                    if(!empty($result['TopUser']))
                    {
                        DataProvider::getMemcache()->set('SB_TopUser',$result['TopUser'][1]['UserName']);
                        DataProvider::getMemcache()->set('SB_TopUser_Boss',$BossId);
                        //DataProvider::getMemcache()->set('SB_TopUser',$mb_TopUser[1]['UserId']);
                    }
                    
                    if($BossLastHit)
                    {
                        $oUserLastHit = User::getById($BossLastHit);

                        if(is_object($oUserLastHit))
                        {
                            $LastHitName = $oUserLastHit->getUserName();
                            DataProvider::getMemcache()->set('SB_LastHit',$LastHitName);
                            DataProvider::getMemcache()->set('SB_LastHit_Boss',$BossId);  
                            //DataProvider::getMemcache()->set('SB_LastHit',$BossLastHit);
                        }
                    }
                    //------------  
                    
                }

            } 
            //Zf_log::write_act_log(Controller::$uId,0,20,'sb_getInfoSeverBoss',0,0,$keyBoss);
            
            $result['UserInfo'] = $oSeverBoss->BossList[$keyBoss] ;
                   
            break ;
        }
        
        $oSeverBoss->save();  
        $result['Error']= Error::SUCCESS ;
        return $result ; 
        
    }
    
    public function attackBoss($param)
    {           
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
                    
        $oSeverBoss = ServerBoss::getById(Controller::$uId);  
            
        // kiem tra xem total damage co du dieu kien ko
        $TotalDam = $oSeverBoss->getTotalDamage() ;
        $Id_Boss = 1;
        
        $conf = Common::getConfig('ServerBossInfo');    
        if(empty($conf))
            return array('Error'=>Error::NOT_LOAD_CONFIG); 
        
        foreach($conf as $id =>$infoboss)
        {
            if( $TotalDam >= $infoboss['DamageRequire'])
                $Id_Boss = $id;
        }
            
        $Current = $oSeverBoss->getCurrentDetailKey();
        if(empty($Current)) // kiem tra keyboss ton tai hay ko  
            return array('Error'=>Error::BOSS_NOT_EXIST);
        //Debug::log('att1');    
        $Day        = $Current['Day'] ;        
        $Time       = $Current['Time'];
        $BossId     = $Current['BossId'];
        $keyBoss    = $Current['KeyBoss'];
        
         Debug::log('$TotalDam'.$TotalDam.'_$Id_Boss:'.$Id_Boss.'_'.$BossId);    
        if($Id_Boss != $BossId)
            return array('Error'=>Error::NOT_ENOUGH_LEVEL); 
            
         
        // kiem tra thoi gian join vao boss
        if(!ServerBoss::checkTime($keyBoss))  
            return array('Error'=>Error::EXPIRED); 

        // kiem tra boss chet chua
        $bossInfo = DataRunTime::get('BossInfo'.$keyBoss,true); 
        if(empty($bossInfo))
        {   
            return array('Error'=>Error::ARRAY_NULL);
        }
 
        
        if(empty($keyBoss))
            return array('Error'=>Error::BOSS_NOT_EXIST);
            
        $result_arr= array();
                             
        if($bossInfo['IsDie'] || !ServerBoss::checkTime($keyBoss)) // boss da chet or thua boss
        {
        }
        else
        {
            
            // kiem tra xem user da du thoi gian hoi sinh ko 
            if(!$oSeverBoss->checkWaitingTime($keyBoss))
                return array('Error'=>Error::NOT_ENOUGH_TIME);
                
                
            $oSeverBoss->BossList[$keyBoss]['AttackNum'] += 1; 
            // tao scene
            
            // kiem tra soldier 
            $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
                //Debug::log($arrSoldier);     
                //Debug::log('att5');     
            $conf_rank = Common::getConfig('RankPoint');  
            $listSoldierAtt = array();
            // check dieu kien ngu thu truoc khi danh 
            foreach($arrSoldier as $idLake => $arrLake)
            {
                foreach($arrLake as $idFish => $oSoldier)    
                {
                    
                    // check enough health         
                    //$HealthSoldier = $oSoldier->getHealth();
                    $StatusSoldier = $oSoldier->Status;

                    /*if ($HealthSoldier < $conf_rank[$oSoldier->Rank]['AttackPoint']) 
                        continue ;
                        Debug::log('u1');     */  
                    // check trang thai cua ca
                    if( $StatusSoldier != SoldierStatus::HEALTHY)
                        continue ; 
                    $listSoldierAtt[$oSoldier->Id] = $oSoldier ; 
                    //Debug::log('nhan'.$idFish);
                }
            }

            if(empty($listSoldierAtt))
                return array('Error' => Error :: NO_FISH);

            // tao boss
            $oBoss = $oSeverBoss->createMonster($BossId);

            if(!is_object($oBoss))
                return array('Error'=>Error::OBJECT_NULL); 
                
            $Sql_Vitality_Result = $oSeverBoss->sql_getVitalityBoss($Day,$Time,$BossId);

            if($Sql_Vitality_Result['Error'] != Error::SUCCESS)
                return array('Error'=>$Sql_Vitality_Result['Error']); 
                
            $oBoss->Vitality = intval($Sql_Vitality_Result['VitalityTotal']);

            $conf_Boss = $conf[$BossId];//Common::getConfig('ServerBossInfo',$Time,$BossId);
            if(empty($conf_Boss))
                return array('Error'=>Error::NOT_LOAD_CONFIG); 
                
            $Rate = $oSeverBoss->getDiceRate($keyBoss); 
            
            $result_batter = $oSeverBoss->generateScene($listSoldierAtt,$oBoss,0,$conf_Boss['Alpha'],$Rate);

            //Debug::log($Sql_Vitality_Result['VitalityTotal']);
            //Debug::log('$result_batter---------------------');
            //Debug::log($result_batter['DamageTotal']);
            //exit ;
            if($result_batter['DamageTotal'] > 0)
            {
                if(!$oSeverBoss->updateAttackBoss($Day,$Time,$BossId,$result_batter['DamageTotal']))
                    return array('Error'=>Error::CANT_UPDATE_DATA); 
            }
                
            

            // update lai Dice 
            $oSeverBoss->BossList[$keyBoss]['Dice'] = 0 ;
            $oSeverBoss->BossList[$keyBoss]['SpecialDice'] = 0 ;

            //update last time attack boss
            $oSeverBoss->BossList[$keyBoss]['LastTimeAttack'] = $_SERVER['REQUEST_TIME'] ;

            // roi qua normal
            $result = $oSeverBoss->getSeverBossGift($BossId,'NormalBonus');
            $oUser->saveBonus($result['NormalBonus']);

            // update equipment durability
            foreach($listSoldierAtt as $idF => $oSoldier)
            {
                $oSoldier->updateDurability();      
            }
            
            
        }
        
        $result_arr = $this->getInfoSeverBoss($oSeverBoss);
        
        // qua khi danh quai
        $result_arr['Bonus'] = $result['NormalBonus'];
        
        if($result_arr['Error'] != Error::SUCCESS)
                return array('Error' => $result_arr['Error']);
                
        $BossLastHit = DataRunTime::get('LastHit'.$keyBoss,true); 
        if($BossLastHit == Controller::$uId)
            $result_arr['LastHit'] = true ;
        else 
            $result_arr['LastHit'] = false;    
            
        $result_arr['Scene'] = $result_batter['Scene'];           
        $result_arr['DamageTotal'] = $result_batter['DamageTotal'];           
        
        $result_arr['Error'] = Error::SUCCESS;  
        
        $oSeverBoss->save();
        $oUser->save();
        
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_attackBoss',0,0,$keyBoss,$result_arr['DamageTotal'],$result_arr['LastHit'],$result_arr['Position']);
        
        return $result_arr ;         
        
    }
    
    
     // get all info of BossSever
    
    public function getAndSaveBonus()
    {
          
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
        return array('Error'=>Error::NO_REGIS);
         
        $oSeverBoss = ServerBoss::getById(Controller::$uId);  

        if(empty($oSeverBoss->BossList))
            return array('Error'=>Error::BOSS_NOT_EXIST);
            

        $result = array();
        
        foreach($oSeverBoss->BossList as $keyBoss => $info_arr)
        {
            if(empty($info_arr['Bonus']))
                return array('Error'=>Error::NOT_GIFT); 
                
            $oSeverBoss->saveBonus($info_arr['Bonus']); 
            unset($oSeverBoss->BossList[$keyBoss]);
            break ;
        }
        $oSeverBoss->save();
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_getAndSaveBonus',0,0,$keyBoss);
        return array('Error'=>Error::SUCCESS);
        
    }
    
    // get thong tin cua 3 boss o time hien tai 
    
    public function getVitalityAllBoss()
    {
        if(!ServerBoss::checkEventCondition())
            return array('Error'=>Error::EVENT_EXPIRED);
            
        
         
        $result = array();   
        $result['RoomGift'] = 0 ;
        $oSeverBoss = ServerBoss::getById(Controller::$uId);
        if(is_object($oSeverBoss))
        {
            foreach($oSeverBoss->BossList as $keyBoss => $info_arr)
            {
                if($info_arr['IsGetBonus']== true)
                {
                    unset($oSeverBoss->BossList[$keyBoss]);
                    $result['RoomInfo'] = 0;
                }
                else
                {
                    $result     = $this->getInfoSeverBoss($oSeverBoss);
                    $RoomInfo   = $oSeverBoss->getCurrentDetailKey() ;
                    $result['RoomInfo'] = $RoomInfo['BossId'];
                    $result['RoomGift'] = $RoomInfo['BossId'];
                    if($result['IsGetBonus'])
                    {
                        $result['RoomInfo'] = 0;
                    }
                }
                
                break ;
                
            } 
            
                       
        }
        // tra ve thong tin 3 boss neu trong thoi gian danh  
        $Time = ServerBoss::checkTime();
        if($Time) 
        {
            $keyBoss = array(
            1=> date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.'1' , 
            2=> date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.'2' , 
            3=> date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.'3' ,
            ) ;  
            foreach($keyBoss as $index => $key)
            {
            $bossInfo = DataRunTime::get('BossInfo'.$key,true); 
            if(is_array($bossInfo) && !empty($bossInfo))
               $result['BossList'][$index]    = intval($bossInfo['Vitality']); 
            }
            
        } 
                 
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_getVitalityAllBoss',0,0,$keyBoss,intval($result['IsGetBonus']));      
    
        
        $oSeverBoss->save();
        $result['Error'] = Error::SUCCESS;
        return $result;
    }
    
    
    // get thong tin cua 3 boss o time hien tai 
    
    public function speedUpTime($param)
    {
        $PriceType  = $param['PriceType'];
        if(empty($PriceType))
            return array('Error'=>Error::PARAM); 
        
        if(!ServerBoss::checkEventCondition())
            return array('Error'=>Error::EVENT_EXPIRED);
            
        $Time = ServerBoss::checkTime();
        if(!$Time)  
            return array('Error'=>Error::EXPIRED); 
        
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
            
        $oldZmoney = $oUser->ZMoney; 
        $oldmoney = $oUser->Money; 
        $result = array();   
        $oSeverBoss = ServerBoss::getById(Controller::$uId);
        if(is_object($oSeverBoss))
        {
            foreach($oSeverBoss->BossList as $keyBoss => $info_arr)
            {
                $AttackNum = $info_arr['AttackNum']; 
                $conf_time = Common::getConfig('ServerBossTime');
                $AttackNum = ($AttackNum > count($conf_time))?count($conf_time):$AttackNum ;
                $conf_time = $conf_time[$AttackNum];                
                if(empty($conf_time))
                    return array('Error'=>Error::NOT_LOAD_CONFIG);
                // tru tien user
                if($PriceType == Type::ZMoney)
                {
                     $info = "$AttackNum:speedUpTime:1";
                     if (!$oUser->addZingXu(-$conf_time['ZMoney'],$info))
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);  
                }
                else
                {
                    if (!$oUser->addMoney(-$conf_time['Money']))
                        return array('Error' => Error::NOT_ENOUGH_MONEY);  
                }

                // reduce waitingTime
                $oSeverBoss->BossList[$keyBoss]['LastTimeAttack'] -= intval($conf_time['Time']) ; 
                break;
            }            
        } 
        
        $difXu      = $oUser->ZMoney - $oldZmoney; 
        $difMoney   = $oUser->Money  - $oldmoney ; 
              
        Zf_log::write_act_log(Controller::$uId,0,20,'sb_speedUpTime',$difMoney,$difXu,$keyBoss,intval($AttackNum));   
        
        $oSeverBoss->save();
        $result['Error'] = Error::SUCCESS;
        return $result;
    }
  
  
  
}

