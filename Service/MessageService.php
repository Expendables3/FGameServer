<?php
/**
 * @author AnhBV
 * @version 1.0
 * @created 3-9-2010
 * @Description : thuc hien viec xu ly phan gui nhan Message va Gift
 */

class MessageService extends Controller
{

	/**
	 * @author AnhBV  , ToanTN edited
	 * @version 1.0
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan gui qua
	 * @Param :
	 * 		$GiftId Id cua qua tang trong file config
	 * 		$ReceiveList  dang dach cac nguoi nhan qua
	 */
	public function sendGift($param)
	{
		$GiftId         =$param['GiftId'];
		$ReceiveList    =$param['ReceiveList'];
        
		// kiem tra du lieu dau vao
		if(empty($GiftId)|| !is_array($ReceiveList))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User::getById(Controller::$uId);
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}


       // kiem tra xem Gift co ton tai hay khong
        $GiftConfig = Common::getConfig('Gift');

        if(!is_array($GiftConfig[$GiftId]))
        {
            return array('error' => Error :: NOT_GIFT) ;
        }

		// kiem tra nguoi gui co du level so voi mon qua nay khong
		if($oUser->getLevel() < $GiftConfig[$GiftId]['LevelRequire'] )
		{
			return array('Error' => Error :: NOT_ENOUGH_LEVEL) ;
		}

        $oUserProfile = UserProfile::getById(Controller::$uId); 
        if(!$oUserProfile->checkGiftList($ReceiveList))
        {
            return array('Error' => Error :: GOT_GIFT) ;
        }
        
        $oUserProfile->saveReceiverGift($ReceiveList);

		 // luu thong tin qua tang vao messagebox cua nguoi nhan

        $arr_sender = array();
        $success = 0;
        foreach($ReceiveList as $Key => $ReceiveId)
        {
            // kiem tra xem nguoi nhan co phai la ban khong
            if(!$oUser->isFriend($ReceiveId))
            {
               return array('Error' => Error :: NOT_FRIEND) ;
            }
            // tao 1 mon qua trong messagebox cua nguoi nhan
            
            $oGiftBox = GiftBox::getById($ReceiveId);
            $oGiftBox->add(Controller::$uId,$GiftId);
           
            $oGiftBox->save();

            // thong bao qua moi
            $oRecei= UserProfile::getById($ReceiveId);
            if(!is_object($oRecei))
            {
                continue ;
            }
            $oRecei->addSenders(Controller::$uId);
            $oRecei->NewGift = TRUE ;
            $oRecei->save();
            $success++;
        }
        $oUserProfile->save();

		Zf_log::write_act_log(Controller::$uId, 0, 20, 'sendGift', 0, 0, $GiftId);

		return array('Error' => Error :: SUCCESS, 'NumSuccess' => $success);
	}

	/**
	 * @author AnhBV
	 * @version 1.0
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan gui thu
	 * @Param :
	 * 		$Content noi dung cua thu
	 * 		$ReceiverId ID nguoi nhan qua
	 */
	Public function sendMessage($param)
	{
		$ReceiverId    = $param['ReceiverId'];
		$Content       = $param['Content'];
		// nhan du lieu dau vao
		if(!Controller::$uId)
		{
			return  array('Error' => Error ::LOGIN) ;
		}
		// kiem tra du lieu dau vao
		if(empty($ReceiverId)|| empty($Content))
		{
			return array('Error' => Error :: PARAM) ;
		}

		$oUser = User::getById(Controller::$uId);
		if (!is_object($oUser))
		{
			return array('Error' => Error :: NO_REGIS) ;
		}

		// kiem tra xem nguoi nhan co ton tai hay khong
		if(!$oUser->isFriend($ReceiverId))
		{
			return array('Error' => Error :: NOT_FRIEND) ;
		}
		// tao 1 tin nhan trong messagebox cua nguoi nhan
		$oMailBox = MailBox::getById($ReceiverId);
		$oMailBox->add(Controller::$uId, $Content);
		$oMailBox->save();
         
		// thong bao thu moi

		$oRecei= UserProfile::getById($ReceiverId);
		$oRecei->NewMail = TRUE ;
		$oRecei->save();

		//Zf_log::write_act_log(Controller::$uId, 0, 20, 'sendMessage');
	
		return array('Error' => Error :: SUCCESS) ;


	}

	/**
	 * @author ToanTN edited
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan load email cua user
	 *
	 */
	Public function loadMailBox()
	{

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }
     
        $oMailBox = MailBox::getById(Controller::$uId);

        $oUserProfile = UserProfile::getById(Controller::$uId);
        $oUserProfile->NewMail = false ;
        $oUserProfile->save();

		$arr_result['ListMail']    = $oMailBox->getList();
		$arr_result['Error']     = Error ::SUCCESS;
		return $arr_result;

	}
	
   /**
	 * @author AnhBV  - ToanTN edited
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan load hom qua 
	 *
	 */
     
	Public function loadGiftBox()
	{
        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }
      
        $oGiftBox = GiftBox::getById(Controller::$uId);

        $oUserProfile = UserProfile::getById(Controller::$uId);
        
        $oUserProfile->NewGift = false ;
        $oUserProfile->save();

		$arr_result['ListGift']    = $oGiftBox->getList(); 
		$arr_result['Error']     = Error ::SUCCESS;
		return $arr_result;

	}



	/**
	 * @author AnhBV  ToanTN edited
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan chap nhan qua tang
	 * @Param :
	 * 		$MessageId Id cua thu
	 */
	public function acceptGift($param)
	{
		$MessageId = $param['MessageId'];
        $cancel = $param['Cancel']; 

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
      
        $oGiftBox = GiftBox::getById(Controller::$uId);
        if(!isset($oGiftBox->list[$MessageId]))
        {
          return array('Error' => Error :: PARAM) ;   
        }
        
        $GiftConfig = Common::getConfig('Gift');
        $GiftConfig = $GiftConfig[$oGiftBox->list[$MessageId]->GiftId];

        if ($GiftConfig['ItemType']==Type::ItemCollection && $GiftConfig['ItemId']==16)
        {
            $rand = rand(1,100);
            if ($rand <=90)
                $GiftConfig['ItemId'] = ItemCollection::TOM_THAN;
            else $GiftConfig['ItemId'] = ItemCollection::CUA_THAN;
        }
        
        // Event Euro
        switch($GiftConfig['ItemType'])
        {
            case EventEuro::EURO_BALL:
                $GiftConfig['ItemId'] = EventEuro::EURO_BETTYPE_VIP;
                Zf_log::write_act_log(Controller::$uId, 0, 20, 'bonusActionEuroBall', 0, 0, EventEuro::EURO_BALL_TYPE, $GiftConfig['ItemId'], $GiftConfig['Num'], 'FriendGift');    
                break;          
        }
       
		if(!is_array($GiftConfig))
		{
			return array('Error' => Error :: NOT_LOAD_CONFIG) ;
		}
        
        if(!$cancel)
        {             
            $oUser->saveBonus(array($GiftConfig));
            $oUser->save(); 
        }
         
        
        $oGiftBox->del($MessageId);
		$oGiftBox->save();
		
		//log
		//Zf_log::write_act_log(Controller::$uId, 0, 20, 'acceptGift');

		return array('Error' => Error :: SUCCESS, 'ItemType' => $GiftConfig['ItemType'], 'ItemId' => $GiftConfig['ItemId']) ;
	}

    /**
	 * @author AnhBV     ToanTN eidted
	 * @created 14-9-2010
	 * @Description : thuc hien viec xu ly phan doc thu 
	 * @Param :
	 * 		$MessageId Id cua thu
	 */
	public function readMail($param)
    {
        $MessageId = $param['MessageId'];

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }

        $oMailBox = MailBox::getById(Controller::$uId);

        if(!isset($oMailBox->list[$MessageId]))
        {
          return array('Error' => Error :: PARAM) ;   
        }
        
        
        $oMailBox->read($MessageId);
        $oMailBox->save();;
        
        return array('Error' => Error :: SUCCESS) ;
    }
    
    public function readSystemMail($param)
	{
		$MessageId = $param['MessageId'];

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }

        $oMailBox = SystemMail::getById(Controller::$uId);

		if(!isset($oMailBox->ListMailOwner[$MessageId]))
        {
          return array('Error' => Error :: PARAM) ;   
        }
        
        $oMailBox->read($MessageId);
        $oMailBox->save();;
		
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'readSystemMail',0,0,$MessageId);
		return array('Error' => Error :: SUCCESS) ;
	}
	
	
	/*
	 *
	 * @author AnhBV     - ToanTN edited
	 * @created 15-9-2010
	 * @Description : thuc hien viec xu ly phan xoa thu va qua tang
	 * @Param :
	 * 		$MessageId Id cua thu

	 */
	public function removeMessage($param)
	{
		if($param['MessageType'] == 1) // tu choi qua tang
          {
              $param['Cancel'] = true ;
              return $this->acceptGift($param);
          }

        $MessageId = $param['MessageId'];

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }

        $oMailBox = MailBox::getById(Controller::$uId);
        
		if(!isset($oMailBox->list[$MessageId]))
        {
          return array('Error' => Error :: PARAM) ;   
        }
        
        $oMailBox->del($MessageId);
        $oMailBox->save();
		return array('Error' => Error :: SUCCESS) ;

	}
	
    // xoa mail he thong 
    public function removeSystemMessage($param)
    {       

        $MessageId = $param['MessageId'];

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('error' => Error :: NO_REGIS) ;
        }

        $oMailBox = SystemMail::getById(Controller::$uId);
        
        if(!isset($oMailBox->ListMailOwner[$MessageId]))
        {
          return array('Error' => Error :: PARAM) ;   
        }
        
        $oMailBox->del($MessageId);
        $oMailBox->save();
        
        // log
        Zf_log::write_act_log(Controller::$uId,0,20,'removeSystemMessage',0,0,$MessageId);
        return array('Error' => Error :: SUCCESS) ;

    }
}
?>
