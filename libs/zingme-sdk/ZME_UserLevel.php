<?php

/**
 * Copyright 2013 ZingMe
 * 
 */
class ZME_UserLevel extends BaseZingMe {

	private $userlevel_path = "level/getlevel/@%s";

	public function __construct($config) {
		parent::__construct($config);
	}

	/**
	 * Get vip level of user logged in
	 *
	 * @param type $access_token
	 * @return vip level of user
	 */
	public function getLevel($access_token) {

		
		$path = sprintf($this->userlevel_path, $this->appname);

		$params = array();
		$params['access_token'] = $access_token;
		
		$url = $this->getUrl("graph", $path, $params);

		$data = $this->sendRequest($url);
		return $data;
	}

}

?>
