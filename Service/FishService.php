<?php


/**
 * Fish Service
 * @author Toan Nobita
 * 2/9/2010
 */
class FishService extends Controller
{

	/*
	 * Cham soc ca
	 * @author ToanTN
	 * 13/02/2010
	 */
	public function careFriendFish($param)
	{
		$FriendId   = $param['UserId'];
		$LakeId     = $param['LakeId'];
		$FishList   = $param['FishList'];
		$FishId     = $FishList[0];

		// kiem tra du lieu vao
		if (empty ($FriendId) || empty ($LakeId) || empty ($FishId) || ($LakeId > 3) || ($LakeId < 1))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
		if (!$oUser->isFriend($FriendId))
		{
			return array('Error' => Error :: NOT_FRIEND) ;
		}
		$energyConfig = Common :: getConfig('Energy') ;
		$expConfig = Common :: getConfig('Experience') ;
		// luu thong so cu~
		$expDiff = $oUser->Exp;

		// lay thong tin ca

		$oLake = Lake::getById($FriendId, $LakeId);
		$oFish = $oLake->getFish($FishId);
		if (!is_object($oFish))
		{
			return array('Error' => Error :: FISH_NOT_EXITS) ;
		}
		// kiem tra co du nang luong khong
		if (!$oUser->addEnergy(- $energyConfig['carefriendfish']))
		{
			return array('Error' => Error :: NOT_ENOUGH_ENERGY) ;
		}
		// Cham soc ca
		if (!$oFish->careFriendFish())
		{
			return array('Error' => Error :: CANT_CARE_FISH) ;
		}
		
		$oLake->save();

		// cap nhat diem cho user
		$oUser->addExp($expConfig['carefriendfish']) ;

		// random ra nguyen lieu lai
        
	    $arr_result['Bonus'][] = $oUser->randomActionGift($oFish->FishTypeId);
      	$oUser->saveBonus($arr_result['Bonus']);

		$oUser->save() ;
        
		$arr_result['Exp'] = $oUser->Exp - $expDiff ;
		$arr_result['Num'] = 1 ;
		$arr_result['Error'] = Error :: SUCCESS ;
        
		//Zf_log::write_act_log(Controller::$uId, $FriendId, 20, 'careFriendFish');
		
		////////////
		return $arr_result ;

	}
  
  public function collectMoney($param)
    {
        $FishList = $param['FishList'];
        $lakeId = $param['LakeId'];
        $friendId = $param['FriendId'];
        $isMagnet = $param['isMagnet'];

        if (empty ($FishList) || empty ($lakeId)|| empty ($friendId))
        {
            return array('Error' => Error :: PARAM) ;
        }

        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        if($friendId != Controller::$uId)
        {
          if (!$oUser->isFriend($friendId))
          {
             return array('Error' => Error :: NOT_FRIEND) ;
          }
        }
 
        $oLake = Lake::getById($friendId, $lakeId);
        if(!is_object($oLake))
        {
          return array('Error' => Error :: LAKE_INVALID) ;   
        }
        $oldmoney = $oUser->Money ;
        foreach( $FishList as $index => $fishId)
        {
            $oFish = $oLake->getFish($fishId);
            if (!is_object($oFish))
            {
                return array('Error'  =>  Error::OBJECT_NULL);
            }
            
            $currentTimes = $oFish->getCurrentPocketNum();
            if($currentTimes <=0 )
            {
                return array('Error' => Error::CANT_STEAL_MONEY);  
            } 
            $res = $oFish->collectMoney();
            if ($res['Error'] != Error::SUCCESS)
                return array('Error'  =>  $res['Error']); 
            $confFish = Common::getConfig('Fish',$oFish->FishTypeId);
            if(!is_array($confFish)) 
            {
                return array('Error' => Error::NOT_LOAD_CONFIG);        
            }
            $oUser->addMoney($confFish['StealOnce']); 
        }
        
        if ($isMagnet)
        {
            $oMagnet = $oUser->SpecialItem[Type::Magnet];
            if (!is_object($oMagnet))
                return array('Error' => Error::OBJECT_NULL);
            if (!$oMagnet->useMagnet())
                return array('Error' => Error::ACTION_NOT_AVAILABLE);
            $oUser->SpecialItem[Type::Magnet] = $oMagnet;
            Zf_log::write_act_log(Controller::$uId,0, 20, 'useMagnet');  
        }
        
        $oUser->save();
        $oLake->save();       
        // log
        
        $diffmoney = $oUser->Money - $oldmoney ;
        Zf_log::write_act_log(Controller::$uId,$friendId, 30, 'stealMoney',$diffmoney, 0);
        return array('Error'  => 0);
    }


	/**
	 * Cho ca an
	 * @author Toan Nobita
	 * 2/9/2010
	 */


	public function feed($param)
	{
		$FishList       = $param['FishList'];
		$TotalAmount    = intval($param['TotalAmount']);
		$LakeId         = $param['LakeId'];
		$FriendId       = $param['UserId'];
    
		if (empty ($FriendId) || empty ($LakeId) || ($TotalAmount < 1) || ($LakeId < 1) || ($LakeId > 3))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
		if (Controller :: $uId != $FriendId)
		{
			if (!$oUser->isFriend($FriendId))
			return array('Error' => Error :: NOT_FRIEND) ;  
			
		}
		
		$EnergyConfig = Common :: getConfig('Energy') ;
		if (!$oUser->addEnergy(- $EnergyConfig['feed'] * $TotalAmount))
			return array('Error' => Error :: NOT_ENOUGH_ENERGY) ;
            
        if ($oUser->addFood(-$TotalAmount*Common ::getParam(PARAM::FoodAmount)))
        {
            return array('Error' => Error :: NOT_ENOUGH_FOOD) ;
        }
			
		$totalEated = 0 ;
		$runArr = array();

        $oLake = Lake::getById($FriendId, $LakeId);
                
        if(is_array($FishList))
        {
            foreach ($FishList as $key => $Value)
            {
            	$temp = 0 ;
            
            	$oFish = $oLake->getFish($Value['Id']);
            	if(!is_object($oFish))
            	{
            	    return  array('Error' => Error :: FISH_NOT_EXITS) ;
            	}
            	$temp = ceil($oFish->eat($Value['EatAmount'])) ;
            	$totalEated += $temp ;
                
                if($temp > 0)
                {
                    for($i = 0;$i < $temp ; $i++)
                    {
                        $runArr['Bonus'][] = $oUser->randomActionGift($oFish->FishTypeId);          
                    }
                }
            }
        }
        
        if($TotalAmount*Common ::getParam(PARAM::FoodAmount) < $totalEated )
         return array('Error' => Error :: CANT_FISHING) ; 
        
        $ExpConfig = Common :: getConfig('Experience') ;
		$feedExp = $totalEated * $ExpConfig['feed'];
		$oUser->addExp($feedExp);
        $oUser->saveBonus($runArr['Bonus']);
        
		$oLake->save();
		$oUser->save() ;   	
        
        
        
        $runArr['Exp'] = $feedExp ;
		$runArr['Num'] = $totalEated;
		$runArr['Error'] = Error :: SUCCESS ;
		//log
		//Zf_log::write_act_log(Controller::$uId, $FriendId, 20,'feed');

		///////////
		return $runArr ;
	}

	/**
	 * Ban ca
	 * @author AnhBV
	 * 2/4/2011
	 */

	public function sell($param)
	{
		$FishList = $param['FishList'];
		$LakeId = $param['LakeId'];
		$FishId = $FishList[0];
        $arrQuartzType = Common::getConfig('General', 'QuartzTypes');

		if (empty ($FishId) || ($LakeId < 1) || ($LakeId > 3))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		
        }

		$oLake = Lake::getById(Controller::$uId, $LakeId);
        if (!is_object($oLake))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
        
		$oFish = $oLake->getFish($FishId);
		if (!is_object($oFish))
		{
			return array('Error' => Error :: FISH_NOT_EXITS) ;
		}
        
		$confFish = & Common::getConfig('Fish',$oFish->FishTypeId);

		$ValueFish = $oFish->getValue();
		
		$expFish =  $oFish->getExp();

		// cong tien va kinh nghiem 
		$oUser->addMoney($ValueFish,'SellFish') ;
		$oUser->addExp(ceil($expFish)) ;
		
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        // check if soldier, store back equipment
        if ($oFish->FishType == FishType::SOLDIER)
        {
              $oStore = Store::getById(Controller::$uId);
              // store back equipment 
              foreach($oStoreEquip->SoldierList[$FishId]['Equipment'] as $indexType => $listType)
                {
                    foreach($listType as $id => $oEquip)
                    {   
                        if( in_array($oEquip->Type, $arrQuartzType) ) {
                            $oStoreEquip->deleteQuartz($FishId,$oEquip->Type,$oEquip->Id);    
                            $oStore->addQuartz($oEquip->Type, $oEquip->Id, $oEquip);
                        } else {
                            $oStoreEquip->deleteEquipment($FishId,$oEquip->Type,$oEquip->Id);
                            $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);                            
                        }  
                    }
                }
              $oStore->save();    
        }
        $oStoreEquip->deleteSoldier($FishId);
 
		//save
		$oLake->delFish($FishId);

        $oSmashEgg = SmashEgg::getById(Controller::$uId);
        $oSmashEgg->removeSoldierSlot($FishId);
        $oSmashEgg->save();
        
        // xoa khoi viec trainning neu co
        $oTrain = TrainingGround::getById(Controller::$uId);
        $oTrain->sellSoldier($FishId);
        $oTrain->save();
        
		$oUser->save();
		$oLake->save();
        $oStoreEquip->save();
		//log
		Zf_log::write_act_log(Controller::$uId, 0, 30, 'sellFish', $ValueFish, 0,$FishId,$oFish->FishType);
        
		//return
		$arrResult = array() ;
		$arrResult['Money'] = $oUser->Money ;
        $arrResult['Exp'] = $oUser->Exp ;
		$arrResult['ExpFish'] = ceil($expFish);
		$arrResult['Error'] = Error :: SUCCESS ;
		return $arrResult ;
	}

	/**
	 * Mua ca giong
	 * @author AnhBV   , ToanTN Edited
	 * 3/2/2010
	 */

	public function buy($param)
	{
		$FishList = $param['FishList'];
		$LakeId = $param['LakeId'];

		$PriceType 	= $FishList[0]['PriceType'];
		$FishTypeId = $FishList[0]['FishType'];
		$Sex = $FishList[0]['Sex'];
		$Id = $FishList[0]['Id'];

		if (empty ($PriceType) || empty ($FishTypeId) || empty ($Id) || ($LakeId < 1) || ($LakeId > 3))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;

		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}

		$oLake = Lake :: getById(Controller :: $uId, $LakeId) ;
        
		if (!is_object($oLake))
        	return array('Error' => Error :: LAKE_INVALID) ;

		// kiem tra hop le cua dan ca


		$oId = $oUser->getAutoId() ;
        
		if ($Id != $oId)
		{
			return array('Error' => Error :: ID_INVALID) ;
		}
		if (!isset($Sex)) $Sex = 1 ;
        
		$fishConfig = Common :: getConfig('Fish',$FishTypeId) ;
        
		if (!is_array($fishConfig))
		{
			return array('Error' => Error ::NOT_LOAD_CONFIG) ;
		}

		// kiem tra xem level cua con ca va level cua user de xac dinh con
		// ca nao duoc mua theo dang unlock theo level
        
		if ($fishConfig['UnlockType'] == UnlockType::Level )
		{
			if ($oUser->Level < $fishConfig['LevelRequire'])
			{
				return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
			}
            $price = $fishConfig['ZMoney'];
            
            if($PriceType != Type::ZMoney)
            {
              $price = $fishConfig['Money'] ;
            }
		}
		else if ($fishConfig['UnlockType'] == UnlockType::Mix ) // unlock do lai or bang xu
		{
			// check this fish that is existed in unlock Mix or unlock ZXu ?

            $oUserProfile = UserProfile::getById(Controller::$uId);
            $unlock = $oUserProfile->checkUnlockFish($FishTypeId);
         
			if(!$unlock)
			 {
				if($PriceType != Type::ZMoney)
				{
				    return array('Error' => Error :: SLOT_NOT_UNLOCK) ;
				}
                else // unklock = xu
                {
                   // kiem tra xem max level ca lai 
                    if($fishConfig['LevelRequire'] > $oUserProfile->MaxFishLevelUnlock)
                    {
                        return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
                    }
                    $price = $fishConfig['ZMoneyUnlock'];     
                }
			 }
             else if($PriceType == Type::ZMoney)  // mua = xu ca unklock = xu
             {
                   $price = $fishConfig['ZMoney'];
             }
             else  $price = $fishConfig['Money'];
                   
		}
        
		//check Money of User
		if($PriceType == Type::ZMoney)
		{
			$info = $FishTypeId.':'.'Fish'.':1' ;
			if (!$oUser->addZingXu(-$price,$info))
			return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
            
            if(isset($unlock) &&  $unlock === false)  // unlock xu 
              {
                  $oUserProfile->updateFishUnlock($FishTypeId);
                  $oUserProfile->save();
                  $oUser->save();
                  $arrResult['Error'] = Error :: SUCCESS ;
                  $conf_log = Common::getConfig('LogConfig');
                  if(isset($conf_log['Fish']))
                  {
                    $TypeItemId = $conf_log['Fish'];
                  }
                  Zf_log::write_act_log(Controller::$uId,0,23,'unlockByXu',0,-$price,$TypeItemId,$FishTypeId, 0,0,1);
                  return $arrResult;
              } 
		}
		else
		{
			if (!$oUser->addMoney(-$price,'BuyFish'))
			return array('Error' => Error :: NOT_ENOUGH_MONEY) ;
		}
        
        if (!$oLake->isAddedFish(NULL))
            return array('Error' => Error :: LAKE_FULL) ;
		// new Fish
		$oFish = new Fish($Id, $FishTypeId,$Sex,ColorType::EMPTY_COLOR) ;
		$oLake->addFish($oFish);
        $oLake->save();
		
	
		// log
		$conf_log = Common::getConfig('LogConfig');
		if(isset($conf_log['Fish']))
		{
			$TypeItemId = $conf_log['Fish'];
		}
		if ($PriceType == 'Money')
			Zf_log::write_act_log(Controller::$uId,0,23,'buyFish',-$fishConfig['Money'],0,$TypeItemId,$FishTypeId, 0,0,1);
			else
			Zf_log::write_act_log(Controller::$uId,0,23,'buyFish',0,-$fishConfig['ZMoney'],$TypeItemId,$FishTypeId,0,0,1);
		
        $oUser->save() ;
		
		$arrResult['Error'] = Error :: SUCCESS ;

		return $arrResult ;
	}


	/**
   *
   * @param : FishList, LakeList, MaterialFish, MoneyPaid : boolean
   */


  function mate($param)
  {
    // Lay cac tham so can thiet   
    $FishList = $param['FishList'];
    $listMaterial = $param['MaterialList'];
    $fishId1 = intval($FishList[0]['Id']);
    $fishId2 = intval($FishList[1]['Id']);
    $lakeId1 = intval($FishList[0]['LakeId']);
    $lakeId2 = intval($FishList[1]['LakeId']);
    $skill = $param['Skill'];
    $Formula = $param['MixFormula'];
        
    // check tham so
    if (empty($fishId1) || empty($fishId2) || empty($lakeId1) || empty($lakeId2) 
      || ($fishId1 == $fishId2) || $lakeId1 >3 || $lakeId2 >3 )
      return array('Error' => Error :: PARAM) ;
        
    if(!empty($skill) && !Skill::check($skill))
      return array('Error' => Error :: PARAM) ; 

    if(!empty($Formula['Type']) && !empty($Formula['Id']) && !FormulaType::checkExist($Formula['Type'])) 
      return array('Error' => Error :: PARAM) ;
      

    $oUser = User :: getById(Controller::$uId) ;
    if (!is_object($oUser))
    return array('Error' => Error :: NO_REGIS) ;
      
    // luu thong so cu~
    $moneyDiff = $oUser->Money;
    $zMoneyDiff = $oUser->ZMoney;
    $expDiff = $oUser->Exp;

    //Lay du lieu cua ca va ho tuong ung va check tham so 
    $oLake1 = Lake::getById(Controller::$uId, $lakeId1);
        if(!is_object($oLake1))
            return array('Error' => Error :: PARAM) ;
    $oFish1 = $oLake1->getFish($fishId1);
        
    $oLake2 = Lake::getById(Controller::$uId, $lakeId2);
        if(!is_object($oLake2))
            return array('Error' => Error :: PARAM) ;
    $oFish2 = $oLake2->getFish($fishId2);

    if (!is_object($oFish1) || !is_object($oFish2))
      return array('Error' => Error :: FISH_NOT_EXITS) ;

    if ($oFish1->Sex == $oFish2->Sex)
      return array('Error' => Error :: THE_SAME_SEX) ;

    if (!$oFish1->canBirth() || !$oFish2->canBirth())
      return array('Error' => Error :: FISH_NO_BIRTH) ;
    
    $oStore = Store::getById(Controller::$uId);  
    
    if(empty($Formula['Type']))
    {
          
      // find min,max fish
      $minFish = ($oFish1->Level < $oFish2->Level) ? $oFish1 : $oFish2;
      $maxFish = ($oFish1->Level < $oFish2->Level) ? $oFish2 : $oFish1;
              
      // init Param,check & bonus Skill
      $userPro = UserProfile::getById(Controller::$uId);

      $bonus = array();
      $bonus['percentRare'] = 0;
      $bonus['percentSpecial'] = 0;
      $bonus['percentOverLevel'] = 0;
      $moneyNeed = 1 ;
          
      if(!empty($skill))
      {
        $effect = $oUser->getSkillEffect($skill);
        if(!$oUser->addEnergy(-$effect['Energy']))
         return array('Error' => Error :: NOT_ENOUGH_ENERGY) ;
        switch($skill)
          {
              case Skill::Money :
                      $moneyNeed = 1 - $effect['Buff'];
                      break;
              case Skill::Level :
                      $bonus['percentOverLevel'] += $effect['Buff']*100 ;
                      break;
              case Skill::Special :
                      $bonus['percentSpecial'] += $effect['Buff']*100 ;
                      break;
              case Skill::Rare :
                      $bonus['percentRare'] += $effect['Buff']*100 ;
                      break;
          }    
       }
        
      // kiem tra tien
     
      $levelMax = min($oUser->Level + 5, $minFish->Level + 2 );
      $levelMin = min($oUser->Level + 5, $minFish->Level );
      
      $MateFishCost = Common::getConfig('MateFishCost');
      $arrMoney = array();
      for ($i=$levelMin; $i<=$levelMax; $i++)
        $arrMoney[] = $MateFishCost[$i];
      $moneyNeed *= max($arrMoney);
      if($moneyNeed==0)
        $moneyNeed = $MateFishCost[count($MateFishCost)];

      if (!$oUser->addMoney(-round($moneyNeed),'MateFish'))
          return array('Error' => Error :: NOT_ENOUGH_MONEY) ;

      // check material
      
      $countNum = 0;
      if (is_array($listMaterial))
      $levelIndex = Fish::getLevelIndex($minFish->Level);
      $conf_mater = Common::getConfig('RateOfMaterial',$levelIndex);
             
      foreach ($listMaterial as $mater)
      {
        if (!(($mater['TypeId'] <=10 && $mater['TypeId']>=1)||($mater['TypeId'] <=110 && $mater['TypeId']>=101)))
            return array('Error' => Error :: PARAM) ; 

        $countNum += intval($mater['Num']);

        if (!$oStore->useItem('Material',$mater['TypeId'], intval($mater['Num'])))
          return array('Error' => Error :: NOT_ENOUGH_MATERIAL);

        // bonus material
        $bonus['percentSpecial'] += $conf_mater[$mater['TypeId']]['RateSpecial']*$mater['Num'];
        $bonus['percentRare'] += $conf_mater[$mater['TypeId']]['RateRare']*$mater['Num'];
        $bonus['percentOverLevel'] += $conf_mater[$mater['TypeId']]['RateOverLevel']*$mater['Num'];
        
        // param for Log
        $arr_material[$mater['TypeId']] = intval($mater['Num']) ;
      }
    
      // check enough slot unlock
      if ($countNum > $userPro->SlotUnlock)
      {
          return array('Error' => Error :: SLOT_NOT_UNLOCK) ;  
      }

      // perform action
      $NewFish = Fish::mateFish($oUser->Level,$minFish,$maxFish,$bonus);
      
      // fish attribute
      $result['Id'] = $oUser->getAutoId();
      $result['Sex'] = rand(0,1);
      $fishReturn = Fish::getIdByLevel($NewFish['Level']);
          
      $result['TypeId'] = $fishReturn['TypeId'];
      $result['Level'] = $fishReturn['Level'];
      $result['TypeFish'] = $NewFish['TypeFish'];
      

      $result['Color'] = rand(0,2); 
      
      // random option for rare fish and percent
      $result['RateOption'] = Fish::randOption($result['TypeFish'],$result['Level']);
      
      if(!empty($skill))
      {
        $skillBonus = $oUser->getSkillBonus($skill,$minFish->Level - $oUser->Level,$result['Level'] - $minFish->Level,$result['TypeFish']);
        $oUser->addExp($skillBonus['Exp']);   

        $oUser->bonusMastery($skill,$skillBonus['Mastery']);    
      }
        
      $userPro->updateMaxFishUnlock($result['Level']);
      $userPro->updateBirthFish($result['TypeId']);
      
      // update store

      if($result['TypeFish'] == FishType::NORMAL_FISH)
        {
            $oFish = new Fish($result['Id'],$result['TypeId'],$result['Sex'],$result['Color']);      
        }
        else if($result['TypeFish'] == FishType::SPECIAL_FISH)
        {
            $oFish = new SpecialFish($result['Id'],$result['TypeId'],$result['Sex'],$result['RateOption'],$result['Color']);
        }
        else if($result['TypeFish'] == FishType::RARE_FISH)
        {
            $oFish = new RareFish($result['Id'],$result['TypeId'],$result['Sex'],$result['RateOption'],$result['Color']);
        }

      $oStore->addFish($oFish->Id,$oFish);
      
      // tinh chenh lech
      $moneyDiff = $oUser->Money - $moneyDiff;
      $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
      $expDiff = $oUser->Exp - $expDiff;
      
      $userPro->save();   
      
      // return client
      $result['Exp'] = $oUser->Exp;
      $result['Skill'] = $oUser->Skill;
        
      // log
      Zf_log::write_act_log(Controller::$uId,0,30,'mate',$moneyDiff,0,$oUser->Level,$result['Level'], $result['TypeFish']+1,0,0);
      Zf_log::write_act_log(Controller::$uId,0,30,'use_Material_Mating',0,0,intval($arr_material[1]),intval($arr_material[2]),intval($arr_material[3]),intval($arr_material[4]),intval($arr_material[5]));
      
      $conf_log = Common::getConfig('LogConfig');
      $skillType = $conf_log[$skill];
      zf_log::write_act_log(Controller::$uId ,0, 30 ,'mixFishSkill',0,0 ,  intval($skillType) , intval($oUser->Skill[$skill]['Level']), intval($oUser->Skill[$skill]['Mastery']));   
    }
    else
    {   
        $Formula_Conf = Common::getConfig('MixFormula',$Formula['Type'],$Formula['Id']) ;
        if(!is_array($Formula_Conf)) 
          return array('Error' => Error :: NOT_LOAD_CONFIG) ; 
        
        if (!$oUser->addMoney(-round($Formula_Conf['MixPrice']),'MateFish'))
          return array('Error' => Error :: NOT_ENOUGH_MONEY) ; 
          
        if (!$oStore->useItem($Formula['Type'], $Formula['Id'], 1))
          return array('Error' => Error :: NOT_ENOUGH_FORMULA);
          
        // check cac dieu kien lai khac
        $Fish_Conf_1 = $Formula_Conf['Fish_1'];
        $Fish_Conf_2 = $Formula_Conf['Fish_2'];
        
        $checkCondition = Fish::checkConditionFish($oFish1,$oFish2,$Fish_Conf_1,$Fish_Conf_2);
        if( $checkCondition['Error'] !== Error::SUCCESS )
        {
            return $checkCondition['Error'];
        }
        $rand = mt_rand(1,100);
        if($rand > $Formula_Conf['SuccessPercent'] )
        {  
          $result['TypeId']= -1 ;      
        }
        else
        {
          $Formula_Conf = Common::getConfig(Type::MixFormula,$Formula['Type'],$Formula['Id']);
          $FishTypeId   = $Formula_Conf['FishTypeId'] ;     
          $oStore->createSoldierByRecipe($Formula['Type'],$Formula['Id'],SoldierType::MATE,$Num = 1) ; 
          $result['TypeId'] = $FishTypeId ;
        }
        if($oFish1->FishType!= FishType::NORMAL_FISH)
        {
            // tru option cua ho 
            $oLake1->buffToLake($oFish1->RateOption,False);
            $oFish1->resetOption();
        }
        if($oFish2->FishType!= FishType::NORMAL_FISH)
        {
            // tru option cua ho 
            $oLake2->buffToLake($oFish2->RateOption,False) ;
            $oFish2->resetOption();
        }
        // log
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        Zf_log::write_act_log(Controller::$uId,0,30,'mate',$moneyDiff,$zMoneyDiff,$oUser->Level);
    }
    
    $oFish1->birth();
    $oFish2->birth();
    $result['Error'] = Error::SUCCESS;        
    // save
    $oUser->save();
    $oStore->save();
    $oLake1->save();
    $oLake2->save();
    // return client
 
    return $result;


  }

  
	/**
	 * chuyen ho cho ca
	 * @author AnhBV  ToanTN edited
	 * 27/10/2010
	 */

	public function changeFish($param)
	{
		$FishId         = intval($param['FishId']);
		$FromLakeId      = intval($param['FromLakeId']);
		$ToLakeId       = intval($param['ToLakeId']);
    // kiem tra thong tin dau vao
    if ($ToLakeId < 1 || $ToLakeId > 3 || $FromLakeId < 1 ||
       $FromLakeId > 3 || $FishId < 1 || $FromLakeId == $ToLakeId )
    {
      return array('Error' => Error :: PARAM) ;
    }
		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}

		$oLakeOld = Lake::getById(Controller::$uId, $FromLakeId);
		$oFish = $oLakeOld->getFish($FishId);
		if (!is_object($oFish))
		{
			return array('Error' => Error :: FISH_NOT_EXITS) ;
		}
			
		$oLakeNew = Lake::getById(Controller::$uId, $ToLakeId);

		if (!$oLakeNew->isAddedFish($oFish))
		    return array('Error' => Error :: LAKE_FULL) ;

        if ($oFish->FishType == FishType::SOLDIER)  
        {
            if (!$oLakeNew->canSoldierIntoLake($oFish->Element))    
                return array('Error' => Error::NO_MORE_TWO_ELEMENTS);
        }
            
            
		$oLakeNew->addFish($oFish);
        
        $oLakeOld->delFish($FishId);

		$oLakeNew->save();
		$oLakeOld->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0, 20, 'changeFish',0,0,$FishId,$FromLakeId,$ToLakeId);
		return array('Error' => Error :: SUCCESS) ;
	}
  
  
	public function createGiftForFish($param)
	{
		$FishId		= $param['FishId'];
		$LakeId		= $param['LakeId'];

		if (empty ($FishId)||empty ($LakeId))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}

		$oLake = Lake::getById(Controller::$uId, $LakeId);
		$oFish = $oLake->getFish($FishId);
		if (!is_object($oFish))
		{
			return array('Error' => Error :: OBJECT_NULL) ;
		}
		if($oFish->FishType == FishType::NORMAL_FISH)
		{
			return array('Error' => Error ::TYPE_INVALID) ;
		}
		// kiem tra xem da du dieu kien nhan qua chua
		if(!$oFish->checkConditionCreateGift($oUser->Level))
		{
			return array('Error' => Error ::GOT_GIFT) ;
		}
		if (!empty($oFish->LevelUpGift))
		{
			return array('Error' => Error ::CREATED_GIFT_FOR_FISH) ;
		}

		//tao ra qua tang se nhan cho ca
		$arrResult = array();
		$arrResult['GiftList'] = $oFish->createLevelUpGift();
		$arrResult['Error'] = Error::SUCCESS;
		$oLake->save();

		return $arrResult ;
	}
	/**
	 * nhan qua khi ca len level
	 * @author AnhBV
	 * 2/3/2011
	 */
	public function getGiftOfFish($param)
	{

		$FishId = $param['FishId'];
		$LakeId = $param['LakeId'];

		if (empty ($FishId)||empty ($LakeId))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}


		$oLake = Lake::getById(Controller::$uId, $LakeId);
		$oFish = $oLake->getFish($FishId);
		if (!is_object($oFish))
		{
			return array('Error' => Error :: OBJECT_NULL) ;
		}
		if($oFish->FishType == FishType::NORMAL_FISH)
		{
			return array('Error' => Error ::TYPE_INVALID) ;
		}
		// kiem tra xem ca truong thanh chua
		if ($oFish->getGrowingPeriod() < FishPeriod::OVER_MATURE )
		{
			return array('Error' => Error ::GOT_GIFT);
		}
		// kiem tra xem da du dieu kien nhan qua chua
		if(!$oFish->checkConditionGetGift($oUser->Level))
		{
			return array('Error' => Error ::GOT_GIFT) ;
		}
		
		// thong so truoc khi thay doi
		$money = $oUser->Money ;
		$zmoney = $oUser->ZMoney ;
	
    $optExp[Type::Exp] = $oLake->getOption(Type::Exp);
          
    $arrGift = $oFish->getLevelUpGift($optExp,$oFish->FishTypeId);
    $oUser->saveBonus($arrGift);

		// update lai level va startime cho ca
		$oFish->updateLevelUpGift();

		// save
		$oLake->save();
		$oUser->save();
		
		// log
		
     	$difMoney = $oUser->Money - $money ;
    	$difzmoney = $oUser->ZMoney - $zmoney;
		Zf_log::write_act_log(Controller::$uId,0,30,'collectGiftOfFish',$difMoney,$difzmoney);
		
		// return
		return array('Error' => Error ::SUCCESS) ;

	}
       
    
    /**
  * use viagra
  * 
  */
  public function useViagra($param){
    $FishId         = intval($param['FishId']);
    $LakeId      = intval($param['LakeId']); 
    
    if(empty($FishId) || empty($LakeId))
      return array('Error' => Error::PARAM);
    
    if($FishId<=0 || $LakeId>3 || $LakeId<1)
      return array('Error' => Error::PARAM);
    
    $oLake = Lake::getById(Controller::$uId, $LakeId);    
    $oFish = $oLake->getFish($FishId);
    if (!is_object($oFish))
    {
      return array('Error'  =>  Error::OBJECT_NULL);
    }
    
    $oStore = Store::getById(Controller::$uId);
    if(!$oStore->useItem('Viagra',1,1))
      return array('Error'=>Error::NOT_ENOUGH_ITEM);
    
    if (date('dmY', $oFish->LastTimeViagra) != date('dmY', $_SERVER['REQUEST_TIME']))
    {
        $oFish->LastTimeViagra = 0;
        $oFish->ViagraUsed = 0;
    }
        

    if(!$oFish->useViagra())
        return array('Error'=>Error::CANT_USE_VIAGRA);
        
        
    $conf = Common::getParam();
    $oUser = User::getById(Controller::$uId);
    $oUser->addExp($conf['UseViagra']);

    
    $oLake->save();
    $oStore->save();
    $oUser->save();
    
    
    
    Zf_log::write_act_log(Controller::$uId,0,20,'useViagra',0,0,$FishId);
    
    
    
    return array('Error'=>Error::SUCCESS) ;
    
    
    
  }
  
  
   /**
   * Unlock slot mate
   * @author hieupt
   * 
   */
   public function unlockSlotMate($param)
   {
       
    if ($param['PriceType']== Type::Money)
    	$isMoney = true ;
	else
		$isMoney = false ;
		
    $oUser = User :: getById(Controller::$uId) ;
    if (!is_object($oUser))
    {
      return array('Error' => Error :: NO_REGIS) ;
    }

    $conf_slot = Common::getConfig('LevelUnlockSlot');
    if (!is_array($conf_slot)){
        return array('Error' => Error::NOT_LOAD_CONFIG);
    }
    $userPro = UserProfile::getById(Controller::$uId);
    
    $slotId = $userPro->SlotUnlock + 1;
    if (!isset($conf_slot[$slotId])){
        return array('Error' => Error::SLOT_ERROR);
    }

    $arrResult = array();
    
    if ($oUser->Level < $conf_slot[$slotId]['LevelRequire']){
        return array('Error' => Error::NOT_ENOUGH_LEVEL);
    }
    
    // thong so truoc khi thay doi
	$money = $oUser->Money ;
	$zmoney = $oUser->ZMoney ;

    if ($isMoney==true)
    {
      if (!$oUser->addMoney(-$conf_slot[$slotId]['Money'],'unlockSlotMate'))
        return array('Error' => Error::NOT_ENOUGH_MONEY);      
    } 
    else 
    {
        $info = $slotId.':UnlockSlot'.':1' ;
        if (!$oUser->addZingXu(-$conf_slot[$slotId]['ZMoney'],$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        $oUser->addExp($conf_slot[$slotId]['Exp']); 
        $arrResult['Exp'] = $conf_slot[$slotId]['Exp'];
    }
    
    $userPro->updateSlotUnlock();
    $oUser->save();
    $userPro->save();
    
    //log
    $difMoney = $oUser->Money - $money ;
    $difzmoney = $oUser->ZMoney - $zmoney;
    Zf_log::write_act_log(Controller::$uId,0,23,'unlockSlotMate',$difMoney,$difzmoney,$slotId);
    
    $arrResult['Error'] = Error::SUCCESS;
    
    return $arrResult;
    
  }
  
  
  
  /**
  * Fish Machine
  * @author hieupt
  * 
  */
  
/*  public function exchangeFairyDrop($param)
  {
      $fishId = $param['FishId'];
      $lakeId = $param['LakeId'];
      
      if (empty($fishId) || empty($lakeId) || !($lakeId>=1 && $lakeId<=2))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$lakeId);
      if(!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
        
      $oUser = User::getById(Controller::$uId); 
      if ($oUser->Level < Common::getParam('MinLevelFairyDrop'))
        return array('Error' => Error::NOT_ENOUGH_LEVEL);
        
      $oFish = $oLake->getFish($fishId);
      if (!$oFish)
        return array('Error' => Error::FISH_NOT_EXITS);
        
      if ($oFish->FishType==0)
        return array('Error' => Error::FISH_TYPE);
        
      if (!$oFish->isGrowth())
        return array('Error' => Error::FISH_MATURE);
      
      // exchange
      $conf_rate = Common::getConfig('FairyDropLevel');
      $conf_point = Common::getConfig('FairyDropPoint');
      $conf_fish = Common::getConfig('Fish');
      $numPoint = 0;
      foreach($oFish->RateOption as $sType => $value)
      {
          $numPoint += $conf_point[$value][$sType];
      }
      
      $oUser = User::getById(Controller::$uId);
      $total = $conf_rate[$conf_fish[$oFish->FishTypeId]['LevelRequire']]*$numPoint;
      $numPointMin = round($total);
      $numPointMax = ceil((Common::getParam('PercentLuckyFM')+100)*$total/100);
      $numPoint = rand(0,1) ? $numPointMax : $numPointMin;
      $oUser->FairyDrop += $numPoint;
      
      
      // delete fish
      $oLake->buffToLake($oFish->RateOption,false);
        
      // delele buff
      unset($oLake->FishList[$fishId]);
      
      $oLake->save();
      $oUser->save();
      
      return array('Error' => Error::SUCCESS, 'FairyDrop' => $numPoint);
          
  }*/
  
  /**
  * Take Soldier attack friend
  * @author hieupt
  * 21/07/2011
  */
  
  public function attackFriendLake($param)
  {
      $FishId = $param['FishId'];
      $SoldierLakeId = $param['SoldierLakeId'];
      $FriendId = $param['FriendId'];
      $FriendLakeId = $param['FriendLakeId'];
      $VictimId = $param['VictimId'];
      $ItemList = $param['ItemList'];
      
      // check condition
      if (empty($FishId) || empty($SoldierLakeId) || empty($FriendId) || empty($FriendLakeId))
        return array('Error' => Error::PARAM);
       
      $oSLake = Lake::getById(Controller::$uId,$SoldierLakeId);
      if (empty($oSLake))
        return array('Error' => Error::PARAM);

      $oSoldier = $oSLake->getFish($FishId);
      if (!$oSoldier)
        return array('Error' => Error::OBJECT_NULL);
      if ($oSoldier->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);
        
      if ($oSoldier->Status!=SoldierStatus::HEALTHY)
        return array('Error' => Error::SOLDIER_EXPIRED);
      
      // get list user's soldier before attack, return client for synchronize
      // use clone for static function
      $mSoldier = Lake::getAllSoldier(Controller::$uId,false,true,true); 
      foreach($mSoldier as $idLake => $oLake)
      {
            foreach($oLake as $idFish => $oFish)    
            {
                $mSoldier2[$idLake][$idFish] = clone $oFish;
            }
      }

      // check enough item
      $oStore = Store::getById(Controller::$uId);
      foreach($ItemList as $id => $oItem)
      {
        if (!$oStore->useBuffItem($oItem[Type::ItemType],$oItem[Type::ItemId],$oItem[Type::Num]))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
      }   

      if (!SoldierFish::checkValidItemList($ItemList))
        return array('Error' => Error::ID_INVALID);
  
      $oUser = User::getById(Controller::$uId);
      if (!$oUser->isFriend($FriendId))
        return array('Error' => Error::NOT_FRIEND);
        
      $oFriendLake = Lake::getById($FriendId,$FriendLakeId);
      if (!is_object($oFriendLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $myUserPro = UserProfile::getById(Controller::$uId);  
      $oFriendUser = User::getById($FriendId);  
      $conf_maxTimes = Common::getConfig('Param','MaxTimesAttackLake')*$oFriendUser->LakeNumb;
          
      // reset attack time in new day
      if (date('Ymd',$_SERVER['REQUEST_TIME'])!=date('Ymd',$myUserPro->Attack['LastTimeAttack']))
      {
        $myUserPro->Attack['FriendLake'] = array();
        $myUserPro->BonusAttackTime = array();
      }  
 
      // check max times attack 
      if ($myUserPro->Attack['FriendLake'][$FriendId] >= ($conf_maxTimes + $myUserPro->BonusAttackTime[$FriendId]))    
        return array('Error' => Error::ACTION_NOT_AVAILABLE);  
 
      // check enough mana vs energy ???
      $conf_rank = Common::getConfig('RankPoint'); 
      $eneryLost = $conf_rank[$oSoldier->Rank]['AttackEnergy'];
      if (!$oUser->addEnergy(-$eneryLost))
        return array('Error' => Error::NOT_ENOUGH_ENERGY);
      
      $conf_defence = Common::getConfig('DefenceSoldier');
       
      // check enough health attack     
      if (!$oSoldier->addHealth(-$conf_rank[$oSoldier->Rank]['AttackPoint']))
        return array('Error' => Error::NOT_ENOUGH_HEALTH);  

      // select soldier to attack vs clone friend's soldier list for client  
      $fSoldier = Lake::selectSoldier($FriendId);
      $ffSsoldier = Lake::getAllSoldier($FriendId,false,true,true);
      foreach($ffSsoldier as $idLake => $oLake)
      {
            foreach($oLake as $idFish => $oFish)    
            {
                $fbeforeSoldier[$idLake][$idFish] = clone $oFish;
            }
      }
                                                                         
      // take attack and create bonus  
      $bonusMe = array();
      if (!$fSoldier['Soldier']) // friend's Soldier doesnt exist
      {   
          // select fish to attack
          $attackFish = $oFriendLake->getFish($VictimId);
          if (!is_object($attackFish))
            return array('Error' => Error::OBJECT_NULL);
          if (!in_array($attackFish->FishType,array(0,1,2))){
              if ($attackFish->Status!=SoldierStatus::HEALTHY)
                return array('Error' => Error::SOLDIER_FRIEND_EXPIRED);
              else 
                return array('Error' => Error::NOT_ENOUGH_FRIEND_HEALTH);      
          }
           
          // get bonus from attack this fish
          $bonusMe = $attackFish->beingAttacked($oSoldier);
          $attackSuccess = 1;
      }
      else  // friend's Soldier exist 
      {
          // calculate money can get
          $moneyCanGet = $oFriendLake->getMoneyAttack($oSoldier);
          if ($oUser->Money < $moneyCanGet)
                return array('Error' => Error::NOT_ENOUGH_MONEY); 
          $friendSoldier = $fSoldier['Soldier'];

          // check friend health
          if ($friendSoldier->getCurrentHealth() < $conf_rank[$friendSoldier->Rank]['AttackPoint'])
            return array('Error' => Error::NOT_ENOUGH_FRIEND_HEALTH);
          
          // battle  
          $resultBattle = SoldierFish::takeAttack($oSoldier,$friendSoldier, $oSoldier->BuffItem);
          $attackSuccess = $resultBattle['Result'];
         
          // log for diary          
          $oDiary = array();
          $oDiary['IsWin'] = $attackSuccess;
          $oDiary['Attacker'] = Controller::$uId;
          $oDiary['Time'] = $_SERVER['REQUEST_TIME'];
          
          $param_rank = Common::getParam('RankPoint');    
          if (!$attackSuccess) // if attack failed 
          {
                // calculate rank user lost, friend get
                $indexRank = SoldierFish::calculateIndexRank($oSoldier->Rank,$friendSoldier->Rank); 
                if (!$oSoldier->addHealth(-$conf_rank[$oSoldier->Rank]['AttackPoint']))
                    return array('Error' => Error::NOT_ENOUGH_HEALTH);
               
                // decrease money by amount can get    
                $oUser->addMoney(-$moneyCanGet,'attackFriendLake');             

                // decrease rankpoint if doesnt exist StoreRank item
                if(!SoldierFish::checkExistItem($oSoldier->BuffItem,BuffItem::StoreRank,1))
                {
                    $rankLost = $param_rank[$indexRank];
                    $oSoldier->addRankPoint(-$rankLost);
                }
              
                // bonus Friend    
                // add rank to bonus friend pack        
                $bonusFriend = array();
                if ($friendSoldier->SoldierType == SoldierType::MATE) 
                {
                    $indexRank = SoldierFish::calculateIndexRank($oSoldier->Rank,$friendSoldier->Rank); 
                    $bonusFriend[0][Type::ItemType] = Type::Rank;
                    $bonusFriend[0][Type::ItemId] = 1;
                    $bonusFriend[0][Type::Num] = $param_rank[$indexRank];    
                    $oDiary['RankPoint'] = $param_rank[$indexRank];    
                }
                
                $cBonus = count($bonusFriend);
                // get exp vs money of loose fish
                $bonusFriend[$cBonus][Type::ItemType] = Type::Exp;
                $bonusFriend[$cBonus][Type::ItemId] = 1;
                $bonusFriend[$cBonus][Type::Num] = $conf_defence[$friendSoldier->Rank][Type::Exp];
                $oDiary['Exp'] = $conf_defence[$friendSoldier->Rank][Type::Exp];
              
                // check max time bonus money
                $maxBonusMoney = $conf_defence[$friendSoldier->Rank]['LimitMoney'];
                if ($friendSoldier->numBonusMoney < $maxBonusMoney)
                {
                    $bonusFriend[++$cBonus][Type::ItemType] = Type::Money;
                    $bonusFriend[$cBonus][Type::ItemId] = 1;
                    $bonusFriend[$cBonus][Type::Num] = $moneyCanGet;
                    $oDiary['Money'] = $moneyCanGet;
                    $friendSoldier->numBonusMoney++;                   
                }
                
                // get qua cua Event 
                //$oEvent = Event::getById(Controller::$uId);
                //$Gift = $oEvent->island_getGiftInEvent('Friend','Lose',1,1);
                $Gift = Event::getActionGiftInEvent(EventType::EventActive,'Friend','Lose');  
                
                 if(!empty($Gift))
                $bonusMe = array_merge($bonusMe, $Gift);
                               
          }
          else // if attack success
          {
                              
            // calculate rank can get
            $indexRank = SoldierFish::calculateIndexRank($friendSoldier->Rank,$oSoldier->Rank);  
            //$friendSoldier->addHealth(-$conf_rank[$friendSoldier->Rank]['AttackPoint']);

            // calculate rank friend lost
            $indexRankFriend = SoldierFish::calculateIndexRank($friendSoldier->Rank,$oSoldier->Rank);  

            // check protected status of friend's soldier, if on: do not decrease
            $conf_protect = Common::getConfig('Param','ProtetedRank');
            if (in_array($friendSoldier->Rank,$conf_protect))
                $protectStatus = true;
            else { // if higher rank
                if ($friendSoldier->NumDefendFail >= $conf_rank[$friendSoldier->Rank]['TurnDefend'])
                    $protectStatus = true;  
                else $protectStatus = false;    
            }

            if (!$protectStatus)
            {
                $friendSoldier->addRankPoint(-$param_rank[$indexRankFriend]);
                $oDiary['RankPoint'] = -$param_rank[$indexRankFriend];    
            }

            // add rankpoint to soldier, include bonus from item
            if ($oSoldier->SoldierType == SoldierType::MATE)
            {
                $rank = array();
                $rank[0][Type::ItemType] = Type::Rank;
                $rank[0][Type::ItemId] = 1;
                $rank[0][Type::Num] = $param_rank[$indexRank];

                $rank_cal = $this->calculateBuffItem($rank,$ItemList);
                $oSoldier->addRankPoint($rank_cal[0][Type::Num]);                     
            }

            // bonus attack lake
            $lakeValue = $oFriendLake->takeAttackLake($oSoldier);
            $bonusMe = SoldierFish::bonusWinner($lakeValue, $oSoldier->BuffItem, $oSoldier, $friendSoldier);

            //$oUser->saveBonus($bonusMe);             
            $countBonusMe = count($bonusMe);
            if ($rank[0][Type::Num]>0)  
                $bonusMe[$countBonusMe] = $rank[0];

            // get qua cua Event                        
            //$oEvent = Event::getById(Controller::$uId);
            //$Gift = $oEvent->island_getGiftInEvent('Friend','Win',1,1);
            $Gift = Event::getActionGiftInEvent(EventType::EventActive,'Friend','Win');              
            if(!empty($Gift))
                $bonusMe = array_merge($bonusMe, $Gift);
            
            //---------
          }
      }
          
      // exp
      $countBonusMe = count($bonusMe);
      $bonusMe[$countBonusMe][Type::ItemType] = Type::Exp;
      $bonusMe[$countBonusMe][Type::ItemId] = 1;
      
      if ($attackSuccess)
        $bonusMe[$countBonusMe][Type::Num] = 2*$eneryLost;
      else $bonusMe[$countBonusMe][Type::Num] = $eneryLost;
  
      // recalculate bonus depend on buff items
      $bonusMe  = $this->calculateBuffItem($bonusMe,$ItemList);
 
      // update avatar for me vs friend
      if (!$friendSoldier || $attackSuccess)
        $ava = Battle::WIN;
      else $ava = Battle::LOSE;
      $ava++;
      
      $myUserPro->Avatar[$FriendId] = $ava;
      $myUserPro->LastUpdateAvatar = $_SERVER['REQUEST_TIME'];
      $myUserPro->save();

      $friendUserPro = UserProfile::getById($FriendId);
      $friendUserPro->Avatar[Controller::$uId] = Battle::LOSE+Battle::WIN+2-$ava;
      $friendUserPro->LastUpdateAvatar = $_SERVER['REQUEST_TIME']; 
      $friendUserPro->save();
  
      // decrease buffItem's turn
      foreach($oSoldier->BuffItem as $id => $oBuff)
      {
          if ($oBuff['Turn']<=1)
            unset($oSoldier->BuffItem[$id]);
          else $oSoldier->BuffItem[$id]['Turn']--;
      }
      
      // update turn of gem
      $oSoldier->updateGemAfterBattle();
     
      // update max times     
      $myUserPro->Attack['LastTimeAttack'] = $_SERVER['REQUEST_TIME'];
      $myUserPro->Attack['FriendLake'][$FriendId]++;
      
      //update Battle Statistic
      if ($friendSoldier)
      {
          $oUserPro = UserProfile::getById(Controller::$uId);
          
          if (!isset($oUser->BattleStat['FirstTimeAttack']))
            $oUser->BattleStat['FirstTimeAttack'] = $_SERVER['REQUEST_TIME'];
          if ($attackSuccess)  // if attack success
          {
              $oUser->BattleStat['Win'] += 1;
             
              $rateAve = $resultBattle['Ratewin'];
              $toTalBattle = $oUser->BattleStat['Win']+$oUser->BattleStat['Lose'];
              $oUser->BattleStat['AverageWinRate'] = ($oUser->BattleStat['AverageWinRate']*$toTalBattle+$rateAve)/($toTalBattle+1);

              // Item Collection 
              $conf_ele = Common::getParam('Elements');   
              $conf_WeaponCollection = Common::getConfig('General','ItemCollection');
              
              if ($conf_ele['Conflict'][$friendSoldier->Element]==$oSoldier->Element)
                $conf_WeaponCollection = $conf_WeaponCollection['Conflict'];
              else $conf_WeaponCollection = $conf_WeaponCollection['NoConflict'];
             
              $idItem = Common::randomIndex($conf_WeaponCollection);
              if (!($idItem == Type::Nothing))
              {
                  $bonusMe[++$countBonusMe][Type::ItemType] = Type::ItemCollection;
                  $bonusMe[$countBonusMe][Type::ItemId] = $idItem;
                  $bonusMe[$countBonusMe][Type::Num] = 1;   
              }  
              
              // update last time friend's soldier lose
              if (date('dmY', $friendSoldier->LastTimeDefendFail) != date('dmY', $_SERVER['REQUEST_TIME']))
              {
                  $friendSoldier->NumDefendFail = 0;
              }
              $friendSoldier->LastTimeDefendFail = $_SERVER['REQUEST_TIME'];
              $friendSoldier->NumDefendFail++;
          }            
          else // attack fail
          {
            $oUser->BattleStat['Lose'] += 1;                   
          }
       
          //$friendSoldier->updateDurability();
          $oUserPro->save();
      }
      
      // update equipment durability
      $oSoldier->updateDurability();
     
      // save bonus
      $oUser->saveBonus($bonusMe);
      $oUser->save();
    
      $oSLake->FishList[$FishId] = $oSoldier;
      $oSLake->save();
      $oStore->save();
      $oFriendLake->save();
      $myUserPro->save(); 
      
      $rankFriendSoldier = -1; 
      if ($fSoldier['Soldier'])     // if exist friend soldier 
      {
          // update gem vs buffitem
            $friendSoldier->updateGemAfterBattle();
            foreach($friendSoldier->BuffItem as $id => $oBuff)
            {
                  if ($oBuff['Turn']<=1)
                    unset($friendSoldier->BuffItem[$id]);
                  else $friendSoldier->BuffItem[$id]['Turn']--;
            }
            
            $friendSoldier->addBonus($bonusFriend);
            $friendSoldier->addDiary($oDiary);
            $friendSoldierLake = Lake::getById($FriendId, $fSoldier['LakeId']);
            $friendSoldierLake->FishList[$fSoldier['SoldierId']] = $friendSoldier;
            $friendSoldierLake->save();  
            $rankFriendSoldier = $friendSoldier->Rank; 

      }

      // result
      $arrResult = array();  
      $arrResult['Error'] = Error::SUCCESS;
      $arrResult['Bonus'] = $bonusMe;
      $arrResult['isWin'] = intval($attackSuccess);
      $arrResult['Penalty'] = array();
      $arrResult['Penalty']['MoneyGet'] = $moneyCanGet;
      $arrResult['Penalty']['RankLost'] = $rankLost;
      $arrResult['Scene'] = $resultBattle['Scene'];
      $arrResult['FriendSoldier'] = $fbeforeSoldier;
      $arrResult['MySoldier'] = $mSoldier2;
      $arrResult['DefenceSoldier'] = $friendSoldier->Id;
      
      $arrResult['MyEquipment'] = StoreEquipment::getById(Controller::$uId); 
      $arrResult['FriendEquipment'] = StoreEquipment::getById($FriendId); 
      $arrResult['RankFriendSoldier'] = $rankFriendSoldier; 
      
      return $arrResult;
  }
 
  /**
  * Tinh lai exp, money, rank ... phan thuong khi tan cong
  * @author hieupt
  * 15/08/2011
  */
 
  private static function calculateBuffItem($bonus, $ItemList)
  {
      $increaseBonus = array(BuffItem::BuffExp => Type::Exp,BuffItem::BuffMoney => Type::Money,BuffItem::BuffRank => Type::Rank);
      
      foreach($bonus as $idb => $oBonus)
      {
            foreach($ItemList as $idItem => $oItem)
            {
                if ($increaseBonus[$oItem[Type::ItemType]]==$oBonus[Type::ItemType])
                {
                    $conf_buff = Common::getConfig('BuffItem',$oItem[Type::ItemType]);
                    $per = $conf_buff[1]['Num']*$oItem[Type::Num];
                    $bonus[$idb][Type::Num] += ceil($per*$bonus[$idb][Type::Num]/100);
                }
            }
      }
         

      return $bonus;
  }
  
  /**
  * Nhan qua ca chien binh
  * @author hieupt
  * 29/07/2011
  */
  
  public function getBonusSoldier($param)
  {
      $LakeId = $param['LakeId'];
      $FishId = $param['FishId'];
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
        
      $oSoldier = $oLake->getFish($FishId);
      if (!is_object($oSoldier) || ($oSoldier->FishType!=FishType::SOLDIER))
        return array('Error' => Error::ID_INVALID);
        
      $oUser = User::getById(Controller::$uId);   
      $bn = $oSoldier->Bonus;
      
      $conf_defence = Common::getConfig('DefenceSoldier',$oSoldier->Rank);
      $list_limit = array(Type::Rank,Type::Exp);
      foreach($bn as $id => $oBonus)  
      {
          if (in_array($oBonus[Type::ItemType],$list_limit))
          {
              if ($oBonus[Type::Num] > $conf_defence['Limit'.$oBonus[Type::ItemType]])
                $oBonus[Type::Num] = $conf_defence['Limit'.$oBonus[Type::ItemType]];
          }
          
          if ($oBonus[Type::ItemType] == Type::Rank)
            $oSoldier->addRankPoint($oBonus[Type::Num]);
          else $oUser->saveBonus(array($oBonus));
      }
        
      $oSoldier->Bonus = array();
      $oSoldier->Diary = array();
      $oSoldier->numBonusMoney = 0;
      
      $oUser->save();
      $oLake->save();
      
      return array('Error' => Error::SUCCESS);
  }
  
  /**
  * Len level ca chien binh
  * @author hieupt
  * 29/07/2011
  */
  public function levelUpSoldier($param)
  {
      $LakeId = intval($param['LakeId']);
      $FishId = intval($param['FishId']);
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
        
      $oSoldier = $oLake->getFish($FishId);     
      if (!is_object($oSoldier) || ($oSoldier->FishType!=FishType::SOLDIER))
        return array('Error' => Error::ID_INVALID);
      
      if (!$oSoldier->promo())
        return array('Error' => Error::NOT_ENOUGH_LEVEL);
      
      $oLake->save();
      return array('Error' => Error::SUCCESS);
      
  }
  
  /**
  * Hoi phuc suc khoe ca linh
  * @author hieupt
  * 02/08/2011
  */
  
  
  public function recoverHealthSoldier($param)
  {
      $LakeId = intval($param['LakeId']);
      $FishId = intval($param['FishId']);
      $IdItem = intval($param['ItemId']);
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $oSolider = $oLake->getFish($FishId);
      if (!$oSolider)
        return array('Error' => Error::OBJECT_NULL);
        
      if ($oSolider->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);
      
      $oStore = Store::getById(Controller::$uId);
      if (!$oStore->useBuffItem(Type::RecoverHealthSoldier,$IdItem,1))  
        return array('Error' => Error::NOT_ENOUGH_ITEM);
        
      $conf_health = Common::getConfig('RecoverHealthSoldier', $IdItem);
      if (!is_array($conf_health))
        return array('Error' => Error::OBJECT_NULL);
      
      if(!$oSolider->addHealth($conf_health['Num']))
        return array('Error' => Error::CANT_RECOVER);
        
      $oLake->save();
      $oStore->save();
      return array('Error' => Error::SUCCESS);
  }
  
  
  /**
  * Hoi sinh ca chien binh  
  * @author hieupt
  * 29/07/2011
  */
  
  public function rebornSoldier($param)
  {
      $LakeId = $param['LakeId'];
      $FishId = $param['FishId'];
      $ItemId = $param['GinsengId'];
      $isGrave = $param['isGrave'];
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $oStore = Store::getById(Controller::$uId) ;
      if (!$oStore->useBuffItem('Ginseng',$ItemId,1))
        return array('Error' => Error::NOT_ENOUGH_ITEM);
      
      $conf_gin = Common::getConfig('Ginseng', $ItemId);
      if (!is_array($conf_gin))
        return array('Error' => Error::OBJECT_NULL);
    
      if ($isGrave)  
      {
          $oSolider = $oLake->Grave[$FishId];
          if (!$oSolider)
            return array('Error' => Error::OBJECT_NULL);
          if (!in_array($oSolider->Rank, $conf_gin['Rank']))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
          $oSolider->Status = SoldierStatus::CLINICAL;
          unset($oLake->Grave[$FishId]);
          
          if (!$oSolider->reborn($conf_gin['Expired']))
            return array('Error' => Error::CANT_REBORN); 

          if (!$oLake->canSoldierIntoLake($oSolider->Element))
              return array('Error' => Error::NO_MORE_TWO_ELEMENTS); 
               
          $oLake->addFish($oSolider);
          $conf_maxSoldier = Common::getParam('MaxSoldier');
          if ($oLake->getSoldierCount() > $conf_maxSoldier)
            return array('Error' => Error::LAKE_FULL);           
      }
      else
      {
          $oSolider = $oLake->getFish($FishId);
          if (!$oSolider)
            return array('Error' => Error::OBJECT_NULL);
            
          if ($oSolider->FishType!=FishType::SOLDIER)
            return array('Error' => Error::ID_INVALID);
          
          if (!in_array($oSolider->Rank, $conf_gin['Rank']))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
          
          if (!$oSolider->reborn($conf_gin['Expired']))
            return array('Error' => Error::CANT_REBORN);    
      }
      
         
      $oLake->save();
	  $oStore->save();
      
      //log
      Zf_log::write_act_log(Controller::$uId,0,20,'rebornSoldier',0,0,$FishId,$ItemId);
      
      return array('Error' => Error::SUCCESS);
      
      
  }
  
  /**
  * Cap nhat ca chien binh het han
  * @author hieupt
  * 02/08/2011
  */
 /* 
  public function updateExpiredSolider($param)
  {
      $LakeId = $param['LakeId'];
      $FishId = $param['FishId'];
      $UserId = $param['UserId'];
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      if (empty($UserId))        
        $UserId = Controller::$uId;
        
      $oLake = Lake::getById($UserId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $oSolider = $oLake->getFish($FishId);
      if (!$oSolider)
        return array('Error' => Error::OBJECT_NULL);
        
      if ($oSolider->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);
      
      $status = $oSolider->updateStatus();
      
      
      if ($status == SoldierStatus::DIED)
      {
          $oStore = Store::getById($UserId);
          // store back equipment 
          foreach($oSolider->Equipment as $indexType => $listType)
            {
                foreach($listType as $id => $oEquip)
                {
                    //if ($oEquip->isExpired())
                    //{
                        $oSolider->deleteEquipment($oEquip->Type,$oEquip->Id);
                        $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);
                    //}
                }
            }
          $oStore->save();  
            
          $oLake->Grave[$FishId] = $oSolider;
          $oLake->delFish($FishId);
      }
      
      $oLake->save();
      
      return array('Error' => Error::SUCCESS, 'Status' => $status);
  }
  */
  
  /**
  * Click ngoi mo de nhan qua
  * 08/08/2011
  * @author hieupt
  */
  
  
  public function clickGrave($param)
  {
      $FishId = $param['FishId'];
      $LakeId = $param['LakeId'];
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);

      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);

      if (!isset($oLake->Grave[$FishId]))
        return array('Error' => Error::OBJECT_NULL);
             
      $oSolider = $oLake->Grave[$FishId];
      $bonus = $oSolider->getBonusDied();
      $oUser = User::getById(Controller::$uId);
      $oUser->saveBonus($bonus);
      $oUser->save();
      
      unset($oLake->Grave[$FishId]);
      $oLake->save();
 
      
      return array('Error' => Error::SUCCESS, 'Bonus' => $bonus);
  }
  
  /**
  * Hoa sinh cho ca
  * @author hieupt
  * 29/07/2011
  */
  
  
  public function beingBiochemical($param)
  {
      $LakeId = $param['LakeId'];
      $FishId = $param['FishId'];
      
      if (empty($LakeId) || empty($FishId))
        return array('Error' => Error::PARAM);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);
      
      $oSolider = $oLake->getFish($FishId);
      if (!$oSolider)
        return array('Error' => Error::OBJECT_NULL);
        
      if ($oSolider->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);
      
      $conf_bio = Common::getConfig('General','SoldierType',$oSolider->SoldierType);
      if (!$conf_bio['biochemical'])  
        return array('Error' => Error::ID_INVALID);
      else
        $recipe = $oSolider->getRecipe();
      
      // delete fish
      unset($oLake->FishList[$FishId]);
      $oUser = User::getById(Controller::$uId);
      $oUser->saveBonus(array($recipe));
      
      $oLake->save();
      $oUser->save();

      return array('Error' => Error::SUCCESS, 'Recipe' => $recipe);
  }
  
  /**
  * Mua cac loai ca dac biet trong shop
  * @author hieupt
  * 08/08/2011
  */
  public function buySpecialFish($param)
  {
      $ItemType = $param['ItemType'];
      $ItemId   = intval($param['ItemId']);
      $isMoney = $param['isMoney'];
      
      if (empty($ItemType))
        return array('Error' => Error::PARAM);
       
      $oUser = User::getById(Controller::$uId);
      if(!is_object($oUser))
        return array('Error' => Error::NO_REGIS);    
            
      // kiem tra xem loai can mua la loai gi
      
      // loai ca linh 
      if($ItemType == FormulaType::Rent)
      {                  
        $conf = Common::getConfig(Type::MixFormula,'Rent',$ItemId);
      }
      else if (in_array($ItemType,array(Type::Sparta,Type::Swat,Type::Spiderman,Type::Batman,Type::Superman),true))
      {
        $conf = Common::getConfig('SuperFish',$ItemType); 
      }
      else 
      {
         return array('Error' => Error::TYPE_INVALID); 
      }
      
      if(empty($conf))
          return array('Error' => Error::NOT_LOAD_CONFIG);
          
      // check level 
      if($oUser->Level < $conf['LevelRequire'] )
        return array('Error' => Error::NOT_ENOUGH_LEVEL);     
      
      // check unlock type
      if($conf['LevelRequire'] == 5 || $conf['LevelRequire'] == 6 )
        return array('Error' => Error::TYPE_INVALID);
      
      // thong so truoc khi thay doi
      $zmoney = $oUser->ZMoney ;
      $money =  $oUser->Money ; 
              
      // check money
      if ($isMoney)
      {
          if (!$oUser->addMoney(-$conf['Money'],'buySpecialFish'))
            return array('Error' => Error::NOT_ENOUGH_MONEY);
      }
      else
      {
          $info = '1:'.'BuySpecialFish'.':1' ;
          if (!$oUser->addZingXu(-$conf['ZMoney'],$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
      }
      
      
      // thuc hien add vao kho
      $oStore = Store::getById(Controller::$uId);
      // loai ca linh 
      if($ItemType == FormulaType::Rent)
      {                  
        $oStore->createSoldierByRecipe($ItemType,$ItemId,SoldierType::BUYSHOP,$Num = 1); 
      }
      else if (in_array($ItemType,array(Type::Sparta,Type::Swat,Type::Spiderman,Type::Batman,Type::Superman),true))
      {
        // check so lan mua trong ngay 
        $oUserPro = UserProfile::getById(Controller::$uId);    
        if(!$oUserPro->updateActionTimes('BuySuperFishTime',$ItemType,1))
        {
             return array('Error' => Error::NOT_ACTION_MORE);      
        }
        $autoId = $oUser->getAutoId() ;
        $objectFish = new Sparta($autoId,$conf['Option'],$conf['Expired'],$ItemType);
        $oStore->addOther($ItemType,$objectFish->Id,$objectFish); 
        $oUserPro->save();
      }
      
      // cong exp neu co 
      $oUser->addExp($conf['Exp']);     

      $oStore->save();  
      $oUser->save();
      //log
      // thong so sau khi thay doi
       $difzmoney = $oUser->ZMoney - $zmoney;
       $difmoney = $oUser->Money - $money;
       Zf_log::write_act_log(Controller::$uId,0,23,'buySpecialFish',$difmoney,$difzmoney,$ItemType,intval($objectFish->Id));
      return array('Error' => Error::SUCCESS);
  }
  
  
    
  /**
  * Get gift of sparta family
  * @author hieupt
  * 24/08/2011
  */
  public function getSpartaGift($param)
  {
      $FishId = intval($param['FishId']);
      $LakeId = intval($param['LakeId']);
      $FishType = $param['FishType'];
      
      if (empty($FishId) || empty($LakeId))                        
        return array('Error' => Error::PARAM);
        
      $oDeco = Decoration::getById(Controller::$uId,$LakeId);
      if (!is_object($oDeco))
        return array('Error' => Error::OBJECT_NULL);
        
      $spartaF = Common::getParam('SpartaFamily');
      if (!in_array($FishType,$spartaF))
        return array('Error' => Error::ID_INVALID);
        
      $oSparta = $oDeco->SpecialItem[$FishType][$FishId];
      if (!is_object($oSparta))
        return array('Error' => Error::OBJECT_NULL);
        
      $oGift = $oSparta->getGift($FishType);
      if (!$oGift)
        return array('Error' => Error::ACTION_NOT_AVAILABLE);
      $oUser = User::getById(Controller::$uId);
      $oUser->saveBonus($oGift);
      $oUser->save();
      
      $oDeco->save();
      
      return array('Error' => Error::SUCCESS, 'Gift' => $oGift);
  } 
  
  public function buySoldierFish($param)
  {
      $RecipeType = $param['RecipeType'];
      $RecipeId = intval($param['RecipeId']);
      $isMoney = $param['isMoney'];
      
      $oUser = User::getById(Controller::$uId);
      $oStore = Store::getById(Controller::$uId);
      
      $conf_cost = Common::getConfig('Soldier',$RecipeType, $RecipeId);
      if (!is_array($conf_cost))
        return array('Error' => Error::PARAM);
      
      $oldMoney = $oUser->Money ;
      $oldZMoney = $oUser->ZMoney ;
        
      
      if ($isMoney)
      {
          if (!$oUser->addMoney(-$conf_cost['Money'],'buySoldierFish'))
            return array('Error' => Error::NOT_ENOUGH_MONEY);
      }
      else
      {
          $info = $RecipeId.':'.$RecipeType.':1';
          if (!$oUser->addZingXu(-$conf_cost['ZMoney'],$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
      }
      
      if (!$oStore->createSoldierByRecipe($RecipeType,$RecipeId))
        return array('Error' => Error::ACTION_NOT_AVAILABLE);
      
      $oUser->save();
      $oStore->save();
      
      $oldMoney = $oUser->Money - $oldMoney ;
      $oldZMoney = $oUser->ZMoney - $oldZMoney ;
      //log
      Zf_log::write_act_log(Controller::$uId,0,23,'buySoldierFish',$oldMoney,$oldZMoney,$RecipeType,$RecipeId);
      return array('Error' => Error::SUCCESS);
  }
  
  
  public function useHerbPotion($param)
  {           
      $LakeId = intval($param['LakeId']);
      $FishId = intval($param['FishId']);
      $IdHerbItem = intval($param['HerbPotionId']);
      $NumUse = intval($param['Num']);
      
      if (empty($LakeId) || empty($FishId) || ($IdHerbItem>3) || ($IdHerbItem<1))
        return array('Error' => Error::PARAM);
      
      if (empty($NumUse))  
        $NumUse = 1;
      
      if(!Event::checkEventCondition('MagicPotion'))
          return array('Error' => Error::ACTION_NOT_AVAILABLE);
        
      $oLake = Lake::getById(Controller::$uId,$LakeId);
      if (!is_object($oLake))
        return array('Error' => Error::OBJECT_NULL);  
      $oSolider = $oLake->getFish($FishId);
      if (!$oSolider)
        return array('Error' => Error::OBJECT_NULL);       
      if ($oSolider->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);              

      $conf_gift = Common::getConfig('HerbPotion');  
  
      $oStore = Store::getById(Controller::$uId);
      if (!$oStore->useItem(Type::HerbPotion,$IdHerbItem,$NumUse))
        return array('Error' => Error::NOT_ENOUGH_ITEM);
 
      $oUser = User::getById(Controller::$uId);
      for($i=0; $i<$NumUse; $i++)
        $oUser->saveBonus(array($conf_gift[$IdHerbItem]['Sure'][1]));
      $oSolider->addRankPoint($conf_gift[$IdHerbItem]['Sure'][2]['Num']*$NumUse); 
    
      $randList = array();
      foreach($conf_gift[$IdHerbItem]['Lucky'] as $id => $oGift)
      {
          $randList[$id] = $oGift['Rate'];
      }
      $ooGift = array();
      for ($i=0; $i<$NumUse; $i++)  
      {
          $idRandGift = Common::randomIndex($randList);     
          if ($conf_gift[$IdHerbItem]['Lucky'][$idRandGift][Type::ItemType]==Type::RandomEquipment)
          {     
              $Gift = $conf_gift[$IdHerbItem]['Lucky'][$idRandGift];
              $arrEquip = array(SoldierEquipment::Armor,SoldierEquipment::Belt,SoldierEquipment::Bracelet,SoldierEquipment::Helmet,SoldierEquipment::Necklace,SoldierEquipment::Ring,SoldierEquipment::Weapon);
              $Gift[Type::ItemType] = $arrEquip[array_rand($arrEquip)];
              $arrGift = array($Gift);
              $arrGift = Equipment::mappingLevelToRankEquipment($arrGift,rand(1,5));
              $Gift = $arrGift[0];
              $conf_equip = Common::getConfig('Wars_'.$Gift[Type::ItemType]);
              $conf_equip = $conf_equip[$Gift['Rank']][$Gift['Color']];
              for ($i1=0; $i1< $Gift['Num']; $i1++)
              {
                    $oEquip = new Equipment($oUser->getAutoId(),$conf_equip['Element'],$Gift[Type::ItemType],$Gift['Rank'],$Gift['Color'],rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'], SourceEquipment::EVENT);
                    $oStore->addEquipment($Gift[Type::ItemType],$oEquip->Id,$oEquip);    
                    $ooGift[] = $oEquip;
                    Zf_log::write_equipment_log(Controller::$uId, 0, 20,'useHerbPotion', 0, 0, $oEquip);  
              }    
          }
          else
          {
              $oUser->saveBonus(array($conf_gift[$IdHerbItem]['Lucky'][$idRandGift]));
              $ooGift[] = $conf_gift[$IdHerbItem]['Lucky'][$idRandGift];
          }        
      }
      
      
      $oLake->FishList[$FishId] = $oSolider;
      $oUser->save();
      $oStore->save();
      $oLake->save();
      
      
      Zf_log::write_act_log(Controller::$uId,0,20,'useHerbPotion',0,0, $IdHerbItem, $FishId, $LakeId);
      return array('Error' => Error::SUCCESS, 'Lucky' => $ooGift);  
  }
    
  public function changeSoldierName($param)
  {                                             
      $SoldierId = intval($param['SoldierId']);
      $LakeId = intval($param['LakeId']);
      $NameSoldier = $param['NameSoldier'];
      // check condition    
      if (empty($LakeId) || empty($SoldierId) || empty($NameSoldier))
            return array('Error' => Error::PARAM);                         
                                                         
      $oLake = Lake::getById(Controller::$uId, $LakeId);  
      if (empty($oLake))
        return array('Error' => Error::ID_INVALID);         
      $oSoldier = $oLake->getFish($SoldierId);                                                          
      if (!$oSoldier)
        return array('Error' => Error::OBJECT_NULL);  
                  
      if ($oSoldier->FishType!=FishType::SOLDIER)
        return array('Error' => Error::ID_INVALID);              
                                                                 
      $oSoldier->nameSoldier = $NameSoldier;
      $oSoldier->lastTimeChangeName =  $_SERVER['REQUEST_TIME'];
      $oLake->save();
      return array('Error' => Error::SUCCESS);   
  }
}

?>
