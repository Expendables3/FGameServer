<?php
error_reporting(E_ALL & ~E_NOTICE); //=> show all

header('Cache-Control: no-cache, must-revalidate, max-age=0',true);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT',true);
header('Pragma: no-cache',true);
define( "IN_INU" , true );

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );

define( "XML_DIR" , ROOT_DIR ."/imgcache/xml" );
require LIB_DIR.'/Common.php';

$a = array( 
          "BuffItem",
          "Gem",
          "Tournament",
		  "ChanceToHit",
		  "ReputationBuff",
		  "SmashEgg_QuartzLevel",
		  "SmashEgg_Quartz",
		  "Wars_Seal",
    );

$b = array();

$file = fopen(XML_DIR . "/myFish.json", 'w');
$json = '{';
fwrite($file, $json, strlen($json));
foreach ($a as $value)
{
    $json = '';
	$b[$value] = Common::getConfig($value);         
    $encode = json_encode($b);    
    $encode = substr($encode, 1); 		//Cat dau    
    $encode = substr($encode, 0, -1); 	//Cat duoi
    $json .= $encode.', ';
    $b = array();
    fwrite($file, $json, strlen($json));
	echo $value.'<br/>';
} 

$json = substr($json, 0, -2);                 
$json = '}';  
fwrite($file, $json, strlen($json));

fclose($file);

echo "OK_json_socket";