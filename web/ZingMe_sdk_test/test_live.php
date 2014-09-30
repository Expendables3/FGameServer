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

$config_dev = array(
	'appname' => 'test',
	'apikey' => 'db177303aa0b1498e512f112803f846d',
	'secretkey' => '8d3738883de7a75b382bb1253f59c943',
	'env' => 'development'
);

$config_live = array(
	'appname' => 'test1',
	'apikey' => '4bc575ea590a0b297e526815a38e9565',
	'secretkey' => '8295a3d9b21c624ac0ff353733d227b8',
	'env' => 'production'
);

$config = $config_live;

$zm_Me = new ZME_Me($config);

$signed_request = "VfCcOx8hESRgHi4JkwfrEl_MMEmBx8PgEJp-HnRoj7A=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImV4cGlyZXMiOjcyMDAsImlzc3VlZF9hdCI6MTMyMDc0NTMxNiwiYWNjZXNzX3Rva2VuIjoiNGJjNTc1ZWE1OTBhMGIyOTdlNTI2ODE1YTM4ZTk1NjUuWlRjNU9XWTFPR1k9VDYwR2pUeURyZDFzTHNoc2kwcDhUbjE4OTlSMEN3cjJMZFNTeWo4MW03OE1HMlpzczNJOEkyZmtSZkZBM2tDWTZZUzFkOHpOdnFaM21LSWRRQVFZV05DeFIzTTVMX2tkR005c09WR21nUVM0NmQ0dHpLN1lncU9VUnFkbkZpb3g5S25ZS0x2UDlpdmpEWHA1SUZyUSIsInVpZCI6NDg3NTc5fQ==";

//////////////////////////
$access_token = $zm_Me->getAccessTokenFromSignedRequest($signed_request);

echo "1. test get accesstoken=" . $access_token;
echo "<br><br>";


$profile = "singer.phamquynhanh";

$isfan = $zm_Me->isFanOf($access_token, $profile);

var_dump($isfan);

exit();

//get userid from signed_request
$uid = $zm_Me->getUserLoggedIn($signed_request);
echo "2.test get uid of user logged by singed request:";
var_dump($uid);

echo "<br><br>";

echo "3.test get me info from accesstoken:";
$me = $zm_Me->getInfo($access_token);
var_dump($me);

echo "<br><br>";

echo "4.test get me friends from accesstoken:";
$friends = $zm_Me->getFriends($access_token);
echo "totalfriend=" . count($friends);
echo "<br>list=";var_dump($friends);

echo "<br><br>";

echo "5.test get 20 friends' info from friend list above:";
$uids = array();

for($i=0;$i<20;$i++) {
	$uids[] = $friends[$i];
}

$zm_User = new ZME_User($config);

$user = $zm_User->getInfo($access_token, $uids,$fields="id,username,displayname");

var_dump($user);

$whichapp = "kvtm";

$isBookmark = $zm_Me->isBookmark($access_token, $whichapp);
var_dump($isBookmark);
?>