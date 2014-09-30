<?php
class VipBox extends Model {
    private $NumOpen = 0;
    private $NumKey = 0;
    
    // Init method      
    public function __construct($uId)
    {
        parent::__construct($uId);        
    }
    //
    static public function init($uId)
    {
        $res = new VipBox($uId);
        return $res;
    }

    static public function getById($uId)
    {
        //DataProvider::delete($uId, 'VipBox');
        $oVipBox = DataProvider::get($uId, 'VipBox');
        if(empty($oVipBox))
        {            
            $oVipBox = self::init($uId);
        }        
        return $oVipBox;    
    }
    
    public function getNumOpen() {
        return $this->NumOpen;
    }
    
    public function addNumOpen($Num) {
        if(!isset($this->NumOpen)) {
            $this->NumOpen = $Num;
        } else {
            $this->NumOpen += $Num;
        }
    }

    public function setNumOpen($Num) {
        $this->NumOpen = $Num;
    }
    
    public function updateFirstTimeOfDay() {
        $this->NumOpen = 0;
    }
    
    public function getNumKey(){
        return $this->NumKey;
    }

    public function setNumKey($Num){
        $this->NumKey = $Num;
    }
    
    public function addNumKey($Num) {
        if(!isset($this->NumKey)) {
            $this->NumKey = $Num;
        } else {
            $this->NumKey += $Num;
        }        
    }
            
    public function useNumKey($Num) {
        if(!isset($this->NumKey)) 
            return false;
        $Num = intval($Num);    
        if($Num <= 0) 
            return false;
        if($this->NumKey < $Num)
            return false;
        $this->NumKey -= $Num;
        return true;        
    } 
    
    public function getVipBox() {
        $vipBoxInfo = array();
        $vipBoxInfo['NumOpen'] = $this->NumOpen;
        $vipBoxInfo['NumKey'] = $this->NumKey;
        
        return $vipBoxInfo;
    }
}  
?>
