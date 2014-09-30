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
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");

require LIB_DIR.'/Common.php';
require LIB_DIR.'/ZingApi.php';
require LIB_DIR.'/dal/DataProvider.php';

$_REQUEST = array_merge( $_POST , $_GET );
$oMB = DataProvider::getMemcache();
$srvId_membase = array();
$srvId_membase = $oMB->get('Admin_Tool_ServerId');

if($_REQUEST['login'])  
{
    if(empty($_REQUEST['uId']) || empty($_REQUEST['pass']))
    {
        echo 'can nhap them thong tin dang nhap ' ; 
        header('location: http://fish.apps.zing.vn/web/loginAdmin.php');    
    }
    
    // kiem tra user
    
    if(!Controller::checkAdmin($_REQUEST['uId'],$_REQUEST['pass']))
    {
        echo 'Ban Khong co quyen truy cap';
        return;
    }  

    $srvId_membase = array(); 
    $srvId_membase['uId'] = $_REQUEST['uId'] ;  
    $srvId_membase['pass'] = $_REQUEST['pass'] ;  
    $oMB->set('Admin_Tool_ServerId',$srvId_membase,0,4*3600);  
    
}
else if($_REQUEST['logout'])  
{
    $srvId_membase = array();    
    $oMB->set('Admin_Tool_ServerId',$srvId_membase,0,4*3600);  
    header('location: http://fish.apps.zing.vn/web/loginAdmin.php');    
}
else if(empty($srvId_membase))
{       
     echo 'can nhap them thong tin dang nhap ' ; 
     header('location: http://fish.apps.zing.vn/web/loginAdmin.php');    
}

 Controller::$uId = $srvId_membase['uId'] ;

if(empty(Controller::$uId ))
{
    echo 'wrong tham so uId' ;
    exit() ;
}

//Controller::setUp();

$act = empty( $_GET['act'] ) ? 'run' : $_GET['act'];

Common::loadService('Admin') ;

$object = new Admin();

$conf = Common::getConfig();        
Model::$appKey = $conf['appKey'];

{

	if( method_exists( $object , $act ) )
	{
		$result = $object->$act();
		return ;
	}
}
