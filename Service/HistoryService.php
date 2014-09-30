<?php
/**
 * @author ToanTN
 * @version 1.0
 * @created 12-1-2011
 * @Description : thuc hien viec xu ly phan gui nhan Message va Gift
 */

class HistoryService 
{
    public function log($param)
    {
        $FriendId       =$param['FriendId'];
        $Time           =$param['Time'];
        $DataLog        =$param['Log'];

        // kiem tra du lieu dau vao
        if(empty($FriendId)|| !is_array($DataLog))
        {
            return array('Error' => Error :: PARAM) ;
        }

        $oUser = User::getById(Controller::$uId);
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

       // kiem tra xem nguoi nhan co phai la ban khong
       if(!$oUser->isFriend(intval($FriendId)))
       {
               return array('Error' => Error :: NOT_FRIEND) ;
       }

       $oDiary = Diary::getById($FriendId);
       $oDiary->add(Controller::$uId,$DataLog,$Time);     
       $oDiary->save();

       return array('Error' => Error :: SUCCESS) ;

    }
   public function getAll($param)
   {
      $UserId  = $param['UserId'];

      $oUser = User::getById(Controller::$uId);
      if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

     $oDiary = Diary::getById(Controller::$uId);
     $arr['AllHistory'] = $oDiary->list ;
     $arr['Error'] = Error :: SUCCESS ;
     
     return $arr ;
   }
   public function del()
   {

    $oUser = User::getById(Controller::$uId);
    if (!is_object($oUser))
    {
        return array('Error' => Error :: NO_REGIS) ;
    }

    $oDiary = new Diary(Controller::$uId);
    $oDiary->save();
    return array('Error' => Error :: SUCCESS) ;
   }
}
?>