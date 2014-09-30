<?php
/**
* noi xu ly cac minigame
*/
  class MiniGame extends Model
  {
      public $GameList = array();   // noi chua noi dung cac game
      
      public function __construct($uId)
      {
          $this->GameList = array();
          $this->addNewGame();
          parent :: __construct($uId);
      }
      
      public function addNewGame($GameName = null)
      {
          $oUser = User::getById(Controller::$uId);
          $NowTime = $_SERVER['REQUEST_TIME'] ;
          if(!empty($GameName))
          {
                if(self::checkMinigameCondition($GameName))
                {
                    if(isset($this->GameList[$GameName]))
                        return false ;
                    $this->GameList[$GameName] = array();
                }
          }
          else
          {
              $Conf = Common::getConfig('Event');
              foreach($Conf as $KeyGame => $value)
              {
                  if(self::checkMinigameCondition($KeyGame))  
                  {
                      if(isset($this->GameList[$KeyGame]))
                        continue;
                      $this->GameList[$KeyGame] = array();    
                  }
                   
              }   
          }    
          
      }

      public static function getById($uId)
      {
        $object = DataProvider :: get($uId,'MiniGame') ;
        if(!is_object($object))
        {
            $object = new MiniGame($uId);
            $object->save();
        }       
        return $object ;
      }   
    
    /**
    * ham thuc hien random ra qua
    * 
    */
    public function lm_randomGift($TicketType)
    {
        // luu lai ticket
        $this->GameList[GameType::LuckyMachine]['TicketType'] = $TicketType ;
        
        $conf = Common::getConfig('M_LuckyGiftRate',$TicketType);
        $PlayNum = intval($this->GameList[GameType::LuckyMachine]['PlayNum']);
        foreach($conf as $id => $value)
        {
            $arr_rand[$id] = $value['Rate'.$PlayNum]*100;
        }
        $GiftId = Common::randomIndex($arr_rand);
        return $GiftId ;
    }
    
    /**
    * ham thuc hien update qua moi va luu tam thoi 
    * 
    */
    public function lm_updateGift($GiftId,$conf)
    {
        $TicketType = $this->GameList[GameType::LuckyMachine]['TicketType'];
        $Detailconf = Common::getConfig('M_GiftContent',$TicketType);
        
        $arr = array();
        if(empty($this->GameList[GameType::LuckyMachine]['GiftArr'])) // quay lan dau
        {
            $arr['ItemType'] = $conf['ItemType'];
            $arr['LevelGift'] = $conf['LevelGift'];
            $arr['TicketType'] = $TicketType ;
            $arr['GiftId']     = $GiftId ;
            
        }
        else // quay lan thu 2 tro di 
        {
            $OldItemType    = $this->GameList[GameType::LuckyMachine]['GiftArr']['ItemType'];
            $OldId          = $this->GameList[GameType::LuckyMachine]['GiftArr']['LevelGift'];
            $TicketType     = $this->GameList[GameType::LuckyMachine]['GiftArr']['TicketType'];
            if($conf['ItemType'] == $OldItemType)
            {
                $arr['ItemType']    = $conf['ItemType'];
                $arr['TicketType']  = $TicketType ;
                $arr['LevelGift']   = $conf['LevelGift'] + $OldId ;
                $MaxId = count($Detailconf[$OldItemType]) ;
                $arr['LevelGift'] = $arr['LevelGift'] > $MaxId ? $MaxId:$arr['LevelGift'] ;
                $arr['GiftId']     = $GiftId ;
            }
        }
        
        $this->GameList[GameType::LuckyMachine]['GiftArr'] = $arr ;
    }
    
    // luu qua 
    public function lm_saveGift()
    {
        // luu qua cho user
        $gift = $this->GameList[GameType::LuckyMachine]['GiftArr'];
        if(empty($gift)) return false ;
        $TicketType = $gift['TicketType'];
        
        $conf = Common::getConfig('M_GiftContent',$TicketType,$gift['ItemType']);
        $conf = $conf[$gift['LevelGift']] ;
        
        if(empty($conf)) return false ;
        
        $ItemType   = $gift['ItemType'] ;
        $ItemId     = intval($conf['ItemId']) ;
        $Num        = intval($conf['Num']) ;
        $Color      = intval($conf['Color']);
        $GiftId     = $gift['GiftId'] ;    
        
        $oUser = User::getById(Controller::$uId);
        $oStore = Store::getById(Controller::$uId);
        $oEquipment = array();
        if(SoldierEquipment::checkExist($ItemType))
        {
            $arr['Source'] = SourceEquipment::LUCKYMACHINE ;
                        
            $oEquipment = Common::randomEquipment($oUser->getAutoId(),$ItemId,$Color,SourceEquipment::LUCKYMACHINE,$ItemType);
        
            $oStore->addEquipment($ItemType, $oEquipment->Id, $oEquipment);
            
            // them gioi han vu khi vip cua user
            if($oEquipment->Color >= 5)
                DataRunTime::inc('LuckyMachine_VipWeapon',1); 
        }
        else
        {
            $arr[1]['ItemType'] = $ItemType;
            $arr[1]['ItemId']   = $ItemId;
            $arr[1]['Num']      = $Num;
            
            $oUser->saveBonus($arr) ;   
        }
        
        // them vao gioi han giai tren 1 user
        $this->GameList[GameType::LuckyMachine]['Limit'][$gift['TicketType'].'_'.$gift['ItemType'].'_'.$gift['LevelGift']]++;
        
        // them gioi han giai 6 tren toan server
        if($gift['LevelGift'] >= 6)
            DataRunTime::inc('LuckyMachine_Gift6',1);
                
        // xoa qua di 
        $this->GameList[GameType::LuckyMachine]['GiftArr'] = array() ;
               
        return $oEquipment ;
    }
    
    public function updateFirstTimeOfDay()
    {
        if(isset($this->GameList[GameType::LuckyMachine]['Limit']))
        {
            $this->GameList[GameType::LuckyMachine]['Limit'] = array() ;
        }
    }
    
    //chan ko cho ra qua cap 6
    public function lm_checkRandomGift($GiftId,$M_LuckyGiftRate,$M_GiftContent)
    {
        
        $result = $this->checkSeverLimit($GiftId,$M_LuckyGiftRate,$M_GiftContent);
        
        $gift = $this->GameList[GameType::LuckyMachine]['GiftArr'];
        if(empty($gift))// quay lan dau tien
        {
            
            $TicketType = $this->GameList[GameType::LuckyMachine]['TicketType'];

            
            $UserLimit = intval($this->GameList[GameType::LuckyMachine]['Limit'][$TicketType.'_'.$M_LuckyGiftRate[$GiftId]['ItemType'].'_'.$M_LuckyGiftRate[$GiftId]['LevelGift']]);
            
            $confLimit = intval($M_GiftContent[$M_LuckyGiftRate[$GiftId]['ItemType']][$M_LuckyGiftRate[$GiftId]['LevelGift']]['LimitNum']);
            
            if($confLimit > 0)
            {
                if(intval($UserLimit) < intval($confLimit) && !$result  )//$result = true -> da vuot gioi han
                    return false ;   
            } 
            else
            {
                return $result ;
            }                           
        }
        else
        {// quay lai
            if($gift['ItemType']== $M_LuckyGiftRate[$GiftId]['ItemType'])
            {
                $NewlevelGift = $gift['LevelGift'] + $M_LuckyGiftRate[$GiftId]['LevelGift'] ;
                
                $MaxId = count($M_GiftContent[$gift['ItemType']]) ;
                
                $NewlevelGift = $NewlevelGift > $MaxId ? $MaxId:$NewlevelGift;
                
                $UserLimit = intval($this->GameList[GameType::LuckyMachine]['Limit'][$gift['TicketType'].'_'.$gift['ItemType'].'_'.$NewlevelGift]);
                
                $confLimit = $M_GiftContent[$M_LuckyGiftRate[$GiftId]['ItemType']][$NewlevelGift]['LimitNum'] ;
                
                if($confLimit > 0)
                {
                    if(intval($UserLimit) < intval($confLimit) && !$result  )//$result = true -> da vuot gioi han
                        return false ;   
                } 
                else
                {
                    return $result ;
                }
                    
                
            }
            else
            {
                return false ;
            }
        }
        
        return true;
    }
    
    public function checkSeverLimit($GiftId,$M_LuckyGiftRate,$M_GiftContent)
    {
                
        $gift = $this->GameList[GameType::LuckyMachine]['GiftArr'];
        if(empty($gift))// quay lan dau tien
        {           
            // gioi han vu khi vip tren toan Server
            $color = $M_GiftContent[$M_LuckyGiftRate[$GiftId]['ItemType']][$M_LuckyGiftRate[$GiftId]['LevelGift']]['Color'];
            if(intval($color) >= 5)
            {
                $ServerLimit_weapon = intval(DataRunTime::get('LuckyMachine_VipWeapon')); 
                $LimitWeaponInServer = Common::getConfig('Event');
                $LimitWeaponInServer = intval($LimitWeaponInServer['LuckyMachine']['LimitWeaponInServer']);
                     
                if($ServerLimit_weapon >= $LimitWeaponInServer )
                    return true ;      
            }
        }
        else
        {// quay lai
            if($gift['ItemType']== $M_LuckyGiftRate[$GiftId]['ItemType'])
            {
                $NewlevelGift = $gift['LevelGift'] + $M_LuckyGiftRate[$GiftId]['LevelGift'] ;
                
                $MaxId = count($M_GiftContent[$gift['ItemType']]) ;
                
                $NewlevelGift = $NewlevelGift > $MaxId ? $MaxId:$NewlevelGift;
                
                // check giai 6 toan server
                if($NewlevelGift == $MaxId)
                {
                    $ServerLimit = intval(DataRunTime::get('LuckyMachine_Gift6'));
                
                    $confServerLimit = Common::getConfig('Event');
                    $confServerLimit = intval($confServerLimit['LuckyMachine']['ServerLimit']);
                    
                    if($ServerLimit >= $confServerLimit)
                        return true ;   
                }      
            }
        }
        
        return false;
    }
    
    // check dieu kien ton tai cua minigame
    public static function checkMinigameCondition($MinigameName)
    {
        if(!GameType::check($MinigameName))
            return false ;
        $Today = $_SERVER['REQUEST_TIME'];
        $Eventconf = Common::getConfig('Event',$MinigameName);
        $oUser = User::getById(Controller::$uId);
        if(!is_array($Eventconf) || !is_object($oUser))
        return false ;
        $Begin        = $Eventconf['BeginTime'];
        $Expired      = $Eventconf['ExpireTime'];
        $BeginLevel   = $Eventconf['BeginLevel']; 
        $EndLevel     = $Eventconf['EndLevel'];       
      
        return (($oUser->Level >= $BeginLevel)&&($oUser->Level < $EndLevel)&&($Today<$Expired)&&($Today>$Begin));
    }
    
    
  }
?>
