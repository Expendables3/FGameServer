<?
class ZM_Friends extends Services_ZM_Common
{
	protected $subapi = '/friends';

	public function __construct()
	{		
		parent::__construct();
	}
	
	public function getLists($sessionKey)
	{
		 $result = $this->callMethod('Friends.getLists', array(
            'session_key' => $sessionKey
        ));		
        return $result;
	}
	
	public function getAppUsers($sessionKey)
	{
		$result = $this->callMethod('Friends.getAppUsers', array(			
            'session_key' => $sessionKey			
        ));		
        return $result;
	}
}
?>