<?
class ZM_Feed extends Services_ZM_Common
{
	protected $subapi = '/feed';

	public function __construct()
	{		
		parent::__construct();
	}

	public function pushFeed($accountname, $template_bundle_id, $template_data = '')
	{
		$args = array(
            'accountname' => $accountname,
			'template_bundle_id' => $template_bundle_id,
			'template_data' => json_encode($template_data)
		);
		$result = $this->callMethod('Feed.pushFeed', $args);
        return $result;
	}
	
	public function publishUserAction($session_key, $template_bundle_id, $template_data)
	{
		$args = array(
            'session_key' => $session_key,
			'template_bundle_id' => $template_bundle_id,
			'template_data' => json_encode($template_data)
		);		
		$result = $this->callMethod('Feed.publishUserAction', $args);				
        return $result;
	}
	
	public function publishUserActionV2($session_key, $template_bundle_id, $template_data)
	{
		$args = array(
            'session_key' => $session_key,
			'template_bundle_id' => $template_bundle_id,
			'template_data' => json_encode($template_data)
		);		
		$result = $this->callMethod('Feed.publishUserActionV2', $args);				
        return $result;
	}

	public function getRawData($session_key,$feed_type = 'profile', $page = 1, $records = 10)
	{
		$args = array(
            'session_key' => $session_key,
			'feed_type' => $feed_type,
			'page' => $page,
			'records' => $records
		);
		$result = $this->callMethod('Feed.getRawData', $args);
        return $result;
	}

	public function getRawDataFromUser($userid,$feed_type = 'profile', $page = 1, $records = 10)
	{
		$args = array(            
			'userid' => $userid,
			'feed_type' => $feed_type,
			'page' => $page,
			'records' => $records
		);
		$result = $this->callMethod('Feed.getRawDataFromUser', $args);
        return $result;
	}
	
}
?>