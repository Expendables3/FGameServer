<?php
    error_reporting(0);
    //error_reporting(E_ALL & ~E_NOTICE); //=> show all
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );      
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    require LIB_DIR.'/Common.php'; 
    require LIB_DIR.'/dal/DataProvider.php';              
    $start = microtime(true);    
    $bool = DataProvider::set('test','Monitor','abc');        
    $end = microtime(true); 
    $bint = $bool?1:0;
    $time= ($end-$start)*1000;
    echo json_encode(array('result' => $bint, 'time' => $time));