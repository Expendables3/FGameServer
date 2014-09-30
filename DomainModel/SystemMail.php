<?php
class SystemMail extends Model
{
    public $ListMailOwner = array() ;
    public $ListMailSystem = array();
    
    public function __construct($uId)
    {
        parent :: __construct($uId) ;  
    }
    public function getListMail()
    {
        return $this->ListMailOwner;
    }
    
     public function add($fromId,$content)
     {
       $this->ListMailOwner[] = new Mail($fromId,$content);
       if(count($this->ListMailOwner) > 50 )
        array_shift($this->ListMailOwner);  
     }
     
     public function del($mailId)
     {
      if(isset($this->ListMailOwner[$mailId]))
       unset($this->ListMailOwner[$mailId]);   
     }
     
     public function read($mailId)
     {
       if(is_object($this->ListMailOwner[$mailId])) 
            $this->ListMailOwner[$mailId]->read(); 
     }
     
     private function checkExpire()
     {
       $expireTime = Common::getParam('ExpireSystemMail'); 
       $expire = false ;
       foreach($this->ListMailOwner as $mailId => $mail)
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
       return $this->ListMailOwner ;  
     }
     
          
     public static function getById($uId)
     {
         $obj = DataProvider::get($uId,'SystemMail');
         if(!is_object($obj))
            return new SystemMail($uId);
         return $obj ; 
     }
     
     public function updateMailFormSystem()
     {
         $SystemUser = -111 ;
         $SystemNotify = DataProvider::get($SystemUser,'SystemNotify');
         if(empty($SystemNotify))
            return false ;
         foreach($SystemNotify as $Id => $arr_Mail)
         {
             if(empty($arr_Mail)) 
                continue ;
             if(is_string($Id))
                continue ;
             
             if(isset($this->ListMailSystem[$Id]))
                continue ;
                
             $this->add($SystemUser,$arr_Mail['Content']);
             $this->ListMailSystem[$Id] = $Id ;
             //log
             Zf_log::write_act_log(Controller::$uId,0,20,'ItemCodeMessage',0,0,$Id);
         }
     }
}

