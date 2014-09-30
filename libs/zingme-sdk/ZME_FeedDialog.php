<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ZME_FeedItem {
	public $userIdFrom;
	public $userIdTo;
	public $actId;
	public $tplId;
	public $objectId;
	public $attachName;
	public $attachHref;
	public $attachCaption;
	public $attachDescription;
	public $mediaType;
	public $mediaImage;
	public $mediaSource;
	public $actionLinkText;
	public $actionLinkHref;
	
	public function ZME_FeedItem($userIdFrom, $userIdTo, $actId, $tplId, $objectId, $attachName, $attachHref, $attachCaption, $attachDescription, $mediaType, $mediaImage, $mediaSource, $actionLinkText, $actionLinkHref) {
		$this->userIdFrom = $userIdFrom;
		$this->userIdTo = $userIdTo;
		$this->actId = $actId;
		$this->tplId =$tplId;
		$this->objectId = $objectId;
		$this->attachName = $attachName;
		$this->attachHref = $attachHref;
		$this->attachCaption = $attachCaption;
		$this->attachDescription = $attachDescription;
		$this->mediaType = $mediaType;
		$this->mediaImage = $mediaImage;
		$this->mediaSource = $mediaSource;
		$this->actionLinkText = $actionLinkText;
		$this->actionLinkHref = $actionLinkHref;
	}
}

class ZME_FeedDialog extends BaseZingMe {
	
	static $ZME_PREDEFINED_ACTION_THAMGIALANDAU = 1001;	
	static $ZME_PREDEFINED_ACTION_MOIBAN = 1003;	
	
	static $MAXLEN_ATTACHNAME = 80;
	static $MAXLEN_ATTACHHREF = 150;
	static $MAXLEN_ATTACHCAPTION = 30;
	static $MAXLEN_ATTACHDESCRIPTION = 200;
	static $MAXLEN_MEDIAIMAGE = 150;
	static $MAXLEN_MEDIASOURCE = 150;
	static $MAXLEN_ACTIONLINKTEXT = 20;
	static $MAXLEN_ACTIONLINKHREF = 150;


	public function __construct($config) {
		parent::__construct($config);
	}
	
	/**
	 *
	 * @param type $feedId
	 * @param type $validateKey 
	 * @return : feedId if validate successful. 0 : if validate failed; -1 : if feedId is not number
	 */
	public function validateFeedId($feedId, $validateKey) {
		if(!is_numeric($feedId)) return -1;
		$key = $this->apikey . "_" . $this->secretkey . "_" . $feedId;
		$key = md5($key);
		if($key == $validateKey) return $feedId;
		return 0;
	}
	
	/**
	 *
	 * @param $feedItem : feedItem struct
	 * @return signature used with feedDialog api with javascript function zm.ui
	 */	
	public function genFeedSigForDialog(ZME_FeedItem $feedItem) {
		$this->checkValidValue($feedItem);
		
		$key = $this->secretkey . ":" .
				$feedItem->userIdFrom . ":" .
				$feedItem->userIdTo . ":" .
				$feedItem->actId . ":" .
				$feedItem->tplId . ":" .
				$feedItem->objectId . ":" .
				$feedItem->attachName . ":" .
				$feedItem->attachHref . ":" .
				$feedItem->attachCaption . ":" .
				$feedItem->attachDescription . ":" .
				$feedItem->mediaType . ":" .
				$feedItem->mediaImage . ":" .
				$feedItem->mediaSource . ":" .
				$feedItem->actionLinkText . ":" .
				$feedItem->actionLinkHref;
		return md5($key);
	}
	
	private function checkValidValue(ZME_FeedItem &$feedItem) {
		if(intval($feedItem->userIdFrom) <= 0) {
			$this->throwFeedDialogException("userIdFrom <= 0 : invalid");
		}

		if(intval($feedItem->userIdTo) < 0) {
			$this->throwFeedDialogException("userIdTo < 0 : invalid");
		}

		//attachName
		if(iconv_strlen($feedItem->attachName, 'UTF-8') > self::$MAXLEN_ATTACHNAME) {
			$this->throwFeedDialogException("attachName exceed max length ". self::$MAXLEN_ATTACHNAME);
			return;
		}
						
		//attachHref
		if(iconv_strlen($feedItem->attachHref, 'UTF-8') > self::$MAXLEN_ATTACHHREF) {
			$this->throwFeedDialogException("attachHref exceed max length ". self::$MAXLEN_ATTACHHREF);
			return;
		}
				
		//attachCaption		
		if(iconv_strlen($feedItem->attachCaption, 'UTF-8') > self::$MAXLEN_ATTACHCAPTION) {
			$this->throwFeedDialogException("attachCaption exceed max length ". self::$MAXLEN_ATTACHCAPTION);
			return;
		}
				
		//attachDescription
		if(iconv_strlen($feedItem->attachDescription, 'UTF-8') > self::$MAXLEN_ATTACHDESCRIPTION) {
			$this->throwFeedDialogException("attachDescription exceed max length ". self::$MAXLEN_ATTACHDESCRIPTION);
			return;
		}
				
		//mediaImage
		if(iconv_strlen($feedItem->mediaImage, 'UTF-8') > self::$MAXLEN_MEDIAIMAGE) {
			$this->throwFeedDialogException("mediaImage exceed max length ". self::$MAXLEN_MEDIAIMAGE);
			return;
		}
				
		//mediaSource
		if(iconv_strlen($feedItem->mediaSource, 'UTF-8') > self::$MAXLEN_MEDIASOURCE) {
			$this->throwFeedDialogException("mediaSource exceed max length ". self::$MAXLEN_MEDIASOURCE);
			return;
		}
				
		//actionLinkText
		if(iconv_strlen($feedItem->actionLinkText, 'UTF-8') > self::$MAXLEN_ACTIONLINKTEXT) {
			$this->throwFeedDialogException("actionLinkText exceed max length ". self::$MAXLEN_ACTIONLINKTEXT);
			return;
		}
						
		if(iconv_strlen($feedItem->actionLinkHref, 'UTF-8') > self::$MAXLEN_ACTIONLINKHREF) {
			$this->throwFeedDialogException("actionLinkHref exceed max length ". self::$MAXLEN_ACTIONLINKHREF);
			return;
		}
	}
	
	private function throwFeedDialogException($msg_error) {
		throw new ZingMeApiException(-301, "GraphAPIException.FeedDialog.InvalidValue:" . $msg_error);
	}
	
}

?>
