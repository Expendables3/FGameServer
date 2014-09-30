<?php
ini_set('soap.wsdl_cache_enabled', "0");
try{
    $uId = $_GET['uId'];
    if(empty($uId)) $uId = 18313052 ;
	$LoginClient = new SoapClient("http://10.30.44.34/zingfish_payment_services/wsdl/PaymentZingFish.wsdl");

	$arrResponse = $LoginClient->GetUser($uId,md5($uId.'1647737a6091f41cd0f4e02a00e80ea2'));
	print_r($arrResponse);
	echo '<br>';
	$time	=	time();

	$key 	= "5000".$uId.$time."1647737a6091f41cd0f4e02a00e80ea2";
	$arrResponse = $LoginClient->AddMoney("$uId",5000,$time ,md5($key ));
	
	print_r($arrResponse);

}
catch(SoapFault $exception)
{   echo '<pre/>';
	print_r($exception);
	exit;
}

?>