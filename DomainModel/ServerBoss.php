<?php
  class ServerBoss extends Model
  {
      public $BossList = array();
      public $JoinLastTime = 0 ;
      public function __construct($uId)
      {
          parent::__construct($uId);     
      }
      
      
      // create boss
      public function sql_createBoss($day,$time,$bossid,$Vitality)
      {Debug::log("CreateBoss:$day--$time--$bossid--$Vitality");
          if(empty($day) ||$time < 10000 ||$time > 240000||$bossid < 1 ||$bossid > 4 || $Vitality <=0 )
            return array('Error'=> Error::PARAM);  
            
          $query = sprintf('call CreateBoss("%s",%d,%d,%d, @error)',$day,$time,$bossid,$Vitality);

          try
          { 
                $Data = Common::queryMySql('ServerBoss',$query,'');   
                if(!$Data)
                    return array('Error'=> Error::PARAM);                 
                $Data_1 = Common::queryMySql('ServerBoss','Select @error as ERROR','');  

                while($row = mysql_fetch_array($Data_1, MYSQL_ASSOC))
                {
                 $error = $row['ERROR'];
                }     
          }
          catch(Exception $e)
          {
          }

          if($error != Error::SUCCESS)
            return array('Error'=> $error);

          return array('Error'=> Error::SUCCESS);        
      }
      
      // attack boss    
      //_uid int, _day varchar(45) ,_time int, _bossid int, _damage int , OUT _vitalitytotal int, OUT _lasthit boolean,out _error int, out _DamTotal int)
      public function sql_attackBoss($day,$time,$bossid,$damage)
      {
          if(empty($day) ||$time < 10000 ||$time > 240000||$bossid < 1 ||$bossid > 4 || $damage <=0 )
            return array('Error'=> Error::PARAM);   
             
          $uId = Controller::$uId ;
          $Vitalitytotal = 0 ;
          $LastHit = false ;
          $Error = 0 ;
          $DamTotal = 0 ;
          $arr = array(); 
          //$query = "call AttackBoss($uId,$day ,$time,$bossid, $damage,@VitalityTotal,@LastHit,@Error,@DamTotal)";
          $query = sprintf('call AttackBoss(%d,"%s",%d,%d,%d,@VitalityTotal,@LastHit,@Error,@DamTotal)',$uId,$day ,$time,$bossid, $damage);
          try
          {
                $Data = Common::queryMySql('ServerBoss',$query,'');       
                if(!$Data)
                    return array('Error'=> Error::PARAM);
                $Data_1 = Common::queryMySql('ServerBoss','Select @VitalityTotal,@LastHit,@Error,@DamTotal ','');  
                while($row = mysql_fetch_array($Data_1, MYSQL_ASSOC))
                {
                 $arr['VitalityTotal']  = $row['@VitalityTotal'] ;
                 $arr['LastHit']        = $row['@LastHit'] ;   
                 $arr['DamTotal']       = $row['@DamTotal'] ;   
                 $arr['Error']          = $row['@Error'] ;   
                }     
          }
          catch(Exception $e)
          {
              
          }
          return $arr ;
      }
      
      // get top user and boss
      public function sql_getTopUser($day,$time,$bossid)
      {
            if(empty($day) ||$time < 10000 ||$time > 240000||$bossid < 1 ||$bossid > 4)
                return array('Error'=> Error::PARAM);     
            $query = sprintf('select UserId,DamageTotal from BossServer_Attack where to_days("%s") = Date and %d = Time and %d = BossId ORDER BY DamageTotal Desc limit 100;',$day,$time,$bossid);
            
           // $query = 'select UserId,DamageTotal from BossServer_Attack where to_days($day) = Date and $time = Time and $bossid = BossId ORDER BY DamageTotal Desc limit 100;' ;
            try
            {
              $Data = Common::queryMySql('ServerBoss',$query,'');     
              if(!$Data)
                return array('Error'=> Error::PARAM);
            }
            catch(Exception $e)
            {
              
            }
            $Position = -1 ;
            $TopUser = array();
            $count = 1 ;
            while($row = mysql_fetch_array($Data, MYSQL_ASSOC))
            {
                if(!empty($row))
                {
                    $TopUser[$count]['DamageTotal'] =  $row['DamageTotal'] ;
                    $TopUser[$count]['UserId'] =  intval($row['UserId']);
                    if($row['UserId'] == Controller::$uId)
                        $Position = $count; 
                }
                else
                {
                    continue ;
                }
                $count++ ;
            }
            $result['Error'] = Error::SUCCESS ;
            $result['TopUser'] = $TopUser ;
            $result['Position'] = $Position ;
            //Debug::log('top user ne ne ne');
            //Debug::log($result) ;
            return $result ;
          
      }

      
      // get vitality boss
      public function sql_getVitalityBoss($day,$time,$bossid)
      {
            if(empty($day) ||$time < 10000 ||$time > 240000||$bossid < 1 ||$bossid > 4)
                return array('Error'=> Error::PARAM);     
            
            //$query = "call getTopUserInServerBoss($day,$time,$bossid);" ;
            //$query = "select BossId,VitalityTotal from BossServer_Boss where to_days($day) = Date and $time = Time and $bossid = BossId ;" ;
            $query = sprintf('select BossId,VitalityTotal from BossServer_Boss where to_days("%s") = Date and %d = Time and %d = BossId ;',$day,$time,$bossid);
            
            try
            {
              $Data = Common::queryMySql('ServerBoss',$query,'');     
              if(!$Data)
                return array('Error'=> Error::PARAM);
            }
            catch(Exception $e)
            {
              
            }
            $result = array();
            $result['VitalityTotal'] = -1 ;
            while($row = mysql_fetch_array($Data, MYSQL_ASSOC))
            {
                if($row['BossId'] <= 0)
                    return array('Error'=> Error::NOT_ACTION_MORE); 
                          
                $result['VitalityTotal'] =  intval($row['VitalityTotal']) ;
            }
            $result['Error'] = Error::SUCCESS ;
           
            return $result ;
          
      }
                                                           
    // chuyen thoi gian tu dang string sang sang times unix
    public static function checkTime($KeyBoss = '') 
    {
        $checkTime = intval(date('His',$_SERVER['REQUEST_TIME']));          
        
        $conf_time = Common::getParam('ServerBoss','JoinTime');
        
        $time = 0 ;
        if(empty($KeyBoss))
        {
            foreach($conf_time as $index => $arr_t)
            {
                
                $begin = intval(str_replace('-','',$arr_t['BeginTime']));  
                $end   = intval(str_replace('-','',$arr_t['EndTime']));     
                //Debug::log('check1'.$begin.'_'.$checkTime.'_'.$end);
                if($checkTime < $begin || $checkTime > $end)
                    continue ;
                $time = $begin ;
            }
        }
        else
        {
            $ext = explode('_',$KeyBoss);
            $Time = intval($ext[3]);
            $BossId = $ext[4];
            
            foreach($conf_time as $index2 => $arr_t1)
            {
                $begin = intval(str_replace('-','',$arr_t1['BeginTime']));  
                $end   = intval(str_replace('-','',$arr_t1['EndTime']));
                if($begin !== $Time)
                    continue ; 
                //Debug::log($Time.'check_2:'.$begin.'_'.$checkTime.'_'.$end);
                if($checkTime < $begin || $checkTime > $end)
                    continue ;
                $time = $begin ;
            }
        }
        return $time ;
    }
    
    // chuyen thoi gian tu dang string sang sang times unix
    public static function checkTime111($confBeginTime,$confEndTime,$inputTime) 
    {
        $checkTime = date('His',$_SERVER['REQUEST_TIME']);          
        
        $conf_time = Common::getParam('JoinTime');
        if(empty($confBeginTime) || empty($confEndTime) ||empty($inputTime))
            return false ;
        
        $begin = intval(str_replace('-','',$confBeginTime));  
        $end   = intval(str_replace('-','',$confEndTime));     
        $now   = intval(str_replace('-','',$inputTime));     
        
        if($now < $begin || $now > $end)
            return false ;
        return true ;    
        /*
        $begin = explode('-',$confBeginTime);  
        $end   = explode('-',$confEndTime);  
        $now   = explode('-',$inputTime);  

        if($begin[0] == $end[0])  // neu 2 h = nhau 
        {
            if($now[0] == $begin[0])
            {
                if($now[1] < $begin[1] || $now[1] > $end[1])  // kiem tra phut
                    return false ;   
            }
            else
                return false ;
                
                   
        }
        else if($begin[0] < $end[0])
        {
            // kiem tra gio
            if($now[0] < $begin[0] || $now[0] > $end[0])
                return false ; 
                
            if($now[0] == $begin[0])
            {
                 if($now[1] < $begin[1])  // kiem tra phut
                    return false ;
            }
            elseif($now[0] == $end[0])
            {
                 if($now[1] > $end[1])   // kiem tra phut   
                    return false ;
            }                 
            
            
        }
        else 
            return false ;
        
        return true ;    */

    }
    
    // check dieu kien ton tai Event 
    public static function checkEventCondition()
    {
      $Today = $_SERVER['REQUEST_TIME'];
      $Eventconf = Common::getParam('ServerBoss');
      $oUser = User::getById(Controller::$uId);
      if(!is_array($Eventconf) || !is_object($oUser))
        return false ;
      $Begin        = $Eventconf['BeginTime'];
      $Expired      = $Eventconf['ExpireTime'];
      $BeginLevel   = $Eventconf['BeginLevel']; 
      $EndLevel     = $Eventconf['EndLevel']; 
      return (($oUser->Level >= $BeginLevel)&&($oUser->Level < $EndLevel)&&($Today<$Expired)&&($Today>$Begin));
    }
    
    public static function getById($uId)
    {
        $object = DataProvider :: get($uId,__CLASS__) ;
        if(!is_object($object))
        {
        $object = new ServerBoss($uId);
        $object->save();
        }       
        return $object ;
    }
    
    // check damage total
    
    public function getTotalDamage()
    {
        // kiem tra soldier 
        $arr_lake = Lake::getAllSoldier(Controller::$uId,true,true,true);
        
        //Debug::log($arr_lake);
        $listIndex = Common::getParam('SoldierIndex');  
        $oStoreEquip = StoreEquipment::getById(Controller::$uId);  
        $TotalDam = 0 ;
        foreach($arr_lake as $lakeId => $arrSoldier)
        {
            foreach($arrSoldier as $id => $oSoldier)
            {
                // get all index 
                //$attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());  
                if ($oSoldier->Status!=SoldierStatus::HEALTHY)
                    continue; 
                     
                $oStoreEquip->updateBonusEquipment($oSoldier->Id) ;            
                $arrIndex = array();      
                foreach($listIndex as $name)
                {
                  // get from base + equipment
                  if (isset($oStoreEquip->SoldierList[$oSoldier->Id]['Index'][$name]))
                    $arrIndex[$name] += $oSoldier->$name + $oStoreEquip->SoldierList[$oSoldier->Id]['Index'][$name];
                  else $arrIndex[$name] += $oSoldier->$name;
                  
                  //get from meridian 
                  if(!empty($oStoreEquip->listMeridian[$oSoldier->Id][$name]))
                  {
                      $arrIndex[$name] += $oStoreEquip->listMeridian[$oSoldier->Id][$name];
                  }
                  
                  // them Reputation buff
                  $ReputationBuff = $oSoldier->getReputationBuff(Controller::$uId);
                  if(!empty($ReputationBuff[$name]))
                    $arrIndex[$name] += $ReputationBuff[$name] ;
                  
                }
                $TotalDam += ceil($arrIndex['Damage']+ $arrIndex['Defence'] + $arrIndex['Critical'] + $arrIndex['Vitality']/3);                 
            }
        }
        
        return $TotalDam ;
    }
    
    public function addBoss($KeyBoss)
    {
        if(empty($KeyBoss))
            return false ;

        //$keyBoss = date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.$BossId ;
        $ex = explode('_',$KeyBoss);
        $day     = substr($KeyBoss,0,10);            //Y_m_d
        $time    = intval($ex[3]);     //H
        $bossid  = intval($ex[4]);     
        $bossInfo = array()  ;
        $bossInfo = DataRunTime::get('BossInfo'.$KeyBoss,true); 
        // Debug::log($bossInfo);
        if(empty($bossInfo)) // boss chua duoc khoi tao
        {   
           // khoi tao boss in mysql
           $conf = Common::getConfig('ServerBossInfo',$bossid);
           $vitality = $conf['Vitality'] ;
           if(empty($conf)|| empty($vitality))
                return false ;
           //Debug::log($KeyBoss);        
           $result = $this->sql_createBoss($day,$time,$bossid,$vitality);
           if($result['Error'] == Error::SUCCESS)
           {
                $Info = array();
                $Info['IsDie']        = false ;
                $Info['Vitality']     = $vitality ;

                $set_result = DataRunTime::set('BossInfo'.$KeyBoss,$Info,true);  
                if(!$set_result)
                {
                    //Debug::log('set boss xuong membase bi false');
                    return false ;
                }
                    
                // add in membase
                $this->BossList[$KeyBoss]= array();
                $this->BossList[$KeyBoss]['Dice']= 0;   // loai xuc xac  1
                $this->BossList[$KeyBoss]['SpecialDice'] = 0 ;  // loai xuc xac  2         
                $this->BossList[$KeyBoss]['DamTotal']= 0; // tong dam danh boss
                $this->BossList[$KeyBoss]['IsGetBonus']= false; // da nhan qua hay chua
                $this->BossList[$KeyBoss]['Position']= -1;
                $this->BossList[$KeyBoss]['LastTimeAttack'] = 0;
                $this->BossList[$KeyBoss]['AttackNum'] = 0; // so lan danh boss
           }
           else if($result['Error'] == Error::BOSS_IS_EXIST)
           {
                // add in membase
                $this->BossList[$KeyBoss]= array();
                $this->BossList[$KeyBoss]['Dice']= 0;   // mat xuc xac
                $this->BossList[$KeyBoss]['SpecialDice'] = 0 ;
                $this->BossList[$KeyBoss]['DamTotal']= 0; // tong dam danh boss
                $this->BossList[$KeyBoss]['IsGetBonus']= false; 
                $this->BossList[$KeyBoss]['Position']= -1; 
                $this->BossList[$KeyBoss]['LastTimeAttack'] = 0;
                $this->BossList[$KeyBoss]['AttackNum'] = 0; 
           }
           else
                return false ;
                
       }
       else if(isset($bossInfo) && $bossInfo['IsDie'] == false)
       {
           // add in membase
            $this->BossList[$KeyBoss]= array();
            $this->BossList[$KeyBoss]['Dice']= 0;   // mat xuc xac
            $this->BossList[$KeyBoss]['SpecialDice'] = 0 ;
            $this->BossList[$KeyBoss]['DamTotal']= 0; // tong dam danh boss
            $this->BossList[$KeyBoss]['IsGetBonus']= false;  
            $this->BossList[$KeyBoss]['Position']= -1; 
            $this->BossList[$KeyBoss]['LastTimeAttack'] = 0;
            $this->BossList[$KeyBoss]['AttackNum'] = 0; 
       }
       else
       {
           return false ;
       }
       
       return true ;
        
    }
    
    //
    public  function generateScene($listSoldierAtt,$soldierDef,$isResistance = 1,$alpha = 0,$Dice = 0)
    {
          
           $scene = array();
           $conf_health = Common::getConfig('RankPoint');
           $conf_Turn = Common::getParam('ServerBoss','TurnAttackMax');
           $turn = 0;
           
           // chance to attack first
           $attackFirst = rand(0,1);
            
           // Attack list constant
           $attackIndex = array();
           foreach($listSoldierAtt as $id => $oSoldier)
           {
               // get all index 
               $attackIndex[$id] = $oSoldier->getIndex($oSoldier->getUserId());
               $attackIndex[$id]['CurrentHealth'] = $oSoldier->getHealth();
               $attackIndex[$id]['MaxHealth'] = $oSoldier->getMaxHealth();
               
               // get rank different
               $indexRank = $oSoldier->Rank - $soldierDef->Rank;
               if ($indexRank > 10)
                  $indexRank = 10;
               else if ($indexRank < -25)
                  $indexRank = -25;
               $attackIndex[$id]['LevelIndex'] = $indexRank;
           }
           
           // defence constant
           $defenceIndex = $soldierDef->getIndex();
           $defenceIndex['CurrentHealth'] = $soldierDef->getHealth();
           $defenceIndex['MaxHealth'] = $soldierDef->getMaxHealth();
           
           // turn 0: initial vitality
           $scene[$turn]['attackFirst'] = $attackFirst;
           foreach($listSoldierAtt as $id => $oSoldier)
           {               
               $scene[$turn]['Vitality']['Attack'][$id] = $attackIndex[$id]['Vitality'];
           }
           $scene[$turn]['Vitality']['Defence']['Left'] = $defenceIndex['Vitality'];
           
           
                      
           $conf_ele = Common::getParam('Elements');
           //$criticalThreshold = Common::getParam('CriticalThreshold');
           $conf_hit = Common::getConfig('ChanceToHit');
           
           $DamageTotal =  0 ; 
           
           // loop until max turn
           while($turn< $conf_Turn['Max'])
           {
                $turn++;                      
                $total = 0;

                // select my soldier to defence
                if (!is_array($listSoldierAtt))
                    break;
                $idVictim = array_rand($listSoldierAtt,1);

                $scene[$turn]['Vitality']['Defence']['Left'] = $scene[$turn-1]['Vitality']['Defence']['Left'];
                                
                // Attack turn         
                foreach($listSoldierAtt as $id => $oSoldier)
                {  
                    // calculate index 

                    // check resistance when soldier attack
                    foreach($oSoldier->BuffItem as $id1 => $oItem)
                    { 
                        if ($oItem[Type::ItemType] == BuffItem::Resistance)  
                        {
                            $isResistance = 0;
                        }    
                    }                  

                    $attTurn = SoldierFish::attackByTurn($oSoldier->Element,$soldierDef->Element,$conf_hit[$attackIndex[$id]['LevelIndex']],$attackIndex[$id]['Critical'],$isResistance) ;
                    if ($attTurn['Miss'])     // if miss
                    {
                       $DamageAttack = 0; 
                       $scene[$turn]['Status']['Attack'][$id] = SceneAttack::MISS;
                    }
                    else {
                        $vitalityDamage = SoldierFish::getVitalAttack($attackIndex[$id],$defenceIndex, $attTurn['Conflict'],$alpha);    
                        $DamageAttack = round($vitalityDamage['Damage'] + ($vitalityDamage['Damage']*$Dice)/100 );

                    }

                    // update scene param
                    if($turn < $conf_Turn['Max'])
                    {
                        $DamageTotal += $DamageAttack ;
                        $scene[$turn]['Vitality']['Defence']['Left'] -= $DamageAttack;
                        $scene[$turn]['Vitality']['Defence']['ListAtt'][$id] = $DamageAttack;
                    }
                                      
                    if ($scene[$turn]['Vitality']['Defence']['Left'] < 0)
                    {
                       // if do not reach min turn, fake data
                       if ($turn < $conf_Turn['Min'])
                       {
                            $curVital = $scene[$turn-1]['Vitality']['Defence']['Left'];
                            $scene[$turn]['Vitality']['Defence']['Left'] = ceil($curVital/2);
                            $scene[$turn]['Vitality']['Defence']['ListAtt'][$id] = ceil(($curVital-1)/2);    
                       }
                       else $scene[$turn]['Vitality']['Defence']['Left'] = 0;
                    }

                                      
                    if (!$attTurn['Miss'])
                    {
                       if ($vitalityDamage['Critical'] == 2)
                            $scene[$turn]['Status']['Attack'][$id] = SceneAttack::CRITICAL;
                       else $scene[$turn]['Status']['Attack'][$id] = SceneAttack::NORMAL;
                    }

                } 
                // end attack list
                
                if($turn < $conf_Turn['Max'])
                {
                                 
                   // Defence turn
                   $defIndex = -$attackIndex[$idVictim]['LevelIndex'];
                   if ($defIndex > 10)
                      $defIndex = 10;
                   else if ($defIndex < -25)
                      $defIndex = -25;
                      
                   // check resistance when boss attack
                   foreach($listSoldierAtt[$idVictim]->BuffItem as $id1 => $oItem)
                   { 
                        if ($oItem[Type::ItemType] == BuffItem::Resistance)  
                        {
                            $isResistance = 0;
                        }    
                   }
                   
                   $defTurn = SoldierFish::attackByTurn($soldierDef->Element,$listSoldierAtt[$idVictim]->Element,$conf_hit[$defIndex],$defenceIndex['Critical'],$isResistance) ;

                   if ($defTurn['Miss'])    // if Defence attack miss 
                   {
                       $DamageAttack = 0;
                       $scene[$turn]['Status']['Defence'] = SceneAttack::MISS;
                   }
                   else {
                        $vitalityDamage = SoldierFish::getVitalAttack($defenceIndex,$attackIndex[$idVictim],$defTurn['Conflict'],$alpha);    
                        $DamageAttack = $vitalityDamage['Damage'];    

                   }
                   
                   // update scene param
                   
                   $scene[$turn]['Vitality']['Attack'][$idVictim] = $scene[$turn-1]['Vitality']['Attack'][$idVictim] - $DamageAttack;
                   if ($scene[$turn]['Vitality']['Attack'][$idVictim] < 0)
                   {
                        // if do not reach min turn, fake data  
                        if ($turn < $conf_Turn['Min']) 
                        {
                            $scene[$turn]['Vitality']['Attack'][$idVictim] = ceil($scene[$turn-1]['Vitality']['Attack'][$idVictim]/2);                                                    
                        }   
                        else $scene[$turn]['Vitality']['Attack'][$idVictim] = 0;
                   } 
                   
                   if (!$defTurn['Miss'])
                   {
                       if ($vitalityDamage['Critical'] == 2)
                            $scene[$turn]['Status']['Defence'] = SceneAttack::CRITICAL;
                       else $scene[$turn]['Status']['Defence'] = SceneAttack::NORMAL;
                   }
                  
                   // update other attacker
                   foreach($listSoldierAtt as $id => $oSoldier) 
                   {
                       if ($id != $idVictim)
                        $scene[$turn]['Vitality']['Attack'][$id] = $scene[$turn-1]['Vitality']['Attack'][$id];
                   }
                   
                   // check condition break
                   // calculate total vitality attack
                   foreach($listSoldierAtt as $id => $oSoldier)
                   {
                        $total += $scene[$turn]['Vitality']['Attack'][$id];    
                   }
                    
                   // if def attack first, att die, recover def's vitality 
                   if (!$attackFirst)
                   {                        
                        if ($total==0)
                        {
                            $scene[$turn]['Vitality']['Defence']['Left'] = $scene[$turn-1]['Vitality']['Defence']['Left'];            
                            break;
                        }
                   } 
                   else // if att attack first, def die, reover att's vitality
                   {
                       if ($scene[$turn]['Vitality']['Defence']['Left']==0)
                       {
                            foreach($listSoldierAtt as $id => $oSoldier)         
                            {
                                $scene[$turn]['Vitality']['Attack'][$id] = $scene[$turn-1]['Vitality']['Attack'][$id];  
                            }
                            break;
                       }    
                   }
                   
                   // def or att die, break
                   if ($total==0 || $scene[$turn]['Vitality']['Defence']['Left']==0)
                      break;

                   // unset no vitality soldier
                   if ($scene[$turn]['Vitality']['Attack'][$idVictim] == 0) 
                    unset($listSoldierAtt[$idVictim]); 
                }
                else // qua 5 turn 
                {
                   // update vitality of soldier into 0
                   foreach($listSoldierAtt as $id => $oSoldier) 
                   {
                        $scene[$turn]['Vitality']['Attack'][$id] = 0;
                   }
                   
                   $scene[$turn]['Vitality']['Defence']['Left'] = $scene[$turn-1]['Vitality']['Defence']['Left'];
                   $scene[$turn]['Status']['Defence'] = SceneAttack::NORMAL; 
               }
            
                
           }
           $result['DamageTotal']   = $DamageTotal ;
           $result['Scene']         = $scene ;
           return $result;
      }
    
    // create info for Boss
    public function createMonster($BossId)
    {
        // tao boss
        $confBoss = Common::getConfig('ServerBossInfo',$BossId);    
        $soldierDef = new Monster($BossId,0,$confBoss['Vitality'],$confBoss['Dam'],$confBoss['Defend'],$confBoss['Critical'],$confBoss['Health'],array(),array(),true);
        $soldierDef->Rank = $confBoss['Rank'];
        
        return $soldierDef ;
          
        //-----------  
    }
    
    public function updateAttackBoss($Day,$Time,$BossId,$DamageTotal)
    {
       
        $sql_result = $this->sql_attackBoss($Day,$Time,$BossId,$DamageTotal);
        if($sql_result['Error'] != Error::SUCCESS)
            return false ;
        
        $sql_TopUser = $this->sql_getTopUser($Day,$Time,$BossId);
        if($sql_TopUser['Error'] != Error::SUCCESS)
            return false ;
            
        $keyBoss = $Day.'_'.$Time.'_'.$BossId ;
        
        // update damage total into membase
        $this->BossList[$keyBoss]['DamTotal'] = $sql_result['DamTotal'];
        
        // up date Vitality boss into membase
        $bossInfo = DataRunTime::get('BossInfo'.$keyBoss,true); 
        if(empty($bossInfo))
            return false ;
            
        $bossInfo['Vitality']     = $sql_result['VitalityTotal'];
        if($bossInfo['Vitality'] <= 0)
            $bossInfo['IsDie']    = true ;
        else
            $bossInfo['IsDie']    = false ;
        
        $set_result = DataRunTime::set('BossInfo'.$keyBoss,$bossInfo,true);
        if(!$set_result)
            return false ;
                        
        // update last hit 
        if($sql_result['LastHit'])
        {
            $BossLastHit = 0;
            $BossLastHit = DataRunTime::get('LastHit'.$keyBoss,true); 
            $BossLastHit = Controller::$uId;
            $set_result = DataRunTime::set('LastHit'.$keyBoss,$BossLastHit,true);
            if(!$set_result)
                return false ;
        }
         //Debug::log("Position".$sql_TopUser['Position']);
        // update lai vi tri user
        $this->BossList[$keyBoss]['Position'] = $sql_TopUser['Position']; //if = -1  => over 100
        
        // update topuser
        $mb_TopUser = array();
        //$mb_TopUser = DataRunTime::get('TopUser'.$keyBoss,true); 
        $i = 0 ;
        foreach($sql_TopUser['TopUser'] as $index => $arrTop)
        {
            $oUserTop = User::getById($arrTop['UserId']);
            if(!is_object($oUserTop))
                continue ;
            //$arrTop['UserName'] = $oUserTop->getUserName();
            $arrTop['UserName'] = empty($oUserTop->Name)?'UnKnown':$oUserTop->Name;

            $i++; 
            $mb_TopUser[$i] = $arrTop ;  
            
            if($i >= 100)
                break ;
        }
             
        $set_result = DataRunTime::set('TopUser'.$keyBoss,$mb_TopUser,true);
         if(!$set_result)
                return false ;
         
        if($bossInfo['IsDie']  == true)
        {            
            $this->backupDatabaseTopUser($keyBoss);
        }
        
        return true ;
    }
    
    public function getSeverBossGift($BossId,$GiftType)
    {
        $arr['NormalBonus']= array();
/*        $arr['TopBonus']= array();
        $arr['OutTopBonus']= array();
        $arr['LastHitBonus']= array();
        $arr['LostBonus']= array();*/

        $conf_gift = Common::getConfig('ServerBossBonus',$BossId,$GiftType);
        if(empty($conf_gift))
            return array();
        if($GiftType === 'NormalBonus')
        {
            $arr['NormalBonus'] = $conf_gift ;
        }
        
        return $arr ;
        
    }
    
    public function  updateGiftFromConfig($keyBoss)
    {
        $arr['TopBonus']= array();
        $arr['OutTopBonus']= array();
        $arr['LastHitBonus']= array();
        $arr['LostBonus']= array();
        $arr['DamageGift']= array();
        
 
        //date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.$BossId ;
        $ext = explode('_',$keyBoss);
        $Day = $ext[0];
        $Time = $ext[3];
        $BossId = $ext[4];
        $conf = Common::getConfig('ServerBossBonus',$BossId);
        if(empty($conf))
            return $arr ;
        
        // boss chet chua ?
        $bossInfo = DataRunTime::get('BossInfo'.$keyBoss,true); 
        
        // tinh level qua nhan duoc theo damage
        $DamgeGift_conf = Common::getConfig('ServerBossGift',$BossId);
        $LevelGiftFollowDam = 0 ;
        foreach($DamgeGift_conf as $indexGift =>  $GiftList)
        {
            if(empty($GiftList))
                continue;
            if($this->BossList[$keyBoss]['DamTotal'] >= $GiftList['DamageRequire'] )
            {
                $LevelGiftFollowDam = $indexGift ;
            }
        }
        // bonus event
        $bonusEventTitle = '';
        $eventGiftLastHit = false;
               
        if($bossInfo['Vitality'] > 0) // boss chua chet
        {
                      
            if($this->checkTime($keyBoss))  // chua het thoi gian
                return $arr ;
            else // da het thoi gian 
            {//Debug::log('qua_5_da het thoi gian');  
                // qua thua boss
                $arr['LostBonus']= $conf['LostBonus'];
                $bonusEventTitle =  'LostBonus';
                
                foreach($DamgeGift_conf[$LevelGiftFollowDam]['Gift'] as $id =>$gift)
                {
                    if(!empty($gift))
                        $arr['DamageGift'][] = $gift ;
                }
                
            }
        }
        else // thang boss
        {           
            // qua top user
            switch ($this->BossList[$keyBoss]['Position'])
            {
                case 1:
                    $arr['TopBonus'] = $conf['TopBonus_1'];
                    $bonusEventTitle = 'TopBonus_1';
                    break;
                case 2:
                case 3:
                case 4:
                case 5:
                    $arr['TopBonus'] = $conf['TopBonus_5'];
                    $bonusEventTitle = 'TopBonus_5';
                    break;
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    $arr['TopBonus'] = $conf['TopBonus_10'];
                    $bonusEventTitle = 'TopBonus_10';
                    break;
                default:
                    $arr['OutTopBonus'] = $conf['OutTopBonus']; // ngoai top
                    $bonusEventTitle = 'OutTopBonus';
                    break;
            }
            $BossLastHit = DataRunTime::get('LastHit'.$keyBoss,true); 
            // qua last hit
            if($BossLastHit == Controller::$uId)
            {
                $arr['LastHitBonus']= $conf['LastHitBonus'];
                $eventGiftLastHit = true;
            }
            
            foreach($DamgeGift_conf[$LevelGiftFollowDam]['Gift'] as $id =>$gift)
            {
                if(!empty($gift))
                    $arr['DamageGift'][] = $gift ;
            }
        }
        if(empty($arr))
            return $arr ;
        // random so luong    
        foreach($arr as $index1 => $bonus_arr)
        {
            foreach($bonus_arr as $index2 => $bonus)
            {
                if(is_array($bonus['Num']))
                {
                    $id = array_rand($bonus['Num'],1);
                    $arr[$index1][$index2]['Num'] = $bonus['Num'][$id]; 
                    
                    unset($arr[$index1][$index2]['Rate']);
                }
            }
        }
        
        // bonus event action gift
        if(!empty($bonusEventTitle))
        {
            $listGiftEvent = Event::getActionGiftInEvent(EventType::EventActive, 'ServerBoss', $BossId, $bonusEventTitle);
            switch($bonusEventTitle)
            {
                case 'LostBonus':
                case 'OutTopBonus':
                    $arr[$bonusEventTitle] = array_merge($arr[$bonusEventTitle], (array)$listGiftEvent);
                    break;
                case 'TopBonus_1':
                case 'TopBonus_5':
                case 'TopBonus_10':
                    $arr['TopBonus'] = array_merge($arr['TopBonus'], (array)$listGiftEvent);
                    break;
            }    
        }
        if($eventGiftLastHit)
        {
            $listGiftEvent = Event::getActionGiftInEvent(EventType::EventActive, 'ServerBoss', $BossId, 'LastHitBonus');
            $arr['LastHitBonus'] = array_merge($arr['LastHitBonus'], (array)$listGiftEvent);
        }
        
        // end bonus event
        
        $arr_gift = $this->saveBonus($arr);
        //$this->BossList[$keyBoss]['Bonus'] = $arr ;
        return $arr_gift ;    

    }
    
    public function saveBonus($arr_bonus)
    {
        // random so luong    
        $arr = array();
        foreach($arr_bonus as $index1 => $bonus_arr)
        {
            if(empty($bonus_arr))
                continue ;
            
            $arr[$index1]= Common::addsaveGiftConfig($bonus_arr,rand(1,5),SourceEquipment::EVENT);
        }
        return $arr ;
    }
    
    public function checkWaitingTime($keyBoss)
    {
        $AttackNum = $this->BossList[$keyBoss]['AttackNum']; 
        $conf = Common::getConfig('ServerBossTime');     
        $AttackNum =   ($AttackNum > count($conf))? count($conf) : $AttackNum ; 
        $waitTime = $conf[$AttackNum]['Time'];
        if(empty($waitTime))
            return true ;
        
        //$waitTime = intval(Common::getParam('ServerBoss','WaitTime'));
        if($this->BossList[$keyBoss]['LastTimeAttack'] + $waitTime <= $_SERVER['REQUEST_TIME'])
            return true ;
        return false ;       
    }
    
    public function getCurrentDetailKey()
    {
        //$keyBoss = date('Y_m_d',$_SERVER['REQUEST_TIME']).'_'.$Time.'_'.$BossId ;
        $result = array();
        if(empty($this->BossList))
            return $result;
            
        foreach($this->BossList as $KeyBoss =>$arr_Boss)
        {
            if(empty($arr_Boss))
                continue ;
            $ex = explode('_',$KeyBoss);
            $result['Day']     = substr($KeyBoss,0,10);            //Y_m_d
            $result['Time']    = intval($ex[3]);     //H
            $result['BossId']  = intval($ex[4]);//intval(substr($KeyBoss,-1,1));    
            $result['KeyBoss'] = $KeyBoss;
        }
        
        return $result ; 
    }
    
    public function backupDatabaseTopUser($keyBoss)
    {
        
        $BackUp_Arr = DataRunTime::get('BackUp'.$keyBoss,true); 
        if($BackUp_Arr['Is_BackUp'] == 1)
            return false ;

        $mb_TopUser = DataRunTime::get('TopUser'.$keyBoss,true); 
        
        $BackUp_Arr['TopUser']      = $mb_TopUser ; //oder=>array('UserId','DamgeTotal')
        $BackUp_Arr['Is_BackUp']    = 1 ;
        DataRunTime::set('BackUp'.$keyBoss,$BackUp_Arr,true);

    }
    
    public function getDiceRate($keyBoss)
    {
        $conf = Common::getConfig('ServerBossDice'); 
        if($this->BossList[$keyBoss]['Dice'] != 0 )
        {
             $rate = $conf[1][$this->BossList[$keyBoss]['Dice']] ;
        }
        else if($this->BossList[$keyBoss]['SpecialDice'] != 0 ) 
        {
             $rate = $conf[2][$this->BossList[$keyBoss]['SpecialDice']] ;
        }
        else
            return 0;
        
        return $rate ;
    }

  }
?>
