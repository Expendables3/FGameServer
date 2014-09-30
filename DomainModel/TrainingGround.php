<?php
class TrainingGround extends Model
{
    public $Room ;

    public function __construct($uId)
    {
        // khoi tao 
        $this->Room = array();
        $this->Room['FishTimeList'] = array();
        $this->addRoom(1);
        parent::__construct($uId);

    }
    
    // add new Training Room
    public function addRoom($RoomId)
    {
        if($RoomId>4 || $RoomId < 1 )
            return false ;
            
        if(isset($this->Room[$RoomId]))
            return false ;
            
        $this->Room[$RoomId]= array();
        /*
        $this->Room[$RoomId]['Status']          = 0 ;// Free
        $this->Room[$RoomId]['EndTime']         = 0 ;// finish training time 
        $this->Room[$RoomId]['TimeType']        = 0 ;// Time Type of Training
        $this->Room[$RoomId]['IntensityType']   = 0 ;// Intensity Type of Training
        $this->Room[$RoomId]['SoldierId']       = 0 ;
        $this->Room[$RoomId]['LakeId']          = 0 ;
        */
        
        return true ;
    }
    
    // start training
    public function startTraining($roomId,$lakeId,$soldierId,$timeType,$intensityType,$soldierName='')
    {
        $this->Room[$roomId]['StartTime']       = $_SERVER['REQUEST_TIME'] ;// finish training time 
        $this->Room[$roomId]['TimeType']        = $timeType ;// Time Type of Training
        $this->Room[$roomId]['IntensityType']   = $intensityType ;// Intensity Type of Training
        $this->Room[$roomId]['SoldierId']       = $soldierId ;
        $this->Room[$roomId]['SoldierName']     = $soldierName ;
        $this->Room[$roomId]['LakeId']          = $lakeId ;
    }
    
    public function speedUpTraining($roomId)
    {
        $this->Room[$roomId]['StartTime']  -= $this->Room[$roomId]['TimeType']*60 ; 
        $this->Room['SpeedUpNum'] += 1 ; 
    }
    
    public function checkCompeleteTraining($roomId)
    {
        //$conf = Common::getConfig('CustomTraining','Time');
        $TimeType = $this->Room[$roomId]['TimeType'];
        if($this->Room[$roomId]['StartTime'] + $TimeType*60 < $_SERVER['REQUEST_TIME'] )        
            return true;
        return false ;
    }
    
    public function getGiftTraining($roomId)
    {
        $conf = Common::getConfig('CustomTraining');
        $TimeType       = $this->Room[$roomId]['TimeType'];
        $IntensityType  = $this->Room[$roomId]['IntensityType'];
        $TimeConf = $conf['Time'][$TimeType];
        $IntensityConf = $conf['Intensity'][$IntensityType];
        
        $gift= array();
        // qua co dinh
        
        foreach($TimeConf['Gift'] as $key =>$arr)
        {
            if(empty($arr)) continue ;
            
            if(is_array($arr['Num']))
            {
                $index = array_rand($arr['Num'],1);
                $arr['Num'] = $arr['Num'][$index];
                $arr['Num'] = round($arr['Num']*$IntensityConf['GiftNum']);
                
            }
                
            $gift[] = $arr ;
        }
        
        // qua may man
        $arr_rand = array('Gift'=>20,'Gift_2'=>30,'Gift_3'=>40,'Gift_4'=>10);
        for($i =1 ; $i <= $IntensityConf['GiftNum'] ;$i++)
        {
            $BlockGiftName = Common::randomIndex($arr_rand);
            Debug::log('$BlockGiftName:'.$BlockGiftName);
            
            $rand_list = array();           
            foreach($IntensityConf[$BlockGiftName] as $key2 =>$arr)
            {
                if(empty($arr)) continue ;
                $rand_list[$key2] = $arr['Rate'] ;
            }
            
            $id =  Common::randomIndex($rand_list);
            
            
            if($id >0)
            {  
                $arr_bonus = array();
                $arr_bonus = $IntensityConf[$BlockGiftName][$id];
                if(is_array($arr_bonus['Num']))
                {
                    $index = array_rand($arr_bonus['Num'],1);
                    $arr_bonus['Num'] = $arr_bonus['Num'][$index];
                }
                $arr_bonus['Num'] = round($arr_bonus['Num']*$TimeConf['GiftNum']);
                
                $gift[] = $arr_bonus;
            }
        }
        
        // nhan doi qua tang ngu mach
        $conf_param = Common::getParam('TrainingGround') ;
        if($_SERVER['REQUEST_TIME'] >= $conf_param['BeginTime'] && $_SERVER['REQUEST_TIME'] <= $conf_param['ExpireTime'])
        {
            foreach($gift as $id => $_arr)
            {
                if(empty($_arr)|| $_arr['ItemType'] != "Meridian")
                    continue ;
                
                $gift[$id]['Num'] = round($_arr['Num']*intval($conf_param[multi]));
            }
        }
        //-------
        return $gift ;

    }
    
    public static function getById($uId)
    {
        $object = DataProvider :: get($uId,__CLASS__) ;
        if(!is_object($object))
        {
            $object = new TrainingGround($uId);
            $object->save();
        }       
        return $object ;
        
    }
    
    public function updateFirstTimeOfDay()
    {
        $this->Room['SpeedUpNum'] = 0 ;
        // reset thoi gian traing ca 
        $this->Room['FishTimeList'] = array(); 
    }
    
    public function sellSoldier($SoldierId)
    {
        foreach($this->Room as $roomid => $arr)
        {
            if($arr['SoldierId'] == $SoldierId)
            {
                $this->Room[$roomid] = array();
            }
        }
    }
    
    


  
  
}
?>
