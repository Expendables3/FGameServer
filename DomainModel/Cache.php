<?php

  Class Cache extends Model
  {
     public $conditionEvent = false;
     public $friendIdInvite = 0;
     
     public function __construct($uId)
     {
         parent :: __construct($uId) ;  
     }

     public function del($key)
     { 
       unset($this->$key);   
     }

     
     public static function getById($uId)
     {
         $obj = DataProvider::get($uId,'Cache');
         if(!is_object($obj))
            return new Cache($uId);
         return $obj ; 
     }
     
  }
