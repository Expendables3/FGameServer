<?php
//error_reporting(0); 
error_reporting(E_ALL & ~E_NOTICE); //=> show all

header('Cache-Control: no-cache, must-revalidate, max-age=0',true);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT',true);
header('Pragma: no-cache',true);
define( "IN_INU" , true );

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Index");

require LIB_DIR.'/Common.php';
require LIB_DIR.'/ZingApi.php';
require LIB_DIR.'/dal/DataProvider.php';
Controller::init();
    // block user
    $blockUid = require CONFIG_DIR.'/BlockUid.php';
    if(in_array(Controller::$uId, $blockUid))
    {
        include TPL_DIR.'/index_block.php';
        exit();   
    }

    // log link soure 
    $site =    $_REQUEST['site'];
    //$link =    $_REQUEST['link'];
    $site = str_replace("'",'',$site);
    $p = explode(".", $site);
    $p0 = isset($p[0])?$p[0]:0;
    $p1 = isset($p[1])?$p[1]:0;
    $p2 = isset($p[2])?$p[2]:0;
    $p3 = isset($p[3])?$p[3]:0;
    
    Zf_log::write_act_log_new(Controller::$uId,0,0,'SourceLink',0,0,0,0,$site,$p0,$p1,$p2,$p3);   
    if(Controller::$uId)
    {
	    $conf = Common::getConfig();
        $confUser = Common::getConfig('UserTest');
	    if($conf['maintain'] && !in_array(Controller::$uId,$confUser,true))
	    {
            $notice = true ;
            //include TPL_DIR.'/maintainance.php';
            header('location:'.TPL_DIR.'/maintainance.php');
            exit();
	    }
        else
        {
            // phan ghi log 
            Zf_log::write_act_log(Controller::$uId,0,10,'loadIndex'); 

            //Khoa tai khoan khi vao game
            $oUser = User :: getById(Controller::$uId) ;
            if($oUser->passwordState == PasswordState::IS_UNlOCK)
            {
                $oUser->passwordState = PasswordState::IS_LOCK;
                $oUser->save();
                StaticCache::forceSaveAll();
                Debug::log('lock tai khoan'.$oUser->passwordState);    
		echo("logloglog");
		exit();
            }
            include TPL_DIR . '/index_run.php' ;  
        }
          
        
    }else
    {
        header('location: http://passport.me.zing.vn/login');
        exit();
    }
