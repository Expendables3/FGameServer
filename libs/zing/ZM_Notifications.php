<?
class ZM_Notifications extends Services_ZM_Common
{
	protected $subapi = '/notifications';

	public function __construct()
	{		
		parent::__construct();
	}
	
	public function sendEmailByTemplate($recipients,$template_id, $template_data)
	{
		$args = array(
            'recipients' => $recipients,
			'template_id' => $template_id,
			'template_data' => json_encode($template_data)
		);		
		$result = $this->callMethod('Notifications.sendEmailByTemplate', $args);				
        return $result;
	}
	
	public function sendEmail($recipients,$subject,$text)
	{
		$args = array(
            'recipients' => $recipients,
			'subject' => $subject,
			'text' => $text
		);		
		$result = $this->callMethod('Notifications.sendEmail', $args);				
        return $result;
	}
	
	public function notifyZingChat($uid, $title, $title_content, $content, $pic_url, $url)
	{
		$args = array(
			'uid' => $uid,
			'title' => $title,
			'title_content' => $title_content,
			'content' => $content,
			'pic_url' => $pic_url,
			'url' => $url
		);
		
		$result = $this->callMethod('Notifications.notifyZingChat', $args);
		return $result;
	}

	public function pushNotification($appId, $username, $body)
	{
		$args = array(
			'appId' => $appId,
			'username' => $username,
			'body' => $body
		);
		$result = $this->callMethod('Notifications.pushNotification', $args);
		return $result;
	}
	
	
}
?>