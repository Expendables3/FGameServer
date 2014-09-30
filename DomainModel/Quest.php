<?php

/**
 * UserAction
 * @author Toan Nobita
 * 2/9/2010
 */


class Quest extends Model
{
	public  $QuestInfo = array() ;
    public  $DailyInfo = array() ;
    public  $LastUpdateDaily = 0 ;
    public  $CurrentQuest = 1 ;
    public  $UnlockQuest2 = false ;
    public  $ResetTimes = 0 ;
    public  $MainQuestSeriesId = 0;
    public  $ElementMainQuest = 1;

    // moi mot task co 2 gia tri : Num va Status
    // moi mot quest co 1 gia tri : Status
    // moi mot series quest co 1 gia tri : Status

	public function __construct($uId,$Level= 1)
	{
	  // Khoi tao thong tin quest
      // ....
    $this->checkNewQuest($Level,true,false);
    $this->newDailyTask($Level);
    parent :: __construct($uId) ;

	}
    
  /**
  * create new daily quest
  * @author hieupt
  * 17/05/2011
  */
  public function newDailyTask($Level = 1)
  {
    // Check Last Update Day
    $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
    $Lastday =  date('Ymd', $this->LastUpdateDaily);
    if ($Today == $Lastday){
        return FALSE ;
    }
      
    // Load Config
    //$KeyLevel =   intval(($Level - 1)/5)+1 ;
    $KeyLevel =   $this->getLevelDailyQuest($Level);
    $DailyQuestConf = Common::getConfig('DailyQuest',$KeyLevel) ;
    if(!is_array($DailyQuestConf)){
        return FALSE ;
    }
    
    
    // unset old dailyinfo
    $this->DailyInfo = array();
  
  
  
    $numTask = array(1 => 3,2 => 1,3 => 3);

      $actionMap = Common::getConfig('ActionMapDailyGift');
      $listEasy = $actionMap['Easy'];
      $listHard = $actionMap['Hard'];
      
      $arrHard = array();
      $arrEasy = array();
      
        foreach($DailyQuestConf as $idTask => $oTask){
          if (isset($listEasy[$idTask])){
              $arrEasy[$idTask] = $idTask;
          }
          else if (isset($listHard[$idTask])){
              $arrHard[$idTask] = $idTask;
          }        
        }
      foreach ($numTask as $questId => $taskNum){
          
        if ($questId==1){
            $listTask = array();
            $listTask = array_rand($arrEasy,3);
        }
        else if ($questId==2){
            $listT = array_rand($arrEasy,1);
            $listTask = array();  
            $listTask[] = $listT;
        }
        else if ($questId==3){
            $listT = array_rand($arrEasy,1);
            $hardTask = array_rand($arrHard,2);
            $listTask = array();  
            $listTask[] = $listT;
            $listTask[] = $hardTask[0];
            $listTask[] = $hardTask[1];
        }

        foreach($listTask as $index => $taskId){
            $this->DailyInfo[$questId][$taskId] = array();
            $this->DailyInfo[$questId][$taskId]['Num']    = 0;
            $this->DailyInfo[$questId][$taskId]['Status'] = FALSE;
            $this->DailyInfo[$questId][$taskId]['Action'] = $DailyQuestConf[$taskId]['Action'];
            $this->DailyInfo[$questId][$taskId]['Level'] = $Level;
        }
        
      }

    $this->UnlockQuest2 = FALSE;
      $this->CurrentQuest = 1;
    $this->LastUpdateDaily = $_SERVER['REQUEST_TIME'];
    
    $oUserPro = UserProfile::getById(Controller::$uId);
    $oUserPro->NewDailyQuest = true ; 
    $oUserPro->save();
    
    return TRUE ;
    
  }
  
  
    public function checkNewQuest($level = 1,$refresh = false,$nextQuest = false)
    {
      $QuestConfig = & Common::getConfig('SeriesQuest') ;
      if(!is_array($QuestConfig))
      {
        return FALSE;
      }
      
      // Initial MainQuest
      $initialMainQuest = false; 
      if($this->MainQuestSeriesId == 0)
      {
          if(($level > MainQuest::FishWarLevel))
            $this->MainQuestSeriesId = MainQuest::BeforeFishWarSeriesId;
          $initialMainQuest = true;          
      }
      
       // Main Quest 

      if( ( $initialMainQuest || ($this->QuestInfo[$this->MainQuestSeriesId]['Status'] == TRUE) ))
      {
          if($this->MainQuestSeriesId < MainQuest::END_SERIES_ID)
          {
               $this->MainQuestSeriesId ++;
                $ids = $this->MainQuestSeriesId;
 
                 $seri = $QuestConfig[$ids];
               $this->QuestInfo[$ids]['Status'] = FALSE ;
                $this->QuestInfo[$ids]['Id'] = $ids ;
                
                if($level < $seri['LevelRequire'])
                    $this->QuestInfo[$ids]['Active'] = FALSE ;
                else $this->QuestInfo[$ids]['Active'] = TRUE ;
                
                $this->QuestInfo[$ids]['Step'] = 1 ;
                foreach($seri['Quest'] as $idq => $quest)
                {
                      if(!is_array($quest)|| !isset($idq))
                      {
                            continue;
                      }
                      $this->QuestInfo[$ids][$idq]['Status'] = FALSE ;
                      $this->QuestInfo[$ids][$idq]['Id'] = $idq ;
                      // neu ton tai Task
                      if(is_array($quest['TaskList']))
                      {
                        foreach($quest['TaskList'] as $idt => $task)
                        {
                         $this->QuestInfo[$ids][$idq][$idt]['Status'] = FALSE ;
                         $this->QuestInfo[$ids][$idq][$idt]['Num'] = 0 ;
                         $this->QuestInfo[$ids][$idq][$idt]['Id'] = $idt ;
                        }
                      }
                }
          }      
      }
      elseif (!$this->QuestInfo[$this->MainQuestSeriesId]['Active'] && ($level >= $this->QuestInfo[$this->MainQuestSeriesId]['LevelRequire']))
            $this->QuestInfo[$this->MainQuestSeriesId]['Active'] = TRUE;
      
      // for other series quest # main quest
      /*foreach($QuestConfig as $ids => $seri)
      {          
        if($ids <= MainQuest::END_SERIES_ID) continue;   
                    
        if(!is_array($seri)|| !isset($ids))
        {
          continue;
        }
        //if($_SERVER['REQUEST_TIME'] > $seri['ExpireDate'])
//        {
//          unset($this->QuestInfo[$ids]) ;
//          continue;
//        }

        if(isset($this->QuestInfo[$ids]) && !$refresh)
        {
            continue;
        }
        else if (!$nextQuest && ($level < $seri['LevelRequire']) )
        {
           continue ;
        }

        $this->QuestInfo[$ids]['Status'] = FALSE ;
        $this->QuestInfo[$ids]['Id'] = $ids ;
        $this->QuestInfo[$ids]['Active'] = TRUE ; 
        $this->QuestInfo[$ids]['Step'] = 1 ;
        foreach($seri['Quest'] as $idq => $quest)
        {
          if(!is_array($quest)|| !isset($idq))
          {
                continue;
          }
          $this->QuestInfo[$ids][$idq]['Status'] = FALSE ;
          $this->QuestInfo[$ids][$idq]['Id'] = $idq ;
          // neu ton tai Task
          if(is_array($quest['TaskList']))
          {
            foreach($quest['TaskList'] as $idt => $task)
            {
             $this->QuestInfo[$ids][$idq][$idt]['Status'] = FALSE ;
             $this->QuestInfo[$ids][$idq][$idt]['Num'] = 0 ;
             $this->QuestInfo[$ids][$idq][$idt]['Id'] = $idt ;
            }
          }

        }
      }         */
      return TRUE ;
    }

   /**
   * UserAction
   * @author AnhBV
   * 27/10/2010
   * Decription : thuc hien check series da hoan thanh hay chua
   */
    public function checkComplete($SeriesQuestId)
    {

        $arrSeries = $this->QuestInfo[$SeriesQuestId];

        if(!is_array($arrSeries))
        {
            return Error :: OBJECT_NULL ;
        }
        if($arrSeries['Status'] == TRUE)
        {
          return Error :: SUCCESS ;
        }
        foreach($arrSeries as $key => $arrQuest )
        {
            if($key == 'Status'||$key == 'Id'||$key == 'Step')
            {
              continue ;
            }
            if(!is_array($arrQuest))
            {
              continue ;
            }
            $return_quest = $this->checkStatusQuest($SeriesQuestId,$key);
            if($return_quest != Error::SUCCESS )
            {
               return  Error ::NOT_COMPLETE_TASK ;
            }
        }
        $this->QuestInfo[$SeriesQuestId]['Status'] = TRUE ;
        return  Error :: SUCCESS ;

    }
    /**
   * UserAction
   * @author AnhBV
   * 27/10/2010
   * Decription : thuc hien check quest da hoan thanh hay chua
   */
    public function checkStatusQuest($SeriesQuestId,$QuestId)
    {
        $arrQuest = $this->QuestInfo[$SeriesQuestId][$QuestId];
        if(!is_array($arrQuest))
        {
            return Error :: OBJECT_NULL ;
        }
        if($arrQuest['Status'] == TRUE)
        {
          return  Error :: SUCCESS ;
        }
        foreach($arrQuest as $key => $arrTask )
        {
            if($key == 'Status'||$key == 'Id' )
            {
              continue ;
            }
            if(!is_array($arrTask))
            {
              continue ;
            }
            $return_Task = $this->checkStatusTask($SeriesQuestId,$QuestId,$key);
            if($return_Task != Error::SUCCESS )
            {
                return  Error ::NOT_COMPLETE_TASK ;
            }
        }
        $this->QuestInfo[$SeriesQuestId][$QuestId]['Status'] = TRUE ;
        return  Error :: SUCCESS ;

    }
    /**
   * UserAction
   * @author AnhBV
   * 27/10/2010
   * Decription : thuc hien check task da hoan thanh hay chua
   */
    public function checkStatusTask($SeriesQuestId,$QuestId,$TaskId)
    {
        $arrTask = $this->QuestInfo[$SeriesQuestId][$QuestId][$TaskId];
        if(!is_array($arrTask))
        {
            return  Error :: SUCCESS ;
        }
        if($arrTask['Status'] == TRUE)
        {
          return  Error :: SUCCESS ;
        }
        $QuestConfig = & Common::getConfig('SeriesQuest') ;
        $confTask = $QuestConfig[$SeriesQuestId]['Quest'][$QuestId]['TaskList'][$TaskId];

        if(!is_array($confTask))
        {
          return Error :: NOT_LOAD_CONFIG ;
        }

        if($arrTask['Num'] >= $confTask['Num'])
        {
          $this->QuestInfo[$SeriesQuestId][$QuestId][$TaskId]['Status'] = TRUE ;
          return  Error :: SUCCESS ;
        }
        return  Error ::NOT_COMPLETE_TASK ;
    }

    
    /**
    * check task daily quest complete
    * @author hieupt
    * 17/05/2011
    */
    
    public function checkTaskComplete($idTask)
    {
      $idQuest = $this->CurrentQuest;      
      if (!is_array($this->DailyInfo[$idQuest][$idTask]))
          return FALSE;
      
      if ($this->DailyInfo[$idQuest][$idTask]['Status'] == TRUE){
          return TRUE ;
      }
      //$KeyLevel =   intval(($this->DailyInfo[$idQuest][$idTask]['Level']-1)/5)+1 ;
      $KeyLevel =   $this->getLevelDailyQuest($this->DailyInfo[$idQuest][$idTask]['Level']);   
      $DailyQuestConf = & Common::getConfig('DailyQuest',$KeyLevel) ;
    
      if($this->DailyInfo[$idQuest][$idTask]['Num']>= $DailyQuestConf[$idTask]['Num']){
          $this->DailyInfo[$idQuest][$idTask]['Status'] = TRUE;
          return TRUE;
      } 
      else {
          return FALSE;
      }
    
  }
    
    
    /**
    * check dailyquets complete
    * @author hieupt
    * 17/05/2011
    */
    
    public function checkDailyQuestComplete()
    {

      $idQuest = $this->CurrentQuest;
      if (!is_array($this->DailyInfo)){
          return FALSE;
      }
      
      foreach($this->DailyInfo[$idQuest] as $idTask => $oTask){
          if ($this->DailyInfo[$idQuest][$idTask]['Status'] == TRUE){
              continue ;
          }
          
          //$KeyLevel =   intval(($this->DailyInfo[$idQuest][$idTask]['Level']-1)/5)+1 ;
          $KeyLevel =   $this->getLevelDailyQuest($this->DailyInfo[$idQuest][$idTask]['Level']);

          $DailyQuestConf = Common::getConfig('DailyQuest',$KeyLevel) ;
          
          if(!is_array($DailyQuestConf)){
              return FALSE ;
          }
          
          if($this->DailyInfo[$idQuest][$idTask]['Num']>= $DailyQuestConf[$idTask]['Num']){
            $this->DailyInfo[$idQuest][$idTask]['Status'] = TRUE;
          } 
          else {
              return FALSE;
          }
      }

      return TRUE ;
    }
    
     /**
   * UserAction
   * @author AnhBV
   * 27/10/2010
   * Decription : thuc hien viec lay qua nhiem vu gui ve cho client
   */
    public function loadGift($SeriesQuestId,$QuestId = Null,$TaskId = Null)
    {
        $QuestConfig = & Common::getConfig('SeriesQuest') ;
        if(!is_array($QuestConfig))
        {
          return array('Error' => Error :: NOT_LOAD_CONFIG) ;
        }

        if(!empty($TaskId)&& !empty($QuestId))
        {
            $conf = $QuestConfig[$SeriesQuestId]['Quest'][$QuestId]['TaskList'][$TaskId];
            if(!is_array($conf))
            {
                 return array('Error' => Error :: NOT_LOAD_CONFIG) ;
            }
            $oQuest = $this->QuestInfo[$SeriesQuestId][$QuestId][$TaskId] ;


        }
        else if(!empty($QuestId))
        {
            $conf = $QuestConfig[$SeriesQuestId]['Quest'][$QuestId];
            if(!is_array($conf))
            {
                    return array('Error' => Error :: NOT_LOAD_CONFIG) ;
            }
             $oQuest = $this->QuestInfo[$SeriesQuestId][$QuestId];

        }
        else
        {
           $conf = $QuestConfig[$SeriesQuestId];
           if(!is_array($conf))
            {
                return array('Error' => Error :: NOT_LOAD_CONFIG) ;
            }
             $oQuest = & $this->QuestInfo[$SeriesQuestId] ;
        }
        if(!is_array($conf['Bonus']))
        {
          return array('Error' => Error :: NOT_GIFT) ;
        }
        $seriesGift = array();
        foreach($conf['Bonus'] as $key => $value)
        {
            if(!is_array($value))
            {
                continue;
            }
            $seriesGift['GiftList'][] = $value ;
         }
        // xoa quest da hoan thanh
        if(!empty($TaskId)&& !empty($QuestId))
        {
           unset($this->QuestInfo[$SeriesQuestId][$QuestId][$TaskId]) ;
        }
        else if(!empty($QuestId))
        {
           unset($this->QuestInfo[$SeriesQuestId][$QuestId]);
        }
        else
        {
          $this->QuestInfo[$SeriesQuestId]['Status']= true ;
        }
        $seriesGift['Error'] =  Error :: SUCCESS ;
        return $seriesGift ;

    }
    /**
   * UserAction
   * @author AnhBV
   * 2/11/2010
   * Decription : thuc hien viec lay nhiem vu series hien tai gui ve cho client
   */
    public function getQuestActive()
    {
       $result_quest = array();
       foreach($this->QuestInfo as $key => $arr_quest)
       {
          if(!is_array($arr_quest)||$arr_quest['Status']== true || ($key == (MainQuest::END_SERIES_ID + 1)))
          {
             continue;
          }
          
          $result_quest[$key]['Status'] = $arr_quest['Status'];
          $result_quest[$key]['Active'] = $arr_quest['Active'];
          $result_quest[$key]['Id']     = $arr_quest['Id'];
          //$result_quest[$key]['Step']     = $arr_quest['Step'];

          foreach($arr_quest[$arr_quest['Step']]  as $key_2 =>$TaskList)
          {
            if($key_2 =='Id'||$key_2 =='Status')
            {
                $result_quest[$key]['Quest'][$key_2] = $TaskList;
            }
            else
            {
              $result_quest[$key]['Quest']['TaskList'][] = $TaskList;
            }
          }
       }
       return $result_quest ;
    }
    
    /**
    * get current daily quest
    * 
    */
    public function getCurrentQuest()
    {
      return $this->CurrentQuest;
    }
    

    public function update($action,$param = array(),$result = array())
    {
      //update daily quest
      $this->updateDailyQuest($action,$param);
      $actionMap =& Common::getConfig('ActionMap',$action) ;
      if(!is_array($actionMap))  return false ;
            
      foreach($actionMap as $questIndex)
      {
        $resultArr = $this->preProcessParam($action,$questIndex,$param,$result); 
        $this->execute($questIndex,$resultArr['Param'],$resultArr['Result']);
      }

      return true ;
    }
    
    // su ly du lieu truoc khi dua vao thuc hien quest
    public function preProcessParam($action,$questIndex,$param,$result)
    {
      $arr = array('Param'=>array(),'Result'=>array());   
      
      $step = $this->QuestInfo[$questIndex[0]]['Step'] ;
      if($step != $questIndex[1])
      {
         return $arr ;
      } 
            
      // xu ly du lieu da co san trong database       
      $_array = $this->updateQuestData($questIndex,$param,$result);
      
      $param  = $_array['Param'] ;
      $result = $_array['Result'] ;
       
      // tinh xem check truoc hay sau :     
      if($questIndex['CheckType'] == CheckType::AfterCheck)
      {
          $arr['Result'] = $result ;      
      }
      else
      {
          $arr['Param'] = $param ;         
      }
      return $arr ;
    }
    
    public function updateQuestData($questIndex,$param,$result)
    {
        $ActionMapKey = $questIndex[0]."_".$questIndex[1]."_".$questIndex[2] ;    
        if($ActionMapKey == '7_4_1') // sua du lieu param dau vao khi dung BuffItem
        {    
            foreach ($param['ItemList'] as $key => $item)
            {
              if($item[Type::ItemType] == BuffItem::Samurai)
              {
                $param['ItemList'][0] = $item ;
                break ;
              }
            }
        }

        $_array = array('Param'=>$param,'Result'=>$result);
        
        return $_array ;
        
      }
    
    
    /**
    * update daily quest
    * @author hieupt
    * 17/05/2011
    */
    
    private function updateDailyQuest($action,$param)
    {

    $DailyQuestConf = Common::getConfig('DailyQuest') ;
    if(!is_array($DailyQuestConf))
    {
      return FALSE ;
    }
    
    if ($this->getCurrentQuest()==2 && $this->UnlockQuest2==FALSE){
        return false;
    }
    

    $currentQuest = $this->getCurrentQuest();
    foreach($this->DailyInfo[$currentQuest] as $key => $arr_DQuest)
    {
      if(!is_array($arr_DQuest))
      {
        continue;
      }
      if($arr_DQuest['Action'] == $action )
      {
        //kiem tra trong file config xem co ok voi param khong
        //$KeyLevel = intval(($arr_DQuest['Level']-1)/5)+1 ;
        $KeyLevel =   $this->getLevelDailyQuest($arr_DQuest['Level']); 
        $Daily = $DailyQuestConf[$KeyLevel][$key] ;
        if(!is_array($Daily))
        {
          continue;
        }   
        if($this->DailyInfo[$currentQuest][$key]['Num'] < $Daily['Num'])
        {
          $hit = $this->checkParam($Daily['Param'],$param);
          //neu ok thi
          $this->DailyInfo[$currentQuest][$key]['Num']+=$hit;
          if($this->DailyInfo[$currentQuest][$key]['Num'] > $Daily['Num'] )
          {
            $this->DailyInfo[$currentQuest][$key]['Num'] = $Daily['Num'] ;
          }

        }

      }
    }

    return TRUE;
  }
    
    
    
    /**
    * update action quest
    * @author hieupt
    * 17/05/2011
    */
    
    public function updateActionQuest($actionQuest)
    {
      $map = array('Money' => 'earnMoney',
                  'Energy' => 'useEnergy',
                  'NumMaterial' => 'collectMaterial',
                  'NumFish' => 'fishingFish');
    
      $idQuest = $this->getCurrentQuest();
      foreach($this->DailyInfo[$idQuest] as $idTask => $oTask){
        foreach ($actionQuest as $type => $num) {
            
            if ($this->DailyInfo[$idQuest][$idTask]['Action']==$map[$type]){
                $this->DailyInfo[$idQuest][$idTask]['Num'] += $num;
            }
          
        }
        
      }
    
    }
    

    public function execute($questIndex,$param,$result)
    {
      // ensure active cua seriest quest
      if(!$this->QuestInfo[$questIndex[0]]['Active']) return false;
        
      $Config = & Common::getConfig('SeriesQuest') ;
      $QuestConfig = $Config[$questIndex[0]];     
      // kiem tra xem quest dang thuoc buoc nao
      $step = $this->QuestInfo[$questIndex[0]]['Step'] ;
      if($step != $questIndex[1])
      {
         return false ;
      }
      $TaskInfo = $QuestConfig['Quest'][$step]['TaskList'];
      if(!is_array($TaskInfo))
      {
         return 1;
      }
      $TaskInfo = $TaskInfo[$questIndex[2]];

      // Kiem tra tham so
      $hit = 0;
      if($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] < $TaskInfo['Num'] )
      {
          if(!empty($param))
          {
            $hit = $this->checkParam($TaskInfo['Param'],$param);
          }
          
          if (!empty($result))
          {
            $hit = $this->checkParam($TaskInfo['Result'],$result);   
          }  
          if($hit == false ) return false ;
          $this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] += $hit ;
          if($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] > $TaskInfo['Num'] )
          {
               $this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] =  $TaskInfo['Num'];
          }
      }
      //update
      if(!is_array($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]))
      {
        return false;
      }

      return $hit;
    }
    /*public function execute($questIndex,$param,$result)
    {

      $Config = & Common::getConfig('SeriesQuest') ;
      $QuestConfig = $Config[$questIndex[0]];
      // kiem tra xem quest dang thuoc buoc nao
      $step = $this->QuestInfo[$questIndex[0]]['Step'] ;
      if($step != $questIndex[1])
      {
         return false ;
      }
      $TaskInfo = $QuestConfig['Quest'][$step]['TaskList'];
      if(!is_array($TaskInfo))
      {
         return 1;
      }
      $TaskInfo = $TaskInfo[$questIndex[2]];

      // Kiem tra tham so
      $hit = 0;
      if($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] < $TaskInfo['Num'] )
      {
          $hit = $this->checkParam($TaskInfo['Param'],$param);
          if($hit == false ) return false ;
          $this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] += $hit ;
          if($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] > $TaskInfo['Num'] )
          {
               $this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]['Num'] =  $TaskInfo['Num'];
          }
      }
      //update
      if(!is_array($this->QuestInfo[$questIndex[0]][$questIndex[1]][$questIndex[2]]))
      {
        return false;
      }

      return $hit;
    }*/

    public function checkParam($questParam,$param,$deep = 1)
    {
      if($deep > 5) return false ;
      if(!is_array($questParam)|| empty($questParam))  return 1 ;
      $uId = Controller::$uId ;
      foreach($questParam as $key => $value)
      {
          if(!isset($param[$key])  ) return false  ;
          if(!is_array($value))
          {
              if($key == 'UserId')
              {
                  if($value == 'Self')
                  {
                     if($param[$key] != $uId)
                     {
                       return false ;
                     }
                  }
                  else
                  {
                     if($param[$key] == $uId)
                     {
                         return  false ;
                     }
                  }
             }
             else if($param[$key] != $value )
             {
               return false ;
             }
             continue;
          }
          else 
          {
              if(!$this->checkParam($value,$param[$key],$deep++))
              {
                return false ;
              }
          }

      }
      return 1 ;
    }
    
    
    
    /**
    * unlock quest by xu
    * @author hieupt
    * 17/05/2011
    */
    
    public function unlockQuestByXu()
    {
    
      if ($this->getCurrentQuest()==1)
          return Error::NOT_COMPLETE_TASK;
        
      if ($this->UnlockQuest2==TRUE)
          return Error::QUEST_UNLOCKED;


      $oUser = User::getById(Controller::$uId);
      if (!is_object($oUser))
        return Error::NO_REGIS;

      $idQuest = $this->CurrentQuest;  
      foreach($this->DailyInfo[$idQuest] as $idTask => $oTask){
          $Level = $oTask['Level'];
      }
      //$KeyLevel =   intval(($Level - 1)/5)+1 ;   
      $KeyLevel =  $this->getLevelDailyQuest($Level);
      
      $conf_xu = Common::getConfig('XuDailyQuest', $KeyLevel);

      
      $info = '1'.':'.'UnlockQuest'.':1' ;
      
      //happy day
      $dailyquestUnlockByXuFree = Common::bonusHappyWeekDay('dailyquestUnlockByXuFree');
      if(!$dailyquestUnlockByXuFree)
      {
          if (!$oUser->addZingXu(-$conf_xu['XuUnlock'],$info))
          {
            return  Error :: NOT_ENOUGH_ZINGXU ;
          }
      }
      $this->UnlockQuest2 = TRUE;
      $oUser->save();
      return Error::SUCCESS;
      
    }
    
    /**
    * done quest quickly
    *  @author hieupt
    * 17/05/2011
    */
    
    public function doneByXu($idTask)
    {
    
    $idQuest = $this->CurrentQuest;  
    $result = array();
    
    if(!isset($idTask))
    {
       return array('Error' => Error::PARAM); 
    }
    if (!is_array($this->DailyInfo[$idQuest][$idTask]))
    {
       return array('Error' => Error::PARAM); 
    }
    
    if($this->checkTaskComplete($idTask)==TRUE)
    {
        return array('Error' => Error::QUEST_DONE); 
    }

    if ($idQuest == 2 || $idQuest == 3)
      if ($this->UnlockQuest2==false)
        return array('Error' => Error::NOT_UNLOCK_QUEST);
    
    // Load Config
    //$KeyLevel =   intval(($this->DailyInfo[$idQuest][$idTask]['Level']-1)/5)+1 ;
    $KeyLevel =   $this->getLevelDailyQuest($this->DailyInfo[$idQuest][$idTask]['Level']) ;

    $DailyQuestConf = Common::getConfig('DailyQuest',$KeyLevel) ;
    $XuConf = Common::getConfig('XuDailyQuest', $KeyLevel);
    if(!is_array($DailyQuestConf)||!is_array($XuConf))
    {
      $result['Error'] = Error::NOT_LOAD_CONFIG; 
    }
    
    $oUser = User :: getById(Controller::$uId) ;    
    
    // load xu
    
    
    $XuNeed = $XuConf[$this->CurrentQuest];

    
    $info = '1'.':'.'DoneByXu'.':1' ;
    if (!$oUser->addZingXu(-$XuNeed,$info))
    {
        return array('Error' => Error::NOT_ENOUGH_ZINGXU);
    }
    else {
        $this->DailyInfo[$idQuest][$idTask]['Num']= $DailyQuestConf[$idTask]['Num'];
        $this->DailyInfo[$idQuest][$idTask]['Status']= TRUE;
        
        $oUser->save();
        $result['Error'] = Error::SUCCESS;
        $result['ZMoney'] = $XuNeed;
    }
    
    

    return $result;
  }
  
  // ham thuc hien viec cho phep reset lai dailyquest lan nua 
  
  public function payToResetDaily()
  {
    // neu van con quest 
    if($this->CurrentQuest < 4)
    {
      return false ;
    }
    
    $oUser = User::getById($this->uId);
    //$KeyLevel =   intval(($oUser->Level-1)/5)+1 ;
    $KeyLevel =   $this->getLevelDailyQuest($oUser->Level) ; 
    // du tien hay khong  
    $Conf = intval(Common::getConfig('XuDailyQuest',$KeyLevel,'XuReset')) ;
    
    $info = "137:XuReset:".$Conf;   
    if(!$oUser->addZingXu(-$Conf,$info))
      return false ;
      
    // kiem tra lan nay lan reset thu may ?
    $numReset = Common::getParam(Param::NumResetDailyQuest);
    if($this->ResetTimes >= $numReset)
      return false ;  
       
    // thuc hien reset 
    $this->UnlockQuest2 = false ;
    $this->CurrentQuest = 1 ;
    $this->LastUpdateDaily = 0 ;
    $this->ResetTimes++;
    $oUser->save();
    return true ;
  }

    /**
    * get gift daily quest
    * @author hieupt
    * 17/05/2011
    */
    
    public function getGift()
    {
    
        $idQuest = $this->CurrentQuest; 
        foreach($this->DailyInfo[$idQuest] as $idTask => $oTask)
        {
            $Level = $oTask['Level'];
        }
        
        $Level = $Level > 300 ? 300 : $Level ;
        $conf_gift = Common::getConfig('DailyQuestGift', $Level);
        
        $conf_gift = $conf_gift[$this->CurrentQuest];
        $arrGift = array();
        $arrGift['Sure'] = $conf_gift['Sure'];
        
        if (!is_array($conf_gift))
            return FALSE;  
            
        if ($this->CurrentQuest > 1){
          
            $arrLucky = array();
            foreach ($conf_gift['Lucky'] as $idgift => $arr_gift)
            {
                $arrLucky[$idgift] =  $arr_gift['Rate'];
            }
            /*
            // gioi han so luong Ring
            if($this->CurrentQuest > 2)
            {     
                $SwatNumb = DataRunTime::get('Ring');
                if($SwatNumb >= 1000)
                {
                    $arrLucky[5] = 0 ;
                }
            }   */
            
            $idLucky = Common::randomIndex($arrLucky);
            /*
            // gioi han so luong Ring
            if($this->CurrentQuest > 2 && $idLucky == 5 )     
                DataRunTime::inc('Ring',1);*/

            $arrGift['Lucky'][] = $conf_gift['Lucky'][$idLucky];
        }

        return $arrGift;
    
    }
    
    /**
    * update reset time once everyday
    * 
    */
    public function updateFirstTimeLogin(){
        $this->ResetTimes = 0;
    }
    
    
    
   	public static function getById($uId)
	{
		return DataProvider :: get($uId,'Quest') ;
	}


	public static function del($uId)
	{
		return DataProvider :: delete($uId, 'Quest') ;
	}
    
    public function rollbackSeriesquest($Level,$SeriesId,$questId = 1)
    {
        if(empty($SeriesId))
            return false ;
        if($this->QuestInfo[$SeriesId]['Status'] || empty($this->QuestInfo[$SeriesId]))
            return false ;
        $oldStep = $this->QuestInfo[$SeriesId]['Step'] ;
                
        // tao lai toan bo seriesquest
        unset($this->QuestInfo[$SeriesId]);
        
        $QuestConfig = & Common::getConfig('SeriesQuest') ;
        if(!is_array($QuestConfig))
        {
        return FALSE;
        }
        
        foreach($QuestConfig as $ids => $seri)
        {
            if(!is_array($seri) || ($ids != $SeriesId) )
            {
              continue;
            }
            if($_SERVER['REQUEST_TIME'] > $seri['ExpireDate'])
            {
              unset($this->QuestInfo[$ids]) ;
              continue;
            }
            
            if ($Level < $seri['LevelRequire'])
            {
               continue ;
            }

            $this->QuestInfo[$ids]['Status'] = FALSE ;
            $this->QuestInfo[$ids]['Id'] = $ids ;
            $this->QuestInfo[$ids]['Step'] = 1 ;
            foreach($seri['Quest'] as $idq => $quest)
            {
              if(!is_array($quest))
              {
                    continue;
              }
              $this->QuestInfo[$ids][$idq]['Status'] = FALSE ;
              $this->QuestInfo[$ids][$idq]['Id'] = $idq ;
              // neu ton tai Task
              if(is_array($quest['TaskList']))
              {
                foreach($quest['TaskList'] as $idt => $task)
                {
                 $this->QuestInfo[$ids][$idq][$idt]['Status'] = FALSE ;
                 $this->QuestInfo[$ids][$idq][$idt]['Num'] = 0 ;
                 $this->QuestInfo[$ids][$idq][$idt]['Id'] = $idt ;
                }
              }

            }
        }   
        // roll back 
        if($oldStep > $questId)    
            $this->QuestInfo[$SeriesId]['Step'] = $questId ;
        else
            $this->QuestInfo[$SeriesId]['Step'] = $oldStep ;

    }
    
    public function getLevelDailyQuest($LevelUser)
    {
      $arr_Level = Common::getParam('Level_DailyQuest');
      $L = 0 ;
      for($i =0 ; $i < count($arr_Level);$i++)
      {
        if ($LevelUser >= $arr_Level[$i])
        {
          $L +=1;
        }
      }
      if($L >= count($arr_Level)) $L = count($arr_Level)-1 ;
      if($L<1) $L = 1 ;
      return $L ;
      //  intval(($oUser->Level-1)/5)+1 ;      
    }

}
?>
