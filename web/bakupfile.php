<?php
   //error_reporting(E_ALL); //=> show all

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "SOURCE" , "/data/myfish_bakup" );
    
    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 

    Controller::init();
    Model::$appKey = "";
    
    $uId = intval($_GET['uId']) ;
    if (empty($uId))
        return 'uId is required !' ;
    
    // real db
    global $CONFIG_DATA;
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.42',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11213, true),
                        );    
    DataProvider::init();    
    
    $allData = array();
    $allData = Common::snapshot_udata($uId);
                
    if(empty($allData)) 
    {
        echo 'data empty '. $uId;
        exit();
    }
    
    $achieved_data = Common::achieve_data($allData);
    
    $file = SOURCE.'/'.$uId;
    try{
        $file_handle = fopen($file, 'w');        
        fwrite($file_handle, $achieved_data, strlen($achieved_data));    
        fclose($file_handle);    
    }
    catch(Exception $exc){
        echo 'error bakup '.$uId;
        exit();
    }
    
    echo 'Bakup file success'; 
?>
