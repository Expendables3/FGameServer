<?php
 /*//   error_reporting(E_ALL); //=> show all
      error_reporting(0); //=> show all 
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service/" );
    define( "LIB_DIR" , ROOT_DIR ."/libs/" );

    require LIB_DIR.'/Common.php'; 
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';
    
    Controller::init();

    Common :: loadModel('User') ;  
    Common :: loadModel('UserProfile') ;
    Common :: loadModel('Store') ;
    Common::loadModel('NewQuest');
    Common::loadModel('Quest');
    Common::loadModel('Diary'); 
 
   $funcType = $_GET['funcType'];
   $uId =  $_GET['uId'];
   $step = $_GET['step'];
   
   if(empty($uId) || empty($funcType))
   {
       die('sai tham so');
   }
   
   $runArr = array();
   
   $oUser = User::getById($uId);
   if(!is_object($oUser))
   {
       $runArr['Error'] = Error::NO_REGIS;
   } 
  else if($funcType == 1)
  {
     $oUserPro = UserProfile::getById($uId);
     $oQuest   = Quest::getById($uId);  
     $runArr['User']['Level'] = $oUser->Level;
     $runArr['User']['MateFish'] = count($oUserPro->ActionInfo['UnlockFishList']);
     if($oQuest->QuestInfo[1]['Status'] == true )
     {
       $runArr['User']['Tutorial'] = true ;
     }
     else $runArr['User']['Tutorial'] = false ;    
     $runArr['User']['GiftNum'] = $oUserPro->Event['Coop']['GiftNum'];
     $runArr['Error'] = Error::SUCCESS;  
  }
  else
  {
    if($step > 0)
    {
        $oUserPro = UserProfile::getById($uId);
        $oUserPro->Event['Coop']['Step'] = $step ;
        if(!isset($oUserPro->Event['Coop']['GiftNum']))
            $oUserPro->Event['Coop']['GiftNum'] = 0 ;
        $oUserPro->forceSave();
    }
    $runArr['Error'] = Error::SUCCESS; 
  } 
  $data = json_encode($runArr);
  echo $data;    */
?>