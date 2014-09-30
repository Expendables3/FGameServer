<?php
  Class SoldierService
  {
      // unlock Room
      public function unlockRoom()
      {
          $oUser = User::getById(Controller::$uId);
          if (!is_object($oUser))
          {
            return array('Error' => Error :: NO_REGIS) ;
          }
          
          $oTrain = TrainingGround::getById(Controller::$uId);
          
          $conf = Common::getConfig('CustomTraining','Room');
          
          $RoomTotal = count($conf);
          $RoomId = 0 ;
          for($i= 1; $i <= $RoomTotal ;$i++)
          {
              if(!isset($oTrain->Room[$i]))
              {
                  $RoomId = $i ;
                  break;
              }   
          }
          
          // old Money
          $oldMoney = $oUser->Money ;
          $oldZMoney = $oUser->ZMoney ;
          
          // check money
          if(empty($conf[$RoomId]))
            return array('Error' => Error :: NOT_LOAD_CONFIG) ; 
          $info = "1:unlockTrainingRoom:".$RoomId ;
          if(!$oUser->addZingXu(-$conf[$RoomId]['ZMoney'],$info))
            return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
          
          if(!$oTrain->addRoom($RoomId))
            return array('Error' => Error :: PARAM) ;
          
          $oTrain->save();
          $oUser->save();
          
          // new Money
          $diffMoney = $oUser->Money - $oldMoney ;
          $diffZMoney = $oUser->ZMoney - $oldZMoney ;
          
          Zf_log::write_act_log(Controller::$uId,0,20,'unlockRoom',$diffMoney,$diffZMoney,$RoomId);
          
          return array('Error'=>Error::SUCCESS);
          
      }
      
      // start training
      public function startTraining($param)
      {
          $RoomId       = $param['RoomId'];
          $SoldierId    = $param['SoldierId'];
          $LakeId       = $param['LakeId'];
          $TimeType     = $param['TimeType'];
          $IntensityType = $param['IntensityType'];
          $PriceType_Time           = $param['PriceType_Time'];
          $PriceType_Intensity      = $param['PriceType_Intensity'];
          
          if(empty($SoldierId)||empty($LakeId)||empty($IntensityType)|| !in_array($TimeType,array(30,60,240,480,),true)||$RoomId < 1 || $RoomId > 4 || $IntensityType < 1 || $IntensityType > 4)
            return array('Error'=>Error::PARAM);
          
          // kiem tra thong tin dau vao cua ca co het han hay ko
          $oLake = Lake::getById(Controller::$uId,$LakeId);
          
          $oSoldier = $oLake->getFish($SoldierId);
          if(!is_object($oSoldier))
            return array('Error'=>Error::OBJECT_NULL);
          //if($oSoldier->updateStatus() != SoldierStatus::HEALTHY)
          //  return array('Error'=>Error::FISH_WAS_DIED);
            
           // kiem tra xem phong co trong ko
          $oTrain = TrainingGround::getById(Controller::$uId);
          if(!isset($oTrain->Room[$RoomId]) || !empty($oTrain->Room[$RoomId]))
            return array('Error'=>Error::NO_ROOM);
          
          
          $conf = Common::getConfig('CustomTraining');
          $oUser = User::getById(Controller::$uId);
          
          // check training time Limit of Fish
          $oTrain->Room['FishTimeList'][$SoldierId] += $conf['Time'][$TimeType]['TrainingTime'] ;
          $limitTime = intval(Common::getParam('TrainingGround','TrainingTimeLimit'));
          if($oTrain->Room['FishTimeList'][$SoldierId] > $limitTime)
          {
              return array('Error'=>Error::NOT_ACTION_MORE); 
          }

          // kiem tra co du xu ko
          // old Money
          $oldMoney = $oUser->Money ;
          $oldZMoney = $oUser->ZMoney ;
          
          
          // kiem tra theo time training
          $ZMoney1 = $conf['Time'][$TimeType]['ZMoney'];     
          $Money1 = $conf['Time'][$TimeType]['Money'];     
          $Diamond1 = $conf['Time'][$TimeType]['Diamond'];     
          
          
          // kiem tra tien cua time 
          if($PriceType_Time == 'ZMoney')
          {
              $info = $TimeType.":startTraining:1";
              if(!$oUser->addZingXu(-$ZMoney1,$info))
                return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ; 
          }
          else if($PriceType_Time == 'Diamond')
          {
              if(!$oUser->addDiamond(-$Diamond1,DiamondLog::Training))
                return array('Error' => Error :: NOT_ENOUGH_DIAMOND);
          }
          else
          {
              if($Diamond1 > 0 )
              {
                  if(!$oUser->addDiamond(-$Diamond1,DiamondLog::Training))
                    return array('Error' => Error :: NOT_ENOUGH_DIAMOND);
              }
              else if($ZMoney1 > 0 )
              {
                  $info = $TimeType.":startTraining:1";
                  if(!$oUser->addZingXu(-$ZMoney1,$info))
                    return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ; 
              }
              else if($Money1 > 0 )
              {
                    if(!$oUser->addMoney(-$Money1,'startTraining'))
                        return array('Error' => Error :: NOT_ENOUGH_MONEY) ;
                  
              }
              
          }         
                
           
          // kiem tra theo level training
          $ZMoney2 = $conf['Intensity'][$IntensityType]['ZMoney'];     
          $Money2 = $conf['Intensity'][$IntensityType]['Money'];     
          $Diamond2 = $conf['Intensity'][$IntensityType]['Diamond'];     
          
          
          // kiem tra tien cua Intensity
          if($PriceType_Intensity == 'ZMoney')
          {
              $info = $IntensityType.":startTraining:1";
              if(!$oUser->addZingXu(-$ZMoney2,$info))
                return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ; 
          }
          else if($PriceType_Intensity == 'Diamond')
          {
              if(!$oUser->addDiamond(-$Diamond2,DiamondLog::Training))
                return array('Error' => Error :: NOT_ENOUGH_DIAMOND);
          }
          else
          {
              if($Diamond2 > 0 )
              {
                  if(!$oUser->addDiamond(-$Diamond2,DiamondLog::Training))
                    return array('Error' => Error :: NOT_ENOUGH_DIAMOND);
              }
              else if($ZMoney2 > 0 )
              {
                  $info = $IntensityType.":startTraining:1";
                  if(!$oUser->addZingXu(-$ZMoney2,$info))
                    return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ; 
              }
              else if($Money2 > 0 )
              {
                    if(!$oUser->addMoney(-$Money2,'startTraining'))
                        return array('Error' => Error :: NOT_ENOUGH_MONEY) ;
                  
              }
              
          }
                
          $oTrain->startTraining($RoomId,$LakeId,$SoldierId,$TimeType,$IntensityType);
          // lay qua ve
          $GiftList= $oTrain->getGiftTraining($RoomId);
          $oTrain->Room[$RoomId]['GiftList']  = $GiftList ;
          
          $oTrain->save();
          $oUser->save();
          $result['GiftList']= $GiftList ;
          $result['Error']= Error::SUCCESS ;
         
          // new Money
          $diffMoney = $oUser->Money - $oldMoney ;
          $diffZMoney = $oUser->ZMoney - $oldZMoney ;
          // log
                   
          Zf_log::write_act_log(Controller::$uId,0,20,'startTraining',$diffMoney,$diffZMoney,$RoomId,$SoldierId,$TimeType,$IntensityType);
          return $result ;
          
          
      
      }
      
      // stop training
      public function stopTraining($param)
      {
          $RoomId       = $param['RoomId'];
          if($RoomId < 1 || $RoomId > 4)
            return array('Error'=>Error::PARAM); 
          $oTrain = TrainingGround::getById(Controller::$uId);
          if(!isset($oTrain->Room[$RoomId]) || empty($oTrain->Room[$RoomId]))
            return array('Error'=>Error::NO_ROOM);
          
          $oTrain->Room[$RoomId] = array();
          
          $oTrain->save();
          Zf_log::write_act_log(Controller::$uId,0,20,'stopTraining',0,0,$RoomId);       
          return array('Error'=>Error::SUCCESS); 
      }
      // speed up training
      public function speedUpTraining($param)
      {
          $RoomId       = $param['RoomId'];
          if($RoomId < 1 || $RoomId > 4)
            return array('Error'=>Error::PARAM); 
            
          $oUser = User::getById(Controller::$uId);
          if (!is_object($oUser))
          {
            return array('Error' => Error :: NO_REGIS) ;
          }
          $oTrain = TrainingGround::getById(Controller::$uId);
          if(!isset($oTrain->Room[$RoomId]) || empty($oTrain->Room[$RoomId]))
            return array('Error'=>Error::NO_ROOM);
          
          // check xem da het lan tua chua 
          //$confLimit = Common::getConfig('CustomTraining','Time',$oTrain->Room[$RoomId]['TimeType']);
          //if($oTrain->Room['SpeedUpNum'] >= $confLimit['SpeedUpLimit'])
          //  return array('Error'=>Error::OVER_NUMBER);
          
          // check money
          // old Money
          $oldMoney = $oUser->Money ;
          $oldZMoney = $oUser->ZMoney ;
          
          $conf = Common::getConfig('CustomTraining','Time');
          $TimeType = $oTrain->Room[$RoomId]['TimeType'];
          
          $conf = $conf[$TimeType];
          $ZMoney = $conf['SpeedUpZMoney'];
          
          $info = "1:speedUpTraining:".$RoomId ;
          if(!$oUser->addZingXu(-$ZMoney,$info))
            return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
          
          $oTrain->speedUpTraining($RoomId);
          $oTrain->save();
          $oUser->save();
          
          // new Money
          $diffMoney = $oUser->Money - $oldMoney ;
          $diffZMoney = $oUser->ZMoney - $oldZMoney ;
          
          Zf_log::write_act_log(Controller::$uId,0,20,'speedUpTraining',$diffMoney,$diffZMoney,$RoomId);

          return array('Error'=>Error::SUCCESS); 
      }
      // get gift of training
      public function getGiftTraining($param)
      {
          $RoomId       = $param['RoomId'];
          if($RoomId < 1 || $RoomId > 4)
            return array('Error'=>Error::PARAM); 
            
          $oUser = User::getById(Controller::$uId);
          if (!is_object($oUser))
          {
            return array('Error' => Error :: NO_REGIS) ;
          }
          $oTrain = TrainingGround::getById(Controller::$uId);
          if(!isset($oTrain->Room[$RoomId]) || empty($oTrain->Room[$RoomId]))
            return array('Error'=>Error::NO_ROOM);
            
          // check compelete of Training 
          if(!$oTrain->checkCompeleteTraining($RoomId))
            return array('Error'=>Error::NOT_ENOUGH_TIME);
          
          // lay qua ve
          //$GiftList= $oTrain->getGiftTraining($RoomId);
          $GiftList = $oTrain->Room[$RoomId]['GiftList'] ;
          $oTrain->Room[$RoomId]['GiftList'] = array() ;
          // cong vao ngu thu
          $soldierInfo = $this->saveGift($oTrain->Room[$RoomId],$GiftList);
          // cong vao user
          $oUser->saveBonus($GiftList);
          
          $result['TimeType']       = $oTrain->Room[$RoomId]['TimeType'] ;
          $result['IntensityType']  = $oTrain->Room[$RoomId]['IntensityType'] ;
          
          $oTrain->Room[$RoomId] = array();
          $oUser->save();
          $oTrain->save();
                 
          $result['Error'] = Error::SUCCESS;
          // return ve
          Zf_log::write_act_log(Controller::$uId,0,20,'getGiftTraining',0,0,$RoomId,intval($soldierInfo['oldRankPoint']),intval($soldierInfo['oldRank']),intval($soldierInfo['newRankPoint']),intval($soldierInfo['newRank']));
          return $result ;
      }
      private function saveGift($RoomInfo,$GiftList)
      {
          if(empty($GiftList))
            return false ;
          
          $oLake = Lake::getById(Controller::$uId,$RoomInfo['LakeId']);
          $oSoldier = $oLake->getFish($RoomInfo['SoldierId']);
          if(!is_object($oSoldier))
            return false ;
            
          $soldierInfo['oldRankPoint'] = $oSoldier->RankPoint;
          $soldierInfo['oldRank'] = $oSoldier->Rank;
          
          $oStoreEquip = StoreEquipment::getById(Controller::$uId);
          
          foreach($GiftList as $index => $Gift)
          {
              if($Gift['ItemType'] == Type::Meridian)
              {
                  $oStoreEquip->addMeridian($RoomInfo['SoldierId'],$Gift['Num']);
              }
              if($Gift['ItemType'] == 'RankPoint')
              {
                  $oSoldier->addRankPoint($Gift['Num']);
              }
          }
          $oStoreEquip->save();
          $oLake->save();
          $soldierInfo['newRankPoint'] = $oSoldier->RankPoint;
          $soldierInfo['newRank'] = $oSoldier->Rank;
          return $soldierInfo;
         
          
      }
      
      public function getStatusTraining()
      {
          $oUser = User::getById(Controller::$uId);
          if (!is_object($oUser))
          {
            return array('Error' => Error :: NO_REGIS) ;
          }
          $oTrain = TrainingGround::getById(Controller::$uId);
          $arr['Error'] = Error::SUCCESS ;
          $arr['Object'] = $oTrain ;
          return $arr;
      }
      
      // Da thong ngu mach
      public function upgradeMeridian($param)
      {
          $soldierId = $param['soldierId'];
          $lakeId = $param['lakeId'];
          if(empty($soldierId) || empty($lakeId))
            return array('Error'=>Error::PARAM);
                                                                   
          $oLake = Lake::getById(Controller::$uId,$lakeId);
          
          $oSoldier = $oLake->getFish($soldierId);
          if(!is_object($oSoldier))
            return array('Error'=>Error::OBJECT_NULL);
          //if($oSoldier->updateStatus() != SoldierStatus::HEALTHY)
          //  return array('Error'=>Error::FISH_WAS_DIED);
          
          $oStoreEquip = StoreEquipment::getById(Controller::$uId);
          if(empty($oStoreEquip->listMeridian[$soldierId]))
          {                                          
              $oStoreEquip->listMeridian[$soldierId]['meridianRank'] = 1;
              $oStoreEquip->listMeridian[$soldierId]['meridianPosition'] = 0;
              $oStoreEquip->listMeridian[$soldierId]['meridianPoint'] = 0;            
              $oStoreEquip->save();
              return array('Error'=>Error::NOT_ENOUGH_CONDITION);
          }
          if($oSoldier->Rank < $oStoreEquip->listMeridian[$soldierId]['meridianRank'])
          {
              return array('Error'=>Error::NOT_ENOUGH_CONDITION);
          }                                
          $result = $oStoreEquip->upgradeMeridianPosition($oSoldier);
          if($result)
          { 
              $oStoreEquip->save();       
              
              Zf_log::write_act_log(Controller::$uId,0,20,'upgradeMeridian',0,0,intval($oStoreEquip->listMeridian[$soldierId]['meridianRank']),intval($oStoreEquip->listMeridian[$soldierId]['meridianPosition']),intval($oStoreEquip->listMeridian[$soldierId]['meridianPoint']),$soldierId);
              
              return array('Error'=>Error::SUCCESS);
          }
          
          

          
          return array('Error'=>Error::NOT_ENOUGH_CONDITION);
      }
  }
?>
