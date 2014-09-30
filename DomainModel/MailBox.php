<?php
  /**
  * @author : ToanTN
  * @version : 0.1
  * 
  */

  Class MailBox extends Model
  {
     public $list = array();
     
     public function __construct($uId)
     {
         parent :: __construct($uId) ;  
     }
     
     public function add($fromId,$content)
     {
       $this->list[] = new Mail($fromId,$content);
       if(count($this->list) > 50 )
        array_shift($this->list);  
     }
     
     public function del($mailId)
     {
      if(isset($this->list[$mailId]))
       unset($this->list[$mailId]);   
     }
     
     public function read($mailId)
     {
       if(is_object($this->list[$mailId])) $this->list[$mailId]->read(); 
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
         $obj = DataProvider::get($uId,'MailBox');
         if(!is_object($obj))
            return new MailBox($uId);
         return $obj ; 
     }
     
  }
