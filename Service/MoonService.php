<?php

class MoonService extends Controller
{    
    // ham mua cac mui ten chi duong (arrow)
    public function buyArrow($param)
    {
        $ArrowId = intval($param['ArrowId']);
        $PriceType =  $param['PriceType'];
        if($ArrowId < 1 || $ArrowId > 5)
            return array('Error'=>Error::PARAM);
        
        $oStore = Store::getById(Controller::$uId);
        $oUser  = User::getById(Controller::$uId);
        if(!is_object($oStore)||!is_object($oUser))
            return array('Error'=>Error::OBJECT_NULL);
        $conf = Common::getConfig('Arrow',$ArrowId);
        if(!is_array($conf))
            return array('Error'=>Error::NOT_LOAD_CONFIG);
            
        // check level 
        if($oUser->Level < $conf['LevelRequire'] )
            return array('Error' => Error::NOT_ENOUGH_LEVEL);     

        // check unlock type
        if($conf['LevelRequire'] == 5 || $conf['LevelRequire'] == 6 )
        return array('Error' => Error::TYPE_INVALID);
        
        if(!Event::checkEventCondition('PearFlower'))
        {
             return array('Error' => Error::EXPIRED);    
        }
        
        // luu thong so cu~
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
      
        // check money
        if ($PriceType == Type::Money)
        {
          //if (!$oUser->addMoney(-$conf['Money']))
          return array('Error' => Error::NOT_ENOUGH_MONEY);
        }
        else
        {
          $info = $ArrowId.':'.'BuyArrow'.':1' ;
          if (!$oUser->addZingXu(-$conf['ZMoney'],$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }
        
        $oStore->addEventItem(EventType::PearFlower,'Arrow',$ArrowId,1); 
        
        // log
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        
        Zf_log::write_act_log(Controller::$uId,0,23,'buyArrow',$moneyDiff,$zMoneyDiff,$ArrowId); 
          
        $oUser->save();
        $oStore->save();
        return array('Error' => Error::SUCCESS);   
        
    }
    
    // ham thuc hien viec tung xuc xac
    public function randomDice($param)
    {
        $isTwoDice = $param['isTwoDice'] ;
        $ArrowId = intval($param['ArrowId']);
        
        if($ArrowId < 1 || $ArrowId > 4)
            return array('Error'=>Error::PARAM);
        
        $oStore = Store::getById(Controller::$uId);
        $oUser  = User::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId);
        if(!is_object($oStore)||!is_object($oUser)||!is_object($oEvent))
            return array('Error'=>Error::OBJECT_NULL);
        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EXPIRED);
            
        if(!$oStore->useEventItem(EventType::PearFlower,'Arrow',$ArrowId,1))   
        {
            return array('Error'=>Error::NOT_ENOUGH_ITEM);
        }
        
        if(!is_object($oEvent->EventList['PearFlower']['Object']))
            return array('Error'=>Error::OBJECT_NULL);
                    
        // kiem tra xem ko trong trang thai loc xoay
        if($oEvent->EventList['PearFlower']['Object']->RoadState == RoadState::TORNADO)
        {
            // kiem tra xem da nhan qua o cell nay chua 
            //check xem toa do nay da nhan qua chua 
            if($oEvent->EventList['PearFlower']['Object']->checkHistory($oEvent->EventList['PearFlower']['Object']->Position))
            {
                return array('Error'=>Error::BEING_TORNADO);     
            }
            else
            {
                $oEvent->EventList['PearFlower']['Object']->updateRoadState(RoadState::NORMAL);
            }
   
        }

        // kiem tra xem da tung xuc xac chua ?
        if($oEvent->EventList['PearFlower']['Object']->RoadState != RoadState::NORMAL)
            return array('Error'=>Error::NOT_ACTION_MORE);
                
        // kiem tra xem no xuc xac co dung huong ko
        $result = $oEvent->EventList['PearFlower']['Object']->checkRoad($ArrowId,1);   
        if($result['Step'] <= 0)
        {
            return array('Error'=>Error::NOT_ACTION_MORE);   
        }
          
        $result = array();
        if($isTwoDice)
        {
            $xu = Common::getParam('DiceXu');
            $info = '2:'.'randomDice'.':2' ;
            if (!$oUser->addZingXu(-$xu,$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
            
            $result['Num'] =  mt_rand(1,6);
            
            $result['Num2'] =  mt_rand(1,6);
            
            //Zf_log::write_act_log(Controller::$uId,0,23,'randomDice',0,-$xu); 
        }
        else
        {
            $result['Num'] =  mt_rand(1,6);    
        }
        
        if($result['Num'] == 6)
        {
            $oStore->addEventItem(EventType::PearFlower,Type::Arrow,$ArrowId,1);
        }
        
        if($result['Num2'] == 6 )
        {
            $oStore->addEventItem(EventType::PearFlower,Type::Arrow,$ArrowId,1);
        }
        
        $Num = $result['Num'] + $result['Num2'] ;
                        
        $oEvent->EventList['PearFlower']['Object']->Dice = array('Arrow'=>$ArrowId, 'Num'=>$Num) ;
        $oEvent->EventList['PearFlower']['Object']->updateRoadState(RoadState::DICE);
        $oUser->save();
        $oStore->save();
        $oEvent->save() ;
        
        $result['Error'] = Error::SUCCESS ; 
        
        return $result ;

    }
    
    public function goGo($param)
    {
        $NumStep    = $param['NumStep'];
        
        if($NumStep < 1)
            return array('Error'=>Error::PARAM);
        
        $oUser  = User::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId); 
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error'=>Error::NO_REGIS);        

        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EXPIRED);
            
        $ePearFlower    = $oEvent->getEvent('PearFlower');
        $oPear          = $ePearFlower['Object'] ;
        
        // kiem tra xem da tung xuc xac chua ?
        if($oPear->RoadState != RoadState::DICE)
            return array('Error'=>Error::NOT_DICE);
        if($NumStep > $oPear->Dice['Num'])
        {
            return array('Error'=>Error::OVER_NUMBER);   
        }
        // kiem tra xem co di duoc tung ay o ko ?
        $arr_result = $oPear->checkRoad($oPear->Dice['Arrow'],$NumStep) ;
                
        if($arr_result['Step'] == 0 )
        {
            return array('Error'=>Error::WRONG_ROAD);   // ko the di duoc
        }
        $New_Position = $arr_result['Position']; 
        
        // sau khi di xong xoa phan Dice di
        $oPear->Dice = array(); 
        
        //check xem toa do nay da di qua chua 
        if(!$oPear->checkHistory($New_Position))
        {
            // da di qua roi thi ko lay qua nua 
            $oPear->updateRoadState(RoadState::NORMAL);     
        }
        else
        {
            $oPear->updateRoadState(RoadState::GOING);  
        }
        
        //doan check truong hop ko co key khi di toi o cuoi 
        $mapId = $oPear->MapId ;
        $Mapconf = Common::getConfig('Map',$mapId);
        $cellStatus = $Mapconf[$New_Position['Y']][$New_Position['X']];
        
        if($cellStatus == CellStatus::CELL_END || $cellStatus == CellStatus::CELL_SPECIAL_TREASURE ) 
        {
            // kiem tra so chia khoa
            $oStore = Store::getById(Controller::$uId);

            if($oPear->MazeKeyInfo['Num'] <= 0 && $oStore->Items['Arrow'][5] <= 0)
            {
                $oPear->updateRoadState(RoadState::NORMAL); 
            }
        }
        
        //update
        $oPear->updatePosition($New_Position);  
       
        $oEvent->save();
        $result['New_Position'] = $New_Position ; 
        $result['Error'] = Error::SUCCESS ; 
        return $result ;
    }
    
    public function eventOnRoad()
    {
                
        $oUser  = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
                   
        $oStore = Store::getById(Controller::$uId); 
        $oEvent = Event::getById(Controller::$uId); 
        if(!is_object($oStore)||!is_object($oEvent))
            return array('Error'=>Error::OBJECT_NULL);        

        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EVENT_EXPIRED);
        
        $ePearFlower    = $oEvent->getEvent('PearFlower');
        $oPear          = $ePearFlower['Object'] ;

        $mapId = $oPear->MapId ;
        $Mapconf = Common::getConfig('Map',$mapId);
        $Now_Position =  $oPear->Position ;
        $cellStatus = $Mapconf[$Now_Position['Y']][$Now_Position['X']];
        
        //check xem toa do nay da nhan qua chua 
        if(!$oPear->checkHistory($Now_Position))
        {
            return array('Error'=>Error::GOT_GIFT);    
        }
   
        $result = array();
        $result['CellStatus'] = $cellStatus ;
        //$result['New_Position'] = $New_Position ;
        if($cellStatus == CellStatus::CELL_END) 
        {
            // kiem tra so chia khoa
            if($oPear->MazeKeyInfo['Num'] <= 0)
            {
                if(!$oStore->useEventItem(EventType::PearFlower,Type::Arrow,5,1))
                    return array('Error'=>Error::NOT_ENOUGH_ITEM);
            }
            else
            {
                $oPear->MazeKeyInfo['Num'] -= 1 ;
            }

            $result['Gift'] = $oPear->getFinishRewards() ; 
             
            // luu qua vao kho
            $oStore->saveRewardsToStore($result['Gift']);
            $oStore->saveRewardsToStore($oPear->Revards);
            
            // luu thong tin map da di qua
            $oEvent->EventList['PearFlower']['MapOver'][$mapId] = $mapId ;
            // xoa toan bo thong tin map
            $oEvent->a_cleanMap();
        }
        else if($cellStatus == CellStatus::CELL_SPECIAL_TREASURE)
        {
            // kiem tra so chia khoa
            if($oPear->MazeKeyInfo['Num'] >0)
            {
                // kho bau 
                $result['Gift']= $oPear->getSpecialTreasureRewards();
                $oPear->saveRevards($result['Gift']) ; 
                $oPear->RoadState = RoadState::NORMAL ;
                
                $oPear->MazeKeyInfo['Num'] -= 1 ;  
                
                // luu lai duong da di qua 
                $oPear->saveHistory($Now_Position);

            }
            else if($oStore->useEventItem(EventType::PearFlower,Type::Arrow,5,1))
            {
                // kho bau 
                $result['Gift']= $oPear->getSpecialTreasureRewards();
                $oPear->saveRevards($result['Gift']) ; 
                $oPear->RoadState = RoadState::NORMAL ;
                
                // luu lai duong da di qua 
                $oPear->saveHistory($Now_Position);
            }
                        
            
            
        }
        else
        {
            $result =  $oPear->getcontentCell($cellStatus,$Now_Position);  
            // luu lai duong da di qua 
            $oPear->saveHistory($Now_Position);
        }
        $oEvent->save();
        $oStore->save();
        $oUser->save() ;
        
        $result['Error'] = Error::SUCCESS ;
        /*
        // log 
        if($cellStatus == CellStatus::CELL_END) 
        {
            Zf_log::write_act_log(Controller::$uId,0,21,'finishMap'); 
        }*/
        return $result ;
    }
    
           
    // ham thuc hien viec tra loi cau hoi // bao gom tra loi nhanh luon
    public function answer($param)
    {
        $AnswerId =  $param['AnswerId'];
        $isQuick  =  $param['isQuick'];

        $oEvent = Event::getById(Controller::$uId); 
        $oUser  = User::getById(Controller::$uId);    
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);      

        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EVENT_EXPIRED);
        
        $ePearFlower    = $oEvent->getEvent('PearFlower');
        $oPear          = $ePearFlower['Object'] ;    

        // kiem tra xem dang o trang thai cau hoi hay ko
        if($oPear->RoadState != RoadState::ANSWER)
            return array('Error'=>Error::ACTION_NOT_AVAILABLE); 
        $questId = $oPear->Question ;
        $conf_question = Common::getConfig('Question',$questId) ;
        
        $result = array();
        
        $oPear->updateRoadState(RoadState::NORMAL);  
        
        $question = true ;
        if($isQuick) // tra loi nhanh
        {
            // check xu 
            $Info = '1:answer:'.$questId ;
            if(!$oUser->addZingXu(-$conf_question['XuPay'],$Info))
                return array('Error'=>Error::NOT_ENOUGH_ZINGXU); 
            
            Zf_log::write_act_log(Controller::$uId,0,23,'answer',0,-$conf_question['XuPay'],0); 
                 
        }
        else
        {
            if(empty($AnswerId))
                return array('Error'=>Error::PARAM);
            if(!$conf_question['Answer'][$AnswerId]['Status'])
            {
                // tra loi sai
                $New_Position = $oPear->tornado();
                $oPear->updatePosition($New_Position);
                
                // kiem tra xem da nhan qua o cell nay chua 
                if($oPear->checkHistory($New_Position))
                {
                    $oPear->updateRoadState(RoadState::TORNADO);
                }
                else
                {
                    $oPear->updateRoadState(RoadState::NORMAL);
                }
        
                $result['New_Position'] = $New_Position ;
                $question = false ;
            }
        }
        
        // nhan qua
        if($question)
        {
            $result['Gift'] = $conf_question['Gift'];
            foreach($conf_question['Gift'] as $gift)
            {
                $arr_gift['NormalGift'] = $gift ;
                $oPear->saveRevards($arr_gift) ; 
            }
        }

                
        // xoa cau hoi 
        $oPear->Question = 0 ;
        
        $oEvent->save();
        $oUser->save();
        
        $result['Error'] = Error::SUCCESS ;
        
        
        return $result ;  
    }
    
    
    // service thuc hien viec choi lan nua 
    public function nextMap($param)
    {
        $isFirstLogin   = $param['isFirstLogin'];
        $PriceType      = $param['PriceType'];
                            
        $oEvent     = Event::getById(Controller::$uId);
        $oUser      = User::getById(Controller::$uId);    
        if(!is_object($oEvent) || !is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
            
        // kiem tra event
        if(!Event::checkEventCondition(EventType::PearFlower))
            return array('Error'=>Error::EVENT_EXPIRED);
            
        $moneyDiff = $oUser->Money ;
        $zMoneyDiff = $oUser->ZMoney;
        if($isFirstLogin)
        {
            
            // kiem tra xem no da reset trong ngay hay chua 
            $today      = date('Ymd',$_SERVER['REQUEST_TIME']);
            $LastReset  = date('Ymd',$oEvent->EventList[EventType::PearFlower]['CreateMapTime']);
                       
            if($today == $LastReset)
            {
                return array('Error'=>Error::NOT_ACTION_MORE); 
            }
            $oEvent->EventList[EventType::PearFlower]['JoinNum'] = 0 ;
            
            // luu it qua tang cho user
            if(is_object($oEvent->EventList[EventType::PearFlower]['Object']))
            {
                $NumExp = $oEvent->EventList[EventType::PearFlower]['Object']->getFailRewards();
                $oUser->addExp(round($NumExp));
            }
            $oEvent->a_creatNewMap($isFirstLogin);
            
            // tang user mui ten 
            $bonus = Common::getParam('ArrowBonus');
            $oUser->saveBonus($bonus);           
            
        }
        else
        {
            // kiem tra xem da di het map truoc chua
            if(!empty($oEvent->EventList['PearFlower']['Object']))
            {
                return array('Error'=>Error::NOT_ACTION_MORE); 
            }
            // gioi han di bao nhieu map 1 ngay
            
            $numonday = $oEvent->EventList['PearFlower']['JoinNum'] + 1 ;
            
            $conf_Fresh = Common::getConfig('RefreshRockMazeMap');
            
            if($numonday >= count($conf_Fresh))
                return array('Error'=>Error::OVER_NUMBER); 
            
            $cooldown = Common::getParam('MapCoolDown');
            $NowTime  = $_SERVER['REQUEST_TIME'];
            $LastResetTime  = $oEvent->EventList['PearFlower']['CreateMapTime'];
            if($LastResetTime + $cooldown < $NowTime)
            {
                $oEvent->a_creatNewMap(false);
            }
            else
            {
                if (empty($PriceType)|| $PriceType != 'ZMoney')
                    return array('Error'=>Error::PARAM);
                    
                $Price = $conf_Fresh[$numonday]['ZMoney'] ;
                if($PriceType == 'ZMoney')
                {
                    // check xu
                    
                    $info = '2:'.'refreshMap'.':2' ;
                    if (!$oUser->addZingXu(-$Price,$info))
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);
                }
                /*else
                {
                    // money
                    $Price = Common::getParam('RefreshMap');
                    if (!$oUser->addMoney(-$Price['Money'],'Event'))
                        return array('Error' => Error::NOT_ENOUGH_MONEY);
                }*/
                $oEvent->a_creatNewMap(false);
            }
            

            
        }
        // them vao so lan choi trong ngay 
        $oEvent->EventList['PearFlower']['JoinNum'] +=1 ;
        $oEvent->save();
        $oUser->save();
        
        // log
        
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;

        Zf_log::write_act_log(Controller::$uId,0,23,'nextMap',$moneyDiff,$zMoneyDiff); 
        
        $arr = array();
        $arr['Exp']  = $NumExp ; 
        $arr['Error']   = Error::SUCCESS ;
        $arr['NewMap']  = $oEvent->EventList['PearFlower']['Object'];
        
        return  $arr ;
        
    }
    
    public function teleport($param)
    {
        $newPostion = $param['Postion'] ;
        
        if (empty($newPostion))
            return array('Error'=>Error::PARAM);
              
        $oEvent   = Event::getById(Controller::$uId);
        $oUser      = User::getById(Controller::$uId);    
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
        
        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EVENT_EXPIRED);
         
        $ePearFlower    = $oEvent->getEvent('PearFlower');
        $oPear          = $ePearFlower['Object'] ;    
        
        if(!is_object($oPear))   
        return array('Error'=>Error::OBJECT_NULL);   
        
        // kiem tra so xu 
        $conf_arrow = Common::getConfig(Type::Arrow,6);
        $Info = '1:teleport:4' ;
        if(!$oUser->addZingXu(-$conf_arrow['ZMoney'],$Info))
            return array('Error'=>Error::NOT_ENOUGH_ZINGXU);
            
        // kiem tra cac trang thai duoc teleport
        if($oPear->RoadState != RoadState::NORMAL)
            return array('Error'=>Error::NOT_ACTION_MORE);
            
        // chi teleport 3 lan
        if($oPear->TeleportNum >= 3)
            return array('Error'=>Error::NOT_ACTION_MORE);
            
        // kiem tra vung di chuyen toi
        $PostionInMap = Common::getConfig('Map',$oPear->MapId);
        $PostionInMap = $PostionInMap[$newPostion['Y']][$newPostion['X']] ;
        if(!isset($PostionInMap))
            return array('Error'=>Error::WRONG_ROAD);        
        if(($newPostion["Y"] > $oPear->Position['Y']+ 5) 
        || ($newPostion["Y"] < $oPear->Position['Y'] - 5)
        || ($newPostion["X"] > $oPear->Position['X']+ 5) 
        || ($newPostion["X"] < $oPear->Position['X'] - 5))
            return array('Error'=>Error::WRONG_ROAD);
        // update lai vi tri moi va trang thai moi
        $oPear->updatePosition($newPostion);
        
        $oPear->updateTeleport();
        // save 
        $oEvent->save();
        $oUser->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'buyTeleport',0,-$conf_arrow['ZMoney']); 
        // return 
        
        return array('Error'=>Error::SUCCESS);         
        
    }
     
    // auto di tren map
    public function autoPlay($param)
    {
        $Type    = intval($param['Type']);
        
        if($Type < 1)
            return array('Error'=>Error::PARAM);
        
        $oUser  = User::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId); 
        if(!is_object($oUser)||!is_object($oEvent))
            return array('Error'=>Error::NO_REGIS);        

        // kiem tra event
        if(!Event::checkEventCondition('PearFlower'))
            return array('Error'=>Error::EXPIRED);
            
        $ePearFlower    = $oEvent->getEvent('PearFlower');
        $oPear          = $ePearFlower['Object'] ;
        if(empty($oPear))
            return array('Error'=>Error::OBJECT_NULL);
            
        // tru xu cua user
        $AutoMap = Common::getParam('AutoMap');
        $zMoney = intval($AutoMap[$Type]);
        $info = $Type.':autoPlay:1';
        if(!$oUser->addZingXu(-$zMoney,$info))
           return array('Error'=>Error::NOT_ENOUGH_ZINGXU); 

        $arr = array();
        // get qua cho user
        $arr = $oEvent->a_getGiftOfAutoPlay($Type);
        
        // luu qua
        //$oStore = Store::getById(Controller::$uId) ;
        //$oStore->saveRewardsToStore($arr);
        
        // reset map va cong so lan qua map 
        $oEvent->a_cleanMap();
                
        $oEvent->save();
        $oUser->save();
        //$oStore->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'autoPlay',0,-$zMoney,$Type);
        $arr['Error']= Error::SUCCESS ;
        
        return $arr ;
    }
        
};
?>
