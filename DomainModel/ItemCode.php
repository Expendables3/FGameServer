<?php

class ItemCode extends Model
{
    public $ItemCode = array();//keycode , key config
    public $ConfigItemCode = array();
    
    public function __construct($uId)
    {
        $this->ItemCode['Code'] = array();
        $this->ItemCode['ConfigId'] = array();
        parent::__construct($uId);
    }
    
    public static function getById($uId)
    {
        $data = DataProvider :: get($uId,__CLASS__) ;
        if(!is_object($data))
        {
            $newObject = new ItemCode($uId);
            return  $newObject ;
        }
        return $data ;
    }
    
    
    //ham decode check tu membase
    public function decodeItemCode($Code,$Element = 0)
    {   Debug::log('decodeItemCode0');       
        // kiem tra xem da dung ItemCode nay chua
        if(isset($this->ItemCode['Code'][$Code]))
            return array('Error' => Error::EXIST); 
        Debug::log('decodeItemCode1');
        // kiem tra xem tren he thong code nay da dung hay chua   
        $arr_Info = DataProvider::get($Code,'ConfigItemCode');
        if(empty($arr_Info))
            return array('Error' => Error::WRONG_CODE);
            
        if(is_array($arr_Info))
        {
            $IdConfigGift   = $arr_Info['IdConfig'];
            $UserId         = $arr_Info['UserId'];
        }
        else
        {
            $IdConfigGift = $arr_Info ;
        }

        if($IdConfigGift < 1 )
            return array('Error' => Error::EXIST);
            Debug::log('decodeItemCode2');
        // kiem tra xem da dung IdConfig nay chua    
        if(isset($this->ItemCode['ConfigId'][$IdConfigGift]))
            return array('Error' => Error::EXIST); 
            Debug::log('decodeItemCode3');
        $conf = common::getConfig('ItemCodeContent',$IdConfigGift);
        // kiem tra han su dung code
        if(empty($conf))
            return array('Error' => Error::NOT_LOAD_CONFIG); 
        $today = $_SERVER['REQUEST_TIME'];
        if($conf['FromDay'] > $today || $conf['ToDay'] < $today)
            return array('Error' => Error::EXPIRED); 
        unset($conf['FromDay']);
        unset($conf['ToDay']);
        //check userid
        if(!empty($conf['UserId'])&& Controller::$uId != $conf['UserId'])
            return array('Error' => Error::NOT_ME); 
        
        // luu qua
        $result['Gift'] = $this->saveGiftOfItemCode($conf,$Element);
        Debug::log('decodeItemCode5');
        if($result['Gift'])
        {
            $this->ItemCode['Code'][$Code] = true ;// luu ma 
            $this->ItemCode['ConfigId'][$IdConfigGift] = true ; // luu key qua
        
            // luu lai code da dung tren he thong 
            $data = array() ;
            $data['IdConfig'] = -1;
            $data['UserId'] = Controller::$uId;
            
            DataProvider::set($Code,'ConfigItemCode',$data);
            
            $result['Error'] = Error::SUCCESS;
            //log
            Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,$Code,$IdConfigGift);
        }
        else
            $result['Error'] = Error::WRONG_CODE;
        Debug::log('decodeItemCode6');
        return $result;
        
    }
    
    /*
    //ham decode check tu file config
    public function decodeItemCode11111($Code,$Element = 0)
    {          
        // kiem tra xem da dung ItemCode nay chua
        if(isset($this->ItemCode['Code'][$Code]))
            return array('Error' => Error::EXIST); 
        // lay IdConfig
        $IdConfigGift = Common::getConfig('CodeList',$Code);
        if(empty($IdConfigGift))
            return array('Error' => Error::WRONG_CODE);     
        // kiem tra xem da dung IdConfig nay chua    
        if(isset($this->ItemCode['ConfigId'][$IdConfigGift]))
            return array('Error' => Error::EXIST); 
        
        // kiem tra xem tren he thong code nay da dung hay chua 
        
        $ListCode = DataProvider::get(-111,'ConfigItemCode',$IdConfigGift);
        if(isset($ListCode[$Code]))
            return array('Error' => Error::EXIST);
            
        $conf = common::getConfig('ItemCodeContent',$IdConfigGift);
        // kiem tra han su dung code
        if(empty($conf))
            return array('Error' => Error::NOT_LOAD_CONFIG); 
        $today = $_SERVER['REQUEST_TIME'];
        if($conf['FromDay'] > $today || $conf['ToDay'] < $today)
            return array('Error' => Error::EXPIRED); 
        unset($conf['FromDay']);
        unset($conf['ToDay']);
        //check userid
        if(!empty($conf['UserId'])&& Controller::$uId != $conf['UserId'])
            return array('Error' => Error::NOT_ME); 
        
        // luu qua
        $result['Gift'] = $this->saveGiftOfItemCode($conf,$Element);
        
        if($result['Gift'])
        {
            $this->ItemCode['Code'][$Code] = true ;// luu ma 
            $this->ItemCode['ConfigId'][$IdConfigGift] = true ; // luu key qua
            $uid = Controller::$uId ;
            $ListCode[$Code] = $today."_".$uid ; // luu ma trong toan he thong
            DataProvider::set(-111,'ConfigItemCode',$ListCode,$IdConfigGift);
            
            $result['Error'] = Error::SUCCESS;
            //log
            Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,$Code,$IdConfigGift);
        }
        else
            $result['Error'] = Error::WRONG_CODE;
        
        return $result;
        
    }
    */
    /*
    // ham decode
    public function decodeItemCode2222($Code)
    {
        //tien xu ly 
        $Type = substr($Code,0,4);
        $arr = array();
        $result = array();
        if($Type == 'AAA_') // loai config
        {
            // kiem tra xem da dung ItemCode nay chua
            if(isset($this->ConfigItemCode[$Code]))
                return array('Error' => Error::EXIST); 
            
            $Itemcode_conf= Common::getConfig('ConfigItemCode');
            if(!isset($Itemcode_conf[$Code]))
                return array('Error' => Error::NOT_LOAD_CONFIG); 
            
            $conf = Common::getConfig('Secret',1);
        
            $output = substr($Code,4);
            $output = strrev($output);
            $output = base64_decode($output);
            
             //$Id.$FromTime.$Content.$ToTime.$Id ;
            $len_a = strlen($conf['Id']) + $conf['LenDate'];
            $arr['FromTime']   = substr($output,strlen($conf['Id']),$conf['LenDate']);
            $arr['ToTime']     = substr($output,-$len_a,$conf['LenDate']);   
            $arr['Secret']     = '';
            if($this->Verification($arr,1))
            {
                // luu qua
                $result['Gift'] = $this->saveGiftOfItemCode($Itemcode_conf[$Code]);
                
                if($result['Gift'])
                {
                    // luu ma 
                    $this->ConfigItemCode[$Code] = true ;
                    $result['Error'] = Error::SUCCESS;
                    
                    //log
                    Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,$Code);
                }
                else
                    $result['Error'] = Error::WRONG_CODE;
                
                
                return $result;
            }
            else
                return array('Error' => Error::WRONG_CODE); 
              
        }
        else// loai binh thuong
        {
            $conf = Common::getConfig('Secret',2);

            $len = ceil(strlen($Code)/3);
            $output1 = substr($Code,-$len);
            $output2 = substr($Code,0,-$len);
            $output1 = strrev($output1);
            $output = $output1.$output2 ;
                        
            $output = base64_decode($output);
            $input              = json_decode($output,true);
            $arr['UserType']  = $input['UserType'] - $conf['UserId'];
            $arr['Id']        = $input['Id']         ;
            $arr['FromTime']  = $input['FromTime']   ;
            $arr['Secret']    = $input['Secret']     ;
            $arr['Content']   = $input['Content'] ;
            $arr['ToTime']    = $input['ToTime']     ;
            
            // kiem tra xem da dung ItemCode nay chua
            if(isset($this->ItemCode[$arr['Id']]))
                return array('Error' => Error::EXIST); 
            if(intval($arr['UserType']) != $conf['AllUser'] && intval($arr['UserType']) != Controller::$uId )
                return array('Error' => Error::NOT_ME);      
            if($this->Verification($arr,2))
            {
                // luu qua
                $result['Gift'] = $this->saveGiftOfItemCode($arr['Content']);
                
                if($result['Gift'])
                {
                    $this->ItemCode[$arr['Id']] = true ;  // luu ma 
                    $result['Error'] = Error::SUCCESS;
                    //log
                    Zf_log::write_act_log(Controller::$uId,0,20,'ItemCode',0,0,$arr['Id']);
                }
                else
                    $result['Error'] = Error::WRONG_CODE;
                
                return $result;
            }
            else
                return array('Error' => Error::WRONG_CODE); 
        }
        
    }
    */
    /*public function Verification($arr,$type)
    {
        $conf = Common::getConfig('Secret',$type);
        if(empty($conf))
            return false ;
        if($arr['Secret'] != $conf['Secret'] )
            return false;
        $nowTime = date('Ymd',$_SERVER['REQUEST_TIME']);
        if($nowTime <  $arr['FromTime'] || $nowTime >  $arr['ToTime'])
            return false ;
        return true ;
    }*/
    
    // ham luu qua
    public function saveGiftOfItemCode($conf,$Element_1 = 0)
    {
        $arr_gift = array('Normal'=>array(),'Special'=>array());
        $oUser  = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        
        foreach($conf as $key => $arr)
        {
            if(empty($arr)) continue ;
            $Element = $Element_1 ;           
            if(SoldierEquipment::checkExist($arr['ItemType']))
            {
                $Element = empty($Element_1)?$arr['Element']:$Element_1 ;
                $Element = empty($Element)?rand(1,5):$Element ;    
                $oE = Common::randomEquipment($oUser->getAutoId(),intval($arr['Rank']),intval($arr['Color']),intval($arr['Source']),$arr['ItemType'],intval($arr['Enchant']),$Element,intval($arr['Option']));
                if(!empty($arr['Bonus']))
                {
                    $BonusOption = array();
                    foreach($arr['Bonus'] as $index => $arr_bonus)
                    {
                        if(empty($arr_bonus))
                            continue;
                        if(!in_array($arr_bonus['Name'],array('Vitality','Defence','Critical','Damage'),true))  
                            continue;
                        if(empty($arr_bonus['Num']))
                            continue;
                        $BonusOption[] = array($arr_bonus['Name']=>intval($arr_bonus['Num']));
                    }
                    $oE->bonus = $BonusOption ;
                }
                $oStore->addEquipment($oE->Type,$oE->Id,$oE);
                
                Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oE);
                
                $arr_gift['Special'][$oE->Id]= $oE ;
            }
            
            if(in_array($arr['ItemType'],array(Type::AllChest,Type::EquipmentChest,Type::JewelChest),true))
            {
                $Element = empty($Element_1)?$arr['Element']:$Element_1 ;
                for($i = 0; $i < $arr['Num']; $i ++)
                {                                                                          
                    $EquipSet = Common::getConfig('ChestGift', $arr['ItemType'], $arr['Rank']);
                    $EquipSet = $EquipSet[$arr['Color']];
                    $EquipBasic = $EquipSet[array_rand($EquipSet)];
                    $oEquip = Common::randomEquipment($oUser->getAutoId(), $arr['Rank'],  $arr['Color'], intval($arr['Source']), $EquipBasic['ItemType'], $arr['Enchant'], $Element, $arr['Option']);
                    $oStore->addEquipment($oEquip->Type,$oEquip->Id,$oEquip);
                    
                    Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip); 
                    
                    $arr_gift['Special'][$oEquip->Id] = $oEquip;
                }
            }           
            
            if(in_array($arr['ItemType'],array(SoldierEquipment::Seal),true))
            {
                $oseal = new Seal('Seal',$oUser->getAutoId(),$arr['Rank'],$arr['Color']);
                $oStore->addEquipment($oseal->Type,$oseal->Id,$oseal);
                
                Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oseal); 
                
                $arr_gift['Special'][$oseal->Id]= $oseal ;
            }
            
            if(in_array($arr['ItemType'],array(Type::OceanAnimal,Type::OceanTree,Type::Other),true))
            {
                $oItem = new Item($oUser->getAutoId(),$arr[Type::ItemType],$arr[Type::ItemId]);
                $oStore->addOther($arr[Type::ItemType],$oItem->Id,$oItem); 
                $oStore->save();
                
                $arr_gift['Special'][$oItem->Id]= $oItem ;
            }
            
            if(in_array($arr['ItemType'],array(Type::Soldier),true))
            {
                $TypeList = array(1=>'Draft',2=>'Paper',3=>'GoatSkin',4=>'Blessing');
                if(isset($TypeList[$arr['Rank']]))
                {   
                    $Element = empty($Element_1)?$arr['Element']:$Element_1 ;
                    $Element = empty($Element)?rand(1,5):$Element ;
                    $oStore->createSoldierByRecipe($TypeList[$arr['Rank']],$Element,SoldierType::MATE,1);
                    
                    $arr['ItemId']  = $Element ;
                    
                    $arr_gift['Normal'][] = $arr ;
                }              
                               
                
            }
            
            if(in_array($arr['ItemType'],array(Type::Gem),true))
            {   
                $Element = empty($Element_1)?$arr['Element']:$Element_1 ;
                $Element = empty($Element)?rand(1,5):$Element ;
                
                $oStore->addGem($Element,$arr[Type::ItemId],$arr['Day'],$arr['Num']); 
                
                $arr['Element'] = $Element ;              
                $arr_gift['Normal'][] = $arr ;
            }           
            
            if(in_array($arr['ItemType'],array(Type::Exp,Type::Money,Type::ZMoney,Type::EnergyItem,Type::License,Type::GodCharm,Type::Material,Type::RankPointBottle)) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            if(in_array($arr['ItemType'],array(Type::Ginseng,Type::RecoverHealthSoldier,BuffItem::Samurai,BuffItem::BuffExp,BuffItem::BuffMoney,BuffItem::BuffRank,BuffItem::Resistance,BuffItem::StoreRank,BuffItem::Dice)) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(FormulaType::Draft,FormulaType::Blessing,FormulaType::GoatSkin,FormulaType::Paper)) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(Type::PowerTinh,Type::OccupyToken,Type::VipTag),true) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(Type::Diamond),true) )
            {
                $oUser->addDiamond($arr['Num'],DiamondLog::ItemCode);
                $arr_gift['Normal'][] = $arr ;
            }
			
			if(in_array($arr['ItemType'],array("Hammer"),true) )
			{
				$giftList[0] = $arr;
				switch($giftList[0]['ItemId'])
				{
					case 1:
						$giftList[0]['ItemType'] = 'HammerWhite';						
						break;
					case 2:
						$giftList[0]['ItemType'] = 'HammerGreen';						
						break;
					case 3:
						$giftList[0]['ItemType'] = 'HammerYellow';						
						break;
					case 4:
						$giftList[0]['ItemType'] = 'HammerPurple';						
						break;
				}
				$giftList[0]['ItemId'] = 1;
				$oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $giftList[0] ;
			}
            
            // huy hieu , loai mau , level, so luong , Id
            $arrQuartzType = Common::getConfig('General', 'QuartzTypes'); 
            if(in_array($arr['ItemType'],$arrQuartzType,true))
            {   
                for($i = 1 ; $i <= $arr["Num"];$i++)
                {
                    $Id = 0  ;
                    $Id = $oUser->getAutoId();
                    $oQuartz = new Quartz($Id, $arr['ItemId'], $arr['ItemType']);
                    $oQuartz->Level = !empty($arr['Rank'])? intval($arr['Rank']):1 ;
                    $oStore->addQuartz($arr['ItemType'], $Id, $oQuartz);
                    $arr_gift['Special'][$Id] = $oQuartz;   
                }           
            }
            
            if($arr['ItemType'] == 'AccPoint') {
                $oAccumulationPoint = AccumulationPoint::getById(Controller::$uId);                
                $oAccumulationPoint->setPoint(intval($arr["Num"]));
                $oAccumulationPoint->save();                
            }            
        }
        
        $oStore->save();
        $oUser->save();
        
        return $arr_gift ;
    }
    
    /*
    // ham luu qua
    public function saveGiftOfItemCode2222($conf)
    {
        if(empty($conf))
            return false ;
        $arr_gift = array('Normal'=>array(),'Special'=>array());
        $oUser  = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        foreach($conf as $key => $arr)
        {
            if(empty($arr)) continue ;
            if(SoldierEquipment::checkExist($arr['ItemType']))
            {
                $oE = Common::randomEquipment($oUser->getAutoId(),intval($arr['Level']),intval($arr['Color']),intval($arr['Source']),$arr['ItemType'],intval($arr['Enchant']),intval($arr['Element']),intval($arr['NumOpt']));
                if(!empty($arr['Bonus']))
                {
                    $oE->bonus = $arr['Bonus'];
                }
                $oStore->addEquipment($oE->Type,$oE->Id,$oE);
                $arr_gift['Special'][$oE->Id]= $oE ;
            }
            
            if(in_array($arr['ItemType'],array(SoldierEquipment::Seal),true))
            {
                $oseal = new Seal('Seal',$oUser->getAutoId(),$arr['Level'],$arr['Color']);
                $oStore->addEquipment($oseal->Type,$oseal->Id,$oseal);
                $arr_gift['Special'][$oseal->Id]= $oseal ;
            }
            
            if(in_array($arr['ItemType'],array(Type::OceanAnimal,Type::OceanTree,Type::Other),true))
            {
                $oItem = new Item($oUser->getAutoId(),$arr[Type::ItemType],$arr[Type::ItemId]);
                $oStore->addOther($arr[Type::ItemType],$oItem->Id,$oItem); 
                $oStore->save();
                
                $arr_gift['Special'][$oItem->Id]= $oItem ;
            }
            
            if(in_array($arr['ItemType'],array(Type::Soldier),true))
            {
                $oStore->createSoldierByRecipe($arr['ItemType'],$arr['ItemId'],SoldierType::MATE,1);
                               
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(Type::Gem),true))
            {   
                $oStore->addGem($arr['Element'],$arr[Type::ItemId],$arr['Day'],$arr['Num']);               
                $arr_gift['Normal'][] = $arr ;
            }           
            
            if(in_array($arr['ItemType'],array(Type::Exp,Type::Money,Type::ZMoney,Type::EnergyItem,Type::License,Type::GodCharm,Type::Material,Type::RankPointBottle)) )
            {
				$giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            if(in_array($arr['ItemType'],array(Type::Ginseng,Type::RecoverHealthSoldier,BuffItem::Samurai,BuffItem::BuffExp,BuffItem::BuffMoney,BuffItem::BuffRank,BuffItem::Resistance,BuffItem::StoreRank)) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(FormulaType::Draft,FormulaType::Blessing,FormulaType::GoatSkin,FormulaType::Paper)) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(Type::PowerTinh),true) )
            {
                $giftList[0] = $arr ;
                $oUser->saveBonus($giftList);
                $arr_gift['Normal'][] = $arr ;
            }
            
            if(in_array($arr['ItemType'],array(Type::Diamond),true) )
            {
                $oUser->addDiamond($arr['Num'],DiamondLog::ItemCode);
                $arr_gift['Normal'][] = $arr ;
            }
        }
        
        $oStore->save();
        $oUser->save();
        
        return $arr_gift ;
    }
    */
    
}
