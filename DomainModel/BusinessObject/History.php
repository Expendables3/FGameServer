<?php
class History 
{
	public $FriendId;		// int      Id cua nguoi du?c thuc hien Action
	public $Time;		// bool 	Thoi gian cua log
    public $Log = array() ; // Noi dung Log
    public $Self = false ;

    public function __construct($FriendId,$Content,$time = null,$self = false)
    {
    	$this->FriendId = $FriendId ;
        if($time == null)
            $this->Time = $_SERVER['REQUEST_TIME'];
        else $this->Time = $time ;
    	$this->Log = $Content ;
        $this->Self = $self ;
    }

}

?>