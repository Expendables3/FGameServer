<?php

   //error_reporting(E_ALL); //=> show all
   //error_reporting(0); //=> show all 
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service/" );
    define( "LIB_DIR" , ROOT_DIR ."/libs/" );

    require LIB_DIR.'/DataRunTime.php';
    require LIB_DIR.'/Common.php'; 
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 
    
    $vip = DataRunTime::get('LuckyMachine_VipWeapon'); 
    $gift = DataRunTime::get('LuckyMachine_Gift6');
    echo 'so luong trang bi vip  :'.$vip ;    
    echo '<br>so luong qua so 6 :'.$gift ;
    
?>
