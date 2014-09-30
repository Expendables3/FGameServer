<?php
class Mail 
{
    
    public $Content;    // string     noi dung thu
    public $FromId;        // int      Id cua nguoi gui
    public $IsRead;        // bool     danh dau thu da doc hay chua
    public $FromTime ; // thoi gian nhan thu


    public function __construct($FromId,$Content = NULL)
    {
        $this->Content = $Content ;
        $this->FromId = intval($FromId) ;
        $this->FromTime = $_SERVER['REQUEST_TIME'];
        $this->IsRead = false ; // Chua doc
    }

    public function read()
    {
        $this->IsRead = true;
    }
    
    // kiem tra xem thu da qua gioi han chua
    
    public function isExpire($time)
    {
        
        return ($_SERVER['REQUEST_TIME'] > ($this->FromTime + $time)) ;
    }

}

?>