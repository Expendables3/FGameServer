<?php
class Gift 
{
    public $GiftId;        // int      Id cua nguoi gui
    public $FromTime ; // thoi gian nhan thu
    public $FromId  ;


    public function __construct($FromId,$giftId)
    {
        $this->GiftId = $giftId ;
        $this->FromId = intval($FromId) ;
        $this->FromTime = $_SERVER['REQUEST_TIME'];
    }

    // kiem tra xem thu da qua gioi han chua
    
    public function isExpire($time)
    {
        return ($_SERVER['REQUEST_TIME'] > ($this->FromTime + $time)) ;
    }

}

?>