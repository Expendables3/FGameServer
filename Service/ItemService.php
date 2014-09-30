<?php


/**
* @author AnhBV
* @version 1.0
* @created 2-9-2010
* @Description : thuc hien viec xu ly Item
*/
class ItemService 
{
	/**
	* @author AnhBV
	* @created 9-9-2010
	* @Description : ham thuc hien viec mua Item
	*/

   /*
    public function buyDeco($param)
	{
        $DecoList[0] = $param['DecoList'];
        $LakeId = $param['LakeId'];
        if (!is_array($DecoList[0])||($LakeId < 1) || ($LakeId > 3))
		{
			return array('Error' => Error :: PARAM) ;
		}
		$oUser = User :: getById(Controller::$uId) ;
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
		if ($oUser->LakeNumb < $LakeId)
        {
            return array('Error' => Error :: LAKE_INVALID) ;
        }

		$oDecorate = Decoration::getById(Controller::$uId,$LakeId);
		
		foreach ($DecoList as $oDeco)
		{
            if(!is_array($oDeco)||empty ($oDeco['Type'])||empty($oDeco['Id']) || empty($oDeco['ItemId']))
            {
               return array('Error' => Error :: PARAM) ;
            }
            if($oDeco['Type']!== Type::Other && $oDeco['Type']!==Type::OceanTree && $oDeco['Type']!==Type::OceanAnimal)
            {
                return array('Error' => Error :: TYPE_INVALID) ;
            }

            $oId = $oUser->getAutoId();
            $idarr = array();
            if ($oDeco['Id'] != $oId )
            {
			   return array('Error' => Error :: ID_INVALID) ;
            }
			$DecoConfig = Common :: getConfig($oDeco['Type']) ;
            $DecoConfig = $DecoConfig[$oDeco['ItemId']];
            if (!is_array($DecoConfig))
                return array('Error' => Error :: NOT_LOAD_CONFIG) ;

            if($DecoConfig['UnlockType']== 5 || $DecoConfig['UnlockType']== 6)
            {
              return array('Error' => Error ::TYPE_INVALID ) ;
            }
			if ($oUser->Level < $DecoConfig['LevelRequire'])
				return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;

            if($oDeco['PriceType'] != 'Money')
            {
                $info = $oDeco['ItemId'].':'.$oDeco['Type'].':1' ;
                if (!$oUser->addZingXu(-$DecoConfig['ZMoney'],$info))
				    return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
                // cong diem kinh nghiem
                $oUser->addExp($DecoConfig['Exp']);
            }
            else
            {
               if (!$oUser->addMoney(- $DecoConfig['Money'],'buyDeco'))
				return  array('Error' => Error :: NOT_ENOUGH_MONEY) ;
            }

			$oItem = new Item($oId, $oDeco['Type'], $oDeco['ItemId'], $oDeco['x'],$oDeco['y'],$oDeco['z']) ;
			$oDecorate->addItem($oId,$oItem);

			// log
			$conf_log = Common::getConfig('LogConfig');
			if(isset($conf_log[$oItem->ItemType]))
			{
				$TypeItemId = $conf_log[$oItem->ItemType];
			}
			if ($oDeco['PriceType'] == 'Money')
            	Zf_log::write_act_log(Controller::$uId,0,23,'buyDeco',-$DecoConfig['Money'],0,$TypeItemId, $oItem->ItemId, 0,0,1);
			else            	
            	Zf_log::write_act_log(Controller::$uId,0,23,'buyDeco',0,-$DecoConfig['ZMoney'],$TypeItemId, $oItem->ItemId,0,0,1);
			
		}
		$oDecorate->save();
		$oUser->save() ;
		$arrResult['Money'] = $oUser->Money ;
        $arrResult['Exp'] = $oUser->Exp ;
        $arrResult['ZMoney'] = $oUser->ZMoney ;
		$arrResult['Error'] = Error ::SUCCESS ;

		return $arrResult ;
	}
              */
    // service mua background
    public function buyBackGround($param)
    {
        $DecoList[0] = $param['DecoList'];
        
        if (!is_array($DecoList[0]))
        {
            return array('Error' => Error :: PARAM) ;
        }
        
        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $diffMoney  = $oUser->Money ;
        $diffZMoney  = $oUser->ZMoney ;
        
        $oStore = Store::getById(Controller::$uId);
                
        foreach ($DecoList as $oDeco)
        {
            if(!is_array($oDeco)||empty ($oDeco['ItemType'])||empty($oDeco['Id']) || empty($oDeco['ItemId']))
            {
               return array('Error' => Error :: PARAM) ;
            }
            if($oDeco['ItemType']!== Type::BackGround)
            {
                return array('Error' => Error :: TYPE_INVALID) ;
            }

            $oId = $oUser->getAutoId();
            
            if ($oDeco['Id'] != $oId )
            {
               return array('Error' => Error :: ID_INVALID) ;
            }
            $DecoConfig = Common :: getConfig($oDeco['ItemType'],$oDeco['ItemId']) ;
            if (!is_array($DecoConfig))
                return array('Error' => Error :: NOT_LOAD_CONFIG) ;

            if($DecoConfig['UnlockType']== 5 || $DecoConfig['UnlockType']== 6)
            {
              return array('Error' => Error ::TYPE_INVALID ) ;
            }
            if ($oUser->Level < $DecoConfig['LevelRequire'])
                return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;

            if($oDeco['PriceType'] != 'Money')
            {
                $info = $oDeco['ItemId'].':'.$oDeco['ItemType'].':1' ;
                if (!$oUser->addZingXu(-$DecoConfig['ZMoney'],$info))
                    return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
                // cong diem kinh nghiem
                $oUser->addExp($DecoConfig['Exp']);
            }
            else
            {
               if (!$oUser->addMoney(- $DecoConfig['Money'],'buyBackGround'))
                return  array('Error' => Error :: NOT_ENOUGH_MONEY) ;
            }

            $oItem = new Item($oId, $oDeco['ItemType'],$oDeco['ItemId']) ;
            $oStore->addOther($oDeco['ItemType'],$oId,$oItem);            
        }
        
        $diffMoney  = $oUser->Money - $diffMoney ;
        $diffZMoney  = $oUser->ZMoney - $diffZMoney ;
        
        $oStore->save();
        $oUser->save() ;
        
        $arrResult['Money'] = $oUser->Money ;
        $arrResult['Exp'] = $oUser->Exp ;
        $arrResult['ZMoney'] = $oUser->ZMoney ;
        $arrResult['Error'] = Error ::SUCCESS ;
        
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[$oItem->ItemType]))
        {
            $TypeItemId = $conf_log[$oItem->ItemType];
        }
        Zf_log::write_act_log(Controller::$uId,0,23,'buyDeco',$diffMoney,$diffZMoney,$TypeItemId, $oItem->ItemId, 0,0,1);

        return $arrResult ;
    }

  
  
  public function buyOther($param)
  {
      $OtherList = $param['OtherList'];

      if (empty ($OtherList))
      {
          return array('Error' => Error :: PARAM) ;
      }

      $oUser = User :: getById(Controller::$uId) ;
      if (!is_object($oUser))
      {
          return array('Error' => Error :: NO_REGIS) ;
      }

      $oStore = Store::getById(Controller::$uId);
      // luu thong so cu~
      $moneyDiff = $oUser->Money;
      $zMoneyDiff = $oUser->ZMoney;
      $expDiff = $oUser->Exp;
    
      $conf_log = Common::getConfig('LogConfig');
      
    
      foreach($OtherList as $objectOther)
      {
          
         if(!is_array($objectOther))
         {
          
              return  array('Error' => Error :: PARAM) ;
         }
         
         if(isset($conf_log[$objectOther['Type']]))
          {
            $TypeItemId = $conf_log[$objectOther['Type']];
          }
         
         if (BuffItem::checkExist($objectOther['Type']))
         {
             if (($objectOther['Type'] == Type::Ginseng) || ($objectOther['Type'] == Type::RecoverHealthSoldier))
             {
                 $OtherConfig = & Common::getConfig($objectOther['Type']); 
             }
             else
             {
                $OtherConfig = & Common::getConfig(Type::BuffItem,$objectOther['Type']);   
             }
             $DetailConfig = $OtherConfig[$objectOther['Id']];     
         }
         else if(FormulaType::checkExist($objectOther['Type']))
         {
             $OtherConfig = & Common::getConfig(Type::MixFormula,$objectOther['Type']); 
             $DetailConfig = $OtherConfig[$objectOther['Id']]; 
         }
         else if(in_array($objectOther['Type'],array(Type::Food,Type::License,Type::Material,
              Type::EnergyItem,Type::Viagra,Type::Petrol,Type::EnergyMachine,Type::RebornMedicine,Type::MagnetItem, Type::GodCharm),true))
         {           
            $OtherConfig = & Common::getConfig($objectOther['Type']);
            $DetailConfig = $OtherConfig[$objectOther['Id']];
         }
         else
         {
            return array('Error' => Error :: TYPE_INVALID) ;   
         }
         
         if(!is_array($DetailConfig))
         {
            return  array('Error' => Error :: NOT_LOAD_CONFIG) ;
         }
         
         // kiem tra loai Unlock
         $UnlockType = $DetailConfig['UnlockType'];
         if($UnlockType == 5 || $UnlockType== 6)
         {
            return array('Error' => Error ::TYPE_INVALID ) ;
         }

         // kiem tra tien va level cua nguoi choi
         if($oUser->Level < $DetailConfig['LevelRequire'])
         {
             return  array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
         }
         
         if($objectOther['PriceType'] != 'Money')
         {
              $info = $objectOther['Id'].':'.$objectOther['Type'].':1' ;
              if (!$oUser->addZingXu(-$OtherConfig[$objectOther['Id']]['ZMoney'],$info))
            return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
              // cong diem kinh nghiem
              $oUser->addExp($OtherConfig[$objectOther['Id']]['Exp']);
         }
         else
         {
            if(!$oUser->addMoney(-$OtherConfig[$objectOther['Id']]['Money'],'buyOther'))
            {
                return  array('Error' => Error :: NOT_ENOUGH_MONEY) ;
            }
         }

         // thuc an loai 1 - loai 3 la + vao thuc an nguoi choi
         if($objectOther['Type']==Type::Food && $objectOther['Id'] >= 1 && $objectOther['Id'] <= 3)
         {
            // them vao no thuc an cua nguoi choi
              $oUser->addFood($OtherConfig[$objectOther['Id']]['Num']) ;
         }
         else if ($objectOther['Type']==Type::EnergyMachine)
         {
           $arr = array() ;
           $arr[0][Type::ItemType] = Type::EnergyMachine  ;
           $arr[0][Type::ItemId]   =  $objectOther['Id']  ;
           $arr[0][Type::Num]   = 1 ;           
           $oUser->saveBonus($arr) ;
         }
         else if ($objectOther['Type']==Type::MagnetItem)    // magnet item
         {
            $oMagnet = $oUser->SpecialItem[Type::Magnet];
            if (!is_object($oMagnet))   
                $oMagnet = new Magnet($oUser->getAutoId(),1,0);
            
            $conf_magnet = Common::getConfig('MagnetItem',$objectOther['Id']);                
            $oMagnet->addNumUse($conf_magnet['NumUse']*$objectOther['Num']);
            $oUser->SpecialItem[Type::Magnet] = $oMagnet;
            $oUser->save();
         }
         else if (BuffItem::checkExist($objectOther['Type']))
         {
           $oStore->addBuffItem($objectOther['Type'],$objectOther['Id'],1);
           $oStore->save();  
         }
         else
         {
              // cac loai khac thi cat het vao trong kho
            $oStore->addItem($objectOther['Type'], $objectOther['Id'], 1);       
            $oStore->save();  
         }

        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[$objectOther['Type']]))
        {
          $TypeItemId = $conf_log[$objectOther['Type']];
        }
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;

         Zf_log::write_act_log(Controller::$uId,0,23,'buyOther',$moneyDiff,$zMoneyDiff,$TypeItemId, $objectOther['Id'], 0,0,1);

      $oUser->save();
      $arr_result = array();
      $arr_result['Money'] = $oUser->Money ;
      $arr_result['ZMoney'] = $oUser->ZMoney ;
      $arr_result['Exp'] = $oUser->Exp ;
      $arr_result['Error'] = Error :: SUCCESS ;
      
      return $arr_result;
    }
  }
  

	/**
	* @author AnhBV
	* @created 10-9-2010
	* @Description : ham thuc hien viec ban Item o trong ho
	*/

	private  function sellDeco($param)
	{
        $DecoList = $param['DecoList'];
        $LakeId = $param['LakeId'];

		// kiem tra thong tin dau vao
		if (empty ($LakeId) || empty ($DecoList))
		{
			return array('Error' => Error :: PARAM) ;
		}

  		$oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
		
        $conf_param = & Common ::getParam();
        if(!is_array($conf_param))
        {
              return array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }

		$oDecorate = Decoration::getById(Controller::$uId,$LakeId);
        
		foreach ($DecoList as $oDeco)
		{
			$oItem = $oDecorate->ItemList[$oDeco['Id']];
			if (!is_object($oItem))
			{
				return  array('Error' => Error :: OBJECT_NULL) ;
			}

			$DecoConfig = Common :: getConfig($oItem->ItemType) ;
            $oConfig = $DecoConfig[$oItem->ItemId];
			if (!is_array($oConfig))
			{
				return array('Error' => Error :: NOT_LOAD_CONFIG) ;
			}

            $MoneyUser = round($oConfig['Money'] /$conf_param[PARAM::MoneySellItem]) ;
            $oUser->addMoney($MoneyUser,'sellDeco');         
            $oDecorate->delItem($oItem->Id);

		}
    
		$oUser->save();  
		$oDecorate->save();
        $arr_result = array() ;
        $arr_result['Money'] = $oUser->Money ;
		//$arr_result['Exp'] = $oUser->Exp;
        $arr_result['Error'] =  Error :: SUCCESS ;
        
		return $arr_result ;
	}

	/**
	* @author AnhBV
	* @created 12-9-2010
	* @Description : ham thuc hien viec thay doi vi tri cua Item
	*/


	public function saveDeco($param)
	{
        $DecoList = $param['DecoList'];
        $LakeId = $param['LakeId'];

        if (!is_array($DecoList) || ($LakeId < 1) || ($LakeId > 3))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User :: getById(Controller::$uId) ;

		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}

		if ($oUser->LakeNumb < $LakeId)
			return array('Error' => Error :: LAKE_INVALID) ;

		$oDecorate = Decoration::getById(Controller::$uId,$LakeId);
		$Spartafamily = Common::getParam('SpartaFamily');
		foreach ($DecoList as $key => $aDeco)
		{
            if(in_array($aDeco['ItemType'],$Spartafamily,true))
            {
                $oSuperFish = $oDecorate->getSpecialItem($aDeco['ItemType'],$aDeco['Id']) ;
                if(!is_object($oSuperFish))
                    return array('Error' => Error :: OBJECT_NULL) ; 

                 $Position['X'] = $aDeco['x'];
                 $Position['Y'] = $aDeco['y'];
                 $Position['Z'] = $aDeco['z'];
                 
                if(!$oSuperFish->updatePosition($Position)) 
                    return array('Error' => Error :: NOT_ENOUGH_TIME) ;    
            }
            else
            {
    		    if (!$oDecorate->saveItem($aDeco['Id'],$aDeco['x'],$aDeco['y'],$aDeco['z']))
    		    {
    			    return array('Error' => Error :: OBJECT_NULL) ; 
    		    }
            }
		}
		
		$oDecorate->save();
		
		return array('Error' => Error :: SUCCESS) ;
	}
	
/**
	* @author AnhBV
	* @created 14-5-2011
	* @Description : ham thuc hien viec ban cac Item dac biet cua Ho 
	*/
	public  function sellSpecialItem($param)
	{
		$ItemType 	= strval($param['ItemType']);
    $Id 		= intval($param['Id']);
    $LakeId 	= intval($param['LakeId']);

		// kiem tra thong tin dau vao
		if ($LakeId < 1 || $LakeId > 3 || $Id < 1 || empty($ItemType))
		{
			return array('Error' => Error :: PARAM) ;
		}

  		$oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
		       
		$oLake = Lake :: getById(Controller :: $uId,$LakeId) ;
		if (!is_object($oLake))
		{
			return array('Error' => Error :: LAKE_INVALID) ;
		}

		$oDecorate = Decoration::getById(Controller::$uId,$LakeId);

        $object =  $oDecorate->getSpecialItem($ItemType,$Id); 
        if(!is_object($object))
         return  array('Error' => Error :: OBJECT_NULL) ; 
         
    // tinh chenh lech
    $moneyDiff = $oUser->Money ;
    $zMoneyDiff = $oUser->ZMoney; 
    
    $spartaF = Common::getParam('SpartaFamily');

	if (in_array($ItemType,$spartaF))
	{
		// xoa viec buff cho ho + tinh gia ban
        $conf_cost = Common::getConfig('SuperFish',$ItemType);
        $arrBonus = array() ;
        $arrBonus[0][Type::ItemType] = Type::Exp;
        $arrBonus[1][Type::ItemType] = Type::Money;
        
        if($object->isExpried)
        {
            $oLake->buffToLake($object->Option,FALSE);
            $arrBonus[0][Type::Num] = $conf_cost['Active']['Exp'];
            $arrBonus[1][Type::Num] = $conf_cost['Active']['Money'];
        }
        else
        {
            $arrBonus[0][Type::Num] = $conf_cost['Disable']['Exp'];
            $arrBonus[1][Type::Num] = $conf_cost['Disable']['Money'];   
        }
        
        $oUser->saveBonus($arrBonus);
      
		// xoa khoi list Decoration 
		$oDecorate->delSpecialItem($ItemType,$Id);
	}
    if(in_array($ItemType,array(Type::Sparta,Type::Swat,Type::Superman)))
    {
        $oUserPro = UserProfile :: getById(Controller :: $uId) ;
        $oUserPro->Event['DailyQuest'][$ItemType] -= 1 ;
        $oUserPro->save();
    }

   
	$oUser->save();  
	$oDecorate->save();
	$oLake->save();
		
    $conf_log = Common::getConfig('LogConfig');
    if(isset($conf_log[$ItemType]))
    {
      $TypeItemId = $conf_log[$ItemType];
    }
    // tinh chenh lech
    $moneyDiff = $oUser->Money - $moneyDiff;
    $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
    
    Zf_log::write_act_log(Controller::$uId,0,30,'sellSpecialItem',$moneyDiff,$zMoneyDiff,$TypeItemId);    
    
    $arr_result = array() ;
    $arr_result['Money']  = $oUser->Money ;
		$arr_result['Exp']    = $oUser->Exp;
    $arr_result['Error']  =  Error :: SUCCESS ;
    
		return $arr_result ;
	}
	
/**
	* @author AnhBV
	* @created 15-5-2011
	* @Description : ham thuc hien viec chuyen ho cho ca Sparta
	*/
	public  function changeSparta($param)
	{
        $ItemType   = strval($param['ItemType']);   
        $Id 		    = intval($param['Id']);
        $LakeFromId = intval($param['LakeFromId']);
        $LakeToId 	= intval($param['LakeToId']);

		// kiem tra thong tin dau vao
		if ($LakeFromId < 1 || $LakeFromId > 3 || $LakeToId < 1 ||
       $LakeToId > 3 || $Id < 1 || $LakeFromId == $LakeToId ||empty($ItemType))
		{
			return array('Error' => Error :: PARAM) ;
		}

  		$oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}
    
    $spartaF = Common::getParam('SpartaFamily');
		if(!in_array($ItemType,$spartaF)) 
    {
       return array('Error' => Error :: TYPE_INVALID) ;     
    }  
          
		$oLakeForm 	= Lake :: getById(Controller :: $uId,$LakeFromId) ;
		$oLakeTo 	= Lake :: getById(Controller :: $uId,$LakeToId) ;
		if (!is_object($oLakeForm)||!is_object($oLakeTo) )
		{
			return array('Error' => Error :: LAKE_INVALID) ;
		}

		$oDecorate = Decoration::getById(Controller::$uId,$LakeFromId);
		$oDecorateNew = Decoration::getById(Controller::$uId,$LakeToId);
    
 	  $object =  $oDecorate->getSpecialItem($ItemType,$Id);
		if (!is_object($object))
		{
		  return  array('Error' => Error :: OBJECT_NULL) ;
		}
 
		// xoa trong ho cu 
		$oDecorate->delSpecialItem($ItemType,$Id);
		// xoa viec buff cho ho cu
		$oLakeForm->buffToLake($object->Option,FALSE);
		
		// tao ca sang ho moi 
		$oDecorateNew->addSpecialItem($ItemType,$Id,$object);
		// them buff cho ho moi
		$oLakeTo->buffToLake($object->Option,TRUE);

		
		$oDecorate->save();
		$oDecorateNew->save();
		$oLakeForm->save();
		$oLakeTo->save();

		$arr_result['Error'] =  Error :: SUCCESS ;
		return $arr_result ;
	}
    
	/*
	 * date :17-5- 2011
	 * @author anhbv
	 */
	// ham thuc hien viec update cac thay doi khi cac loai dac biet het han het han
 	public function updateExpired($param)
 	{
    
        $ownerId = $param['ownerId'] ;
 		$oUser = User :: getById(Controller::$uId);
		if (!is_object($oUser))
		{
			return  array('Error' => Error::NO_REGIS) ;
		}
    
        if(!Decoration::updateExpired($ownerId))
        {
          return  array('Error' => Error::OBJECT_NULL) ;   
        }    

 		return  array('Error' => Error::SUCCESS) ;
 		
 	}
    
    // ham thuc hien viec luu cac do trang tri het han vao kho
    public function updateExpiredDeco($param)
     {
    
        $ownerId    = $param['ownerId'];
        $DecoList   = $param['DecoList'];
        $LakeId     = $param['LakeId'];
        
        if(empty($LakeId)|| empty($DecoList)||empty($LakeId))
            return  array('Error' => Error::PARAM) ;   
        
        $oUser = User :: getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return  array('Error' => Error::NO_REGIS) ;
        }
        
        $oDecorate  = Decoration::getById($ownerId,$LakeId);
        $oStore     = Store::getById($ownerId) ;
        $oLake      = Lake:: getById($ownerId,$LakeId);
        if(!is_object($oDecorate))
        {
            return  array('Error' => Error::OBJECT_NULL) ;
        }

        foreach ($DecoList as $index => $Id) 
        {
            $oItem = $oDecorate->ItemList[$Id] ;
            if(!is_object($oItem))
            {
                continue ;
            }
            if($oItem->checkExpired())
            {
                // het han thi luu do vao kho
                $oStore->addOther($oItem->ItemType,$oItem->Id,$oItem) ;

                // xoa buff cho ho neu la hoa pha le
                if($oItem->ItemType == Type::PearFlower)
                {
                $oLake->buffToLake($oItem->Option,false);
                }
                
                //unset do o ho
                unset($oDecorate->ItemList[$Id]); 
            }
            else
            {
                return  array('Error' => Error::NOT_ENOUGH_TIME) ;
            }
          
        }

         $oDecorate->save();
         $oStore->save();
         $oLake->save();
         return  array('Error' => Error::SUCCESS) ;
         
     }
    
    
    /**
    * reborn Sparta Family
    * @author hieupt
    * 13/07/2011
    */
    
    public function rebornXFish($param)
    {
        $type = $param['TypeFish']; // typeFish
        $medicine = $param['TypeMedicine']; // type reborn medicine
        $idDeco = $param['IdFish'];
        $idLake = $param['IdLake'];
        
        $spartaF = Common::getParam('SpartaFamily');
        if(!in_array($type,$spartaF) || empty($type) || empty($idDeco) || empty($idLake) || !($idLake>=1 && $idLake<=3))
            return array('Error' => Error::PARAM);
            
        $oDeco = Decoration::getById(Controller::$uId,$idLake);
        if (!is_object($oDeco))
            return array('Error' => Error::OBJECT_NULL);
            
        $oSparta = $oDeco->SpecialItem[$type][$idDeco];            
        if (!is_object($oSparta))
            return array('Error' => Error::FISH_NOT_EXITS);
            
        if ($oSparta->isExpried)
            return array('Error' => Error::FISH_NOT_DIE);
            
        $oStore = Store::getById(Controller::$uId);
        if (!$oStore->useItem('RebornMedicine',$medicine,1))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
            
        // get time reborn   
        $conf_re = Common::getConfig('RebornMedicine',$medicine);
        $dayR = floatval($conf_re['RebornTime']/(24*3600));
  
        if (!$oSparta->reborn($dayR))
            return array('Error' => Error::CANT_REBORN);
            
        $oLake = Lake::getById(Controller::$uId, $idLake);
        $oLake->buffToLake($oSparta->Option, true);
            
        $oDeco->save();
        $oStore->save();
        $oLake->save();
        
        // log
        
        Zf_log::write_act_log(Controller::$uId,0,20,'rebornXFish',0,0,$type,$medicine,$idDeco);
        return array('Error' => Error::SUCCESS);

    }
    
    // ham thuc hien viec gia han do trang tri
    public function extensionDeco($param)
    {
        $DecoList   = $param['DecoList'];
        
        if(empty($DecoList))
            return array('Error' => Error::PARAM);

        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        $xu= 0 ;
        $num = 0 ;
        $oStore = Store::getById(Controller :: $uId);
        $arr_oDecoration = array();
        foreach($DecoList as $index => $arr)
        {
            //if(!in_array($arr[Type::ItemType],array(Type::OceanAnimal,Type::Other,Type::OceanTree,Type::PearFlower,Type::BackGround),true) ||$arr['LakeId'] < 0 || $arr['LakeId'] >3)
            //    continue ;
            if(!in_array($arr[Type::ItemType],array(Type::BackGround),true) ||$arr['LakeId'] < 0 || $arr['LakeId'] >3)
                continue ;
            if($arr['LakeId'] == 0) // do trong kho
            {
                $oItem = $oStore->getOther($arr[Type::ItemType],$arr['Id']);
            }
            else
            {
                $oLake = Lake::getById(Controller::$uId,$arr['LakeId']);
                if (!is_object($oLake))
                {
                    return array('Error' => Error ::LAKE_INVALID) ;
                }
                $oDeco = Decoration::getById(Controller::$uId,$arr['LakeId']);
                $oItem = $oDeco->getItem($arr['Id']);
                $arr_oDecoration[] = $oDeco ;                 
            }
            
            if (!is_object($oItem))
            {
                return array('Error' => Error :: OBJECT_NULL) ;
            }
            $subTime = $oItem->ExpiredTime - $_SERVER['REQUEST_TIME'] ;
            if($subTime > 6*24*3600) // chua den ngay gan het han
            {
                 return array('Error' => Error::NO_EXPIRED);  
            }
            if(($_SERVER['REQUEST_TIME'] - $oItem->ExpiredTime) > 7*24*3600) // da het han
            {
                 return array('Error' => Error::EXPIRED);  
            }
            $conf = Common::getConfig($arr[Type::ItemType],$oItem->ItemId);
            if(!is_array($conf))
            {
                 return array('Error' => Error::NOT_LOAD_CONFIG);  
            }        
            $xu += ceil($conf['ZMoney']/2);
            $num++ ;
            $oItem->updateExpiredTime($conf['TimeUse']);
        }
        
        $maxExpiredItem = Common::getParam('MaxExpiredItem');
        $XuPercent = Common::getParam('ExtensionXuPercent');
        
        if($num >= $maxExpiredItem)
        {
            $xu = ceil($xu*$XuPercent) ;
        }
        
        $zMoneyDiff = $oUser->ZMoney ;
        
        //check xu          
        $Info = "1:extensionDeco:1";
        if(!$oUser->addZingXu(-$xu,$Info))
        {
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);       
        }
        
        if(is_array($arr_oDecoration))
        {
            foreach($arr_oDecoration as $oDecoration)
            {
                $oDecoration->save();
            }
        }
        $oUser->save();
        $oStore->save();
        
        // log    
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
        Zf_log::write_act_log(Controller::$uId,0,23,'extensionDeco',0,$zMoneyDiff,$DecoList[0]['ItemType']);
        return array('Error' => Error::SUCCESS,"ZMoney"=>$xu);
    }  
    /**
    * user buff items for soldier
    * @author hieupt
    * 30/08/2011
    */
    
    public function useItemSoldier($param){
        $LakeId = intval($param['LakeId']);
        $FishId = intval($param['FishId']);
        $ItemList = $param['ItemList'];
        
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
        foreach($ItemList as $id => $oItem)
        {
            if (!$oStore->useBuffItem($oItem[Type::ItemType],$oItem[Type::ItemId],$oItem[Type::Num]))
                return array('Error' => Error::NOT_ENOUGH_ITEM);
        }
        
        $buffItemAfter = Common::addItemToList($oSolider->BuffItem, $ItemList);
        if (!SoldierFish::checkValidItemList($buffItemAfter))
            return array('Error' => Error::ID_INVALID);
        
        $conf_buff = Common::getConfig('BuffItem');
        foreach($buffItemAfter as $id => $oItem)
        {
            if ($conf_buff[$oItem[Type::ItemType]][$oItem[Type::ItemId]]['Turn'] > 1)
            {
                $buffItemAfter[$id]['Turn'] = intval($buffItemAfter[$id]['Turn']*$buffItemAfter[$id]['Num']);
                $buffItemAfter[$id][Type::Num] = 1;
            }    
        }
        
        $oSolider->BuffItem = $buffItemAfter;      
        $oLake->FishList[$FishId] = $oSolider;   
        $oLake->save();
        $oStore->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'useItemSoldier',0,0,$ItemList[0]['ItemType'],$ItemList[0]['ItemId'],$ItemList[0]['Num']);
        return array('Error' => Error::SUCCESS);
    }
        
    
    public function useEquipmentSoldier($param)
    {
        $EquipmentId = intval($param['EquipmentId']);
        $EquipmentType = $param['EquipmentType'];
        $SoldierId = intval($param['SoldierId']);
        $LakeId = intval($param['LakeId']);
        
        if(empty($LakeId))
            $LakeId = 1;
        if (empty($EquipmentId) || empty($SoldierId))
            return array('Error' => Error::PARAM);
        $oStore = Store::getById(Controller::$uId);
        $oEquip = $oStore->Equipment[$EquipmentType][$EquipmentId];
        if (!is_object($oEquip))
            return array('Error' => Error::OBJECT_NULL);
        $oLake = Lake::getById(Controller::$uId, $LakeId);
        $oSoldier = $oLake->getFish($SoldierId);
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        if (!is_object($oSoldier))
            return array('Error' => Error::OBJECT_NULL);
        if ($oSoldier->FishType!=FishType::SOLDIER)
            return array('Error' => Error::ID_INVALID);
            
        $oResult = $oStoreEquip->addEquipment($SoldierId, $oSoldier->Element, $oEquip);
        if (!$oResult)
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
       
        //update JadeSeal
        if(!empty($oStoreEquip->SoldierList[$SoldierId]['Equipment'][Type::JadeSeal]))
        {                        
                $oStoreEquip->updateBonusFromJadeSeal($SoldierId);
        }
        // end update JadeSeal
        
        $oStore->removeEquipment($EquipmentType,$EquipmentId);
            
        $oStoreEquip->save();
        $oStore->save();

        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'useEquipmentSoldier', 0, 0, $oEquip);  
        return array('Error' => Error::SUCCESS);
    }
        
    public function storeEquipment($param)
    {
        $EquipmentId = intval($param['EquipmentId']);
        $EquipmentType = $param['EquipmentType'];
        $SoldierId = intval($param['SoldierId']);
        $LakeId = intval($param['LakeId']);
        
        if (empty($LakeId))
            $LakeId = 1;
        if (empty($EquipmentId) || empty($SoldierId) || empty($EquipmentType))                    
            return array('Error' => Error::PARAM);
        $oStore = Store::getById(Controller::$uId);
        
        $oLake = Lake::getById(Controller::$uId, $LakeId);
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
        $oSoldier = $oLake->getFish($SoldierId);
        if (!is_object($oSoldier))
            return array('Error' => Error::OBJECT_NULL);
        $oEquipment = $oStoreEquip->SoldierList[$SoldierId]['Equipment'][$EquipmentType][$EquipmentId];
        if (!$oStoreEquip->deleteEquipment($SoldierId,$EquipmentType,$EquipmentId))
            return array('Error' => Error::OBJECT_NULL);
        
        //update JadeSeal
        if(($EquipmentType != Type::Mask) && (!empty($oStoreEquip->SoldierList[$SoldierId]['Equipment'][Type::JadeSeal])))
        {
            $oStoreEquip->disableAllLevelJadeSeal($SoldierId);
            $oEquipment->PercentBonus = 0;
        }
            
        else if($EquipmentType == Type::JadeSeal)
        {
           $oEquipment->disableAllLevelJadeSeal(); 
           $oStoreEquip->setPercentBonus($SoldierId, 0);
           $oStoreEquip->updateBonusEquipment($SoldierId) ;
        }
           
        // end update JadeSeal
        
        $oStore->addEquipment($EquipmentType, $EquipmentId, $oEquipment);
        
        $oStoreEquip->save();
        $oStore->save();
        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'storeEquipment', 0, 0, $oEquipment); 
        return array('Error' => Error::SUCCESS);    
    }
    
    /**
    * Delete Equipment
    * @author hieupt
    * 05/10/2011
    */
    public function deleteEquipment($param)
    {
        $EquipmentId = intval($param['EquipmentId']);
        $EquipmentType = $param['EquipmentType'];

        if (empty($EquipmentId) || empty($EquipmentType))                    
            return array('Error' => Error::PARAM);
        
        $oStore = Store::getById(Controller::$uId);
        $oEquip = $oStore->Equipment[$EquipmentType][$EquipmentId];
        if(!is_object($oStore->Equipment[$EquipmentType][$EquipmentId]))
            return array('Error' => Error::OBJECT_NULL);
        $oStore->removeEquipment($EquipmentType,$EquipmentId);
        $oStore->save();
        
        // log
        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'deleteEquipment', 0, 0, $oEquip); 
        
        return array('Error' => Error::SUCCESS)   ;
    }
    
    /**
    * update expired equipment, store it back
    * @author hieupt
    * 05/10/2011
    */
    public function updateExpiredEquipment($param)
    {
        $userId = $param['UserId'];
        if (empty($userId))
            $userId = Controller::$uId;
        $oUser = User::getById($userId);
        $NumLake = $oUser->LakeNumb;
        $oStoreEquip = StoreEquipment::getById($userId);            
        
        for ($i = 1; $i<=$NumLake; $i++)
        {
            $oLake = Lake::getById($userId,$i);
            foreach($oLake->FishList as $id => $oFish)
            {
                if ($oFish->FishType == FishType::SOLDIER)
                {
                    foreach($oStoreEquip->SoldierList[$id]['Equipment'] as $indexType => $listType)
                    {
                        foreach($listType as $idx => $oEquip)
                        {
                            if ($oEquip->isExpired())
                            {
                                if ($oEquip->InUse)
                                {
                                    $oStoreEquip->addBonusEquipment($id, $oEquip->getIndex(),false);
                                    $oStoreEquip->SoldierList[$id]['Equipment'][$indexType][$idx]->InUse = false;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $oStoreEquip->save();
        return array('Error' => Error::SUCCESS) ;
    }
    
    /**
    * update expired Time equipment and delete it
    * @author AnhBV
    * 18/1/2012
    */
    public function updateExpiredTimeOfEquipment($param)
    {
        $userId     = $param['UserId'];
        $LakeId     = $param['LakeId'];
        $SoldierId  = $param['SoldierId'];
        $ItemType   = $param['ItemType'];
        $Id         = $param['Id'];

        if (empty($userId)|| empty($ItemType)|| empty($Id))
            return array('Error'=>Error::PARAM);
            
        $oUser = User::getById($userId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
                
        if(!empty($LakeId)) // check in SoldierFish 
        {
            if($ItemType != SoldierEquipment::Mask)
                return array('Error'=>Error::TYPE_INVALID);
                
            $oLake = Lake::getById($userId,$LakeId);
            $oSoldier = $oLake->getFish($SoldierId);
            if(!is_object($oSoldier)|| $oSoldier->FishType != FishType::SOLDIER)
                return array('Error'=>Error::OBJECT_NULL);
            
            $storeSoldier = StoreEquipment::getById($userId);
            $oEquip = $storeSoldier->SoldierList[$SoldierId]['Equipment'][$ItemType][$Id] ;
            if (!is_object($oEquip))
            {
                return array('Error'=>Error::OBJECT_NULL);  
            }
            
            if (!$oEquip->checkExpiredTime())
            {
                $storeSoldier->deleteEquipment($SoldierId,$ItemType,$Id);
            }
            else
            {
                return array('Error'=>Error::NOT_ENOUGH_TIME);
            }
 
            $storeSoldier->save();
        }
        else  // check in store
        {
            $oStore = Store::getById($userId);
            $oEquip = $oStore->getEquipment($ItemType,$Id);
            if(!is_object($oEquip))
                return array('Error'=>Error::ARRAY_NULL);
                
            if($ItemType != SoldierEquipment::Mask)
                        return array('Error'=>Error::ID_INVALID);
            
            if (!$oEquip->checkExpiredTime())
            {
                $oStore->removeEquipment($ItemType,$Id);
            }
            else
            {
                return array('Error'=>Error::NOT_ENOUGH_TIME);
            }
            $oStore->save();
        } 

        return array('Error' => Error::SUCCESS) ;
    }
    
    /**
    * buy Equipment
    * @author hieupt
    * 05/10/2011
    */
    public function buyEquipment($param)
    {
        $Type = $param['Type'];
        $Rank = intval($param['Rank']);
        $Color = intval($param['Color']);
        $isMoney = $param['isMoney'];
        
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        $conf_equip = Common::getConfig('Wars_'.$Type);
        $conf_equip = $conf_equip[$Rank][$Color];
        
        // can buy in shop ?
        if ($conf_equip['UnlockType'] == 6)
            return array('Error' => Error::ID_INVALID);
        if ($isMoney)
        {
            if (!$oUser->addMoney(-$conf_equip['Money'],'buyEquipment'))
                return array('Error' => Error::NOT_ENOUGH_MONEY);    
        }
        else {
            $info = $Rank.':'.$Type.':1'; 
            if (!$oUser->addZingXu(-$conf_equip['ZMoney'],$info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);    
        }

        $oEquip = new Equipment($oUser->getAutoId(),$conf_equip['Element'],$Type,$Rank,$Color,rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'], SourceEquipment::SHOP);
        $oStore->addEquipment($Type,$oEquip->Id,$oEquip);
        
        $oUser->save();
        $oStore->save();
        
        // log
        //$conf_log = Common::getConfig('LogConfig');
        //Zf_log::write_act_log(Controller::$uId,0, 20, 'buyEquipment',0, -$conf_equip['ZMoney'], $conf_log[$Type], $Rank);    
        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'buyEquipment', 0, -$conf_equip['ZMoney'], $oEquip);   
        return array('Error' => Error::SUCCESS);
    }
    
    /**
    * extend equipment expire's time
    * @author hieupt
    * 13/10/2011
    */
    public function extendEquipment($param)
    {
        $EquipmentId = intval($param['EquipmentId']);
        $EquipmentType = $param['EquipmentType'];
        $SoldierId = intval($param['SoldierId']);
        $LakeId = intval($param['LakeId']);
        $PriceType = $param['PriceType'];
        
        // check param
        if (empty($EquipmentType) || empty($EquipmentId))    
            return array('Error' => Error::PARAM);
        
        
        // if in soldier
        if (!empty($SoldierId) && !empty($LakeId))
        {
            $oLake = Lake::getById(Controller::$uId, $LakeId);
            if (!is_object($oLake))
                return array('Error' => Error::OBJECT_NULL);    
            $oSoldier = $oLake->getFish($SoldierId);
            if (!is_object($oSoldier))
                return array('Error' => Error::OBJECT_NULL);    
            
            $oStoreEquip = StoreEquipment::getById(Controller::$uId);
            $oEquipment = $oStoreEquip->getEquipment($SoldierId,$EquipmentType,$EquipmentId);
            //$oEquipment = $oSoldier->Equipment[$EquipmentType][$EquipmentId];
        }
        else // else if in Store
        {
            $oStore = Store::getById(Controller::$uId);
            $oEquipment = $oStore->Equipment[$EquipmentType][$EquipmentId]; 
        }
        
        if (!is_object($oEquipment))    
            return array('Error' => Error::OBJECT_NULL);
         
        if($EquipmentType == SoldierEquipment::Mask)
            return array('Error' => Error::ID_INVALID);   
             
        // check money
        $oUser = User::getById(Controller::$uId);
        $conf_durability = Common::getConfig('Wars_'.$EquipmentType,$oEquipment->Rank,$oEquipment->Color);
        $costExtend = $this->getCostExtendEquipment($EquipmentType,$oEquipment->Color,ceil($oEquipment->Durability),$conf_durability['Durability']);
        // if paid by money
        if ($PriceType == Type::Money)
        {
            if (!$oUser->addMoney(-$costExtend['Money'],'extendEquipment'))
                return array('Error' => Error::NOT_ENOUGH_MONEY);
        }
        else if($PriceType == Type::ZMoney)   // else if paid by zmoney 
        {
            $info = '1:extendEquip:1';
            if (!$oUser->addZingXu(-$costExtend['ZMoney'],$info))    
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }
        else if($PriceType == Type::Diamond)   // else if paid by Diamond
        {
            if (!$oUser->addDiamond(-$costExtend['Diamond'],DiamondLog::extendEquipment))    
                return array('Error' => Error::NOT_ENOUGH_DIAMOND);
        }
        else
            return array('Error' => Error::TYPE_INVALID); 
        
        // take action        
        $oldDurability = $oEquipment->Durability ;
        if (!$oEquipment->addDurability($conf_durability['Durability']-$oEquipment->Durability))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);    
        
        // save
        if (is_object($oStore)) // if in store
        {
            $oStore->Equipment[$EquipmentType][$EquipmentId] = $oEquipment;
            $oStore->save();
        }
        else {      // if in soldier
            $oStoreEquip->setEquipment($SoldierId,$EquipmentType,$EquipmentId,$oEquipment);
            $oStoreEquip->save();
        }
        $oUser->save();                    
        
        // log
        //Zf_log::write_act_log(Controller::$uId,0, 20,'extendEquipment', 0,-$costExtend['ZMoney']);
        Zf_log::write_equipment_log(Controller::$uId, 0,0,'extendEquipment', -$costExtend['Money'],-$costExtend['ZMoney'], $oEquipment);       
        return array('Error' => Error::SUCCESS);
    }
    
    /**
    * enchant equipment
    * @author hieupt
    * 03/11/2011
    */
    public function enchantEquipment($param)
    {
        // param validation
        $EquipmentId = intval($param['EquipmentId']);
        $EquipmentType = $param['EquipmentType'];
        $isMoney = $param['isMoney'];
        $listMaterial = $param['ListMaterial'];
        $usingGodCharm = $param['UseGodCharm'];

        if (empty($EquipmentId) || empty($EquipmentType))                    
            return array('Error' => Error::PARAM);
        
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        $oEquipment = $oStore->Equipment[$EquipmentType][$EquipmentId];
        $conf_major = Common::getConfig('Param','SoldierEquipment','Major');
        if (in_array($EquipmentType,$conf_major))
            $conf_equip = Common::getConfig('EnchantEquipment_Minor',round($oEquipment->Rank%100),$oEquipment->Color);
        else $conf_equip = Common::getConfig('EnchantEquipment_Minor',$oEquipment->Rank,$oEquipment->Color);
        $enchantId = $oEquipment->EnchantLevel + 1;
        if (!isset($conf_equip[$enchantId]))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        
        // material validation
        $successRate = 0;
        $totalMater = 0;
        foreach ($listMaterial as $mater)
        {
            if (!(($mater['ItemId'] <=14 && $mater['ItemId']>=1)||($mater['ItemId'] <=114&& $mater['ItemId']>=101)))
                return array('Error' => Error :: PARAM) ;

            if (!$oStore->useItem('Material',$mater['ItemId'], intval($mater['Num'])))
              return array('Error' => Error :: NOT_ENOUGH_MATERIAL);
            
            $totalMater += intval($mater['Num']);  
            $successRate += $conf_equip[$enchantId][$mater['ItemId']]*$mater['Num'];
        }
        
        $oUserPro = UserProfile::getById(Controller::$uId);
        if ($oUserPro->EnchantSlot < $totalMater)
            return array('Error' => Error::OVER_NUMBER);
        // equipment valiation   
        
        if(!is_object($oEquipment))
            return array('Error' => Error::OBJECT_NULL);
        
        if ($usingGodCharm)
        {
            $typeGodCharm = $oEquipment->Color;
            $conf_godCharm = Common::getConfig('GodCharm');
            if ($typeGodCharm>=count($conf_godCharm))
                $typeGodCharm = count($conf_godCharm);
            if (!$oStore->useItem(Type::GodCharm,$typeGodCharm, 1))
                return array('Error' => Error::NOT_ENOUGH_ITEM);
        }
            
        // check enough money
        if ($isMoney)   // paid by money
        {
            if (!$oUser->addMoney(-$conf_equip[$enchantId]['Money'],'enchantEquipment'))   
                return array('Error' => Error::NOT_ENOUGH_MONEY);
        }
        else {  // if paid by zmoney
            $info = '1:EnchantEquipment:1';
            if (!$oUser->addZingXu(-$conf_equip[$enchantId]['ZMoney'],$info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }

        // take action
        $isSuccess = $oEquipment->enchant($successRate, $usingGodCharm);
        
        // save
        $oStore->Equipment[$EquipmentType][$EquipmentId] = $oEquipment;
        $oStore->save();
        $oUser->save();
        
        // log
        Zf_log::write_equipment_log(Controller::$uId, 0, 20,'enchantEquipment', 0, -$conf_equip[$enchantId]['ZMoney'], $oEquipment);
        Zf_log::write_act_log(Controller::$uId, 0, 20,'enchantEquipment_2',0,0,intval($isSuccess),$successRate);
        return array('Error' => Error::SUCCESS, 'isSuccess' => $isSuccess, 'EnchantLevel' => $oEquipment->EnchantLevel);
        
    }
    
    /**
    * calculate cost extend equipment
    * @author hieupt
    * 15/11/2011
    */
    private function getCostExtendEquipment($Type, $Color, $Current, $Max)
    {
        $conf_extend = Common::getConfig('Equipment_Extend',$Type,$Color);
        $cost = array();
        if ($Current < 0)
            $Current = 0;
        $Diff = $Max - $Current;
        foreach($conf_extend as $id => $oCost)
        {
            if (($Diff >= $oCost['Durability']['Min']) && ($Diff <= $oCost['Durability']['Max']))
            {
                $cost['Money'] += $oCost['Money'];
                $cost['ZMoney'] += $oCost['ZMoney'];
                $cost['Diamond'] += $oCost['Diamond'];
                break ;
            }
        }
        
        return $cost;            
    }
    
    public function buyDiscount($param)
    {  
        $idDiscount = intval($param['discountId']);
        $isMoney = $param['isMoney'];
        $conf_discount = Common::getConfig('EventDiscount', $idDiscount);
        if (!is_array($conf_discount))
            return array('Error' => Error::OBJECT_NULL);
       
        $oUser = User::getById(Controller::$uId);        
        if (!Event::checkEventCondition($conf_discount['EventName']))
            return array('Error' => Error::EVENT_EXPIRED);
        
        $oUserPro = UserProfile::getById(Controller::$uId);
        $oUserPro->ActionInfo['Discount'][$conf_discount['EventName']][$idDiscount]++;

        if ($oUserPro->ActionInfo['Discount'][$conf_discount['EventName']][$idDiscount] > $conf_discount['MaxItem'])                
            return array('Error' => Error::MAX_DISCOUNT);
                
        if ($isMoney)
        {
            if (!$oUser->addMoney(-$conf_discount['Money'],'buyDiscount'))
                return array('Error' => Error::NOT_ENOUGH_MONEY);
        }
        else
        {
            $info = $conf_discount['ItemId'].':'.$conf_discount['ItemType'].':1';
            if (!$oUser->addZingXu(-$conf_discount['ZMoney'],$info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }       
        $oUser->saveBonus(array($conf_discount));
      
        $oUser->save();
        $oUserPro->save();
        
        if ($isMoney)
            Zf_log::write_act_log(Controller::$uId,0, 23,'buyDiscount', 0,-$conf_discount['ZMoney']); 
        else Zf_log::write_act_log(Controller::$uId,0, 23,'buyDiscount', -$conf_discount['Money'],0); 
        return array('Error' => Error::SUCCESS);
    }
    
    public function changeEnchantLevel($param)
    {
        $InEquip = $param['InEquip']; // Type , Id
        $OutEquip = $param['OutEquip']; // Type , Id
        $PriceType = $param['PriceType']; //ZMoney, Money
        $MaterialList = $param['MaterialList'];
        
        if(empty($InEquip)|| empty($OutEquip)|| !SoldierEquipment::checkExist($InEquip['Type']) || !SoldierEquipment::checkExist($OutEquip['Type']) || $InEquip['Type'] != $OutEquip['Type'])
            return array('Error'=>Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
        $oStore = Store::getById(Controller::$uId);
        
        $In_Equip = $oStore->getEquipment($InEquip['Type'], $InEquip['Id']) ;       
        $Out_Equip = $oStore->getEquipment($OutEquip['Type'], $OutEquip['Id']) ;  
        if(!is_object($In_Equip)|| !is_object($Out_Equip))  
           return array('Error'=>Error::OBJECT_NULL);
       
        $oldLevel = $Out_Equip->EnchantLevel ;
        
        // check enchant level of 2 equipemt
        if($Out_Equip->EnchantLevel >= $In_Equip->EnchantLevel)
        {
            return array('Error'=>Error::NOT_ACTION_MORE);
        }
        
        // chek cung mau hay ko        
        if($Out_Equip->Color != $In_Equip->Color &&($In_Equip->Color < 5 || $Out_Equip->Color < 5))
        {
            return array('Error'=>Error::NOT_ENOUGH_CONDITION);
        }
        // check Durability
        
        if($Out_Equip->Durability <= 0 || $In_Equip->Durability <= 0)
        {
            return array('Error'=>Error::NOT_ENOUGH_CONDITION);
        }
                   
        $conf_require = Common::getConfig('PowerTinhRequireEnchant',$Out_Equip->getRank());
        $conf_require = $conf_require[$In_Equip->EnchantLevel] ;
        
        if(empty($conf_require))
            return array('Error'=>Error::NOT_LOAD_CONFIG);
        $zmoney = $oUser->ZMoney;    
        $money = $oUser->Money;    
        // kiem tra tien 
        if($PriceType == 'ZMoney')
        {
            $info = '1:changeEnchantLevel:1';
            if(!$oUser->addZingXu(-$conf_require['ZMoney'],$info))
                return array('Error'=>Error::NOT_ENOUGH_ZINGXU);
        }   
        else if($PriceType == 'Money')
        {
            if(!$oUser->addMoney(-$conf_require['Money']))
                return array('Error'=>Error::NOT_ENOUGH_MONEY);
        }
        else
            return array('Error'=>Error::PARAM);
        
        //check powertinh
        $oIngredient = Ingredients::getById(Controller::$uId);
        if(!$oIngredient->usePowerTinh($conf_require['PowerTinh']))
            return array('Error'=>Error::NOT_ENOUGH_ITEM);
        
        $RateBase = Common::getConfig('ChangeEnchantLevel',$In_Equip->getRank(),$Out_Equip->getRank());
        
        // rate tu ngu thach 
        $Rate_M = 0 ;
        if(!empty($MaterialList))
        {
            if(in_array($OutEquip['Type'],array('Armor','Weapon','Helmet')))
                $conf_Enchant = Common::getConfig('EnchantEquipment_Minor',$Out_Equip->getRank());
            else if(in_array($OutEquip['Type'],array('Ring','Bracelet','Necklace','Belt')))
                $conf_Enchant = Common::getConfig('EnchantEquipment_Minor',$Out_Equip->getRank());
            else
                return array('Error'=>Error::TYPE_INVALID);
            $color = ($Out_Equip->Color == 6) ? 5: $Out_Equip->Color;
            $conf_Enchant = $conf_Enchant[$color];
            $conf_Enchant = $conf_Enchant[$In_Equip->EnchantLevel - 1];
            
            $SlotNum = 0 ;
            foreach($MaterialList as $ItemId => $Num)
            {
                if($Num <= 0) continue ;
                if (!(($ItemId <=14 && $ItemId>=1)||($ItemId <=114 && $ItemId>=101)))
                    continue ;
                if (!$oStore->useItem('Material',$ItemId, intval($Num)))
                    return array('Error' => Error :: NOT_ENOUGH_MATERIAL);
                                
                $Rate_M += ($conf_Enchant[$ItemId]*$Num) ;
                
                $SlotNum++;
            }
        }
        
        // ko cho dung qua so luong ngu thach
        if($SlotNum > 8 )
            return array('Error'=>Error::OVER_NUMBER);
        
        $RateSum = $RateBase + $Rate_M ;
        
        // xac dinh ti le
       
        if ($RateSum >= 100)
        {
            // level up and update option
            for ($i = $Out_Equip->EnchantLevel ;$i < $In_Equip->EnchantLevel ; $i++)
            {
                $Out_Equip->levelIncrease(1,false);
            }
            
            //delete option of Equipment input
            for ($i = $In_Equip->EnchantLevel ;$i > 0; $i--)
            {            
                $In_Equip->levelIncrease(-1,false);
            }
                
        }
        else
        {
            return array('Error'=>Error::NOT_ENOUGH_CONDITION);            
        }
        
        $oUser->save();
        $oIngredient->save();
        $oStore->save();
        
        // log 
        $zmoney = $oUser->ZMoney - $zmoney ;    
        $money = $oUser->Money - $money;
        Zf_log::write_act_log(Controller::$uId,0,0,"changeEnchantLevel",$money,$zmoney,$InEquip['Id'],$OutEquip['Id'],$oldLevel,$Out_Equip->EnchantLevel);
        
        return array('Error'=>Error::SUCCESS, 'Equip'=>$Out_Equip);
    }
    
    //mua Item mo khoa trang bi
    
    public function buyOpenkeyItem($param)
    {
        $PriceType = $param['PriceType'] ;
        
        if($PriceType !== 'ZMoney' && $PriceType !== 'Diamond')
            return array('Error'=>Error::PARAM);
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
            
        $conf = Common::getParam('OpenKeyItem');
        
        // luu thong so cu~
      $DiamondDiff = $oUser->Diamond;
      $zMoneyDiff = $oUser->ZMoney;
      
        // kiem tra tien 
        if($PriceType == 'ZMoney')
        {
            $info = '1:buyOpenkeyItem:1';
            if(!$oUser->addZingXu(-$conf['ZMoney'],$info))
                return array('Error'=>Error::NOT_ENOUGH_ZINGXU);
        }   
        else
        {
            if(!$oUser->addDiamond(-$conf['Diamond'],DiamondLog::buyOpenkeyItem))
                return array('Error'=>Error::NOT_ENOUGH_MONEY);
        }
        
        $oStore->addItem(Type::OpenKeyItem,1,1);
             
        $oStore->save();
        $oUser->save();
        
         // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log['OpenKeyItem']))
        {
          $TypeItemId = $conf_log['OpenKeyItem'];
          $DiamondDiff = $oUser->Diamond - $DiamondDiff;
          $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;
          
          Zf_log::write_act_log(Controller::$uId,0,23,'buyOther',0,$zMoneyDiff,$TypeItemId,1,$DiamondDiff,0,1);        
        }
       

         
         
        return array('Error'=>Error::SUCCESS) ;
        
        
    }
    
    // use Openkey for Equipment
    //mua Item mo khoa trang bi
    
    public function useOpenkeyItem($param)
    {
        $Type   = $param['Type'];
        $Id     = $param['Id'];
        
        if(!SoldierEquipment::checkExist($Type) || $Id <= 0 )
            return array('Error'=>Error::PARAM);
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        if(!is_object($oUser))
            return array('Error'=>Error::NO_REGIS);
        
        // kiem tra kho co item do ko
        if(!$oStore->useItem(Type::OpenKeyItem,1,1))
            return array('Error'=>Error::NOT_ENOUGH_ITEM);
            
        $oEquip = $oStore->getEquipment($Type, $Id);
        if(!is_object($oEquip))
            return array('Error'=>Error::OBJECT_NULL);
        
        // check $Durability
        if($oEquip->Durability <= 0 )
            return array('Error'=>Error::OBJECT_NULL);
        // ko cho mo an , mat la ,...
        if(in_array($oEquip->Type,array('Mask','Seal')))
            return array('Error'=>Error::TYPE_INVALID);

        // ko cho mo nhu do ko cho ban tren cho 
        $cansell = Common::getParam('CanSellEquipment');
        if(!in_array($oEquip->Source,$cansell,true))
            return array('Error'=>Error::SOURCE_INVALID);
        
        if($oEquip->IsUsed)
            $oEquip->IsUsed = false ;
        else
            return array('Error'=>Error::ACTION_NOT_AVAILABLE);   
        $oStore->save();
        $oUser->save();
         Zf_log::write_act_log(Controller::$uId,0,20,'useOpenkeyItem',0,0,$oEquip->Type,$oEquip->Rank,$oEquip->Color);    
        return array('Error'=>Error::SUCCESS) ;
        
        
    }
    
    
}

?>
