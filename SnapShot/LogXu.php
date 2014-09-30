<?php
    //error_reporting(E_ALL ); //=> show all
    error_reporting(0); //=> show all
    ini_set("memory_limit", "-1");
    ini_set("max_execution_time", "-1");
    
    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");
    define( "IMAGCACHE" , ROOT_DIR ."/imgcache");

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php'; 
    //require LIB_DIR.'/log/zf_log.php';
    
    Controller::init();

    class DatabaseUser
    {
        public static function get($uId,$keyWord = 'User',$id = '',$exKey = '',$Cache = false)
        { 

            $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";
            if(StaticCache::check($key))
                return  StaticCache::get($key);

            if($Cache == true)
            {
              $data = DataProvider::getMemcache()->get($key);
            }
            else
              $data = DataProvider::getMemBase()->get($key);
            return $data;
        }
    }
    
    //Model::$appKey = Common::getSysConfig('appKey');
    
    $f1 = fopen('UserId_1.csv','r');
    //$f2 = fopen('UserId_2.csv','r');
    //$f3 = fopen('UserId_3.csv','r');
   // $f4 = fopen('UserId_4.csv','r');

    
    if ($f1) 
    {
        $LogAnalytic = new LogAnalytic();
        while (!feof($f1)) 
        {
            $UserId = intval(fgets($f1));
            if(!empty($UserId))
            {
                $oUser = DatabaseUser::get($UserId,'User');
                if(!is_object($oUser))
                    continue ;
                $logData = array();
                $logData= array(
                    'AccName'       =>$oUser->getUserName(),
                    'TotalXu'       =>$oUser->ZMoney,
                    'TotalPromoXu'  =>0,
                    'uId'           =>$UserId,
                    'act'           =>'fulldata',
                    'param1'        =>'',
                );        
                $LogAnalytic->sendLog_snapshot($logData);       
            }
        }
        fclose($f1);
    }    
        
    echo 'ok';
    
    
?>
