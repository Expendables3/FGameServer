<?php
class AdminTool
{
    public function UpdateInventory($uId,$type,$arr)
    {
        if($uId < 1)
        {
            return false ;
        }
        $ItemAll = &Common::getConfig('ItemAll');
        $ItemType = $ItemAll[$type];
        
        $oUser = User::getById($uId);
          if(!is_object($oUser)) return false ;
        $oStore = Store::getById($uId);
            if(!is_object($oStore)) return false ;
        
        $success = false ;       
        switch($type)
        {
            case 'Money':
                $oUser->addMoney($arr['Num']);
                $oUser->save() ;
                $success = true ; 
                break;
            case 'ZMoney':
                $oUser->addZingXu($arr['Num'],'SaveBonus');
                $oUser->save() ;
                $success = true ;
                break;
            case 'Exp':
                $oUser->addExp($arr['Num']);
                $oUser->save(); 
                $success = true ;
                break;
            case 'SuperFish':
                {
                   $paramconf = Common::getParam('SpartaFamily');
                   if(isset($paramconf[$arr['ItemType']]))
                   {
                        $autoId = $oUser->getAutoId();
                        $Option = array('Money'=>$arr['Money'],'Exp'=>$arr['Exp'],'Time'=>$arr['Time']);
                        $oSparta = new Sparta($autoId,$Option,$arr['Expired'],$arr['ItemType']) ;
                        $oStore->addOther($arr['ItemType'], $oSparta->Id,$oSparta);
                        $oStore->save();
                        $oUser->save();
                        
                        $success = true ;
                   }     
                   break; 
                }
            case 'Fish':
                break;
            case 'Decoration':
                if(in_array($arr['ItemType'],array(Type::OceanTree,Type::OceanAnimal,Type::Other,Type::BackGround),true))
                {
                    $autoId = $oUser->getAutoId();
                    $oDeco = new Item($autoId,$arr['ItemType'],$arr['ItemId']);
                    $oStore->addOther($arr['ItemType'],$oDeco->Id,$oDeco);   
                    $oStore->save();
                    $oUser->save();
                    
                    $success = true ;
                }     
                break;
            case 'Item':
                if(isset(Type::$arr['ItemType']))
                {
                    $conf = Common::getConfig($arr['ItemType'],$arr['ItemId']);
                    if(!empty($conf))
                    {
                       $oStore->addItem($arr['ItemType'],$arr['ItemId'],$arr['Num']); 
                       $oStore->save() ;
                       
                       $success = true ;
                    }     
                }
                break;
            case 'Equipment':
                if(SoldierEquipment::checkExist($arr['ItemType']))
                {
                    $autoId = $oUser->getAutoId();
                    $oEquip = Common::randomEquipment($autoId,intval($arr['Level']),intval($arr['Color']),intval($arr['Source']),$arr['ItemType'],intval($arr['Enchant']),intval($arr['Element']),intval($arr['NumOption']));
                    
                    
                    $bonus = array();
                    for($i=0;$i<5;$i++)
                    {
                        $name = 'NameOp'.($i+1) ;
                        $value = 'ValueOp'.($i+1) ;
                        //Debug::log($arr[$name].'_'.$arr[$value]) ;
                        if(!empty($arr[$name]) && intval($arr[$value]) > 0 )
                            $bonus[$i][$arr[$name]] = intval($arr[$value]);
                        
                    }    
                    $oEquip->bonus = $bonus ;
                    $oStore->addEquipment($oEquip->Type, $oEquip->Id,$oEquip); 
                    $oStore->save();
                    $oUser->save(); 
                     
                    $success = true ;                 
                }
                break;
            case 'Soldier':
                break;
            case 'ReputationLevel':
                 {
                     if(isset($arr['Level']) && $arr['Level'] > 0)
                     {
                         $oUser->ReputationLevel = intval($arr['Level']); 
                     }
                     if(isset($arr['Point']) && $arr['Point'] > 0)
                     {
                         $oUser->ReputationPoint = intval($arr['Point']); 
                     }
                     if(isset($arr['resetquest']) && $arr['resetquest'] > 0)
                     {
                         $oUser->initReputationQuest();
                     }
                     $success = true ;     
                     $oUser->save();
                 }
                break;
            default:
                break;
        }     

        if($success)
            $this->InsertAdminLog('Add',$uId,$type,$arr['ItemType'],$arr['ItemId'],$arr['Num']);

        return $success ;

    }
    
    // update Info of Item of User
    public function EditItem($uId,$type,$arr)
    {
       
    }
    
    public function updateStore($uId)
    {
        if(!empty($uId))
        {
             $oStore = Store::getById($uId);
             if(!is_object($oStore)) 
                return false ;
	        foreach($oStore->Items as $id => $arrr)
	        {
		        if($id == null)
		        {
			        unset($oStore->Items[$id]); 
		        }
	        }
            foreach($oStore->Fish as $id => $arrr)
            {
                if($id == null)
                {
                    unset($oStore->Fish[$id]); 
                }
            }
            foreach($oStore->AllOther as $id => $arrr)
            {
                if($id == null)
                {
                    unset($oStore->AllOther[$id]); 
                }
            }
            foreach($oStore->BuffItem as $id => $arrr)
            {
                if($id == null)
                {
                    unset($oStore->BuffItem[$id]); 
                }
            }
            foreach($oStore->Gem as $id => $arrr)
            {
                if($id == null)
                {
                    unset($oStore->Gem[$id]); 
                }
            }
            foreach($oStore->Quartz as $id => $arrr)
            {
                if($id == null)
                {
                    unset($oStore->Quartz[$id]); 
                }
            }
            
		    $oStore->save();
            return true ; 
        }
        else
            return false ;
       
        
    }
    
    // ham thuc hien xoa cac Item cua user
    public function DeleteItem($uId,$type,$arr)
    {
        if($uId < 1)
        {
            return false ;
        }
        
        $ItemAll = &Common::getConfig('ItemAll','Delete');
        $ItemType = $ItemAll[$type];
        
        $oUser = User::getById($uId);
          if(!is_object($oUser)) return false ;
        $oStore = Store::getById($uId);
            if(!is_object($oStore)) return false ;
            
        $success = false ;       
        switch($type)
        {
            case 'Money':
            case 'ZMoney':
            case 'Exp':
                break;
            case 'SuperFish':
                {
                   if(empty($arr['Id'])|| empty($arr['ItemType']))
                        return $success;
                   $paramconf = Common::getParam('SpartaFamily');
                   if(!in_array($arr['ItemType'],$paramconf,true))
                        return $success;
                   if(empty($arr['IsLake'])) // o trong kho
                       unset($oStore->AllOther[$arr['ItemType']][$arr['Id']]);
                   else
                   {
                        $oDeco =  Decoration :: getById($uId,$arr['IsLake']);
                        unset($oDeco->SpecialItem[$arr['ItemType']][$arr['Id']]) ;
                        $oDeco->save();
                   }
                   $oStore->save(); 
                   $success = true ;
                   break; 
                }
            case 'Fish':
                break;
            case 'Decoration':
                if(in_array($arr['ItemType'],array(Type::OceanTree,Type::OceanAnimal,Type::Other,Type::BackGround),true)&& !empty($arr['Id']))
                {
                  if(empty($arr['IsLake'])) // o trong kho
                       unset($oStore->AllOther[$arr['ItemType']][$arr['Id']]);
                   else
                   {
                        $oDeco =  Decoration :: getById($uId,$arr['IsLake']);
                        unset($oDeco->ItemList[$arr['Id']]) ;
                        $oDeco->save();
                   }
                   $oStore->save(); 
                   $success = true ;
                }     
                break;
            case 'Item':
                if(!empty($arr['ItemType']) && !empty($arr['ItemId']) )
                {
                   $oStore->useItem($arr['ItemType'],$arr['ItemId'],$arr['Num']); 
                   $oStore->save() ;
                   $success = true ;  
                }
                break;
            case 'EquipmentInStore':
                if(SoldierEquipment::checkExist($arr['ItemType']))
                {
                    unset($oStore->Equipment[$arr['ItemType']][$arr['Id']]); 
                    $oStore->save();                     
                    $success = true ;                 
                }
                break; 
            case 'EquipmentInSoldier':
                if(SoldierEquipment::checkExist($arr['EquipmentType'])&& !empty($arr['LakeId'])&& !empty($arr['SoldierId'])&& !empty($arr['EquipmentId']))
                {
                    $oLake = Lake::getById($uId,$arr['LakeId']);
                    if(!is_object($oLake))
                    //Debug::log('$oLake loi rois');
                      $oSoldlier = $oLake->getFish($arr['SoldierId']);
                      $oE = $oSoldlier->Equipment[$arr['EquipmentType']][$arr['EquipmentId']];
                if(is_object($oE))
                {
                    unset($oSoldlier->Equipment[$arr['EquipmentType']][$arr['EquipmentId']]);
                }
                    else
                    {
                        return $success ;
                    }
                    $oLake->save();                     
                    $success = true ;                
                }
                break;
            case 'Soldier':
                break;
            case 'EquipmentFollowCodition':
            {
                foreach($oStore->Equipment as $type => $arr_e)
                {
                    if(!empty($arr['ItemType']))
                    {
                       if($type != $arr['ItemType'] )
                            continue ;
                    }
                    foreach($arr_e as $id => $oEquip)
                    {
                        if(!is_object($oEquip))
                            continue ;
                        if(!empty($arr['Rank']))
                        {
                            $Rank = round($oEquip->Rank%100);
                            //Debug::log($Rank);
                            if($Rank != intval($arr['Rank']))
                                continue ;
                        }
                        if(!empty($arr['Color']))
                        {
                            if($oEquip->Color != intval($arr['Color']))
                                continue ;
                        }
                        
                        unset($oStore->Equipment[$type][$id]); 
                        
                    }

                }                       
                $oStore->save();                     
                $success = true ;                 
            }
            break;
            default:
                break;
        }     

        if($success)
            $this->InsertAdminLog($uId,$type,$arr['ItemType'],$arr['ItemId'],$arr['Num']);

        return $success ;
        
    }
    
    public function ResetUser($uId)
    {
        global $CONFIG_DATA ;
        $allData = array();
        $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3));  

        //Model::$appKey = "devpre";
        foreach($CONFIG_DATA['Key'] as $akey => $oKey)
        {
            if (isset($multiObj[$akey]))
            {
                foreach($multiObj[$akey] as $index => $mid)
                {
                    DataProvider::delete($uId,$akey,$mid);        
                }
            }
            else
            {
                DataProvider::delete($uId,$akey);        
            }
        }
        /*

        User::del($uId);
        UserProfile::del($uId);

        Decoration::del($uId,1);
        Decoration::del($uId,2);

        Lake::del($uId,1);
        Lake::del($uId,2);
        Lake::del($uId,3);

        Store::del($uId);

        Quest::del($uId) ;

        DataProvider::delete($uId,'MiniGame');
        DataProvider::delete($uId,'Event');
        DataProvider::delete($uId,'FishWorld');
        DataProvider::delete($uId,'TrainingGround');

        DataProvider::delete($uId,'Diary');

        DataProvider::delete($uId,'MailBox');
        DataProvider::delete($uId,'GiftBox');
 
        UserProfile::del($uId);

        $oWorld = FishWorld::getById($uId);
        if(is_object($oWorld))
        {
            $oWorld->SeaList    = array();
            $oWorld->SeaNum     = 0 ;
            $oWorld->save();
        }
        
        PowerTinhQuest::del($uId);
        StoreEquipment::del($uId);
    
       DataProvider::delete($uId, 'Event');
       
       DataProvider :: delete($uId,'StoreEquipment');
       DataProvider :: delete($uId,'TrainingGround') ;
       */
    }

/*
 	public function UpdateInventory($uId,$type,$id,$number=1,$isRepertory=false,$option,$expired = 1)
    {
        if($uId < 1)
        {
            return false ;
        }
        $success = false ;
        $ItemAll = &Common::getConfig('ItemAll');
        $ItemType = $ItemAll[$type];
        if($type > 100 )
        {
            $configItem =&Common::getConfig($ItemType);
            if(isset($configItem[$id]))
            {
              $oStore = Store::getById($uId);
              $oUser = User::getById($uId)  ;
              
              if(in_array($ItemType,array(Type::OceanTree,Type::OceanAnimal,Type::Other),true))
              {
                  $oItem = new Item($oUser->getAutoId(),$ItemType,$id);
                  $oStore->addOther($ItemType,$oItem->Id,$oItem);   
              }
              else
              {
                 $oStore->addItem($ItemType, $id, $number);   
              }
              
              
              $oStore->save();    
              $success = true ;
            }
            else
            {
              $success = false ;        
            }
        }
        else if(($type > 0) && ($type < 100))
        {
          $oUser = User::getById($uId);
          if(!is_object($oUser)) return false ;
          
          $success = true ; 
          switch($ItemType)
          {
            case 'Money': 
                  $oUser->addMoney($number);
				  $oUser->save() ; 
                  break;
            case 'ZMoney':
                   $oUser->addZingXu($number,'SaveBonus');
                   $oUser->save() ; 
                  break;
            case 'Exp' :
                   $oUser->addExp($number);
                    $oUser->save() ; 
                   break;
            case 'Energy' :
                   $oUser->addEnergy($number);
                    $oUser->save() ; 
                   break;
            case 'Sparta':                  
                  $Option = array();
                  foreach($option as $key => $value)
                  {
                    if(empty($value))
                    {
                      continue;
                    }
                    $Option[$key] = $value;
                  }
                  $autoId = $oUser->getAutoId();
                  $oSparta = new Sparta($autoId,$Option,$expired) ;
                  $oStore = Store::getById($uId);
                  $oStore->addOther(Type::Sparta, $oSparta->Id,$oSparta);
                  $oStore->save();
                  $oUser->save();

                   break;
            case 'Batman':
                  $Option = array();
                  foreach($option as $key => $value)
                  {
                    if(empty($value))
                    {
                      continue;
                    }
                    $Option[$key] = $value;
                  }
                  $autoId = $oUser->getAutoId();
                  $oBatman = new Sparta($autoId,$Option,$expired) ;
                  $oStore = Store::getById($uId);
                  $oStore->addOther(Type::Batman, $oBatman->Id,$oBatman);
                  $oStore->save();
                  $oUser->save();

                  break ;
            case 'Spiderman':
                  $Option = array();
                  foreach($option as $key => $value)
                  {
                    if(empty($value))
                    {
                      continue;
                    }
                    $Option[$key] = $value;
                  }
                  $autoId = $oUser->getAutoId();
                  $oSpiderman = new Sparta($autoId,$Option,$expired) ;
                  
                  $oStore = Store::getById($uId);
                  $oStore->addOther(Type::Spiderman, $oSpiderman->Id,$oSpiderman);
                  $oStore->save();
                  $oUser->save();

                  break ; 
            case 'Swat':
                  $Option = array();
                  foreach($option as $key => $value)
                  {
                    if(empty($value))
                    {
                      continue;
                    }
                    $Option[$key] = $value;
                  }
                  $autoId = $oUser->getAutoId();
                  $oSwat = new Sparta($autoId,$Option,$expired) ;
                  $oStore = Store::getById($uId);
                  $oStore->addOther(Type::Swat, $oSwat->Id,$oSwat);
                  $oStore->save();
                  $oUser->save();

                  break ; 
                  
                  
            case Type::NoelFish :
                    $option = array(OptionFish::EXP=>20,OptionFish::MONEY=>20,OptionFish::TIME=>20);
                    $autoId = $oUser->getAutoId();
                    $oFirework = new Sparta($autoId,$option,5,Type::NoelFish);
                    $oStore = Store::getById($uId);
                    $oStore->addOther(Type::NoelFish, $oFirework->Id,$oFirework);
                    
                    
                    $oLake = Lake :: getById($uId,1) ;				
				  $oOther = $oStore->getOther(Type::NoelFish,$autoId );
				  // buff vao ho ca option
				  $oLake->buffToLake($oOther->Option,true);
				  //add vao ho 
					  $oOther->LastTimeGetGift = $_SERVER['REQUEST_TIME'];
				  $oDecorate = Decoration::getById($uId,1);
				  $oDecorate->addSpecialItem(Type::NoelFish,$autoId ,$oOther);
				  //update Start Time cho Sparta
				  $oDecorate->updateTimeSparta(Type::NoelFish,$autoId);

				  // xoa or tru doi tuong trong kho
				  if(!$oStore->useOther(Type::NoelFish,$autoId))
				  {
					return array('Error' => Error :: OVER_NUMBER ) ;
				  }

				$oStore->save();
				$oLake->save();
                  $oDecorate->save();
                  $oUser->save();
      
                break ;  
                   
            case 'EnergyMachine':
            
                  $success = true ;  
                  break ; 
            case 'BabyFish':                                 
                  $autoId     = $oUser->getAutoId();
                  $Option = array();
                  foreach($option as $key => $value)
                  {
                    if(empty($value))
                    {
                      continue;
                    }
                    $Option[$key] = $value;
                  }
                  
                  $sex        = rand(0,1);
                  $color      = rand(1,2);
                  
                  if(empty($Option)) // ca thuong
                  {
                     $oFish = new Fish($autoId,$id,$sex,$color);
                     
                  }
                  else  // ca quy
                  {
                      $oFish = new RareFish($autoId,$id,$sex,$Option,$color); 
                  } 
                  
                  $oStore = Store::getById($uId);
                  $oStore->addFish($oFish->Id,$oFish); 
                  $oUser->save();
                  $oStore->save();
  
                  break ;
            case 'Key':
                  break ; 
            case 'Icon':
                  break ;   
            default :
                $success = false ;
                break;
          }
        }


        if($success)
		    $this->InsertAdminLog($uId,$type,$id,$number,$isRepertory,$option);

        return $success ;

    }
*/
    /**
    * Ham log lai hanh dong chinh sua cua admin
    *
    * @param mixed $uId
    * @param mixed $type
    * @param mixed $id
    * @param mixed $number
    * @param mixed $isRepertory
    * @param mixed $adminId
    */
    public function InsertAdminLog($TypeLog,$uId,$type,$id,$number,$isRepertory,$option)
    {
        $adminId = Controller::$uId ;
        $content = $uId.'_'.$TypeLog.'_'.$type.'_'.$id.'_'.$number.'_'.$isRepertory.'_'.$option.'_'.$adminId.'_'.$_SERVER["REMOTE_ADDR"].'_'.date('d/m/y',time());
        $this->write_log_file($content);
    }
    // add comment
    public function InsertComment($Comment)
    {
        $adminId = Controller::$uId ;
        $content = date('d/m/y',time()).'_'.$_SERVER["REMOTE_ADDR"].':'.$Comment;
        $this->write_log_file($content);
    }
    public function write_log_file($somecontent)
    {
        $filename = "../log/gmtool.txt";
        if(file_exists($filename))
        {
            if (is_writable($filename)) {
                if (!$handle = fopen($filename, 'a')) {
                     exit;
                }
                // Write $somecontent to our opened file.
                if (fwrite($handle, $somecontent."\n") === FALSE) {
                    exit;
                }
                fclose($handle);
            }
        }
        else
        {
            if (is_writable($filename)) {
                if (!$handle1 = fopen($filename, 'w')) {
                     exit;
                } 
                // Write $somecontent to our opened file.
                if (fwrite($handle1, $somecontent." | ") === FALSE) {
                    exit;
                }
                fclose($handle1);
            }
        }
    }

}
