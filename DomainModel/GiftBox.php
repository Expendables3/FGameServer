<?php
  /**
  * @author : ToanTN
  * @version : 0.1
  * 
  */

  Class GiftBox extends Model
  {
     public $list = array();
     
     public function __construct($uId)
     {
         parent :: __construct($uId) ;  
     }
     
     public function add($fromId,$giftId)
     {
       $this->list[] = new Gift($fromId,$giftId);
       if(count($this->list) > 50 )
        array_shift($this->list);  
     }
     
     public function del($mailId)
     {
      if(isset($this->list[$mailId]))
       unset($this->list[$mailId]);   
     }
     
     private function checkExpire()
     {
       $expireTime = Common::getParam(PARAM::ExpireMail); 
       $expire = false ;
       foreach($this->list as $mailId => $mail)
        {
            if($mail->isExpire($expireTime))
            {
                $this->del($mailId);
                $expire = true ;
            }      
        }
       return $expire ;  
     }
     
     public function getList()
     {
       if($this->checkExpire())
        $this->save();
       return $this->list ;  
     }
     
     /**
     * Data Mapper : ToanTN
     */
     
     public static function getById($uId)
     {
         $obj = DataProvider::get($uId,'GiftBox');
         if(!is_object($obj))
            $obj = new GiftBox($uId);
         return $obj ;
     }
     
  }