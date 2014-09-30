<?php

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
define( "BETA_DIR" , ROOT_DIR ."/BetaTest" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");


include_once LIB_DIR.'/zingme-sdk/BaseZingMe.php';
include_once LIB_DIR.'/zingme-sdk/ZME_Me.php';
include_once LIB_DIR.'/zingme-sdk/ZME_User.php';
include_once LIB_DIR.'/zingme-sdk/ZME_Photo.php';


$config_live = array(
    'appname' => 'testfresher2012',
    'apikey' => 'eba443348315d8c27c8c070cb2a40a52',
    'secretkey' => '7cbe3430d58fa47f4f9f267ea473e382',
    'env' => 'production'
);
$config = $config_live;

$zm_Me = new ZME_Me($config);
$state = rand(1, 100000);

$login_url = $zm_Me->getUrlAuthorized('http://localhost/dev', $state);
$code = "gVpHwdUkbHB9oitsHEchFyJcbAzkdwTaikBff3xBsnF4ZV7v9lN3RV2AzlC3ieC9yyEkpoNjmLYjdltOFjZt7BFvdOuyqRXPaQ-0oa26xbsOfuNBJzhY1AFniizaaPuU-Ad4ZI6MeqhD-wwxNTh1JSB2nRvRZuyQjbMOHGWUdUFCF6ktcNnrH7T7UAIVBpCWVa5htyWdO5Cz52gDrWfdBHm57z-2I4aW9aIjPHDDkTL7";
//////////////////////////
$code = urldecode($code);
$access_token_data = $zm_Me->getAccessTokenFromCode($code);
if ($access_token_data != null) {
    $access_token = $access_token_data['access_token'];
}
echo "1. test get accesstoken=" . $access_token;
$description ="Test PHP SDK Photo zing-mobile";
$filename ="/home/trailn/test.jpg";
$zm_Photo = new ZME_Photo($config);
$zm_Photo->upload($access_token, $filename, $description);

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
