<?



echo "Begin unittest <br><br>";

$unittest = new MyUnitTest();
$unittest->do_unittest();
unset($unittest);

class MyUnitTest {

	
	function __construct() {
		$this->require_list_v2_0();
		
	}

	public function do_unittest() {

		$session_key = "1F0189888C0FE91D6A612025";
			
		//////////// Users ////////////////////

		$username = "fortinet1";
		$password = "123456";
		//$this->doLogin($username, $password);

		

		$this->getLoggedInUser($session_key);				////////////

			

		//$status = date('YmdHis');
		
				
		//$this->updateStatus($session_key,$status);			////////////

		

		$userids = '481401,487579';
		$this->getInfo($userids);							////////////

		

		//////////// Friends ///////////////

		$this->getLists($session_key);						////////////

		
		/////////// Feed /////////////////

		$template_bundle_id = 193;

		$template_data = array();

		$template_data['message'] = "message";
		$_zm_feed = new ZM_Feed();

		

		$this->publishUserAction($session_key, $template_bundle_id, $template_data);		////////////


	
		

	}
	
	////////////////////////////////

	private function doLogin($username, $password)
	{
		$zm_users = new ZM_Users();
		$result = $zm_users->doLogin($username, $password, false);
		echo "Users.doLogin with $username/$password - result = ";
		var_dump($result);
		echo "<br><br>";
	}

	private function getLoggedInUser($session_key)
	{
		$_session = new ZM_Sessions();		
		$uid = $_session->getLoggedInUser($session_key);
		echo "Users.getLoggedInUser - uid = " . $uid;
		echo "<br><br>";
	}

	private function updateStatus($session_key, $status)
	{
		$zm_users = new ZM_Users();
		$result = $zm_users->updateStatus($session_key,$status);
		echo "Users.udpate Status - result = $result";
		echo "<br><br>";
	}

	private function getInfo($userids = '487579',$fields = 'userid,username,dob,status')
	{
		$users = new ZM_Users();
		$infos = $users->getInfo($userids,$fields);
		echo "Users.getInfo - result = ";
		var_dump($infos);
		echo "<br><br>";
	}

	/////////// Friends ////////////
	private function getLists($session_key)
	{
		$zm_friends = new ZM_Friends();
		$result = $zm_friends->getLists($session_key);

		echo "Friends.getLists - result = ";
		var_dump($result);
		echo "<br><br>";
	}

	/////////// Feed ///////////////

	private function publishUserAction($session_key, $template_bundle_id, $template_data)
	{
		$feed = new ZM_Feed();
		$result = $feed->publishUserAction($session_key,$template_bundle_id,$template_data);
		
		echo "Feed.publishUserAction - result = " . $result;
		echo "<br><br>";
	}
	
	private function getRawData($session_key, $feed_type, $page, $records)
	{
		$zm_feed = new ZM_Feed();

		$result = $zm_feed->getRawData($session_key, $feed_type, $page, $records);

		echo "Feed.getRawData - result = ";
		var_dump($result);
		echo "<br><br>";
	}


	function require_list_v2_0() {

		
		require_once "ZM_API_Common.php";
		require_once "ZM_Sessions.php";
		require_once "ZM_Users.php";
		require_once "ZM_Friends.php";
		require_once "ZM_Feed.php";
		require_once "ZM_Notifications.php";
		require_once "ZM_Notice.php";
		require_once "ZM_Photos.php";
		
	}	
}


?>