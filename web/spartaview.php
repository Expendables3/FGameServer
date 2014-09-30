<?php

   error_reporting(0); //=> off all
   //error_reporting(E_ALL); //=> show all
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    //define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';

    Controller::init();

    $a= DataProvider::getMemBase()->get('MaxOpenBox_Event_8_3');

    var_dump($a);