<?php
  /**
  * class Soldier Fish
  * @author hieupt
  * Contain Attribute vs Behavior of Soldier
  */
  class SoldierFish extends Fish 
  {
      public $Rank;
      public $RankPoint;
      public $LifeTime;
      public $Damage;
      public $Defence;
      public $Element;
      public $Bonus;
      public $Diary;
      public $numBonusMoney = 0;
      public $RecipeType;
      public $Health;
      public $LastHealthTime;
      public $Status;       // status: Healthy, Clinical, Died
      public $SoldierType; // Type Soldier: 1-Mate 2-Shop, each type has other behavior
      public $BuffItem = array(); // list buffitem used
      public $GemList = array();  // list gem used
      public $Critical;
      public $Vitality;    
      public $UserBuff = array(); // list user gem
      public $Equipment = array();  // list equipment
      public $bonusEquipment = array(); // bonus from equipment
      public $IsDie = false ;  // use in FishWorld
      
      public $LastTimeDefendFail = 0;
      public $NumDefendFail = 0;
      public $nameSoldier = "";
      public $lastTimeChangeName = 0;
      
      function __construct($id, $FishTypeId, $rank_, $lifetime, $damage, $defence, $critical, $vital, $element, $recipe, $soldierType=1)
      {
          $this->Rank = $rank_;
          $this->RankPoint = 0;
          $this->LifeTime = $lifetime;
          $this->Damage = $damage;
          $this->Defence = $defence;
          $this->Element = $element;
          $this->RecipeType = $recipe;
          $this->Health = 1; // depending on rank
          $this->LastHealthTime = 0;
          parent::__construct($id, $FishTypeId, -1);
          $this->FishType = FishType::SOLDIER;
          $this->Status = 0;
          $this->SoldierType = $soldierType;
          $this->Bonus = array();
          $this->Diary = array();

          $this->Critical = $critical;
          $this->Vitality = $vital;
      }
      
      /**
      * update status = Healthy, Clinical or Died
      */
      public function updateStatus()
      {
          if ($this->OriginalStartTime + $this->LifeTime < $_SERVER['REQUEST_TIME'])
          {
              $this->Status = SoldierStatus::CLINICAL;  
          }
          else $this->Status = SoldierStatus::HEALTHY;
          
          return $this->Status;  
      }
      
      /**
      * reborn soldier, increase time
      * $Time in seconds
      */
      public function reborn($Time)
      {

          // check status is clinical
          //$this->updateStatus();          
          if ($this->Status == SoldierStatus::HEALTHY)
            return false;
          
          $this->StartTime = $_SERVER['REQUEST_TIME'];
          $this->OriginalStartTime = $_SERVER['REQUEST_TIME'];
          $this->LifeTime = $Time;
          $this->Status = SoldierStatus::HEALTHY;
          
          return true;                       
      }

      /**
      *  rank up soldier
      */
      public function promo()
      {
          $conf_st = Common::getConfig('General','SoldierType',$this->SoldierType);
          if (!$conf_st['promo'])
            return false;
            
          $conf_rank = Common::getConfig('RankPoint');
          $nextPoint = $conf_rank[$this->Rank]['PointRequire'];
          if (!$nextPoint)
            return false;
          if (!is_array($conf_rank[$this->Rank + 1]))
            return false;
          $levelUp = false;
          while ($nextPoint && is_array($conf_rank[$this->Rank + 1]) && $this->RankPoint >= $nextPoint)
          {
              $this->RankPoint -= $nextPoint;
              $this->Rank++;
              $this->Damage += ceil(($this->Damage)*$conf_rank[$this->Rank]['RateDamage']);
              $this->Defence += ceil(($this->Defence)*$conf_rank[$this->Rank]['RateDefence']);
              $this->Critical += ceil(($this->Critical)*$conf_rank[$this->Rank]['RateCritical']);
              $this->Vitality += ceil(($this->Vitality)*$conf_rank[$this->Rank]['RateVitality']); 
              
               $nextPoint = $conf_rank[$this->Rank]['PointRequire'];
               $levelUp = true;
          }  
          
          return $levelUp;
      }
      
      /**
      * add Health for soldier
      */
      public function addHealth($num)
      {
          $healthUnit = Common::getConfig('RankPoint',$this->Rank);
          $bonusHealth = floor(($_SERVER['REQUEST_TIME']-$this->LastHealthTime)/$healthUnit['RegenTime']);
          $this->Health += $bonusHealth;
          // check max health
          if ($this->Health > $healthUnit['MaxHealth'])
            $this->Health = $healthUnit['MaxHealth'];
          
          if ($this->Health + $num < 0)
            return false;
          else $this->Health+= $num;  
          
          // check max health
          if ($this->Health > $healthUnit['MaxHealth'])
            $this->Health = $healthUnit['MaxHealth'];                                    

          $this->LastHealthTime = $_SERVER['REQUEST_TIME'];
          return true;
      }
      
      public function getHealth()
      {
          $healthUnit = Common::getConfig('RankPoint',$this->Rank);
          $bonusHealth = floor(($_SERVER['REQUEST_TIME']-$this->LastHealthTime)/$healthUnit['RegenTime']);
          $this->Health += $bonusHealth;
          // check max health
          if ($this->Health > $healthUnit['MaxHealth'])
            $this->Health = $healthUnit['MaxHealth'];    
                                        
          $this->LastHealthTime = $_SERVER['REQUEST_TIME'];
          return $this->Health;
      }
      
      public function setHealth($Num)
      {
          $this->Health += $Num; 
          // check max health
          $healthUnit = Common::getConfig('RankPoint',$this->Rank);   
          if ($this->Health > $healthUnit['MaxHealth'])
            $this->Health = $healthUnit['MaxHealth'];
          if($this->Health < 0 )
            $this->Health = 0 ;     
      }
      
      /**
      * add rank point
      */
      public function addRankPoint1111($num)
      {
          if ($this->RankPoint+$num<0)
            $this->RankPoint = 0;
          else $this->RankPoint+=$num;
          
          $conf_st = Common::getConfig('General','SoldierType',$this->SoldierType);
          if (!$conf_st['promo'])
            return false;
            
          $conf_rank = Common::getConfig('RankPoint');
          $nextPoint = $conf_rank[$this->Rank]['PointRequire'];
          if (!$nextPoint)
            return false;
          if (!is_array($conf_rank[$this->Rank + 1]))
            return false;
          $levelUp = false;
          while ($nextPoint && is_array($conf_rank[$this->Rank + 1]) && $this->RankPoint >= $nextPoint)
          {
              $this->RankPoint -= $nextPoint;
              $this->Rank++;
              $this->Damage += ceil(($this->Damage)*$conf_rank[$this->Rank]['RateDamage']);
              $this->Defence += ceil(($this->Defence)*$conf_rank[$this->Rank]['RateDefence']);
              $this->Critical += ceil(($this->Critical)*$conf_rank[$this->Rank]['RateCritical']);
              $this->Vitality += ceil(($this->Vitality)*$conf_rank[$this->Rank]['RateVitality']); 
              
               $nextPoint = $conf_rank[$this->Rank]['PointRequire'];
               $levelUp = true;
          }  
          
          return $levelUp;
      } 
      
       public function addRankPoint($num)
      {
          $totalPoint = $this->RankPoint+$num ;
          if ($totalPoint < 0)
            $totalPoint = 0;
            
          $conf_st = Common::getConfig('General','SoldierType',$this->SoldierType);
          if (!$conf_st['promo'])
            return false;
              
          $conf_rank = Common::getConfig('RankPoint');
          
          $nextPoint        = $conf_rank[$this->Rank]['PointRequire'];

          if($totalPoint < $nextPoint )
          {
              $this->RankPoint = $totalPoint ;
              return $num ;
          }
          else
          {
                $useNum = 0 ;
             
                while ($nextPoint && is_array($conf_rank[$this->Rank + 1]) && $totalPoint >= $nextPoint)
                {
                    $totalPoint -= $nextPoint;
                    $useNum +=  $nextPoint;  
                    $this->Rank++;                   
                    $this->Damage += ceil(($this->Damage)*$conf_rank[$this->Rank]['RateDamage']);
                    $this->Defence += ceil(($this->Defence)*$conf_rank[$this->Rank]['RateDefence']);
                    $this->Critical += ceil(($this->Critical)*$conf_rank[$this->Rank]['RateCritical']);
                    $this->Vitality += ceil(($this->Vitality)*$conf_rank[$this->Rank]['RateVitality']); 

                    $nextPoint = $conf_rank[$this->Rank]['PointRequire'];
                }

                if($totalPoint >= $nextPoint)
                {
                    $useNum += $nextPoint - $this->RankPoint ;     
                    $this->RankPoint = $nextPoint ; 
                    
                    return $useNum ;
                }
                else
                {
                    $this->RankPoint = $totalPoint ; 
                    return $num ;
                }
                    
                  
          }

      }  
      
      /**
      * get current health
      */
      public function getCurrentHealth()
      {
            $healthUnit = Common::getConfig('RankPoint',$this->Rank);
            $bonusHealth = floor(($_SERVER['REQUEST_TIME']-$this->LastHealthTime)/$healthUnit['RegenTime']);
            return ($this->Health + $bonusHealth);
      }
      
      /**
      * get max Health
      */
      public function getMaxHealth()
      {
          $healthUnit = Common::getConfig('RankPoint',$this->Rank,'MaxHealth'); 
          return $healthUnit;
      }
      
      
      /**
      * Take Attack action
      * Calculate rate win and return result of battle
      * coreAttack: without Gem, buffItem, only core (base + equip + skill)
      */
      public static function takeAttack($mySoldier, $friendSoldier, $ItemList, $coreAttack = false)
      {
            $ItemList = Common::addItemToList($mySoldier->BuffItem,$ItemList);
            
            if($coreAttack)
                $ItemList = array();
            // increase Damage by ItemList
            $myDamage = $mySoldier->Damage;
            $friendDamage = $friendSoldier->Damage;
            
            // Ngu Hanh
            $isResistance = 1;

            // buff items attack
            $conf_buff = Common::getConfig('BuffItem');
            foreach($ItemList as $id => $oItem)
            {
                if ($oItem[Type::ItemType]==BuffItem::Samurai)      // if exist samurai item, increasing damage
                {
                    $myDamage += $conf_buff[$oItem[Type::ItemType]][$oItem[Type::ItemId]]['Num']*$oItem[Type::Num];
                }
                else if ($oItem[Type::ItemType] == BuffItem::Resistance)  // check exist resistance item
                {
                    $isResistance = 0;
                }    
            }
            
            $result = array();
            
            $listAtt = array();
            $listAtt[$mySoldier->Id] = $mySoldier;
            
            //$scene = SoldierFish::generateScene($listAtt,$friendSoldier);
            $scene = SoldierFish::generateScene($listAtt,$friendSoldier, $isResistance, $coreAttack);
            $result['Scene'] = $scene;
            $numTurn = count($scene);
            if ($scene[$numTurn-1]['Vitality']['Defence']['Left']==0)
                $result['Result'] = Battle::WIN;
            else $result['Result'] = Battle::LOSE;
            
            
            // return result of battle
            /*
            $ran = rand(1,100);
            if ($ran<=$rateWin)
                $result['Result'] = Battle::WIN;
            else $result['Result'] = Battle::LOSE;
            */
            return $result;    
      }
      
      /*
      * get functions from gem depend on element
      * @param mixed $gemArr: list Gem
      * @param mixed $myElement: element of soldier
      * @param mixed $baseDama: damage base
      */
      public static function getFunctionGem($gemArr, $myElement, $baseDama)
      {
            $conf_gem = Common::getConfig('Gem');
            $conf_confre = Common::getConfig('Param','Elements','Conflict');
            $arr = array();
            foreach($gemArr as $idElement => $listGem)
            {
                if ($myElement==$idElement)     // soldier is the same element with gem, duplicate
                    $rate = 2;
                else if ($conf_confre[$myElement] == $idElement)      // can not use for conflict element
                    $rate = 0;
                else $rate = 1;

                foreach($listGem as $index => $info_gem)    
                {    
                    $gemId = $info_gem['GemId'] ;
                    $eleRate[$idElement] += $rate*$conf_gem[$gemId][$idElement];
                }
            }
            
            // limit element MOC by 50%
            if (abs($eleRate[Elements::MOC]) > ceil(4*$baseDama/5))
                $eleRate[Elements::MOC] = -ceil(4*$baseDama/5);
            
            $arr['Damage'] = $eleRate[Elements::HOA] + $eleRate[Elements::MOC];
            $arr['Defence'] = $eleRate[Elements::THO];
            $arr['Critical'] = $eleRate[Elements::KIM];
            $arr['Vitality'] = $eleRate[Elements::THUY];

            return $arr;                
      }
      
      /**
      * return recipe made this soldier
      */
      public function getRecipe()
      {
          $arrRecipe = array();
          $arrRecipe[Type::ItemType] = $this->RecipeType[Type::ItemType];
          $arrRecipe[Type::ItemId] = $this->RecipeType[Type::ItemId];
          $arrRecipe[Type::Num] = 1;
          
          return $arrRecipe;
      }
      
      /**
      * get bonus for winner
      */
      public static function bonusWinner($lakeValue, $ItemList, $oSoldier, $friendSoldier)
      {
          $bonusCount = -1;
          $bonus = array();
          
          $conf_bonus = Common::getConfig('General');               
          // Money
          $perMoney = $conf_bonus['BattleBonus'][$oSoldier->Rank][Type::Money];
          
          $numMoney = ceil($lakeValue[Type::Money]*$perMoney/100);
          $bonus[++$bonusCount][Type::ItemType] = Type::Money;
          $bonus[$bonusCount][Type::ItemId] = 1;
          $bonus[$bonusCount][Type::Num] = $lakeValue[Type::Money];

          // Gem
          $conf_rank = Common::getConfig('RankPoint',$oSoldier->Rank,'Gem');
          $arrGem = array();
          foreach($conf_rank as $id => $oRate)
            $arrGem[$id] = $oRate['Rate'];  
          $indexGem = Common::randomIndex($arrGem);
          $bonus[++$bonusCount][Type::ItemType] = Type::Gem;
          $bonus[$bonusCount][Type::ItemId] = $conf_rank[$indexGem]['GemId'];
          $bonus[$bonusCount][Type::Num] = $conf_rank[$indexGem]['Num'];
          $bonus[$bonusCount]['Element'] = $friendSoldier->Element;
          $conf_day = Common::getConfig('Param','NumGemDay'); 
          $bonus[$bonusCount]['Day'] = $conf_day;
          
          // chance to get recipe vs material 
          $ran = rand(1,100);
          // get recipe
          if ($ran <= $conf_bonus['BattleRate']['Recipe'])
          {
              $rc = $friendSoldier->getRecipe();
              if (is_array($rc) && !empty($rc))
                $bonus[++$bonusCount] = $rc;
          }
          else if ($ran <= $conf_bonus['BattleRate']['Recipe']+$conf_bonus['BattleRate']['Material']) // get material
          {
              $idMaterial = Common::randomIndex($conf_bonus['BattleBonus'][$oSoldier->Rank][Type::Material]);
              $bonus[++$bonusCount][Type::ItemType] = Type::Material;
              $bonus[$bonusCount][Type::ItemId] = $idMaterial;
              $bonus[$bonusCount][Type::Num] = 1;
          }       
          
          return $bonus;
      }
     
      /**
      * check valid item list used 
      */
      public static function checkValidItemList($ItemList)
      {
          $conf_buff = Common::getConfig('BuffItem');
          foreach($ItemList as $id => $oItem)
          {
                // check exist item
                if (!isset($conf_buff[$oItem[Type::ItemType]][$oItem[Type::ItemId]]))
                    return false;
                // check max times used
                if ($oItem[Type::Num]<1 || $oItem[Type::Num]>$conf_buff[$oItem[Type::ItemType]][$oItem[Type::ItemId]]['MaxTimes'])
                    return false;
          }
          return true;
      }
      
      /**
      *  check exist item(ItemType, ItemId) in ItemList,
      *  return position(index) in ItemList and Num 
      */
      
      public static function checkExistItem($ItemList, $ItemType, $ItemId=0)
      {
          foreach($ItemList as $id => $oItem)
          {
              if ($oItem[Type::ItemType]==$ItemType){
                  if (($ItemId==0 || empty($ItemId) || ($oItem[Type::ItemId]==$ItemId)) && ($oItem[Type::Num]!=0))
                        return array('Id' => $id, 'Num' => $oItem[Type::Num]);
              }
          }
          return false;
      }
      

      /**
      *  add bonus for attack/defence win
      */
      public function addBonus($arrBonus)
      {
            if(empty($arrBonus))
                return false ;
           foreach($arrBonus as $id => $oBonus)
           {
                $check = true;
                foreach($this->Bonus as $idB => $curBonus){
                    // check existed item, increase num
                    if ($oBonus[Type::ItemType]==$curBonus[Type::ItemType] && $oBonus[Type::ItemId]==$curBonus[Type::ItemId])
                    {
                        $this->Bonus[$idB][Type::Num] += intval($arrBonus[$id][Type::Num]);
                        $check = false;
                        break;
                    }    
                }
                
                // if not existed, add new
                if ($check)
                {
                    $ii = count($this->Bonus);
                    $this->Bonus[$ii] = $oBonus;    
                }
           }
      }
      
      
      /**
      * get exp when click grave
      */
      public function getBonusDied()
      {
          $bonus = array();
          $conf_cost = Common::getConfig('General','Soldier','DiedValue');
          
          $bonus[0][Type::ItemType] = Type::Exp;
          $bonus[0][Type::ItemId] = 1;
          $bonus[0][Type::Num] = $conf_cost['Exp'];
          
          $bonus[1][Type::ItemType] = Type::Money;
          $bonus[1][Type::ItemId] = 1;
          $bonus[1][Type::Num] = $conf_cost['Money'];
          return $bonus;
      }
      
      /**
      * get different rank for calculate rankpoint
      */
      public static function calculateIndexRank($rankA, $rankB)
      {
          // increase rankpoint = (ranA-RankB) in [-2,5]
          $indexRank = ($rankA - $rankB);
          if ($indexRank < -2)
            $indexRank = -2;
          else if ($indexRank > 5)
            $indexRank = 5;
          
          return $indexRank;
      }
      
      /**
      * use gem for soldier , check overwrite
      * note: if want use more than one turn, use another variable for easy change
      */
      public function addGem($listGem, $isMe)
      {
          $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
          $LastDay = date('Ymd',$this->UserBuff['LastTimeUsed']);
          if ($Today != $LastDay)
          {
              unset($this->UserBuff['Elements']);
          }
          
          $conf_maxGem = Common::getConfig('Param','MaxGemSoldier');
          //$conf_maxEle = Common::getConfig('Param','Gem');
          $conf_conflict = Common::getConfig('Param','Elements','Conflict');
          $gem_conf = Common::getConfig('Gem');
          foreach($listGem as $id => $oGem)                         
          {
              if ($oGem['GemId'] < 1)
              {
                  return false;   
              }
   
              // can not use Moc's element item for myself
              if ($oGem['Element']==Elements::MOC)
              {
                  if ($isMe){
                      
                      return false;     
                  }
                  if ($this->Element==Elements::MOC)  
                  {
                      
                      return false;
                  }
              }
              
              // check conflict
              if ($conf_conflict[$this->Element]==$oGem['Element'] && $this->Element!=Elements::KIM)
              {
                  
                  return false;
              }
                
              for($ii = 0; $ii < $oGem['Num']; $ii++ )
              {
                  $curGem = count($this->GemList[$oGem['Element']]);
                  // if current gem list not reach limit
                  if ($curGem<$conf_maxGem)
                  {
                      $this->GemList[$oGem['Element']][$curGem]['GemId'] = $oGem['GemId'];                          
                      $this->GemList[$oGem['Element']][$curGem]['Turn'] = $gem_conf[$oGem['GemId']]['Turn'];                          
                  }   
                  else // if full slot for gem list
                  {
                      
                      return false ;
                  }
              }
              
              // can use gem for myself unlimited
              if (!$isMe)
              {
                  if(isset($this->UserBuff['Elements'][$oGem['Element']][Controller::$uId])){
                    return false;      
                  }
                  else
                  {
                      if ($oGem['Element'] != Elements::MOC)
                        $this->UserBuff['Elements'][$oGem['Element']][Controller::$uId] = $oGem['Num'];        
                  }
                    
              }
                
          }
          
          
          $this->UserBuff['LastTimeUsed'] = $_SERVER['REQUEST_TIME'];
          
            
          return true;
      }
      
      /**
      * use equipment for soldier
      */
      public function useEquipment($type, $oEquip)
      {
          // check soldier type
          $conf_canTake = Common::getConfig('General','SoldierType',$this->SoldierType);
          if (!$conf_canTake['takeEquip'])
            return false;
            
          // check put on by element
          $arrUnique = array(SoldierEquipment::Armor=>1,SoldierEquipment::Helmet=>1,SoldierEquipment::Weapon=>1);
          if (isset($arrUnique[$type]))
          {
              if ($this->Element != $oEquip->Element)
                return false;
          }
          
          // check expired
          if ($oEquip->isExpired())
            return false;

          $conf_max = Common::getConfig('Param','SoldierEquipment','MaxEquipment');    
          $numCur = count($this->Equipment[$type]);
          // check max items in this type soldier can wear
          if ($numCur < $conf_max[$type])
          {
              $oEquip->IsUsed = true; 
              $oEquip->InUse = true; 
              $this->Equipment[$type][$oEquip->Id] = $oEquip;
              //$this->addBonusEquipment($oEquip,true);
              //$this->updateBonusEquipment();
              $this->addBonusEquipment($oEquip->getIndex(),true);
              return true;
          }
          else
              return false;
      }
      
      /**
      * delete equipment 
      */
      public function deleteEquipment($type, $idEquipement)
      {
          if (isset($this->Equipment[$type][$idEquipement]))
          {
              
              //$this->addBonusEquipment($this->Equipment[$type][$idEquipement],false);
              $oEquip = $this->Equipment[$type][$idEquipement];
              if ($oEquip->InUse)
                $this->addBonusEquipment($oEquip->getIndex(),false);
              unset($this->Equipment[$type][$idEquipement]);
              return true;  
          }
          return false;
            
      }
      
      /**
      * add bonus damage, critical to bonusEquipment
      * if put equip off, isIn = false
      */
      /*
      public function addBonusEquipment($oEquip, $isIn)
      {
          if ($isIn)    
            $weight = 1;
          else $weight = -1;
          
          $this->bonusEquipment['Damage'] += $weight*$oEquip->Damage;
          $this->bonusEquipment['Defence'] += $weight*$oEquip->Defence;
          $this->bonusEquipment['Critical'] += $weight*$oEquip->Critical;
          $this->bonusEquipment['Vitality'] += $weight*$oEquip->Vitality;
          
          foreach($oEquip->bonus as $idex => $obonus)
          {
              foreach($obonus as $name => $value)
              {
                  $this->bonusEquipment[$name] += $weight*$value;
              }
          }
          
          if ($this->bonusEquipment['Damage'] < 0)
            $this->bonusEquipment['Damage'] = 0;
          if ($this->bonusEquipment['Defence'] < 0)
            $this->bonusEquipment['Defence'] = 0;
          if ($this->bonusEquipment['Critical'] < 0)
            $this->bonusEquipment['Critical'] = 0;
          if ($this->bonusEquipment['Vitality'] < 0)
            $this->bonusEquipment['Vitality'] = 0;
          return true;
      }
      */
      
      public function addBonusEquipment($oBonus, $isIn)
      {
          if ($isIn)    
            $weight = 1;
          else $weight = -1;
          
          foreach($oBonus as $name => $value)   
          {
              $this->bonusEquipment[$name] += $weight*$value;
              if ($this->bonusEquipment[$name] < 0)
                $this->Equipment[$name] = 0;
          }
      }
      
      
      /**
      * recalculate bonus by all equipments
      * 
      */
      public function calculateBonusEquipment()
      {
          $bonus = array();
          foreach($this->Equipment as $index => $oType)     
          {
              foreach($oType as $id => $oEquip)
              {
                  if (!$oEquip->isExpired())
                  {
                       $bonus['Damage'] += $oEquip->Damage;                   
                       $bonus['Critical'] += $oEquip->Critical;
                       $bonus['Vitality'] += $oEquip->Vitality;
                       $bonus['Defence'] += $oEquip->Defence;
                       
                       foreach($oEquip->bonus as $id => $oIndex)
                       {
                           foreach($oIndex as $name => $value)
                           {
                               $bonus[$name] += $value;
                           }
                       }   
                  }                   
              }
          }
          return $bonus;
      }
      
      /**
      * updateBonusEquipment
      */
      
      public function updateBonusEquipment()
      {
          $this->bonusEquipment = $this->calculateBonusEquipment();
      }
      
      /**
      * reupdate durability after battle
      * 
      */
      public function updateDurability()
      {
          /*
          $conf_dura = Common::getParam('EquipmentDurability');
          foreach($this->Equipment as $eType => $oType)
          {
              if($eType == SoldierEquipment::Mask || $eType==SoldierEquipment::Seal)
                continue ;
              foreach($oType as $id => $oEquip)
              {
                  $this->Equipment[$eType][$id]->addDurability(-1/$conf_dura);
              }
          }
          */
          $oStoreEquip = StoreEquipment::getById(Controller::$uId);
          $oStoreEquip->updateDurability($this->Id);
          $oStoreEquip->save();
      }
      
      /**
      * update gem after battle
      */
      
      public function updateGemAfterBattle()
      {
          //$conf = Common::getConfig('Gem');                         
          foreach ($this->GemList as $ele => $arr_gem)
          {
              foreach($arr_gem as $index => $Info_Gem)
              {
                  $this->GemList[$ele][$index]['Turn'] -= 1 ; 
                  if($this->GemList[$ele][$index]['Turn'] <= 0)
                      unset($this->GemList[$ele][$index]);  
              }
          }
      }
  
      /**
      * put your comment there...
      * isResistance = 0 : use Item Resistance
      * @return float
      */
      public static function generateScene($listSoldierAtt, $soldierDef, $isResistance = 1, $coreAttack = false)
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
               if(!$coreAttack)
                    $attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());
               else $attackIndex[$id] = $oSoldier->getIndexCore($oSoldier->getUserId());
               
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
           if(!$coreAttack)
                $defenceIndex = $soldierDef->getIndex($soldierDef->getUserId());
           else $defenceIndex = $soldierDef->getIndexCore($soldierDef->getUserId());
           
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
                   $attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],$isResistance) ;
                   if ($attTurn['Miss'])     // if miss
                   {
                       $DamageAttack = 0; 
                       $scene[$turn]['Status']['Attack'][$id] = SceneAttack::MISS;
                   }
                   else {
                        $vitalityDamage = SoldierFish::getVitalAttack($attackIndex[$id],$defenceIndex, $attTurn['Conflict']);    
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
               $defTurn = SoldierFish::attackByTurn($soldierDef->Element,$listSoldierAtt[$idVictim]->Element,$conf_hit[$defIndex],$defenceIndex['Critical'],$isResistance) ;

               if ($defTurn['Miss'])    // if Defence attack miss 
               {
                   $DamageAttack = 0;
                   $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
               }
               else {
                    $vitalityDamage = SoldierFish::getVitalAttack($defenceIndex,$attackIndex[$idVictim],$defTurn['Conflict']);    
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
      
      
      
      public static function generateScene_newFormula($listSoldierAtt,$soldierDef, $isResistance = 1)
      {
       $conf_damage = Common::getConfig('DamageCalculation');
       $scene = array();
       $conf_Turn = Common::getParam('TurnAttack');
       $turn = 0;

       // chance to attack first
       $attackFirst = Common::randomIndex(Common::getConfig('Param','Fighting','AttackFirst'));
       // Attack list constant
       $attackIndex = array();
       foreach($listSoldierAtt as $id => $oSoldier)
       {
           // get all index 
           $attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());
           $attackIndex[$id]['Element'] = $oSoldier->Element;
           $attackIndex[$id]['Base'] = $oSoldier->getIndexBase($oSoldier->getUserId());
           
           // get rank different
           $indexRank = $oSoldier->Rank - $soldierDef->Rank;
           if ($indexRank > 10)
              $indexRank = 10;
           else if ($indexRank < -25)
              $indexRank = -25;
           $attackIndex[$id]['LevelIndex'] = $indexRank;
       }
       
       // defence constant
       $defenceIndex = $soldierDef->getIndex($soldierDef->getUserId()); 
       $defenceIndex['Element'] = $soldierDef->Element;
       $defenceIndex['Base'] = $soldierDef->getIndexBase($soldierDef->getUserId());
       
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
               $attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],$isResistance) ;

               if ($attTurn['Miss'])     // if miss
               {
                   $DamageAttack = 0; 
                   $scene[$turn]['Status']['Attack'][$id] = SceneAttack::MISS;
               }
               else {

                    $vitalityDamage = SoldierFish::getVitality($attackIndex[$id],$conf_damage[$oSoldier->Element]['RateDamage'][1],$conf_damage[$oSoldier->Element]['RateDefence'][1],
                        $defenceIndex,$attTurn['Conflict']);  
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
               
               //if ($listSoldierAtt[$idVictim]->Element!=Elements::KIM)
                //echo " ! ".(100*$attackIndex[$id]['Critical']/(2*$attackIndex[$id]['Defence'])).'<br/>'; 

            } // end attack list

           // Defence turn
           $defIndex = -$attackIndex[$idVictim]['LevelIndex'];
           if ($defIndex > 10)
              $defIndex = 10;
           else if ($defIndex < -25)
              $defIndex = -25;
           $defTurn = SoldierFish::attackByTurn($soldierDef->Element,$listSoldierAtt[$idVictim]->Element,$conf_hit[$defIndex],$defenceIndex['Critical'],$isResistance) ;
           //$attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],1) ;

           
           
           if ($defTurn['Miss'])    // if Defence attack miss 
           {
               $DamageAttack = 0;
               $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
           }
           else {                   
                $vitalityDamage = SoldierFish::getVitality($defenceIndex,$conf_damage[$soldierDef->Element]['RateDamage'][1],$conf_damage[$soldierDef->Element]['RateDefence'][1],
                    $attackIndex[$idVictim],$defTurn['Conflict']);
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
      
      
      
      public static function attackByTurn($attElement, $defElement, $chanceToHit, $criticalIndex, $isResistance)
      {
           $thisTurn = array();
           $conflict = 0;
           $conf_ele = Common::getParam('Elements');
           if ($conf_ele['Conflict'][$attElement]==$defElement)
                $conflict = 1;
           else if ($conf_ele['Conflict'][$defElement]==$attElement)
                $conflict = -1;
           
           
           $miss = (rand(1,10000)/100 > $chanceToHit) ? true : false;
           
           $criticalThreshold = Common::getParam('CriticalThreshold');     
           $cri = (rand(1,10000)/100 <= $criticalIndex*100/$criticalThreshold) ? 2 : 1; 
  
           $thisTurn['Conflict'] = $conflict*$isResistance;    
           $thisTurn['Miss'] = $miss;    
           $thisTurn['Critical'] = $cri;
           $thisTurn['AP'] = 0;   
           return $thisTurn;
      }
      
      
      /**
      * get health friend lost
      * 
      */
      public static function getVitalAttack($attIndex, $defIndex, $conflict,$alpha = 0)
      {
          if($alpha == 0)
            $alpha = ceil(max($attIndex['Vitality'],$defIndex['Vitality'])/1000)*100;

          $alphaCri = 3;
          $CriticalIndex = ($attIndex['Critical']/$alphaCri)/($attIndex['Critical']+$defIndex['Critical']);
          $CriticalRate = (rand(1,10000)/100 <= $CriticalIndex*100) ? 2 : 1;;
          
          $conf_param = Common::getConfig('Param'); 
          $conflictRate = $conflict*($conf_param['ConflictDamageRate'])/100; 
          $attIndex['Damage'] = $attIndex['Damage']*(1+$conflictRate);
          
          $damage = ceil(2*$attIndex['Damage']*$alpha/($attIndex['Damage']+$defIndex['Defence'])*$CriticalRate*(1+rand(-5,5)/100));
          if ($damage < 0)
            $damage = 0;
          return array('Damage' => $damage, 'Critical' => $CriticalRate);
      }
      
      private static function getBase($Patk, $Edef)
      {
          /*
          $alpha = floor($Edef/20);  
          $reduce = 1;
          for ($i=0; $i < floor($Edef/$alpha); $i++)
          {
                $reduce = $reduce*99/100;
          }
          return $reduce;
          */
         
          $conf_blockDamage = Common::getConfig('Param','DefenceBlockDamage');          
          $percentDecrease = floor($Edef/$conf_blockDamage['Damage']);
          if ($percentDecrease >= $conf_blockDamage['MaxPercent'])
            $percentDecrease = $conf_blockDamage['MaxPercent'];
          return (100-$percentDecrease)/100;
      }
      
      
      public static function getVitality($attIndex,$rateDamage,$rateDefence,$defIndex,$conflict)
  {

      $conf_param = Common::getConfig('Param');
      $attIndex['Critical'] = $attIndex['Critical']*$conf_param['Fighting']['CriticalRateElement'][$attIndex['Element']];
      $defIndex['Critical'] = $defIndex['Critical']*$conf_param['Fighting']['CriticalRateElement'][$defIndex['Element']];

      //Debug::log($attIndex);
      $alphaCri = 3;

      $CriticalIndex = ($attIndex['Critical']/$alphaCri)/($attIndex['Critical']+$defIndex['Critical']);
      $CriticalRate = (rand(1,10000)/100 <= $CriticalIndex*100) ? 2 : 1;;
      //$CriticalRate = 1; 

      
      $conflictRate = $conflict*($conf_param['ConflictDamageRate'])/100;
      $attIndex['Damage'] = ($attIndex['Damage']-$attIndex['Base']['Damage'] + $attIndex['Base']['Damage']*$rateDamage);
      $attIndex['Defence'] = ($attIndex['Defence']-$attIndex['Base']['Defence'] + $attIndex['Base']['Defence']*$rateDefence);
      
      $attIndex['Damage'] = $attIndex['Damage']*(1+$conflictRate); 
      $totalDamage = ($attIndex['Damage']+$attIndex['Defence']);  

      $damage = (($totalDamage)*(1+rand(-5,5)/100) - $defIndex['Defence'])*$CriticalRate;

      if ($damage <=0 )
        return array('Damage' => 0, 'Critical' => $CriticalRate);
      else return array('Damage' => ceil($damage), 'Critical' => $CriticalRate);
  }
      
         
      /**
      * get buff of soldier; include buffItem, gem, equipment vs base
      * 
      */
      public function getIndex($userId)
      {
          $arrIndex = array(); 
          $listIndex = Common::getParam('SoldierIndex');
          if (empty($userId))
            $userId = Controller::$uId;
          $oStoreEquip = StoreEquipment::getById($userId);
          
          $ReputationBuff = $this->getReputationBuff($userId);
          
          foreach($listIndex as $name)
          {
              // get from base + equipment
              if (isset($oStoreEquip->SoldierList[$this->Id]['Index'][$name]))
                $arrIndex[$name] += $this->$name + $oStoreEquip->SoldierList[$this->Id]['Index'][$name];
              else $arrIndex[$name] += $this->$name;
              //get from meridian 
              if(!empty($oStoreEquip->listMeridian[$this->Id][$name]))
              {
                  $arrIndex[$name] += $oStoreEquip->listMeridian[$this->Id][$name];
              }
              
              // them Reputation buff
              if(!empty($ReputationBuff[$name]))
                $arrIndex[$name] += $ReputationBuff[$name] ;
          }

          
          // get from BuffItem
          $arrIndex['Damage'] += $this->getDamageBuffItem();
          
          // get from Gem
          $funcGem = SoldierFish::getFunctionGem($this->GemList, $this->Element, $arrIndex['Damage']);
          $arrIndex['Damage'] += $funcGem['Damage'];
          $arrIndex['Defence'] += $funcGem['Defence'];
          $arrIndex['Vitality'] += $funcGem['Vitality'];
          $arrIndex['Critical'] += $funcGem['Critical'];

          return $arrIndex;
      }
      
      //get buff of soldier; include base without equipment, 
      public function getIndexBase($userId)
      {
          $arrIndex = array();
          $listIndex = Common::getParam('SoldierIndex');
          $oStoreEquip = StoreEquipment::getById($userId);
          foreach($listIndex as $name)
          {
              $arrIndex[$name] += $this->$name;
              
              //get from meridian 
              if(!empty($oStoreEquip->listMeridian[$this->Id][$name]))
              {
                  $arrIndex[$name] += $oStoreEquip->listMeridian[$this->Id][$name];
              }
          }
  
          return $arrIndex;
      }
      
      public function getIndexCore($userId)
      {
          $arrIndex = array(); 
          $listIndex = Common::getParam('SoldierIndex');
          if (empty($userId))
            $userId = Controller::$uId;
          $oStoreEquip = StoreEquipment::getById($userId);
          
          $ReputationBuff = $this->getReputationBuff($userId);
          
          foreach($listIndex as $name)
          {
              // get from base + equipment
              if (isset($oStoreEquip->SoldierList[$this->Id]['Index'][$name]))
                $arrIndex[$name] += $this->$name + $oStoreEquip->SoldierList[$this->Id]['Index'][$name];
              else $arrIndex[$name] += $this->$name;
              //get from meridian 
              if(!empty($oStoreEquip->listMeridian[$this->Id][$name]))
              {
                  $arrIndex[$name] += $oStoreEquip->listMeridian[$this->Id][$name];
              }
              
              // them Reputation buff
              if(!empty($ReputationBuff[$name]))
                $arrIndex[$name] += $ReputationBuff[$name] ;
              
          }
          
          return $arrIndex;
      }
      
      /**
      * get function from buffitem
      * 
      */
      public function getDamageBuffItem()
      {
            $damgeBuff = 0;          
            $conf_buff = Common::getConfig('BuffItem');
            foreach($this->BuffItem as $id => $oItem)
            {
                if ($oItem[Type::ItemType]==BuffItem::Samurai)      // if exist samurai item, increasing damage
                {
                    $damgeBuff += $conf_buff[$oItem[Type::ItemType]][$oItem[Type::ItemId]]['Num']*$oItem[Type::Num];
                }   
            }
          
            return $damgeBuff;              
      }
      
      
      public function addDiary($oDiary)
      {
          $countD = count($this->Diary);
          $this->Diary[$countD] = $oDiary;
      }
      
      // update die status of Soldier Fish
      public function updateIsDie($isDie = true)
      {
          $this->IsDie = $isDie ;
      }
      
      public function updateId($autoId)            
      {
          $this->Id = $autoId;
      }

      public function updateBonusFromJadeSeal()
      {
            $idsJadeSeal = array_keys($this->Equipment[Type::JadeSeal]);  
            $id = $idsJadeSeal[0];  // chi co 1 JadeSeal
            $oJadeSeal = $this->Equipment[Type::JadeSeal][$id];
            $jadesealConf = Common::getConfig('Wars_Seal', $oJadeSeal->Rank, $oJadeSeal->Color);
            $level = $this->getLevelJadeSeal($oJadeSeal->Rank, $oJadeSeal->Color, $jadesealConf);
            if($level > 0)
            {
                for($i = 1; $i <= $level; $i++)
                {                    
                    $oJadeSeal->updateSealOption($i, $jadesealConf[$level]);
                }
                $this->updateBonusEquipment();
            }
      }
      
      public function disableAllLevelJadeSeal()
      {
            $idsJadeSeal = array_keys($this->Equipment[Type::JadeSeal]);  
            $id = $idsJadeSeal[0];  // chi co 1 JadeSeal
            $oJadeSeal = $this->Equipment[Type::JadeSeal][$id];
            $rank = $oJadeSeal->Rank;
            $color = $oJadeSeal->Color;
            $oJadeSeal = new Seal(Type::JadeSeal, $id, $rank, $color);
            
            $this->Equipment[Type::JadeSeal][$id] = $oJadeSeal;
            $this->updateBonusEquipment();    
      }
      
      private function getLevelJadeSeal($rankRequire, $colorRequire, $jadesealConf)
      {
          $achiveLevel = 0;
            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
            $MaxEnchantLevel = Common::getConfig('General','MaxEnchantLevel');
            $numHad = 0;
            $lowEnchantLevelStan = $MaxEnchantLevel;
            foreach ($this->Equipment as $type => $arrEquip)
            {
                if(($type == Type::Mask) || ($type == Type::JadeSeal))
                    continue;
                foreach($arrEquip as $oEquip)
                {
                    if(($oEquip->Rank%100 == $rankRequire)&&(($colorRequire == 4) ? ($oEquip->Color >= $colorRequire) : ($oEquip->Color == $colorRequire)))
                        {
                            $lowEnchantLevelStan = ($oEquip->EnchantLevel < $lowEnchantLevelStan) ? $oEquip->EnchantLevel : $lowEnchantLevelStan;   
                            $numHad ++ ;
                        }
                }
            }
            if($numHad < $fullsetConf)  return 0;
          $levelSealEnchant = array();
          foreach($jadesealConf as $level => $levelConf)
          {
                $levelSealEnchant[$level] = $levelConf['Require']['EnchantLevel'];
          }
          asort($levelSealEnchant);
          foreach($levelSealEnchant as $level => $levelRequire)
                $achiveLevel = ($lowEnchantLevelStan < $levelRequire)  ? $achiveLevel : $level;
          return $achiveLevel;
      }
      
      // get Reputation Buff
      
      public function getReputationBuff($UserId)
      {
          $oUser = User::getById($UserId);
          if(!is_object($oUser))
            return array();
          $conf = Common::getConfig('ReputationBuff',$oUser->ReputationLevel);
          if(empty($conf)|| !is_array($conf))
            return array();
          return $conf ;
      }
  }
?>
