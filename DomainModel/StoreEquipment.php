<?php
class StoreEquipment extends Model
{
    /*  Mang trang bi ca linh
              - Id
                + Index
                + Equipment 
    */  
    public $SoldierList = array();
           
    /* Mang ngu mach ca linh
        - Id
            + meridianRank cap ngu mach tu 1-13
            + meridianPosition vi tri diem mach tu 0-10
            + meridianPoint diem ngu mach
            + Damage  Tong damage nhan duoc 
            + Vitality
            + Critical
            + Defence
    */
    public $listMeridian = array();
            
    public function __construct($uId)
    {
        parent :: __construct($uId) ;
    }
    
    public function addEquipment($soldierId, $soldierElement, $oEquip)
    {           
          // check put on by element
          $arrUnique = array(SoldierEquipment::Armor=>1,SoldierEquipment::Helmet=>1,SoldierEquipment::Weapon=>1);
          if (isset($arrUnique[$oEquip->Type]))
          {
              if ($soldierElement != $oEquip->Element)
                return false;
          }
          
          // check expired
          if ($oEquip->isExpired())
            return false;

          $conf_max = Common::getConfig('Param','SoldierEquipment','MaxEquipment');    
          $numCur = count($this->SoldierList[$soldierId]['Equipment'][$oEquip->Type]);
          
          // check max items in this type soldier can wear
          if ($numCur < $conf_max[$oEquip->Type])
          {
              $oEquip->IsUsed = true; 
              $oEquip->InUse = true; 
              $this->SoldierList[$soldierId]['Equipment'][$oEquip->Type][$oEquip->Id] = $oEquip;
              $this->addBonusEquipment($soldierId, $oEquip->getIndex(),true);
              //$this->updateBonusEquipment($soldierId);
              return true;
          }
          else
              return false;            
    }

    public function addQuartz($soldierId, $oQuartz) {
          if ($oQuartz->isExpired())
            return false;

          $oQuartz->IsUsed = true; 
          $oQuartz->InUse = true; 
          $this->SoldierList[$soldierId]['Equipment'][$oQuartz->Type][$oQuartz->Id] = $oQuartz;
          $this->addBonusEquipment($soldierId, $oQuartz->getIndex(),true);          
          return true;        
    }

    public function deleteQuartz($soldierId, $type, $idQuartz)
    {
          if (isset($this->SoldierList[$soldierId]['Equipment'][$type][$idQuartz]))
          {
              $oEquip = $this->SoldierList[$soldierId]['Equipment'][$type][$idQuartz];
                              
              if ($oEquip->InUse)
              {
                  $this->addBonusEquipment($soldierId, $oEquip->getIndex(),false);                     
              }
              unset($this->SoldierList[$soldierId]['Equipment'][$type][$idQuartz]);   
              
              
              return true;  
          }
          return false;    
    }
    
    public function getQuartz($soldierId, $type, $id) {
        return $this->SoldierList[$soldierId]['Equipment'][$type][$id];
    }
        
    
    public function deleteEquipment($soldierId, $type, $idEquipement)
    {
          if (isset($this->SoldierList[$soldierId]['Equipment'][$type][$idEquipement]))
          {
              $oEquip = $this->SoldierList[$soldierId]['Equipment'][$type][$idEquipement];
                            
                
              if ($oEquip->InUse)
              {
                  $this->addBonusEquipment($soldierId, $oEquip->getIndex(),false);                     
              }
              unset($this->SoldierList[$soldierId]['Equipment'][$type][$idEquipement]);   
              
              $this->updateBonusEquipment($soldierId);     
              
              return true;  
          }
          return false;    
    }
    
    public function addBonusEquipment($soldierId, $oBonus, $isIn)
    {
        if ($isIn)    
          $weight = 1;
        else $weight = -1;
      
        foreach($oBonus as $name => $value)   
        {
            $this->SoldierList[$soldierId]['Index'][$name] += $weight*$value;
            if ($this->SoldierList[$soldierId]['Index'][$name] < 0)
              $this->SoldierList[$soldierId]['Index'][$name] = 0;
        }
    }
    
      public function updateBonusFromJadeSeal($soldierId)
      { 
            $idsJadeSeal = array_keys($this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal]);  
            $id = $idsJadeSeal[0];  // chi co 1 JadeSeal
            $oJadeSeal = $this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal][$id];
            $jadesealConf = Common::getConfig('Wars_Seal', $oJadeSeal->Rank, $oJadeSeal->Color);
            $level = $this->getLevelJadeSeal($soldierId, $oJadeSeal->Rank, $oJadeSeal->Color, $jadesealConf);
            $oJadeSeal->disableAllLevelJadeSeal(); 
            if($level > 0)
            {
                for($i = 1; $i <= $level; $i++)
                {                    
                    $oJadeSeal->updateSealOption($i, $jadesealConf[$i]);
                    if(isset($jadesealConf[$i]['TotalPercent']))
                        $this->setPercentBonus($soldierId, $jadesealConf[$i]['TotalPercent']);
                }
                $this->updateBonusEquipment($soldierId);
            }
      }
      
      public function disableAllLevelJadeSeal($soldierId)
      {
            $idsJadeSeal = array_keys($this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal]);  
            $id = $idsJadeSeal[0];  // chi co 1 JadeSeal
            $oJadeSeal = $this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal][$id];
            $oJadeSeal->disableAllLevelJadeSeal();
            $this->setPercentBonus($soldierId, 0);
            $this->updateBonusEquipment($soldierId);    
      }
      
      public function setPercentBonus($soldierId, $percent)
      {
          $arrQuartzType = Common::getConfig('General', 'QuartzTypes');
          foreach($this->SoldierList[$soldierId]['Equipment'] as $type => $arrEquip)
          {
              if($type == Type::Mask || $type == Type::JadeSeal || in_array($type, $arrQuartzType)) continue;
              foreach ($arrEquip as $oEquip)
              {
                  $oEquip->PercentBonus = $percent;
              }
          }
      }
      
      private function getLevelJadeSeal($soldierId, $rankRequire, $colorRequire, $jadesealConf)
      {
          $achiveLevel = 0;
            $fullsetConf = Common::getConfig('Param','FullSetJadeSeal');
            $numHad = 0;
            $lowEnchantLevelStan = Common::getConfig('General','MaxEnchantLevel');
            $minColor = 20;
            $arrQuartzType = Common::getConfig('General', 'QuartzTypes');
            foreach ($this->SoldierList[$soldierId]['Equipment'] as $type => $arrEquip)
            {
                if(($type == Type::Mask) || ($type == Type::JadeSeal) || in_array($type, $arrQuartzType))
                    continue;
                foreach($arrEquip as $oEquip)
                {
                    if(($oEquip->Color >= 4) && !$oEquip->isExpired())
                        {
                            $lowEnchantLevelStan = ($oEquip->EnchantLevel < $lowEnchantLevelStan) ? $oEquip->EnchantLevel : $lowEnchantLevelStan;   
                            $minColor = ($minColor > $oEquip->Color) ? $oEquip->Color : $minColor;
                            $numHad ++ ;
                        }
                }
            }
          if($numHad < $fullsetConf)  return 0;
          $levelSealEnchant = array();
          foreach($jadesealConf as $level => $levelConf)
          {
                $levelSealEnchant[$level]['EnchantLevel'] = $levelConf['Require']['EnchantLevel'];                
                $levelSealEnchant[$level]['ColorRequire'] = $levelConf['Require']['Color'];
          }
          $levelSealEnchant = array_reverse($levelSealEnchant, true);
          //asort($levelSealEnchant);
          foreach($levelSealEnchant as $level=>$require)
          {
              if(isset($require['ColorRequire']))
              {
                  if(($minColor >= $require['ColorRequire']) && ($lowEnchantLevelStan >= $require['EnchantLevel']))
                  {
                      $achiveLevel = $level;
                      break;
                  }
              }elseif($lowEnchantLevelStan >= $require['EnchantLevel'])
              {
                  $achiveLevel = $level;
                  break;
              }              
          }                
          return $achiveLevel;
      }
      
      
      /**
      * recalculate bonus by all equipments
      * 
      */
      public function calculateBonusEquipment($soldierId)
      {   
          $arrQuartzType = Common::getConfig('General', 'QuartzTypes');                   
          $bonus = array();
          foreach($this->SoldierList[$soldierId]['Equipment'] as $index => $oType)     
          {
              
              foreach($oType as $id => $oEquip)
              {
                  if(in_array($oEquip->Type,$arrQuartzType) ){
                      $oQuartz = $this->getQuartz($soldierId,$oEquip->Type,$oEquip->Id);                                            
                      $this->addBonusEquipment($soldierId,$oQuartz->getIndex(),true);
                      
                      $bn = $oQuartz->getIndex();
                      $bonus["Damage"] += $bn["Damage"];
                      $bonus["Defence"] += $bn["Defence"];
                      $bonus["Critical"] += $bn["Critical"];
                      $bonus["Vitality"] += $bn["Vitality"];                                            
                  } elseif($index != Type::JadeSeal) {
                      if (!$oEquip->isExpired())
                      {
                          $percentBonus = (isset($oEquip->PercentBonus)) ? $oEquip->PercentBonus : 0;
                           $bonus['Damage'] += $oEquip->Damage + ceil($percentBonus*$oEquip->Damage/100);                   
                           $bonus['Critical'] += $oEquip->Critical + ceil($percentBonus*$oEquip->Critical/100);
                           $bonus['Vitality'] += $oEquip->Vitality + ceil($percentBonus*$oEquip->Vitality/100);
                           $bonus['Defence'] += $oEquip->Defence + ceil($percentBonus*$oEquip->Defence/100);
                           
                           foreach($oEquip->bonus as $id => $oIndex)
                           {
                               foreach($oIndex as $name => $value)
                               {
                                   $bonus[$name] += $value + ceil($percentBonus*$value/100);
                               }
                           }   
                      }                                         
                  }
                  else{
                       $bonus['Damage'] += $oEquip->Damage ;                   
                       $bonus['Critical'] += $oEquip->Critical ;
                       $bonus['Vitality'] += $oEquip->Vitality ;
                       $bonus['Defence'] += $oEquip->Defence ;
                  }
              }
          }          
                    
          return $bonus;
      }
      
      /**
      * updateBonusEquipment
      */
      
      public function updateBonusEquipment($soldierId)
      {
          $this->SoldierList[$soldierId]['Index'] = $this->calculateBonusEquipment($soldierId);
      }
      
      public function updateDurability($soldierId)
      {
          if (!isset($this->SoldierList[$soldierId]))
            return false;
          $conf_dura = Common::getParam('EquipmentDurability');
          $arrQuartzType = Common::getConfig('General', 'QuartzTypes');
          // JadeSeal
		  $hadJadeSeal = false;
		$disableJadeSeal = false;
          if(!empty($this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal]))
          {
              $hadJadeSeal = true;
          }
          
          foreach($this->SoldierList[$soldierId]['Equipment'] as $eType => $oType)
          {
              if($eType == SoldierEquipment::Mask || $eType==SoldierEquipment::Seal  || in_array($eType, $arrQuartzType) )
                continue ;
              foreach($oType as $id => $oEquip)
              {
                  $this->SoldierList[$soldierId]['Equipment'][$eType][$id]->addDurability(-1/$conf_dura);
                  
                  // disable jadeseal, because of expired equip
                  if($oEquip->isExpired() && $hadJadeSeal && !$disableJadeSeal)
                  {
						 $this->disableAllLevelJadeSeal($soldierId);  
						$disableJadeSeal = true;
					}
              }
          }    
      }
      
      public function getEquipment($soldierId, $equipType, $equipId)      
      {
          return $this->SoldierList[$soldierId]['Equipment'][$equipType][$equipId];
      }
      
      public function setEquipment($soldierId, $equipType, $equipId, $oEquip)
      {
          $this->SoldierList[$soldierId]['Equipment'][$equipType][$equipId] = $oEquip;
          
            //update JadeSeal
            if(!empty($this->SoldierList[$soldierId]['Equipment'][Type::JadeSeal]))
            {
                $this->updateBonusFromJadeSeal($soldierId);
            }
            // end update JadeSeal
      }
      
    public function deleteSoldier($soldierId)  
    {
        if (isset($this->SoldierList[$soldierId]))
            unset($this->SoldierList[$soldierId]);
    }
    
    // them ngu mach 
    public function addMeridian($soldierId,$Num)
    {
        if(empty($soldierId)|| $Num < 0)
            return false ;
        $this->listMeridian[$soldierId]['meridianPoint'] += $Num ;
        if(empty($this->listMeridian[$soldierId]['meridianRank']))
        {
            $this->listMeridian[$soldierId]['meridianRank'] = 1;
        }
        return true ;   
    }
    // su dung ngu mach
    public function useMeridian($soldierId,$Num)
    {
        if(empty($soldierId)|| $Num < 0)
            return false ;
        if( $this->listMeridian[$soldierId]['MeridianPoint'] < $Num )
            return false ;
        $this->listMeridian[$soldierId]['MeridianPoint'] -= $Num ;
        if(empty($this->listMeridian[$soldierId]['meridianRank']))
        {
            $this->listMeridian[$soldierId]['meridianRank'] = 1;
        }
        return true ;   
        
    }
       

    public static function getById($uId)
    {   
        $oStoreE = DataProvider :: get($uId,__CLASS__) ;
        if (!is_object($oStoreE))
        {
            $oStoreE = new StoreEquipment($uId);
        }
        return $oStoreE;
    }


    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }
    
    public function upgradeMeridianPosition($soldier)
    {         
        $soldierId = $soldier->Id;
        $meridianRank = $this->listMeridian[$soldierId]['meridianRank'];
        $meridianPosition = $this->listMeridian[$soldierId]['meridianPosition'];
        $meridianPoint = $this->listMeridian[$soldierId]['meridianPoint'];              
        if($meridianPosition < 10)
        {
            $requirePoint = Common::getConfig('MeridianPointRequire', $meridianRank, $meridianPosition + 1);
        }
        else
        {
            $requirePoint = Common::getConfig('MeridianPointRequire', $meridianRank + 1, 1);
        }                                                                                                     
        if($requirePoint && $requirePoint <= $meridianPoint)
        { 
            //Cap nhat chi so ca linh    
            $configActive = Common::getConfig('ActiveMeridian', $soldier->Element, $meridianRank);
            $configActive = $configActive[$meridianPosition+1];
            foreach($configActive as $index=>$value)
            { 
                if(empty($this->listMeridian[$soldierId][$index]))
                {
                    $this->listMeridian[$soldierId][$index] = 0;
                }                                              
                $this->listMeridian[$soldierId][$index] += $value;  
            }
            //Cap nhat mach ca         
            $this->listMeridian[$soldierId]['meridianPoint'] -= $requirePoint;  
            if($meridianPosition < 9)
            {                                                                                
                $this->listMeridian[$soldierId]['meridianPosition']++;
            }
            else
            {                                             
                $this->listMeridian[$soldierId]['meridianRank']++;
                $this->listMeridian[$soldierId]['meridianPosition'] = 0;   
            }
           
            return true;
        }
        return false;
    }
    
}
?>
