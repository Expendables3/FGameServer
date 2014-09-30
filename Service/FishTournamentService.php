<?php
class FishTournamentService extends Controller
{
    /** FLASH
    * check time tour
    * get Joined Slot
    */
    public function getTourInfo($params)
    {              
        $joinedSlot = array(
            'group1' => intval(DataRunTime::get('TournamentGroup1', true)),
            'group2' => intval(DataRunTime::get('TournamentGroup2', true)),
            'group3' => intval(DataRunTime::get('TournamentGroup3', true)),
        );
        
        return array(
            'Error' => Error::SUCCESS, 
            'joinedSlot' => $joinedSlot,
        );
    }       
    
    public function getZMoney()
    {
        $oUser = User::getById(self::$uId);
        if (empty($oUser))
        {
            return  (array("Error" => Error::OBJECT_NULL));
        }                                        
        $zMoney = intval($oUser->ZMoney);         
        $runArr["Error"] = 0;
        $runArr["ZMoney"] = intval($zMoney);
        return $runArr;
    }
    
    public function repayAll($params)
    {                          
        if (!is_int($params['groupId']))
        {
            return   (array("Error" => Error::PARAM));
        }
        $tourType = $params['groupId'];
        $oUser = User::getById(self::$uId);
        $oFishTour = FishTournament::getById(self::$uId);
        $oFishTour->LastJoinTime = 0;
        $oFishTour->save();                       
        DataRunTime::set('TournamentGroup'.$tourType, 0, true);
    }
    
    /**
    * JAVA SER 
    * comfirm Warrior & get Warrior: Item, equiqment, $money
    */
    public function joinTour($params)      
    {	
        if ((!is_int($params['groupId'])) || (!is_int($params['priceType'])) || 
            (!is_int($params['tour'])) || (!is_int($params['numUserInGroup'])))
            return   (array("Error" => Error::PARAM));
        $groupId = $params['groupId'];
        $priceType = $params['priceType'];
        $curTour = $params['tour'];  
        $numUser = $params['numUserInGroup'];  
        $oUser = User::getById(self::$uId);
        if (empty($oUser))
        {
            Debug::log('Ko ton tai user: '.self::$uId);
            return  (array("Error" => Error::OBJECT_NULL));
        }
        
        $oFishTour = FishTournament::getById(self::$uId);
        if (empty($oFishTour))
        {
            $oFishTour = FishTournament::init(self::$uId);
        }
        if($priceType == 1)
        {
            $curTime = $_SERVER['REQUEST_TIME'];
            $lastJoinByGold = $oFishTour->LastJoinByGold;
            if(intval(date('Ymd', $lastJoinByGold)) >= intval(date('Ymd', $curTime)))
            {
                return  (array("Error" => Error::CANT_USE_GOLD_TO_JOIN));
            }
        }

        // check Tour not existed in day
        $oFishTourMan = FishTourManager::getById();
        if (empty($oFishTourMan))
        {
            Debug::log('User join tournament, FishTourManager null');
            $oFishTourMan = FishTourManager::init();
            $oFishTourMan->save();
        }
        if($curTour > 0 && $oFishTourMan->CurrentTour != $curTour)
        {                                                      
            $oFishTourMan->CurrentTour = $curTour;
            $oFishTourMan->save();
        }
        
        // check joining Condition
        $levelRequire = Common::getConfig('Tournament', 'LevelRequire');
        $soldierRequire = Common::getConfig('Tournament', 'SoldierRequire');
        if ($levelRequire > $oUser->Level)
        {
            return array("Error" => Error::NOT_ENOUGH_LEVEL);
        }
        $arrSoldier = Lake::getAllSoldier(self::$uId,true,true,true);
        if (count($arrSoldier) < $soldierRequire)
        {
            return array("Error" => Error::NOT_ENOUGH_CONDITION);
        }                                                         
                                                                      
        switch ($priceType)
        {
            case 1:
                $feeJoinConf = Common::getConfig('Tournament', 'FeeMoney', $groupId);
                if ($oUser->Money < $feeJoinConf)
                {
                    return array("Error" => Error::NOT_ENOUGH_MONEY);
                }
                $oFishTour->LastJoinByGold = $_SERVER['REQUEST_TIME'];
                break;
            case 2:
                $feeJoinConf = Common::getConfig('Tournament', 'FeeZMoney', $groupId);
                if ($oUser->ZMoney < $feeJoinConf)
                {
                    return array("Error" => Error::NOT_ENOUGH_ZINGXU);
                }
                break;
            default:
                return array("Error" => Error::PARAM);
        }
        
        try
        {
            // luu lai user vao tour
            Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentJoin', 0, 0); 
        }
        catch(Exception $e)
        {
          
        }

        // Joined Tour        
        $oFishTour->LastJoinTime = $_SERVER['REQUEST_TIME'];
        $oFishTour->save();

        $oStore = Store::getById(self::$uId);
        $oStoreEquip = StoreEquipment::getById(self::$uId);

        $runArr["Error"] = 0;
        $runArr["UserInfo"]["Id"] = intval($oUser->Id);
        $runArr["UserInfo"]["Name"] = $oUser->Name;
        $runArr["UserInfo"]["ReputationLevel"] = $oUser->ReputationLevel;
        $runArr["UserInfo"]["Level"] = intval($oUser->Level);
        $runArr["UserInfo"]["Money"] = intval($oUser->Money);
        $runArr["UserInfo"]["ZMoney"] = intval($oUser->ZMoney);
        $runArr["UserInfo"]["AvatarPic"] = $oUser->AvatarPic;
        $runArr["SoldierList"] = $arrSoldier;
        $runArr['EquipmentList'] = $oStoreEquip->SoldierList;
        $runArr['MeridianList'] = $oStoreEquip->listMeridian;
        $runArr["WarItemList"]["BuffItem"] = $oStore->BuffItem;        
        $runArr["WarItemList"]["Gem"] = $this->arrayToObject($oStore->Gem);        
        try
        {
            DataRunTime::inc('TournamentGroup'.$groupId, 1, true);
        }
        catch (Exception $e)
        {
            
        }
        return $runArr;
    }

    public function arrayToObject($arr)
    {
        if(!is_array($arr))
        {
            return $arr;
        }

        $obj = new stdClass();
        if(is_array($arr) && count($arr) > 0)
        {
            foreach($arr as $name=>$value)
            {
                $name = trim($name);
                if(!empty($name))
                {
                    $obj->$name = $this->arrayToObject($value);
                }
            }
            return $obj;
        }
        else
        {
            return $arr;
        }
    }
    
    public function endUserTour($params)
    {
        if (!is_array($params['listBuyItem']) || !is_array($params['listUseItem']) ||
        !is_array($params['listUseGem']) || !is_string($params['achieved']) || 
        !is_int($params['payType']) || !is_bool($params['isGiveup']) ||
        !is_int($params['groupId']) || !is_int($params['numUser']))
            return array('Error' => Error::PARAM);
                                                    
        $buyItems = $params['listBuyItem'];
        $useItems = $params['listUseItem'];
        $useGems = $params['listUseGem'];
        $achieved = $params['achieved'];
        $payType = $params['payType'];
        $isGiveup = $params['isGiveup'];
        $tourType = $params['groupId'];
        $numUser = $params['numUser'];
                            
        DataRunTime::dec('TournamentGroup'.$tourType, 1, true);

        $oFishTourMan = FishTourManager::getById();
        if (empty($oFishTourMan))
        {
            return array('Error' => Error::OBJECT_NULL);
        }

        $oFishTour = FishTournament::getById(self::$uId);
        if (empty($oFishTour))
        {
            return array('Error' => Error::OBJECT_NULL); 
        }
        $oFishTour->LastJoinTime = 0;
        $oFishTour->GroupId = $tourType;
        $oFishTour->save();
        
        $oStore = Store::getById(self::$uId);
        $oUser = User::getById(self::$uId);
        
        try
        {
            // luu lai user thoat tour
            Zf_log::write_act_log(self::$uId, 0, 20,'tournamentQuit', 0, 0); 
        }
        catch(Exception $e)
        {
          
        }
                                                                       
        switch ($payType)
        {
            case 1:
                $feeJoinConf = Common::getConfig('Tournament', 'FeeMoney', $tourType);
                if ($feeJoinConf == null || $oUser->Money < $feeJoinConf)
                {
                    return array("Error" => Error::NOT_ENOUGH_MONEY);
                }                
                $test = $oUser->addMoney(-$feeJoinConf, 'TournamentFee');
                $oUser->save();
                try
                {
                    Zf_log::write_act_log($oUser->Id, 0, 23,'tournamentFee', -$feeJoinConf, 0); 
                }
                catch(Exception $e)
                {
                  
                }
                $oFishTour->LastJoinByGold = $_SERVER['REQUEST_TIME'];
                break;
            case 2:
                $feeJoinConf = Common::getConfig('Tournament', 'FeeZMoney', $tourType);
                $feeJoinConf = intval($feeJoinConf);
                if ($feeJoinConf == null || $oUser->ZMoney < $feeJoinConf)
                {
                    return array("Error" => Error::NOT_ENOUGH_ZINGXU);
                }
                $test = $oUser->addZingXu(-$feeJoinConf, '1:'.'TournamentFee'.':1');
                $oUser->save();
                try
                {
                    Zf_log::write_act_log($oUser->Id, 0, 23,'tournamentFee', 0, -$feeJoinConf); 
                }
                catch(Exception $e)
                {
                  
                }
                break;
            default:
                return array('Error' => Error::SUCCESS);
        }
        
        // user quit sau khi dang ki
        if($isGiveup)
        {
            return array('Error' => Error::SUCCESS);
        }
        
        foreach ($buyItems as $item)
        {                                                                                           
            $itemConf = Common::getConfig('BuffItem', $item['ItemType'], $item['ItemId']);
            $info = $item['ItemId'].':'.$item['ItemType'].':'.$item['Num'] ;            
            if (!$oUser->addZingXu(-$itemConf['ZMoney'] * $item['Num'],$info))
            {
                return  array('Error' => Error :: INTERNAL_PROCESS_FAIL) ;
            }
            
            try
            {
                Zf_log::write_act_log($oUser->Id, 0, 23,'tournamentBuyItem', 0, -$itemConf['ZMoney'] * $item['Num'], $item['Num'], $item['ItemType'], $item['ItemId']);
            }
            catch(Exception $e)
            {
              
            }
            
        }
        $oUser->save();

        foreach ($useItems as $item)
        {
            if(BuffItem::checkExist($item['ItemType']))
            {                                                                                                           
                if (!$oStore->useBuffItem($item['ItemType'], $item['ItemId'], 1))
                {

//                    return array('Error' => Error::INTERNAL_PROCESS_FAIL);
                }
            }	
        }

        foreach($useGems as $gem)
        {             
            if (!$oStore->removeGemById($gem['Type'],$gem['GemId'],$gem['num']))
            {
                return array('Error' => Error::INTERNAL_PROCESS_FAIL);
            }
        }

        $oStore->save();

        // save user end Tour
        if ($achieved == 'Champion')
        {       
            try
            {
                // luu lai user nay da vo dich tour
                Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentChampion', 0, 0); 
            }
            catch(Exception $e)
            {
              
            }
            
            $oFishTour->LastAchieved = 5;
            $logicRound = ceil(log(Common::getConfig('Tournament', 'MaxUserGroup'.$tourType)) / log(2)) + 1;
            $reward = Common::getConfig('Tournament_Reward', $tourType, $logicRound);
            $oFishTour->GiftAchieved = intval($reward['Num']);
            $date = intval(date('Ymd', $_SERVER['REQUEST_TIME']));
            
            $champions = array(
                'date' => $date,
                'tour' => $oFishTourMan->CurrentTour,
                'Id' => $oUser->Id,
                'Name'=> $oUser->Name,
                'AvatarPic' => $oUser->AvatarPic,
            );
            switch($tourType)
            {
                case 1:
                    $tourname = 'normal';
                    break;
                case 2:
                    $tourname = 'nightmare';
                    break;
                case 3: 
                    $tourname = 'hell';  
                    break;
                default:
                    return array('Error' => Error::PARAM); 
                    break;
            }
            array_push($oFishTourMan->championList[$tourname], $champions);
            $oFishTourMan->save();                             
            DataRunTime::set('TournamentGroup'.$tourType, 0, true);
            
            $maskColor = $tourType + 1;
            $mask = Common::randomEquipment($oUser->getAutoId(), 5, $maskColor, SourceEquipment::TOURNAMENT, Type::Mask);            
            $oStore->addEquipment(Type::Mask, $mask->Id, $mask);
            $oStore->save();
            $oUser->save();
            
            try
            {
                Zf_log::write_equipment_log(self::$uId, 0, 20, 'tournamentGiftEquip', 0, 0, $mask);
            }
            catch (Exception $e)
            {
                
            }
        }
        else
        {
            // Round logic da dc server JAVA tinh toan xong
            $logicRound = intval(substr($achieved, strlen("Round")));
            
            try
            {
                // luu lai round ma user thua
                Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentLoser', 0, 0, $logicRound); 
            }
            catch(Exception $e)
            {
              
            }
                
            $reward = Common::getConfig('Tournament_Reward', $tourType, $logicRound);
            if($reward == null)
            {
                return array('Error' => Error::PARAM);
            }
            $oFishTour->LastAchieved = intval($reward['Star']);
            $oFishTour->GiftAchieved = intval($reward['Num']);

        }  
        
        $oFishTour->save();
        
        $oUserPro = UserProfile::getById(self::$uId);
        $oUserPro->ActionInfo['ReceivedGiftTournament'] = false;
        // save 2 params $achieved & $payType into ActionInfo property of UserProfile
        $oUserPro->ActionInfo['lastAchieved'] = $achieved;
        $oUserPro->ActionInfo['lastPayType'] = $payType;
        //---        
        $oUserPro->save();

        return array('Error' => Error::SUCCESS);
    }

    public function endTour($params)
    {
        $oFishTourMan = FishTourManager::getById();
        if (empty($oFishTourMan))
            return  array("Error" => Error::OBJECT_NULL);
        $date = date('dmY', $_SERVER['REQUEST_TIME']);
        if (!$oFishTourMan->endTour($date))
            return array('Error' => Error::ACTION_NOT_AVAILABLE);
        $oFishTourMan->save();

        return array('Error'=> Error::SUCCESS);
    }

    public function getChampions()
    {
        $oFishTour = FishTournament::getById(self::$uId);
        $curTime = $_SERVER['REQUEST_TIME'];
        $lastJoinByGold = $oFishTour->LastJoinByGold;     
        if(intval(date('Ymd', $lastJoinByGold)) >= intval(date('Ymd', $curTime)))
        {
            $canJoinByGold = false;
        }
        else
        {
            $canJoinByGold = true;
        }
        
        $oFishTourMan = FishTourManager::getById();        
        if (empty($oFishTourMan) || $oFishTourMan->championList == null)
        {                            
            $oFishTourMan = FishTourManager::init();
            $oFishTourMan->save();
        }
                
        foreach($oFishTourMan->championList as $tourname => $tour)
        {
            $temp = array();
            for($i = 0; $i < count($oFishTourMan->championList[$tourname]); $i++)
            {
                $date = $oFishTourMan->championList[$tourname][$i]['date'];
                $date = strtotime($date);
                $date = ($_SERVER['REQUEST_TIME'] - $date) / 86400;
                if(intval($date) <= 30)
                {
                    array_push($temp, $oFishTourMan->championList[$tourname][$i]);
                }
            }
            $oFishTourMan->championList[$tourname] = $temp;
            $oFishTourMan->save();
        }
        
        $runArr['Error'] = Error::SUCCESS;
        $runArr['canJoinByGold'] = $canJoinByGold;
        $runArr['Champions'] = $oFishTourMan->championList;               

        return $runArr;
    }

    public function getGiftTourInfo()
    {
        $oFishTour = FishTournament::getById(self::$uId);
        if($oFishTour == null)
        {
            return array('Error'=> Error::PARAM);
        }
        $cardStar = $oFishTour->LastAchieved;
        $numChoose = $oFishTour->GiftAchieved;      
        $lastCardId = $oFishTour->LastCardId;
        $groupId = intval($oFishTour->GroupId);
        
        return array('Error'=> Error::SUCCESS, 'userId' => self::$uId, 'groupId' => $groupId, 'cardStar' => $cardStar, 'lastCardId' => $lastCardId, 'numChoose' => $numChoose);
    }
    
    public function getGiftTour($params)
    {
        if (!is_int($params['cardId']) || intval($params['cardId']) <= 0)
            return array("Error" => Error::PARAM);

        $cardId = $params['cardId'];
        $oFishTour = FishTournament::getById(self::$uId);
        $cardStar = $oFishTour->LastAchieved;
        $groupId = $oFishTour->GroupId;
        $rewardArrConf = Common::getConfig('Tournament_Card', $cardId, $groupId);
        if($rewardArrConf == null)
        {
            return array("Error" => Error::PARAM);
        }
        $rewardArr = $rewardArrConf[$cardStar];
        if(empty($rewardArr))
        {
            return array("Error" => Error::NOT_ACTION_MORE);
        }
        $oUser = User::getById(self::$uId);
        $oStore = Store::getById(self::$uId);
        $equip = null;
        foreach($rewardArr as $key => $reward)
        {
            switch($key)
            {
                case Type::Exp:
                {
                    $oUser->addExp(intval($reward));
                    try
                    {
                        // Luu lai so exp user dc tang
                        Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentGift', 0, 0, intval($reward), $key); 
                    }
                    catch(Exception $e)
                    {
                      
                    }
                    break;                    
                }
                case Type::Diamond:
                {
                    $oUser->addDiamond(intval($reward));
                    try
                    {
                        // Luu lai so kim cuong user dc tang
                        Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentGift', 0, 0, intval($reward), $key); 
                    }
                    catch(Exception $e)
                    {
                      
                    }
                    break;                    
                }
                case BuffItem::Dice:
                {
                    $oStore->addBuffItem($key, 1, intval($reward));
                    try
                    {
                        // Luu lai so kim cuong user dc tang
                        Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentGift', 0, 0, intval($reward), $key);
                    }
                    catch(Exception $e)
                    {
                      
                    }
                    break;
                }
                case Type::Material:
                case Type::RankPointBottle:
                case Type::EnergyItem:
                {                    
                    $oStore->addItem($key, $reward['Type'], intval($reward['Num']));
                    try
                    {
                        // Luu lai so kim cuong user dc tang
                        Zf_log::write_act_log($oUser->Id, 0, 20,'tournamentGift', 0, 0, intval($reward['Num']), $key); 
                    }
                    catch(Exception $e)
                    {
                      
                    }
                    break;                    
                }                
                case "Equipment":
                {
                    $arrEquipment = array('Armor'=>10,'Helmet'=>10,'Weapon'=>10);
                    $equipType = Common::randomIndex($arrEquipment);                                                                                                                                   
                    $equip = Common::randomEquipment($oUser->getAutoId(), intval($reward['Rank']), intval($reward['Color']), SourceEquipment::TOURNAMENT, $equipType);
                    $oStore->addEquipment($equipType, $equip->Id, $equip);
                    
                    try
                    {
                        Zf_log::write_equipment_log(self::$uId, 0, 20, 'tournamentGiftEquip', 0, 0, $equip);
                    }
                    catch (Exception $e)
                    {
                        
                    }
                    break;                    
                }
                case "Jewel":
                {
                    $arrEquipment = array('Ring'=>10,'Bracelet'=>10,'Necklace'=>10,'Belt'=>10);
                    $jewelType = Common::randomIndex($arrEquipment);                    
                    $equip = Common::randomEquipment($oUser->getAutoId(), intval($reward['Rank']), intval($reward['Color']), SourceEquipment::TOURNAMENT, $jewelType);

                    $oStore->addEquipment($jewelType, $equip->Id, $equip);
                    
                    try
                    {
                        Zf_log::write_equipment_log(self::$uId, 0, 20, 'tournamentGiftEquip', 0, 0, $equip);
                    }
                    catch (Exception $e)
                    {
                        
                    }
                    break;                    
                }
            }
        }
        
         $groupId = $oFishTour->GroupId;
                
        // add gifts event, current event: Halloween
        $listGiftEvent = array();
//        Debug::log('$oFishTour->LastCardId '.$oFishTour->LastCardId);
        if(!isset($oFishTour->LastCardId) || count($oFishTour->LastCardId)<=0){
            $oUserPro = UserProfile::getById(self::$uId);
            // get 2 params $achieved & $payType into ActionInfo property of UserProfile
            $achieved = "";
            if(isset($oUserPro->ActionInfo['lastAchieved'])){
                $achieved = $oUserPro->ActionInfo['lastAchieved'];    
            }
                        
            $listGiftEvent = Event::getActionGiftInEvent(EventType::EventActive, 'FishTournament', $groupId, $achieved);
            $oUser->saveBonus($listGiftEvent);
        }    
        $oUser->save();
        $oStore->save();
        
        // user nhan du qua roi
        $oFishTour->GiftAchieved = $oFishTour->GiftAchieved - 1;
        if(!is_array($oFishTour->LastCardId))
        {
            $oFishTour->LastCardId = array();
        }
        array_push($oFishTour->LastCardId, $cardId); 
        if($oFishTour->GiftAchieved <= 0)
        {
            $oFishTour->LastCardId = array();
            $oFishTour->LastAchieved = 0;            
        }
        
        $oFishTour->save();
        return array('Error'=> Error::SUCCESS, 'cardId' => $cardId, 'star' => $cardStar,'groupId' =>$groupId, 'equipment' => $equip,'eventItems'=>$listGiftEvent);
    }
}
