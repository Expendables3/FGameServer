<?php
class PearFlower
{
    public $MapId = 0 ;
    public $Position = array() ;
    public $Dice = array() ;
    public $Question ;
    public $Revards = array() ;
    public $History = array();
    public $CreateTime ;
    public $RoadState ;
    public $MazeKeyInfo = array();
    public $TeleportNum = 0 ;

    public function __construct($MapId = 1)    
    {
        $conf_map = Common::getConfig('Map',$MapId);
        if(empty($conf_map)) return false ;
        $this->MapId = $MapId ; 
        foreach($conf_map as $y => $x_arr)
        {
            foreach($x_arr as $x => $cellStatus)
            {
                if($cellStatus == CellStatus::CELL_START)
                {
                     $this->Position['Y']  = $y;
                     $this->Position['X']  = $x;
                }
                continue ;
            }
            
        }
           
        $this->Dice['Arrow']    = 0;   
        $this->Dice['Num']      = 0;   
        $this->Question        = 0;   
        $this->Revards          = array();
        $this->CreateTime       = $_SERVER['REQUEST_TIME']; 
        $this->RoadState        =  RoadState::NORMAL ;
        $this->MazeKeyInfo['Num'] = 0 ;
        $this->MazeKeyInfo['Num2'] = 0 ;
        $this->MazeKeyInfo['OpenTreasure'] = 0;
        
        $this->saveHistory($this->Position);
        
        return true ;
       
    }

    public function getcontentCell($cellStatus,$Position)
    {
        $result = array();
        $result['CellStatus'] = $cellStatus ; 
        $result['New_Position'] = $Position ;
        if($cellStatus >= CellStatus::CELL_EXP)
        {
           
            // qua tang binh thuong  
           $result['Gift']= $this->getMapRewards($cellStatus);
           $this->saveRevards($result['Gift']) ;
           $this->RoadState = RoadState::NORMAL ;
        }
        else if($cellStatus == CellStatus::CELL_TREASURE)
        {
           // kho bau 
           $result['Gift']= $this->getTreasureRewards() ;
           $this->saveRevards($result['Gift']) ; 
           $this->RoadState = RoadState::NORMAL ;
        }
        else if($cellStatus == CellStatus::CELL_FATE)
        {
            // van menh 
            $rate_arr = Common::getConfig('General','PearFlowerInfo','rateInFate');
            $rand_fate = Common::randomIndex($rate_arr);
            if($rand_fate == CellStatus::GIFT)
            {
                //neu la nhan duoc qua
                $result['Gift'] = $this->getGiftOfFate();
                $this->saveRevards($result['Gift']) ; 
                $result['CellStatus'] = CellStatus::GIFT;
                $this->RoadState = RoadState::NORMAL ;
            }
            else if($rand_fate == CellStatus::TORNADO)
            {
                // neu la loc xoay
                $result['New_Position'] = $this->tornado();
                $this->Position = $result['New_Position'] ;
                
                $result['CellStatus'] = CellStatus::TORNADO ;
                //check xem toa do nay da nhan qua chua 
                if(!$this->checkHistory($result['New_Position']))
                {
                    $this->updateRoadState(RoadState::NORMAL);   
                }
                else
                {
                    $this->RoadState = RoadState::TORNADO ;  
                } 
            }
            else if($rand_fate == CellStatus::QUESTION)
            {        
                // neu la cau hoi
                $result['Question'] = $this->randomQuestion();
                $this->Question = $result['Question'] ;
                $result['CellStatus'] = CellStatus::QUESTION ;  
                $this->RoadState = RoadState::ANSWER ; 
            }           
        }
               
        return $result ;
        
        
    }
    
    public function checkRoad($ArrowId,$NumStep)
    {
        $Mapconf = Common::getConfig('Map',$this->MapId);
        $Position['Y'] = $this->Position['Y'];
        $Position['X'] = $this->Position['X'];
        $conf_y = $Mapconf[$Position['Y']];         
        
        $step = 0 ;
        switch($ArrowId)
        {
            case Arrow::LEFT:
            
                for($x= $Position['X']-1; $x >= ($Position['X'] - $NumStep) ; $x-- ) 
                {
                    if(!isset($conf_y[$x]))
                    {
                        break;
                    }
                    $step++ ;
                }
                break ;
            case Arrow::RIGHT:
            
                for($x= $Position['X']+1; $x <= ($Position['X'] + $NumStep) ; $x++ ) 
                {
                    if(!isset($conf_y[$x]))
                    {
                        break;
                    }
                    $step++ ;
                }
                break ;
            case Arrow::TOP:
                for($y= $Position['Y']-1; $y >= ($Position['Y'] - $NumStep) ; $y-- ) 
                {
                    if(!isset($Mapconf[$y][$Position['X']]))
                    {
                        break;
                    }
                    $step++ ;
                }
                break ;
            case Arrow::DOWN:
                for($y= $Position['Y']+1; $y <= ($Position['Y'] + $NumStep) ; $y++ ) 
                {
                    if(!isset($Mapconf[$y][$Position['X']]))
                    {
                        break;
                    }
                    $step++ ;
                }
                break ;
        }
        switch($ArrowId)
         {
            case Arrow::LEFT:
                $Position['X'] -= $step ;
                break ;
            case Arrow::RIGHT:
                $Position['X'] += $step ;
                break ;
            case Arrow::TOP:
                $Position['Y'] -= $step ;
                break ;
            case Arrow::DOWN:
                $Position['Y'] += $step ;
                break ;
         }
         $result = array('Step'=>$step ,'Position' => $Position ) ;
        
        return $result ;
        
    }
    public function updatePosition($Position = array(),$ArrowId,$NumStep)
    {
         if(!empty($Position))
         {
             $this->Position['X'] = $Position['X'];
             $this->Position['Y'] = $Position['Y'];
         }
         else if($NumStep >= 1 && $ArrowId >= 1 && $ArrowId <= 4 )
         {
             switch($ArrowId)
             {
                case Arrow::LEFT:
                    $this->Position['X'] -= $NumStep ;
                    break ;
                case Arrow::RIGHT:
                    $this->Position['X'] += $NumStep ;
                    break ;
                case Arrow::TOP:
                    $this->Position['Y'] -= $NumStep ;
                    break ;
                case Arrow::DOWN:
                    $this->Position['Y'] += $NumStep ;
                    break ;
            }
             
         }

         return true ;
    }
    
    public function randomQuestion()
    {   
        $conf_quest = Common::getConfig('Question');    
        do
        {
            $questtion = rand(1,count($conf_quest));    
        }
        while($questtion == $this->Question);
        
        return $questtion ;
        
    }
    
    // ham thuc hien viec random mot vi tri moi cho user
    public function tornado()
    {
      $Map_conf = Common::getConfig('Map',$this->MapId);
      
      do
      {
        $y = array_rand($Map_conf,1);
        $x = array_rand($Map_conf[$y],1);
      }while($Map_conf[$y][$x]== CellStatus::CELL_END);
      
      $newPostion = array ('Y'=>$y,'X'=>$x);
      
      return $newPostion ;   
    }
    
    // lay qua tu special treasure
    public function getSpecialTreasureRewards()
    {
        $arr_gift = array();   
        $conf_treasure = Common::getConfig('SpecialTreasure');
        
        $oUser  = User::getById(Controller::$uId);  
        
        $rand_Id = $this->newRandom($conf_treasure);
        $gifId = $this->newRandom($conf_treasure[$rand_Id]['Rewards']);
        $gift = $conf_treasure[$rand_Id]['Rewards'][$gifId] ;    
        if(empty($gift))
            return $arr_gift ;
     
        if(SoldierEquipment::checkExist($gift['ItemType']))
        {
            $AutoId = $oUser->getAutoId(); 
            $oEquipment = Common::randomEquipment($AutoId,$gift['Rank'],$gift['Color'],SourceEquipment::EVENT,$gift['ItemType']);             $arr_gift['SpecialGift'] = $oEquipment ;
            $arr_gift['SpecialGift'] =  $oEquipment ;         
        }
        else
        {   
            unset($gift['Rate']);
            $arr_gift['NormalGift'] = $gift ;
            
        }
        return $arr_gift ;
        
    }
    // lay qua tu kho bau
    public function getTreasureRewards()  
    {
        $conf_treasure = Common::getConfig('TreasureRewards')  ;
        
        $oUser  = User::getById(Controller::$uId);  
        
       
        // kiem tra xem da ra maze key hay chua
        if($this->MazeKeyInfo['Num2'] > 0)
            $rand_Id = $this->newRandom($conf_treasure,12); // loai truong hop ra mazekey
        else if($this->MazeKeyInfo['OpenTreasure'] >= 6)
            $rand_Id = 12; // cho ra maze key luon
        else      
            $rand_Id = $this->newRandom($conf_treasure);
            
            
        $gifId = $this->newRandom($conf_treasure[$rand_Id]['Rewards']);
        $gift = $conf_treasure[$rand_Id]['Rewards'][$gifId] ;
        
        //update MazekeyInfo
        if($rand_Id == 12 )
        {
            $this->MazeKeyInfo['Num']++ ;
            $this->MazeKeyInfo['Num2']++ ;
        }
            
            
        $this->MazeKeyInfo['OpenTreasure']++;
        
        
        $arr_gift = array();
        
        if(SoldierEquipment::checkExist($gift['ItemType']))
        {
            $AutoId = $oUser->getAutoId(); 
            $oEquipment = Common::randomEquipment($AutoId,$gift['Rank'],$gift['Color'],SourceEquipment::EVENT,$gift['ItemType']);             $arr_gift['SpecialGift'] = $oEquipment ;
            $arr_gift['SpecialGift'] =  $oEquipment ;         
        }
        else
        {   
            unset($gift['Rate']);
            $arr_gift['NormalGift'] = $gift ;
            
        } 
        return $arr_gift ;
                
    }
    
    // ham thuc hien viec random keu moi ; dat trong commmon
    public function newRandom($arr,$ExceptionKey = -1)
    {            
        $arr_rand = array();
        foreach ($arr as $key => $value)
        {
            if(!empty($value['Rate']))
                $arr_rand[$key] = $value['Rate'];
        }
        if($ExceptionKey != -1)
            $arr_rand[$ExceptionKey] = 0 ;
        return Common::randomIndex($arr_rand); 
    }
    
    // lay qua khi gap o khi van
    public function getGiftOfFate()  
    {
        $arr_gift = array();
        $Luckyconf = Common::getConfig('LuckyRewards');
        if(empty($Luckyconf))
            return array();
        
        $LuckyType = $this->newRandom($Luckyconf);
        $gifId = $this->newRandom($Luckyconf[$LuckyType]['Rewards']);
        
       
        $gift = $Luckyconf[$LuckyType]['Rewards'][$gifId] ;
        $oUser = User::getById(Controller::$uId);
        if(SoldierEquipment::checkExist($gift['ItemType']))
        {
            $AutoId = $oUser->getAutoId(); 
            $oEquipment = Common::randomEquipment($AutoId,$gift['Rank'],$gift['Color'],SourceEquipment::EVENT,$gift['ItemType']); 
            $arr_gift['SpecialGift'] = $oEquipment ;
        }
        else
        {   
            unset($gift['Rate']);
            $arr_gift['NormalGift'] = $gift ;
            
        } 
        return $arr_gift ;
         
    }
    
    // lay qua khi gap tren duong
    public function getMapRewards($id_Gift)  
    {
        $conf = Common::getConfig('MapRewards',$id_Gift);
        if(empty($conf))
            return array();
        
        $oUser  = User::getById(Controller::$uId);
        $gifId = $this->newRandom($conf);
        
        $gift = $conf[$gifId] ;
        
        $arr_gift = array();
        
        if(in_array($gift['ItemType'],array(Type::Exp,Type::Money ,Type::Material,Type::EnergyItem,Type::RankPointBottle),true))
        {
            unset($gift['Rate']);
            $arr_gift['NormalGift'] = $gift ;
            return $arr_gift ; 
        }
        else
        {
            return array();
        }
         
    }
    
    // lay qua khi ra khoi map
    public function getFinishRewards()  
    {/*
        $conf = Common::getConfig('FinishRewards');
        
        $oUser  = User::getById(Controller::$uId);
        $oEvent = Event::getById(Controller::$uId);
        $maxPearFlower = Common::getConfig('General','PearFlowerInfo','maxgetPearFlower');
        $rand_Id = $this->newRandom($conf);
        do
        {
            $gifId = $this->newRandom($conf[$rand_Id]['Rewards']);
        }
        while($conf[$rand_Id]['Rewards'][$gifId]['Color'] == 4 && $oEvent->EventList['PearFlower']['MaxPearFlower'] >=$maxPearFlower);
        if($conf[$rand_Id]['Rewards'][$gifId]['Color'] == 4)
            $oEvent->EventList['PearFlower']['MaxPearFlower'] += 1 ;
        
        $gift = $conf[$rand_Id]['Rewards'][$gifId] ;
        
        $arr_gift = array();
        
        if(SoldierEquipment::checkExist($gift['ItemType']))
        {
            $AutoId = $oUser->getAutoId(); 
            $oEquipment = Common::randomEquipment($AutoId,$gift['Rank'],$gift['Color'],SourceEquipment::EVENT,$gift['ItemType']);             $arr_gift['SpecialGift'][] = $oEquipment ;
            $arr_gift['SpecialGift'][] =  $oEquipment ;
        }
        else
        {
            unset($gift['Rate']);
            $arr_gift['NormalGift'][]= $gift ;  
        } 
        */
        // them kinh nghiem
        $successExp  = Common::getParam('SuccessRewards');
        $ExpGift = array('ItemType'=>'Exp','Num'=>$successExp);
        $arr_gift['NormalGift'][]= $ExpGift;
        
        return $arr_gift ;
    }
    
    // lay qua khi ko ra duoc khoi map
    public function getFailRewards()
    {
/*        if(empty($this->Revards['NormalGift']))
            return false ;

        foreach ($this->Revards['NormalGift'] as $index => $gift)
            {
                if($gift[Type::ItemType] == Type::Exp)   
                {
                    $per = Common::getConfig('General','PearFlowerInfo','FailRewardsPercent');
                    $num = ceil($gift[Type::Num]*$per/100);
                    return $num ;
                }
             }
        return false ;*/
        
        return intval(Common::getParam('FailRewards')); 
    }

    public function saveRevards($arr_gift)
    {
         if(!empty($arr_gift['NormalGift']))
         {
            $ItemType = $arr_gift['NormalGift'][Type::ItemType] ;
            $ItemId= $arr_gift['NormalGift'][Type::ItemId] ;
            $Num = $arr_gift['NormalGift'][Type::Num] ;
            $is_exist = -1 ;
            foreach ($this->Revards['NormalGift'] as $index => $gift)
            {
                if($gift[Type::ItemType] == $ItemType && $gift[Type::ItemId] == $ItemId)   
                    $is_exist = $index ;     
            }
            
            if($is_exist != -1)   
            {
                $this->Revards['NormalGift'][$is_exist][Type::Num] += $Num ;
            }
            else
            {
                 $this->Revards['NormalGift'][] = $arr_gift['NormalGift'];
            }
         }
         
         if(!empty($arr_gift['SpecialGift']))
         {
             $object = $arr_gift['SpecialGift'] ;
             $this->Revards['SpecialGift'][$object->Id] = $object ;      
         }
   
    }
    
    // ham thuc hien update vi tri vua di qua 
    public function saveHistory($Position)
    {
      $this->History[$Position['Y']][$Position['X']] = true ;   
    }
    
    // ham thuc hien check xem toa do nay da di qua chua
    public function checkHistory($Position)
    {
       if(isset($this->History[$Position['Y']][$Position['X']]))
       {
           return false ; // o nay da di qua roi
       }
       return true;
    }
    
    
    // ham update status cua duong di 
    public function updateRoadState($state)
    {
        if($state < 0 || $state > RoadState::ANSWER)
            return false ;
        $this->RoadState = $state ;
        return true ;
    }
    
    public function updateTeleport($Num = 1)
    {
        $this->TeleportNum += $Num ;
    }
         
      
}
?>
