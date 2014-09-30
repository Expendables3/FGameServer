<?php
  class Seal
  {
      public $Id;
      public $Type;
      public $Element;
      public $Rank;
      public $Color;
      public $Level;
      public $IsUsed;
      public $Damage;
      public $Defence;
      public $Critical;
      public $Vitality;
      public $Durability;
      public $InUse;
      
      public function __construct($type, $id, $rank, $color)
      {
          $this->Id = $id;
          $this->Type = $type;
          $this->Element = 0;
          $this->Rank = $rank;
          $this->Color = $color;
          $this->Level = 0;
          $this->IsUsed = false;
          $this->InUse = false;
          $this->Damage = 0;
          $this->Defence = 0;
          $this->Critical = 0;
          $this->Vitality = 0;
          $this->Durability = 100;
      }
      
      public function updateSealOption($level, $optionConf)
      {
          $this->Level = $level;
          foreach ($optionConf as $char => $value)
          {
              if(property_exists($this, $char))
                $this->$char += $value;
          }
      }
      
      public function isExpired(){
        return false;           
      }
     
      public function getIndex()
    {
        $bonus = array();
        $bonus['Damage'] += $this->Damage;                   
        $bonus['Critical'] += $this->Critical;
        $bonus['Vitality'] += $this->Vitality;
        $bonus['Defence'] += $this->Defence;
       
        foreach($this->bonus as $id => $oIndex)
        {
            foreach($oIndex as $name => $value)
            {
                $bonus[$name] += $value;
            }
        }    
        return $bonus;
    }
    
    public function disableAllLevelJadeSeal()
    {
        $this->Critical = 0;
        $this->Damage = 0;
        $this->Defence = 0;
        $this->Vitality = 0;
        $this->Level = 0;
    } 
  }
?>
