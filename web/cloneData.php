<?php
   //error_reporting(0); //=> off all
    error_reporting(0); //=> show all

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';                                                                      
    require LIB_DIR.'/dal/DataProvider.php';

    global $CONFIG_DATA;
    
    $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3));
    
    Controller::init();

    $allData = array();
    $uId = $_GET['uId'];

    foreach($CONFIG_DATA['Key'] as $akey => $oKey)
    {
        if (isset($multiObj[$akey]))
        {
            foreach($multiObj[$akey] as $index => $mid)
            {
                $allData[$akey][$mid] = DataProvider::get($uId,$akey,$mid);        
            }
        }
        else
        {
            $allData[$akey] = DataProvider::get($uId,$akey);        
        }
    }

    $allData = serialize($allData);
    echo $allData ;
