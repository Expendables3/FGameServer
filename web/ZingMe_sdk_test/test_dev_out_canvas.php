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

echo "<br><br>";

$state = rand(1, 100000);

$login_url = $zm_Me->getUrlAuthorized('http://localhost/dev',$state);
//note : state param is an option to let the 3rd party to transfer state value to protect against CSRF attacks
//when zingme authorize user and redirect to redirect_uri, the 3rd must recheck the state value
echo "1. test get url to get code authorized:<br>"; 
var_dump($login_url);


//////////////////////////
$code = 'umv-pOt9Isk3E6BHiOaa585e5DM_q0uGqqv2YvgfVnI0So7mZinWDECl8SUtWGzPpcLMnAVQ7WwU94BwsynSO-OY6g6BcmPfj7PFg8_E1JM-SKwgt94BQjHsAlccja1JXZfTp_s6IZxL1s2vuhWTCUnjOhcsiHHclJtow9IdYX7W04viMdGXmGNxVMr9SYBHDwnxBMCiA8eNlNqzOp5HXJVFQmrcCqNzMIOujSrWjzuBFW%3D%3D';
$code = urldecode($code);
$access_token_data = $zm_Me->getAccessTokenFromCode($code);
echo "<br><br>";
echo "2. test get accesstoken from authorized code<br>";
var_dump($access_token_data);
echo "<br><br>";
exit;
if($access_token_data != null) {
	$access_token = $access_token_data['access_token'];
}

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

