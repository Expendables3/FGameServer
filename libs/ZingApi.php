<?php

    require_once "zingme-sdk/BaseZingMe.php";
    require_once "zingme-sdk/ZME_Me.php";
    require_once "zingme-sdk/ZME_User.php";
    require_once "zingme-sdk/ZME_UserLevel.php";
    require_once "zingme-sdk/ZME_FeedDialog.php";
    require_once "zingme-sdk/ZME_Photo.php";

    $zm_config = array(
            'appname' => 'fish',
            'apikey' => 'b610ef9d2c67bd7f9c3d2b447372f913',
            'secretkey' => '6b5fa8df3e3894ff776a7ec6604ac595',
            'env' => 'production'
        );

class ZingApi
{
    /**
     * require api
     * @param unknown_type $file
     */
    static function require_api($file)
    {
        require_once "zingme-sdk/".$file.".php";
    }
    
    static function getAccessTokenFromSignedRequest($SignedRequest)
    {
        if(empty($SignedRequest))
        {
            return false ;
        }
        $AccessK =  ZME_Me::getAccessTokenFromSignedRequest($SignedRequest) ;  
        if(!empty($AccessK)&&is_string($AccessK))
        {
            return $AccessK ; 
        }
        else if(is_object($AccessK))
        {
            $ErrCode =  $AccessK->getErrCode();
            
            if($ErrCode == -13)
            {
                 header('location: http://me.zing.vn/apps/ohfish');        
                 exit();    
            }
            else
            {
                 header('location: http://me.zing.vn');        
                 exit();    
            }
            
        }
    }
    
    public static function getAccessTokenFromCode($code)
    {
        if(empty($code))
        {
            return false ;
        }
        global $zm_config ;
        
        $oZM_ME = new ZME_Me($zm_config) ;
        $AccessK =  $oZM_ME->getAccessTokenFromCode($code) ;  
        if(!empty($AccessK)&& isset($AccessK['access_token']))
        {
            return $AccessK ; 
        }
        else
        {
                 header('location: http://me.zing.vn');        
                 exit();    
        }
    }    
    
    
    /**
     * get uid login zing me
     * return int
     */
    public static function getUserId()
    {
        $uId = 0;
        if(Controller::$access_token)
        {            
            global $zm_config ;
            $oZM_ME = new ZME_Me($zm_config) ;
            $result =  $oZM_ME->getInfo(Controller::$access_token,'id');
            $uId = $result['id']  ;
        }
        return $uId;
    }

    /**
     * Lay Id = User Name
     * return int
     */
 /*
    public static function getUserIdByUsername($userName)
    {
      self::require_api('ZM_Users');
      $ZM_User = new ZM_Users();
      return $ZM_User->getUserIdByUsername($userName);
    } */
    
    
    /**
     * lay thong tin cua 1 danh sach user
     * @param $uIds mang cac user id
     * author : anhbv
     * 21/8/2013
     */
    static function getUserInfo()
    {
        $sfields = "id,username,displayname,tinyurl";
        global $zm_config ;   
        //$ZM_User = new ZME_User($zm_config);
        //$infos = $ZM_User->getInfo(Controller::$access_token,$uIds,$sfields);
        $oZM_ME = new ZME_Me($zm_config) ;
        $infos[0] =  $oZM_ME->getInfo(Controller::$access_token,$sfields);
        return $infos;
    }
    /**
     * lay danh sach id cua ban be
     */
    static function getFriendList()
    {
        global $zm_config ;
        $oZM_ME = new ZME_Me($zm_config) ;
        $friends =  $oZM_ME->getFriends(Controller::$access_token);
        return $friends;
    }
    /**
     * ham lay toan bo uid cua ban be co choi ung dung
     * toannm
     * 1/9/2010
     */
/*    static function getAppUsers()
    {
        self::require_api('ZM_Friends');
        $ZM_Friend =  new ZM_Friends();
        $users = $ZM_Friend->getAppUsers(Controller::$session_key);
        return $users;
    } */
    /**
     * ham feed len wall
     * toannm
     * 1/9/2010
     */
  /*  function feedWall($template_bundle_id,$template_data)
    {
        self::require_api('ZM_Feed');
        $ZM_Feed = new ZM_Feed();
        $result = $ZM_Feed->publishUserActionV2(Controller::$session_key,$template_bundle_id,$template_data);
        return $result;
    } */

}
