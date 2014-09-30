<?php

/**
*   @author hieupt
*   Energy Machine
*   01/06/2011
*/


class EnergyMachine{
      
    public $Id;
    public $StartTime;
    public $ExpiredTime;
    public $IsExpired;
    public $Type = 1;
    
    public function __construct($autoId, $type = 1){
      $this->Id = $autoId;
      $this->StartTime = 0;
      $this->ExpiredTime = 0;
      $this->IsExpired = true;
      $this->Type = $type;
    }      
  
    /**
    * use petrol for energyMachine
    * 
    * @type : type in config
    */
  
    public function usePetrol($type){
        $oStore = Store::getById(Controller::$uId);
        if (!$oStore->useItem('Petrol',$type,1))
          return false;
        
        $conf_petrol = Common::getConfig('Petrol',$type);
        
        $oUser = User::getById(Controller::$uId); 
        
        if ($this->IsExpired==true)
        {
            $this->IsExpired = false; 
            $this->StartTime = $_SERVER['REQUEST_TIME'];
            $this->ExpiredTime = $conf_petrol['ExpiredTime']*24*60*60;
            
            $ener = $oUser->getCurrentEnergy();
            
            $conf_MaxEnergy = & Common::getConfig('MaxEnergy'); 
            
            if ($ener > $conf_MaxEnergy[$oUser->Level])
            {
                $oUser->Energy = $conf_MaxEnergy[$oUser->Level];
                $oUser->LastEnergyTime = $_SERVER['REQUEST_TIME'];
            }
        }
        else
        {
            $this->ExpiredTime += $conf_petrol['ExpiredTime']*24*60*60;    
        }
      
        
        $conf_param = Common::getConfig('EnergyMachine');
        $oUser->bonusEnergy = $conf_param[$this->Type]['Buff'];
        
        
        $oUser->save();
        $oStore->save();
        
        return true;
    }
    
    public function isExpired(){
        if ($this->IsExpired==true)
          return true;
        $deltaTime = Common::getParam('DeltaTime');
        if ($this->StartTime+$this->ExpiredTime-$deltaTime < $_SERVER['REQUEST_TIME']){
          return true;
        }
        return false;
    }
  
  
}
?>
