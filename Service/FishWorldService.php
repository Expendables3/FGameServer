<?php

/**
* @author hieupt
* 07/07/2011
* FishWorld
*/

class FishWorldService
{
    
  //ham vao the gioi ca
    public function loadOcean()
    {
      $oUser = User :: getById(Controller::$uId) ;
      if (!is_object($oUser))
      {
        return array('Error' => Error :: NO_REGIS) ;
      }
      $oWorld = FishWorld::getById(Controller::$uId);
      if (!is_object($oWorld))
      {
          $oWorld = new FishWorld(Controller::$uId) ;
      }
      else
      {
          // lat co trang thai dang trong the gioi ca
          $oWorld->IsInWorld = true ;
          // check ngay moi
          $Today    = date('Ymd',$_SERVER['REQUEST_TIME']);
          $LastDay   = date('Ymd',$oWorld->LastTime);
          if($Today != $LastDay)
          {
              //reset tat ca cac ho voi cac thong so ko co quai
              $oWorld->resetAllSea();
              $oWorld->resetAllMonster();
                   
              if($oWorld->ErrorFlag)
              {
                  $oWorld->ErrorFlag = 0 ;                   
              }
              
              //reset trang thai ca linh
              $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
              
              foreach ($arrSoldier as $LakeId => $arr)
              {
                    if(empty($arr))
                        continue ;
                    $oLake = Lake::getById(Controller::$uId,$LakeId) ;
                    foreach ($arr as $idSoldier => $oSoldier)
                    {
                       if(!is_object($oSoldier))
                            continue ;   
                       $oSoldier->updateIsDie(false);          
                    }

                   $oLake->save() ;
              }
              
              $oWorld->LastTime = $_SERVER['REQUEST_TIME'];

          }
          else
          {
               //back lai ban truoc 
               if($oWorld->ErrorFlag)
               {

                     //$oWorld->SeaList   = $oWorld->CloneSeaList ;                    
                     $oWorld->ErrorFlag = 0 ;
                     $oWorld->save() ;
               }
               else
               {
                  // xoa toan bo quai trong cac bien
                  $oWorld->resetAllMonster();
                  //reset trang thai ca linh
                  $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
                  
                  foreach ($arrSoldier as $LakeId => $arr)
                  {
                       $oLake = Lake::getById(Controller::$uId,$LakeId) ;
                       foreach ($arr as $idSoldier => $oSoldier)
                       {    
                           if(!is_object($oSoldier))
                            continue ;   
                           $oSoldier->updateIsDie(false);          
                       }
                       
                       $oLake->save();
                   }
               }
  
          }

      }
      
      $oWorld->save();
      $result = array('Error'=>0,'World'=>$oWorld) ;
      //Zf_log::write_act_log(Controller::$uId,0,20,'loadOcean');  
      return $result;
    }

    // unlock moi bien moi
    public function unlockSea($param)
    {
      $SeaId        =   $param['SeaId'] ;
      $PriceType    =   $param['PriceType'] ;
    
      if(empty($SeaId)|| !SeaType::check($SeaId))
      {
        return array('Error' => Error :: PARAM) ;  
      }
      
      $oUser = User :: getById(Controller::$uId) ;
      if (!is_object($oUser))
      {
        return array('Error' => Error :: NO_REGIS) ;
      }

      $oWorld = FishWorld::getById(Controller::$uId);
      if (!is_object($oWorld))
      {
        $oWorld = new FishWorld(Controller::$uId);
        //return array('Error' => Error :: OBJECT_NULL) ;
      }
      
      if(is_object($oWorld->getSea($SeaId)))
        return array('Error' => Error :: EXIST) ;     
        
      
      $SeaConf = Common::getWorldConfig('Sea',$SeaId);
      if(!is_array($SeaConf))
        return array('Error' => Error :: NOT_LOAD_CONFIG) ;        
      
      $oStore = Store::getById(Controller::$uId); 
      
      // log
      $oldZMoney = $oUser->ZMoney ;
      
      if($PriceType == Type::ZMoney)  // unlock = xu
      {
        $info = "1:openSea:".$SeaId ;
        if(!$oUser->addZingXu(-$SeaConf['ZMoney'],$info))
          return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
      }
      else // unlock binh thuong
      {
        // check Level User
        if($oUser->Level < $SeaConf['LevelRequire'])
        {
          return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;      
        }
        if(isset($SeaConf['Lisence']))
        {
            if(!$oStore->useItem(Type::License,1,$SeaConf['Lisence']))
                return array('Error' => Error :: NOT_ENOUGH_LICENSE) ; 
        }
        if(isset($SeaConf['FriendNum']))
        {
            // kiem tra so ban co
            $FriendList = $oUser->getFriends(false);
            if(count($FriendList) < $SeaConf['FriendNum'])
                return array('Error' => Error :: NOT_ENOUGH_FRIEND) ;
        }
        
        if(isset($SeaConf['PassedSea']))
        {
            $oSea  = $oWorld->getSea($SeaConf['PassedSea']);
            if(!is_object($oSea))
                return array('Error' => Error ::OBJECT_NULL) ; 
            if($oSea->KillBossNum <= 0 )
                return array('Error' => Error ::NOT_ENOUGH_CONDITION) ; 
        }
        
               
      }
      
      // khoi tao bien moi 
      $oWorld->addSea($SeaId);
      $oStore->save();
      $oUser->save();
      $oWorld->save();
      
      //log
      if($PriceType == Type::ZMoney)  // unlock = xu
      {
          // log
          $DiffZMoney = $oUser->ZMoney - $oldZMoney  ;  
          Zf_log::write_act_log(Controller::$uId,0,23,'unlockSea',0,$DiffZMoney,$SeaId);  
      }
      else
      {
          Zf_log::write_act_log(Controller::$uId,0,20,'unlockSea',0,0,$SeaId);  
      }
      
      
      return array('Error'=> 0,'Sea'=>$oWorld->getSea($SeaId)) ;
  
    }
    
    
    // quay tro lai bien 
    public function joinSeaAgain($param)
    {
        $SeaId      = $param['SeaId'];
        $PriceType  = $param['PriceType'];
        
        if(empty($SeaId))
            return array('Error' => Error :: PARAM) ;
                                
        // Lay toan bo thong tin the gioi ca va user
        $oWorld = FishWorld::getById(Controller::$uId);
        $oUser = User::getById(Controller::$uId);
        
        
        $oSea = $oWorld->getSea($SeaId);
        if (!is_object($oSea))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
        
        if(!$oWorld->checkInWorld())
        {
          return array('Error' => Error :: YOU_NOT_STAY_IN_WORLD) ;      
        }
        
        $diffZMoney = $oUser->ZMoney;
        
        $SeaConf = Common::getWorldConfig('Sea',$SeaId);
        // check dieu kien vao lai bien
        $result = $oSea->checkJoinSea($PriceType, $SeaConf['JoinSeaTime'], $SeaConf['JoinSeaZingxu']) ;
        
        if($result['Error'] != Error::SUCCESS)
        {
            return array('Error' => $result['Error']) ;
        }
        
        if(empty($SeaConf))
        {
            return array('Error' => Error::NOT_LOAD_CONFIG) ;
        }
        $oStore = Store::getById(Controller::$uId);
                
        $oSea->joinAgain();
        $oUser->save();
        $oStore->save();
        $oWorld->save();
        
        $diffZMoney =$oUser->ZMoney - $diffZMoney ;
        Zf_log::write_act_log(Controller::$uId,0,23,'joinSeaAgain',0,$diffZMoney,$SeaId);
        
        return array('Error'=> 0 ,'Sea'=>$oSea ) ;   
             
    }
    
    // chon vong danh
    public function chooseRoundAttack($param)
    {
        $SeaId      =   $param['SeaId'];
        $RoundId      =   $param['RoundId'];
        if(empty($SeaId) || empty($RoundId))
        {
            return array('Error' => Error :: PARAM);
        }
                                                    
        $oWorld = FishWorld::getById(Controller::$uId);    
        $oSea   = $oWorld->getSea($SeaId);        
        if (!is_object($oSea))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
                                       
        if(!$oWorld->checkInWorld())
        {
          return array('Error' => Error :: YOU_NOT_STAY_IN_WORLD) ;      
        }                               
                                       
        $oSea->RoundNum = $RoundId;       
  
        $oReturnMonster = $oSea->getMonsterInRound($RoundId);          
        $oWorld->save();     
        if($oSea->SeaId == SeaType::SEA_4)                    
        {
            if($oSea->RoundNum == SeaRound::ID_ROUND_1)    // o bung bien do - di len
            {
                //return  array('Error'=> 0 ,'Monster'=> $oReturnMonster, 'Sequence'=> $oSea->sequenceRedUp) ;    
                $oReturnSequence = $oSea->sequenceRedUp;
            }
            else if($oSea->RoundNum == SeaRound::ID_ROUND_3)
            {
                $oReturnSequence = $oSea->sequenceYellowDown;             
                return array('Error'=> 0 ,'Monster'=> $oReturnMonster, 'Sequence'=> $oReturnSequence, 'ArrHide'=> $oSea->arrHideInGreenDown) ;
                //return  array('Error'=> 0 ,'Monster'=> $oReturnMonster, 'Sequence'=> $oSea->sequenceYellowDown) ;    
            }
            else if($oSea->RoundNum == SeaRound::ID_ROUND_2)
            {
                $oReturnSequence = $oSea->arrRandomBuff;
                //return  array('Error'=> 0 ,'Monster'=> $oReturnMonster, 'Sequence'=> $oSea->sequenceYellowDown) ;    
            }
        }
        return array('Error'=> 0 ,'Monster'=> $oReturnMonster, 'Sequence'=> $oReturnSequence) ;
    }
    
    // danh boss
    
    public function acttackBoss($param)
    {
        $SeaId      = $param['SeaId'];
        $IdMonster  = $param['IdMonster'];        
        if(empty($SeaId)||empty($IdMonster))
            return array('Error' => Error :: PARAM);
            
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
                              
        $oWorld = FishWorld::getById(Controller::$uId);
        $oSea   = $oWorld->getSea($SeaId);
        if (!is_object($oSea))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
        
        if(!$oWorld->checkInWorld())
        {
          return array('Error' => Error :: YOU_NOT_STAY_IN_WORLD) ;      
        }
        $idSea = $SeaId;
        $idRound = $oSea->RoundNum;              
        // kiem tra Monster 
        $oMonster = $oSea->getMonster($IdMonster) ; 
        if(!is_object($oMonster))
             return array('Error' => Error :: OBJECT_NULL) ; 
        // kiem tra co phai la boss ko 
        if(!$oMonster->IsBoss)
        {
            return array('Error' => Error::PARAM);        
        }
        // kiem tra cac dieu kien rieng can de danh boss
        if (!$oSea->isAttackedBoss())
        	return array('Error' => Error::CANT_ATTACK);   
        // kiem tra soldier 
        $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);
        
        $result = array() ; 
        $ListS = array();
        
        $TotalEnergy = Common::getParam('EnergyKillBoss') ;
        if (!$oUser->addEnergy(-$TotalEnergy))
            return array('Error' => Error::NOT_ENOUGH_ENERGY);    
        $conf_rank = Common::getConfig('RankPoint');  
        // check dieu kien truoc khi danh    
        foreach($arrSoldier as $idLake => $arrLake)
        {
            foreach($arrLake as $idFish => $oSoldier)    
            {

                // check enough health         
                $HealthSoldier = $oSoldier->getHealth();
                $StatusSoldier = $oSoldier->Status;
                
                $result['MySoldier'][$idLake][$idFish] = clone $oSoldier;
                
                if ($HealthSoldier < $conf_rank[$oSoldier->Rank]['AttackPoint']) 
                    continue ;
                if($oSoldier->IsDie)
                    continue ;
                
                // check trang thai cua ca
                if( $StatusSoldier != SoldierStatus::HEALTHY)
                    continue ;
                //$TotalEnergy += $Energy ;     
                $ListS[$oSoldier->Id] = $oSoldier ; 
                
            }
        }  

        if(empty($ListS))
            return array('Error' => Error :: OBJECT_NULL); 
                         
        $aaa  = Common::getWorldConfig('SeaMonster',$oSea->SeaId,$oSea->RoundNum);  
        $alpha = intval($aaa[$IdMonster]['Alpha']);
        $attack = $oSea->generateScene($ListS,$oMonster,1,$alpha);        	   
        $attackResult = $this->checkWin($attack,$oSea,$oMonster,true);
        
        $result['Bonus'] = $attackResult['Bonus'];
        $result['Scene'] = $attack ;
        $result['isWin'] = $attackResult['is_win'] ;
            
               
        if($attackResult['is_win'])  //boos died
        {
            /*
            // len ti vi 
            DataProvider::getMemcache()->set('WinBoss',$oUser->getUserName());
            DataProvider::getMemcache()->set('LastTimeGetGift',$_SERVER['REQUEST_TIME']);
            */
            // get qua cua Event 
            $Gift = Event::getActionGiftInEvent(EventType::EventActive, 'FishWorld', $oSea->SeaId, $oSea->RoundNum,$IdMonster);
            //$oEvent = Event::getById(Controller::$uId);   
            //$Gift = $oEvent->island_getGiftInEvent('FishWorld',$oSea->SeaId,$oSea->RoundNum,$IdMonster);
            if(!empty($Gift))
                    $result['Bonus'][100]['Event'] = $Gift;            
            //---------
            
            // xoa du lieu cua quai
            $oSea->delMonster($IdMonster) ;
            
            // tang so lan kill duoc boss
            $RoundNum = count($oSea->Monster) ;
            if(empty($oSea->Monster[$RoundNum]))
            {
                $oSea->updateKillBossNum(1);
                $oSea->updateKillBossNumOnDay(1); 
            }
            
        }
        else
        {                
             // luu lai nang luong
            $oUser->addExp($TotalEnergy); 
            $result['Bonus'][100]['Normal'][] = array('ItemType'=>'Exp','Num'=>$TotalEnergy);
            
        }
        foreach ($arrSoldier as $LakeId => $arr)
        {
           $oLake = Lake::getById(Controller::$uId,$LakeId) ;
           foreach ($arr as $idSoldier => $oSoldier)
           {
                if(!isset($ListS[$idSoldier]))
                    continue;                        
                // tru gem cua ca linh 
                $oSoldier->updateGemAfterBattle() ;
                // update equipment durability
                $oSoldier->updateDurability();
                
                // deletebuffItem
                foreach($oSoldier->BuffItem as $id => $oBuff)
                {
                    if ($oBuff['Turn']<=1)
                    unset($oSoldier->BuffItem[$id]);
                    else $oSoldier->BuffItem[$id]['Turn']--;
                }
                
                if($attackResult['is_win'])  //boos died
                {
                    // set Fish to be die
                    if(isset($attackResult['isdie'][$idSoldier]))
                    {
                        $oSoldier->updateIsDie(true);
                    }
                    
                    
                    // update Health 
                    $oSoldier->addHealth(-$conf_rank[$oSoldier->Rank]['AttackPoint']);
                }
                else
                {                
                    // tru 50 % suc khoe 
                    $oSoldier->setHealth(-round($conf_rank[$oSoldier->Rank]['MaxHealth']/2)) ;
                    
                    $oSoldier->updateIsDie(true);

                }       
                
           }
           
           $oLake->save() ;
           
        }

        // luu do vao kho 
        $this->saveActtackBonus($result['Bonus']);
        
        // cong do xin vao trong list do dac
        $oSea->updateEquipmentGave($result['Bonus']);
        
        // update thong tin sau tran danh 
        $oWorld->updateInfoAfterMatch($SeaId);
        //update NumRound
        $oSea->updateNumRound();
        
        $oWorld->save(); 
        $oUser->save();
        
        
        $result['Error'] = Error::SUCCESS ; 
        $result['MyEnergy'] = $oUser->getRealEnergy(); 
        $result['IdRound'] = $idRound; 
        $result['IdSea'] = $idSea; 
        $result['MyEquipment'] = StoreEquipment::getById(Controller::$uId); 
        
        //Zf_log::write_act_log(Controller::$uId,0,30,'acttackMonster',0,0,$SeaId,$oSea->RoundNum);         
               
        return $result ;
    }   
    
    public function acttackBossForest($param)
    {        
        $SeaId      = $param['SeaId'];
        $IdMonster  = $param['IdMonster'];        
        if(empty($SeaId)||empty($IdMonster))
            return array('Error' => Error :: PARAM);    
        if($SeaId != SeaType::SEA_4)
        {
            return array('Error' => Error :: NO_IN_FOREST_WORLD);
        }                                     
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }                                                       
        $oWorld = FishWorld::getById(Controller::$uId);
        $oSea   = $oWorld->getSea($SeaId);
        if (!is_object($oSea))
        {                                  
            return array('Error' => Error :: OBJECT_NULL) ;   
        }                                      
        if(!$oSea->CheckInRound4Forest())
        {
            return Error::NO_IN_ROUND4_FOREST_WORLD;
        }
        else
        {
            $oSea->RoundNum = SeaRound::ID_ROUND_4;
        }                                     
        if(!$oWorld->checkInWorld())
        {
          return array('Error' => Error :: YOU_NOT_STAY_IN_WORLD) ;      
        }                                       
             
        $idSea = $SeaId;
        $idRound = $oSea->RoundNum;
        // kiem tra Monster 
        $oMonster = $oSea->getMonster($IdMonster) ;                                 
        // Check lai o day dang co van de
        if(!is_object($oMonster))
        {
             return array('Error' => Error :: OBJECT_NULL) ;
        }                                        
        // kiem tra co phai la boss ko
        //if(!$oMonster->IsBoss)
        //{
        //    return array('Error' => Error::PARAM);
        //}
        // kiem tra cac dieu kien rieng can de danh boss
        if (!$oSea->isAttackedBoss())
            return array('Error' => Error::CANT_ATTACK);         
        
        // len ti vi 
        DataProvider::getMemcache()->set('WinBoss',$oUser->getUserName());
        DataProvider::getMemcache()->set('LastTimeGetGift',$_SERVER['REQUEST_TIME']);
                 
        // kiem tra soldier 
        $arrSoldier = Lake::getAllSoldier(Controller::$uId,true,true,true);           
        $result = array() ; 
        $ListS = array();
        
        $TotalEnergy = Common::getParam('EnergyKillBoss') ;    
        //if (!$oUser->addEnergy(-$TotalEnergy))
        //    return array('Error' => Error::NOT_ENOUGH_ENERGY);    
            
        $conf_rank = Common::getConfig('RankPoint');
        // check dieu kien truoc khi danh      
        foreach($arrSoldier as $idLake => $arrLake)
        {
            foreach($arrLake as $idFish => $oSoldier)    
            {                                                  
                // check enough health         
                $HealthSoldier = $oSoldier->getHealth();
                $StatusSoldier = $oSoldier->Status;
                
                $result['MySoldier'][$idLake][$idFish] = clone $oSoldier;
                
                if ($HealthSoldier < $conf_rank[$oSoldier->Rank]['AttackPoint']) 
                    continue ;
                if($oSoldier->IsDie)
                    continue ;
                
                // check trang thai cua ca
                if( $StatusSoldier != SoldierStatus::HEALTHY)
                    continue ;
                //$TotalEnergy += $Energy ;
                $ListS[$oSoldier->Id] = $oSoldier ; 
                
            }
        }                                                                         
        if(empty($ListS))
        {                                  
            return array('Error' => Error :: OBJECT_NULL);  
        }                                      
        $result['Error'] = Error::SUCCESS;                                    
        if(empty($oSea->arrGift))
        {                                    
            $result['Scene'] = $oSea->acttackBossForest($ListS,$oMonster);   
            $result['isWin'] = 1;     
            $oSea->arrGift = $result['Scene'];
            $oWorld->save();
            
            // tang so lan kill duoc boss
            $oSea->updateKillBossNum(1);
            $oSea->updateKillBossNumOnDay(1); 
            
        }
        else
        {  
            $result['Scene'] = $oSea->arrGift;
            $result['isWin'] = 0;        
        }
        
        
        
        return $result ;
    }
    
    public function startChooseSerialAttackInForestYellow($param)
    {   
        $SeaId      = $param['SeaId'];
        if(empty($SeaId))
        {
          return array('Error' => Error :: PARAM);
        }                        
        
        $oWorld         = FishWorld::getById(Controller::$uId);
        $oSea           = $oWorld->getSea($SeaId);
        if (!is_object($oSea))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }                                   
        $oSerialAttack  = $oSea->sequenceYellowDown;   
        if(empty($oSerialAttack))
        {                                   
            array('Error' => Error :: OBJECT_NULL) ; 
        }                                                    
        $arrHideInGreenDown  = $oSea->arrHideInGreenDown;   
        if(empty($arrHideInGreenDown))
        {                                   
            array('Error' => Error :: OBJECT_NULL) ; 
        }                           
        $result['Error'] = Error::SUCCESS ; 
        $result['Sequence'] = $oSerialAttack;
        $result['ArrHide'] = $arrHideInGreenDown;
        
        return $result;
    }
    
    // danh quai
    
    public function acttackMonster($param)
    {
        $SeaId      = $param['SeaId'];
        $IdMonster  = $param['IdMonster'];
        $IdSoldier  = $param['IdSoldier'];
        $LakeId     = $param['LakeId'] ;
        $ItemList   = $param['ItemList'];
        $ParamForest    = $param['ParamForest'];    //voi vong 1 la chi so vi tri
        
        // check param client gui len xem co dung form khong
        if(empty($SeaId)||empty($IdMonster)||empty($IdSoldier))
        {
          return array('Error' => Error :: PARAM);
        }
        // get thong tin cua user len    
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        // lay thong tin the gioi ca len
        $oWorld = FishWorld::getById(Controller::$uId);
        $oSea   = $oWorld->getSea($SeaId);
        if (!is_object($oSea))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
        // kiem tra xem user co trong the gioi ca khong
        if(!$oWorld->checkInWorld())
        {
          return array('Error' => Error :: YOU_NOT_STAY_IN_WORLD) ;      
        }
        $idSea = $SeaId;
        $idRound = $oSea->RoundNum;
        // kiem tra Monster xem con ton tai khong
        $oMonster = $oSea->getMonster($IdMonster) ; 
        if(!is_object($oMonster))
             return array('Error' => Error :: OBJECT_NULL) ;  
        // kiem tra co phai la boss ko 
        if($oMonster->IsBoss && $SeaId != SeaType::SEA_4)
        {
            return array('Error' => Error::PARAM);        
        }           
        // kiem tra con ca linh nha dem di danh xem co ton tai khong
        $oLake = Lake::getById(Controller::$uId,$LakeId);
        $oSoldier = $oLake->getFish($IdSoldier);
        if(!is_object($oSoldier) || $oSoldier->FishType != FishType::SOLDIER)
            return array('Error' => Error :: NOT_SELECTED_SOLDIER); 
        // Kiem tra xem con ca da thua nhung khong duoc hoi sinh o cac vong khac hay chua
        if($oSoldier->IsDie)
        {
            return array('Error' => Error :: FISH_WAS_DIED);
        }
        // check trang thai cua ca xem co du dieu kien de danh khong
        if($oSoldier->Status != SoldierStatus::HEALTHY)
            return array('Error' => Error :: EXPIRED); 
        // check Item    
        $oStore = Store::getById(Controller::$uId);
        $conf_buff = Common::getConfig('BuffItem');
        if(!empty($ItemList))
        {
            foreach($ItemList as $id => $oItem)
            {
                if (!$oStore->useBuffItem($oItem[Type::ItemType],$oItem[Type::ItemId],$oItem[Type::Num]))
                {
                    return array('Error' => Error::NOT_ENOUGH_ITEM);
                }
            }
            
            // Cap nhat cac item bo tro su dung cho con ca
            $buffItemAfter = Common::addItemToList($oSoldier->BuffItem, $ItemList);
            $oSoldier->BuffItem = $buffItemAfter;      
        
            
            // check max times use ItemBuff   
            if (!SoldierFish::checkValidItemList($buffItemAfter))
            {
                return array('Error' => Error::OVER_NUMBER);
            }
        }
        // check resistance
        $isResistance = 1;
        foreach($oSoldier->BuffItem as $id => $oItem)
        {   
            if ($oItem[Type::ItemType] == BuffItem::Resistance)  
            {
                $isResistance = 0;
            }    
        }                  
        // check enough mana vs energy ???
        $Energy = Common::getParam('EnergyKillMonster');
        if (!$oUser->addEnergy(-$Energy))
            return array('Error' => Error::NOT_ENOUGH_ENERGY); 
        // check enough health
        $conf_rank = Common::getConfig('RankPoint');            
        $HealthSoldier = $oSoldier->getHealth();
        if ($HealthSoldier < $conf_rank[$oSoldier->Rank]['AttackPoint']) 
            return array('Error' => Error::NOT_ENOUGH_HEALTH); 
        // Lay du lieu cac con ca linh de tra ve cho client
        $result = array() ; 
        $SoldierList = Lake::getAllSoldier(Controller::$uId,true,true,true); 
        foreach($SoldierList as $idLake => $arrLake)
        {
            foreach($arrLake as $idFish => $object)    
            {
                $result['MySoldier'][$idLake][$idFish] = clone $object;
            }
        }
        // Kiem tra xem con quai co duoc phep danh khong - the gioi moc _ co lien quan den thu tu danh quai
        if($oSea->SeaId == SeaType::SEA_4 && $oSea->RoundNum == 3)
        {
            if($oMonster->Id == 6 && !empty($oSea->sequenceYellowDown))
            {  
                return array('Error' => Error::PARAM);        
            }
        }                            
        $arrSoldier = array()     ;
        $arrSoldier[$oSoldier->Id] = $oSoldier ;  
        $attack = $oSea->generateScene($arrSoldier,$oMonster,$isResistance);    
        $attackResult = $this->checkWin($attack,$oSea,$oMonster,false);          
        $result['Bonus'] = $attackResult['Bonus'];                               
        $result['Scene'] = $attack ; 
        $result['isWin'] = $attackResult['is_win'] ;        
        $param_rank = Common::getParam('RankPoint');            
        // danh quai thuong
        if($attackResult['is_win'] == Battle::WIN)  // monster died
        {
            $oUser->BattleStat['Win'] += 1; // Phuc vu event dua top so tran thang
    
            $oSoldier->addHealth(-$conf_rank[$oSoldier->Rank]['AttackPoint']) ;  // Cap nha suc khoe con ca
             
            // Cong rank point 
            $indexRank = SoldierFish::calculateIndexRank($oMonster->Rank,$oSoldier->Rank);
            $rankAdd = $param_rank[$indexRank];          
            $conf_buff = Common::getConfig('BuffItem',BuffItem::BuffRank,1);
            $perAdd = 0;
            foreach($oSoldier->BuffItem as $idItem => $oItem)
            {
                if($oItem['ItemType'] == BuffItem::BuffRank)
                {
                    $perAdd = $conf_buff['Num']*$oItem[Type::Num];
                    break ;
                }           
            }
            $rankAdd += ceil($perAdd*$rankAdd/100);
            $oSoldier->addRankPoint($rankAdd);
            $result['Bonus'][100]['Normal'][] = array('ItemType'=>'Rank','Num'=>$rankAdd);
            
            // them qua tu event 
           // $oEvent = Event::getById(Controller::$uId);   
            //$Gift = $oEvent->island_getGiftInEvent('FishWorld',$oSea->SeaId,$oSea->RoundNum,$IdMonster);
            $Gift = Event::getActionGiftInEvent(EventType::EventActive, 'FishWorld', $oSea->SeaId, $oSea->RoundNum,$IdMonster);
            if(!empty($Gift))
                    $result['Bonus'][100]['Event'] = $Gift;
                    
            // xoa du lieu cua quai
            if($SeaId != SeaType::SEA_4)
            {
                $oSea->delMonster($IdMonster); 
            }
            else
            {               
                 if($oSea->RoundNum == SeaRound::ID_ROUND_1)   // dang danh bui cay, quai o vong 1
                 {                                     
                     if($oMonster->IsBoss)
                     {
                        $oMonsterRound = $oSea->getMonsterInRound();
                        foreach($oMonsterRound as $idMonsterInRound => $oMonsterInRound)
                        {
                            $oSea->delMonster($idMonsterInRound);
                        }
                     }
                     else
                     {
                         $oSea->createSequenceRedUp();
                     }
                 }
                 else if($oSea->RoundNum == SeaRound::ID_ROUND_3)  // Danh ran, quai o vong 3
                 {                                                                                                         
                     if(!empty($oSea->sequenceYellowDown))
                     {                                                        
                         if($oMonster->Element == $this->getCurElement($oMonster, $oSea))
                         {                                                              
                             $attackTrue = true;
                             $oSea->delMonster($IdMonster);
                             unset($oSea->sequenceYellowDown[(String)$this->getIndex($oMonster, $oSea)]);        
                         }
                         else 
                         {                             
                             $this->reNewMonsterInRound3($oSea);   
                             $attackTrue = false;
                         }
                     }  
                     else
                     {            
                         $oSea->delMonster($IdMonster);
                         unset($oSea->sequenceYellowDown[(String)$this->getIndex($oMonster, $oSea)]);      
                     }
                 }
                 else if($oSea->RoundNum == SeaRound::ID_ROUND_2)  // Danh quai o vong 2
                 {                                 
                     unset($oSea->arrRandomBuff);
                     $oSea->delMonster($IdMonster);                            
                 }
            }
        }
        else
        {           
            if($SeaId == SeaType::SEA_4)                         
            {                                  
                if($oSea->RoundNum == SeaRound::ID_ROUND_3)
                {   
                    if(!empty($oSea->sequenceYellowDown))                                                                  
                    { 
                        if($oMonster->Element != $this->getCurElement($oMonster, $oSea))
                        {      
                            $this->reNewMonsterInRound3($oSea);   
                            $attackTrue = false;
                        }
                    }
                }
            }            
            $oUser->BattleStat['Lose'] += 1;   
            $result['Bonus'][100]['Normal'][] = array('ItemType'=>'Exp','Num'=>$Energy);
            // tru 50% suc khoe 
            $oSoldier->setHealth(-round($conf_rank[$oSoldier->Rank]['MaxHealth']/2)) ;
            // Fish to be Die
            $oSoldier->updateIsDie(true);
            // tru rank point 
            $indexRank = SoldierFish::calculateIndexRank($oSoldier->Rank,$oMonster->Rank);
            if(!SoldierFish::checkExistItem($oSoldier->BuffItem,BuffItem::StoreRank,1))
            {
                $rankLost = $param_rank[$indexRank];
                $oSoldier->addRankPoint(-$rankLost);
                $result['Bonus'][100]['Normal'][] = array('ItemType'=>'Rank','Num'=>-$rankLost); 
            }
            else
            {
                $result['Bonus'][100]['Normal'][] = array('ItemType'=>'Rank','Num'=>0); 
            }
        }                          
        // cong buff tien va exp 
        $conf_BuffItem = Common::getConfig('BuffItem');
        $perMoney = 0;
        $perExp = 0 ;                       
        foreach($oSoldier->BuffItem as $idItem => $oItem)
        {
            if($oItem['ItemType'] == BuffItem::BuffMoney)
            {
                $perMoney = $conf_BuffItem[BuffItem::BuffMoney][1]['Num']*$oItem[Type::Num];
                continue ;
            }
            if($oItem['ItemType'] == BuffItem::BuffExp)
            {
                $perExp = $conf_BuffItem[BuffItem::BuffExp][1]['Num']*$oItem[Type::Num];
                continue ;
            } 
                 
        }                                           
        foreach($result['Bonus'][100]['Normal'] as $idbonus => $arr_bonus)
        {
            if($arr_bonus['ItemType'] == type::Money)
            {
                $arr_bonus['Num'] += round($arr_bonus['Num']*$perMoney/100);
            }
            else if($arr_bonus['ItemType'] == type::Exp)
            {
                $arr_bonus['Num'] += round($arr_bonus['Num']*$perExp/100);
            }
            
            $result['Bonus'][100]['Normal'][$idbonus] = $arr_bonus ;
        }                                               
        //---------------------       
        if (!isset($oUser->BattleStat['FirstTimeAttack']))
            $oUser->BattleStat['FirstTimeAttack'] = $_SERVER['REQUEST_TIME'];      
        // tru gem cua ca linh
        $oSoldier->updateGemAfterBattle() ;                              
        // update equipment durability
        $oSoldier->updateDurability();          
        // deletebuffItem
        foreach($oSoldier->BuffItem as $id => $oBuff)
        {
              if ($oBuff['Turn']<=1)
                unset($oSoldier->BuffItem[$id]);
              else $oSoldier->BuffItem[$id]['Turn']--;
        }                                                  
        // luu do vao kho 
        $this->saveActtackBonus($result['Bonus']);             
        // cong do xin vao trong list do dac
        $oSea->updateEquipmentGave($result['Bonus']);           
        // update thong tin sau tran danh 
        $oWorld->updateInfoAfterMatch($SeaId);                
        //update NumRound
        if($oSea->SeaId != SeaType::SEA_4)
        {
            $oSea->updateNumRound();
        }
        //$oWorld->updateInfoAfterMatch($SeaId);   
        $result['Error'] = Error::SUCCESS ; 
        $result['MyEnergy'] = $oUser->getRealEnergy(); 
        $result['IdRound'] = $idRound; 
        if($idRound == $oSea->RoundNum - 1)
        {
            
            $result['RoundId'] =  $idRound ;
        }
        else
        {
            $result['RoundId'] = 0 ;
        }
        
        $result['IdSea'] = $idSea; 
        $result['MyEquipment'] = StoreEquipment::getById(Controller::$uId);                              
        if($SeaId == SeaType::SEA_4)
        {                                                    
            if($oSea->RoundNum == SeaRound::ID_ROUND_1)  
            {                           
                $result['Sequence'] = $oSea->sequenceRedUp; 
            }
            else    if($oSea->RoundNum == SeaRound::ID_ROUND_3)
            {                                             
                $result['Sequence'] = $oSea->sequenceYellowDown; 
                $result['ArrHide'] = $oSea->arrHideInGreenDown; 
                $result['AttackTrue'] = $attackTrue; 
            }
            else    if($oSea->RoundNum == SeaRound::ID_ROUND_2)
            {                                                           
                unset($oSea->arrRandomBuff);      
                unset($oSea->currentMonster);
                $oSea->createObjRandomBuff();
                $result['Sequence'] = $oSea->arrRandomBuff;       
                $result['currentBoss'] = $oSea->currentMonster;      
            }
        }
        Zf_log::write_act_log(Controller::$uId,0,30,'acttackMonster',0,0,$SeaId,$oSea->RoundNum);
        
        $oWorld->save();
        $oLake->save();
        $oUser->save();
        $oStore->save();      
               
        return $result ;
    }
    
    private function reNewMonsterInRound3($oSea)
    {
        $monster_conf = Common::getWorldConfig('SeaMonster',$oSea->SeaId);  
        if(!is_array($monster_conf)) return false ;
        $monster_conf = $monster_conf[SeaRound::ID_ROUND_3];       
        $oSea->createSequenceYellowDown();          
        foreach ($monster_conf as $Id => $info)
        {
            if($Id != 6)    // hard code, chi khoi tao lai 5 con ca ban dau thoi
            {
                $Element = $info['Element'] ;
                if(empty($Element))
                    $Element = rand(Elements::KIM,Elements::HOA);
                $recipe = array ('ItemType'=>$info['RecipeType'],'ItemId'=>$Element);
                $equimentList = array ();
                if(!empty($info['Equipment']))
                {
                    $equimentList = array () ;
                    foreach($info['Equipment'] as $key => $arr_equ)
                    {
                         $equimentList[$key] = $this->getEquipmentFollowElement($arr_equ['ItemType'],$Element,$arr_equ['Rank'],$arr_equ['Color']) ; 
                    }
                }
                $oMonster = new Monster($Id,$Element,$info['Vitality'],$info['Dam'],$info['Defend'],$info['Critical'],$info['Health'],$equimentList,$recipe,$info['IsBoss']);
                
                if(!empty($info['Rank']))
                    $oMonster->Rank = $info['Rank'];
                
                $oSea->Monster[SeaRound::ID_ROUND_3][$Id] = $oMonster ;
            }
        }
    }
    
    private function getIndex($oMonster, $oSea)
    {
        foreach($oSea->sequenceYellowDown as $index => $element) 
        {
            if($element == $oMonster->Element)
            {
                return $index;
            }
        }
    }
    private function getCurElement($oMonster, $oSea)
    {
        $sequenceYellowDown = $oSea->sequenceYellowDown;
        for($i = 1; $i <= 5; $i++)
        {
            if(!empty($sequenceYellowDown[(string)$i]))
            {
                return $sequenceYellowDown[(string)$i];
            }
        }
    }
    
    private function checkWin($array_Acttack,$oSea,$oMonster,$is_boss = false)
    {
        $arr = array();
        $arr['Bonus'] = array();      
        $arr['is_win'] = Battle::LOSE; 
        $arr['isdie'] = array();

        $monster_conf = Common::getWorldConfig('SeaMonster',$oSea->SeaId,$oSea->RoundNum);
        $monster_conf = $monster_conf[$oMonster->Id];   
        
        $fivePerVitality = floor(($monster_conf['Vitality']*5)/100) ;
        $lastTimeGift = 0 ;
        for($i= 1 ; $i < count($array_Acttack) ;$i++) 
        {         
            $bloodMonster = $array_Acttack[$i]['Vitality']['Defence']['Left'];
            // tinh so luong ca linh con sau moi turn 
            $count = 0 ;
            foreach($array_Acttack[$i]['Vitality']['Attack'] as $IdSoldier => $Vitality)
            {
                if($Vitality > 0)
                    $count += 1 ; 
                else
                {
                    if(!isset($arr['isdie'][$IdSoldier]))
                        $arr['isdie'][$IdSoldier] = true ;
                }                   
            } 
               
            if($bloodMonster <= 0 && $count > 0 )                   
            {
                $arr['is_win'] = Battle::WIN ;
                
                // get qua tang 
                if($is_boss)
                {     
                    $lastTimeGift =floor(($monster_conf['Vitality']- $bloodMonster)/$fivePerVitality);
                                        
                    $RateWin = $lastTimeGift*5 ;
                      
                     while($lastTimeGift != 0 && !isset($arr['Bonus'][$RateWin]))
                     {
                        $arr['Bonus'][$RateWin] = $oSea->getBossBonus($oMonster,rand(1,5),$RateWin); 
                        $RateWin -=5 ;
                        $lastTimeGift -=1 ;
                     }
                   // xoa nhung con ca bi die   
                   $arr['isdie'] = array();    
                   // $RateWin = 100 ;
                   // $arr['Bonus'][100] = $oSea->getBossBonus($oMonster,rand(1,5),$RateWin);
                }
                else
                {
                    // get qua tang quai thuong
                    $arr['Bonus'][100] = $oSea->getMonsterBonus($oMonster,true) ; 
                }
                break ;
                
                
             }
             else
             {
                $arr['is_win'] = Battle::LOSE ;    
                if($is_boss)
                {
                    $lastTimeGift =floor(($monster_conf['Vitality']- $bloodMonster)/$fivePerVitality);
                                        
                    $RateWin = $lastTimeGift*5 ;
                      
                     while($lastTimeGift != 0 && !isset($arr['Bonus'][$RateWin]))
                     {
                        $arr['Bonus'][$RateWin] = $oSea->getBossBonus($oMonster,rand(1,5),$RateWin); 
                        $RateWin -=5 ;
                        $lastTimeGift -=1 ;
                     }
                    
 
                }
             }             
        }
        
        if($is_boss)
        {
            // log
            Zf_log::write_act_log(Controller::$uId,0,30,'acttackBoss',0,0,$this->SeaId);
        }
        
        return $arr ;
        
    }
    private function saveActtackBonus($arr)
    {
         if(empty($arr))
            return false ;
         $oUser     = User :: getById(Controller::$uId) ;   
         $oStore    = Store::getById(Controller::$uId) ;
         
         foreach ($arr as $per => $arr_Bonus)
         {
             
                 // save Normal Gift
             if(!empty($arr_Bonus['Normal']))
             {
                 $oUser->saveBonus($arr_Bonus['Normal']) ;
             }
             
			if(!empty($arr_Bonus['Event']))
             {
                 $oUser->saveBonus($arr_Bonus['Event']) ;
             }
             if(!empty($arr_Bonus['Collection']))
             {
                 foreach($arr_Bonus['Collection'] as $key => $Value)
                 {
                      $oStore->addItem($Value['ItemType'],$Value['ItemId'],$Value['Num']); 
                 }    
             }
             
             if(!empty($arr_Bonus['GemList']))
             {
                 foreach($arr_Bonus['GemList'] as $key => $Value)
                 {
                      $oStore->addGem($Value['Element'], $Value['ItemId'],$Value['Day'], $Value['Num']) ;  
                 }    
             }
             
             if(!empty($arr_Bonus['MixFomula']))
             {
                 foreach($arr_Bonus['MixFomula'] as $key => $Value)
                 {
                      $oStore->addItem($Value['ItemType'],$Value['ItemId'],$Value['Num']);     
                 }    
             }
             if(!empty($arr_Bonus['Material']))
             {
                 foreach($arr_Bonus['Material'] as $key => $Value)
                 {
                      $oStore->addItem($Value['ItemType'],$Value['ItemId'],$Value['Num']);     
                 }    
             }
             
             
             if(!empty($arr_Bonus['ItemList']))
             {   
                 foreach($arr_Bonus['ItemList'] as $Id => $oEquip)
                 {
                     if(is_object($oEquip))
                     {
                         $oStore->addEquipment($oEquip->Type,$oEquip->Id,$oEquip); 
                        Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip);
                         
                     }
                     
                 }
                 
             }
             
             if(!empty($arr_Bonus['Mask']))
             {
                 foreach($arr_Bonus['Mask'] as $key => $Value)
                 {
                     $conf_E = Common::getConfig('Wars_'.$Value['ItemType'],$Value['Rank'],$Value['Color']);
                          
                     $oEquip = new Equipment($oUser->getAutoId(),$conf_E['Element'],$Value['ItemType'],$Value['Rank'],$Value['Color'],
                        rand($conf_E['Damage']['Min'],$conf_E['Damage']['Max']),rand($conf_E['Defence']['Min'],$conf_E['Defence']['Max']),
                        rand($conf_E['Critical']['Min'],$conf_E['Critical']['Max']),$conf_E['Durability'],$conf_E['Vitality'],SourceEquipment::FISHWORLD);
                    $oStore->addEquipment($Value['ItemType'],$oEquip->Id,$oEquip);
                    
                    Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip); 
                 }    
                 
             }
             
         }

         $oStore->save();
         $oUser->save();
           
           
    }
    
    /**
    * su dung hoa sen hoi sinh 
    */
    
    public function useLotusFlower($param)
    {
        $SoldierList  = $param['SoldierList'];
        
        if(empty($SoldierList))
            return array('Error' => Error::PARAM);
            
        $oUser = User :: getById(Controller::$uId) ;       
        
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $zMoneyDiff = $oUser->ZMoney;
        $conf_Lotus = Common::getConfig('LotusFlower',1);
        
        foreach($SoldierList as $LakeId => $Arr_Soldier)
        {
            $oLake = Lake::getById(Controller::$uId,$LakeId);
            if (!is_object($oLake))
            {
                return array('Error' => Error :: LAKE_INVALID) ;
            }
            foreach($Arr_Soldier as $SoldierId)
            {
            
                $oSoldier = $oLake->getFish($SoldierId) ;         
                if (!is_object($oSoldier))
                {
                    return array('Error' => Error :: OBJECT_NULL) ;
                }

                $info = '1:LotusFlower:1' ;
                if (!$oUser->addZingXu(-$conf_Lotus['ZMoney'],$info))
                    return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;

                $oSoldier->updateIsDie(false);
            }    
            $oLake->save();
            $oUser->save();
        }
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[Type::LotusFlower]))
        {
            $TypeItemId = $conf_log[Type::LotusFlower];
        }
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        Zf_log::write_act_log(Controller::$uId,0,23,'buyOther',0,$zMoneyDiff,$TypeItemId,1,0,0,
                                        round($zMoneyDiff/$conf_Lotus['ZMoney']));
       
       return array('Error' => Error :: SUCCESS) ; 
       
        
    }
        
}
?>
