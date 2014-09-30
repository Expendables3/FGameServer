<?php
  error_reporting(E_ALL); //=> show all

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 
    
    $uId = intval($_GET['uId']) ;
    if (empty($uId))
        return 'uId is required !' ;
    
    
    global $CONFIG_DATA;
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.31',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11213, true),
                        );
    
    DataProvider::init();
    Controller::init();
    


    
    $allData = array();
    $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3));  
    
    Model::$appKey = "";
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
    
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.40',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11221, true),
                                  'Memcached'  =>  array(11222, true),
                        );
  
    DataProvider::init();
    Model::$appKey = "";
    
    
    
    
    foreach($CONFIG_DATA['Key'] as $akey => $oKey)
    {
        if (isset($multiObj[$akey]))
        {
            foreach($multiObj[$akey] as $index => $mid)
            {      
                if (is_object($allData[$akey][$mid])){
                    echo 'Object ' . $akey . $mid . " = " . get_class($allData[$akey][$mid]) . "<br/>";  
                    $allData[$akey][$mid]->forceSave();
                }
                    
            }
        }
        else
        {      
            if (is_object($allData[$akey])){
                echo 'Object ' . $akey ." = " . get_class($allData[$akey]) . "<br/>";   
                $allData[$akey]->forceSave();
            }
                
        }
    }
   
   echo "Clone user $uId done !" ;
    
    
?>