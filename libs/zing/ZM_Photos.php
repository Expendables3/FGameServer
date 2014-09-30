<?
class ZM_Photos extends Services_ZM_Common
{
	protected $subapi = '/photos';

	public function __construct()
	{		
		parent::__construct();
	}

	
	public function upload($session_key, $description = "")
	{
		$file = APPLICATION_PATH . "etc/errorBmp.gif";		

		$result = $this->callMethodWithPostRawData('Photos.upload', 
			array('session_key' => $session_key, 'description' => $description),
			$file
		);
		
        return $result;	
	}
}


?>