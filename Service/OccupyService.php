<?php
    class OccupyService
    {
        public function getOccupying()
        {
            // ensure always having at least one Soldier to join Occupying                       
            $choseSoldier = Lake::selectSoldierId(Controller::$uId);            
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);
            
            // no-fish, not ever join occupy
            if(!$choseSoldier && empty($oProfile))
            {
                // only view
                return array(
                    'Error' => Error::SUCCESS,
                    'Occupying' => array(),
                );        
            }
            $logJoined = false;
            if(empty($oProfile))
            {
            
                                                            
                $oProfile = new OccupyingProfile(Controller::$uId, $_SERVER['REQUEST_TIME']);
                
              //   add Soldier
                $oLake = Lake::getById(Controller::$uId, $choseSoldier['LakeId']);
                $mySoldier = $oLake->getFish($choseSoldier['Id']);
                
                $oStoreEquip = StoreEquipment::getById(Controller::$uId);
                $mySoldierEquip = $oStoreEquip->SoldierList[$choseSoldier['Id']] ;
                $mySoldierMeridian = $oStoreEquip->listMeridian[$choseSoldier['Id']];
                $sProfile = array(
                    'Soldier' => $mySoldier,
                    'Equipment' => $mySoldierEquip['Equipment'],
                    'Index' => $mySoldierEquip['Index'],
                    'Meridian' => $mySoldierMeridian, 
                );
                
                $oProfile->setCurrSoldier($choseSoldier['Id'], $choseSoldier['LakeId'], $sProfile);                
                              
                $GiftSaved = array();
                
                $logJoined = true;                  
            }
            else
            { 
                // not exist, replace other                
                if($choseSoldier)
                {
                    $mySoldier = $oProfile->CurrSoldier;
                    $oLake = Lake::getById(Controller::$uId, $mySoldier['LakeId']);
                    $mySoldier = $oLake->getFish($mySoldier['Id']);
                    if(!$mySoldier)
                    {
                        $SoldierId = $choseSoldier['Id'];
                        $LakeId = $choseSoldier['LakeId'];
                        $oLake = Lake::getById(Controller::$uId, $LakeId);
                        $oSoldier = $oLake->getFish($SoldierId);
                        
                        $oStoreEquip = StoreEquipment::getById(Controller::$uId);
                        $mySoldierEquip = $oStoreEquip->SoldierList[$SoldierId] ;
                        $mySoldierMeridian = $oStoreEquip->listMeridian[$SoldierId]; 
                        $sProfile = array(
                            'Soldier' => $oSoldier,
                            'Equipment' => $mySoldierEquip['Equipment'],
                            'Index' => $mySoldierEquip['Index'],
                            'Meridian' => $mySoldierMeridian,
                        );
                        $oProfile->setCurrSoldier($SoldierId, $LakeId, $sProfile);  
                    }
                }
  
                //   check & set Gift from last occupied, get at the first time join new campain Occupy
                $giftRank = $this->_checkIsGotGift($oProfile, false);
                if($giftRank)
                    $oProfile->CurrRank = OccupyFea::NOT_ATTEND;
                switch($giftRank)
                {
                    case Error::SET_GIFT_UNFINISHED:
                        return Common::returnError(Error::SET_GIFT_UNFINISHED);
                        break;
                    case Error::GET_GIFT_EXPIRED:
                    case false:
                        $GiftSaved = array();
                        break;
                    default:
                        $uRank = $giftRank['rank'] ;
                        $time = $giftRank['time'];
                        $GiftConfig = Common::getConfig('Occupy_Gifts', 'Top');
                        $Gifts = $this->_getGiftTop($uRank, $GiftConfig);
                        $GiftSaved = Common::addsaveGiftConfig($Gifts, $Element, SourceEquipment::OCCUPY); 
                    

                        break;                  
                }                                      
                 // update last join
                 $oProfile->LastOccupiedTime = $_SERVER['REQUEST_TIME'];
            }
            // join the next campain Occupy
            if($oProfile->CurrRank == OccupyFea::NOT_ATTEND)    
                $resJoined = $this->_joinRankBoard();  
            // set Rank
            if(isset($resJoined))
                switch($resJoined)
                {
                     case OccupyFea::FULL_BOARD:
                        $oProfile->CurrRank = OccupyFea::RANK_END_BOARD + 1;
                        break;
                     case MySqlCode::QUERY_FAIL:
                     case false:
                        $oProfile->CurrRank = OccupyFea::NOT_ATTEND;
                        break; 
                     default:
                        $oProfile->CurrRank = $resJoined;
                        break;
                }
                
            // gift token 
            $gotToken = $this->_giftToken($oProfile);
            if(!$gotToken)
                    $runArr['Occupying']['GotGiftToken'] = true;
            else 
            {
               $runArr['Occupying']['GotGiftToken'] = false;
               $runArr['Occupying']['TokenExpired'] = $gotToken['expired'];
               $runArr['Occupying']['TokenCurr'] = $gotToken['curr'];
            }
            
            // limit Occupy Time
            $this->_checkDateReset($oProfile);
               
            $oProfile->save(); 
 
           $runArr['Error'] = Error::SUCCESS;
           $runArr['Occupying']['CurrSoldier'] = $oProfile->CurrSoldier;
           $runArr['Occupying']['LastOccupy'] = $oProfile->LastOccupy;
           $runArr['Occupying']['LastRefreshBoard'] = $oProfile->LastRefreshBoard;
           $runArr['Occupying']['LastGiftRank'] = (empty($uRank)) ? $oProfile->LastGiftRank : $uRank;
           $runArr['Occupying']['LastGiftTime'] = (empty($time)) ? 0 : $time;
           $runArr['Occupying']['LastGift'] = $GiftSaved;
           $runArr['Occupying']['RemainOccupyCount'] = $oProfile->RemainOccupyCount; 
           
           if($logJoined)
                Zf_log::write_act_log(Controller::$uId, 0, 20, 'occupyJoined' );
            if(!empty($uRank) && ($uRank <= OccupyFea::RANK_END_BOARD))
                Zf_log::write_act_log(Controller::$uId, 0, 20, 'occupyRanked', 0, 0, $uRank, $time);
           return $runArr ;
        }
        
        public function refreshOccupyingBoard($params)
        {   
            $sysRefresh = $params['System'];
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);
            $OccupyingBoard = array();
            $BoardRankConfig = Common::getConfig('Occupy_RankBoard');  
                        
            if(empty($oProfile) || ($oProfile->CurrRank == OccupyFea::NOT_ATTEND))
            {
               // show bottom of board
               $uRank = OccupyFea::NOT_ATTEND;
               $getRank = true;
               $currentRank = DataRunTime::get('OccupyRankInitial', true);
               // none join
               if($currentRank == 0)
                    return array(
                        'Error' => Error::SUCCESS,
                        'CurrRank' => $uRank,
                        'OccupyingBoard' => array(),                        
                    );
               $currentRank = ($currentRank > OccupyFea::RANK_END_BOARD) ? (OccupyFea::RANK_END_BOARD + 1) : $currentRank;
               $BoardRanks = $this->_getRanksBoard($BoardRankConfig, $currentRank);                                                 
            }
            else
            {
               // check user refresh, cooldown
                if(isset($sysRefresh) && (!$sysRefresh))
                {
                    if(!$this->_checkCoolDown('RefreshBoard', $oProfile))
                        return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                        
                    // current user out of board, not necessary get User rank 
                    if($oProfile->CurrRank > OccupyFea::RANK_END_BOARD)
                    {
                        $getRank = false;
                        $uRank = OccupyFea::RANK_END_BOARD + 1;
                    } else $getRank = true;        
                    $this->_setCoolDown('RefreshBoard', $oProfile);   
                }
                else
                {
                    if(!$this->_checkCoolDown('SystemRefresh', $oProfile))
                        return Common::returnError(Error::ACTION_NOT_AVAILABLE);
                    if((!$oProfile->LastOccupyResult) && ($oProfile->CurrRank > OccupyFea::RANK_END_BOARD))
                    {
                        $getRank = false;
                        $uRank = OccupyFea::RANK_END_BOARD + 1;
                    }
                    else $getRank = true;         
                    $this->_setCoolDown('SystemRefresh', $oProfile);    
                }
                
                if($getRank)
                    $uRank = $this->_getRank();
                $BoardRanks = $this->_getRanksBoard($BoardRankConfig, $uRank);
                
                $oProfile->CurrRank = $uRank;
                $oProfile->save();
            }

            $RankUids = $this->_getUidsFromRanks($BoardRanks);
            
            foreach($RankUids as $rank=>$uId)
            {
                $oUser = User::getById($uId);                    
                $profile = array(
                    'Id' => $uId,
                    'Name' => $oUser->Name,
                    'Avatar' => $oUser->AvatarPic,
                    'Rank' => $rank
                );
                $OccupyingBoard[$rank] = $profile ;
            }

            $runArr['Error'] = Error::SUCCESS;
            $runArr['CurrRank']  = $uRank;
            $runArr['OccupyingBoard'] = $OccupyingBoard; 
            
            return $runArr;  
        }
        
        public function occupy($params)
        {
            // at the time, during set Gift
            $request_time = $_SERVER['REQUEST_TIME'];
            $currDate = date('Y-m-d', $request_time);
            $timeEndConf = Common::getConfig('Param', 'Occupy', 'TimeEndInDay');
            $GiftEndOccupyTime = strtotime($currDate . ' ' . $timeEndConf);
            $SetGiftDurationConf = Common::getConfig('Param', 'Occupy', 'SetGiftDuration');
            $TimeEndOccupy =  $GiftEndOccupyTime - $SetGiftDurationConf;
            if(($request_time <= $GiftEndOccupyTime) && ($request_time >= $TimeEndOccupy))
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
            
            $fightRank = $params['FightRank'] ;
                        
            if(!is_int($fightRank) || ($fightRank < 0) || ($fightRank > OccupyFea::RANK_END_BOARD))
                return array('Error' => Error::PARAM);                
                
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);            
            if(empty($oProfile))
               return array('Error' => Error::OBJECT_NULL);
            // limit occupy
            if($oProfile->RemainOccupyCount <= 0)
                return array('Error' => Error::ACTION_NOT_AVAILABLE);
                              
            if(!$this->_checkCoolDown('Occupy', $oProfile))
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
            
            $mySoldier = $oProfile->CurrSoldier;
            $oLake = Lake::getById(Controller::$uId, $mySoldier['LakeId']);
            $mySoldier = $oLake->getFish($mySoldier['Id']);
            if(!$mySoldier)
                return Common::returnError(Error::NO_FISH);
            if($mySoldier->Status!=SoldierStatus::HEALTHY)
                return array('Error' => Error::SOLDIER_EXPIRED);
                
            $uRank = $this->_getRank();
            if($fightRank > $uRank)         // fight lower Rank
                return array('Error' => Error::PARAM);                
            
            $BoardRankConfig = Common::getConfig('Occupy_RankBoard'); 
            $validRanks = $this->_getRanksBoard($BoardRankConfig, $oProfile->CurrRank);     // the same Rank User view
            if($fightRank < min($validRanks))       // prohibit: cheat Occupy top, not in Top view, 
                return array('Error' => Error::PARAM);                

            // occupying                    
            $fightUid = $this->_getUidFromRank($fightRank);
            if(empty($fightUid))
                return array('Error' => Error::UID_INVALID);                                                   
            
            $ofightProfile = OccupyingProfile::getByUid($fightUid);
            if(empty($ofightProfile))
               return array('Error' => Error::OBJECT_NULL);
            
            // spent a token for occupying ]
            $oStore = Store::getById(Controller::$uId);
            if(!$this->_useToken($oProfile, $oStore, 1))
                return Common::returnError(Error::NOT_ENOUGH_ITEM); 
            
            // limit Occupy
            $oProfile->RemainOccupyCount -= 1;    
            // attack    
            $fightSoldier = $ofightProfile->CurrSoldier;
            
            $foughtResult = SoldierFish::takeAttack($mySoldier, $fightSoldier['Soldier'], array(), true);            
            if($foughtResult['Result'])
            {
                 $uid = Controller::$uId;
                 $sql = "call OccupiedRank ({$uid},{$uRank}, {$fightRank})";
                 $res = Common::queryMySqli(OccupyFea::CODE, $sql);
                 if($res['Error'] != Error::SUCCESS)
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);                            
            }

            // save Soldier
            $oStoreEquip = StoreEquipment::getById(Controller::$uId);
            $mySoldierEquip = $oStoreEquip->SoldierList[$mySoldier->Id] ;
            $mySoldierMeridian = $oStoreEquip->listMeridian[$mySoldier->Id];
            $sProfile = array(
                'Soldier' => $mySoldier,
                'Equipment' => $mySoldierEquip['Equipment'],
                'Index' => $mySoldierEquip['Index'],
                'Meridian' => $mySoldierMeridian,                 
            );
            $oProfile->setCurrSoldier("", "", $sProfile);
            
            // coolDown
            $this->_setCoolDown('Occupy', $oProfile);
            //can get Top after occupy
            $oProfile->toggleGetTop();
            // mark the last join occupying, for get gift
            $oProfile->LastOccupiedTime = $_SERVER['REQUEST_TIME'];
            //update result
            $oProfile->LastOccupyResult = $foughtResult['Result']; 
            
            $Gifts = Common::getConfig('Occupy_Gifts', 'Occupied', $foughtResult['Result']);
            
            // add Gifts
            foreach($Gifts as $gift)
            {
                switch($gift['ItemType'])
                {
                    case Type::Exp:
                        $oUser = User::getById(Controller::$uId);
                        $oUser->addExp($gift['Num']);
                        $oUser->save(); 
                        break;
                    case Type::RankPoint:
                        $mySoldier->addRankPoint($gift['Num']);
                        $oLake->save();
                        break;
                }
            }                        
                                    
            $oStore->save();  
            $oProfile->save();            
            
            $foughtUser = User::getById($fightUid);
            $runArr['UserFought'] = array(
                'Name' => $foughtUser->Name,
                'Level' => $foughtUser->Level,
                'Avatar' => $foughtUser->AvatarPic,
                'ReputationLevel' => $foughtUser->ReputationLevel,
            );
            
            //add bonus event, current event: Halloween
            $listGiftEvent = array();   
            
            if(intval($foughtResult['Result'])>0 ){ // if user win                  
                    $listGiftEvent = Event::getActionGiftInEvent(EventType::EventActive, 'Occupy', 'Win');
            } else{ // else user lose               
                    $listGiftEvent = Event::getActionGiftInEvent(EventType::EventActive, 'Occupy', 'Lose');
            }
            if(!empty($listGiftEvent)){
                $oUser = User::getById(Controller::$uId);
                $oUser->saveBonus($listGiftEvent);
                $oUser->save();
            }
            //---
            
            $runArr['Scene'] = $foughtResult['Scene'];            
            $runArr['SoldierFought']  = $fightSoldier;                                 
            $runArr['Error'] = Error::SUCCESS;
            $runArr['IsWin'] = $foughtResult['Result'];
            $runArr['Gift'] = array_merge($Gifts,$listGiftEvent);
            
            // log
            Zf_log::write_act_log(Controller::$uId, 0, 20, 'occupy', 0, 0, $fightRank, $foughtResult['Result']);
            
            return $runArr ;
        }
        
        public function changeSoldier($params)
        {
            $SoldierId = $params['SoldierId'];
            $LakeId = $params['LakeId'];
            if(!is_int($SoldierId) || !is_int($SoldierId) || ($SoldierId < 0) || ($LakeId <= 0))
                return array('Error' => Error::PARAM);
            $oLake = Lake::getById(Controller::$uId, $LakeId);
            if(empty($oLake))
                return array('Error' => Error::PARAM);
                
            $oSoldier = $oLake->getFish($SoldierId);
            
            if (!$oSoldier)
                return array('Error' => Error::OBJECT_NULL);
            if ($oSoldier->FishType!=FishType::SOLDIER)
                return array('Error' => Error::ID_INVALID);            
            if ($oSoldier->Status!=SoldierStatus::HEALTHY)
                return array('Error' => Error::SOLDIER_EXPIRED);
                
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);
            if(empty($oProfile))
               return array('Error' => Error::OBJECT_NULL);
            
            $oStoreEquip = StoreEquipment::getById(Controller::$uId);
            $mySoldierEquip = $oStoreEquip->SoldierList[$SoldierId] ;
            $mySoldierMeridian = $oStoreEquip->listMeridian[$SoldierId];
            $sProfile = array(
                'Soldier' => $oSoldier,
                'Equipment' => $mySoldierEquip['Equipment'],
                'Index' => $mySoldierEquip['Index'],
                'Meridian' => $mySoldierMeridian, 
            );
            $oProfile->setCurrSoldier($SoldierId, $LakeId, $sProfile);  
            $oProfile->save();
            
            return array('Error' => Error::SUCCESS);
        }
        /**
        * logic gettop:
        * in top, all alway get real
        * otherwise, aftering occupy, get real. 
        * get Cache
        */
        public function getTop10Occupying()
        {
            $gotCache = true; 
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);
            if(is_object($oProfile))
            {
               $uRank = $this->_getRank();
                // reduce retrievel from mysql server            
                if($uRank < OccupyFea::RANK_SHOW_TOP10_REAL)
                    $gotCache = false;
                else 
                {
                    if($oProfile->GetTop10Real)
                    {
                        $gotCache = false;
                        $oProfile->toggleGetTop();
                        $oProfile->save();
                    }
                } 
            }

            // check cache
            $Top10Cache = DataProvider::get('share','Top10OccupierCache');
            if(empty($Top10Cache))
                $gotCache = false;
                        
            if(!$gotCache)
            {
                $rankuid = array(1,2,3,4,5,6,7,8,9,10);
                $Top10Cache = $this->_getUidsFromRanks($rankuid);
                DataProvider::set('share', 'Top10OccupierCache', $Top10Cache);
            }
            
            $TopOccupying = array();
            foreach($Top10Cache as $rank=>$uId)
                {
                    $oUser = User::getById($uId);                    
                    $profile = array(
                        'Id' => $uId,
                        'Name' => $oUser->Name,
                        'Avatar' => $oUser->AvatarPic,
                        'Rank' => $rank
                    );
                    $TopOccupying[$rank] = $profile ;
                }
            $runArr['Error'] = Error::SUCCESS;
            $runArr['TopOccupying'] = $TopOccupying;
            
            return $runArr;
        }
        
        public function buyToken($params)
        {
            $ItemType = $params['ItemType'];
            $ItemId = $params['ItemId'];
            $Num = $params['Num'];
            $PriceType = $params['PriceType'];
            
            if(($ItemType != OccupyFea::TOKEN) || (!is_int($Num)) || ($Num <= 0))
                return array('Error' => Error::PARAM); 
              
            $BuyTokenConf = Common::getConfig('Param', 'Occupy', 'BuyToken'); 
               
            if(!is_numeric($BuyTokenConf[$ItemId][$PriceType]))
                return array('Error' => Error::PARAM);                     
            
            $cost = $BuyTokenConf[$ItemId][$PriceType] * $Num ;
            
            $oUser = User::getById(Controller::$uId);
            switch($PriceType)
            {
                case Type::ZMoney:
                    $info = $ItemId . ':' . $ItemType . ':' . $Num ;
                    if(!$oUser->addZingXu(-$cost, $info))
                        return array('Error' => Error::NOT_ENOUGH_ZINGXU);    
                    break;
                
                default:
                   return array('Error' => Error::ACTION_NOT_AVAILABLE);   
            }
            
            $oStore = Store::getById(Controller::$uId);
            if(!$oStore->addItem($ItemType, $ItemId, $Num))
                return array('Error' => Error::ACTION_NOT_AVAILABLE);
            
            $oStore->save();
            $oUser->save();
            
            // log
            $runArr['Error'] = Error::SUCCESS;
            $runArr['ZMoney'] = $oUser->ZMoney;
            $runArr['Money'] = $oUser->Money;
            
            //log 
            Zf_log::write_act_log(Controller::$uId, 0, 23, 'buyOccupyToken', 0, -$cost, $ItemType, $ItemId, $oStore->Items[$ItemType][$ItemId], 0,$Num);             
            
            return $runArr;
        }
        
        public function getGiftOccupied()
        {            
            $oProfile = OccupyingProfile::getByUid(Controller::$uId);
             if(empty($oProfile))
                {       // not join occupy, at view mode
                    return array(
                        'Error' => Error::SUCCESS,
                        'LastGiftRank' => OccupyFea::RANK_END_BOARD + 1,     // ko co qua
                        'LastGift' => array(),
                    ); 
                }
             $giftRank = $this->_checkIsGotGift($oProfile, true);
             
             if(!$giftRank)
                return Common::returnError(Error::ACTION_NOT_AVAILABLE);
             if($giftRank == Error::SET_GIFT_UNFINISHED)
                return Common::returnError(Error::SET_GIFT_UNFINISHED);
                             
             $uRank = $giftRank['rank'];
             $GiftConfig = Common::getConfig('Occupy_Gifts', 'Top');
             $Gifts = $this->_getGiftTop($uRank, $GiftConfig);
             $GiftSaved = Common::addsaveGiftConfig($Gifts, $Element, SourceEquipment::OCCUPY);
             
             $oProfile->LastOccupiedTime = $_SERVER['REQUEST_TIME'];

             // join new board, after reset old-gotgift board
             $resJoined = $this->_joinRankBoard();
             switch($resJoined)
            {
                 case OccupyFea::FULL_BOARD:
                    $oProfile->CurrRank = OccupyFea::RANK_END_BOARD + 1;
                    break;
                 case MySqlCode::QUERY_FAIL:
                 case false:
                    $oProfile->CurrRank = OccupyFea::NOT_ATTEND;
                    break;
                 default:
                    $oProfile->CurrRank = $resJoined;
                    break;
            }
            
            $oProfile->save(); 
             
             $runArr['Error'] = Error::SUCCESS;
             $runArr['LastGiftRank'] = $uRank;
             $runArr['LastGiftTime'] = $giftRank['time'];
             $runArr['LastGift'] = $GiftSaved;
             
             if(!empty($uRank) && ($uRank <= OccupyFea::RANK_END_BOARD))
                Zf_log::write_act_log(Controller::$uId, 0, 20, 'occupyRanked', 0, 0, $uRank, $giftRank['time']);
                
             return $runArr;
        }
        
        private function _joinRankBoard()
        {
            // check before
//            $currRank = DataRunTime::get('OccupyRankInitial', true); 
//            if($currRank >= OccupyFea::RANK_END_BOARD)
//                return OccupyFea::FULL_BOARD;   
            // check exist
            //$sql = 'select Rank from Occupy_OccupyingBigBoard where Uid = ' . Controller::$uId ;
            $choseSoldier = Lake::selectSoldierId(Controller::$uId);
            if(!$choseSoldier)
                return false;
                
            $currRank = $this->_getRank();
            if($currRank <= OccupyFea::RANK_END_BOARD)
                return $currRank;    
            
            $currRank = DataRunTime::inc('OccupyRankInitial', 1, true);
            if($currRank > OccupyFea::RANK_END_BOARD)
                return OccupyFea::FULL_BOARD;                
                             
            // satisfied
            $sql = 'insert ignore into Occupy_OccupyingBigBoard(Rank, Uid) values (' . $currRank. ',' . Controller::$uId . ')'  ;
            $res = Common::queryMySql(OccupyFea::CODE, $sql);
            if(!$res)
                return MySqlCode::QUERY_FAIL;
            return $currRank;                
        }
        
        private function _getRank($ranking = true)
        {
            $rank = OccupyFea::RANK_END_BOARD + 1;
            // get rank Occupying
            if($ranking)
            {
                $sql = 'select Rank from Occupy_OccupyingBigBoard where Uid = ' . Controller::$uId ;
                $res = Common::queryMySql(OccupyFea::CODE, $sql);
                if(!$res)
                    die('Khong lay dc Rank nguoi dung');
                if($row = mysql_fetch_array($res, MYSQL_ASSOC))
                    $rank = ($row['Rank'] > OccupyFea::RANK_END_BOARD) ? $rank : $row['Rank'];                                
            }
                        
            return $rank;
        }
        
        private function _getUidsFromRanks($ranks)
        {
            $uIds = array();
            if(!is_array($ranks)) $ranks = (array)$ranks;
            
            $sql =  'select Rank, Uid from Occupy_OccupyingBigBoard where Rank IN (' . implode(",",$ranks) . ')';
            $res = Common::queryMySql(OccupyFea::CODE, $sql);
            if(!$res)
                die('Khong lay dc Id nguoi dung');

            while($row = mysql_fetch_array($res, MYSQL_ASSOC))
            {
                $uIds[$row['Rank']] = $row['Uid'];
            }
            
            return $uIds;
        }
        
        private function _getUidFromRank($rank)
        {
            $sql = 'select Uid from Occupy_OccupyingBigBoard where Rank = ' . $rank ;  
           $res = Common::queryMySql(OccupyFea::CODE, $sql);
           if(!$res)
                die('Khong lay dc Id nguoi dung');
           if($row = mysql_fetch_array($res, MYSQL_ASSOC))
                return $row['Uid'];
           else return false;
           
        }
        
        private function _getRanksBoard($RankBoardConfig, $uRank)
        {
            $rankLevel = array_keys($RankBoardConfig);
            arsort($rankLevel);
            for($i = 0 ; $i < count($rankLevel); $i ++)
            {
                if($uRank > $rankLevel[$i])     // find level satistified
                {
                    $numOccupier = $RankBoardConfig[$rankLevel[$i]]['NumOccupier'];
                    $step =  $RankBoardConfig[$rankLevel[$i]]['StepRank'];
                    break;
                }
            }
            // rank > top board & rank in top board
            $RankBoardStart = ($uRank > OccupyFea::RANK_END_BOARD) ? OccupyFea::RANK_END_BOARD : $uRank;
            // rank in top board & rank in top 10 Num Occupier
            if($RankBoardStart == $uRank)
            {
                $otherRankNum = $numOccupier - 1; // others
                $RankBoardStart = (($RankBoardStart - $otherRankNum * $step) <= 0) ? (OccupyFea::RANK_TOP_1st + $otherRankNum * $step) : $RankBoardStart;              
            }
            
            $BoardRanks = array();    
            for($i = 0; $i < $numOccupier; $i ++)
            {
                $BoardRanks[$i] = $RankBoardStart - $i*$step;
            }
            
            return $BoardRanks;                    
        }
        
        private function _bakRankBigBoard($time)
        {                        
            $sql = "call BakOccupiedBigBoard ({$time})";
            $res = Common::queryMySqli(OccupyFea::CODE, $sql);
            if(($res['Error'] != Error::SUCCESS) && ($res['Error'] != MySqlCode::ERROR_INSERT_DUPLICATE))
            {
                return false; 
            }
                
            
            $RankBigBoardBak = array();
             $sql = "select Rank, Uid from Occupy_TempBigBoard where RankTime = {$time} order by Rank ASC" ;            
             $res = Common::queryMySql(OccupyFea::CODE,$sql);
             if(!$res)
             { 
                return false; 
             }
                
             while($row = mysql_fetch_array($res,MYSQL_ASSOC))
             {   
                 $RankBigBoardBak[$row['Uid']] = $row['Rank'];
             }
                          
             if(count($RankBigBoardBak) == 0)
             {
                 // check bak up
                 $sql = "select Rank, Uid from Occupy_OccupiedBigBoardBak where RankTime = {$time} order by Rank ASC" ;            
                 $res = Common::queryMySql(OccupyFea::CODE,$sql);
                 if(!$res)
                 {                     
                    return false; 
                 }
                    
                 while($row = mysql_fetch_array($res,MYSQL_ASSOC))
                 {   
                     $RankBigBoardBak[$row['Uid']] = $row['Rank'];
                 }
                 if(count($RankBigBoardBak) == 0)
                    return Error::SET_GIFT_UNFINISHED;
             }   
             
             if(!DataRunTime::setDataTime('OccupiedBak', $time, $RankBigBoardBak, OccupyFea::DAYS_GET_GIFT*24*3600))
             {
                return false; 
             }
                
             else 
             {
                 // allow joinBoard
                DataRunTime::set('OccupyRankInitial', 0, true);
                // delete temp
                $sql = "call DoneRankedOccupied()";
                $res = Common::queryMySql(OccupyFea::CODE, $sql);

                return $RankBigBoardBak;                    
             }             
        }
        
        private function _getGiftTop($uRank, $GiftConf)
        {
            $topLst = array_keys($GiftConf);
            asort($topLst);
            foreach($topLst as $index => $top)
            {
                if($uRank <= $top)
                    return $GiftConf[$topLst[$index]];
            }
            
            return array();
        }
        
        private function _checkCoolDown($type, $oProfile)
        {
            $timeCoolDown = Common::getConfig('Param', 'Occupy', 'CoolDown');
            $timeCoolDown = $timeCoolDown[$type];
            
            $property = "Last{$type}";
            if(($_SERVER['REQUEST_TIME'] - $oProfile->$property) < $timeCoolDown)
                return false;
            else return true;
        }
        
        private function _setCoolDown($type, $oProfile)
        {
            $property = "Last{$type}";
            $oProfile->$property = $_SERVER['REQUEST_TIME'];
        }
        
        private function _useToken($oProfile, $oStore, $Num)
        {            
             if(!$oStore->useItem(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_DEFAULT, $Num))
                return false;
             $oProfile->addItems(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_GIFT, -$Num);
             return true;
        }
        
        private function _giftToken($oProfile)
        {            
            if(date('Ymd', $_SERVER['REQUEST_TIME']) != $oProfile->LastGiftToken) 
                {
                   $lastGiftToken = $oProfile->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_GIFT];
                   $configGift = Common::getConfig('Param', 'Occupy', 'GiftToken');
                   $oProfile->addItems(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_GIFT, $configGift - $lastGiftToken);                    
                   $oProfile->LastGiftToken = date('Ymd', $_SERVER['REQUEST_TIME']) ;
                   
                   $oStore = Store::getById(Controller::$uId);
                   $oStore->addItem(OccupyFea::TOKEN, OccupyFea::TOKEN_ID_DEFAULT, $configGift - $lastGiftToken);                   
                                      
                   $oStore->save();     
                   return array('expired' => $lastGiftToken, 'curr' =>$oStore->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_DEFAULT]);
                }
            else 
                return false;
        }
        
        private function _checkIsGotGift($oProfile, $online = true)
        {
            $request_time = $_SERVER['REQUEST_TIME'];
             $currTime = date('H:i:s', $request_time);
             $currDate = date('Y-m-d', $request_time);
             
             $timeEndConf = Common::getConfig('Param', 'Occupy', 'TimeEndInDay');
             $LastGotGift = $oProfile->LastGotGiftOccupiedCampain;                                                             
                          
             if($online)             
             {
                $GiftEndOccupyTime = strtotime($currDate . ' ' . $timeEndConf);                      
                if($currTime < $timeEndConf)        // chua den h
                {
                    return false;
                }                    

                if($LastGotGift == $GiftEndOccupyTime)       // da nhan
                {                    
                    return false;                                                                   
                }                    
             }
             else
             {
                $LastOccpiedDate = date('Y-m-d', $oProfile->LastOccupiedTime);                
                $LastOccpiedTime = date('H:i:s', $oProfile->LastOccupiedTime);
                
                if($LastOccpiedTime <= $timeEndConf)
                    $LastGetGiftDate = $LastOccpiedDate;
                else                
                    $LastGetGiftDate = date('Y-m-d',strtotime($LastOccpiedDate) + 24 * 3600);
                
                $GiftEndOccupyTime =  strtotime($LastGetGiftDate . ' '. $timeEndConf);
                                    
                if(!(($LastGotGift < $GiftEndOccupyTime)&&($GiftEndOccupyTime < $request_time)))
                    return false;
             }
             if($oProfile->CurrRank <= OccupyFea::RANK_END_BOARD)
             {
                $RankBigBoardBak = DataRunTime::getDataTime('OccupiedBak', $GiftEndOccupyTime);
                 if(!$RankBigBoardBak)
                 {
                     if(($request_time - $GiftEndOccupyTime) >= OccupyFea::DAYS_GET_GIFT*24*3600)   // expired gift
                        return Error::GET_GIFT_EXPIRED;
                     if($online || ($LastOccpiedDate <= $currDate)) $RankBigBoardBak = $this->_bakRankBigBoard($GiftEndOccupyTime);     // bak when: one online Occupying Zone or noone online, getOccupying later
                     if(!$RankBigBoardBak)          // error bak
                     {                        
                        return false; 
                     }
                        
                     if($RankBigBoardBak == Error::SET_GIFT_UNFINISHED)
                        return Error::SET_GIFT_UNFINISHED;           
                 }
                   
                 $uRank = $RankBigBoardBak[Controller::$uId];     
             }
             
             if(empty($uRank))      // out of top
                 $uRank = OccupyFea::RANK_END_BOARD + 1;
                              
             $oProfile->LastGotGiftOccupiedCampain = $GiftEndOccupyTime;
                          
             return array('rank' => $uRank, 'time' => $GiftEndOccupyTime);
        }
        
        private function _checkDateReset($oProfile)
        {
            if(date('Ymd', $_SERVER['REQUEST_TIME']) != $oProfile->LastDateForReset)
            {
               // limit Occupy
               $MaxLimit = Common::getConfig('Param', 'Occupy', 'MaxOccupy');
               $oProfile->RemainOccupyCount = $MaxLimit;
               $oProfile->LastDateForReset = date('Ymd', $_SERVER['REQUEST_TIME']);
               return;
            }
            else return;
        }               
    }    
?>
