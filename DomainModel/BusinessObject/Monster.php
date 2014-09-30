<?php
  class Monster
  {
      public $Id ;
      public $Rank ;
      public $Element ;
      public $Vitality ;
      Public $Damage ;
      public $Defence ;
      public $FishTypeId ;
      public $RecipeType = array();
      public $Equipment  = array();
      public $Critical ;
      public $Health ;
      public $IsBoss ;
      
      
      public function __Construct($id,$element = 0,$Vitality = 0,$dam = 0,$defend = 0,$Critical,$health,$equiment = array(),$recipe = array(),$isBoss = false)
      {
          $this->Id         = $id ;
          $this->Element    = $element ;
          $this->Vitality   = $Vitality ;
          $this->Damage        = $dam ;
          $this->Defence     = $defend ;
          $this->Critical   = $Critical;  
          $this->Health     = $health ;
          $this->IsBoss     = $isBoss ;
      
          
          if(!empty($recipe))
          {
            $recipe_conf = Common::getConfig('MixFormula',$recipe['ItemType'],$recipe['ItemId']);
            $this->Rank = $recipe_conf['Rank'];
            $this->FishTypeId = $recipe_conf['FishTypeId'];
            $this->RecipeType = $recipe ;
          }
          
          $this->Equipment  = $equiment; 
       
      }
      
     
      /**
      * get buff of soldier; include buffItem, gem, equipment vs base
      * 
      */
      public function getIndex()
      {
          $arrIndex = array(); 
          
          $arrIndex['Damage']   = $this->Damage;
          $arrIndex['Defence']  = $this->Defence;
          $arrIndex['Critical'] = $this->Critical;
          $arrIndex['Vitality'] = $this->Vitality;

          return $arrIndex;
      }
      
      public function getIndexBase()
      {
          $arrIndex = array(); 
          
          $arrIndex['Damage']   = $this->Damage;
          $arrIndex['Defence']  = $this->Defence;
          $arrIndex['Critical'] = $this->Critical;
          $arrIndex['Vitality'] = $this->Vitality;

          return $arrIndex;
      }
      
      public function getHealth()
      {
          return $this->Health;
      }
      
      /**
      * get max Health
      */
      public function getMaxHealth()
      {
            return $this->Health;;
      }
  }
?>
