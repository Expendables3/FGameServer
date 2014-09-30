<?php
/**
 * @author AnhBV
 * @version 1.0
 * @created 3-9-2010
 * @Description : thuc hien viec xu ly phan Inventory
 */


class StoreService 
{

	/**
	 * @author AnhBV
	 * @created 9-9-2010
	 * @Description : ham thuc hien viec load do trong kho
	 */
	public function loadInventory()
	{
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $oStore = Store::getById(Controller::$uId);
        $equipmentInSoldier = StoreEquipment::getById(Controller::$uId);
        Store::logEquipment($oStore->Equipment, $equipmentInSoldier->SoldierList,$oStore->Quartz);
        
		$arr_result = array();
		$arr_result['StoreList'] = $oStore;
		$arr_result['Error'] = Error::SUCCESS ;
		return $arr_result;
	}

    
    
    
	/**
	 * @author AnhBV
	 * @created 23-2-2011
	 * @Description : ham thuc hien viec luu tru do vao trong kho
	 */
	public function store($param)
	{
		$ItemList =  $param['ItemList'] ;
		$LakeId   = $param['LakeId'] ;

		// kiem tra thong tin dau vao
		if(!is_array($ItemList)||empty($LakeId)|| $LakeId < 1 || $LakeId > 3)
		{
			//thong bao loi
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User::getById(Controller::$uId);
        $oLake = Lake::getById(Controller::$uId,$LakeId);
        
		if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        if (!is_object($oLake))
		{
			return array('Error' => Error :: LAKE_INVALID) ;
		}

		$oDecorate = Decoration::getById(Controller::$uId,$LakeId);
		$oStore = Store::getById(Controller::$uId);
        
		foreach($ItemList as $index => $IdItem)
		{
      		$oItem = $oDecorate->getItem($IdItem);
			if(!is_object($oItem))
			{
				return  array('Error' => Error :: OBJECT_NULL) ;
			}
			// luu vao kho
            if(in_array($oItem->ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
            {
                $oStore->addOther($oItem->ItemType,$oItem->Id,$oItem);   
            }
            else if(in_array($oItem->ItemType,array(Type::PearFlower),true))
            {
                $oStore->addOther($oItem->ItemType,$oItem->Id,$oItem); 
                $oLake->buffToLake($oItem->Option,false);
            }
            else
            {
                //$oStore->addItem($oItem->ItemType, $oItem->ItemId,1);   
            }
			
			// xoa doi tuong  o ho di
			$oDecorate->delItem($IdItem);
		}
		$oStore->save();
		$oDecorate->save();
        $oLake->save();
		
		return array('Error' => Error :: SUCCESS) ;

	}

	/**
	 * @author AnhBV
	 * @created 1-3-2011
	 * @Description : ham thuc hien viec ban Item o trong kho
	 */
	public function sellItem($param)
	{
	 $ItemList = $param['ItemList'];

	 // kiem tra thong tin dau vao
	 if (!is_array($ItemList))
	 {
	 	return array('Error' => Error :: PARAM) ;
	 }
	 $oUser = User :: getById(Controller :: $uId) ;
	 if (!is_object($oUser))
	 {
	 	return array('Error' => Error :: NO_REGIS) ;
	 }

	 $oStore = Store::getById(Controller :: $uId);
	 $money = 0 ;
	 foreach ($ItemList as $item)
	 {
	    $num = intval($item['Num']); 
        if($item['ItemType'] == Type::BabyFish)
	 	{
	 		continue ;
	 	}
	 	else
	 	{
	 		
      		$nDeco = $oStore->getItem($item['ItemType'],$item['ItemId']);
      
		 	if ($num <= 0|| ($nDeco - $num < 0))
		 	{
		 		return array('Error' => Error :: OBJECT_NULL) ;
		 	}

		 	if ($item['ItemType']== Type::FishGift)
		 	{
		 		$Conf = Common :: getConfig('FishGift') ;
			 	$GConf = $Conf[$item['ItemId']];
			 	if (!is_array($GConf))
			 	{
			 		return array('Error' => Error :: NOT_LOAD_CONFIG) ;
			 	}
		 		$money += ($num*$GConf['Money']) ;
		 	}
		 	else if ($item['ItemType']== Type::Fish)
		 	{
		 		$FishConf = Common :: getConfig('Fish');
			 	$FConf = $FishConf[$item['ItemId']];
			 	if (!is_array($FConf))
			 	{
			 		return array('Error' => Error :: NOT_LOAD_CONFIG) ;
			 	}
		 		$money += $num*$FConf['Money'] ;
		 	}
	 	}
	 	
	 	// xoa or tru doi tuong trong kho
	 	$oStore->useItem($item['ItemType'], $item['ItemId'], $num);
	 	$oStore->save();
	 }
	 
	 // cong tien cho user
	 $oUser->addMoney($money,'sellItem') ;
	 $oUser->save() ;
	 
	 // log
     Zf_log::write_act_log(Controller::$uId, 0, 30, 'sellItemInStore', $money, 0,$ItemList[0]['ItemType'],$ItemList[0]['ItemId']);
        
	 $arr_result = array();
	 $arr_result['Money'] = $oUser->Money;
	 $arr_result['Error'] = Error :: SUCCESS ;

	 return $arr_result ;
	}

	/**
	 * @author AnhBV
	 * @created 24-9-2010
	 * @Description : ham thuc hien viec mang Item trong kho ra su dung
	 */

	public function useItem($param)
	{
		$ItemList 	= $param['ItemList'] ;
		$LakeId 	= $param['LakeId'] ;

		// kiem tra thong tin dau vao
		if(empty($ItemList))
		{
			return array('Error' => Error :: PARAM);
		}
			
		$oUser = User :: getById(Controller :: $uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}  
		$oStore = Store::getById(Controller :: $uId);
		$oUserPro = UserProfile::getById(Controller::$uId);
		foreach ($ItemList as $Key => $iteminfo)
		{
			$ItemType 	= $iteminfo['ItemType'];
			$ItemId 	= intval($iteminfo['ItemId']);
			$Id			= intval($iteminfo['Id']);
			$Pos['x']   = intval($iteminfo['x']);
		 	$Pos['y']   = intval($iteminfo['y']);
		 	$Pos['z']   = floatval($iteminfo['z']);
		 	
            $spartaF = Common::getParam('SpartaFamily'); 
            
            if(in_array($ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other,Type::BackGround,Type::PearFlower),true))
            {
                if(($LakeId<1)||($LakeId>3)||empty($Id))
                {
                    return array('Error' => Error :: PARAM) ;
                }
                $oLake = Lake :: getById(Controller :: $uId,$LakeId) ;
                if (!is_object($oLake))
                {
                    return array('Error' => Error :: LAKE_INVALID) ;
                }
                $oItem = $oStore->getOther($ItemType,$Id);
                if(!is_object($oItem))
                    return array('Error' => Error :: OBJECT_NULL) ; 
                //check het han hay chua
                if($oItem->checkExpired())
                    return array('Error' => Error ::EXPIRED) ;  
                    
                //add vao ho 
                $oDecorate = Decoration::getById(Controller::$uId,$LakeId);
                
                // luu background cu 
                if($ItemType == Type::BackGround)
                {
                    foreach($oDecorate->ItemList as $id => $object)
                    {
                        if($object->ItemType == Type::BackGround)
                        {
                            $oStore->addOther(Type::BackGround,$object->Id,$object);
                            $oDecorate->delItem($id);
                            break ;
                        }
                    }
                }
                
                // them background moi
                
                $oItem->X = $Pos['x'] ;
                $oItem->Y = $Pos['y'] ;
                $oItem->Z = $Pos['z'];
                $oDecorate->addItem($Id,$oItem);
                
                // buff cho ho neu la hoa pha le
                if($ItemType == Type::PearFlower)
                {
                    $oLake->buffToLake($oItem->Option,true);
                    
                    $oLake->save();
                }
                
                // xoa or tru doi tuong trong kho
                if(!$oStore->useOther($ItemType,$Id))
                {
                    return array('Error' => Error :: OVER_NUMBER ) ;
                }
                $oDecorate->save();
                $oStore->save();
                
            }
			if (in_array($ItemType,array(Type::EnergyItem),true))
			{
            	$Num = intval($oStore->getItem($ItemType,$ItemId));
				if($Num <= 0)
				{
					return array('Error' => Error :: OBJECT_NULL) ;
				}
                
				$conf_Energy =  Common::getConfig($ItemType,$ItemId);

				if (!is_array($conf_Energy))
				{
					return array('Error' => Error :: NOT_LOAD_CONFIG ) ;
				}
                
                if($ItemType == Type::EnergyItem)
                {
                
	                
                    
                    // Full Energy
                    if ($ItemId==6){
                        $maxEner = $oUser->getMaxEnergy();
                        $curEner = $oUser->getRealEnergy();
                        $addEner = $maxEner - $curEner;
                        if ($addEner > 0)
                            $oUser->addBonusMachine($addEner);   
                        else 
                            return array('Error' => Error::ACTION_NOT_AVAILABLE);
                    }                        
                    else
                        $oUser->addBonusMachine($conf_Energy['Num']);
                        
                    if(!$oUserPro->updateMaxEnergyUse($ItemId,1))
                    {
                        return array('Error' => Error ::CAN_NOT_USE_ENERGYITEM );
                    }
                    
                }

				// xoa or tru doi tuong trong kho
				if(!$oStore->useItem($ItemType, $ItemId,1))
				{
					return array('Error' => Error :: OVER_NUMBER ) ;
				}
				
			}
			else if(in_array($ItemType,$spartaF))
			{
				if(($LakeId<1)||($LakeId>3)||empty($Id))
				{
					return array('Error' => Error :: PARAM) ;
				}
				$oLake = Lake :: getById(Controller :: $uId,$LakeId) ;
				if (!is_object($oLake))
				{
					return array('Error' => Error :: LAKE_INVALID) ;
				}				
				$oOther = $oStore->getOther($ItemType,$Id);
				if (!is_object($oOther))
				{
					return array('Error' => Error :: OBJECT_NULL) ;
				}
				// buff vao ho ca option
				$oLake->buffToLake($oOther->Option,true);
				//add vao ho 
                $oOther->LastTimeGetGift = $_SERVER['REQUEST_TIME'];
				$oDecorate = Decoration::getById(Controller::$uId,$LakeId);
				$oDecorate->addSpecialItem($ItemType,$Id,$oOther);
				//update Start Time cho Sparta
				$oDecorate->updateTimeSparta($ItemType,$Id);

				// xoa or tru doi tuong trong kho
				if(!$oStore->useOther($ItemType,$Id))
				{
					return array('Error' => Error :: OVER_NUMBER ) ;
				}
				$oLake->save();
                $oDecorate->save();
			}
		}
			
		$oUser->save();
		$oStore->save();
		$oUserPro->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'useItem',0,0,$ItemList[0]['ItemType'],intval($ItemList[0]['ItemId']),intval($ItemList[0]['Id']));
		return  array('Error' => Error :: SUCCESS) ;

	}

	    public function useBabyFish($param)
    {

        $Id            = $param['Id'];
        $LakeId     = $param['LakeId'] ;
        $TypeFish = $param['TypeFish']; 

        if(empty($Id)||empty($LakeId))
        {
            return array('Error' => Error :: PARAM) ;
        }
        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

        $oStore = Store::getById(Controller::$uId);
        $oBabyFish = $oStore->getFish($Id);
        if(!is_object($oBabyFish))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }

        $oLake = Lake :: getById(Controller :: $uId,$LakeId) ;
        if (!is_object($oLake))
        {
            return array('Error' => Error :: LAKE_INVALID) ;
        }
        // kiem tra so ca trong ho
        $Lake_conf = Common::getConfig('Lake',$LakeId);
        if (!is_array($Lake_conf))
        {
            return array('Error' => Error :: NOT_LOAD_CONFIG ) ;
        }
        

        if ($oBabyFish->FishType == FishType::SPECIAL_FISH)
        {
            $newFish = new SpecialFish($oBabyFish->Id, $oBabyFish->FishTypeId,
            $oBabyFish->Sex, $oBabyFish->RateOption,$oBabyFish->ColorLevel);
            $oLake->addFish($newFish);
        }
        else if ($oBabyFish->FishType == FishType::RARE_FISH)
        {
            $newFish = new RareFish($oBabyFish->Id, $oBabyFish->FishTypeId,
            $oBabyFish->Sex, $oBabyFish->RateOption,$oBabyFish->ColorLevel);
            $oLake->addFish($newFish);
        }
    else if ($oBabyFish->FishType == FishType::SOLDIER)
    {
        
        if ($oLake->canSoldierIntoLake($oBabyFish->Element))
        {
            $oBabyFish->Status = SoldierStatus::HEALTHY;
            if (in_array($oBabyFish->SoldierType, array(SoldierType::GIFT_SERIES,SoldierType::BUYSHOP))){
                $conf_fish = Common::getConfig('Fish',$oBabyFish->FishTypeId);
                $oBabyFish->OriginalStartTime = $_SERVER['REQUEST_TIME'];
                $oBabyFish->StartTime = $_SERVER['REQUEST_TIME']- $conf_fish['MatureTime']*3600;
                $oBabyFish->FeedAmount = 10;       
            }
            else
            {
                $oBabyFish->OriginalStartTime = $_SERVER['REQUEST_TIME'];
                $oBabyFish->StartTime = $_SERVER['REQUEST_TIME'];    
            }
            
            $oLake->addFish($oBabyFish);    
        }
        else
        {
            return array('Error' => Error::NO_MORE_TWO_ELEMENTS);
        }
        
        
        
    }
        else
        {
            $newFish = new Fish($oBabyFish->Id, $oBabyFish->FishTypeId,
            $oBabyFish->Sex,$oBabyFish->ColorLevel);
            $oLake->addFish($newFish);
        }

        
        $total__Fish = $Lake_conf[$oLake->Level]['TotalFish'];
        if($oLake->getFishCount()> $total__Fish )
        {
            return array('Error' => Error :: LAKE_FULL ) ;
        }
        
        $conf_maxSoldier = Common::getParam('MaxSoldier');
        if ($oLake->getSoldierCount() > $conf_maxSoldier)
            return array('Error' => Error::LAKE_FULL);
        
        
        // xoa or tru doi tuong trong kho
        $oStore->useFish($Id);
            
        $oUser->save();
        $oLake->save();
        $oStore->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'useBabyFish',0,0,$oBabyFish->FishType,$Id);

        return  array('Error' => Error :: SUCCESS) ;
    }
    /**
    * use RankPoint Bottle for SoldierFish
    * 
    * @param mixed $param
    * @return mixed
    */
    public function useRankPointBottle($param)
    {
        $LakeId     = $param['LakeId'] ;
        $SoldierId  = $param['SoldierId'];
        $ItemId     = $param['ItemId'];
        $Num = $param['Num'];
        
        if (empty($LakeId) || empty($SoldierId))
            return array('Error' => Error::PARAM);
        
        $oLake = Lake::getById(Controller::$uId,$LakeId);
        if (!is_object($oLake))
            return array('Error' => Error::OBJECT_NULL);
      
        $oSolider = $oLake->getFish($SoldierId);
        if (!$oSolider)
            return array('Error' => Error::OBJECT_NULL);
        
        if ($oSolider->FishType!=FishType::SOLDIER)
            return array('Error' => Error::ID_INVALID);
        
        // check level Soldier
        $oldRank = intval($oSolider->Rank);
        $oldRankPoint = intval($oSolider->RankPoint);
        $conf = Common::getConfig(Type::RankPointBottle,$ItemId);
      
        $oStore = Store::getById(Controller::$uId);
        
        $useNum = $oSolider->addRankPoint(round($conf['Num']*$Num));
        if($useNum == 0 )
            return array('Error' => Error::NOT_ACTION_MORE);  
        
        $useNum = ceil($useNum/$conf['Num']);  
        
        if (!$oStore->useItem(Type::RankPointBottle,$ItemId,$useNum))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
                                
        $oStore->save();
        $oLake->save();
        $arr['Error'] =  Error::SUCCESS ;
        $arr['RankPoint'] =  $oSolider->RankPoint ;
        $arr['Rank'] =  $oSolider->Rank;
        $arr['Num'] =  $oStore->getItem(Type::RankPointBottle,$ItemId);
        $arr['UseNum'] =  $useNum ;   
        //log
        Zf_log::write_act_log(Controller::$uId,0,20,'useRankPointBottle',0,0,$oldRank.'_'.$oldRankPoint,$oSolider->Rank.'_'.$oSolider->RankPoint,$SoldierId,$ItemId,$useNum,$arr['Num']);
        return $arr;
        
    }
    
    /**
    * mo Lixi nhan qua
    * 
    * @param mixed $param
    * @return Equipment
    */
  /*
    public function openLixi($param)
    {
        $LixiType   = $param['ItemType'];
        $LixiId     = $param['ItemId'];
        $LixiNum    = 1 ;
        
        if(empty($LixiType)||empty($LixiId)||($LixiType != Type::Lixi && $LixiType != Type::LockLixi) )
        {
            return array('Error' => Error :: PARAM) ;
        }
        $oUser = User :: getById(Controller :: $uId);
        $oStore = Store::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        if(!$oStore->useItem($LixiType,$LixiId,$LixiNum))
            return array('Error' => Error :: NOT_ENOUGH_ITEM) ;
            
        if($LixiType == Type::Lixi)
            $Source = SourceEquipment::SHOP;
        else
            $Source = SourceEquipment::LUCKYMACHINE;
            
        $oEquipment = Common::randomEquipment(1,4,$Source);   
        $oStore->addEquipment($oEquipment->Type, $oEquipment->Id, $oEquipment);
        
        $oStore->save();
        
        $arr = array();
        $arr['Equipment'] = $oEquipment ;
        $arr['Error'] = Error::SUCCESS ;
        
        return $arr ;
        
    }
    */
    
}
