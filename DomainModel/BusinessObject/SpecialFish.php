<?php
/*
 * class ca dac biet 
 * date : 23_2_2011
 */
class SpecialFish extends Fish
{
    public $LastGetLevelGift ;         // level da nhan qua
    public $RateOption ;            // ti le % option
    public $LevelUpGift = array();    // qua tang khi len level 
    
    function __construct($Id, $fishTypeId = 1, $sex = 1,$rateOption = null,$color = 0)
    {
        $this->LastGetLevelGift = 0 ;
        $this->RateOption = $rateOption ;
        // tao qua cho ca 
        parent :: __construct($Id, $fishTypeId, $sex,$color);
        $this->FishType = FishType::SPECIAL_FISH ; 
        
    }
    
    // ham thuc hien viec kiem tra ca co du dieu kien nhan qua khong
    public function checkConditionGetGift($userLevel)
    {
        $confParam = Common::getParam();
        $subLevel = $this->Level - $userLevel ; 
        if ( ($this->LastGetLevelGift > $this->Level -1 ) || $subLevel >= $confParam[PARAM::FishOverLevel] )
        {
            return false ;
        }
        
        if($this->getGrowingPeriod() < FishPeriod::OVER_MATURE)
        {
            return false ;
        }
        
        return true ;
    }
    
   public function checkConditionCreateGift($userLevel)
    {
        $confParam = Common::getParam();
        $subLevel = $this->Level - $userLevel ; 
        if ( ($this->LastGetLevelGift > $this->Level -1 ) || $subLevel >= $confParam[PARAM::FishOverLevel] )
        {
            return false ;
        }        
        return true ;
    }
    
     // ham thuc hien viec lay qua tu config -> database
    function createLevelUpGift()
    {
        $this->LevelUpGift =  $this->randomGiftForFish();
        return $this->LevelUpGift  ;
    }
    
    // ham thuc hien viec random qua tang cua level tiep theo cho ca 
    public function randomGiftForFish()
    {
        $conf_fish = Common::getConfig('Fish',$this->FishTypeId); 
        
        for ($i = 91; $i> 1 ;$i=$i-10)
        {
            if($i < 21)
            {
                $Level = 1 ;
                break ;
            }
            
            if($conf_fish['LevelRequire'] >= $i)
            {
                $Level = $i;
                break ;
            }
        }
                
        $confRate = Common::getConfig('RateRandomFishGift',$Level);
        if(!is_array($confRate))
        { 
          return array();
        }            
        $rate_random = Common::getConfig('General','rateRandomFishLevelGift');
        
        $key = Common::randomIndex($rate_random) ;
        $arrGiftList = array() ;
               
        // exp 
        $arrGiftList[0][Type::ItemType]= Type::Exp ;  
        $arrGiftList[0][Type::ItemId]= '';
        $arrGiftList[0][Type::Num]= $conf_fish['Exp'];
        
            
        $arrGiftList[1][Type::ItemType]= $confRate[$key]['ItemType'] ;   
        $arrGiftList[1][Type::ItemId]= $confRate[$key]['ItemId'] ;
        
        if($arrGiftList[1][Type::ItemType] == Type::Material)
        {
            if(empty($confRate[$key]['Num']))
            {
                $arrGiftList[1][Type::Num] = $conf_fish['LevelRequire'] ;
            }
            else
            {
                $arrGiftList[1][Type::Num] = $confRate[$key]['Num'];
            }
        }
        else if ($arrGiftList[1][Type::ItemType] == Type::EnergyItem) 
        {
            $numoption = $this->RateOption['Money'] + $this->RateOption['Exp']*1.1 + $this->RateOption['Time']*1.5 + $this->RateOption['MixSpecial'] + $this->RateOption['MixFish']*1.1; 
            if($key == 4)
            {
                $arrGiftList[1][Type::Num]= round($numoption*0.6); 
            }
            else
            {
                $arrGiftList[1][Type::Num]= round($numoption*0.9); 
            }
        }  
        return $arrGiftList ;
    }
        
    // ham thuc hien tra qua tang khi len level cua ca
    public function getLevelUpGift($option,$FishTypeId)
    {
      $confFish = Common::getConfig('Fish',$FishTypeId);
      $this->LevelUpGift[0]['Num'] = $confFish['Exp'] ;
      if(isset($option['Exp']))
      {
          
         $this->LevelUpGift[0]['Num'] *= (1+ $option['Exp']/100) ;    
         $this->LevelUpGift[0]['Num'] = ceil($this->LevelUpGift[0]['Num']) ;
      }
      return $this->LevelUpGift;
    }
    
    //ham update level va start time cho ca khi duoc nhan
    public function updateLevelUpGift()
    {
        $this->LastGetLevelGift = $this->Level;
        $this->LevelUpGift = array();
        
        $this->Level++;

        $conf_fish = Common::getConfig('Fish',$this->FishTypeId);
        $TimeFood = $this->FeedAmount*$conf_fish['EatedSpeed'];
        $TotalFoodEat = $_SERVER['REQUEST_TIME'] - $this->StartTime ;
        $FoodExist = $TimeFood - $TotalFoodEat ;
        if ($FoodExist < 0)
        {
            $this->FeedAmount = 0 ;
        }
        else 
        {
            $this->FeedAmount = $FoodExist/$conf_fish['EatedSpeed'];
        }
        
        // update lai thoi gian phat trien cua ca 
        $this->StartTime = $_SERVER['REQUEST_TIME'] ;
        
		    // reset lai so tien roi ra va cac thong so khac
		    if ($this->FishType != FishType::NORMAL_FISH)
        {
          $this->TotalBalloon += $conf_fish['TotalBalloon'];
        } 
   }
        
  public function resetOption()
    {
      $this->RateOption = array();
      $this->FishType = FishType::NORMAL_FISH ;
	  $this->LevelUpGift = array() ;
      $this->Material = array();
    }
   /**
   * update RateOption for Fish
   * 
   * @param $option : array 
   * @param $isBuff : boolen
   */
    
   public function updateOption($option,$isBuff = true)
   {
       if(!is_array($option))
            return false;
       foreach($option as $key => $value)
       {
           if($value < 1) continue ;
           if($isBuff)
            $this->RateOption[$key] += $value ;
           else
            {
                $this->RateOption[$key] -= $value ;
                if($this->RateOption[$key] < 0 )
                    $this->RateOption[$key] = 0 ;
            }
       }
       return true ;
   }
    
}
