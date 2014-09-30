<?php


/**
 * @author AnhBV
 * @version 1.0
 * @created 26-10-2010
 * @Description : thuc hien viec xu ly Quest
 */
class QuestService extends Controller
{
     
    /**
    * complete daily quest
    *  
    */          
    public function completeDailyQuest()
    {

        // kiem tra user
        if (!self :: $uId)
        {
            return array('Error' => Error :: LOGIN) ;
        }
        
        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        // luu thong so truoc khi update cua user
        $uMoney = $oUser->Money;
      	$uZMoney = $oUser->ZMoney;
        
        $Quest = Quest::getById(Controller :: $uId) ;
        if(!is_object($Quest))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }

        $idQuest = $Quest->getCurrentQuest();
        if ($idQuest < 1 || $idQuest >3)
            return array('Error' => Error::ID_INVALID);
        
        // kiem tra trang thai cua quest va update trang thai
        $result = array();
        
        if ($idQuest==2 || $idQuest==3){
            if ($Quest->UnlockQuest2==FALSE) 
                return array('Error' => Error::QUEST_NOT_COMPLETE);
        } 
        if ($Quest->checkDailyQuestComplete()==FALSE)
            return array('Error' => Error::NOT_COMPLETE_TASK);   
        
        // Nhan qua
        $result['Gift'] = $Quest->getGift();
        $result['QuestId'] = $Quest->getCurrentQuest();
        if (!is_array($result['Gift']))   
            return array('Error' => Error::NOT_LOAD_CONFIG);
        
        //save bonus
        $oUser->saveBonus($result['Gift']['Sure']);
        
        $gift = Common::addsaveGiftConfig($result['Gift']['Lucky'],rand(1,5),SourceEquipment::DAILYQUEST);

        $result['Gift']['Lucky'] = array();
        if(!empty($gift))
            foreach($gift as $type => $arr)
            {
                if(empty($arr)) continue ;
                foreach($arr as $Id => $arr_g)
                {
                    if(empty($arr_g)) continue ;
                    
                    $result['Gift']['Lucky'][] = $arr_g;
                }
            }
            
        unset($Quest->DailyInfo[$idQuest]);
        
        $Quest->CurrentQuest++;     
        
        $result['Error'] = Error :: SUCCESS  ;
        $oUser->save();
        $Quest->save();
        
        //log

        // luu thong so truoc khi update cua user
        $difMoney = $oUser->Money - $uMoney;
      	$difZMoney = $oUser->ZMoney - $uZMoney;
        
        Zf_log::write_act_log(Controller :: $uId,0, 20, 'completeDailyQuest', $difMoney,$difZMoney,$idQuest);    
        
        return $result;
   
    }
     

    public function getSeriesQuest()
    {   
        if (!self :: $uId)
        {
            return array('Error' => Error::LOGIN) ;
        }
        
        $oUser = User :: getById(self :: $uId) ;
        if (!is_object($oUser))
        {
            return  array('Error' => Error::NO_REGIS) ;
        }

        
        $oQuest = Quest::getById(Controller::$uId);
        if(!is_object($oQuest))
        {
            $oQuest = new Quest(Controller::$uId,$oUser->Level);
        }
        $runArr = array();
        // check new series quest
        $oQuest->checkNewQuest($oUser->Level,false,false);
        $runArr['QuestList'] = $oQuest->getQuestActive();
        $runArr['QuestInfo']['ElementMainQuest'] = $oQuest->ElementMainQuest;
        $oQuest->save();
        $runArr['Error'] = Error :: SUCCESS  ;
        return $runArr ;
    }

    
     
    public function getDailyQuest($param)
    {

        $IsView = $param['IsView'];
       
        $oUser = User::getById(Controller::$uId) ;
        if (!is_object($oUser))
        {
            return  array('Error' => Error::NO_REGIS) ;
        }
        
        $oQuest = Quest::getById(Controller::$uId);

        $runArr = array();
        if(!is_object($oQuest))
        {
            $oQuest = new Quest(Controller::$uId,$oUser->Level);
            $runArr['New'] = 'New';
        }

		    $oUserProfile = UserProfile::getById(Controller::$uId);
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        
        $Lastday =  date('Ymd', $oQuest->LastUpdateDaily);

        if ($Today != $Lastday)
        {
            $oUserProfile->NewDailyQuest = TRUE ;
            $oQuest->newDailyTask($oUser->Level);
        }

        if ($IsView == True)
        {
            $oUserProfile->NewDailyQuest = FALSE ;
        }
        

        ////
        $oQuest->save();
        $oUserProfile->save();
        
        $runArr['DailyQuest'] = $oQuest->DailyInfo ;
        $runArr['UnlockQuest2'] = $oQuest->UnlockQuest2;
        $runArr['CurrentQuest'] = $oQuest->CurrentQuest;
        $runArr['ResetTime'] = $oQuest->ResetTimes;
        $runArr['Error'] = Error :: SUCCESS  ;

        // log
        //Zf_log::write_act_log(self::$uId, 0, 20, 'getDailyQuest', 0, 0 );

        return $runArr;
    }
    
    /**
    * Hoan thanh quest nhanh bang xu
    *  {"IdTask":1}
    */
     
    public function doneByXu($param){
        
        $idTask = $param['IdTask'];
        
        if(empty($idTask))
        {
            return array('Error' => Error :: PARAM) ;
        }
        // kiem tra user

        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
         
        $oQuest = Quest::getById(Controller::$uId);
        if (!is_object($oQuest))
            return array('Error' => Error::OBJECT_NULL);
            
         // thong so truoc khi thay doi
        $zmoney = $oUser->ZMoney ;
         
        $err = $oQuest->doneByXu($idTask);
        
        if($err['Error'] != Error::SUCCESS)
            return $err['Error'] ;
            
        $oQuest->save();
        
        // thong so truoc khi thay doi
     	$difzmoney = $oUser->ZMoney - $zmoney;
        //log
        Zf_log::write_act_log(self::$uId, 0, 23, 'doneByXu', 0,$difzmoney, $idTask);
         
        $result = array();
        $result['Error'] = $err['Error'];
        return $result;
    }

    public function unlockByXu(){

        // kiem tra user

        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
         
        
        $oQuest = Quest::getById(Controller::$uId);
         
        if (!is_object($oQuest))
        return array('Error' => Error::OBJECT_NULL);
		
         // thong so truoc khi thay doi
        $zmoney = $oUser->ZMoney ;
        
        $err = $oQuest->unlockQuestByXu();
        $oQuest->save();    
        //Log
        // thong so truoc khi thay doi
     	$difzmoney = $oUser->ZMoney - $zmoney;
     	
        $conf_param = & Common ::getParam();
        Zf_log::write_act_log(Controller :: $uId, 0, 23, 'unlockByXu', 0,$difzmoney, 2 );
            
        return array('Error' => $err);
         
    }
    
    public function payToResetDailyQuest()
    {
        
        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }
        
        $oQuest = Quest::getById(Controller::$uId);    
        if (!is_object($oQuest))
        return array('Error' => Error::OBJECT_NULL);
        
         // thong so truoc khi thay doi
        $zmoney = $oUser->ZMoney ;
        
        // neu van con quest 
        if(!$oQuest->payToResetDaily())
        {
          return array('Error' => Error :: ACTION_NOT_AVAILABLE) ;   
        }
        $oQuest->save();
        
        // log
        // thong so truoc khi thay doi
        $difzmoney = $oUser->ZMoney - $zmoney;
        Zf_log::write_act_log(Controller :: $uId, 0, 23, 'resetDailyQuest', 0,$difzmoney,$oUser->Level); 
        
        return array('Error' => Error :: SUCCESS) ;   
        
    }
    
    public function completeQuest($param)
    {
        // kiem tra dau vao
        $SeriesQuestId  = $param['SeriesQuestId'];
        $QuestId        = $param['QuestId'];
        $TaskId         = $param['TaskId'];
        $Param        = $param['Param'];
        
        if(empty($SeriesQuestId))
        {
            return array('Error' => Error :: PARAM) ;
        }
        // kiem tra user
        if(!self::$uId)
        {
            return  array('Error' => Error ::LOGIN) ;
        }
        

        $oUser = User :: getById(Controller :: $uId) ;
        if (!is_object($oUser))
        {
            return array('Error' => Error :: NO_REGIS) ;
        }

        
        $series = Quest::getById(Controller :: $uId) ;
        if(!is_object($series))
        {
            return array('Error' => Error :: OBJECT_NULL) ;
        }
        // check status of quest
        if(!empty($TaskId)&& !empty($QuestId))
        {
            $return_status = $series->checkStatusTask($SeriesQuestId,$QuestId,$TaskId);
        }
        else if(!empty($QuestId))
        {
            $return_status = $series->checkStatusQuest($SeriesQuestId,$QuestId);
            if($return_status == Error :: SUCCESS)
            {
                $series->QuestInfo[$SeriesQuestId]['Step'] +=1 ;
                $step = $series->QuestInfo[$SeriesQuestId]['Step'] ;
                if(!is_array($series->QuestInfo[$SeriesQuestId][$step]))
                {
                   $series->QuestInfo[$SeriesQuestId]['Status'] = true;
                    $series->checkNewQuest($oUser->Level,false, false);  
                }
            }
        }
        else
        {
            $return_status = $series->checkComplete($SeriesQuestId);
            if($return_status == Error :: SUCCESS)
            {
                $series->checkNewQuest($oUser->Level,false, false);
            }
        }
        // luu lai trang thai cua cac quest
        $series->save();
        $Arr_Gift = array();
        $Arr_Gift['GiftList'] = '';
        $Arr_Gift['QuestList'] = '';
        
        // thong so truoc khi thay doi
        $money = $oUser->Money ;
        $zmoney = $oUser->ZMoney ;
        
     if($return_status === Error :: SUCCESS )
     {
         // gui lai qua cho client
         $oUser = User::getById(self::$uId);
         $oStore = Store::getById(Controller::$uId); 
         
         $Arr_Gift =  $series->loadGift($SeriesQuestId,$QuestId,$TaskId);                                                                                                      
         if($Arr_Gift['Error'] !== Error::SUCCESS)
         {
            return array('Error' => Error :: ACTION_NOT_AVAILABLE) ;   
         }
         
         $giftAddedForClient = array();
         
         foreach ($Arr_Gift['GiftList'] as $index =>$gift)
         {
              if($gift['ItemType'] == Type::Soldier)
              {
                  // random 1 element
                  if(!empty($Param['Element']))
                  {
                      $gift['Element'] = (($Param['Element'] > 0) && ($Param['Element'] <6) ? $Param['Element'] : rand(1,5)); 
                      $series->ElementMainQuest = $Param['Element'] ; 
                  }
                  else
                  {
                      $gift['Element'] = (empty($series->ElementMainQuest)) ? rand(1,5) : $series->ElementMainQuest ;
                  }
         
                  $gift['RecipeType'] = (empty($gift['RecipeType'])) ? FormulaType::Draft : $gift['RecipeType'];
                          
                  // adjust User save structure;                          
                  $Arr_Gift['GiftList'][$index] = $gift;                                           
                  continue ;
              }             
              
              if($gift['ItemType'] == 'StampPack')
              {
                  $StampIds = array(1,2,3,4,5);
                                    
                  foreach($StampIds as $id)
                  {
                      $oStore->addItem(Type::ItemCollection, $id, 5);
                  }
                  
                  $oStore->save();
                  unset($Arr_Gift['GiftList'][$index]); 
                  continue ;
              }
              
              $AvailableSet = Common::getConfig('General', 'TypeSet', 'AvailableSet');
              if(in_array($gift['ItemType'],$AvailableSet))
              {
                  $Element = (($Param['Element'] > 0) && ($Param['Element'] <6) ? $Param['Element'] : $series->ElementMainQuest);
                  $oEquip = Common::randomEquipment($oUser->getAutoId(),$gift['Rank'], $gift['Color'], SourceEquipment::MAINQUEST,$gift['ItemType'], '', $Element);
                  $oStore->addEquipment($gift['ItemType'],$oEquip->Id,$oEquip);
                  $oStore->save();
                  
                  unset($Arr_Gift['GiftList'][$index]);
                     
                  $giftAddedForClient[]= array(
                        'ItemType' => $gift['ItemType'],
                        'ItemId' => $oEquip->Id,
                        'Num'   => 1,
                        'Equip' => $oEquip,
                  );
                  continue;
              }
              
         }
         $oUser->saveBonus($Arr_Gift['GiftList']);
         
         // add bonus for Condition next quest
                           
         if(($SeriesQuestId == MainQuest::Gem) && ($QuestId == 1) && ($TaskId == 0))
         {
             $element = $series->ElementMainQuest ;
             $idGem = ($element == 2) ? 5 : $element;
             
             $oStore->addGem(intval($idGem),0,intval(7),intval(10)); 
             $oStore->Gem['LastUpdateTime'] = $_SERVER['REQUEST_TIME'];
             $oStore->save();             
         }
         
         foreach($giftAddedForClient as $gift)
         {
             $Arr_Gift['GiftList'][] = $gift; 
         }
         
         $Arr_Gift['QuestList'] = $series->getQuestActive();

         $oUser->save();
         $series->save();
     }
     else
     {
         return array('Error' => $return_status) ;
     }
     $Arr_Gift['Error']= Error :: SUCCESS ;

     //Log
     // thong so truoc khi thay doi
     $difmoney = $oUser->Money - $money;
     $difzmoney = $oUser->ZMoney - $zmoney;

     if (isset($Arr_Gift['QuestList'][1]) && $Arr_Gift['QuestList'][1]['Status']== TRUE )
     {
         Zf_log::write_act_log(self::$uId, 0, 13,'completeSeriesQuest_1', $difmoney, $difzmoney, 2, $oUser->Level);
     }
     else 
     {
         Zf_log::write_act_log(self::$uId, 0, 20,'completeQuest', $difmoney, $difzmoney, $SeriesQuestId, $QuestId, $TaskId);
     }

     // return Element
     $Arr_Gift['QuestInfo']['ElementMainQuest'] = $series->ElementMainQuest; 
     
     return $Arr_Gift;
    }

};

?>
