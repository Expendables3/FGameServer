<?php
    error_reporting(E_ALL ); //=> show all
    ini_set("memory_limit", "-1");
    ini_set("max_execution_time", "5236");
    
    define( "ROOT_DIR" , '/home/www/webroot/myFish' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");
    define( "IMAGCACHE" , ROOT_DIR ."/imgcache");
	define( "ITEMCODE" , ROOT_DIR ."/ItemCode");

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 
    
    //Controller::init();

    Model::$appKey = Common::getSysConfig('appKey');
        
    $file_1 = fopen('interactkey.csv','w');
    $file_2 = fopen('savefailkey.csv','w');
    
            
    $confKey = include(ITEMCODE.'/CodeList.php');   
    $flag = 0 ;
    foreach($confKey as $key =>$confId)
    {         
        
        $aa = DataProvider::get($key,'ConfigItemCode');

        if(!empty($aa)) // key nay da duoc luu tren he thong 
        {
            $string_1 = "$key=>$confId,\n" ;
            fwrite($file_1,$string_1,strlen($string_1));
            continue ;
        }        
        $data = array() ;
        $data['IdConfig']   = $confId;
                    
        if(!DataProvider::set($key,'ConfigItemCode',$data))
        {
         $string_2 = "$key=>$confId,\n" ;
         fwrite($file_2,$string_2,strlen($string_2));
        }

    }
    
    fclose($file_1);
    fclose($file_2);

    echo 'save thanh cong';
    
    
?>
