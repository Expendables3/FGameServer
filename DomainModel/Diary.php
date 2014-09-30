<?php
 /**
  * @author : ToanTN
  * @version : 0.1
  * 
  */
  
class Diary extends Model 
{
   public $list = array();
  
   public function __construct($uId)
     {
         parent :: __construct($uId) ;  
     }
     
     public function add($friendId,$content = array(),$time = null,$self = false)
     {

            $this->list[] = new History($friendId,$content,$time,$self);
            if(count($this->list) > 50 )
                array_shift($this->list);              
     }
     
     public function clear()
     {
        unset($this->list);
     }
          

     public static function getById($uId)
     {
         $obj = DataProvider::get($uId,'Diary');
         if(!is_object($obj))
            return new Diary($uId);
        return $obj ;
     }
}

?>
