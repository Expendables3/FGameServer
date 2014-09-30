<?php
  class UpdateDataVersion
  {
      public static function update_46($uId)
      {
           // update tren ca
            $oStoreEquip = StoreEquipment::getById($uId);
            //$oEquip = $oStoreEquip->SoldierList[$FishId]['Equipment'][$oEquip->Type][$oEquip->Id] =
            foreach($oStoreEquip->SoldierList as $FishId => $Arr_Fish)
            {
                if(!is_array($Arr_Fish))
                    continue ;
                // xoa cac chi so 
                $oStoreEquip->SoldierList[$FishId]['Index'] = array();
                
                foreach($Arr_Fish as $IndexName => $Arr_Equip)
                {
                    if(!is_array($Arr_Equip))
                        continue ;
                    if($IndexName == 'Equipment')
                    {
                        foreach($Arr_Equip as $TypeName => $Arr_Type)
                        {
                            if(!is_array($Arr_Type)|| $TypeName == SoldierEquipment::Seal ||$TypeName == SoldierEquipment::Mask)
                                continue ;
                            foreach($Arr_Type as $EquipId => $oEquip)
                            {
                                if(!is_object($oEquip))
                                    continue ;
                                    
                                $conf = Common::getConfig('Wars_'.$oEquip->Type,$oEquip->Rank,$oEquip->Color);
                                if(empty($conf))
                                    continue ;
                                $arr['Damage']      = rand($conf['Damage']['Min'],$conf['Damage']['Max']);
                                $arr['Defence']     = rand($conf['Defence']['Min'],$conf['Defence']['Max']);
                                $arr['Critical']    = rand($conf['Critical']['Min'],$conf['Critical']['Max']);
                                $arr['Durability']  = $oEquip->Durability;
                                $arr['Vitality']    = $conf['Vitality'];

                                $oEquip_new = new Equipment($oEquip->Id,$oEquip->Element,$oEquip->Type,$oEquip->Rank,$oEquip->Color,$arr['Damage'],$arr['Defence'],$arr['Critical'],$oEquip->Durability,$arr['Vitality'],$oEquip->Source,0);

                                $oEquip_new->Author = $oEquip->Author ;
                                $oEquip_new->StartTime = $oEquip->StartTime ;
                                $oEquip_new->IsUsed = $oEquip->IsUsed ;
                                $oEquip_new->InUse = $oEquip->InUse ;      
                                
                                // clone bonus of equipment    
                                $oEquip_new->bonus = array();
                                
                                $arrConver = Common::getParam('ConvertIncreaseEquipment');
                                $keyAttribute = array_flip($arrConver);
                                
                                foreach($oEquip->bonus as $id => $attribute_arr)
                                {
                                    if(empty($attribute_arr))
                                        continue ;
                                    foreach($attribute_arr as $attribute => $val )
                                    {   
                                        $value = rand($conf[$keyAttribute[$attribute]]['Min'],$conf[$keyAttribute[$attribute]]['Max']);
                                        $oEquip_new->bonus[$id][$attribute] =  intval($value) ;
                                        break;
                                    }
    
                                    
                                }          
                                
                                // thuc hien enchant
                                if($oEquip->EnchantLevel > 0)
                                {
                                    for ($i=1; $i <= $oEquip->EnchantLevel; $i++)
                                    $oEquip_new->enchant(101,true);
                                }   
                                
                                $oStoreEquip->SoldierList[$FishId][$IndexName][$TypeName][$EquipId] = $oEquip_new;
                                
                                // cong lai chi so do cho ca
                                if ($oEquip_new->InUse)
                                {
                                    $oStoreEquip->addBonusEquipment($FishId, $oEquip_new->getIndex(),true);
                                }
                            }
                        }
                    }
                
                }
            }
            
            
            // update trong kho
            $oStore = Store::getById($uId);
            
            foreach($oStore->Equipment as $TypeE =>$arr_E)
            {
                if(empty($arr_E))
                    continue ;
                
                if($TypeE == SoldierEquipment::Seal || $TypeE == SoldierEquipment::Mask)
                    continue ;
                    
                foreach($arr_E as $index => $oEquip)
                {
                    if(!is_object($oEquip))
                        continue ;
                    
                    $conf = Common::getConfig('Wars_'.$oEquip->Type,$oEquip->Rank,$oEquip->Color);
                    $arr['Damage']      = rand($conf['Damage']['Min'],$conf['Damage']['Max']);
                    $arr['Defence']     = rand($conf['Defence']['Min'],$conf['Defence']['Max']);
                    $arr['Critical']    = rand($conf['Critical']['Min'],$conf['Critical']['Max']);
                    $arr['Durability']  = $oEquip->Durability;
                    $arr['Vitality']    = $conf['Vitality'];

                    $oEquip_new = new Equipment($oEquip->Id,$oEquip->Element,$oEquip->Type,$oEquip->Rank,$oEquip->Color,$arr['Damage'],$arr['Defence'],$arr['Critical'],$oEquip->Durability,$arr['Vitality'],$oEquip->Source,0);

                    $oEquip_new->Author = $oEquip->Author ;
                    $oEquip_new->StartTime = $oEquip->StartTime ;
                    $oEquip_new->IsUsed = $oEquip->IsUsed ;
                    $oEquip_new->InUse = $oEquip->InUse ;      
                    
                    // clone bonus of equipment    
                    $oEquip_new->bonus = array();
                                        
                    $arrConver = Common::getParam('ConvertIncreaseEquipment');
                    $keyAttribute = array_flip($arrConver);
                    foreach($oEquip->bonus as $id => $attribute_arr)
                    {
                        if(empty($attribute_arr))
                            continue ;
                        
                        foreach($attribute_arr as $attribute => $val )
                        {   
                            $value = rand($conf[$keyAttribute[$attribute]]['Min'],$conf[$keyAttribute[$attribute]]['Max']);
                            $oEquip_new->bonus[$id][$attribute] =  intval($value) ;
                            break;
                            
                        }
                            
                        
                    }          
                    
                    // thuc hien enchant
                    if($oEquip->EnchantLevel > 0)
                    {
                        for ($i=1; $i <= $oEquip->EnchantLevel; $i++)
                            $oEquip_new->enchant(101,true);
                    }
                    
                    $oStore->addEquipment($TypeE,$index,$oEquip_new);
                    
                }
                
            }
            
            
            $oStoreEquip->save();
            $oStore->save();
      }
      
      // Update event delete 8-3
      public static function update_47($uId)
      {
        if(!Event::checkEventCondition('Event_8_3_Flower'))
            return false;
              
        $oEvent = Event::getById($uId);
        if(!is_object($oEvent))
            return false;

        if(isset($oEvent->EventList[EventType::Event_8_3_Flower]))
        {
            $oEvent->resetEvent(EventType::Event_8_3_Flower);
            $oEvent->save();
        }
         
        return true;
      }
      
      //
      // Update event delete 8-3 and event PearFlower
      public static function update_48($uId)
      {
            if(!Event::checkEventCondition(EventType::PearFlower))
                return false;
              
            $oEvent = Event::getById($uId);
            if(!is_object($oEvent))
                return false;

            if(isset($oEvent->EventList[EventType::PearFlower]))
            {
                $oEvent->resetEvent(EventType::PearFlower);
                $oEvent->save();
            }
                            
            if(!Event::checkEventCondition(EventType::Event_8_3_Flower))
                return false;
                
            if(isset($oEvent->EventList[EventType::Event_8_3_Flower]))
            {
                $oEvent->resetEvent(EventType::Event_8_3_Flower);
                $oEvent->save();
            }
            
            // xoa do cua event truoc trong kho di
            $oStore = Store::getById($uId);
            if(!is_object($oStore))
                return false;
            if(isset($oStore->EventItem[EventType::PearFlower]))
                $oStore->EventItem[EventType::PearFlower] = array();
            
            if(isset($oStore->Items[Type::Arrow]))
                unset($oStore->Items[Type::Arrow]);
            if(isset($oStore->Items[Type::VipMedal]))
                unset($oStore->Items[Type::VipMedal]);                
            
            if(isset($oStore->EventItem[EventType::Event_8_3_Flower]))
                $oStore->EventItem[EventType::Event_8_3_Flower] = array();
            
            $oStore->save(); 
             
            return true;
      }
      
      public static function udpate_50($uId)
      {
            $oProfile = OccupyingProfile::getByUid($uId);
            if(is_object($oProfile))
            {
                DataProvider::delete($uId, 'OccupyingProfile');
                $oStore = Store::getById($uId);
                $oStore->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_DEFAULT] = 0;
                $oStore->save();
            }
      }
            
      // he thong danh tieng 
      public static function udpate_55($uId)
      {
            $oUser = User::getById($uId);
            if(is_object($oUser))
            {
                // he thong danh tieng
                if (Event::checkEventCondition('ReputationQuest'))
                {
                    if(empty($oUser->ReputationQuest))
                        $oUser->initReputationQuest();
                }
            }
            $oUser->save(); 
      }                
      
      public static function update_resetEvent($uId, $eventType = '')
      {
          $oEvent = Event::getById($uId);
          if(!is_object($oEvent))
            return false;
          if(empty($eventType))
            $oEvent->EventList = array();
          else
            unset($oEvent->EventList[$eventType]);
          $oEvent->save();          
      }
      
      public static function update_57($uId)
      {          
          $equipIds = array(); 
          $oUser = User::getById($uId);
          
          // filter & fix on Soldier                    
          $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {   
                        $equipSoldier = array();
                        $equipSoldier = $oStoreEquip->SoldierList[$id]['Equipment'];             
                        foreach ($equipSoldier as $type => $typeEquips)
                        {
                             foreach($typeEquips as $ideq => $oEquip)
                              {
                                  if(in_array($ideq, $equipIds))
                                  {
                                      $newId = $oUser->getAutoId();
                                      $oEquip->Id = $newId;
                                      unset($oStoreEquip->SoldierList[$id]['Equipment'][$type][$ideq]);
                                      $oStoreEquip->SoldierList[$id]['Equipment'][$type][$newId] = $oEquip; 
                                  }
                                  else $equipIds[] = $ideq;                    
                              }                                               
                        }
                    }                
                }
                $oLake->save();
            }   
            $oStoreEquip->save();
                    
          // filter & fix on Store
          $oStore = Store::getById($uId);
          $equipStore = array();
          $equipStore = $oStore->Equipment; 
          
          foreach($equipStore as $type => $typeEquips)
          {
              foreach($typeEquips as $id => $oEquip)
              {
                  if(in_array($id, $equipIds))
                  {
                      $newId = $oUser->getAutoId();
                      $oEquip->Id = $newId;
                      
                      $oStore->removeEquipment($type, $id);
                      $oStore->addEquipment($type, $newId, $oEquip);
                  }
                  else $equipIds[] = $id;                    
              }              
          }
          $oStore->save();
          
          // filter & fix on Market
          $oMarket = Market::getById($uId);
          $currentAutoMarket = DataRunTime::get('Market_'.$uId,true);          
          if($currentAutoMarket == 0)
          {
             DataRunTime::inc('Market_'.$uId,1,true); 
             $equipType = array(SoldierEquipment::Armor, SoldierEquipment::Belt, SoldierEquipment::Bracelet, SoldierEquipment::Helmet, SoldierEquipment::Necklace, SoldierEquipment::Ring, SoldierEquipment::Weapon);
             foreach($oMarket->ItemList as $index => $arrObject)
             {
                 if(in_array($arrObject['Type'], $equipType))
                 {
                    $oObject = $arrObject['Object'];
                    if(in_array($oObject->Id, $equipIds))
                      {
                          $newId = $oUser->getAutoId();
                          $oObject->Id = $newId;
                          $oMarket->ItemList[$index]['Object'] = $oObject;
                      }
                    else $equipIds[] = $oObject->Id; 
                 }
             }
             DataRunTime::dec('Market_'.$uId,1,true); 
             $oMarket->save();
          }          
                    
          $oUser->save();
      }

      // convert number change option to property of equipment
      public static function update_58($uId) 
      {
          // get MakeOption data                    
          $Types = array(SoldierEquipment::Armor,SoldierEquipment::Helmet,SoldierEquipment::Weapon,SoldierEquipment::Ring,SoldierEquipment::Bracelet,SoldierEquipment::Necklace,SoldierEquipment::Belt);
          $oHammerMan = HammerMan::getById($uId);
          
          $MakeOption = $oHammerMan->getMakeOption();
          $oStore = Store::getById($uId);
          $StoreEquipment = $oStore->getAllEquipment();          
                    
          foreach($MakeOption as $Id => $Num ){              
              // Find in Store
              $isNotFind = true;
              foreach($Types as $Type) {
                 //echo "Type = ".$Type."<br />"; 
                 if(isset($StoreEquipment[$Type][$Id])) {
                     $oEquip = $StoreEquipment[$Type][$Id];
                     if(is_object($oEquip)) {
                         $oEquip->NumChangeOption = $Num;
                         $StoreEquipment[$Type][$Id] = $oEquip;
                         $isNotFind = false;                         
                     }
                     
                 }
                 if(!$isNotFind) break;
             }      
             // Find MakeOption 
             if($isNotFind) {
                 $oEquip = $oHammerMan->getEquip();
                 if(is_object($oEquip) && intval($oEquip->Id) == $Id) {
                     $oEquip->NumChangeOption = $Num;
                     $oHammerMan->setEquip($oEquip);
                     $isNotFind = false;
                 }
             }
             // Find on Soldier 
             if($isNotFind) {
                $oUser = User::getById($uId); 
                $oStoreEquip = StoreEquipment::getById($uId);
                                
                for($i=1; $i<=$oUser->LakeNumb; $i++)
                {                    
                    $oLake = Lake::getById($uId,$i);
                    foreach($oLake->FishList as $id => $oFish)
                    {
                       
                        if ($oFish->FishType == FishType::SOLDIER)
                        {   
                            $equipSoldier = array();
                            $equipSoldier = $oStoreEquip->SoldierList[$id]['Equipment'];             
                            foreach ($equipSoldier as $type => $oEquip)
                            {              
                                
                                if(isset($oEquip[$Id]) ) {
                                    $oEquip = $oEquip[$Id];
                                    $oEquip->NumChangeOption = $Num;            
                                    $type = $oEquip->Type;
                                    $oStoreEquip->SoldierList[$id]['Equipment'][$type][$Id] = $oEquip; 
                                    $isNotFind = false;                                    
                                }                                       
                                if(!$isNotFind) break;                      
                            }
                            if(!$isNotFind) break;                            
                        }
                        if(!$isNotFind) break;                       
                    }
                    $oLake->save();                    
                }                
                $oStoreEquip->save();
             }       
          }                    
          $oStore->save();
          $oHammerMan->makeOption = array();
          $oHammerMan->save();
      }


      public static function update_59($uId) 
      {
            $arrQuartzType = Common::getConfig('General', 'QuartzTypes');            
            $oUser = User::getById($uId);
            $oStoreEquip = StoreEquipment::getById($uId);
            $oSmashEgg = SmashEgg::getById($uId);
            $oStore = Store::getById($uId); 
            $arrSoldier = Lake::getAllSoldier($uId,true,true,true);
            $arrSoldierId = array();     
                        
            for($i=1; $i<=$oUser->LakeNumb; $i++) {            
                
                $FishList = $arrSoldier[$i];
                foreach($FishList as $SoldierId => $oFish) {
                    //array_push($arrSoldierId,$id);                    
                    $EquipSoldier = $oStoreEquip->SoldierList[$SoldierId]['Equipment'];
                    foreach($EquipSoldier as $eType => $Equips){
                        if(in_array($eType, $arrQuartzType) ){
                            foreach($Equips as $eId => $Equip) {
                                $QType = $Equip->Type;
                                $QId = $Equip->Id;
                                $isFind = false;
                                $Slots = $oSmashEgg->getSoldierSlot($SoldierId);
                                if( $Slots != null) {                                    
                                    foreach($Slots as $Slot) {
                                        if($Slot["QuartzType"] == $QType && $Slot["Id"] == $QId) {
                                            $isFind = true; break;
                                        }
                                    }                                     
                                }
                                if($isFind == false) {
                                    // restore Quartz
                                    $oQuartz = $oStoreEquip->getQuartz($SoldierId,$QType, $QId);                                                                        
                                    // remove Quartz out Store Equipment                                    
                                    $oStoreEquip->deleteQuartz($SoldierId,$QType, $QId);
                                    $oStoreEquip->updateBonusEquipment($SoldierId);                                            
                                    // change property and add Store
                                    $oQuartz->IsUsed = false;                                    
                                    $oStore->addQuartz($QType, $QId, $oQuartz);                                                                                                                    
                                }                                                                
                            }                                                        
                        }
 
                    }
                    
                }
                
            }  
            
            $oStoreEquip->save();
            $oStore->save();                          
            
      }
      
      public static function update_60($uId) {
           $oKeepLogin = KeepLogin::getById($uId);
           $oKeepLogin->resetDataKeepLogin();
           $oKeepLogin->save();     
      }

      public static function update_61($uId) {
           UpdateDataVersion::update_60($uId);
           $oHammerMan = HammerMan::getById($uId);
           $oHammerMan->convertPoint();
           $oHammerMan->save();     
           $oNoel = Noel::getById($uId);
           $oNoel->setCurrentBoardId(0);
           $oNoel->save();
      }
      
      public static function update_62($uId) 
      {     
            $oUser = User::getById($uId);
          
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                if(!is_object($oLake))
                    continue ;
                foreach($oLake->FishList as $id => $oFish)
                {
                    if(!is_object($oFish))
                        continue ;
                        
                    if ($oFish->FishType != FishType::SOLDIER)
                        continue ;
                    $oFish->reborn(5184000) ;
                }
                $oLake->save() ;
            }
      }
      
      public static function update_63($uId) {
          UpdateDataVersion::update_60($uId);
          //DataRunTime::set('NumView6Star', 0, true);
          $oVipBox = VipBox::getById($uId);        
          $oVipBox->setNumOpen(0);
          $oVipBox->setNumKey(0);
          $oVipBox->save();
          //DataRunTime::set('QuotaVipMax', 0, true);
          
          $oAccumulationPoint = AccumulationPoint::getById($uId);
          $oAccumulationPoint->setPoint(0);
          $oAccumulationPoint->save();
          
          $oStore = Store::getById($uId);
          $oEvent = Event::getById($uId);
          if(is_object($oStore))
          {
              $oStore->EventItem[EventType::TreasureIsland] = array();
              $oStore->save();
          }
          if(is_object($oEvent))
          {
                $oEvent->EventList[EventType::TreasureIsland]['Map']          = array();  // map
                $oEvent->EventList[EventType::TreasureIsland]['MapId']        = 0 ;
                $oEvent->EventList[EventType::TreasureIsland]['TempGift']     = array(); //qua tam thoi lam tren map
                $oEvent->EventList[EventType::TreasureIsland]['Treasure']     = array();  // qua nhan duoc tam thoi     
                $oEvent->EventList[EventType::TreasureIsland]['JoinNum']      = 0 ;
                $oEvent->EventList[EventType::TreasureIsland]['LastJoinTime'] = 0 ;

                $oEvent->HideParam[EventType::TreasureIsland]['GiftOnMap']    = array();  // qua tren map
        
                $oEvent->save();
          }
          
          
      }  
      
      public function update_65($uId) {
           UpdateDataVersion::update_60($uId);
      }
      
      //update lai chi ro rankpoint cho ngu thu 
      public static function update_66($uId) 
      {

            $conf_rank_Old  = Common::getConfig('RankPoint_Old');   
            $conf_rank      = Common::getConfig('RankPoint');   
            $oUser = User::getById($uId);

            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                if(!is_object($oLake))
                continue ;
                foreach($oLake->FishList as $id => $oFish)
                {
                    if(!is_object($oFish))
                        continue ;

                    if ($oFish->FishType != FishType::SOLDIER)
                        continue ;
                    
                    if($oFish->Rank <= 40)
                        continue;

                    // tinh nguoc
                    for($k = $oFish->Rank ; $k >=1 ;$k--)
                    {
                        $oFish->Damage      =  floor($oFish->Damage/(1 + $conf_rank_Old[$k]['RateDamage']));
                        $oFish->Defence     =  floor($oFish->Defence/(1 + $conf_rank_Old[$k]['RateDefence']));  
                        $oFish->Critical    =  floor($oFish->Critical/(1 + $conf_rank_Old[$k]['RateCritical']));  
                        $oFish->Vitality    =  floor($oFish->Vitality/(1 + $conf_rank_Old[$k]['RateVitality']));  
                    } 

                    //tinh xuoi 
                    for($m = 1; $m < $oFish->Rank;$m++)
                    {
                        $j = $m + 1;
                        $oFish->Damage      +=  ceil(($oFish->Damage)*$conf_rank[$j]['RateDamage']);  
                        $oFish->Defence     +=  ceil(($oFish->Defence)*$conf_rank[$j]['RateDefence']);  
                        $oFish->Critical    +=  ceil(($oFish->Critical)*$conf_rank[$j]['RateCritical']);    
                        $oFish->Vitality    +=  ceil(($oFish->Vitality)*$conf_rank[$j]['RateVitality']);  
                    } 
                }
            
                $oLake->save() ;
            }

      } 
      
       public static function update_67($uId) 
       {
          UpdateDataVersion::update_63($uId);
       }
       
       public static function update_68($uId) 
       {
          UpdateDataVersion::update_60($uId);
          
          $oAccumulationPoint = AccumulationPoint::getById($uId);
          $oAccumulationPoint->setPoint(0);
          $oAccumulationPoint->save();
          
          $oStore = Store::getById($uId);
          $oEvent = Event::getById($uId);
          if(is_object($oStore))
          {
              $oStore->EventItem[EventType::EventActive] = array();
              $oStore->save();
          }
          if(is_object($oEvent))
          {
                $oEvent->EventList[EventType::EventActive]  = array();
                $oEvent->save();
          }
          
          
      } 
      
      // Update event delete 8-3 and event PearFlower
      public static function update_69($uId)
      {
            if(!Event::checkEventCondition(EventType::PearFlower))
                return false;
            self::update_68($uId);
            $oEvent = Event::getById($uId);
            if(!is_object($oEvent))
                return false;
            
            // xoa do cua event truoc trong kho di
            $oStore = Store::getById($uId);
            if(!is_object($oStore))
                return false;
            if(isset($oStore->EventItem[EventType::PearFlower]))
                unset($oStore->EventItem[EventType::PearFlower]) ;
            
            if(isset($oStore->Items[Type::Arrow]))
                unset($oStore->Items[Type::Arrow]);
            if(isset($oStore->Items[Type::VipMedal]))
                unset($oStore->Items[Type::VipMedal]);                
            
            $oStore->save(); 
            
            DataRunTime::set('QuotaVipMax',0,true);
            
            $oVipBox = VipBox::getById($uId);        
            $oVipBox->setNumOpen(0);
            $oVipBox->setNumKey(0);
            $oVipBox->save();
             
            return true;
      }
      
      public static function update_72($uId)
      {
           self::update_63($uId);
      }
      
      // pattern reset any event
      public static function update_73($uId)
      {
           $oUser = User::getById($uId) ;
           if(is_object($oUser))
           {
               if(isset($oUser->FirstAdXu))
               {
                   $oUser->FirstAddXu += intval($oUser->FirstAdXu) ;
                   $oUser->FirstAdXu = 0  ;
                   $oUser->save() ;
               }
           }
      }  
      public static function update_74($uId)
      {
          if(!Event::checkEventCondition(EventType::EventActive))
                return false;
                
          $oKeepLogin = KeepLogin::getById($uId);
          $oKeepLogin->resetDataKeepLogin();          

          $oVipBox = VipBox::getById($uId);        
          $oVipBox->setNumOpen(0);
          $oVipBox->setNumKey(0);
                   
          
          $oAccumulationPoint = AccumulationPoint::getById($uId);
          $oAccumulationPoint->setPoint(0);          
          
          $oEvent = Event::getById($uId);
          $oStore = Store::getById($uId);
          if(!is_object($oEvent))
                return false;
          unset($oEvent->EventList[EventType::EventActive]);
                
          if(is_object($oStore))
          {
              $oStore->EventItem[EventType::EventActive] = array();
              $oStore->save();
          }
          
          $oEvent->save();                                    
          $oVipBox->save(); 
          $oKeepLogin->save();          
          $oAccumulationPoint->save();          
      } 
      
      // sua loi do vip vi sai config khi cuong hoa 
      public static function update_75($uId)
      {          
          $equipIds = array(); 
          $oUser = User::getById($uId);
          $equipType = array(SoldierEquipment::Armor, SoldierEquipment::Belt, SoldierEquipment::Bracelet, SoldierEquipment::Helmet, SoldierEquipment::Necklace, SoldierEquipment::Ring, SoldierEquipment::Weapon);
          // filter & fix on Soldier                    
          $oStoreEquip = StoreEquipment::getById($uId);
            for($i=1; $i<=$oUser->LakeNumb; $i++)
            {
                $oLake = Lake::getById($uId,$i);
                foreach($oLake->FishList as $id => $oFish)
                {
                    if ($oFish->FishType == FishType::SOLDIER)
                    {   
                        $equipSoldier = array();
                        $equipSoldier = $oStoreEquip->SoldierList[$id]['Equipment'];             
                        foreach ($equipSoldier as $type => $typeEquips)
                        {
                            if(!in_array($type, $equipType))
                                continue;
                                
                             foreach($typeEquips as $ideq => $oEquip)
                              {
                                    if(!is_object($oEquip))
                                        continue ;
                                    $Rank = ($oEquip->Rank%100) ;
                                    if($oEquip->Color >= 5 && $Rank >=4 && $oEquip->EnchantLevel >= 1 && $oEquip->StartTime >= mktime(8,0,0,8,20,2013) && $oEquip->StartTime <= mktime(21,0,0,8,20,2013))
                                    {      
                               
                                          $Equip_new = Common::randomEquipment($oEquip->Id,$Rank,$oEquip->Color,$oEquip->Source,$oEquip->Type,
                                          $oEquip->EnchantLevel,$oEquip->Element,5);                                           
                                          if(!is_object($Equip_new))
                                          {
                                              continue;
                                          }                               
                                          $Equip_new->PercentBonus = $oEquip->PercentBonus;
                                          $oStoreEquip->SoldierList[$id]['Equipment'][$type][$ideq] = $Equip_new ;                                                        
                                    }
                              }                                               
                        }
                        $oStoreEquip->updateBonusEquipment($id);
                    }                
                }
                $oLake->save();
            }   
            $oStoreEquip->save();
                    
          // filter & fix on Store
          $oStore = Store::getById($uId);
          $equipStore = array();
          $equipStore = $oStore->Equipment; 
          
          foreach($equipStore as $type => $typeEquips)
          {
             if(!in_array($type, $equipType))
                continue;
              foreach($typeEquips as $id => $oEquip)
              {
                    if(!is_object($oEquip))
                        continue ;
                        $Rank = ($oEquip->Rank%100) ;            
                    if($oEquip->Color >= 5 && $Rank >=4 && $oEquip->EnchantLevel >= 1 && $oEquip->StartTime >= mktime(8,0,0,8,20,2013) && $oEquip->StartTime <= mktime(21,0,0,8,20,2013))
                    {
                          $Equip_new = Common::randomEquipment($oEquip->Id,$Rank,$oEquip->Color,$oEquip->Source,$oEquip->Type,
                          $oEquip->EnchantLevel,$oEquip->Element,5); 
                          if(!is_object($Equip_new))
                          {
                              continue;                        
                          }                               
                          $oStore->Equipment[$type][$id] = $Equip_new ;              
                    }
              }              
          }
          $oStore->save();
          
          // filter & fix on Market
          $oMarket = Market::getById($uId);
          $currentAutoMarket = DataRunTime::get('Market_'.$uId,true);          
          if($currentAutoMarket == 0)
          {
             DataRunTime::inc('Market_'.$uId,1,true); 
             $equipType = array(SoldierEquipment::Armor, SoldierEquipment::Belt, SoldierEquipment::Bracelet, SoldierEquipment::Helmet, SoldierEquipment::Necklace, SoldierEquipment::Ring, SoldierEquipment::Weapon);
             foreach($oMarket->ItemList as $index => $arrObject)
             {
                 if(in_array($arrObject['Type'], $equipType))
                 {
                    $oEquip = $arrObject['Object'];
                    $Rank = ($oEquip->Rank%100) ;            
                    if($oEquip->Color >= 5 && $Rank >=4 && $oEquip->EnchantLevel >= 1 && $oEquip->StartTime >= mktime(8,0,0,8,20,2013) && $oEquip->StartTime <= mktime(21,0,0,8,20,2013))
                    {
                          $Equip_new = Common::randomEquipment($oEquip->Id,$Rank,$oEquip->Color,$oEquip->Source,$oEquip->Type,
                          $oEquip->EnchantLevel,$oEquip->Element,5); 
                          
                          $oMarket->ItemList[$index]['Object'] = $Equip_new ;              
                    }
                 }
             }
             DataRunTime::dec('Market_'.$uId,1,true); 
             $oMarket->save();
          }                  

      }
      
      public static function update_76($uId)
      {
           $numVIP = 0;
           $oStore = Store::getById($uId);
           foreach($oStore->Quartz['QVIP'] as $qId => $oQuartz)
           {
               if($oQuartz->ItemId == 0)
               {
                   $oQuartz->ItemId = 13;
                   $numVIP ++;
               }
           }           
           $NumView6Star = intval(DataRunTime::get('NumView6Star', true));
           $Num6Star =  intval(DataRunTime::get('Num6Star', true));                       
           if($NumView6Star - $numVIP < 0)
           {                
                $Num6Star += $NumView6Star;
                $NumView6Star = 0;                
           }               
           else
           {
               $NumView6Star -=$numVIP;
               $Num6Star +=$numVIP;
           }           
           DataRunTime::set('NumView6Star', $NumView6Star, true);  
           DataRunTime::set('Num6Star', $Num6Star, true);           
           $oStore->save();
      } 
       
                
  }
?>
