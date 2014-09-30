<?php

class MaterialService 

{
    
  /**
  * @author hieupt
  * 06/06/2011
  * new boost item formula
  */
  
  public function boostItem($param)
  {
    $TypeId = $param['TypeId'] ;
    $Num = $param['Num'];
    $PriceType = $param['PriceType'];
    
    if(!is_int($Num) || ($TypeId < 100 && $Num < 5))
        return Common::returnError(Error::PARAM);
        
    $conf_material = Common::getConfig('UpgradeMaterial');
    if(empty($conf_material[$TypeId]))
        return Common::returnError(Error::PARAM);
    if($TypeId == max(array_keys($conf_material)))
        return Common::returnError(Error::ACTION_NOT_AVAILABLE);
    $cost = $conf_material[$TypeId%100 + 1][$PriceType];
    if(empty($cost))
        return Common::returnError(Error::PARAM);
    if ($TypeId > 100)
    {
        $NumUpgrade = $Num;
        $NumReal = $Num;
    }
    else
    {
        $NumUpgrade = intval($Num/5);
        $NumReal = $NumUpgrade * 5;
    }
    
    $oStore = Store::getById(Controller::$uId);
    if (!$oStore->useItem('Material',$TypeId,$NumReal))
        return array('Error' => Error :: NOT_ENOUGH_MATERIAL);
        
    $oUser = User :: getById(Controller :: $uId);
    $moneyDiff = $oUser->Money ;
    $zMoneyDiff = $oUser->ZMoney;    
    
    switch($PriceType)
    {
        case Type::Money:
            // happyday       
        $boostItemCostRate = Common::bonusHappyWeekDay('boostItemCostRate');
        if(!$boostItemCostRate)
            $boostItemCostRate = 1;
        $money = -$cost*$NumUpgrade * $boostItemCostRate;  
        if (!$oUser->addMoney($money,'boostItem'))
            return array('Error' => Error :: NOT_ENOUGH_MONEY) ;
            break;
        case Type::ZMoney:
            $info = $TypeId.':'.'BoostMaterial:'.$NumUpgrade;
            $zmoney = -$cost*$NumUpgrade;
            if (!$oUser->addZingXu($zmoney, $info))  
                return array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
            break;
        default:
            return Common::returnError(Error::ACTION_NOT_AVAILABLE);
    }
    $TypeId = intval($TypeId%100) + 1;
    $oStore->addItem('Material', $TypeId, $NumUpgrade);
    
    $oUser->save();
    $oStore->save();
    
    $moneyDiff = $oUser->Money - $moneyDiff;
    $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
    
    Zf_log::write_act_log(Controller::$uId,0,30,'boostItem',$moneyDiff,$zMoneyDiff,$TypeId, 0, 0, 0, $NumUpgrade);
   
    $result['Error'] = Error::SUCCESS;
    $result['TypeId'] = $TypeId;
    $result['Num'] = $NumUpgrade;
    
    return $result;  
  }
  
  /**
  * Get Percent Success Mate fish by material
  */
  private function getPercentSuccess($listMaterial,$maxItemId){
    $conf_material = Common::getConfig('UpgradeMaterial'); 
    $conf_mastery = Common::getConfig('M_MaterialSkill');
    $percentSuccess = 0;
    $issetMax = false;
    foreach ($listMaterial as $mater){
      $percentSuccess += intval($mater['Num']) * $conf_material[$mater['TypeId']][$maxItemId+1];
      
      // check exist normal material with maxItemId
      if ($mater['TypeId'] == $maxItemId)
        $issetMax = true;
    }
    // decrease max rate
    if ($issetMax)
      $percentSuccess -= $conf_material[$maxItemId][$maxItemId+1];
    else 
      $percentSuccess -= $conf_material[$maxItemId+100][$maxItemId+1]; 
    $userPro = UserProfile::getById(Controller::$uId);
    // increase mastery rate
    $percentSuccess += $conf_mastery[$userPro->MatLevel]['SuccessRate'];  
    return $percentSuccess;  
  }
  
  
  /**
  * Use Gem for soldier
  * @author hieupt
  * 07/09/2011
  */
  
  public function useGem($param)
  {
      $LakeId = intval($param['LakeId']);
      $FishId = intval($param['FishId']);
      $UserId = intval($param['UserId']);
      $ListGem = $param['ListGem'];
      
      if (empty($UserId))
        $UserId = Controller::$uId;
        
      $isMe = ($UserId == Controller::$uId) ? true : false; 
      
      $oUser = User::getById(Controller::$uId);
      if (!$oUser->isFriend($UserId) && !$isMe)
        return array('Error' => Error::NOT_FRIEND);
        
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById($UserId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $oSoldier = $oLake->getFish($FishId);
      if (!$oSoldier)
        return array('Error' => Error::OBJECT_NULL);
        
      if ($oSoldier->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);
      
      $oStore = Store::getById(Controller::$uId);
      foreach($ListGem as $id => $oGem)
      {
          if (!$oStore->addGem($oGem['Element'],$oGem['GemId'],$oGem['Day'],-$oGem['Num']))
            return array('Error' => Error::NOT_GEM);
          $GemId = $oGem['GemId'];
          $GemElement = $oGem['Element'];
      }
      
      if (!$oSoldier->addGem($ListGem,$isMe))
        return array('Error' => Error::ACTION_NOT_AVAILABLE);
      
      
      if (!$isMe)
      {
          $oDiary = array();      
          $oDiary['Attacker'] = Controller::$uId;
          $oDiary['Time'] = $_SERVER['REQUEST_TIME'];
          $oDiary['GemId'] = $GemId;
          $oDiary['Element'] = $GemElement;
          $oSoldier->addDiary($oDiary);  
      }
      /*
      // check if THUY's element
      $lg = array();
      foreach($ListGem as $id => $oGem)
      {
          
        $lg[$oGem['Element']][$id]['GemId'] = $oGem['GemId'];        
      }
      $rateInc = SoldierFish::getFunctionGem($lg,$oSoldier->Element, $oSoldier->Damage);
      // add health from gem buff
      $oSoldier->addHealth($rateInc['Health']);
      */
      
      $oStore->save() ;
      $oLake->FishList[$FishId] = $oSoldier;   
      $oLake->save();
      
      // log
      Zf_log::write_act_log(Controller::$uId,$UserId, 20, 'useGem',0,0,$FishId,$ListGem[0]['Element'],$ListGem[0]['Day'],$ListGem[0]['GemId']);

      return array('Error' => Error::SUCCESS,'GemId' => $GemId);     
  }
  
  /**
  * Upgrade gem to higher level
  * @author hieupt
  * 31/08/2011
  */
  
  public function upgradeGem($param)
  {
      $element = $param['Element'];
      $listGem = $param['ListGem'];
      $levelDone = intval($param['LevelDone']);
      $slotId = intval($param['SlotId']);
      
      if (!is_array($listGem) || count($listGem)>10)
        return array('Error' => Error :: PARAM) ;
      
      if ($element > 5 || $element < 1)
        return array('Error' => Error::PARAM);
        
      if (empty($slotId))   
        $slotId = 0; 
      
      if ($slotId < 0 || $slotId>2)
        return array('Error' => Error::PARAM);
        
      $oStore = Store::getById(Controller::$uId);
      $maxDay = 7;
      $NumGem = 0;
      $minLevel = 20;  
      foreach($listGem as $id => $oGem)
      {
          if (!$oStore->addGem($element,$oGem['GemId'],$oGem['Day'],-$oGem['Num']))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
          if ($oGem['Day'] <= 0)
            return array('Error' => Error::ID_INVALID);
          if ($levelDone <= $oGem['GemId'])
            return array('Error' => Error::ID_INVALID);
          if ($oGem['GemId'] >=20 || $oGem['GemId']<0)
            return array('Error' => Error::ID_INVALID);
          if ($oGem['Day'] < $maxDay)
            $maxDay = $oGem['Day'];
          $NumGem += $oGem['Num'];
          if ($oGem['GemId'] < $minLevel)
                $minLevel = $oGem['GemId'];
      }
      
      // check enough num gem
      $conf_gem = Common::getConfig('Gem');
      $needGem = 1;
      for($i = $minLevel; $i<$levelDone; $i++)
        $needGem *= $conf_gem[$i]['NumGem'];
      if ($needGem != $NumGem)
        return array('Error' => Error::OVER_NUMBER);
      
      
      if (!$oStore->upgradeGem($element,$listGem,$maxDay, $minLevel, $levelDone, $slotId))
        return array('Error' => Error::ACTION_NOT_AVAILABLE);
      $oStore->save();
      
      // log
      Zf_log::write_act_log(Controller::$uId,0, 20, 'upgradeGem',0, 0,$element,$levelDone);
      
      return array('Error' => Error::SUCCESS);
  }
  
  /**
  * Recover expired gem
  * @author hieupt
  * 31/08/2011
  */
  public function recoverGem($param)
  {
    $ListGem = $param['ListGem'];
    if (!is_array($ListGem))
        return array('Error' => Error::PARAM);
        
    $oStore = Store::getById(Controller::$uId);
    $conf_gem = Common::getConfig('Gem');
    $oUser = User::getById(Controller::$uId);
    
    $zMoneyLost = 0;
    
    $oldMoney = $oUser->Money;
    $oldZMoney = $oUser->ZMoney;
    
    foreach($ListGem as $id => $oGem)
    {
        if ($oGem['Day'] > 0 || $oGem['Day'] < -6)    
            return array('Error' => Error::ID_INVALID);
            
        if ($oGem['Element'] > 5 || $oGem['Element'] < 1)
            return array('Error' => Error::ID_INVALID);
            
        if ($oGem['GemId'] < 0 || $oGem['GemId'] >= count($conf_gem))
            return array('Error' => Error::ID_INVALID);
            
        if ($oGem['Num'] <= 0)
            return array('Error' => Error::ID_INVALID);
        if($conf_gem[$oGem['GemId']]['MoneyRecover'] != 0)
        {      
            if (!$oUser->addMoney(-$conf_gem[$oGem['GemId']]['MoneyRecover']*$oGem['Num'],'recoverGem'))
                return array('Error' => Error::NOT_ENOUGH_MONEY);
        }    
        else
        {        
            $info = '222'.':'.'UpgradeGem'.':1' ; 
            if (!$oUser->addZingXu(-$conf_gem[$oGem['GemId']]['ZMoneyRecover']*$oGem['Num'], $info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
            $zMoneyLost += $conf_gem[$oGem['GemId']]['ZMoneyRecover']*$oGem['Num'];
        }

        if (!$oStore->addGem($oGem['Element'],$oGem['GemId'],$oGem['Day'],-$oGem['Num']))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
 
        if (!$oStore->addGem($oGem['Element'],$oGem['GemId'],7,$oGem['Num']))
            return array('Error' => Error::ACTION_NOT_AVAILABLE); 
 
    }
  
    $oStore->save();
    $oUser->save();
    
    
    $diffMoney = $oUser->Money - $oldMoney;
    $diffZMoney = $oUser->ZMoney - $oldZMoney;
    
    // log    
    Zf_log::write_act_log(Controller::$uId,0, 20, 'recoverGem',$diffMoney,$diffZMoney,$ListGem[0]['Element'],$ListGem[0]['Day'],$ListGem[0]['GemId'],$ListGem[0]['Num']);    

    
    
    return array('Error' => Error::SUCCESS);                            
  }
  
  /**
  * Cancel upgrade gem
  * @author hieupt
  * 01/09/2011
  */
  
  public function cancelUpgrade($param) 
  {
    $IdUpgradingGem = intval($param['GemId']);
    if (empty($IdUpgradingGem))
        $IdUpgradingGem = 0;
        
    $conf_maxGem = Common::getConfig('Param','MaxUpgradingGem');        
    if ($IdUpgradingGem < 0 || $IdUpgradingGem >= $conf_maxGem)    
        return array('Error' => Error::ID_INVALID);
        
    $oStore = Store::getById(Controller::$uId);
    if (!is_array($oStore->UpgradingGem[$IdUpgradingGem]))
        return array('Error' => Error::OBJECT_NULL);
        
    if (!$oStore->cancelUpgrade($IdUpgradingGem))
        return array('Error' => Error::ACTION_NOT_AVAILABLE);

    $oStore->save();
        
    return array('Error' => Error::SUCCESS);    
        
          
  }
  
  /**
  * Get gem upgrade successfully
  * @author hieupt
  * 31/08/2011
  */
  public function getGem($param)
  {
    $IdUpgradingGem = intval($param['GemId']);
            
    $conf_maxGem = Common::getConfig('Param','MaxUpgradingGem');        
    if ($IdUpgradingGem < 0 || $IdUpgradingGem >= $conf_maxGem)    
        return array('Error' => Error::ID_INVALID);
        
    $oStore = Store::getById(Controller::$uId);
    if (!is_array($oStore->UpgradingGem[$IdUpgradingGem]))
        return array('Error' => Error::OBJECT_NULL);
    
    $oGem = $oStore->UpgradingGem[$IdUpgradingGem];     
    if (!$oStore->getGem($IdUpgradingGem))
        return array('Error' => Error::NOT_ENOUGH_TIME);

    $oStore->save();
        
    return array('Error' => Error::SUCCESS, 'GemId' => $oGem['LevelDone']);
            
  }
  
  /**
  * Quick upgrade gem 
  * @author hieupt
  * 01/09/2011
  */
  public function quickUpgrade($param)
  {
    $IdUpgradingGem = intval($param['GemId']);
    $isMoney = $param['isMoney'];
    
    if (empty($IdUpgradingGem))
        $IdUpgradingGem = 0;

        
    $conf_maxGem = Common::getConfig('Param','MaxUpgradingGem');        
    if ($IdUpgradingGem < 0 || $IdUpgradingGem >= $conf_maxGem)    
        return array('Error' => Error::ID_INVALID);
        
    $oStore = Store::getById(Controller::$uId);
    if (!is_array($oStore->UpgradingGem[$IdUpgradingGem]))
        return array('Error' => Error::OBJECT_NULL);
    
    $curLevel = $oStore->getCurLevelUpgrading($IdUpgradingGem);
    if ($curLevel >= $oStore->UpgradingGem[$IdUpgradingGem]['LevelDone'])
        return array('Error' => Error::ACTION_NOT_AVAILABLE);
    
    // check money vs zmoney    
    $oUser = User::getById(Controller::$uId);
    $cost = $oStore->getCostUpgrade($curLevel,$oStore->UpgradingGem[$IdUpgradingGem]['LevelDone']-$curLevel);
    if ($isMoney)
    {
        if (!$oUser->addMoney(-$cost['Money'],'quickUpgrade'))
            return array('Error' => Error::NOT_ENOUGH_MONEY);
    }
    else
    {
        $info = '222'.':'.'QuickUpgrade'.':1' ; 
        if (!$oUser->addZingXu(-$cost['ZMoney'], $info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);    
    }
    
    // get min GemId
    $oGem = $oStore->UpgradingGem[$IdUpgradingGem]['ListGem'];
    $minGemId = 10;
    foreach($oGem as $id => $ooGem)
    {
        if ($minGemId > $ooGem['GemId'])
            $minGemId = $ooGem['GemId'];
    }
    if (!$oStore->quickUpgrade($IdUpgradingGem, $minGemId))
        return array('Error' => Error::ACTION_NOT_AVAILABLE);

    $oStore->save();
    $oUser->save();
    
    // log
    Zf_log::write_act_log(Controller::$uId,0, 23, 'quickUpgradeGem',0, -$cost['ZMoney'],$minGemId);
    
    return array('Error' => Error::SUCCESS);              
  }
  
  /**
  * Delete gem in store
  * @author hieupt
  * 12/09/2011
  */
  
  public function deleteGem($param)
  {
    $ListGem = $param['ListGem'];
    if (!is_array($ListGem))
        return array('Error' => Error::PARAM);
        
    $oStore = Store::getById(Controller::$uId);
    $oUser = User::getById(Controller::$uId);
    
    foreach($ListGem as $id => $oGem)
    {
        if (!$oStore->addGem($oGem['Element'],$oGem['GemId'],$oGem['Day'],-$oGem['Num']))
            return array('Error' => Error::NOT_ENOUGH_ITEM);      
    }
    
    $oStore->save();
    Zf_log::write_act_log(Controller::$uId,0,20,'deleteGem',0,0,$ListGem[0]['Element'],$ListGem[0]['Day'],$ListGem[0]['GemId'],$ListGem[0]['Num']);
    return array('Error' => Error::SUCCESS);
        
  }
  
   /**
  * add Material into Fish
  * @author AnhBV
  * 10/05/2011
  */
  
  public function addMaterialIntoFish($param)
  {
      $ItemType     = $param['ItemType'];
      $Id           = $param['Id'];
      $LakeId       = $param['LakeId'];
      $MaterialId   = $param['MaterialId'];
      
      if(empty($Id) || $LakeId < 1 ||$LakeId > 3)
        return array('Error' => Error::PARAM);
      if($MaterialId < 6 || $MaterialId > 110 || ($MaterialId < 106 && $MaterialId > 10) )
        return array('Error' => Error::PARAM);
      
      $oUser = User::getById(Controller::$uId);
      $oStore = Store::getById(Controller::$uId);
            
      if(!is_object($oUser) || ! is_object($oStore))
      {
          return array('Error' => Error::NO_REGIS);
      }
            
      $oDecoration = Decoration::getById(Controller::$uId,$LakeId);
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if(!is_object($oLake) || !is_object($oDecoration))
      {
          return array('Error' => Error::LAKE_INVALID);
      }
      
      //kiem tra xem vien do co ton tai trong kho khong
      if(!$oStore->useItem(Type::Material,$MaterialId,1))
      {
          return array('Error' => Error::NOT_ENOUGH_ITEM);
      }
      
      // cong option vao cho ca
      $conf_Material = Common::getConfig(Type::Material,$MaterialId);
      if(!is_array($conf_Material))
      {
          return array('Error' => Error::NOT_LOAD_CONFIG);
      }
      
      
      $SpartaFamily = Common::getParam('SpartaFamily');
      if(in_array($ItemType,$SpartaFamily,true))
      {
            if(empty($ItemType))
                return array('Error' => Error::PARAM);
                
            $object = $oDecoration->getSpecialItem($ItemType,$Id);
            if(!is_object($object))
            {
                return array('Error' => Error::OBJECT_NULL);
            }
            if($object->isExpried  == false )
            {
                return array('Error' => Error::EXPIRED);
            }
          
      }
      else if (in_array($ItemType,array(Type::Fish,Type::RareFish),true))
      {
          $object = $oLake->getFish($Id);
          if(!is_object($object))
          {
              return array('Error' => Error::OBJECT_NULL);
          }
          $fish_conf = Common::getConfig('Fish',$object->FishTypeId);
          // chan kho cho ca cap cao add ngu thach cap thap
          $maxLevelUse = Common::getParam('MaxLevelUseMaterial');
          if($fish_conf['LevelRequire'] >= $maxLevelUse && $MaterialId ==6)
            return array('Error' => Error::NOT_ENOUGH_LEVEL);
          
          
      }
      else
      {
            return array('Error' => Error::TYPE_INVALID);
      }
      
      // kiem tra so slot cua ca
      if(count($object->Material)>= 5 )
      {
         return array('Error' => Error::OVER_NUMBER); 
      }
      
      // them Material vao doi tuong 
      $object->addMaterial($MaterialId);
      
      $ran = array('Exp'=>10,'Money'=>10,'Time'=>10);
      $arr_buff = array_intersect_key($ran,$conf_Material['Buff']);
      $keyOption = Common::randomIndex($arr_buff);
      $option[$keyOption] = $conf_Material['Buff'][$keyOption];
      
      if ($ItemType == Type::Fish && $object->FishType == FishType::NORMAL_FISH )
      {
          // convert Fish => RareFish
         $newObject = Common::convertObjectFish($object,Type::RareFish);
         if(!is_object($newObject)) 
            array('Error' => Error::ACTION_NOT_AVAILABLE);
         
         $newObject->updateOption($option,true);
         $newObject->FishType = FishType::RARE_FISH ;
         // xoa doi tuong cu trong ho di
         $oLake->delFish($object->Id); // ham nay da tu buff cho ho 
         // them doi tuong moi vao ho    
         $oLake->addFish($newObject); 
             
      }
      else if ($ItemType == Type::RareFish && $object->FishType == FishType::RARE_FISH )
      {
          // cong option vao cho superFish
          $object->updateOption($option,true);
          $oLake->buffToLake($option, true);
      }
      else if(in_array($ItemType,$SpartaFamily,true))
      {
          // cong option vao cho superFish
          $object->updateOption($option,true);
          // them buff vao cho ho
          $oLake->buffToLake($option, true);
      }
      else
      {
          return array('Error' => Error::TYPE_INVALID);
      }
       
      $oLake->save();
      $oDecoration->save();
      $oStore->save();    
      
      $result['Error'] = Error::SUCCESS ;
      $result['Option'] = $option ;
      
      //log
      Zf_log::write_act_log(Controller::$uId,0,20,'addMaterialIntoFish',0,0,$ItemType,$Id,$MaterialId);
      return $result ;
      
  }
  

}


?>
