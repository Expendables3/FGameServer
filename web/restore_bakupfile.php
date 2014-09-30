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
    echo 'Start restore';        
    
    $restoredata = array();
    
    $file = SOURCE.'/'.$uId;
    try{
        $achieved_data = file_get_contents($file);        
    }
    catch(Exception $exc){
        echo 'error get data '.$uId;
        exit();        
    }
    
    $allData = Common::deachieve_data($achieved_data);
    
    $desUser = intval($_GET['toId']);
     if (empty($desUser))
        return 'uId is required !' ;    
    echo 'to User '. $desUser . "<br/>";
    
    global $CONFIG_DATA;
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.140',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11212, true),
                        );
    DataProvider::init();
        
    $res = Common::delete_udata($desUser);    
    
    $res = Common::restore_udata($desUser, $allData);
    
    echo 'Restore file success';
        
?>
