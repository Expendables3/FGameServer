<?php
  class SmashEgg extends Model {
        private $Hammer = array();
        private $Egg = array();  // time, status = 0: Not yet smash egg 1: Smashed, not receive bonus
        //private $Bonus = array();  // saved Bonus when Smash Egg
        private $Slot = array(); // saved position of Quartz on Soldier   
        
        private $IsPurpleHammer = 0;     
        
        private $NumPurpleHammerInEvent = 0;         
        private $Num6Star = 0;   // show in Admin tool but not change
        private $NumView6Star = 0; // show in admin tool and can change
        private $Quota6Star = array(); // after day, it will reset value 

        public function __construct($uId)
        {
            parent::__construct($uId);
            $this->Egg = array( 'WhiteEgg'=>array('Time'=>0,'SmashNum'=>0),
                                'GreenEgg'=>array('Time'=>0,'SmashNum'=>0),
                                'YellowEgg'=>array('Time'=>0,'SmashNum'=>0),
                                'PurpleEgg'=>array('Time'=>0,'SmashNum'=>0) );                    
        }
        //
        static public function init($uId)
        {
            $res = new SmashEgg($uId);            
            return $res;
        }

        static public function getById($uId)
        {
            //DataProvider::delete($uId, 'SmashEgg');
            $oSmashEgg = DataProvider::get($uId, 'SmashEgg');
            if(empty($oSmashEgg))
            {            
                $oSmashEgg = self::init($uId);
            }        
            return $oSmashEgg;    
        }
        //IsPurpleHammer 
        public function setIsPurpleHammer($Value){
            if($this->IsPurpleHammer>0) return false; 
                $this->IsPurpleHammer = $Value;
            return true;
        }
        public function getIsPurpleHammer() {
            return $this->IsPurpleHammer;
        }
        
        // addSlot
        public function addSlot($SoldierId, $SlotId, $QuartzType, $Id){                        
            $this->Slot[$SoldierId][$SlotId] = array('QuartzType'=>$QuartzType,'Id'=>$Id);              
        }
        // removeSlot
        public function removeSlot($SoldierId, $SlotId){
            if(!isset($this->Slot[$SoldierId][$SlotId]) ) return false;
            unset($this->Slot[$SoldierId][$SlotId]);
            return true;
        }                
        //check Slot exist 
        public function checkSlot($SoldierId, $SlotId){
            if(!isset($this->Slot[$SoldierId][$SlotId]) || !is_array($this->Slot[$SoldierId][$SlotId]) ) return true;
            return false;
        } 
        // get Slot
        public function getSlot($SoldierId, $SlotId){
            return $this->Slot[$SoldierId][$SlotId];
        }
        //dem so luong slot dang su dung
        public function countTotalSlot($SoldierId)
        {
             return count($this->Slot[$SoldierId]); 
        }

        // update slot
        public function removeSoldierSlot($SoldierId){
            unset($this->Slot[$SoldierId]); 
        }

        public function getSoldierSlot($SoldierId){
            if(!isset($this->Slot[$SoldierId]) ) return null;
            return $this->Slot[$SoldierId]; 
        }
        
        
        //updateSlot - remove all slot have Slodier not exist 
        public function updateSlot($arrSoldierId){
            if(count($this->Slot)>=1){
                foreach($this->Slot as $SoldierId => $slo){
                    if(!in_array($SoldierId, $arrSoldierId)) {
                        unset($this->Slot[$SoldierId]);
                    }
                }                        
            }
        }
        
        
        //resetSmashNum
        public function resetSmashNum(){
             $this->Egg['WhiteEgg']['SmashNum'] = 0;
             $this->Egg['GreenEgg']['SmashNum'] = 0;
             $this->Egg['YellowEgg']['SmashNum'] = 0;             
             $this->save();
        }
        
        //add hammer in store     
        public function addHammer($HammerType,$HammerId,$num)
        {
            if ($num <=0 || empty($HammerType)||empty($HammerId)) 
                return false ;
            $this->Hammer[$HammerType][$HammerId] += $num ;
            return true ;
        }
        //use hammer in the store     
        public function useHammer($HammerType,$HammerId,$num)
        {
            $num = intval($num);
            if($num < 1 ) return false ;
            if($this->Hammer[$HammerType][$HammerId] < $num )
                return false ;
            $this->Hammer[$HammerType][$HammerId] -= $num ;
            if ($this->Hammer[$HammerType][$HammerId] <=0){
                unset($this->Hammer[$HammerType][$HammerId]);
            }
            return true ;
        }        
        //getHammer
        public function getHammer($HammerType,$HammerId) {
            if(!isset($this->Hammer[$HammerType][$HammerId]) ) {
                return 0;
            } else {
                return $this->Hammer[$HammerType][$HammerId];
            }    
        }
        
        // getSmashEggInfo
        public function getSmashEggInfo(){
            return array( 'Hammer'=>$this->Hammer,
                          'Egg' => $this->Egg,                          
                          'Slot'=>$this->Slot                          
            );
        }         
        public function resetHammer() {
            $this->Hammer = array();
        }       
        //update Egg
        public function getEgg($EggType) {
            return $this->Egg[$EggType];
        }
        public function updateEgg($EggType, $UpdateEgg) {
            $this->Egg[$EggType] = $UpdateEgg; 
        }

        public function updateFirstTimeOfDay() {
            $DateNow = date('Ymd');
            $DateKey = DataRunTime::get('DateKey', true);
            if($DateNow != $DateKey) {
                DataRunTime::set('DateKey', $DateNow, true);
                DataRunTime::set('Num_6', 0, true);
                DataRunTime::set('Num_12', 0, true);
                DataRunTime::set('Num_18', 0, true);
                DataRunTime::set('Num_24', 0, true);
            }
        }
                
        public function getNumPurpleHammerInEvent() {
            return $this->NumPurpleHammerInEvent;
        }
        
        public function addNumPurpleHammerInEvent($Num) {
            $this->NumPurpleHammerInEvent += $Num;
        }
        
        public function setNumPurpleHammerInEvent($Num) {
            $this->NumPurpleHammerInEvent = $Num;
        }
        
  }
?>
