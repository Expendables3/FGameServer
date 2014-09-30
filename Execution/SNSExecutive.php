<?php
/**
*   Lop Executive cho cac service cua SNS FrameWork
*  @author ToanTN PHP GSN team
*  @copyright SNS PHP Team
*/

class Executive {
   
    var $_classConstruct;
   
    var $_methodname;

    var $_arguments;

    function Executive() {
    } 

    function doMethodCall(&$bodyObj, &$object, $method, $args) 
    {

        try
        {
            $output = Executive::deferredMethodCall($bodyObj, $object, $method, $args);
        }
        catch(Exception $fault)
        {
            $output = 'SNS Executive Call Erorr Try Again';
        }
        return $output;
    } 
    

    function buildClass(&$bodyObj, $className)
    {

        global $amfphp;
        if(isset($amfphp['classInstances'][$className]))
        {
            return $amfphp['classInstances'][$className];
        }
        
        try
        {
            $construct = new $className($className);
            $amfphp['classInstances'][$className] = & $construct;
        }
        catch(Exception $fault)
        {

            $construct = 'SNS Executive Call Error Try Again';
        }
        
        return $construct;
    }
    

    function deferredMethodCall(&$bodyObj, &$object, $method, &$args)
    { 
        try
        {
            if($object === NULL)
            {
                
                if(Common::getSysConfig('encrypt') && ($_SERVER['HTTP_USER_AGENT'] != "fish_jav_ser"))
                {
                    foreach($args as $index => $oArg)
                    {
                        $aa = $args[$index];
                        $len = strlen($aa);
                        $cc = strrev(substr($aa,0, $len-2));
                        $dd = substr($aa, $len-2,2);
                        $args2 = $cc.$dd;
                        
                        $args2 = base64_decode($args2); 
                        $args3 = json_decode($args2,true);
                        $args[$index] = $args3;
                    }
                }
                
                
                
                $output = call_user_func_array ($method, $args);
            }
            else
            { 
                //$oActionQuest = new ActionQuest(); 
                
                if(Common::getSysConfig('encrypt') && ($_SERVER['HTTP_USER_AGENT'] != "fish_jav_ser"))
                {
                    foreach($args as $index => $oArg)
                    {
                        $aa = $args[$index];
                        $len = strlen($aa);
                        $cc = strrev(substr($aa,0, $len-2));
                        $dd = substr($aa, $len-2,2);
                        $args2 = $cc.$dd;
                        
                        $args2 = base64_decode($args2); 
                        $args3 = json_decode($args2,true);
                        $args[$index] = $args3;
                    }
                }

                //Chan service khi tai khoan dang khoa hoa xin pha khoa
                $oUser = User::getById(Controller::$uId);
                $lockMethod = Common::getConfig('Param', 'LockMethod');
                $confUser = Common::getConfig('UserTest');
                if(Common::getSysConfig('maintain') && !in_array(Controller::$uId, $confUser))
                {                                      
                    $output = array('Error' => Error::LOGIN);  
                }
                else if(in_array($method, $lockMethod) && ($oUser->passwordState == PasswordState::IS_LOCK || $oUser->passwordState == PasswordState::IS_CRACKING 
                || $oUser->passwordState == PasswordState::IS_BLOCKED))
                {
                    $output = array('Error' => Error::NOT_UNLOCK);
                }
                else
                {                    
                    $output = call_user_func_array (array(&$object, $method), $args);
                }

                // thuc hien ko reset bien khi co loi 
                if($output['Error'] !== Error::SUCCESS)
                {
                    $oWorld = FishWorld::getById(Controller::$uId);
                    if(is_object($oWorld))
                    {
                        if($oWorld->IsInWorld)
                        {
                           $oWorld->ErrorFlag = 1 ;
                           $oWorld->save() ;
                        }
                       
                    } 
                }
                  
                  if($output['Error'] === Error::SUCCESS)
                  {
                    // thuc hien update quest
                    if($args[0]['IsQuest'] && get_class($object)!='QuestService')
                    {
                        $oQuest = Quest::getById(Controller::$uId);
                        //$oQuest->update($method,$args[0]);  

                        if (!is_object($oQuest)){
                            $oUser = User::getById(Controller::$uId);
                            $oQuest = new Quest(Controller::$uId,$oUser->Level);
                        }
                        $oQuest->update($method,$args[0],$output);  
                        $ooAct = ActionQuest::getInstance();
                        if ($args[0]['UserId'] == Controller::$uId)
                            $ooAct->value['NumMaterial'] = 0;
                        if (!($oQuest->CurrentQuest==2 && $oQuest->UnlockQuest2==FALSE)){
                            $oQuest->updateActionQuest($ooAct->value);
                        } 
                        $oQuest->save();
                    }
                     
                    if (Event::checkEventCondition('MagicPotion'))
                    {
                        $listAction = array('acttackMonster','acttackBoss','attackFriendLake','getGem','useGem','attackHerbBoss');                    ;
                        if(in_array($method,$listAction))
                        {     
                            $oHerb = EventMagicPotion::getById(Controller::$uId);
                            $oHerb->addHerbAction($output, $method);
                            $oHerb->save();

                        }    
                    }
                    
                    $actPowerTinhQuest = array('sendGift','getGiftDay','completeDailyQuest','completeDailyQuest','payToResetDailyQuest');
                    if (in_array($method,$actPowerTinhQuest))
                    {
                        $oPowerQuest = PowerTinhQuest::getById(Controller::$uId);
                        $oPowerQuest->updateAction($output,$method);
                        $oPowerQuest->save();
                    }
                    
                    // he thong uy danh
                    if (Event::checkEventCondition('ReputationQuest'))
                    {
                        {   
                            $oUser = User::getById(Controller::$uId);
                            $oUser->updateReputationQuest($output, $method,$args[0]);
                            $oUser->save();   
                          
                        }           
                    }
 
                    // buoc nhung giao dich bang xu phai duoc luu ngay xuong DB
                    if(User::$agent) 
                    {
                        StaticCache::forceSaveAll();
                        User::$agent = false ;
                    }
                    
                  }
                  ActionQuest::getInstance()->resetAction(); 
                  

            }
        }
        catch(Exception $fault)
        {

            $output = 'SNS Executive Call Error Try Again';     
        }
        return $output;
    }
    
    

    function includeClass(&$bodyObj, $location)
    {
        $included = false;
        try
        {
            include_once($location);
            $included = true;
        }
        catch(Exception $fault)
        {
    
        }
        return $included;
    }
    
    /**
    * affter action success, check other condition for get more bonus
    * 
    * @param mixed $Key
    * @param mixed $IdAction
    * @param mixed $params
    * @param mixed $outputAction
    */
    function checkOtherConditionActionSuccess($Key, $IdAction, $params, $outputAction)
    {     
         switch($Key)
         {
             case EventType::EURO:
                switch($IdAction)
                {
                    case 2:
                    case 3:
                    case 5:
                        return ($outputAction['isWin'] == 1) ? true : false; 
                        break;
                    case 1:
                    case 4:
                    case 6:
                       return ($outputAction['Num'] > 0) ? true : false; 
                        break; 
                    default:
                        return false;
                }                
                
                break;
         }
    }
    
    /**
    * add more bonus, extra bonus of action
    * 
    * @param mixed $outputAction
    * @param mixed $Key
    * @param mixed $action
    * @param mixed $bonus
    */
    function addExtraBonusAction($outputAction, $Key, $IdAction, $bonus)
    {
        // add Bonus
        $output = $outputAction;
        switch($Key)
        {         
            case EventType::EURO:
                switch($IdAction)
                {
                    case 2:
                    case 3:
                        $output['Bonus']['100'][$Key][] = $bonus;
                        break;
                    default:
                        $output['Bonus'][] = $bonus;
                        break;
                }
                Zf_log::write_act_log(Controller::$uId, 0, 20, 'bonusActionEuroBall', 0, 0, EventEuro::EURO_BALL_TYPE, $bonus['ItemId'], $bonus['Num'], $IdAction);    
                break;
        }
        
       return $output; 
    }
    
    function addLimitExtraBonusAction($Key, $bonus)
    {
        switch($Key)
        {
            case EventType::EURO:
                if($bonus['ItemId'] == EventEuro::EURO_BETTYPE_VIP)
                {
                    $oEvent = Event::getById(Controller::$uId);
                    $Num = $oEvent->euro_addLimitVipBall($bonus['Num']);
                    $oEvent->save();
                    return $Num;
                } 
                break;
        }
        //default
        return $bonus['Num'];
    } 
} 
?>
