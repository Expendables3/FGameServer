<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
define( "BETA_DIR" , ROOT_DIR ."/BetaTest" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");


include_once LIB_DIR.'/zingme-sdk/BaseZingMe.php';

include_once LIB_DIR.'/zingme-sdk/ZME_UserLevel.php';


$config_live = array(
	'appname' => 'testgraphapi',
	'apikey' => '687982bf4ff2023d7b020543c7f0b302',
	'secretkey' => 'f301a2170662e6ac2e4cb945d9018f6f',
	'env' => 'production'
);


$zm_UserLevel = new ZME_UserLevel($config_live);

$signed_request = "DXb0JasQ7wG8rxkiF6CWjehT5RewHQTCmTk3d_4uKRg=.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImV4cGlyZXMiOjcyMDAsImlzc3VlZF9hdCI6MTM2NTY0OTQ0MywiYWNjZXNzX3Rva2VuIjoiNjg3OTgyYmY0ZmYyMDIzZDdiMDIwNTQzYzdmMGIzMDIuT0dVMFpqTTBZVGM9SGxHakVIaG1xTXJEbXJULVVVUnhOb3RDU2RpNnFTT3ZPRnFJRzQzWWZMOFdwdEhDNlVCeEpuQWZKZHU3dV92djBFYW5NTTdSWjJhWmQ1ak5tT3RhQVBNRktHb3JzeG52ejgwWTJ6aDRwMlVlWkc4VldEZ1JRdVU1QjZfMmtTYmZid21iRmtWaE1JdTBqVmY3IiwidWlkIjo2NjIxOTk1OH0=";

//////////////////////////
$access_token = $zm_UserLevel->getAccessTokenFromSignedRequest($signed_request);

//$access_token = $_REQUEST["access_token"];

$result = $zm_UserLevel->getLevel($access_token);

var_dump($result);

?>
