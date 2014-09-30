<?php
    //error_reporting(E_ALL ); //=> show all
    error_reporting(0); //=> off all
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");
    define( "IMAGCACHE" , ROOT_DIR ."/imgcache");

    if($_SERVER['REMOTE_ADDR'] != "10.60.5.11")
        exit();
    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 
    
    Controller::init();
    
    $UserId = intval($_REQUEST['UserId']);
    if(empty($UserId))
    {
        echo 'Wrong UserId';
        exit();
    }
    
    $oUser = User::getById($UserId);
    if(!is_object($oUser))
    {
        echo 'Data of User is Null';
        exit();
    }
    
    echo "$oUser->ChargeXu|$oUser->PromoXu";   
?>
