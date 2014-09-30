<?
class ZM_Notice extends Services_ZM_Common
{
	protected $subapi = '/notice';

	public function __construct()
	{		
		parent::__construct();
	}
	
	public function getNumberOfFriendRequest($user_list)
	{
		$result = $this->callMethod('Notice.getNumberOfFriendRequest', array(
            'user_list' => $user_list
        ));		
        return $result;
		
	}

	public function getUnreadMessageNumber($user)
	{
		$result = $this->callMethod('Notice.getUnreadMessageNumber', array(
            'user' => $user
        ));
        return $result;
	}

	public function getLastStatus($user_list)
	{
		$result = $this->callMethod('Notice.getLastStatus', array(
            'user_list' => $user_list
        ));
        return $result;

	}
}


?>