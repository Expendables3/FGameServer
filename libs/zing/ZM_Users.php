<?
class ZM_Users extends Services_ZM_Common
{
	protected $subapi = '/users';

	public function __construct()
	{		
		parent::__construct();
	}

	//private
	public function getUserIdByUsername($users)
	{
		$result = $this->callMethod('Users.getUserIdByUsername', array(
            'users' => $users
        ));		
        return intval((string)$result);
	}	

	//private
	public function updateStatus($session_key, $status = null)
	{
		$result = $this->callMethod('Users.updateStatus', array(
            'session_key' => $session_key,
			'status' => $status
        ));
        return $result;
	}
	
	public function getInfo($uids, $fields)
	{
		$result = $this->callMethod('Users.getInfo', array(
            'uids' => $uids,
			'fields' => $fields
        ));
        return $result;
	}
	
}


?>