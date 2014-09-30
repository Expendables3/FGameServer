<?php
  /**
  * @author : ToanTN
  * @version : 0.1
  * 
  */

  Class UserProfile extends Model
  {
      public $NewMail = false ;
      public $NewGift = false ;
      public $NewDailyQuest = false;
      public $ActionInfo = array();
      public $FeedInfo = array();   
      public $BetaLevel = 1 ;
      private $NumOnline = 0 ;
      
      public $MaxFishLevelUnlock = 1 ;
      public $SlotUnlock = 4;

      public function __construct($uId)
      {      
        $this->ActionInfo['Receivers'] =  array() ;
        $this->ActionInfo['Senders'] =  array() ;
        $this->ActionInfo['UnlockFishList'] =  array() ;
        $this->ActionInfo['DayGift'] = array();
        parent :: __construct($uId) ;                
      }
      
      public function updateBeta($Level = 1)
      {
        $this->BetaLevel = $Level ;        
        $slotUn = array(1=>4,15=>7,25=>8);
        $this->SlotUnlock = $slotUn[$Level];  
      }

      public function update()
      {
        $this->ActionInfo['Receivers'] =  array() ;
        $this->ActionInfo['Senders'] =  array() ;
        $this->ActionInfo['DayGift'] = array();
        $this->randGiftDay() ;
      }
      
      // ham thuc hien viec random qua tang hang ngay cho user
      
    private function randGiftDay()
    {
       $conf_gift = & Common::getConfig('DayGift');

       foreach ($conf_gift as $key => $Value)
       {
         $listRate[$key] = intval($Value['Rate']);
       }
       for($i = 0;$i < $this->NumOnline ; $i++)
         {
             if($i == 3) break ;
             $this->ActionInfo['DayGift'][$i] = Common::randomIndex($listRate);
         } 
    }
    
    /*
        ham thuc hien viec cong qua tang vao cho User
    */
    public function saveGiftDay()
    {
      $conf_gift = & Common::getConfig('DayGift');
      foreach($this->ActionInfo['DayGift'] as $id)
      {
          $Gifts[] = $conf_gift[$id] ;
      }
      
      $oUser = User::getById($this->uId);
      $oUser->saveBonus($Gifts);
      $this->ActionInfo['DayGift'] = array();       
    } 
      
    public function addSenders($id)
      {
         $this->ActionInfo['Senders'][$id] = true ;  
      }
    
    public function updateDayOnline($online = false)
      {
        if($online)  $this->NumOnline ++ ;
        else $this->NumOnline = 1; 
      }
      
    public function checkGiftList($ReceiveList = array())
      {
        foreach($ReceiveList as $id)
         if(isset($this->ActionInfo['Receivers'][$id]))
           return false ;
        $total = count($this->ActionInfo['Receivers'])  + count($ReceiveList);
        if($total > Common::getParam(PARAM::NumFriendGift))
            return false ;
        return true ;  
      }
      
    public function saveReceiverGift($ReceiveList)
      { 
        foreach($ReceiveList as $id)
            $this->ActionInfo['Receivers'][$id] = true ;
      }
      
    public function updateBirthFish($FishTypeId)
    {
       $this->ActionInfo['UnlockFishList'][$FishTypeId] = UnlockType::Mix ;
    }

    public function updateMaxFishUnlock($Level){
    	$conf_param = Common::getParam(); 
    	if ($Level> $conf_param[PARAM::MaxFishLevel])
    		$Level = $conf_param[PARAM::MaxFishLevel];
    	if ($Level > $this->MaxFishLevelUnlock){
    		$this->MaxFishLevelUnlock = $Level;
    	}
    }
    
    public function updateFishUnlock($FishTypeId)
    {
      $this->ActionInfo['UnlockFishList'][$FishTypeId] = UnlockType::ZMoney ; 
    }
    
    
    
    
    public function checkUnlockFish($id)
    {
        if(!isset($this->ActionInfo['UnlockFishList'][$id]))
            return false ;
        return $this->ActionInfo['UnlockFishList'][$id];
    }
    
    public static function getById($uId)
    {
        return DataProvider :: get($uId,__CLASS__) ;
    }


    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }
      
  }
  