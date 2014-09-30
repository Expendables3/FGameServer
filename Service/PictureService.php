<?php
  class PictureService{
      public function takePicture()
      {
        // check valid time take picture
        $oUserProfile = UserProfile::getById(Controller::$uId);      
        $timeTakePicture = Common::getParam('TakePicture');
        if($_SERVER['REQUEST_TIME'] - $oUserProfile->LastPictureTime < $timeTakePicture)
        {
          return array('Error'=>Error::CANT_TAKE_PICTURE);
        }
        
        // return token
        $oUser = User::getById(Controller::$uId);  
        if (!is_object($oUser))
        {
            return  array('Error' => Error::NO_REGIS) ;
        }    
        $token = $this->getAppSecureCode(Controller::$uId, $oUser->getUserName(), '1e48c4420b7073bc11916c6c1de226bb');
        
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        $LastDay = date('Ymd',$oUserProfile->LastPictureTime);
        if ($Today != $LastDay)
            $oUserProfile->NumTakePicture = 0;
        
        
        
        $conf_expTimes = Common::getConfig('Param','MaxTimesTakePicture');
        if ($oUserProfile->NumTakePicture < $conf_expTimes)
        {
            $conf_nextLevel = Common::getConfig('UserLevel',$oUser->Level,'NextExp');
            $expReceive = ceil($conf_nextLevel/100);
            
            // happyday
            $happyExpRate = Common::bonusHappyWeekDay('takePhotoExpRate');
            if($happyExpRate)
                $expReceive *= $happyExpRate;
            
            $oUser->addExp($expReceive);
            $oUser->save();    
        }
        
        $oUserProfile->LastPictureTime = $_SERVER['REQUEST_TIME'];
        $oUserProfile->NumTakePicture++;
        
        
        $oUserProfile->save();
        return array('Error' => Error::SUCCESS ,'Token' => $token,'App_id' => 77, 'Username' => $oUser->getUserName(), 'Exp' =>$expReceive);  
      }
      
      
      
      private function getAppSecureCode($userid, $username, $secrect_key)
      {
        return md5("{$userid}:{$username}:{$secrect_key}");
      }
  }
?>
