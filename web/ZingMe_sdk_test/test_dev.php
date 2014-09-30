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

$config = $config_dev;

$zm_Me = new ZME_Me($config);

$signed_request = "EV_iE5s7lzmn0CPhLSW1XtwRxtFJrWtO--eUm1H-VIA=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImV4cGlyZXMiOjcyMDAsImlzc3VlZF9hdCI6MTMyMDc0MzM1MCwiYWNjZXNzX3Rva2VuIjoiZGIxNzczMDNhYTBiMTQ5OGU1MTJmMTEyODAzZjg0NmQuTVdVek9URXpaRFE9a0JsaFFoSFZNdEV3Wjk0ZnNydjVUanA3c0hjZkRJMTRvZ29fQUVmSUd0LThvOHFadU1MSUdSRmxtS3R0MXBpV3FRVWxHa0RVRnZXMEdVZ2gzQXp3ZE5HVGlWS2ItNTJ4SFhJaHlyUkYxQm5KRUZCbTFrajFrbkRsd2U0M2JaUWxUcEpqYWJ2UDlqbm9mTkFrNmQxUCIsInVpZCI6NDg3NTc5fQ==";


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

echo "<br><br>";


?>

