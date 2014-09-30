<?php
  class ForestSea extends Sea
  {
      public $sequenceRedUp;
      public $sequenceYellowDown;
      public $arrHideInGreenDown;
      public $arrRandomBuff;
      public $arrGift;
      public $currentMonster;
      public function __Construct($SeaId)
      {
          Sea::__Construct($SeaId);
          $this->sequenceRedUp = array();
      }
      // ham thuc hien tao ra vi tri cua cac con quai tuong ung voi id cua con quai
      public function createSequenceRedUp()
      {
          $arr = array();
          for($i = 1; $i < 4; $i++)
          {
              $arr[(string)$i] = $i;
          }
          
          for($i = 1; $i < 4; $i++)
          {
              $index = array_rand($arr,1);
              $this->sequenceRedUp[(string)$i] = $arr[$index];
              unset($arr[$index]);
          }
      }
      
      // ham thuc hien tao ra thu tu danh quai trong vong 3
      public function createSequenceYellowDown()
      {
          // Thuc hien gen ra thu tu danh
          $arr = array();
          $this->sequenceYellowDown = array();
          $this->arrHideInGreenDown = array();
          for($i = 1; $i < 6; $i++)
          {
              $arr[(string)$i] = $i;
          }
          
          for($i = 1; $i < 6; $i++)
          {
              $index = array_rand($arr,1);
              $this->sequenceYellowDown[(string)$i] = $arr[$index];
              unset($arr[$index]);
          }
          
          // Thuc hien chon cac vi tri dem an di
          $arr = array();  
          for($i = 1; $i < 6; $i++)
          {
              $arr[(string)$i] = $i;
          }
          if($this->TypeHard == 100)
          {                                                
              $numHide = ForestParam::NUM_HIDE_MODE_HARD;
          }
          else
          {
              $numHide = ForestParam::NUM_HIDE_MODE_NORMAL;
          }
          for($i = 1; $i <= $numHide; $i++)
          {
              $index = array_rand($arr,1);
              $this->arrHideInGreenDown[(string)$i] = $arr[$index];
              unset($arr[$index]);
          }
      }
      
      /**
      * Ham thuc hien gen ra con boss hien tai dang danh neu chua co
      * gen ra trang thai buff hien tai cua than cho con boss va con ca linh nha minh
      */
      public function createObjRandomBuff()
      {
          $aMonster = $this->Monster[SeaRound::ID_ROUND_2];   
          do
          {                                                        
              $numMonsterMax = ForestParam::MAX_MONSTER_ROUND_2;
              if(count($aMonster) > 1)
              {
                  $idMonster = rand(1, $numMonsterMax - 1);
              }
              else
              {
                  $idMonster = $numMonsterMax;
              }                                          
              $this->currentMonster = $aMonster[$idMonster];          
          } while (!isset($this->currentMonster) && count($aMonster) > 0);
          $idBoss = $this->currentMonster->Id;
          $effect_confAll = Common::getWorldConfig('ForestEffect');
          $effect_conf = $effect_confAll[$idBoss];
          $rd = 0;
          $Percent = 0;
          // xac dinh anh huong phep thuat cho con boss
          $rd = rand(1,100);
          $Percent = 100;
          foreach($effect_conf as $typeEffect => $arrRate)
          {
              $Percent = $Percent - $arrRate['RateMonster'];
              if($rd > $Percent)
              {
                   $this->arrRandomBuff['Monster'] = $typeEffect;
                   break;
              }
          }
          $rd = rand(1,100);
          $Percent = 100;
          foreach($effect_conf as $typeEffect => $arrRate)
          {
              $Percent = $Percent - $arrRate['RateBoss'];
              if($rd > $Percent)
              {
                   if($this->arrRandomBuff['Monster'] == TypeEffectDeity::TYPE_BOLT)
                   {
                       $rd2 = rand(1,6);
                       if($rd2 == 1)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_DAMAGE_DECREASE;
                       }
                       else if($rd2 == 2)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_DAMAGE_INCREASE;
                       }
                       else if($rd2 == 3)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_DEFENCE_DECREASE;
                       }
                       else if($rd2 == 4)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_DEFENCE_INCREASE;
                       }
                       else if($rd2 == 5)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_HEALTHY_DECREASE;
                       }
                       else if($rd2 == 6)
                       {
                           $this->arrRandomBuff['Boss'] = TypeEffectDeity::TYPE_HEALTHY_INCREASE;
                       }
                   }
                   else
                   {
                       $this->arrRandomBuff['Boss'] = $typeEffect;
                   }
                   break;
              }
              $oldTypeEff = $typeEffect;
          }
      }
      
      public function generateScene($listSoldierAtt, $soldierDef, $isResistance = 1, $alpha = 0)
      {   
           $scene = array();
           $conf_health = Common::getConfig('RankPoint');
           $conf_Turn = Common::getParam('TurnAttack');
           $turn = 0;
                                       
           // chance to attack first
           $attackFirst = rand(0,1);
            
           // Attack list constant
           $attackIndex = array();
           
           foreach($listSoldierAtt as $id => $oSoldier)
           {
               // get all index 
               $attackIndex[$id] = $this->calculationIndexSoldier($oSoldier);
               $attackIndex[$id]['CurrentHealth'] = $oSoldier->getHealth();
               $attackIndex[$id]['MaxHealth'] = $oSoldier->getMaxHealth();
               
               // get rank different
               $indexRank = $oSoldier->Rank - $soldierDef->Rank;
               if ($indexRank > 10)
                  $indexRank = 10;
               else if ($indexRank < -25)
                  $indexRank = -25;
               $attackIndex[$id]['LevelIndex'] = $indexRank;
           }          
           
           // defence constant
           $defenceIndex = $soldierDef->getIndex();
           $defenceIndex['CurrentHealth'] = $soldierDef->getHealth();
           $defenceIndex['MaxHealth'] = $soldierDef->getMaxHealth();                
           $defenceIndex = $this->deityEffect($defenceIndex, false);
                                          
           // turn 0: initial vitality
           $scene[$turn]['attackFirst'] = $attackFirst;
           foreach($listSoldierAtt as $id => $oSoldier)
           {               
               $scene[$turn]['Vitality']['Attack'][$id] = $attackIndex[$id]['Vitality'];
           }
           $scene[$turn]['Vitality']['Defence']['Left'] = $defenceIndex['Vitality'];
           
           $conf_ele = Common::getParam('Elements');
           //$criticalThreshold = Common::getParam('CriticalThreshold');
           $conf_hit = Common::getConfig('ChanceToHit');
           
           $checkCanAttack = rand(1,100);
           if($soldierDef->Id == ForestParam::ID_MONSTER_4_ROUND_2 && $this->RoundNum == SeaRound::ID_ROUND_2 && $checkCanAttack <= ForestParam::PERCENT_BOSS_ROUND_2_QUIT)
           {        // Boss chay khong danh
                $scene[$turn]['BossQuit'] = 1;
                $turn++;                                                  
                // Attack turn     
                foreach($listSoldierAtt as $id => $oSoldier)
                {  
                   // calculate index 
                   $attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],$isResistance) ;
                   $scene[$turn]['Status']['Attack'][$id] = SceneAttack::NORMAL;           
                   $scene[$turn]['Vitality']['Attack'][$id] = $scene[$turn - 1]['Vitality']['Attack'][$id];    
                }                       
                // update scene param                                              
               $DamageAttack = 0; 
               $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
               $scene[$turn]['Vitality']['Defence']['Left'] = 0;
               $scene[$turn]['Vitality']['Defence']['ListAtt'][$id] = $scene[$turn - 1]['Vitality']['Defence']['Left'];   
               $scene[$turn]['BossQuit'] = 1;  
           }
           else
           {
               // loop until max turn
               while($turn<$conf_Turn['Max'])
               {
                    $turn++;                      
                    $total = 0;

                    // select my soldier to defence
                    if (!is_array($listSoldierAtt))
                        break;
                    $idVictim = array_rand($listSoldierAtt,1);
                    
                    $scene[$turn]['Vitality']['Defence']['Left'] = $scene[$turn-1]['Vitality']['Defence']['Left'];             
                    // Attack turn         
                    foreach($listSoldierAtt as $id => $oSoldier)
                    {  
                    
                       // check resistance when soldier attack
                       foreach($oSoldier->BuffItem as $id1 => $oItem)
                       { 
                            if ($oItem[Type::ItemType] == BuffItem::Resistance)  
                            {
                                $isResistance = 0;
                            }    
                       }                  

                       $attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],$isResistance) ;
                       if ($attTurn['Miss'])     // if miss
                       {                                                       
                           $DamageAttack = 0;
                           $scene[$turn]['Status']['Attack'][$id] = SceneAttack::MISS;
                       }
                       else {
                            $vitalityDamage = SoldierFish::getVitalAttack($attackIndex[$id],$defenceIndex, $attTurn['Conflict'], $alpha);  
                            $DamageAttack = $vitalityDamage['Damage'];
                       }
                       
                       // update scene param                                   
                       $scene[$turn]['Vitality']['Defence']['Left'] -= $DamageAttack;      
                       $scene[$turn]['Vitality']['Defence']['ListAtt'][$id] = $DamageAttack;       
                       if ($scene[$turn]['Vitality']['Defence']['Left'] < 0)
                       {
                           // if do not reach min turn, fake data
                           if ($turn < $conf_Turn['Min'])
                           {
                                $curVital = $scene[$turn-1]['Vitality']['Defence']['Left'];
                                $scene[$turn]['Vitality']['Defence']['Left'] = ceil($curVital/2);
                                $scene[$turn]['Vitality']['Defence']['ListAtt'][$id] = ceil(($curVital-1)/2);    
                           }
                           else $scene[$turn]['Vitality']['Defence']['Left'] = 0;
                       }
                                                                                   
                       if (!$attTurn['Miss'])
                       {
                           if ($attTurn['Critical'] == 2)
                                $scene[$turn]['Status']['Attack'][$id] = SceneAttack::CRITICAL;
                           else $scene[$turn]['Status']['Attack'][$id] = SceneAttack::NORMAL;
                       }

                    } // end attack list
                    // Defence turn            
                   $defIndex = -$attackIndex[$idVictim]['LevelIndex'];
                   if ($defIndex > 10)
                      $defIndex = 10;
                   else if ($defIndex < -25)
                      $defIndex = -25;      
                   $defTurn = SoldierFish::attackByTurn($soldierDef->Element,$listSoldierAtt[$idVictim]->Element,$conf_hit[$defIndex],$defenceIndex['Critical'],$isResistance) ;
                   if ($defTurn['Miss'])    // if Defence attack miss 
                   {
                       $DamageAttack = 0;
                       $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
                   }
                   else {                                        
                        $vitalityDamage = SoldierFish::getVitalAttack($defenceIndex,$attackIndex[$idVictim], $attTurn['Conflict'], $alpha);       
                        $DamageAttack = $vitalityDamage['Damage'];
                   }                                               
                   
                   // update scene param                     
                   $scene[$turn]['Vitality']['Attack'][$idVictim] = $scene[$turn-1]['Vitality']['Attack'][$idVictim] - $DamageAttack;
                   if ($scene[$turn]['Vitality']['Attack'][$idVictim] < 0)
                   {
                        // if do not reach min turn, fake data  
                        if ($turn < $conf_Turn['Min']) 
                        {
                            $scene[$turn]['Vitality']['Attack'][$idVictim] = ceil($scene[$turn-1]['Vitality']['Attack'][$idVictim]/2);                                                    
                        }   
                        else $scene[$turn]['Vitality']['Attack'][$idVictim] = 0;
                   }                                    
                   if (!$defTurn['Miss'])
                   {
                       if ($defTurn['Critical'] == 2)
                            $scene[$turn]['Status']['Defence'] = SceneAttack::CRITICAL;
                       else $scene[$turn]['Status']['Defence'] = SceneAttack::NORMAL;
                   }                                       
                   // update other attacker
                   foreach($listSoldierAtt as $id => $oSoldier) 
                   {
                       if ($id != $idVictim)
                        $scene[$turn]['Vitality']['Attack'][$id] = $scene[$turn-1]['Vitality']['Attack'][$id];
                   }                                          
                   
                   // check condition break
                   // calculate total vitality attack        
                   foreach($listSoldierAtt as $id => $oSoldier)
                   {
                        $total += $scene[$turn]['Vitality']['Attack'][$id];    
                   }                                      
                   // if def attack first, att die, recover def's vitality 
                   if (!$attackFirst)
                   {                        
                        if ($total==0)
                        {
                            $scene[$turn]['Vitality']['Defence']['Left'] = $scene[$turn-1]['Vitality']['Defence']['Left'];            
                            break;
                        }
                   } 
                   else // if att attack first, def die, reover att's vitality
                   {
                       if ($scene[$turn]['Vitality']['Defence']['Left']==0)
                       {
                            foreach($listSoldierAtt as $id => $oSoldier)         
                            {
                                $scene[$turn]['Vitality']['Attack'][$id] = $scene[$turn-1]['Vitality']['Attack'][$id];  
                            }
                            break;
                       }    
                   }                                     
                   // def or att die, break
                   if ($total==0 || $scene[$turn]['Vitality']['Defence']['Left']==0)
                      break;                            
                   // unset no vitality soldier
                   if ($scene[$turn]['Vitality']['Attack'][$idVictim] == 0) 
                    unset($listSoldierAtt[$idVictim]);           
               }           
           }
           return $scene;
      }
      public function isAttackedBoss()
      {
          $numRound1 = count($this->Monster[SeaRound::ID_ROUND_1]) - 1;
          $numRound2 = count($this->Monster[SeaRound::ID_ROUND_2]) - 1;
          $numRound3 = count($this->Monster[SeaRound::ID_ROUND_3]) - 1;
          if($numRound1 + $numRound2 + $numRound3 > 0)
          {
              return false;
          }
          return true;
      }
      
      /**
      * Ham thuc hien tinh toan chi se cua con ca truyen vao
      * 
      * @param mixed $oSoldier
      * @return mixed
      */
      public function calculationIndexSoldier($oSoldier)
      {
          $arrIndex = array(); 

          $userId = Controller::$uId;
          $oStoreEquip = StoreEquipment::getById($userId);
          $listIndex = Common::getParam('SoldierIndex');
          
          $ReputationBuff = $oSoldier->getReputationBuff($userId);
          
          foreach($listIndex as $name)
          {
              // get from base + equipment
              if (isset($oStoreEquip->SoldierList[$oSoldier->Id]['Index'][$name]))
                $arrIndex[$name] += $oSoldier->$name + $oStoreEquip->SoldierList[$oSoldier->Id]['Index'][$name];
              else
                $arrIndex[$name] += $oSoldier->$name;
              //get from meridian 
              if(!empty($oStoreEquip->listMeridian[$oSoldier->Id][$name]))
              {
                  $arrIndex[$name] += $oStoreEquip->listMeridian[$oSoldier->Id][$name];
              }
              
              // them Reputation buff
              if(!empty($ReputationBuff[$name]))
                $arrIndex[$name] += $ReputationBuff[$name] ;
          }
          
          // get from BuffItem
          $arrIndex['Damage'] += $oSoldier->getDamageBuffItem();
          
          // get from Gem
          $funcGem = SoldierFish::getFunctionGem($oSoldier->GemList, $oSoldier->Element, $arrIndex['Damage']);
          
          $arrIndex['Damage'] += $funcGem['Damage'];
          $arrIndex['Defence'] += $funcGem['Defence'];
          $arrIndex['Vitality'] += $funcGem['Vitality'];
          $arrIndex['Critical'] += $funcGem['Critical'];
          
          $arrIndex['Element'] = $oSoldier->Element ;
          $arrIndex['Id'] = $oSoldier->Id;
          $arrIndex = $this->deityEffect($arrIndex,true);
           
          return  $arrIndex;
      }
      
      /**
      * Ham thuc hien cap nhat cac chi so cua ca khi chiu tac dong cua cac hieu ung
      * 
      * @param mixed $arr
      * @param mixed $isMonster
      */
      public function deityEffect($arr, $isMonster)
      {
          if(empty($this->arrRandomBuff)) 
            return $arr ;
          if($this->RoundNum != SeaRound::ID_ROUND_2) 
            return $arr ;
          $percentEffect = Common::getParam('PercentEffectDeity');
          $str = 'Boss';
          if($isMonster == true)    $str = 'Monster';
          $typeEffect = $this->arrRandomBuff[$str];
          if($typeEffect == TypeEffectDeity::TYPE_DAMAGE_INCREASE)
          {
              $arr['Damage'] = (100 + $percentEffect) * $arr['Damage'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_DAMAGE_DECREASE)
          {
              $arr['Damage'] = (100 - $percentEffect) * $arr['Damage'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_DEFENCE_INCREASE)
          {
              $arr['Defence'] = (100 + $percentEffect) * $arr['Defence'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_DEFENCE_DECREASE)
          {
              $arr['Defence'] = (100 - $percentEffect) * $arr['Defence'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_HEALTHY_INCREASE)
          {
              $arr['Vitality'] = (100 + $percentEffect) * $arr['Vitality'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_HEALTHY_DECREASE)
          {
              $arr['Vitality'] = (100 - $percentEffect) * $arr['Vitality'] / 100;
          }
          elseif($typeEffect == TypeEffectDeity::TYPE_BOLT)
          {
              Debug::log('khi co eff set thi chi so con ca luc trước la'.$arr);
              $arr['Damage'] = 0;
              $arr['Defence'] = 0;
              $arr['Critical'] = 0;
              $arr['Vitality'] = 0;
              Debug::log('khi co eff set thi chi so con ca luc sau la'.$arr);
          }
          return $arr ;
      }
      
      public function joinAgain($seaIdGetMonster)
      {
          Sea::joinAgain($seaIdGetMonster);
          $this->createObjRandomBuff();         // KHoi tao du lieu cho lan danh tiep theo cua round 2
          $this->createSequenceRedUp();         // KHoi tao du lieu cho lan danh tiep theo cua round 1
          $this->createSequenceYellowDown();    // KHoi tao du lieu cho lan danh tiep theo cua round 3
      }
      
      public function CheckInRound4Forest()
      {
          $oMonsterRound1 = $this->Monster[SeaRound::ID_ROUND_1];
          $oMonsterRound2 = $this->Monster[SeaRound::ID_ROUND_2];
          $oMonsterRound3 = $this->Monster[SeaRound::ID_ROUND_3];
          
          if(isset($oMonsterRound1) && count($oMonsterRound1) > 0)
          {
              return false;
          }
          if(isset($oMonsterRound2) && count($oMonsterRound2) > 0)      
          {
              return false;
          }                                                             
          if(isset($oMonsterRound3) && count($oMonsterRound3) > 0)     
          {
              return false;
          }
          return true;
      }
      public function acttackBossForest($ListS,$oMonster)
      {
            $scene = array();
            //$TotalDam = 0;
            $gave = 0;
            $conf_sea = Common::getWorldConfig('SeaMonster',$this->SeaId,1);
            $alpha = intval($conf_sea[$oMonster->Id]['Alpha']);
            
            // defence constant
            $defenceIndex = $oMonster->getIndex();
            $defenceIndex['CurrentHealth'] = $oMonster->getHealth();
            $defenceIndex['MaxHealth'] = $oMonster->getMaxHealth();
                
            foreach($ListS as $id => $oSoldier)
            {
                if(!is_object($oSoldier))
                    continue ;
                $TotalDam = 0;
                // attack constant
                $attackIndex = $oSoldier->getIndex($oSoldier->getUserId());
                $attackIndex['CurrentHealth'] = $oSoldier->getHealth();
                $attackIndex['MaxHealth'] = $oSoldier->getMaxHealth();
                              
                $vitalityDamage = SoldierFish::getVitalAttack($attackIndex,$defenceIndex,0,$alpha);
                
                if ($vitalityDamage['Critical'] == 2)
                    $scene[$id]['Status'] = SceneAttack::CRITICAL;
                else
                    $scene[$id]['Status'] = SceneAttack::NORMAL;
                
                $scene[$id]['Damage'] = $vitalityDamage['Damage'];
                
                $TotalDam += intval($vitalityDamage['Damage']);
                 
                $arr_gift = array();
                $arr_gift = $this->getGiftOfBossForest($TotalDam,$gave);      
                
                $gave = intval($arr_gift['Index']) ;
                $scene[$id]['Gift']  = $arr_gift['Gift'];
            }
            
            return $scene ;       
      }
      
      // lay qua tu 
      public function getGiftOfBossForest($TotalDam,$gave = 0)
      {
          if($TotalDam <= 0)
            return array();
          $conf = Common::getWorldConfig('ForestGift');
          if(empty($conf))
            return array();
          $gift = array()  ;
          //$gift['Index'] = $gave ; 
          $gift['Gift'] = array() ; 

          foreach($conf as $index => $arr)
          {
              if(empty($arr))
                continue ;
              if($TotalDam >= $arr['DamRequire'])
              {
                  // kiem tra xem qua nay da nhan chua
                  //if($gave >= $index )
                  //  continue ;
                  // qua co dinh cho user
                  $gift_aa = $conf[$index]['NormalBonus'];
                  
                  
                  foreach($conf[$index]['RandomBonus'] as $id =>$arr_g)
                  {
                      if(empty($arr_g))
                        continue ;
                      $gift_aa[] = $arr_g;
                  }
                                    
                  
                  
                  /*
                  // qua random
                  $arr_rand = array();
                  foreach($conf[$index]['RandomBonus'] as $id =>$arr_g)
                  {
                      if(empty($arr_g))
                        continue ;
                      $arr_rand[$id] = $arr_g['Rate'] ;       
                  }
                  $id_gift = Common::randomIndex($arr_rand);
                  $giftarr = $conf[$index]['RandomBonus'][$id_gift];
                  unset($giftarr['Rate']);
                  
                  if(!empty($giftarr))
                    $gift_aa[] = $giftarr ;
                    */
                  // gan lai gave
                  //$gift['Index'] = $index ;      
                  
                  $return_gift = Common::addsaveGiftConfig($gift_aa,rand(1,5),SourceEquipment::FISHWORLD);
                  
                  if(!empty($return_gift))
                    $gift['Gift'][$index] = $return_gift ;
                  else
                    $gift['Gift'][$index] = array();
              }
          }
          
          
          return $gift;
      }
      
  }
?>
