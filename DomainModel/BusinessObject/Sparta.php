<?php
Class Sparta{
  public $Id;
  public $StartTime;
  public $ExpiredDay; // loai 7 ngay || loai 30 ngay
  public $Option;
  public $isExpried ; // True con han , False het han 
  public $RebornTime;
  public $LastTimeGetGift = 0;
  public $Type;
  public $Material = array();
  public $Position = array();
  
  public function __construct($autoId,$option, $expired, $type = 'Sparta'){
  	  $this->Id = $autoId;
      $this->Option = $option;
      $this->StartTime = 0;
      $this->ExpiredDay = $expired;
      $this->isExpried = true ;
      $this->RebornTime = 0;
      $this->Type = $type;
      $this->Position = array('X'=>0,'Y'=>0,'Z'=>0) ;
  }
  
  public function expire()
  {
      if ($_SERVER['REQUEST_TIME'] > ($this->StartTime + $this->ExpiredDay*24*3600))
        {
            $this->isExpried  = false ;
            return true ;  
        }
      return false ;
  }
  
  public function reborn($time)
  {
      if ($this->isExpried == true)
        return false;
        
      if ($this->RebornTime >= Common::getParam('MaxReborn'))
        return false;
      
      $this->StartTime = $_SERVER['REQUEST_TIME'];
      $this->ExpiredDay = $time;
      $this->isExpried = true;
      $this->RebornTime++;
      
      return true;
  }
  
  public function getGift($Type)
  {
      $conf_gift = Common::getConfig('SpartaGift',$Type);
      if (!is_array($conf_gift))
        return false;

      switch($Type)  
      {
          case Type::Firework:
              $conf_minTime = Common::getConfig('Param','TimeGiftSparta',Type::Firework);
              if ($_SERVER['REQUEST_TIME']-$this->LastTimeGetGift < $conf_minTime)     
                return false;
              
              $arrGift = array();
              $arrGift = $conf_gift['Sure'];
              break;
              
          case Type::NoelFish:
              $conf_minTime = Common::getConfig('Param','TimeGiftSparta',Type::NoelFish);
              if ($_SERVER['REQUEST_TIME']-$this->LastTimeGetGift < $conf_minTime)     
                return false;
              
              $arrGift = array();
              $arrGift = $conf_gift['Sure'];
              $stock = array();
              $sock['ItemType'] = Type::SockExchange;
              $sock['ItemId'] = 1;
              $sock['Num'] = $arrGift[2]['Num'];
              $arrGift[] = $sock;

              
              break;
          
          default:
              $arrGift = array();
              break;
      }
      
      $this->LastTimeGetGift = $_SERVER['REQUEST_TIME'];
      return $arrGift;
  }
  
  public function addMaterial($MaterialId)
  {
      $this->Material[] = $MaterialId ;
  }
  
  public function updateOption($option,$isBuff = true)
   {
       if(!is_array($option))
            return false;
       foreach($option as $key => $value)
       {
           if($value < 1) continue ;
           if($isBuff)
            $this->Option[$key] += $value ;
           else
            {
                $this->Option[$key] -= $value ;
                if($this->Option[$key] < 0 )
                    $this->Option[$key] = 0 ;
            }
       }
       return true ;
   }
   
   public function updatePosition($Position)
   {
      /* if(!$this->isExpried) // het han 
       {*/
          $this->Position['X'] = $Position['X'];
          $this->Position['Y'] = $Position['Y'];
          $this->Position['Z'] = $Position['Z'];
          return true ;
/*       }
       return false ;*/
   }
   
   public function updateId($autoId)            
    {
        $this->Id = $autoId;
    }
  
}
?>
