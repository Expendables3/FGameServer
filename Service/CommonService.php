<?php
class CommonService
{
    /**
    * Ham mo hop qua tang nang luong o nha` minh 
    * 
    * @param array : $param  null
    */
    
    private function openEnergyBox()
    {
        $oUser = User :: getById(Controller::$uId) ;

        if (!is_object($oUser))
        {
            return array('Error' => Error::NO_REGIS) ;
        }
        
        if(!$oUser->openEnergyBox())
        {
           return array('Error' => Error::NOT_ENOUGH_TIME) ; 
        }
        
        $oUser->save();
        
        
       return array('Error' => Error::SUCCESS) ;   
    }
    
    
    /**
    * Bonus qua tang nang luong o nha` ban be
    * 
    * @param int : FriendId  : uId cua ban be minh mo hop 
    */
    
    public function bonusEnergyBox($param)
    {
       $FriendId =  $param['FriendId'];
       if(empty($FriendId) || $FriendId == Controller::$uId)
       {
         return $this->openEnergyBox() ;  
       }
       
        $oUser = User :: getById(Controller::$uId) ;

        if (!is_object($oUser))
        {
            return array('Error' => Error::NO_REGIS) ;
        }
        
      if(!$oUser->isFriend($FriendId))
       return array('Error' => Error::NOT_FRIEND) ;
       
       $oFriend = User :: getById($FriendId);
       if(!is_object($oFriend))
        return array('Error' => Error::NOT_FRIEND) ;
        
       if(!$oFriend->checkEnergyBox())
       {
         return array('Error' => Error::NOT_ENOUGH_TIME) ;  
       }

       $oUserProfile = UserProfile::getById(Controller::$uId);
       if(!$oUserProfile->bonusEngeryBox($FriendId))
        return array('Error' => Error::GOT_GIFT) ;
        
       $oUser->addEnergy(Common::getParam(PARAM::FriendEnergyBonus));
       $oUserProfile->save();
       $oUser->save();
             
       return array('Error' => Error::SUCCESS) ;
    }
    
    /**
    * get Daily Energy
    * @author hieupt
    * 12/09/2011
    */
    
    public function getDailyEnergy($param)
    {
       $FriendId = intval($param['FriendId']);
       if (empty($FriendId))
        return array('Error' => Error::PARAM);
       
       $oUser = User::getById(Controller::$uId) ;
       if(!$oUser->isFriend($FriendId))
        return array('Error' => Error::NOT_FRIEND) ;
       
       $oUserProfile = UserProfile::getById(Controller::$uId);
       $arrGift = $oUserProfile->getDailyEnergy($FriendId);
       if(!$arrGift)
        return array('Error' => Error::GOT_GIFT) ;
       
       $oUser->saveBonus($arrGift); 
       
       $oUserProfile->save();
       $oUser->save();
       
       return array('Error' => Error::SUCCESS, 'Bonus' => $arrGift);
        
    }
    
    public function upgradeSkill($param)
    {
        $skill = $param['Skill'];
        if(!Skill::check($skill))
           return array('Error' => Error :: PARAM) ;
        
        $oUser = User :: getById(Controller::$uId) ;

        if (!is_object($oUser))
        {
            return array('Error' => Error::NO_REGIS) ;
        }
        
        $SkillConfig = Common::getConfig($skill,$oUser->Skill[$skill]['Level'] + 1);
        $Skill__Conf = Common::getConfig($skill,$oUser->Skill[$skill]['Level']); 
        if(!is_array($SkillConfig)||!is_array($Skill__Conf) )
         return array('Error' => Error::MAX_SKILL) ;
         
        if($oUser->Level < $SkillConfig['LevelRequire'])
         return array('Error' => Error::NOT_ENOUGH_LEVEL) ;
         
        
        if($oUser->Skill[$skill]['Mastery'] < $Skill__Conf['MasteryRequire'])
         return array('Error' => Error::NOT_ENOUGH_EXP) ;
        
        $oUser->upgradeSkill($skill);
        $oUser->saveBonus($SkillConfig['Bonus']);
        $oUser->save();
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log[$skill]))
        {
          $TypeItemId = $conf_log[$skill];
        }
        Zf_log::write_act_log(Controller::$uId,0,20,'UpgradeSkill',0,0,intval($TypeItemId),intval($oUser->Skill[$skill]['Level']));
        
        $conf_log = Common::getConfig('LogConfig');
        $skillType = $conf_log[$skill];
        
        zf_log::write_act_log(Controller::$uId ,0, 30 ,'mixFishSkill',0,0 ,  intval($skillType) , intval($oUser->Skill[$skill]['Level']));
              
        
        return array('Error' => Error :: SUCCESS) ;
    }
    
    /** 
    * use petrol for energyMachine
    * 
    */
    
    public function usePetrol($param){
        $type = $param['Type'];
        if (empty($type))
          return array('Error' => Error::PARAM);
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
          return array('Error' => Error::NO_REGIS) ;
        
        $oMachine = $oUser->SpecialItem['EnergyMachine'];
        if(!$oMachine->usePetrol($type))
          return array('Error'=>Error::NOT_ENOUGH_ITEM);
        $oUser->save();
        
        Zf_log::write_act_log(Controller::$uId,0,20,'usePetrol',0,0,intval($type));
        
        return array('Error' => Error::SUCCESS);
    }
    
    
    /**   
    * check when engery machine is expired
    * 
    */
    
    public function expiredEnergyMachine($param){
        $UserId = $param['UserId'];
        if(empty($UserId))
          return array('Error' => Error::PARAM);
            
        
        $oUser = User::getById($UserId);
        if(!is_object($oUser))
          return array('Error' => Error::PARAM);

        $oEnergy = $oUser->SpecialItem['EnergyMachine'];
        if (!is_object($oEnergy))
          return array('Error' => Error::NO_ENERGY_MACHINE);
        if ($oEnergy->isExpired())
        {
            $oUser->SpecialItem['EnergyMachine']->IsExpired = true;
            $oUser->SpecialItem['EnergyMachine']->StartTime = 0;
            $oUser->SpecialItem['EnergyMachine']->ExpiredTime = 0;
            $oUser->bonusEnergy = 0;
            
            $conf_ener = Common::getConfig('EnergyMachine'); 
            $maxBonus = $conf_ener[$oUser->SpecialItem['EnergyMachine']->Type]['Buff'];  
            
            $curEnergy = $oUser->getCurrentEnergy();

            $conf_MaxEnergy = & Common::getConfig('MaxEnergy');
            if ($curEnergy > $maxBonus + $conf_MaxEnergy[$oUser->Level])
                $curEnergy = $maxBonus + $conf_MaxEnergy[$oUser->Level];
            

            if ($curEnergy > $conf_MaxEnergy[$oUser->Level])
            {
                $oUser->Energy = $conf_MaxEnergy[$oUser->Level];
                $oUser->bonusMachine = $curEnergy - $conf_MaxEnergy[$oUser->Level];
            }
                
            
            
            $oUser->save();
            return array('Error' => Error::SUCCESS);
        }
        else return array('Error' => Error::NO_EXPIRED);
        
    }
    
    
    
    /**   
    * level up mastery 
    */
    
    public function levelUpMaterialSkill(){
      
      $oUser = User :: getById(Controller::$uId) ;
      if (!is_object($oUser))
          return array('Error' => Error::NO_REGIS) ;
      
      $conf_mastery = Common::getConfig('M_MaterialSkill');
      
      $userPro = UserProfile::getById(Controller::$uId);
            
      if ($userPro->MatPoint < $conf_mastery[$userPro->MatLevel]['Mastery'])
        return array('Error' => Error::NOT_ENOUGH_MASTERY_POINT); 
        
      if ($oUser->Level < $conf_mastery[$userPro->MatLevel+1]['LevelRequire'])
        return array('Error' => Error::NOT_ENOUGH_LEVEL);
        
      if ($userPro->MatLevel >= count($conf_mastery))
        return array('Error' => Error::CAN_NOT_LEVELUP);
      
      $userPro->MatPoint = 0;  
      $userPro->MatLevel++;
      
      $oGift = $conf_mastery[$userPro->MatLevel]['Bonus'];
      $oUser->saveBonus($oGift);
      $oUser->save();
      $userPro->save();
      
      Zf_log::write_act_log(Controller::$uId,0,20,'levelUpMaterialSkill',0,0,$userPro->MatLevel);
      
      return array('Error' => Error::SUCCESS, 'Gift' => $oGift);
      
    }
    
    
    /**
    * Fill full energy mate fish
    */
    
    
    public function fillEnergy(){
       $oUser = User :: getById(Controller::$uId) ;
       if (!is_object($oUser))
          return array('Error' => Error::NO_REGIS) ;
       $oUser = User::getById(Controller::$uId);
       $userPro = UserProfile::getById(Controller::$uId);
       $conf_xu = Common::getParam();
       $conf_xu = $conf_xu['FillEnergy'];
       if (date('dmY', $userPro->LastFillEnergy) != date('dmY', $_SERVER['REQUEST_TIME']))
          $userPro->NumFill = 1;
       
         // thong so truoc khi thay doi
        $zmoney = $oUser->ZMoney ;
       
       if ($userPro->NumFill>5)
          return array('Error' => Error::CANT_FILL_ENERGY);
               
       $info = Controller::$uId . ':FillEnergy:' . $conf_xu[$userPro->NumFill];
       if(!$oUser->addZingXu(-$conf_xu[$userPro->NumFill],$info))
          return array('Error' => Error::NOT_ENOUGH_ZINGXU);

       
       
       $userPro->NumFill++;
       
       $userPro->LastFillEnergy = $_SERVER['REQUEST_TIME'];
       $oUser->Energy += 300;
       
       $oUser->save();
       $userPro->save();
       
         // thong so sau khi thay doi
       $difzmoney = $oUser->ZMoney - $zmoney;
       
       Zf_log::write_act_log(Controller::$uId,0,23,'fillEnergy',0,$difzmoney);
       
       return array('Error' =>Error::SUCCESS);
    }
    
    
    /**
    * @author hieupt
    * buy Magic Bag from Fairy Drop
    * 13/07/2011 
    */
    
/*    public function exchangeMagicBag($param)
    {
         $MagicName = $param['ItemType'];
         $idMagic = $param['ItemId'];
         
         if (empty($MagicName) || empty($idMagic))
            return array('Error' => Error::PARAM);
            
         $bagItem = Common::getParam('MagicBag');            
         if (!in_array($MagicName,$bagItem))
            return array('Error' => Error::PARAM);

         $conf_ex = Common::getConfig('FishMachineExchange');
         $conf_ex = $conf_ex[$MagicName][$idMagic];
         
         if (!is_array($conf_ex))
            return array('Error' => Error::OBJECT_NULL);
         
         if ($conf_ex['UnlockType']!=1)
             return array('Error' => Error::ID_INVALID);

         // check enough fairy
         $oUser = User::getById(Controller::$uId);
         
         if (!$oUser->addFairyDrop(-$conf_ex['Point']))
            return array('Error' => Error::NOT_ENOUGH_ITEM);

         $bonus[0] = $conf_ex;
         $oUser->saveBonus($bonus);
         
         $oUser->save();
         
         Zf_log::write_act_log(Controller::$uId,0,20,'exchangeFairyDrop',0,0,$conf_ex['Point'],$MagicName, $idMagic,0,1);
         
         return array('Error' => Error::SUCCESS);
    }
    */
    /**
    * @author hieupt
    * 15/07/2011
    * open magic bag
    * 
    */
    
    public function openMagicBag($param)
    {
        $type = $param['ItemType'];
        if (empty($type))               
            return array('Error' => Error::PARAM);
            
        $oStore = Store::getById(Controller::$uId);
        if (!$oStore->useItem('MagicBag',$type,1))
            return array('Error' => Error::NOT_ENOUGH_ITEM);
            
        $con_rand = Common::getConfig('MagicBag');
        if (!is_array($con_rand[$type]))
            return array('Error' => Error::OBJECT_NULL);
        
        $arrGift = array();
        foreach($con_rand[$type]['GiftContent'] as $id => $oog)
        {
            $arrGift[$id] = $oog['Per'];
        }
        
        $idGift = Common::randomIndex($arrGift);
        $oGift[0] = $con_rand[$type]['GiftContent'][$idGift];
        
        $oUser = User::getById(Controller::$uId);
        $oUser->saveBonus($oGift);
        
        $oStore->save();
        $oUser->save();
        
        return array('Error' => Error::SUCCESS, 'IdGift' => $idGift);
        
    }
    /**
    * AnhBV
    * ham thuc hien nhan qua tan thu
    */
    public function getNewUserGiftBag()
    {
        $oUser = User::getById(Controller::$uId);
        if(!is_object($oUser))
          return array('Error' => Error::NO_REGIS);
        
        $oUserPro = UserProfile::getById(Controller::$uId);
        
        // kiem tra xem da nhan qua chua
        $Times = $oUserPro->ActionInfo['NewUserGiftBag']['Gave'] + 1 ;
        $LastGetGiftTime = $oUserPro->ActionInfo['NewUserGiftBag']['LastGetGiftTime'] ;   
        
        if($Times > 4)
        {
          return array('Error' => Error::OVER_NUMBER);   
        }
        $conf = Common::getConfig('NewUserGiftBag',$oUser->Level,$Times);
        if(!is_array($conf))
        {
          return array('Error' => Error::NOT_LOAD_CONFIG);
        }
        // kiem tra xem da du thoi gian nhan chua
        
        if($_SERVER['REQUEST_TIME'] < ($LastGetGiftTime + $conf['Cooldown']))
        {
    
          return array('Error' => Error::NOT_ENOUGH_TIME);
        }
        
        // thong so truoc khi thay doi
        $zmoney = $oUser->ZMoney ;
        $money =  $oUser->Money ;
        
        //cong qua cho user
        $normal_bonus = array();
        $special_bonus = array();
        $Deco_bonus = array();
        
        foreach($conf['Bonus'] as $key => $bonus)
        {
            if(in_array($bonus[Type::ItemType],array(Type::SpecialFish,Type::RareFish,Type::BabyFish)))
            {
                $special_bonus[] = $bonus ;
            }
            else if(in_array($bonus[Type::ItemType],array(Type::OceanAnimal,Type::OceanTree,Type::Other)))
            {
                for($i = 1 ; $i <= $bonus['Num'];$i++)
                {
                    $Deco_bonus[] = $bonus ;
                }
                
            }
            else
            {
               $normal_bonus[] = $bonus ;   
            }  
        }
        
        $oStore = Store::getById(Controller::$uId);
           
        foreach($special_bonus as $key => $bonus)
        {
            $Id = $oUser->getAutoId();
            
            if($bonus[Type::ItemType] == Type::SpecialFish)
            {
                $rateOption = Fish::randOption(FishType::SPECIAL_FISH,-1,$bonus[Type::ItemId]);
               $oFish = new SpecialFish($Id,$bonus[Type::ItemId],rand(0,1),$rateOption,ColorType::EMPTY_COLOR);
            }
            if($bonus[Type::ItemType] == Type::RareFish)
            {
                $rateOption = Fish::randOption(FishType::RARE_FISH,-1,$bonus[Type::ItemId]);
               $oFish = new RareFish($Id,$bonus[Type::ItemId],rand(0,1),$rateOption,ColorType::EMPTY_COLOR); 
            }
            if($bonus[Type::ItemType] == Type::BabyFish)
            {
               $oFish = new Fish($Id,$bonus[Type::ItemId],rand(0,1),ColorType::EMPTY_COLOR);  
            }
            
            $oStore->addFish($oFish->Id,$oFish);     
        }
        
        if(!empty($normal_bonus))
        {
           $oUser->saveBonus($normal_bonus);  
        }
        if(!empty($Deco_bonus))
        {
           $oUser->saveBonus($Deco_bonus);  
        }
        
        // update lai thong tin 
        $oUserPro->updateNewUserGiftBag();
        
        //save
        $oStore->save();
        $oUser->save();
        $oUserPro->save();
        //log
       // thong so sau khi thay doi
       $difzmoney = $oUser->ZMoney - $zmoney;
       $difmoney = $oUser->Money - $money;
       
       Zf_log::write_act_log(Controller::$uId,0,30,'getNewUserGiftBag',$difmoney,$difzmoney,$oUser->Level);
       
       return array('Error' => Error::SUCCESS); 
    }
    
    // log in vao game 
    public function firstTimeLogin()
    {
        if(empty(Controller::$uId))
            return array('Error' => Error::LOGIN);
        
        $oUserPro = UserProfile::getById(Controller::$uId);
        if(!is_object($oUserPro))
            return array('Error' => Error::OBJECT_NULL); 
        //update thoi gian cua tui qua tan thu
        if(isset($oUserPro->ActionInfo['NewUserGiftBag']))
        {
           	$oUserPro->ActionInfo['NewUserGiftBag']['LastGetGiftTime'] = $_SERVER['REQUEST_TIME'] ; 
 			$oUserPro->save();
        }
        $arr = array();
        $arr['LastGetGiftTime']= $oUserPro->ActionInfo['NewUserGiftBag']['LastGetGiftTime'] ;
        $arr['Error']= Error::SUCCESS ;
        
        return $arr ;
    }
     
    /**
    * unlock slot enchant
    * @author hieupt
    * 03/11/2011   
    */
    public function unlockSlotEnchant()
    {
        $isMoney = $param['isMoney'];
                         
        $conf_enchantSlot = Common::getConfig('EnchantSlot');
        $oUser = User::getById(Controller::$uId);
        
        $oUserPro = UserProfile::getById(Controller::$uId);        
        $slotId = $oUserPro->EnchantSlot + 1;                      
        if (!isset($conf_enchantSlot[$slotId]))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);       
        
        // money before unlock
        $money = $oUser->Money ;
        $zmoney = $oUser->ZMoney ;
        
        if ($isMoney)   // paid by money
        {
            if (!$oUser->addMoney(-$conf_enchantSlot[$slotId]['Money'],'unlockSlotEnchant'))
                return array('Error' => Error::NOT_ENOUGH_MONEY);
        }
        else {   // paid by xu
            $info  = '1:UnlockSlotEnchant:1';
            if (!$oUser->addZingXu(-$conf_enchantSlot[$slotId]['ZMoney'],$info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }
               
        $oUserPro->unlockEnchantSlot();
       
        // save 
        $oUser->save();
        $oUserPro->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'unlockEnchantSlot',$oUser->Money-$money,$oUser->ZMoney-$zmoney,$slotId);
        
        return array('Error' => Error::SUCCESS);
    }
    
    /**
    * Exchange item collection
    * @author hieupt
    * 07/11/2011
    */
    public function exchangeItemCollection($param)
    {
        $ItemType = $param['ItemType'];
        $ItemId = $param['ItemId'];                 
        
        if (empty($ItemType) || empty($ItemId))
            return array('Error' => Error::PARAM);
            
        $conf_itemExchange = Common::getConfig('ItemCollectionExchange',$ItemType,$ItemId);
        
        if (!is_array($conf_itemExchange))
            return array('Error' => Error::ID_INVALID);
        
        $oStore = Store::getById(Controller::$uId);    
        $oUser = User::getById(Controller::$uId);
        foreach($conf_itemExchange['NecessaryItem'] as $id => $needItem)
        {
            if (!$oStore->useItem($needItem[Type::ItemType],$needItem[Type::ItemId],$needItem[Type::Num]))    
                return array('Error' => Error::NOT_ENOUGH_ITEM);            
        }
        
        switch($conf_itemExchange[Type::ItemType])
        {
            case Type::Money:
                $oGift = array();
                $oGift[0][Type::ItemType] = Type::Money;
                $oGift[0][Type::ItemId] = 1;
                $oGift[0][Type::Num] = $conf_itemExchange[Type::Num];
                $oUser->saveBonus($oGift);
                break;
            case Type::Exp:
                $oGift = array();
                $oGift[0][Type::ItemType] = Type::Exp;
                $oGift[0][Type::ItemId] = 1;
                $oGift[0][Type::Num] = $conf_itemExchange[Type::Num];
                $oUser->saveBonus($oGift);
                break;
            case Type::ItemTrunk:
                //$arrConvert = array(3=>3,4=>2,5=>1);
                //$conf_trunk = Common::getConfig('ItemCollectionTrunk',$ItemType,$arrConvert[$ItemId]);
                if(strstr($ItemType,'Sea') != FALSE)
                {
                    $conf_trunk = Common::getWorldConfig('FishWorldCollectionTrunk',$ItemType, $conf_itemExchange[Type::ItemId]);
                }      
                else
                {
                     $conf_trunk = Common::getConfig('ItemCollectionTrunk',$ItemType,$conf_itemExchange[Type::ItemId]);  
                }
                
                $arrId = array();
                foreach($conf_trunk as $id => $oGift)
                    $arrId[$id] = $oGift['Rate'];
                    
                $idRandom = Common::randomIndex($arrId);
                $color = $conf_trunk[$idRandom]['Color'];            
                $conf_equip = Common::getConfig('Wars_'.$conf_trunk[$idRandom]['ItemType'],$conf_trunk[$idRandom]['ItemId'],$color);
                $oEquip = new Equipment($oUser->getAutoId(),$conf_equip['Element'],$conf_trunk[$idRandom]['ItemType'],$conf_trunk[$idRandom]['ItemId'],$color,rand($conf_equip['Damage']['Min'],$conf_equip['Damage']['Max']),rand($conf_equip['Defence']['Min'],$conf_equip['Defence']['Max']),
                    rand($conf_equip['Critical']['Min'],$conf_equip['Critical']['Max']),$conf_equip['Durability'],$conf_equip['Vitality'],SourceEquipment::COLLECTION);
                $oStore->addEquipment($conf_trunk[$idRandom]['ItemType'],$oEquip->Id,$oEquip);
                break;        
        }
        
        $conf_log = Common::getConfig('LogConfig');
        
        //log
        Zf_log::write_act_log(Controller::$uId,0, 20, 'exchangeCollection',0, 0, $conf_log[$ItemType], $ItemId);
        
        if(is_object($oEquip))
            Zf_log::write_equipment_log(Controller::$uId,0, 20, 'saveEquipment',0, 0,$oEquip);
        
        //save
        $oStore->save();
        $oUser->save();
        
        return array('Error' => Error::SUCCESS,'Equipment' => $oEquip);
    }
    
    
    // buy Itemcollection
    
    public function buyItemCollection($param)
    {
        $ItemId = intval($param['ItemId']);
        if (empty($ItemId))
        {
            return array('Error' => Error :: PARAM) ;
        }

        $oUser = User :: getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

        // luu thong so cu~
        $moneyDiff = $oUser->Money;
        $zMoneyDiff = $oUser->ZMoney;
        
        $conf = Common::getConfig('ItemCollection',$ItemId);
        if(empty($conf))
            return array('Error' => Error :: NOT_LOAD_CONFIG) ;
            
        // check dieu kien limit
        $Now = DataRunTime::get(Controller::$uId.'ItemCollection'.$ItemId);
        if($Now >= $conf['Limit'])
            return array('Error' => Error :: OVER_NUMBER);   
        
        DataRunTime::inc(Controller::$uId.'ItemCollection'.$ItemId,1);
            
        $info = $ItemId.':'.'ItemCollection'.':1' ;
        if (!$oUser->addZingXu(-$conf['ZMoney'],$info))
            return  array('Error' => Error :: NOT_ENOUGH_ZINGXU) ;
            
       
        $oStore = Store::getById(Controller::$uId);
        $oStore->addItem('ItemCollection',$ItemId,1);
        
        // log
        $conf_log = Common::getConfig('LogConfig');
        if(isset($conf_log['ItemCollection']))
        {
            $TypeItemId = $conf_log['ItemCollection'];
        }
        $moneyDiff = $oUser->Money - $moneyDiff;
        $zMoneyDiff = $oUser->ZMoney - $zMoneyDiff;

        
        Zf_log::write_act_log(Controller::$uId,0,23,'buyOther',$moneyDiff,$zMoneyDiff,$TypeItemId,$ItemId, 0,0,1);

        $oUser->save();
        $oStore->save();
        $arr_result = array();
        $arr_result['Error'] = Error :: SUCCESS ;

        return $arr_result;
    }
    
    // ham thuc hien viec doi huy hieu vip lay qua
    public function openVipMedalBox($param)
    {
        $Type       = intval($param['Type']);
        $Element    = intval($param['Element']);
        
        if($Type < 1 || $Element <1 || $Element > 5)
            return array('Error'=>Error::PARAM);
            
        $oUser  = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId); 
        if(!is_object($oUser)||!is_object($oStore))
            return array('Error'=>Error::NO_REGIS);
        
        //tru medal
        $conf = Common::getConfig('VipMedalBox',$Type);
        if(empty($conf))
            return array('Error'=>Error::NOT_LOAD_CONFIG);
        $input = $conf['Input'][1];
        $output = $conf['Output'][1];
        if(!$oStore->useEventItem(EventType::PearFlower,Type::VipMedal,1,$input['Num']))
            return array('Error'=>Error::NOT_ENOUGH_ITEM);
        
        $arr_gift = array();
        if(SoldierEquipment::checkExist($output['ItemType']))
        {
            $oEquip = Common::randomEquipment($oUser->getAutoId(),$output['Rank'],$output['Color'],SourceEquipment::EVENT,$output['ItemType'],$output['Enchant'],$Element);
            
            $arr_gift['SpecialGift'][$oEquip->Id] = $oEquip ;
            
            $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);
        }
        else if($output['ItemType'] == SoldierEquipment::Seal )
        {
            $oEquip = new Seal(SoldierEquipment::Seal, $oUser->getAutoId(), $output['Rank'], $output['Color']);
            $oStore->addEquipment($oEquip->Type, $oEquip->Id, $oEquip);
        }
        else
        {
            $arr_gift['NormalGift'][] = $output ;
            $oUser->saveBonus($arr_gift['NormalGift']);
        }
            
        $oStore->save();
        $oUser->save();
    
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'openVipMedalBox',0,0,$Type,$oEquip->Type,$oEquip->Id);
        $arr_gift['Error'] = Error::SUCCESS;
        return $arr_gift ;
           
    }
    
    
    public function getLogClient()
    {
        
    }
    
    
    
    
}

