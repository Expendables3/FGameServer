<?php
class FeedService extends Controller
{
  
  private $userIdFrom;
  private $userIdTo;
  private $actId;
  private $tplId;
  private $objectId;
  private $attachName;
  private $attachHref;
  private $attachCaption;
  private $attachDescription;
  private $mediaType;
  private $mediaImage;
  private $mediaSource;
  private $actionLinkText;
  private $actionLinkHref;

  public function FeedItem($param)
  {//$userIdFrom, $userIdTo, $actId, $tplId, $objectId, $attachName, $attachHref, $attachCaption, $attachDescription, $mediaType, $mediaImage, $mediaSource, $actionLinkText, $actionLinkHref) {
    $this->userIdFrom = $param['userIdFrom'];
    $this->userIdTo = $param['userIdTo'];
    $this->actId = $param['actId'];
    $this->tplId =$param['tplId'];
    $this->objectId = $param['objectId']; 
    $attachName = $param['attachName'];
    $attachHref = $param['attachHref'];
    $attachCaption = $param['attachCaption']; 
    $attachDescription = $param['attachDescription']; 
    $this->mediaType = $param['mediaType']; 
    $mediaImage = $param['mediaImage']; 
    $mediaSource = $param['mediaSource']; 
    $actionLinkText = $param['actionLinkText']; 
    $actionLinkHref = $param['actionLinkHref']; 

    //define size item
    $this->attachName = (iconv_strlen($attachName, 'UTF-8') > 80) ? $attachName = iconv_substr($attachName, 0, 80) : $attachName;
    $this->attachHref = (iconv_strlen($attachHref, 'UTF-8') > 150) ? $attachHref = iconv_substr($attachHref, 0, 150) : $attachHref;
    $this->attachCaption = (iconv_strlen($attachCaption, 'UTF-8') > 30) ? $attachCaption = $iconv_substr($attachCaption, 0, 30) : $attachCaption;
    $this->attachDescription = (iconv_strlen($attachDescription, 'UTF-8') > 200) ? $attachDescription = iconv_substr($attachDescription, 0, 200) : $attachDescription;
    $this->mediaImage = (iconv_strlen($mediaImage, 'UTF-8') > 150) ? $mediaImage = iconv_substr($mediaImage, 0, 150) : $mediaImage;
    $this->mediaSource = (iconv_strlen($mediaSource, 'UTF-8') > 150) ? $mediaSource = iconv_substr($mediaSource, 0, 150) : $mediaSource;
    $this->actionLinkText = (iconv_strlen($actionLinkText, 'UTF-8') > 20) ? $actionLinkText = iconv_substr($actionLinkText, 0, 20) : $actionLinkText;
    $this->actionLinkHref = (iconv_strlen($actionLinkHref, 'UTF-8') > 150) ? $actionLinkHref = iconv_substr($actionLinkHref, 0, 150) : $actionLinkHref;
    
    return array('Error'=>Error::SUCCESS,'signKey'=>$this->creatSignKey()); 
   
    
  }
  
  
  private function creatSignKey() {

    $strKey =
        (
        "6b5fa8df3e3894ff776a7ec6604ac595" . ":" .
        $this->userIdFrom . ":" .
        $this->userIdTo . ":" .
        $this->actId . ":" .
        $this->tplId . ":" .
        $this->objectId . ":" .
        $this->attachName . ":" .
        $this->attachHref . ":" .
        $this->attachCaption . ":" .
        $this->attachDescription . ":" .
        $this->mediaType . ":" .
        $this->mediaImage . ":" .
        $this->mediaSource . ":" .
        $this->actionLinkText . ":" .
        $this->actionLinkHref
        );
    return md5($strKey);
  }
} 
?>
