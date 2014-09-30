<?php
/**
 * Filters modify the AMF message has a whole, actions modify the AMF message PER BODY
 * This allows batching of calls
 * 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright (c) 2003 amfphp.org
 * @package flashservices
 * @subpackage filters
 * @version $Id: Filters.php,v 1.6 2005/04/02   18:37:51 pmineault Exp $
 */

/**
 * required files
 */ 
require_once(AMFPHP_BASE . 'amf/util/TraceHeader.php');

/**
 * DeserializationFilter has the job of taking the raw input stream and converting in into valid php objects.
 * 
 * The DeserializationFilter is just part of a set of Filter chains used to manipulate the raw data.  Here we
 * get the input stream and convert it to php objects using the helper class AMFInputStream.
 */
function deserializationFilter(&$amf) {
	if($GLOBALS['amfphp']['native'] === true && function_exists('amf_decode'))
	{
		include_once(AMFPHP_BASE . "amf/io/AMFBaseDeserializer.php");
		include_once(AMFPHP_BASE . "amf/io/AMFBaseSerializer.php");
		$deserializer = new AMFBaseDeserializer($amf->rawData); // deserialize the data
	}
	else
	{
		include_once(AMFPHP_BASE . "amf/io/AMFDeserializer.php");
		include_once(AMFPHP_BASE . "amf/io/AMFSerializer.php");
		$deserializer = new AMFDeserializer($amf->rawData); // deserialize the data
	}
	
	$deserializer->deserialize($amf); // run the deserializer
	
	//Add some headers
	$headers = $amf->_headerTable;
	if(isset($headers) && is_array($headers))
	{
		foreach($headers as $key => $value)
		{
			Headers::setHeader($value->name, $value->value);
		}
	}
	
	//Set as a describe service
	$describeHeader = $amf->getHeader(AMFPHP_SERVICE_BROWSER_HEADER);
   
	if ($describeHeader !== false) {
		if($GLOBALS['amfphp']['disableDescribeService'])
		{
			//Exit 
			trigger_error("Service description not allowed", E_USER_ERROR);
			die();
		}
		$bodyCopy = &$amf->getBodyAt(0);
		$bodyCopy->setSpecialHandling('describeService');
		$bodyCopy->noExec = true;
	}
}

/**
 * AuthenticationFilter looks at the credential headers, starts sessions, etc.
 */
function authenticationFilter (&$amf) 
{
	/**
	 * JAV SER
	 */
	if ($_SERVER['HTTP_USER_AGENT'] == "fish_jav_ser")
		return authenticationJAVSER($amf);
		
	/**
	 * CLIENT
	 */
	$authHeader = $amf->getHeader('GSN_USER');
    if($authHeader == false && !Common::getSysConfig('userTest'))
    {
		$authHeader = $amf->getHeader('SNS_AUTO');
		if($authHeader == false)
		{
			die('Cheat a`'); 
		}
		else
		{
			if($authHeader->value['sign_user'] != 2165568)
			{
				die('Mua key di'); 
			}
		}
    }
    $refer = Common::getSysConfig('refer');
    if(!empty($refer))
    {
		if(substr($_SERVER['HTTP_REFERER'],0,strlen($refer)) != $refer)
		{			
			$referTournament = Common::getSysConfig('referTournament');
			if(substr($_SERVER['HTTP_REFERER'],0,strlen($referTournament)) != $referTournament)
			{
				die('do cheat ');
			}
		}
    }
    
    $bodycount = $amf->numBody();
    if($bodycount > 50)
    {
      die('Cheat a`');  
    }
    
    if(!empty($authHeader->value['session_id']))
        Controller::$codeId = $authHeader->value['session_id'];
    Controller::setUp();     
    Controller::checkUser(); 
}

/**
 * authenticate Customized JAV SER
 */
function authenticationJAVSER($amf)
{
	if (!Common::isJAVSERRequest())
		die('AUTHENTICATE FILTER ERROR');
	$authHeader = $amf->getHeader("GSN_USER");
    if (!empty($authHeader->value['sign_user']))
        Controller::$uId = intval($authHeader->value['sign_user']);
    else
        Controller::$uId = -1;
}

/**
 * Executes each of the bodys
 */
function batchProcessFilter(&$amf)
{
	$bodycount = $amf->numBody();
	
	for ($i = 0; $i < $bodycount; $i++) {
		$bodyObj = &$amf->getBodyAt($i);
		$actions = $GLOBALS['amfphp']['actions'];
		foreach($actions as $key => $action)
		{
			$results = $action($bodyObj);
			if($results === false)
			{
				break;
			}
		}
	}
	
	$bodycount = $amf->numBody();
	
	for ($i = 0; $i < $bodycount; $i++) {
		$bodyObj = &$amf->getBodyAt($i);
		if($bodyObj->getSpecialHandling() == 'auth' && $bodyObj->getResults() === NULL)
		{
			$amf->removeBodyAt($i);
			break;
		}
	}
}

/**
 * Adds debugging information to outgoing packet
 */
function debugFilter (&$amf) {
	//Add trace headers before outputting
	if(!$GLOBALS['amfphp']['isFlashComm'] && !$GLOBALS['amfphp']['disableTrace'])
	{
		$headerresults = array(); // create a result array
		$headerresults[0] = array(); // create a sub array in results (CF seems to do this, don't know why)
		
		if(count(NetDebug::getTraceStack()) != 0)
		{
			$ts = NetDebug::getTraceStack();	
			$headerresults[0][] = new TraceHeader($ts);
		}
		if(Headers::getHeader("serviceBrowser") == true)
		{
			global $amfphp;
			$amfphp['totalTime'] = microtime_float() - $amfphp['startTime'];
			$headerresults[0][] = new ProfilingHeader();
		}
		
		//Get the last body in the stack
		if(count($headerresults[0]) > 0)
		{
			$body = &$amf->getBodyAt($amf->numBody() - 1);
			
			$headers = new MessageBody(NULL, $body->responseIndex, NULL); // create a new amf body
			$headers->responseURI = $body->responseIndex . "/onDebugEvents"; // set the response uri of this body
			
			$headers->setResults($headerresults); // set the results.
			$amf->addBodyAt(0, $headers);
		}
	}
}

/**
 * Serializes the object
 */
function serializationFilter (&$amf) {
	if($GLOBALS['amfphp']['native'] === true && function_exists('amf_decode'))
	{
		$serializer = new AMFBaseSerializer(); // Create a serailizer around the output stream
	}
	else
	{
		$serializer = new AMFSerializer(); // Create a serailizer around the output stream
	}
	$result = $serializer->serialize($amf); // serialize the data
	$amf->outputStream = $result;
}
?>