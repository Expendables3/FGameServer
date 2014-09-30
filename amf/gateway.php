<?php

	//Include things that need to be global, for integrating with other frameworks

	include "globals.php";

	//Include framework
	include "core/amf/app/Gateway.php";

	$gateway = new Gateway();

	$gateway->setClassPath($servicesPath);

	//Set where class mappings are loaded from (ie: for VOs)
	//$voPath defined in globals.php
	$gateway->setClassMappingsPath($voPath); 
	
	$gateway->setCharsetHandler("UTF-8","UTF-8","UTF-8");

	$gateway->setErrorHandling(0);

	if(PRODUCTION_SERVER)
	{
		//Disable profiling, remote tracing, and service browser
		$gateway->disableDebug();
		// Keep the Flash/Flex IDE player from connecting to the gateway. Used for security to stop remote connections. 
		$gateway->disableStandalonePlayer();
	}

	$gateway->enableGzipCompression(25*1024);

    //Service now
	$gateway->service();

?>
