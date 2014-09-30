<?php
class AccumulationPoint extends Model {
    // Declaration property
    private $Point=0; // Point of user
    
    public function __construct($uId)
    {
        parent::__construct($uId);        
    }
    //
    static public function init($uId)
    {
        $res = new AccumulationPoint($uId);
        return $res;
    }

    static public function getById($uId)
    {
        //DataProvider::delete($uId, 'AccumulationPoint');
        $oAccumulationPoint = DataProvider::get($uId, 'AccumulationPoint');
        if(empty($oAccumulationPoint))
        {            
            $oAccumulationPoint = self::init($uId);
        }        
        return $oAccumulationPoint;    
    }
    
    public function checkTime(){
        $conf = Common::getConfig('Param','AccumulationPoint');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        if($now < $StartTime || $now > $EndTime ){              
            $this->Point = 0;
            $this->save();      
        }
    }
    
    // The function updatePoint
    // Update Point when the number of Xu push in game has changed
    public function updatePoint($totalxu){
        //
        $conf = Common::getConfig('Param','AccumulationPoint');
        $StartTime = $conf['StartTime'];
        $EndTime = $conf['EndTime'];
        $now = $_SERVER['REQUEST_TIME'];
        $oUser = User::getById(Controller::$uId);
        
        if($now < $StartTime || $now > $EndTime || count($oUser->FirstAddXuGift)<6 ){              
            $this->Point = 0;            
        }   else if($totalxu > 0 ) {
            $this->Point += $totalxu;
        }      
        $this->save();                   
    }
    
    public function getPoint()
    {
        return $this->Point;
    }
    
    public function setPoint($point){
        $this->Point = $point;
    }
    
    public function usePoint($amount){
        $amount = intval($amount);
        if ($amount < 1)
            return false;
        if ($this->Point < $amount)
            return false;
        $this->Point -= $amount;
        return true;        
    }
                
    
      
}
?>
