<?php
class HammerMan extends Model {
    //Property
    public $Percent = array();        
    public $makeOption = array(); // array of struct($ItemId, $Num)
    public $tempBonus = array(); // temp save options when makeOption
    
    public $tempEquip = null;
    
    public $TotalPercent = 0;
    
    // Init method      
    public function __construct($uId)
    {
        parent::__construct($uId);        
    }
    //
    static public function init($uId)
    {
        $res = new HammerMan($uId);
        return $res;
    }

    static public function getById($uId)
    {
        //DataProvider::delete($uId, 'HammerMan');
        $oHammerMan = DataProvider::get($uId, 'HammerMan');
        if(empty($oHammerMan))
        {            
            $oHammerMan = self::init($uId);
        }        
        return $oHammerMan;    
    }
    
    public function getEquip(){
        return $this->tempEquip;
    }
    
    public function setEquip($oEquip){
        $this->tempEquip = $oEquip;
    }
    
    public function getPercent(){  
        return $this->Percent;
    }    

    public function getPercentDetail($ItemType, $Rank,  $Color, $Element =0){  
        if(isset($this->Percent[$ItemType][$Rank][$Color][$Element]) && intval($this->Percent[$ItemType][$Rank][$Color][$Element])>0 )             
            return intval($this->Percent[$ItemType][$Rank][$Color][$Element]);
        return 0;    
    }    
    
    public function addPercent($ItemType, $Rank, $Color, $Element =0, $amount= 0)
    {
        if ($amount < 0)
            return false;
        if(isset($this->Percent[$ItemType][$Rank][$Color][$Element]) && intval($this->Percent[$ItemType][$Rank][$Color][$Element])>0 ) {
            $this->Percent[$ItemType][$Rank][$Color][$Element] += $amount;    
        } else {
            $this->Percent[$ItemType][$Rank][$Color][$Element] = $amount;    
        } 
        return true;          
    }
    
    public function usePercent($ItemType, $Rank, $Color, $Element =0, $amount=0)
    {
        $amount = intval($amount);
        if ($amount <= 0)
            return false;
        if(isset($this->Percent[$ItemType][$Rank][$Color][$Element]) && intval($this->Percent[$ItemType][$Rank][$Color][$Element])>0 ) {
            if ($this->Percent[$ItemType][$Rank][$Color][$Element] < $amount)
                return false;
            $this->Percent[$ItemType][$Rank][$Color][$Element] -= $amount;
            return true;                  
        }    
        return false;
    }
    
    public function getTotalPercent() {
        return $this->TotalPercent;
    }
    
    public function addTotalPercent($amount) {
        $amount = intval($amount);
        if ($amount <= 0)
            return false;
        if(isset($this->TotalPercent) && intval($this->TotalPercent) > 0 ) {
            $this->TotalPercent += $amount;
        } else {
            $this->TotalPercent = $amount;
        }
        return true;
    }
    
    public function useTotalPercent($amount) {
        $amount = intval($amount);
        if ($amount <= 0)
            return false;
        if(isset($this->TotalPercent) && intval($this->TotalPercent) > 0 ) {
            if($this->TotalPercent < $amount) 
                return false;
            $this->TotalPercent -= $amount;    
            return true;
        }
        return false;
    }
    
    public function convertPoint() {
        $TotalPercent = 0;
        $a = $this->Percent;
       
        if(is_array($a) && count($a) > 0) {
            foreach($a as $a1) {
                foreach($a1 as $a2) {
                    foreach($a2 as $a3) {
                        foreach($a3 as $a4) {
                            if(isset($a4['Percent']) && intval($a4['Percent']) > 0)
                                $TotalPercent += intval($a4['Percent']);                            
                        }                        
                    }
                }
            }                    
        }       
        $this->addTotalPercent($TotalPercent);        
    }    
        
    public function setTempBonus($bonus= array()){
        $this->tempBonus = $bonus;    
    }

    public function getTempBonus(){
        return $this->tempBonus;    
    }
    
    public function getMakeOption(){
        return $this->makeOption;    
    }
    
    public function addMakeOption($ItemId, $Num=1){
        if(isset($this->makeOption[$ItemId])){
           $this->makeOption[$ItemId] += $Num;
        }  else {
           $this->makeOption[$ItemId] = $Num;
        }      
    }
    
  
}
?>
