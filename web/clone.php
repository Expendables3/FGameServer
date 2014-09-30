<?php
    //error_reporting(E_ALL); //=> show all

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 

    //Controller::init();
    Model::$appKey = Common::getSysConfig("appKey");
    
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
	DataProvider::$StaticCache = false;
    
    $desUser = intval($_GET['toId']);
    echo 'to User '. $desUser . "<br/>";
    
    $allData = array();
    $allData = Common::snapshot_udata($uId);
    
    // draft db
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
   
    echo "Clone user $uId done !" ;
    
    
?>
