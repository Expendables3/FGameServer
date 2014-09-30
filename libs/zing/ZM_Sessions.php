<?
class ZM_Sessions extends Services_ZM_Common
{
	protected $subapi = '/sessions';

	public function __construct()
	{		
		parent::__construct();
	}
		
	public function getLoggedInUser($sessionKey)
    {		
        $result = $this->callMethod('Users.getLoggedInUser', array(
            'session_key' => $sessionKey
        ));		
        return intval((string)$result);
    }	

	//private
	public function doLogin($username,$password,$long_session)
	{
		$result = $this->callMethod('Users.doLogin', array(
            'username' => $username,
			'password' => $password,
			'long_session' => $long_session
        ));
        return $result;
	}

	//private
	public function doLogout($session_key)
	{
		$result = $this->callMethod('Users.doLogout', array(
            'session_key' => $session_key
        ));
        return $result;
	}
}


?>