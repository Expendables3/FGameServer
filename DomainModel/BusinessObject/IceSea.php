<?php
  class IceSea extends Sea
  {
      public $Wave ;    
      
      public function __Construct($SeaId)
      {
          Sea::__Construct($SeaId);
          $this->Wave = array();
      }
      
      // khoi tao song bang
      public function createWave()
      {
          $conf = Common::getWorldConfig('IceWave');
          foreach($conf as $RoundId => $arr)
          {
              $this->Wave[$RoundId] = array() ;
              if( rand(1,100) <= $arr['Rate'])
              {
                  $max = count(Common::getWorldConfig('SeaMonster',$this->SeaId,$RoundId));
                  $ran = rand(1,$max);
                  $this->Wave[$RoundId]['Times'] = $ran;
                  $this->Wave[$RoundId]['EffectNum'] = $arr['EffectNum'] ;
              }
          }
          
      }
      
      public function updateEffectNum()
      {
      
          if(empty($this->Wave[$this->RoundNum]))
            return false ;
          
          $MonsterMax = count(Common::getWorldConfig('SeaMonster',$this->SeaId,$this->RoundNum));
          // xac dinh so luong quai con lai cua vong
          $MonsterNum = count($this->Monster[$this->RoundNum]);
          $Times = $MonsterMax - $MonsterNum ;
          if($Times > $MonsterMax )
            $Times = $MonsterMax ;
          // kiem tra so lan anh huong
          if($Times >= $this->Wave[$this->RoundNum]['Times'])
          {
            $this->Wave[$this->RoundNum]['EffectNum']-= 1 ;
            if($this->Wave[$this->RoundNum]['EffectNum'] < 0 )
            {
                $this->Wave[$this->RoundNum] = array() ;
            }
              
          }
      }
      
      
      /**
      * tinh thong so cua ngu thu
      * 
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
          
          
          // phan buff them cua IceWave
           $arrIndex['Element'] = $oSoldier->Element ;
           $arrIndex = $this->iceWaveEffect($arrIndex);

          // get from BuffItem
          $arrIndex['Damage'] += $oSoldier->getDamageBuffItem();
          
          // get from Gem
          $funcGem = SoldierFish::getFunctionGem($oSoldier->GemList, $oSoldier->Element, $arrIndex['Damage']);
          
          $arrIndex['Damage'] += $funcGem['Damage'];
          $arrIndex['Defence'] += $funcGem['Defence'];
          $arrIndex['Vitality'] += $funcGem['Vitality'];
          $arrIndex['Critical'] += $funcGem['Critical'];
          
          return $arrIndex;
      }
      
      public  function generateScene($listSoldierAtt, $soldierDef, $isResistance = 1,$alpha = 0)
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
               //$attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());
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
           $defenceIndex = $this->iceWaveEffect($defenceIndex);    
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
                   // calculate index 
                   
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
                        $vitalityDamage = SoldierFish::getVitalAttack($attackIndex[$id],$defenceIndex, $attTurn['Conflict'],$alpha);    
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
                       if ($vitalityDamage['Critical'] == 2)
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
                  
               // check resistance when boss attack
               foreach($listSoldierAtt[$idVictim]->BuffItem as $id1 => $oItem)
               { 
                    if ($oItem[Type::ItemType] == BuffItem::Resistance)  
                    {
                        $isResistance = 0;
                    }    
               }                  
                   
               $defTurn = SoldierFish::attackByTurn($soldierDef->Element,$listSoldierAtt[$idVictim]->Element,$conf_hit[$defIndex],$defenceIndex['Critical'],$isResistance) ;

               if ($defTurn['Miss'])    // if Defence attack miss 
               {
                   $DamageAttack = 0;
                   $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
               }
               else {
                    $vitalityDamage = SoldierFish::getVitalAttack($defenceIndex,$attackIndex[$idVictim],$defTurn['Conflict'],$alpha);    
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
                   if ($vitalityDamage['Critical'] == 2)
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
           
           return $scene;
      }
      
      // phan tinh toan buff cua song bang
      public function iceWaveEffect($arr)
      {
          $confEffect = Common::getWorldConfig('IceWave',$this->RoundNum);
          if(empty($confEffect)) 
            return $arr ;
          if(empty($this->Wave[$this->RoundNum]))
            return $arr ;
          $MonsterMax = count(Common::getWorldConfig('SeaMonster',$this->SeaId,$this->RoundNum));
          // xac dinh so luong quai con lai cua vong
          $MonsterNum = count($this->Monster[$this->RoundNum]);
          $Times = $MonsterMax - $MonsterNum +1 ;
          if($Times > $MonsterMax )
            $Times = $MonsterMax ;
          // kiem tra so lan anh huong
          
          if($Times < $this->Wave[$this->RoundNum]['Times'] || $this->Wave[$this->RoundNum]['EffectNum'] <= 0)
            return $arr ;
          $arr['Damage'] = round($arr['Damage'] + $arr['Damage']*$confEffect[$arr['Element']]/100);
          return $arr ;
          
      }
      
      public function getDefenceIndex($defenceIndex)
      {
          $HeadNum = 0 ;
          foreach ($this->Monster[4] as $id => $arr)
          {
              if($id <10 && $id > 0)
                $HeadNum++;              
          }
          $EffectConf = Common::getParam('IceSea');
          $HeadNum = $EffectConf['HeadNum'] - $HeadNum ;
          
          $defenceIndex['Damage']   += round($defenceIndex['Damage']*$HeadNum*$EffectConf['EffectBuff']/100);
          $defenceIndex['Defence']  = round($defenceIndex['Defence']*$HeadNum*$EffectConf['EffectBuff']/100);
          
          return $defenceIndex; 
      }
      
      
      public function joinAgain($seaIdGetMonster)
      {
          $this->createWave();
          Sea::joinAgain($seaIdGetMonster);
          
      }
      
      
  }
?>
