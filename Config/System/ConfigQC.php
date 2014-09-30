<?php
/**
 * Config
 */
define( "CON_apiURL" , 'http://dev.openapi2.me.zing.vn/api' );
define( "CON_apiKey" , '5bc2df085caea1a9e280ab67aaa4fe4d' );
define( "CON_secret" , '93d8f34de2aad1f4d1b2cfec84287404' );

$staticServer = "zfi-static.apps.zing.vn";
$applink = 'http://me.zing.vn/apps/fish?_src=m';
return array
(
    'debug' => false,
    'userTest' => true,
    'maintain' => false,
    "domain" => 'http://'.$_SERVER['HTTP_HOST'],
    "appName" => "myFish" ,
    "logName" => "myFish" ,
    "appKey" => "" ,
    "linkFeed" => '<a href ='.$applink.'> myFish</a>', 
    "platform" => "" ,
    'appId' => 999 ,
    "dbServer" => array(
                                  array('10.198.36.64',50)
                                  //'10.198.36.74',
                                  //'10.198.36.76'
                        ),
    "dbBuckets" => array(
                                  'Membase'          => array(11216, true),
                                  'Memcached'  =>  array(11215, true),
                        ),
    'Billing'  => array('10.199.38.21',30002,5),
    'applink' => $applink, 
    'logServer' => array('10.198.36.212') ,
    'flashDir' =>'http://'.$staticServer.'/imgcache/',
    'imgdir'=>'http://'.$staticServer.'/imgcache/file/images/',
    'cssdir'=>'http://'.$staticServer.'/imgcache/file/css/',
    'jsdir'=>'http://'.$staticServer.'/imgcache/file/js/',
    'cssdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_css/',
    'imgdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_images/',
    'jsdir_admin'=>'http://'.$staticServer.'/imgcache/file/admin_js/',
    'version'=>11,
    'dataVersion'=>0,
);

?>
