<?php
/**
* AnhBV
* 24/11/2011
*/
  Class Sea 
   {
        public $SeaId ;
        public $RoundNum ;
        public $Monster;
        public $ItemList;
        public $LastJoinTime;
        public $JoinNum ;
        Public $KillBossNum ;
        public $KillBossNumOnDay;
        public $BonusRate ; // phan tram cong them 
     
        public function __Construct($SeaId)
        {
            $this->SeaId        = $SeaId ;
            $this->RoundNum     = 1;
            $this->LastJoinTime = 0;
            $this->JoinNum      = 0;
            $this->ItemList     = array();
            $this->Monster      = array();
            $this->KillBossNum  = 0 ;
            $this->BonusRate    = 0 ;
            $this->KillBossNumOnDay = 0 ;
        }

        // khoi tao cac quai trong bien 
        public function createMonsterList($SeaId)
        {
            $monster_conf = Common::getWorldConfig('SeaMonster',$SeaId);

            if(!is_array($monster_conf)) return false ;

            foreach ($monster_conf as $roundId => $arr_monster)
            {
                foreach ($arr_monster as $Id => $info)
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
                    
                    $this->Monster[$roundId][$Id] = $oMonster ;
                } 
            }            
        }
     
        // del cac quai trong bien
        public function delMonster($monsterId ,$roundId = -1)
        {
            if($roundId == -1)
            {
                unset($this->Monster[$this->RoundNum][$monsterId]);
            }
            else
            {
                if(isset($this->Monster[$roundId][$monsterId]))
                    unset($this->Monster[$roundId][$monsterId]) ;
            }
                                  
            if($this->SeaId != SeaType::SEA_4)
            {    $numround = count($this->Monster);
                if($this->RoundNum == $numround && empty($this->Monster[$numround]))
                    $this->Monster = array();
            }
        }

        // comeback sea
        public function joinAgain()
        {
            $this->LastJoinTime = $_SERVER['REQUEST_TIME'];
            $this->JoinNum++ ;
            $this->RoundNum = 1 ;
            $this->createMonsterList($this->SeaId);
        }
        
        public function getMonster($monsterId,$roundId = 0)
        {
            if(empty($monsterId))
                return false ;
            if(empty($roundId))
                $roundId = $this->RoundNum ;        
            $oMonster = $this->Monster[$roundId][$monsterId];
            if(!is_object($oMonster))
                return false;
            return $oMonster ;    
            
        }
        
        public function getMonsterInRound($roundId = 0)
        {                                                          
            if(empty($roundId))
                $roundId = $this->RoundNum ;              
            $oMonster = $this->Monster[$roundId];     
            if(empty($oMonster))
                return false ;
            return $oMonster ;    
            
        }
        
        public function checkJoinSea($PriceType, $JoinTime, $JoinMoney)
        {
            $JoinMax = count($JoinTime);
                      
            if($this->JoinNum >= $JoinMax)
                return array('Error' => Error :: OVER_NUMBER) ;
            if($this->JoinNum == 0)
            {
                return array('Error' => Error :: SUCCESS) ;
            }
            
            if($PriceType != Type::ZMoney)// ko tra tien    
            {
                $NowTime = $_SERVER['REQUEST_TIME'];
                if($this->LastJoinTime + $JoinTime[$this->JoinNum] > $NowTime)
                    return array('Error' => Error :: NOT_ENOUGH_TIME) ;
            }
            else
            {
                $oUser = User::getById(Controller::$uId);
                $info = "1:JoinSeaAgain:".$this->SeaId ;
                if(!$oUser->addZingXu(-$JoinMoney[$this->JoinNum],$info))
                  return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
            }
            
            return array('Error' => Error :: SUCCESS) ;           

        }
        
        /**
        *  get Equipment follow Element of Soldier Fish
        * 
        * @param mixed $Type
        * @param mixed $Element
        * @param mixed $Rank
        * @param mixed $color
        */
        public function getEquipmentFollowElement($Type,$Element = 0,$Rank= 1,$color = 1)
        {                 
            $equiment = array () ;
            $conf_E  = Common::getConfig('Wars_'.$Type);       
            if(empty($conf_E)) return $equiment ;
            $listGift = array();
            if(in_array($Type,array('Ring','Bracelet','Necklace','Belt'),true))
            {
                 $Element = 0 ;   
            }  
            foreach( $conf_E as $key => $arr_equ)
            {
                $__element =  floor($key/100) ;
                if($__element != $Element)
                    continue ;
                    
                $__rank =  $key%100;
                if($__rank != $Rank)
                    continue ;
                $keyE = $key ;
            }   
            if(empty($keyE)) return $equiment ;
            
            $equiment['ItemType']   = $Type ;
            $equiment['Rank']       = $keyE ;
            $equiment['Color']      = $color ;
            return $equiment ;     
        }
        
        // tao qua khi danh boss
        public function getBossBonus($oMonster,$ElementOfSoldier,$RateWin)
        {
            $conf = Common::getWorldConfig('BossBonus',$this->SeaId,$oMonster->Id);
            $conf =  $conf[$RateWin];
            $bonus = array(); 
            if(empty($conf))
            {
                return $bonus ;   
            }
   
                $bonus['Normal']        = $conf['NormalBonus'] ;
                $bonus['Collection']    = $this->rendCollection($conf['Collection'], $ElementOfSoldier);
                
                $bonus['ItemList']      = $this->rendItemBoss($conf['Item'],$ElementOfSoldier,$RateWin);    
                
                $bonus['GemList']       = $this->rendGem($conf['Gem'],$ElementOfSoldier); 
                $bonus['MixFomula']     = $this->rendFomula($conf['Fomula'],$oMonster->RecipeType);
                $bonus['Mask']          = $this->rendMask($conf['Mask']) ;  
                $bonus['Material']      = $this->rendMaterial($conf['Material']) ;  
            
            // check for 
            $DropMoneyNum = Common::getWorldConfig('Sea',$this->SeaId,'DropMoneyNum');
            if($this->JoinNum > $DropMoneyNum)
            {
               foreach($bonus['Normal'] as $key => $_arr)
               {
                   if($_arr['ItemType'] == Type::Money)
                   {
                       unset($bonus['Normal'][$key]) ;
                   }
                   if($_arr['ItemType'] == Type::Exp)
                   {
                       unset($bonus['Normal'][$key]) ;
                   }
               }
            }
            return $bonus ;
        }  
        
        // tao qua khi danh quai thuong 
        public function getMonsterBonus($oMonster,$is_win = true)
        {
            $RoundId= $this->RoundNum ;

            $conf = Common::getWorldConfig('SeaBonus',$this->SeaId,$RoundId);
            $conf = $conf[$oMonster->Id] ;

            $bonus = array(); 
            if(empty($conf))
            {
                return $bonus ;   
            }
            
    
            if($is_win)
            {       
                $bonus['Normal']        = $conf['NormalBonus'] ;
                $bonus['Collection']   = $this->rendCollection($conf['Collection'],$oMonster->Element);
                $bonus['ItemList']     = $this->rendItem($conf['Item'],$oMonster->Element);    
                $bonus['GemList']       = $this->rendGem($conf['Gem'],$oMonster->Element); 
                $bonus['MixFomula']    = $this->rendFomula($conf['Fomula'],$oMonster->RecipeType);                   
                $bonus['Mask']          = $this->rendMask($conf['Mask']) ;  
            }
            
            return $bonus ;
            
        }
        
        // create Equipment Bonus of boss
        public function rendItemBoss($conf,$Element,$RateWin)   
        {        
            if($RateWin == 100)
            {
                //  the frist kill
                if($this->KillBossNum == 1)
                {
                    $giftId = rand(1,count($conf));
                    $arr = $conf[$giftId];             
                }
                else
                {
                    foreach($conf as $giftId => $arr_gift)
                    {
                        $conf[$giftId]['Rate'] += round($this->BonusRate/count($conf)) ;
                    }  
                    
                    $arr = $this->randomBonus($conf);
                    if(empty($arr))
                    {
                        // update lai phan tram thuong
                        $Bonus = Common::getParam('BonusRate');
                        $this->BonusRate += $Bonus ;
                        return array();
                    }                    
                }
            }
            else
            {
                $arr = $this->randomBonus($conf);
                if(empty($arr))
                {
                    return array();
                }
            }
           
            if($RateWin == 100)
            {
                // update lai phan tram thuong
                $this->BonusRate = 0 ;
            }
            
            $oUser = User::getById(Controller::$uId);
            
            $oEquip = Common::randomEquipment($oUser->getAutoId(),$arr['Rank'],$arr['Color'],SourceEquipment::FISHWORLD,$arr['ItemType'],0,$Element);
            
            $arr_Equip[$oEquip->Id] = $oEquip;
            
            return $arr_Equip ;
            
        }
        
        // create Equipment Bonus
        public function rendItem($conf,$Element)
        {
            $arr_bonus = array(); 
            $arr_Type = array('Armor' => 10,'Helmet'=> 10,'Weapon'=> 10,'Ring'=> 10,'Bracelet'=> 10,'Necklace'=> 10,'Belt'=> 10); 
            
            $arr = $this->randomBonus($conf);
            if(empty($arr)) return array();
            

            if(empty($arr['ItemType'])) 
                $arr['ItemType'] =  Common::randomIndex($arr_Type);
                
            $arr_E = $this->getEquipmentFollowElement($arr['ItemType'],$Element,$arr['Rank'],$arr['Color']);
            if(empty($arr_E)) return array();
              
            $arr_bonus[1]['ItemType']  = $arr['ItemType'];
            $arr_bonus[1]          = $arr_E ;
            $arr_bonus[1]['Num'] = $arr['Num'] ; 
            
            $oUser = User::getById(Controller::$uId);
            
            $Value = $arr_bonus[1] ;
            $conf_E = Common::getConfig('Wars_'.$Value['ItemType'],$Value['Rank'],$Value['Color']);     
            $oEquip = new Equipment($oUser->getAutoId(),$conf_E['Element'],$Value['ItemType'],$Value['Rank'],$Value['Color'],
            rand($conf_E['Damage']['Min'],$conf_E['Damage']['Max']),rand($conf_E['Defence']['Min'],$conf_E['Defence']['Max']),
            rand($conf_E['Critical']['Min'],$conf_E['Critical']['Max']),$conf_E['Durability'],$conf_E['Vitality'],SourceEquipment::FISHWORLD);

            $arr_Equip[$oEquip->Id] = $oEquip;
            
            return $arr_Equip ;
                        
        }
        // create Mask
        public function rendMask($conf)
        {
            $arr_bonus = array(); 
            
            $arr = $this->randomBonus($conf);
            if(empty($arr)) return array();
              
            $arr_bonus[1]['ItemType']   = $arr['ItemType'];
            $arr_bonus[1]['Rank']       = $arr['ItemId'] ;
            $arr_bonus[1]['Num']        = $arr['Num'] ; 
            $arr_bonus[1]['Color']      = $arr['Color'] ;

            return $arr_bonus ;
            
        }
        public function rendMaterial($conf)
        {
            $arr_bonus = array(); 
            
            if(empty($conf)) return array();
            
            foreach($conf as $id => $_arr)
            {
                if(mt_rand(1,100)<= $_arr['Rate'])
                {
                    $arr_bonus[$id] = $_arr ;
                    unset($arr_bonus[$id]['Rate']);
                }
            }            
            return $arr_bonus ;
            
        }
        
        
        // create Gem Bonus
        public function rendGem($conf,$Element)
        {
            $arr_bonus = array();                   
            
            $arr_gem = $this->randomBonus($conf) ;
            
            if(empty($arr_gem)) return array();

            $arr_bonus[1]['ItemType']  = 'Gem' ;  
            $arr_bonus[1]['ItemId']    = $arr_gem['ItemId'] ; 
            $arr_bonus[1]['Element']   = $Element ; 
            $arr_bonus[1]['Day']       = 7 ; 
            $arr_bonus[1]['Num']       = $arr_gem['Num'] ; 

            return $arr_bonus ;
            
        }
        
        // create MixFomula 
        public function rendFomula($conf,$Recipe)
        {
            $arr_bonus = array();
    
            if(!empty($conf[1]))
            {
                 if(rand(1,100) <= $conf[1]['Rate'])
                 {
                    $arr_bonus[1]['ItemType']   = $conf[1]['ItemType']; 
                    $arr_bonus[1]['ItemId']     = $Recipe['ItemId']; 
                    $arr_bonus[1]['Num']        = 1;
                 } 
            }
            return $arr_bonus ;
            
        }
        // create Collection Gift
        public function rendCollection($conf,$element)
        {
            $arr_bonus= array();
            $conf_collect = Common::getWorldConfig('SeaCollection',$this->SeaId,$element);            
            if(empty($conf)|| empty($conf_collect) )
                return array() ;
            
            $arr_key = array_keys($conf_collect);
            $Num = count($conf) ; 
  
            if($Num == 1)
            {
            
                foreach($conf as $index => $arr_C)
                {
                     if(empty($arr_C['Rate'])||($arr_C['Rate'] >= rand(1,100)))
                     {
                         $arr_bonus[$index]['ItemType']  = 'ItemCollection' ;  
                         $arr_bonus[$index]['ItemId']    = $arr_key[0] ; 
                         $arr_bonus[$index]['Num']       = $arr_C['Num'] ;
                         
                         return  $arr_bonus ;
                     }
                     else
                        return array();
                }
            }
            else
            {
                $rate = 0 ;
                foreach($conf as $index => $arr_C)
                {               
                    $randRate[$index] = $arr_C['Rate'] ;
                    $rate += $arr_C['Rate'] ;
                }
                
                if($rate < 100 )
                {
                   $randRate[0]= 100 - $rate ;
                }
                
                $IdGift = Common::randomIndex($randRate);
                if($IdGift == 0) return array(); 
            }
                  
            $arr_bonus[1]['ItemType']  = 'ItemCollection' ;
            if(!isset($arr_key[$IdGift-1]))  
                $arr_bonus[1]['ItemId']    = $arr_key[0] ;
            else
                $arr_bonus[1]['ItemId']    = $arr_key[$IdGift-1] ; 
            $arr_bonus[1]['Num']       = $conf[$IdGift]['Num'] ; 

            return $arr_bonus ;
        }         
        public function randomBonus($conf)         
        {
            if(empty($conf))
                return array() ;
            $Num = count($conf) ; 
  
            if($Num == 1)
            {
                foreach($conf as $index => $arr_C)
                {
                     if(empty($arr_C['Rate']))
                        return $arr_C ;
                     if($arr_C['Rate'] >= rand(1,100))
                        return $arr_C ;
                     else
                        return array();
                }
            }
            else
            {
                $rate = 0 ;
                foreach($conf as $index => $arr_C)
                {               
                    $randRate[$index] = $arr_C['Rate'] ;
                    $rate += $arr_C['Rate'] ;
                }
                
                if($rate < 100 )
                {
                   $randRate[0]= 100 - $rate ;
                }
                
                $IdGift = Common::randomIndex($randRate);
                if($IdGift == 0) return array(); 
                  
                return $conf[$IdGift] ;
            }
        }
        
        //update Num round again 
        public function updateNumRound()
        {
 			if($SeaId != SeaType::SEA_4)
			{
            $RId = 1 ;
            foreach($this->Monster as $RoundId => $arr)
            {
                if(empty($arr))
                {
                   $RId++ ; 
                }
            }

            
            $RId = $RId > count($this->Monster)?count($this->Monster): $RId ;
            
            if($this->RoundNum != $RId)
            {
                $this->RoundNum = $RId ;
                Zf_log::write_act_log(Controller::$uId,0,15,'nextRound',0,0,$this->SeaId,$RId);
            }
}
                
        }
        
        // cong do xin vao trong list do dac 
        public function updateEquipmentGave($arr_bonus)
        {
            $conf_Item = Common::getWorldConfig('Sea',$this->SeaId,'Item') ;
   
            foreach($arr_bonus as $per => $ItemList)
            {
                if(empty($ItemList['ItemList']))
                    continue ;
                foreach($ItemList['ItemList'] as $key1 => $oEquip)   
                {
                    $diff = true ; 
                    
                    foreach($conf_Item as $key => $arr)
                    {
                        if($arr['ItemType'] == $oEquip->Type && $arr['Rank'] == $oEquip->Rank 
                        && $arr['Color'] == $oEquip->Color)
                        {
                             $Item = $arr ;
                             $diff = true ;  // thoa man dieu kien 
                             break ;
                        }
                        else
                        {
                             $diff = false ; // ko thoa man dieu kien   
                        }
                    }
                    
                    
                    foreach($this->ItemList as $key2 => $arr_Item)  
                    {
                        if($arr_Item['ItemType'] == $oEquip->Type && $arr_Item['Rank'] == $oEquip->Rank  
                        && $arr_Item['Color'] == $oEquip->Color)
                        {
                             $diff = false ; // da ton tai roi
                             break ;
                        }
                    }
                    
                    
                    
                    if($diff)
                    {
                        $this->ItemList[] =  $Item ;
                    }
                
                }
                
            }            
        }
        
        // update so lan kill duoc boss 
        public function updateKillBossNum($Num)
        {
            $this->KillBossNum += $Num ;
        }

        public function updateKillBossNumOnDay($num = 1)
        {
          if($num >= 1)
            $this->KillBossNumOnDay += $num ;
        }     
        
        
        /**
      * put your comment there...
      * isResistance = 0 : use Item Resistance
      * @return float
      */
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
               $attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());
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
      

      public function isAttackedBoss()
      {
      	return true;
      }
      
   }
?>
