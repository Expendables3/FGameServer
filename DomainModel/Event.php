<?php
/**
* recreate, reduce file size. old: EventBak.php
* author taint
* 2012-10-09
*/
class Event extends Model
{
    public $EventList = array() ;
    public $HideParam = array(); // tham so nay ko tra ve user , cau truc giong nhu EventList
    
    public function __construct($uId)
    {
        // khoi tao 
        $newEvent = $this->createEvent();
        if(!empty($newEvent))
        {
            foreach($newEvent as $index => $keyEvent)
            {
                $this->resetEvent($keyEvent);
            }
        }
        //dua du lieu ve trang thai mac dinh
        //$this->resetEvent();
        parent::__construct($uId);
    }
    
    // check dieu kien ton tai Event
    public static function checkEventCondition($EventKey,$extraDay = 0)
    {
      $Today = $_SERVER['REQUEST_TIME'];
      $Eventconf = Common::getConfig('Event',$EventKey);
      $oUser = User::getById(Controller::$uId);
      if(!is_array($Eventconf) || !is_object($oUser))
        return false ;
      $Begin        = $Eventconf['BeginTime'];
      $Expired      = $Eventconf['ExpireTime'] + intval($extraDay*24*3600);
      $BeginLevel   = $Eventconf['BeginLevel']; 
      $EndLevel     = $Eventconf['EndLevel'];       
      
      return (($oUser->Level >= $BeginLevel)&&($oUser->Level < $EndLevel)&&($Today<$Expired)&&($Today>$Begin));
    }
  
  // khoi tao Event
    public function createEvent($Type = 'ALL')
    {
        $newEvent = array();
      if($Type == 'ALL')
      {
        $EventConf = Common::getConfig('Event');
        foreach ($EventConf as $EventKey => $arrEvent)
        {
            if(!is_array($arrEvent))
              continue ;
           if(self::checkEventCondition($EventKey))
           {
               if(!isset($this->EventList[$EventKey]))
               {
                   $this->EventList[$EventKey] = array();
                   $newEvent[] = $EventKey;
               }
           }
        }
      }
      
      return $newEvent ;
      
    }
    
    // Reset Event
    public function resetEvent($EventKey = '')
    {
        $arr_EventKey[0] = $EventKey ;       
        
        foreach($arr_EventKey as $index => $Key)
        {
            switch($Key)
            {                   
                case EventType::Noel:
                {
                    $this->EventList[$Key]['Level']         = 1 ;
                    $this->EventList[$Key]['CareNum']       = 0 ;
                    $this->EventList[$Key]['SpeedUpNum']    = 0 ;
                    $this->EventList[$Key]['LastCareTime']  = 0 ;
                    $this->EventList[$Key]['Seal']          = array(1=>3,2=>3); // gioi han moi loai chi cho ra 3 cai
                    $this->EventList[$Key]['FireFish']      = array('StartTime'=>-1,'FinishTime'=>-1,'NumPlay'=>0); // gioi han moi loai chi cho ra 3 cai
                    
                    break;
                }
                case EventType::TreasureIsland:
                    $this->island_ResetMap();
                    break;
                case EventType::Halloween:
                    $this->hal_init();
                    break;
                default :
                    break;
            
            }
        }
        
        
    }
    
    // reset data KeepLogin
    public function resetDataKeepLogin(){
        $oKeepLogin = KeepLogin::getById(Controller::$uId);
        $oKeepLogin->resetDataKeepLogin();
        $oKeepLogin->save();
    }    
    // get event
    public function getEvent($key)
    {
        return $this->EventList[$key] ;
    }     
    
    // xoa Event
    public function deleteEvent($EventKey = '')
    {
        if(empty($EventKey))
        {
            foreach($this->EventList as $EventKey => $arrEvent)
            {
                if(!self::checkEventCondition($EventKey))
                {
                    unset($this->EventList[$EventKey]);
                }
            }
        }
        else
        {
            if(!self::checkEventCondition($EventKey))
            {
                unset($this->EventList[$EventKey]);
            }
        }
        
    }
    
    public static function getById($uId)
    {
        return DataProvider :: get($uId,__CLASS__) ;
    }
    
    public function updateFirstTimeOfDay()
    {
        
        // reset number speedup at the start day time
        if(self::checkEventCondition(EventType::Noel)) {
            $this->EventList[EventType::Noel]['SpeedUpNum'] = 0;            
            $this->EventList[EventType::Noel]['FireFish']['StartTime'] = -1;
            $this->EventList[EventType::Noel]['FireFish']['FinishTime'] = -1;            
            $this->EventList[EventType::Noel]['FireFish']['NumPlay'] = 0;            
        }
    }
    
    /** 
    * save changes, not all as save all at particular time
    */
    public function saveComponents()
    {
       $this->forceSave();  
    }
    
    // update gift for event when levelup from 6 to 7
    public function updateGiftAtLevelUp()
    {
    }

    public function checkCoolDown($duration_cd, $event_type, $type_cd)
    {
        $property = 'Last'.$type_cd ;
        if(($_SERVER['REQUEST_TIME'] - $this->EventList[$event_type][$property]) < $duration_cd)
            return false;
        else return true; 
    }
    
    public function setCoolDown($event_type, $type_cd)
    {
        $property = 'Last'.$type_cd ; 
        $this->EventList[$event_type][$property] = $_SERVER['REQUEST_TIME'];        
    }

    public static function getActionGiftInEvent($EventType, $key1, $key2, $key3 = '', $key4 = '')
    {
        $arr_gift = array();
        if(!self::checkEventCondition($EventType) && !MiniGame::checkMinigameCondition($EventType) )
        {
            return $arr_gift;
        }
        $conf = Common::getConfig('Event_ActionGift',$key1,$key2);
        if(!empty($key3))
             $conf = $conf[$key3];
        if(!empty($key4))
             $conf = $conf[$key4];            
        $conf = $conf['gift'];        
        if(empty($conf))
            return $arr_gift;
         
         //$oUserPro = UserProfile::getById(Controller::$uId);        
             
        // rate one or more gift 
        foreach($conf as $id => $gift)
        {
           if(rand(1,100) > $gift['Rate'])  continue;
           //if(!$oUserPro->checkOverInLimitOnDayList($gift['ItemType'],50))
            //    continue; 
            $arr_gift[$id]['ItemType']    = $gift['ItemType'];
            $arr_gift[$id]['ItemId']      = $gift['ItemId'];
            $arr_gift[$id]['Num']         = round($gift['Num']);
            if(is_array($gift['Num']))
            {
                $idd = array_rand($gift['Num'],1);
                $arr_gift[$id]['Num'] = round($gift['Num'][$idd]) ;
            }
            
            // special for each event
            switch($gift['ItemType'])
            {
                 case 'PackItem':   // event Halloween
                    $PackConf = Common::getConfig('Hal2012_RatePackItem', $gift['ItemId']);
                    $Total = $arr_gift[$id]['Num'];
                    $itemRate = array();
                    foreach($PackConf as $itemId => $item)
                    {
                        $itemRate[$itemId] = $item['Rate'];
                    }
                    
                    for($i = 1; $i <= $Total; $i++)
                    {
                        $itemId = Common::randomIndex($itemRate);
                        $item = $PackConf[$itemId];
                        $num = $Pack[$item['ItemId']];
                        $num = (empty($num)) ? 1 : ($num + 1);
                        $Pack[$item['ItemId']] = $num;
                    }                    
                    foreach($Pack as $itemid => $num)
                    {
                         $arr_gift[] = array(
                            'ItemType' => 'HalItem',
                            'ItemId' => $itemid,
                            'Num' => $num
                        );
                    }
                    
                    unset($arr_gift[$id]);
                    
                    break;
            } 
            
            
           // $oUserPro->updateInLimitOnDdayList($arr_gift[$id]['ItemType'],$arr_gift[$id]['Num']) ;                   
            
        } 
        //$oUserPro->save() ;                 
        return $arr_gift ;
    }
    /***
    * Apis for particular event
    */
     // Event Collection Pattern
     
     private function colp_init()
     {
         $this->EventList[EventType::CollectPattern]['Items'] = array();
         $this->EventList[EventType::CollectPattern]['RetainExchange'] = array();
         $this->EventList[EventType::CollectPattern]['PointGift'] = array(
            'Point' => 0,
            'Got' => array(),
         );
         
         $confExchange = Common::getConfig('ColP_ExchangeGift');
         foreach($confExchange as $Type => $confType)
         {             
             foreach($confType as $Id => $confId)
             {
                 if(!empty($confId['Max']))
                    $this->EventList[EventType::CollectPattern]['RetainExchange'][$Type][$Id] = $confId['Max']['Num'];
             }             
         }
     }
     
     public function colp_addPointGift($point)
     {
         if($point > 0)
            $this->EventList[EventType::CollectPattern]['PointGift']['Point'] += $point;
         return $this->EventList[EventType::CollectPattern]['PointGift']['Point'] ;
     }
     
     public function colp_getPointGift()
     {
        return $this->EventList[EventType::CollectPattern]['PointGift'];
     }
     
     public function colp_setPointGift($pointGift)
     {
        $this->EventList[EventType::CollectPattern]['PointGift'] = $pointGift;
        return true;
     }
     
     public function colp_addItem($ItemType, $ItemId, $Num)
     {
         if(!isset($this->EventList[EventType::CollectPattern]['Items'][$ItemType][$ItemId]))
            $this->EventList[EventType::CollectPattern]['Items'][$ItemType][$ItemId] = $Num;
         else
            $this->EventList[EventType::CollectPattern]['Items'][$ItemType][$ItemId] += $Num;
     }
     
     public function colp_useItem($ItemType, $ItemId, $Num)
     {
         if(empty($this->EventList[EventType::CollectPattern]['Items'][$ItemType][$ItemId]))
            return false;
         $currNum = &$this->EventList[EventType::CollectPattern]['Items'][$ItemType][$ItemId];
         if(($currNum - $Num) < 0)
            return false;
         $currNum -= $Num;
         return true;
     }
     
     public function colp_addExchangeTimes($ItemType, $ItemId, $Num)
     {
         if(empty($this->EventList[EventType::CollectPattern]['RetainExchange'][$ItemType][$ItemId]))
         {
             return false;
         }                  
         $CurNum = &$this->EventList[EventType::CollectPattern]['RetainExchange'][$ItemType][$ItemId];
         if($CurNum == 0)
            return false;
         if(($CurNum - $Num) < 0)
         {
             $real = $Num - $CurNum;
             $CurNum = 0;
         }
         else
         {
             $real = $Num;
             $CurNum -= $Num;
         }
         return $real;
     }
     // End Event Collection Pattern

     // Event 8-3 backup to file bak    

     /**
     *  BEGIN EVENT NOEL 2012  #NOEL2012
     */     
     private function eNoel_init()
     {
         $this->EventList[EventType::Noel] = array();
     }
     
     public function eNoel_addItem($ItemType, $ItemId, $Num)
     {
         if(!isset($this->EventList[EventType::Noel][$ItemType][$ItemId]))
            $this->EventList[EventType::Noel][$ItemType][$ItemId] = $Num;
         else
            $this->EventList[EventType::Noel][$ItemType][$ItemId] += $Num;
     }
     
     public function eNoel_useItem($ItemType, $ItemId, $Num)
     {
         if(empty($this->EventList[EventType::Noel][$ItemType][$ItemId]))
            return false;
         $currNum = &$this->EventList[EventType::Noel][$ItemType][$ItemId];
         if(($currNum - $Num) < 0)
            return false;
         $currNum -= $Num;
         return true;
     }     
     public function e_checkCareFlower()
    {
        $data = $this->EventList[EventType::Noel];
        $TreeConf = Common::getConfig('Noel_Tree');
        // check level
        if($data['Level'] > count($TreeConf))
            return false ;
        // check thoi gian
        $now = $_SERVER['REQUEST_TIME'];
        if($now < $data['LastCareTime']+ $TreeConf[$data['Level']]['CareTime'] )
            return false ;
        
        return true;
        
    }
    public function e_checkSpeedUp()
    {
        $data = $this->EventList[EventType::Noel];
        $TreeConf = Common::getConfig('Noel_Tree');
        // check level
        if($data['Level'] > count($TreeConf))
            return false ;
        // thoi gian da du thi ko duoc tang toc nua
        $now = $_SERVER['REQUEST_TIME'];
        if($now >= $data['LastCareTime']+ $TreeConf[$data['Level']]['CareTime'] )
            return false ;
        // dat max so lan tang toc trong 1 giai doan cua cay
        if($data['SpeedUpNum'] >= $TreeConf[$data['Level']]['SpeedUpLimit'] )
            return false ;    
        return true;
        
    }
    
    // update level of Tree and last  care Time 
    public function e_updateLevelFlower()
    {
        $this->EventList[EventType::Noel]['CareNum']++ ;
        
        $TreeConf = Common::getConfig('Noel_Tree');
        $NowLevel = $this->EventList[EventType::Noel]['Level'] ;        
        if($this->EventList[EventType::Noel]['CareNum'] >= $TreeConf[$NowLevel]['CareNum'] )
        {
            $this->EventList[EventType::Noel]['CareNum']       = 0 ;
            $this->EventList[EventType::Noel]['LastCareTime']  = $_SERVER['REQUEST_TIME'];
            $this->EventList[EventType::Noel]['SpeedUpNum']    = 0 ;
            $this->EventList[EventType::Noel]['Level']++;
        }
        else
        {
            $this->EventList[EventType::Noel]['LastCareTime'] = $_SERVER['REQUEST_TIME'];
        }
        
    }
    // update level of Tree and last  care Time 
    public function e_updateSpeedUp()
    {     
        $TreeConf = Common::getConfig('Noel_Tree');
        $NowLevel = $this->EventList[EventType::Noel]['Level'] ;
        $this->EventList[EventType::Noel]['SpeedUpNum']++;
        $this->EventList[EventType::Noel]['LastCareTime']  -= $TreeConf[$NowLevel]['CareTime'];  
    }
    
    // #NOEL2012
    public function updateTimeFireFish(){
        $Now = $_SERVER['REQUEST_TIME'];
        $StartTime = $this->EventList[EventType::Noel]["FireFish"]["StartTime"];
        $FinishTime = $this->EventList[EventType::Noel]["FireFish"]["FinishTime"];
        $TimeConfig = Common::getConfig("Noel_BoardConfig","BoardGame");

        if($StartTime >0) {
            // check Time 
            $TimeConfig = Common::getConfig("Noel_BoardConfig","BoardGame");
            $TimeConfig = $TimeConfig["Time"];
            if( ($StartTime+$TimeConfig) < $Now){
                $this->EventList[EventType::Noel]["FireFish"]["StartTime"] = -1;
                if($FinishTime < $StartTime){
                   $this->EventList[EventType::Noel]["FireFish"]["FinishTime"] = $StartTime+$TimeConfig; 
                } 
            }            
            if($FinishTime > $StartTime) {
                $TimeConfig = Common::getConfig("Noel_BoardConfig",5);
                $TimeConfig = $TimeConfig["Time"];
                if( ($FinishTime +$TimeConfig) <$Now ) {
                    $this->EventList[EventType::Noel]["FireFish"]["StartTime"] = -1;    
                }
                
            }
        }              
    }
     
     /**
     *  END OF EVENT NOEL 2012
     */
     
     
     
    // join vao dao giau vang
    public function island_JoinIsland()
    {           
        // khoi tao map moi 
        $result = $this->island_CreateMap();
        if($result === false)
            return false ;
        $this->EventList[EventType::TreasureIsland]['Map']          = $result['Map'];  // map
        $this->EventList[EventType::TreasureIsland]['MapId']          = $result['MapId'];  // map
        $this->EventList[EventType::TreasureIsland]['JoinNum']      +=1;
        $this->EventList[EventType::TreasureIsland]['LastJoinTime'] = $_SERVER['REQUEST_TIME'];
        $this->EventList[EventType::TreasureIsland]['Treasure']     = array();  // qua nhan duoc tam thoi     
        $this->EventList[EventType::TreasureIsland]['TempGift']     = array();//qua tam thoi hien thi tren map chua nhat vao treasure
        $this->HideParam[EventType::TreasureIsland]['GiftOnMap']    = $result['GiftMap'];  // qua tren map ,ko tra ve cho client
        
        return $this->EventList[EventType::TreasureIsland];

    }
    
    // xoa thong tin map va qua tang 
    public function island_ResetMap()
    {
        $this->EventList[EventType::TreasureIsland]['Map']          = array();  // map
        $this->EventList[EventType::TreasureIsland]['MapId']        = 0 ;
        $this->EventList[EventType::TreasureIsland]['TempGift']     = array(); //qua tam thoi lam tren map
        $this->EventList[EventType::TreasureIsland]['Treasure']     = array();  // qua nhan duoc tam thoi     
        $this->EventList[EventType::TreasureIsland]['JoinNum']      = 0 ;
        $this->EventList[EventType::TreasureIsland]['LastJoinTime'] = 0 ;
        
        $this->HideParam[EventType::TreasureIsland]['GiftOnMap']    = array();  // qua tren map
    }
    
   // khoi tao map moi 
    public function island_CreateMap()
    {
        // khoi tao trang thai map
        $map = array();
        $confMapStatus = Common::getConfig('Island_StateMap');
        $confMap = Common::getConfig('Island_Map');
        if(empty($confMapStatus)|| empty($confMap))
            return false ;
           
        $rand_arr = array() ;
        foreach($confMapStatus as $index => $arrState)
        {
            if(empty($arrState))    
                continue ;
            $rand_arr[$index] = $arrState['Rate'];
        }
        
        // tao map 
        $MapId = rand(1,count($confMap)) ;
        
        foreach($confMap[$MapId] as $H => $arr_h)
        {
            foreach($arr_h as $C => $value)
            {
                if($value <= 0)
                    continue ;
                $map[$H][$C] = Common::randomIndex($rand_arr);  
            }
        }
                                 
        // khoi tao qua tang map
        
        $gift_conf = Common::getConfig('Island_GiftMap');
        if(empty($gift_conf))
            return false ;
            
        $rand_Gift = array() ;
        foreach($gift_conf as $index1 => $arrGift)
        {
            if(empty($arrGift))    
                continue ;
            $rand_Gift[$index1] = $arrGift['Rate']*100;
        }
        
        $giftMap = array();
        
        if(empty($map))
            return false ;
        
        // thong tin chan 
        
        $MaxKey = Common::getParam('TreasureIsland','MaxKey');
        $MaxTreasure = Common::getParam('TreasureIsland','MaxTreasure');        
        $MaxCoconut = Common::getParam('TreasureIsland','MaxCoconut');
        $MaxRockRain = Common::getParam('TreasureIsland','MaxRockRain');
        $MaxLucky = Common::getParam('TreasureIsland','MaxLucky');
        
        $TreasureNum    = 0;
        $CollectNum     = 0;
        $CoconutNum     = 0;
        $RockRainNum     = 0;
        $MaxLuckyNum     = 0;
        
        //-----------    
        foreach($map as $h2 => $arr)
            foreach($arr as $c2 => $value)
            {
                if($value > 0)
                {
                    do
                    {
                        $flag = 0;
                        $giftMap[$h2][$c2] = Common::randomIndex($rand_Gift);   
                        
                        if($giftMap[$h2][$c2] == 6)
                        {
                            $TreasureNum++;
                            $flag = 1;
                        }                    
                        else if(in_array($giftMap[$h2][$c2],array(1),true))
                        {
                            $CollectNum++;
                            $flag = 2;
                        }
                        else if(in_array($giftMap[$h2][$c2],array(4),true)) // dua roi dau
                        {
                            $CoconutNum++;
                            $flag = 3;
                        }
                        else if(in_array($giftMap[$h2][$c2],array(3),true)) // mua da
                        {
                            $RockRainNum++;
                            $flag = 4;
                        }
                        else if(in_array($giftMap[$h2][$c2],array(2),true)) // thien than 
                        {
                            $MaxLuckyNum++;
                            $flag = 5;
                        }
                            
                    }
                    while( $flag == 1 && $TreasureNum > $MaxTreasure
                    || $flag == 2 && $CollectNum > $MaxKey
                    || $flag == 3 && $CoconutNum > $MaxCoconut
                    || $flag == 4 && $RockRainNum > $MaxRockRain
                    || $flag == 5 && $MaxLuckyNum > $MaxLucky);

                }
                else
                {
                    $giftMap[$h2][$c2] = 0 ;
                }
            }
        /*
        // tao diem thoat khoi map
        while(!empty($giftMap))
        {
            $h3 = rand(1,7);
            $c3 = rand(1,7);
            if($giftMap[$h3][$c3] <= 0)
                continue;
            $giftMap[$h3][$c3] = 12 ; // cua thoat khoi map
            break ;
        }
         */
        return array('Map'=>$map,'GiftMap'=>$giftMap,'MapId'=>$MapId);
    }
    
    // ham thuc hien viec tra ve qua an ui cho user
    public function island_GetRemainGift()
    {
        $remainGift = Common::getParam('TreasureIsland','RemainGift');
        if(empty($remainGift))
            return array();
        return array($remainGift);
    }
    
    // ham thuc hien viec tim ra ruong vang tren map
 public function island_FindTreasure()
    {
        $Position = array();
        $Cell = array() ;
              
        foreach($this->EventList[EventType::TreasureIsland]['Map'] as $H => $arrH)
        {
            foreach($arrH as $C => $Status)
            {
                if($Status <= 0) // da dao len roi or la mat nuoc
                    continue ;
                $value = $this->HideParam[EventType::TreasureIsland]['GiftOnMap'][$H][$C] ;
                if($value == 6 ) // ruong vang
                {
                    $Position['H'] = $H ;
                    $Position['C'] = $C ;
                    return $Position ;
                }
                $Cell[$H] = $C ;
            }  
        }
        
        if(empty($Position)&& !empty($Cell))
        {
            $k = 0;
            do
            {
                $k++;
                $cell_h = array_rand($Cell,1);

            }
            while($this->HideParam[EventType::TreasureIsland]['GiftOnMap'][$cell_h][$Cell[$cell_h]] == 12 && $k < 10);

            $Position['H'] = $cell_h ;
            $Position['C'] = $Cell[$cell_h] ;
            if($this->HideParam[EventType::TreasureIsland]['GiftOnMap'][$Position['H']][$Position['C']] != 12 )
                $this->HideParam[EventType::TreasureIsland]['GiftOnMap'][$Position['H']][$Position['C']] = 6;
            else
                return array();
        }
        
        return $Position ;
    }
    
    // ham thuc hien viec tao mua da tren map
    public function island_RockRain()
    {
        $Map = $this->EventList[EventType::TreasureIsland]['Map'] ;
        $Cell = array() ;
        foreach($this->EventList[EventType::TreasureIsland]['Map'] as $H => $arrH)
        {
            foreach($arrH as $C => $Status)
            {
                if($Status <= LandState::WATER || $Status > LandState::LAND )// da dao or mat nuoc or co do vat roi ...
                    continue ;
                $Cell[$H] = $C ;
            }  
        }
        
        if(!empty($Cell))
        {
            $RockNum = rand(5,10);
            for($i =1 ;$i <= $RockNum;$i++)
            {
                if(empty($Cell))
                    break ;
                $cell_h = array_rand($Cell,1);
                $Position['H'] = $cell_h ;
                $Position['C'] = $Cell[$cell_h] ;
                $this->EventList[EventType::TreasureIsland]['Map'][$Position['H']][$Position['C']] = LandState::SMALL_ROCK ;
                unset($Cell[$cell_h]);
            } 
        }
    }
    
    // dua roi vao dau => mat mot vai mon qua
    public function island_Coconut()
    {
        
        $MaxLostNum = Common::getParam('TreasureIsland','MaxLostNum');
        $LostNum = rand(1,intval($MaxLostNum));

        $maxGift = count($this->EventList[EventType::TreasureIsland]['Treasure']);
        if($maxGift <= 0 )
            return array();
        $arr_key = array_keys($this->EventList[EventType::TreasureIsland]['Treasure']);
        $arr_key1 = array_flip($arr_key);
        
        $LostNum = $LostNum > $maxGift ? $maxGift:$LostNum ;
        
        $DelGiftList = array();
        
        for($i = 1; $i <= $LostNum;$i++)
        {
            $keydel = array_rand($arr_key1,1);
            $DelGiftList[$keydel] = $this->EventList[EventType::TreasureIsland]['Treasure'][$keydel];
            
            unset($this->EventList[EventType::TreasureIsland]['Treasure'][$keydel]);
            unset($arr_key1[$keydel]);
        }
        
        return $DelGiftList ;
        
    }
    
    public function island_getGift($conf)
    {
        $arr = array();
        if(empty($conf))
            return $arr ;
        $oUser = User::getById(Controller::$uId);
        foreach($conf as $id => $gift)
        {
                if(empty($gift)) continue ;
                
                $rand = mt_rand(1,100);
                if(!empty($gift['Rate']) && $rand > $gift['Rate'])
                    continue ;
                
                if(SoldierEquipment::checkExist($gift['ItemType']))
                {   
                    for($i = 1 ; $i <=$gift['Num']; $i++)
                    {
                          $AutoId = $oUser->getAutoId();
                          $Equip = Common::randomEquipment($AutoId,$gift['Rank'],$gift['Color'],SourceEquipment::EVENT,$gift['ItemType']);
                          $arr['SpecialGift'][$AutoId]= $Equip ;
                    }                    
                }
                else if($gift['ItemType'] == Type::AllChest )
                {
                    $Element = (empty($gift['Element'])) ? rand(1,5) : $gift['Element'];
                    $Source = (empty($gift['Source'])) ? SourceEquipment::EVENT : $gift['Source'];
                    
                    for($j = 1 ; $j <=$gift['Num']; $j++)
                    {
                        $AutoId = $oUser->getAutoId();
                        
                        $EquipSet = Common::getConfig('ChestGift', $gift['ItemType'], $gift['Rank']);
                        $EquipSet = $EquipSet[$gift['Color']];
                        $EquipBasic = $EquipSet[array_rand($EquipSet)];
                        $Equip = Common::randomEquipment($AutoId, $gift['Rank'],$gift['Color'], $Source, $EquipBasic['ItemType'], intval($gift['Enchant']), intval($Element),0);
                        
                        $arr['SpecialGift'][$AutoId]= $Equip ;
                    }
                }
                else if($gift['ItemType'] == Type::Island_Item && $gift['ItemId'] == 20  )
                {
                    // bo collection
                    /*for($i = 1 ; $i <= $gift['Num'];$i++)
                    {
                        $gift_1['ItemType'] = Type::Island_Item ;
                        $gift_1['ItemId']   = rand(5,9);
                        $gift_1['Num']      = 1 ;
                        
                        $arr['NormalGift'][]= $gift_1;
                    } */
                        
                }
                else
                {
                    if(is_array($gift['Num']))
                    {
                        $index = array_rand($gift['Num'],1);
                        $gift['Num'] = $gift['Num'][$index] ;
                    }
                    unset($gift['Rate']) ;
                    if($gift['Num'] > 0 )
                        $arr['NormalGift'][]= $gift;
                }
        }
        $oUser->save();
        return $arr ;        
    }
    // roi qua tu cac hanh dong chien dau
    public function island_getGiftInEvent($key1,$key2,$key3 = 1,$key4 = 1)
    {
        $arr_gift = array();
        if(!self::checkEventCondition(EventType::TreasureIsland))
        {
            return $arr_gift;
        }
        $conf = Common::getConfig('IsLand_ActionGift',$key1,$key2);
        $conf = $conf[$key3][$key4];
        $conf = $conf['gift'];        
        if(empty($conf))
            return $arr_gift;
             
        // rate one or more gift 
        foreach($conf as $id => $gift)
        {
           if(rand(1,100) > $gift['Rate'])  continue;
            
            $arr_gift[$id]['ItemType']    = $gift['ItemType'];
            $arr_gift[$id]['ItemId']      = $gift['ItemId'];
            $arr_gift[$id]['Num']         = round($gift['Num']);
            if(is_array($gift['Num']))
            {
                $idd = array_rand($gift['Num'],1);
                $arr_gift[$id]['Num'] = round($gift['Num'][$idd]) ;
            }
            // special for each event
            switch($gift['ItemType'])
            {
            }             
        }
                       
        return $arr_gift ;

    }
    
      
    // luu qua tam thoi vao trap
    public function island_saveGiftTempTrap($gift_arr)
    {
        if(empty($gift_arr))
            return array() ;
        $arr_gift = array();
        $giftList = $this->island_getGift($gift_arr);
        foreach($giftList as $type => $arr)
        {
            foreach($arr as $gift)
            {
                if(empty($gift)) continue ;
                $this->EventList[EventType::TreasureIsland]['Treasure'][]= $gift;
                $arr_gift[] = $gift ;
            }
        }       
        return $arr_gift ;
    }
    
    // luu qua vao kho 
    public function island_saveGiftIntoStore($gift_arr)
    {
        if(empty($gift_arr))
            return false ;
        $oStore = Store::getById(Controller::$uId);
        $oUser = User::getById(Controller::$uId);
        
        foreach($gift_arr as $Id => $arr)
        {
            if(is_object($arr)&& SoldierEquipment::checkExist($arr->Type))
            {
                $oStore->addEquipment($arr->Type, $arr->Id,$arr);
            }
            else
            {
                if(empty($arr))
                    continue ;
                $oUser->saveBonus(array($arr));
            }    
        }    
        $oUser->save();
        $oStore->save();
           
        return true ;
    }
    
        
    // ham thuc hien viec tao ra mot map moi trong event hoa mua thu
    public function a_creatNewMap($isFirstLogin)
    {
        if($isFirstLogin)
        {
            $this->EventList[EventType::PearFlower]['MapOver']= array() ;     
            $arrMap = array() ;
        }
        else
        {
           $arrMap = array(1=>1,2=>2,3=>3,4=>4,5=>5,6=>6,7=>7,8=>8,9=>9,10=>10); 
           foreach($this->EventList[EventType::PearFlower]['MapOver'] as $Id)
           {
                unset($arrMap[$Id]);
           }
        }

        if(empty($arrMap))
        {
           $mapId = rand(1,10); 
        }
        else
        {
            $mapId = array_rand($arrMap,1);
        }
        $NewMap = new PearFlower($mapId);
        $this->EventList[EventType::PearFlower]['Object']            = $NewMap ;
        $this->EventList[EventType::PearFlower]['MapOver'][$mapId]   = $mapId ;
        $this->EventList[EventType::PearFlower]['CreateMapTime']     = $_SERVER['REQUEST_TIME'] ;
    }
    
    // xoa map sau khi thoat khoi map
    public function a_cleanMap()
    {
        $this->EventList[EventType::PearFlower]['Object'] = array();
    }

     public function a_randomGift()
     {
        $conf_per = Common::getConfig('General','PearFlowerInfo','rateRandomArrow');
        $ArrowId = Common::randomIndex($conf_per);
        $oUser = User::getById(Controller::$uId);
        if($ArrowId < 1)
            return array() ;
        $gift = array();
        $gift[0][Type::ItemType] = Type::Arrow;
        $gift[0][Type::ItemId] = $ArrowId;
        $gift[0][Type::Num] = 1;

        $oUser->saveBonus($gift);
        return $gift[0];
    }
    
    
    public function a_getGiftOfAutoPlay($Type)
    {
        $conf = Common::getConfig('AutoMap',$Type);
        $arr = array();
        if(empty($conf))
            return $arr ;
        foreach($conf as $giftId => $gift)
        {
            if(empty($gift)) continue ;
            $rand = mt_rand(1,100);
            if($rand > $gift['Rate'])
                continue ;
            $arr_gift[] = $gift ;
        }
        // them kinh nghiem
        $successExp  = Common::getParam('SuccessRewards');
        $ExpGift = array('ItemType'=>'Exp','Num'=>$successExp);
        
        $arr_gift[]  =  $ExpGift ;
        
        $result = Common::addsaveGiftConfig($arr_gift,rand(1,5),SourceEquipment::EVENT);     
        
         $arr['SpecialGift'] = $result['Equipment'] ; 
         $arr['NormalGift']  = $result['Normal'] ;
        return $arr ;        
    }
    
    public function hal_init()
    {
        $data = array();
        $data['LastDatePlay'] = '';
        $data['RemainPlayCount'] = EventHal2012::MAX_UNLOCKMAP;
        $data['InMap'] = false;
        $data['UnlockMap'] = true;
        $data['BeginWait'] = 0; 
        $data['HalScene'] = new HalScene();
        $data['KiddingSTime'] = 0;
        $data['FindKey'] = 0;         
        $data['HadKey'] = false;
        $data['GhostRequire'] = 0;
        $this->EventList[EventType::Halloween] = $data;
    }
    
    public function hal_updateFirstDate()
    {                                    
        $data =  $this->EventList[EventType::Halloween];
        $currd = date('Ymd', $_SERVER['REQUEST_TIME']);
        if($data['LastDatePlay'] == $currd)
            return false;
        
        $data['LastDatePlay'] = $currd;
        $data['RemainPlayCount'] = EventHal2012::MAX_UNLOCKMAP;     // first map 
        $this->EventList[EventType::Halloween] = $data;
        
        return array('unlock' => $data['UnlockMap']);
    }
    
    public function hal_createNewMap($map, $baby_pos)
    {
        $data =  $this->EventList[EventType::Halloween];
        if(($data['RemainPlayCount'] -1 ) < 0)
            return false; 
        $data['RemainPlayCount']--;      
        $data['InMap'] = true;
        $data['UnlockMap'] = false;
        $data['BeginWait'] = 0;
        $data['KiddingSTime'] = 0;                
        $data['FindKey'] = 0;         
        $data['HadKey'] = false;
        $data['GhostRequire'] = 0; 
        $oHalScene = $data['HalScene'];
        $oHalScene->resetScene($_SERVER['REQUEST_TIME'], $map, $baby_pos); 
               
        $this->EventList[EventType::Halloween] = $data;               
        return true; 
    }
    
    public function hal_checkIsCreateNewMap()
    {
        $data =  $this->EventList[EventType::Halloween];
        if($data['InMap'])
            return false;
        if(!$data['InMap'] && ($data['BeginWait'] != 0) && (($_SERVER['REQUEST_TIME'] - $data['BeginWait']) < EventHal2012::COOLDOWN_MAP))
            return false;
        return true;
    }
    
    public function hal_checkAvailableStep($X, $Y)
    {
        $data =  $this->EventList[EventType::Halloween];
        $oHalScene = $data['HalScene'] ;
        
        if(!$data['InMap'] || $data['UnlockMap'] || ($data['KiddingSTime'] > 0))
            return false;
        $Step = $oHalScene->Map[$X][$Y];
        if(!isset($Step))
            return false;
        if($Step[0] == EventHal2012::UNLOCK_STATE)
            return false;
            
        $StepL = $oHalScene->Map[$X-1][$Y];
        $StepR = $oHalScene->Map[$X+1][$Y];
        $StepT = $oHalScene->Map[$X][$Y-1];
        $StepB = $oHalScene->Map[$X][$Y+1];
        
        if((isset($StepL) && ($StepL[0] == EventHal2012::UNLOCK_STATE)) || (isset($StepR) && ($StepR[0] == EventHal2012::UNLOCK_STATE)) || (isset($StepT) && ($StepT[0] == EventHal2012::UNLOCK_STATE)) || (isset($StepB) && ($StepB[0] == EventHal2012::UNLOCK_STATE)))
            return $Step;
        return false;
    }
    
    public function hal_changeStateItem($X, $Y)
    {
        $data =  $this->EventList[EventType::Halloween];
        $oHalScene = $data['HalScene'] ;
        
        $state = $oHalScene->changeStateItem($X, $Y);
        if(!$state)
            return false;        
        $this->EventList[EventType::Halloween] = $data;
        
        return $state;
    }
    
    public function hal_setKidding($time, $ghostRequire = 0)
    {
        $data =  $this->EventList[EventType::Halloween];        
        $data['KiddingSTime'] = $time;
        $data['GhostRequire'] = $ghostRequire ;
        $this->EventList[EventType::Halloween] = $data; 
        
        return  $data['KiddingSTime'];
    }
    
    public function hal_haveKey()
    {
       $data =  $this->EventList[EventType::Halloween];
       if($data['HadKey'])
            return false;
       $data['FindKey'] ++;            
       if($data['FindKey'] == EventHal2012::NUM_ORD_GIFT)
       {
          $data['HadKey'] = true;          
          $this->EventList[EventType::Halloween] = $data;  
          return true;
       }else
       {
           $lucky = rand(1,10);
           if($lucky == 1)
                $data['HadKey'] = true;
           $this->EventList[EventType::Halloween] = $data;
           return ($lucky == 1);
       }            
    }
    
    public function hal_addRegard($gift)
    {
        $data = $this->EventList[EventType::Halloween];
        $oHalScene = $data['HalScene'] ;
        $oHalScene->addRegard($gift);
        $this->EventList[EventType::Halloween] = $data;
        
        return;
    }
    
    public function hal_unlockMap($getGift = true)
    {
        $data =  $this->EventList[EventType::Halloween];

        $data['UnlockMap'] = true;
        $data['InMap'] = false;
        $data['BeginWait'] = $_SERVER['REQUEST_TIME'];
        
        $oHalScene = $data['HalScene'] ;
        $oHalScene->Map = array();
        $regard = array();
        if($getGift)             
            $regard = $oHalScene->Reward;
        $oHalScene->Reward = array();
        $oHalScene->UnlockedMap = true;
        
        $this->EventList[EventType::Halloween] = $data;
        
        return $regard;
    }
    
    public function hal_kidded()
    {
        $data =  $this->EventList[EventType::Halloween];
        $oHalScene = $data['HalScene'] ;  
        $freezeArray = $this->hal_kidFreeze($oHalScene->Map);
        $data['KiddingSTime'] = 0;
        $this->EventList[EventType::Halloween] = $data;
        
        return $freezeArray;
    }
    
    private function hal_kidFreeze(&$map)
    {
        $numFreeze = rand(5, EventHal2012::MAX_FREEZE);
        $count = 0;
        $freezeArray = array();
        $avaFreeze = array();
        for($i = 0; $i < 10; $i ++)
        {
            for($j = 0; $j < 10; $j ++)
            {                
                if(($map[$i][$j][0] == 1) && ($map[$i][$j][1] != 31) && ($map[$i][$j][1] != 15) && ($map[$i][$j][1] != 16))
                {                    
                    $avaFreeze[] = array($i, $j);        
                }
            }   
        }
        
        $realfreeze = (count($avaFreeze) > $numFreeze) ? $numFreeze : count($avaFreeze);
        $keyava = array_rand($avaFreeze, $realfreeze);
        foreach($keyava as $id)
        {
            $point = $avaFreeze[$id];
            $map[$point[0]][$point[1]][0] = 2;
            $freezeArray[] = $point;
        }        
        
        return $freezeArray;
    }
    
    
}
?>
