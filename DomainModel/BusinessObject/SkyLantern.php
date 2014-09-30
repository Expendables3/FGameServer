<?php
  class SkyLantern
  {
      public $X;
      public $XL;
      public $Y;
      public $Healthy;
      public $asSpaceScraft;
      public $isProtector;
      public $isMagnetic;
      public $NumUse_Protector;
      public $NumUse_Speeduper;
      public $NumUse_Magnetic;
      
      public function __construct()
      {          
      }
      public function init()
      {          
          $this->X = 0;
          $this->Y = 2;
          $this->XL = 0;
          $this->Healthy = 3; 
          $this->asSpaceScraft = false;
          $this->isMagnetic = false;
          $this->isProtector = false;
          $this->NumUse_Magnetic = 0;
          $this->NumUse_Protector = 0;
          $this->NumUse_Speeduper = 0;
      }
      
      public function addChar($type, $num = 3)
      {          
          $proper = 'NumUse_'.$type;
          $char = 'is'.$type;
          if(($this->$proper + $num) < 0)
          {
              $this->$char = false;
              return;
          }
          $this->$char = true;                                    
          $this->$proper += $num;
      }   
  }
?>
