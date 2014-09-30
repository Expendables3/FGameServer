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
define( "BETA_DIR" , ROOT_DIR ."/BetaTest" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Index");

require LIB_DIR.'/Common.php';
require LIB_DIR.'/ZingApi.php';
require LIB_DIR.'/dal/DataProvider.php';

$object = new Controller();

if($object->checkUser()){
	$conf = Common::getConfig();
	if(!Common::checkTestUser())
	{
		include TPL_DIR . '/index_notice.php' ;
	}
	else
	{
	    // log before load game
        $host = $_SERVER['HTTP_HOST'];
		$self = $_SERVER['PHP_SELF'];
		$query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
		$ip_url = !empty($query) ? "http://{$host}{$self}?$query" : "http://{$host}{$self}";
	    Zf_log::write_act_log(Controller::$uId, Controller::$uId, 'init_begin', '', '', '', '', "{$ip_url}");
		$flash = $conf['flashDir'] . 'ZingFish'.$conf['version'].'.swf?v='. $conf['flashVer'].'&xmldir='.$conf['xmldir'].'&version='.$conf['version'];
		include TPL_DIR . '/index_run.php' ;
	}	    
}else
{
    header('location: http://passport.me.zing.vn/login');
    exit();
}
