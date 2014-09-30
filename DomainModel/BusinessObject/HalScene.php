<?php
  class HalScene
  {
      public $CreatedTime;
      public $Map;
      public $Reward;
      public $BabyChip;
      public $UnlockedMap;
      
      public function __construct()
      {
          $this->CreatedTime = 0;
          $this->Map = array();
          $this->Reward = array();
          $this->BabyChip = array(EventHal2012::SIZE - 1, EventHal2012::SIZE - 1);
          $this->UnlockedMap = true;
      }
      
      public function resetScene($created_time, $map, $baby_pos)
      {
          $this->CreatedTime = $created_time;
          $this->Map = $map;
          $this->BabyChip = $baby_pos;
          $this->UnlockedMap = false;
          $this->Reward = array();          
      }
      
      public function changeStateItem($x, $y)
      {
          if(!isset($this->Map[$x][$y]))
            return false;
          $state = EventHal2012::LOCK_STATE;
          switch($this->Map[$x][$y][0])     // state
          {
              case EventHal2012::FREEZE_STATE:
                $state = EventHal2012::LOCK_STATE;
                break;
              case EventHal2012::LOCK_STATE:
                $state = EventHal2012::UNLOCK_STATE;
                break;
              default:
                return false;                
          }
          if($state == EventHal2012::UNLOCK_STATE)
          {
              $this->BabyChip[0] = $x;
              $this->BabyChip[1] = $y;
          }
          
          if($this->Map[$x][$y][1] == EventHal2012::ENDMAP_STEP)
          {
                $this->UnlockedMap = true;                  
                $unlocked = true;
          }
          else $unlocked = false;
                
          $this->Map[$x][$y][0] = $state;
          
          return array('changed' => $state, 'unlocked' => $unlocked);
      }
           
      public function addRegard($gift)
      {
          $this->Reward['Normal'] = array_merge((array)$this->Reward['Normal'], (array)$gift['Normal']);
          $this->Reward['Equipment'] = array_merge((array)$this->Reward['Equipment'], (array)$gift['Equipment']);
      }
            
  }
?>
