<?php
class KeepLogin extends Model {
    //Declare variable    
    private $LastLoginTime;
    private $NumCurrentLogin = 0;
    private $Status = array(); // struct index, status =(0: can't receive, 1: can but recieve yet, 2: recieved )
    
    // Init method      
    public function __construct($uId)
    {
        parent::__construct($uId);        
    }
    //
    static public function init($uId)
    {
        $res = new KeepLogin($uId);
        
        return $res;
    }

    static public function getById($uId)
    {
        //DataProvider::delete($uId,__CLASS__);
        $oKeepLogin = DataProvider::get($uId,__CLASS__);
        if(empty($oKeepLogin))
        {            
            $oKeepLogin = self::init($uId);
        }        
        return $oKeepLogin;    
    }
    
    // calulator and update keep login info after first login in day    
    public function updateKeepLogin(){
        if(Event::checkEventCondition(EventType::KeepLogin)) {            
            $Today =  $_SERVER['REQUEST_TIME'];                
            $NumCanLogin = $this->getNumCanLogin();
            $ConfigGift = Common::getConfig("KeepLogin_Gift");
            $NumConfigGift = count($ConfigGift);
            if($NumCanLogin <= $NumConfigGift){
                if(count($this->Status) <1) { // login first          
                    $this->NumCurrentLogin = 1;     
                    $this->Status[$this->NumCurrentLogin] = 1;     
                    
                } else { // else                    
                    $NumDay = $this->getNumberDays($this->LastLoginTime,$Today);                    
                    if($NumDay >1){ //
                        
                        $this->NumCurrentLogin += 1;                           
                        if($this->NumCurrentLogin> $NumCanLogin) $this->NumCurrentLogin = $NumCanLogin;                         
                        if(!isset($this->Status[$this->NumCurrentLogin]) ) {
                            $this->Status[$this->NumCurrentLogin] = 1;                    
                        }                                
                    } 
                }        
                $this->LastLoginTime = $Today;
            } else {
                $this->NumCurrentLogin = 0;         
            }            
            $this->save();
        }
    }
    
    // Calulator number days of two time
    public function getNumberDays($FirstTime, $EndTime){
        // convert number seconds to start day
        $FirstTime = date("m-d-Y",$FirstTime);
        $parseFirstTime = explode('-',$FirstTime);        
        $FirstTime = mktime(0,0,0,$parseFirstTime[0],$parseFirstTime[1],$parseFirstTime[2]);
        $EndTime = date("m-d-Y",$EndTime);        
        $parseEndTime = explode('-',$EndTime);        
        $EndTime = mktime(0,0,0,$parseEndTime[0],$parseEndTime[1],$parseEndTime[2]);        
        //
        return ($EndTime - $FirstTime)/86400 + 1;
    }
    
    //return number login of user
    public function getNumCurrentLogin(){
        return $this->NumCurrentLogin;
    }
    // set value for NumCurrentLogin property
    public function setNumCurrentLogin($numCurrentLogin){
        $this->NumCurrentLogin = $numCurrentLogin;
    }
        
    // return size of Status array
    public function getSizeStatus(){
        return count($this->Status);
    }
    // set value of Status 
    public function setStatus($dayIndex, $value){        
        $this->Status[$dayIndex] = $value;        
    }

    public function unsetStatus($dayIndex){        
        unset($this->Status[$dayIndex]);        
    }

    // get value of Status
    public function getStatus($dayIndex) {
        if(isset($this->Status[$dayIndex])) 
            return $this->Status[$dayIndex];
        return 0;                
    }
    
        
    // return number days user can login
    public function getNumCanLogin(){
        $Today   =  $_SERVER['REQUEST_TIME'];
        $Eventconf =  Common::getConfig('Event', EventType::KeepLogin);        
        $Begin     =  $Eventconf['BeginTime'];
        $Expired   =  $Eventconf['ExpireTime'];        
        $NumCanLogin = $this->getNumberDays($Begin,$Today);
        $ConfigGift = Common::getConfig("KeepLogin_Gift");
        $NumConfigGift = count($ConfigGift);
        if($NumCanLogin > $NumConfigGift){
            $NumCanLogin =  $NumConfigGift;
        }
        return $NumCanLogin;        
    }
    
    // return keep login info 
    public function getKeepLogin(){        
        $Ret = array();
        $Ret["LastLoginTime"] = $this->LastLoginTime;
        $Ret["NumCurrentLogin"] = $this->NumCurrentLogin;
        $Ret["NumCanLogin"] = $this->getNumCanLogin();
        $Ret["Status"] = $this->Status;        
        return $Ret;
    }
    
    //setLastLoginTime
    public function setLastLoginTime($time){
        $this->LastLoginTime = $time;
        $this->save();
    }
    // resetDataKeepLogin
    public function resetDataKeepLogin(){
        $this->LastLoginTime = 0;
        $this->NumCurrentLogin =0;
        $this->Status = array();        
    }
            
}  
?>
