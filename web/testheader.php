<?php

        error_reporting(E_ALL & ~E_NOTICE); //=> show all  

        define( "ROOT_DIR" , '..' );
        define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
        define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
        define( "SER_DIR" , ROOT_DIR ."/Service" );
        define( "LIB_DIR" , ROOT_DIR ."/libs" );

        require LIB_DIR.'/Common.php';
        require LIB_DIR.'/ZingApi.php';
        require LIB_DIR.'/dal/DataProvider.php';

        Controller::init();


?>