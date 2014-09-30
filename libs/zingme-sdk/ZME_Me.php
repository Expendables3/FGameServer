<?php

/**
 * Copyright 2011 ZingMe
 * 
 */
class ZME_Me extends BaseZingMe {

	
	private $info_path = "me/@%s";
	private $friends_path = "me/friends/@%s";
	private $isfanof_path = "me/isfanof/@%s";
	private $isbookmark_path = "me/isbookmark/@%s";

	public function __construct($config) {
		parent::__construct($config);
	}

	/**
	 * Get profile info of user logged in with access_token
	 *
	 * @param type $access_token
	 * @param type $fields . Fields can be 'id','username','displayname','tinyurl','profile_url','gender','dob'
	 * @return user info of user with access token
	 */
	public function getInfo($access_token, $fields='') {
		$path = sprintf($this->info_path, $this->appname);

		$params = array();
		$params['access_token'] = $access_token;
		if (!empty($fields))
			$params['fields'] = $fields;

		$url = $this->getUrl("graph", $path, $params);

		$data = $this->sendRequest($url);
		return $data;
	}

	/**
	 * Get friends list of user logged in with access_token
	 *
	 * @param type $access_token
	 * 
	 * @return list of friend id
	 */
	public function getFriends($access_token) {
		$path = sprintf($this->friends_path, $this->appname);
		$params = array();
		$params['access_token'] = $access_token;
		if (!empty($fields))
			$params['fields'] = $fields;

		$url = $this->getUrl("graph", $path, $params);
		$data = $this->sendRequest($url);
		return $data;
	}
	
	/**
	 * Check isFanOf of user logged (with accesstoken) with one profile vip/biz
	 *
	 * @param type $access_token
	 * @param type $profile : profile name of vip / biz, for example singer.phamquynhanh
	 * 
	 * @return true or false or throw Exception if profile not found or profile is not vip or biz
	 */
	public function isFanOf($access_token, $profile = '') {
		$path = sprintf($this->isfanof_path, $this->appname);
		$params = array();
		$params['access_token'] = $access_token;
		$params['profile'] = $profile;
		$url = $this->getUrl("graph", $path, $params);
		$data = $this->sendRequest($url);
		return $data;
	}

	/**
	 * Check isBookmark of user logged (with accesstoken) with current app/game
	 *
	 * @param type $access_token
	 * 
	 * @return true or false if user bookmarked app/game or throw Exception if having error
	 */
	public function isBookmark($access_token, $whichapp) {
		$path = sprintf($this->isbookmark_path, $this->appname);
		$params = array();
		$params['access_token'] = $access_token;
		$params['whichapp'] = $whichapp;
		$url = $this->getUrl("graph", $path, $params);		
		$data = $this->sendRequest($url);
		return $data;
	}
}

?>
