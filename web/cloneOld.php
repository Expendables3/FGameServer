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
    
    // real db
    global $CONFIG_DATA;
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.201',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11213, true),
                        );
    
    DataProvider::init();
    Controller::init();
    
    $desUser = intval($_GET['toId']);
    echo 'to User '. $desUser . "<br/>";
    
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
    
    // draft db
    $CONFIG_DATA['Servers'] = array(
                                  array('10.30.44.140',50)
                        );
    $CONFIG_DATA['Buckets'] = array(
                                  'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11212, true),
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
                    $allData[$akey][$mid]->setKey($desUser,$mid);
                    
                    if ($akey=='Lake')
                    {
                        foreach($allData[$akey][$mid]->FishList as $id => $oFish)       
                        {
                            $oFish->updateLakeKey(Model::$appKey.'_'.$desUser.'_'.$mid.'__Lake');
                        }
                    }
                    
                    
                    DataProvider::set($desUser,$akey,$allData[$akey][$mid],$mid); 
                }
                    
            }
        }
        else
        {      
            if (is_object($allData[$akey])){
                echo 'Object ' . $akey ." = " . get_class($allData[$akey]) . "<br/>";
                if ($akey=='User')   
                    $allData[$akey]->Id = $desUser;
                $allData[$akey]->setKey($desUser); 
                DataProvider::set($desUser,$akey,$allData[$akey]);    
            }
                
        }
    }
   
    
    
   
    echo "Clone user $uId done !" ;
    
    
?>
