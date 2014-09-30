<?php

        error_reporting(1); //=> off all
		ini_set("memory_limit", "-1");
	    define("PRODUCTION_SERVER", false);

    	define( "ROOT_DIR" , '..' );
    	define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    	define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    	define( "SER_DIR" , ROOT_DIR ."/Service" );
    	define( "LIB_DIR" , ROOT_DIR ."/libs" );

        require LIB_DIR.'/Common.php';
	    require LIB_DIR.'/ZingApi.php';
	    require LIB_DIR.'/dal/DataProvider.php';


        //Set start time before loading framework
        list($usec, $sec) = explode(" ", microtime());
        $amfphp['startTime'] = ((float)$usec + (float)$sec);

        $servicesPath = "../Service/";
        $voPath = "../Service/";