<?php
/**
 * Config
 */
define( "CON_apiURL" , 'http://openapi2.me.zing.vn/api' );
define( "CON_apiKey" , 'b610ef9d2c67bd7f9c3d2b447372f913' );
define( "CON_secret" , '6b5fa8df3e3894ff776a7ec6604ac595' );

$staticServer = "fish-static.apps.zing.vn";
$applink = 'http://me.zing.vn/apps/fish?_src=m';
return array
(
    'debug' => false,
    'userTest' => false,
    'maintain' => false,
    'encrypt' => false,
    'paymentDev' => true,
    'production' =>true,
    "domain" => 'http://'.$_SERVER['HTTP_HOST'],
    "appName" => "myFish" ,
    "logName" => "myFish" ,
    "appKey" => "" , 
    "linkFeed" => '<a href ='.$applink.'> myFish</a>', 
    "platform" => "" ,
    'appId' => 999 ,
    "dbServer" => array(
                                   array('10.30.44.140',50),
                        ),
    "dbBuckets" => array(
                                              'Membase'          => array(11211, true),
                                  'Memcached'  =>  array(11212, true),
									
                        ),
    'Billing'  => array('10.30.17.26',30002,10),
    'applink' => $applink, 
    'logServer' => array('127.0.0.1') ,
    'logServer_SnapShot' => array('10.30.70.51'),
    'flashDir' =>'http://'.$staticServer.'/imgcache/',
    'imgdir'=>'http://'.$staticServer.'/imgcache/file/images/',
    'cssdir'=>'http://'.$staticServer.'/imgcache/file/css/',
    'jsdir'=>'http://'.$staticServer.'/imgcache/file/js/',
    'cssdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_css/',
    'imgdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_images/',
    'jsdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_js/',
    'version'=>170,
    'versionJson' => 170,
    'versionLocalization' => 170,
    'dataVersion'=>77,
    'refer' => 'http://fish-static.apps.zing.vn/imgcache/myFish170.swf?',
    'referTournament' => 'http://fish-static.apps.zing.vn/imgcache//content/tournament12.swf',
	'tournamentVersion' => 0,
	'socketIp' => '10.30.44.140',
	'socketPort' => 443,
	'socketServerList' => array(
		array('ip'=>'120.138.65.98'),
		array('ip'=>'10.30.44.140'),
		array('ip'=>'10.30.44.10'),
	),
    'isBetaMarket' => false,
    'isDownMarket' => false, 
    'serverId' => 1,
    'snapshotA1_rootdir' => '/g6bkfarm/SnapshotA1MyFish',     
);

?>