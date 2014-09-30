<?php
require_once "ZM_Config.php";
class Services_ZM_Exception extends Exception
{    
    protected $lastCall = '';
 
    public function __construct($message, $code = 0, $lastCall = '')
    {
        parent::__construct($message, 0);
        $this->lastCall = $lastCall;
    }
    
    public function getLastCall()
    {
        return $this->lastCall;
    }
}

abstract class Services_ZM_Common
{
    protected $subapi = '';

	protected $api = '';
   
    protected $version = '2.0';
   
    public $sessionKey = '';
   
    public function __construct()
    {
        $this->setAPI(Services_ZM::$apiURL);	
    }
	
    public function callMethod($method, array $args = array())
    {
        $this->updateArgs($args, $method);
        
        $response = $this->sendRequest($args);
		
        $result   = $this->parseResponse($response);
        return $result;
    }
    
    public function callMethodWithPostRawData($method, array $args = array(), $file)
    {
    	$this->updateArgs($args, $method);
    	
    	$url = $this->api . "?";
		if(is_array($args))
		{
			foreach($args as $key => $val)
			{
				$url .= $key . "=" . $val . "&";
			}
		}
		
		$post = array();
		$post['_file'] = '@' . $file;			
    					
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);        
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);				
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, Services_ZM::$timeout);
        $response = curl_exec($ch);               
        
        if (curl_errno($ch)) {        	
            throw new Services_ZM_Exception(
                curl_error($ch), curl_errno($ch), $args['method']
            );
        }

        curl_close($ch);
        
        var_dump($response);exit;
		
    	$result   = $this->parseResponse($response);
        return $result;    	
    }
	
	protected function sendRequest(array $args)
	{		
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api);
        curl_setopt($ch, CURLOPT_HEADER, false);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);				
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, Services_ZM::$timeout);
        $response = curl_exec($ch);

		//var_dump($response);
		
        if (curl_errno($ch)) {
            throw new Services_ZM_Exception(
                curl_error($ch), curl_errno($ch), $args['method']
            );
        }

        //var_dump($response, $args);

        curl_close($ch);

        return $response;
	}   
			
	protected function sendRequest1(array $args)
	{
		$url = $this->api . "?";
		if(is_array($args))
		{
			foreach($args as $key => $val)
			{
				$url .= $key . "=" . $val . "&";
			}
		}		
		//echo $url;exit;
		return file_get_contents($url);		
	}
		
	
  
    protected function parseResponse($response)
    {			
		try
		{
			$json = json_decode($response,true);			
		}
		catch(Exception $e)
		{
			//echo "-->" . $e->getMessage();exit();
		}
						
		if($json['error_code'] != 0)
		{
			//throw new Services_ZM_Exception($json['error_message'],$json['error_code'],$this->api);
			return $json['error_message'];
		}
		

        return $json['data'];
    }
    
    protected function updateArgs(array &$args, $method)
    {
        $args['api_key'] = Services_ZM::$apiKey;
        $args['v']       = $this->version;        
        $args['method']  = $method;
        $args['call_id'] = microtime(true);
        $args            = $this->signRequest($args);
    }
    
    protected function signRequest(array $args) 
    {
        if (isset($args['sig'])) {
            unset($args['sig']);
        }

        ksort($args);

        $sig = '';
        foreach ($args as $k => $v) {
            $sig .= $k .'=' . $v;
        }

        $sig        .= Services_ZM::$secret;
        $args['sig'] = md5($sig);		
        return $args;
    }
  
    protected function checkRequest($xml)
    {
        $message = null;
        $code    = 0;
        switch ($this->version) {
        case '1.0':
            if (isset($xml->error_code)) {
                $code = (int)$xml->error_code;
            }

            if (isset($xml->error_msg)) {
                $message = $xml->error_msg;
            }
            break;
        default:
            if (isset($xml->fb_error->code)) {
                $code = (int)$xml->fb_error->code;
            }

            if (isset($xml->fb_error->msg)) {
                $message = $xml->fb_error->msg;
            }
            break;
        }

        if ($code > 0 || !is_null($message)) {
            return array('code' => $code, 'message' => $message);
        }
		
        return false;
    }

    public function getAPI()
    {
        return $this->api;
    }
    
    public function setAPI($api)
    {		
        $this->api = $api . $this->subapi;
    }
}

?>
