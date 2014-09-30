<?php
/**
 * Config
 */
define( "CON_apiURL" , 'http://dev.openapi2.me.zing.vn/api' );
define( "CON_apiKey" , '5bc2df085caea1a9e280ab67aaa4fe4d' );
define( "CON_secret" , '93d8f34de2aad1f4d1b2cfec84287404' );
//$staticServer = "static-fish.zing.vn";
$staticServer = "123.30.184.149/myFish";
$applink = 'http://me.zing.vn/apps/fish?_src=m';
return array
(
	'debug' => true,
    'userTest' => true,
    'maintain' => false,
	"domain" => 'http://'.$_SERVER['HTTP_HOST'].'/myFish',
	"appName" => "myFish" ,
    "logName" => "myFish" ,
    "linkFeed" => '<a href ='.$applink.'> myFish</a>', 
	"platform" => "" ,
	'appId' => 999 ,
    "dbServer" => array(
                                  //array('10.198.36.213',50)
				array('127.0.0.1',50),
                                  //'10.198.36.74',
                                  //'10.198.36.76'
                        ),
    "dbBuckets" => array(
                                  //'Membase'          => array(11208, true),
                                  //'Memcached'  =>  array(11209, true),
				'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11211, true),
                        ),
    'Billing'  => array('10.199.38.21',30002,30),
    'applink' => $applink, 
    'logServer' => array('127.0.0.1') ,
	'flashDir' =>'http://'.$staticServer.'/imgcache/',
	'imgdir'=>'http://'.$staticServer.'/imgcache/file/images/',
	'cssdir'=>'http://'.$staticServer.'/imgcache/file/css/',
	'jsdir'=>'http://'.$staticServer.'/imgcache/file/js/',
	'cssdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_css/',
	'imgdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_images/',
	'jsdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_js/',
//    'version'=>11,
	'version'=>170,
    'dataVersion'=>1,
);

?>
