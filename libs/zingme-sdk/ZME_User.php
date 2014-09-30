<?php

/**
 * Copyright 2011 ZingMe
 * 
 */
class ZME_User extends BaseZingMe {

	private $info_path = "user/info/@%s";

	public function __construct($config) {
		parent::__construct($config);
	}

	/**
	 * Get profile info of userid which are friends of user logged in with access_token
	 *
	 * @param type $access_token
	 * @param uids : list of userid to get info. Please remember these uids must be friend with user logged in with access_token. 
	 * And length of list must be less than 50
	 * @param type $fields . Fields can be 'id','username','displayname','tinyurl','profile_url','gender','dob'
	 * @return user info of user with access token
	 */
	public function getInfo($access_token, $uids = array(), $fields='') {

		if (!is_array($uids)) {
			throw new ZingMeApiException(-101, "FunctionException", "uids params not is a list");
		}

		$path = sprintf($this->info_path, $this->appname);

		$params = array();
		$params['access_token'] = $access_token;
		$params['uids'] = implode(',', $uids);
		if (!empty($fields))
			$params['fields'] = $fields;

		$url = $this->getUrl("graph", $path, $params);

		$data = $this->sendRequest($url);
		return $data;
	}

}

?>
