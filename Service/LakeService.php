<?php

/**
 * Description of LakeService
 *
 * @author ToanTN 
 */
class LakeService 
{    

    public function cleanLake($param)
    {
        $CleanTimes     = $param['CleanTimes'];
        $LakeId         = $param['LakeId'];
        $UserId         = $param['UserId'];
        $uId = Controller::$uId;
         //kiem tra du lieu vao
        if (empty ($CleanTimes)||empty ($UserId)||empty($LakeId)||($LakeId>3)||($LakeId<1))
        {
            // thong bao loi
            return array('Error' => Error :: PARAM);
        }
        
        // lay thong tin user

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            //thong bao loi
            return array('Error' => Error :: NO_REGIS);
        }        

    	// kiem tra be co ton tai hay khong

        $oLake = Lake::getById($UserId, $LakeId);
        if (!is_object($oLake))
        {
            // thong bao loi
            return array('Error'=>  Error::NOT_IS_LAKE);
        }
        // lay muc do ban hien tai cua ho
        $currentDirty = $oLake->getCurrentDirty();

        // kiem tra thanh nang luong cua nguoi choi
        if($currentDirty > 0)
        {
          if ($UserId != Controller::$uId){
	          if (!$oUser->isFriend($UserId))
	  		  {
	  			return array('Error' => Error :: NOT_FRIEND);
	  		  }	 	
          }	
        }

        // get exp config
        $expConfig = Common::getConfig('Experience');
        if(!is_array($expConfig))
        {
             return  array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }
        $expUnit = $expConfig['cleanlake'];
        
        $exp = 0;
        $cleanAmounts =0;
        if ($currentDirty > $CleanTimes)
        {
            $exp = $CleanTimes * intval($expUnit);
            $cleanAmounts += $CleanTimes;
        }
        else
        {
            $exp = $currentDirty *intval($expUnit);
            $cleanAmounts += $currentDirty;
        }
        $EnergyConfig = Common::getConfig('Energy');
        
        if (!$oUser->addEnergy(-$EnergyConfig['cleanlake']*intval($exp/$expUnit)))
        {
            return array('Error' => Error :: NOT_ENOUGH_ENERGY) ;
        }
        
        //$cleanAmounts += $CleanTimes;
        $oUser->addExp($exp);
        $oLake -> updateCleanAmount($cleanAmounts);

        $res = array();
        $res['Error']=Error::SUCCESS;
        $res['Exp']= $exp;
        $res['Num']= $cleanAmounts;
  		               
        if ($cleanAmounts>0){
          $res['Bonus'][] = $oUser->randomActionGift(1); // dua ve loai 1
          $oUser->saveBonus($res['Bonus']);
        }
        else {
          $res['Bonus'] = array();
        }

        $oLake ->save();
        $oUser ->save(); 
          
        //log
        //Zf_log::write_act_log(Controller::$uId, $UserId, 20, 'cleanLake', 0, 0, 0, 0, 0, 0,$cleanAmounts);
		
        return $res;
    }

    /*
     * unlock ho
     *
     */
    public function unlockLake()
    {  	
        // lay thong tin user

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            //thong bao loi
            return array('Error' => Error :: NO_REGIS);
        }

        $uLevel = $oUser->getLevel();
        $uLakeNumb = $oUser->LakeNumb;
        
        // tam thoi de khong cho mua ho 3
    	if ($uLakeNumb >= 2)
        {
          // khong mua duoc them ho
          return array('Error'=>  Error::FULL_LAKE);
        }
        
        /*if ($uLakeNumb > 2)
        {
          // khong mua duoc them ho
          return array('Error'=>  Error::FULL_LAKE);
        }*/
        $uLakeNumb++;
        // get lake config
        $lakeConfig = Common::getConfig('Lake');

        if ($uLevel<$lakeConfig[$uLakeNumb][1]['LevelRequire'])
        {
            // khong du level
          return array('Error'=>  Error::NOT_ENOUGH_LEVEL);
        }

        // kiem tra giay phep mo ho 
        if (!Lake::checkLicense($lakeConfig[$uLakeNumb][1]['License']))
        {
            // khong du giay phep
	        return array('Error'=>  Error::NOT_ENOUGH_LICENSE);
        }
        /*
        // tinh chenh lech
		$moneyDiff = $oUser->Money ;
		$zMoneyDiff = $oUser->ZMoney; 	       
       if (!$oUser->addMoney(-$lakeConfig[$uLakeNumb][1]['Money']))
        {
          // khong du tien
          return array('Error'=>  Error::NOT_ENOUGH_MONEY);
        }
        // cong diem kinh nghiem khi unlock ho
        // $oUser->addExp($lakeConfig[$uLakeNumb][1]['Exp']);
        
        // tinh chenh lech
		$moneyDiff = $oUser->Money - $moneyDiff ;
		$zMoneyDiff = $oUser->ZMoney - $zMoneyDiff ; 
		*/
		// tao ho moi
        $oNewLake = new Lake(Controller::$uId,$uLakeNumb);
        $oNewDeco = new Decoration(Controller::$uId, $uLakeNumb);
        // them background mac dinh 
        $oNewDeco->createDefaultItem($oUser->getAutoId(),Type::BackGround,1); 
        $oUser->unlockLake();
        $oNewLake->save();
        $oUser->save();
        $oNewDeco->save();      
        // log 
       	Zf_log::write_act_log(Controller::$uId,0,20,'unlockLake',0,0,$uLakeNumb);
        
        
        $res = array();
        //$res['LakeList'] 	= $oNewLake;
        $res['Exp'] 		= $oUser->Exp;
        $res['Money'] 		= $oUser->Money;
        $res['Error'] 		= Error::SUCCESS;
       
        return $res;
    }

    /*
     * upgrade lake
     */
    public function upgradeLake($param)
    {
        $LakeId     = $param['LakeId'];

        $uId = Controller::$uId;

        if (empty($LakeId)||($LakeId<1)||($LakeId>3))
        {
            // thong bao loi
            return array('Error'=>  Error::PARAM);
        }
        // lay thong tin user

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            //thong bao loi
            return array('Error' => Error :: NO_REGIS);
        }
        // lay thong tin ho

        $oLake = Lake::getById(Controller::$uId, $LakeId);
        if (!is_object($oLake))
        {
            // thong bao loi
            return array('Error'=>  Error::NOT_IS_LAKE);
        }
        $lakeLevel = $oLake->Level;
        $uLevel = $oUser->getLevel();
        // get lake config
        $lakeConfig = Common::getConfig('Lake');

        if ($lakeLevel>=count($lakeConfig[$LakeId]))
        {
            // khong nang cap ho duoc nua
            return array('Error'=>  Error::CANT_UPGRADE_LAKE);
        }

        if ($uLevel<$lakeConfig[$LakeId][$lakeLevel+1]['LevelRequire'])
        {
          // khong du level
          return array('Error'=>  Error::NOT_ENOUGH_LEVEL);
        }
    	// kiem tra giay phep mo ho 
        if (!Lake::checkLicense($lakeConfig[$LakeId][$lakeLevel+1]['License']))
        {
            // khong du giay phep
	        return array('Error'=>  Error::NOT_ENOUGH_LICENSE);
        }
       /* 
        // thong so truoc khi thay doi
        $money = $oUser->Money ;
        $zmoney = $oUser->ZMoney ;
        
        if (!$oUser->addMoney(- $lakeConfig[$LakeId][$lakeLevel+1]['Money']))
        {
          // khong du tien
          return array('Error'=>  Error::NOT_ENOUGH_MONEY);
        }*/
        // cong diem kinh nghiem khi update ho
        //$oUser->addExp($lakeConfig[$LakeId][$lakeLevel+1]['Exp']);

        // update level ho va money user
        $oLake->updateLevel();
        $oLake->save();
        $oUser->save();
		
        $res = array();
        $res['Exp'] = $oUser->Exp;
        $res['Money'] = $oUser->Money;
        $res['Error'] = Error::SUCCESS;
        
       	// log 
       	// thong so truoc khi thay doi
     	//$difmoney = $oUser->Money - $money;
     	//$difzmoney = $oUser->ZMoney - $zmoney;
     	
        Zf_log::write_act_log(Controller::$uId,0,20,'upgradeLake',0,0,$LakeId,$oLake->Level);
        
        return $res;
    }

     /*
     * lay so luong ca cua cac ho
     */
    public function getTotalFish()
    {
        // lay thong tin user
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            //thong bao loi
            return array('Error' => Error :: NO_REGIS);
        }

        $arr_result['InforLake'] = Lake::getAll(Controller::$uId);
        $arr_result['Error'] =  Error :: SUCCESS;
		return $arr_result;
    }
    
    
    /**
    * Lay toan bo ca linh cua ban
    */
    
    public function getAllSoldier($param)
    {
        $userId = $param['UserId'];
        if (empty($userId))
            $userId = Controller::$uId;
            
        $oFriend = User::getById($userId);
        if (!is_object($oFriend))
            return array('Error' => Error::OBJECT_NULL);
        $arrSoldier = Lake::getAllSoldier($userId,true,true,true);
        
        $oStoreEquip = StoreEquipment::getById($userId);

        return array('Error' => Error::SUCCESS, 'SoldierList' => $arrSoldier, 'EquipmentList' => $oStoreEquip->SoldierList,
                        'MeridianList'=>$oStoreEquip->listMeridian);
    }
    
      /*
     * lay thong tin buff cua cac ho 
     */
    public function getBuffOfAllLake()
    {
        // lay thong tin user
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            //thong bao loi
            return array('Error' => Error :: NO_REGIS);
        }
        $LakeNumb = $oUser->LakeNumb ;
        $ListBuff = array() ;
        for($i=1 ;$i <=$LakeNumb; $i++)
        {
           $oLake = Lake::getById(Controller::$uId,$i);
           if(!is_object($oLake))
           {
               $ListBuff[$i] = array() ;
           }
           else
           {
               $ListBuff[$i] = $oLake->Option ;
           }
        }
        $arr_result['ListBUff'] = $ListBuff ;
        $arr_result['Error'] =  Error :: SUCCESS;
        return $arr_result;
    }
}
?>
