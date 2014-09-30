<?php
class MigrateData
{
       static private $ConfigOld;
       static private $oMemBaseOld ;
       static private $oMemCacheOld;


  static public function init()
  {
      GLOBAL $CONFIG_DATA;
      self::$ConfigOld =& $CONFIG_DATA ;
      self::$ConfigOld['oldServers'] = Common::getSysConfig('dbOldServer') ; 
      self::$ConfigOld['dbOldBuckets'] = Common::getSysConfig('dbOldBuckets') ; 
      $option =& self::$ConfigOld['dbOldBuckets'];
      
      self::$oMemBaseOld = new Memcache();
      self::$oMemCacheOld = new Memcache();
      
      $connected = false;
      $serverLst = self::$ConfigOld['oldServers'];
      do
      {
          $index =  array_rand($serverLst);
          $server =  $serverLst[$index];
          
          $MemBaseOk = self::$oMemBaseOld->connect($server[0],$option['Membase'][0]);
          $MemCacheOk = self::$oMemCacheOld->connect($server[0], $option['Memcached'][0]);
          
          if(!$MemBaseOk || !$MemCacheOk)
                 unset($serverLst[$index]);     // delete this server
          else $connected = true;
          
      } while ((!$connected) && (count($serverLst) > 0));
      
      if(!$connected)
        die('Chua lay duoc du lieu cua nguoi dung') ;
  }
  
  static function get($uId,$keyWord,$id = '',$exKey = '')
  {    
    if(!isset(self::$ConfigOld['Key'][$keyWord]))  self::warm($keyWord) ; 
    $opKey =& self::$ConfigOld['Key'][$keyWord] ;

    if(empty($uId)) self::warm() ;

    if(!is_array($uId))
    {
       $key = Model::$appKey.'_'."{$uId}_{$id}_{$exKey}_{$keyWord}";
    }
    else $key = $uId ;  
    
    if(($opKey['Cache'] === true ))
    {
      $data = self::$oMemCacheOld->get($key);
    }
    else
        $data = self::$oMemBaseOld->get($key);
        
    return $data;
  }
  
  static function migrateUserData($uId)
  {
      $multiObj = array(
            'Lake' => array(1,2,3), 
            'Decoration' => array(1,2,3),
            'Friends' => array('Friends', 'FriendIds'),
      );  
      
      foreach(self::$ConfigOld['Key'] as $akey => $oKey)
      {
        if($akey != 'User')
        {
            if (isset($multiObj[$akey]))
            {
                foreach($multiObj[$akey] as $index => $mid)
                {
                    $data = self::get($uId,$akey,$mid);
                    if(empty($data)) continue;                    
                    $success = DataProvider::set($uId,$akey,$data,$mid);
                    if(!$success)
                       {
                           Zf_log::write_act_log_new($uId, 0, 10, 'MigrateFailed', 0, 0, 0, 0,'Set Failed', $akey, $mid );
                           return false;
                       }                     
                }
            }
            else
            {
                $data = self::get($uId,$akey);
                if(empty($data)) continue;
                $success = DataProvider::set($uId,$akey,$data);
                if(!$success)
                   {
                       Zf_log::write_act_log($uId, $uId, 10, 'MigrateFailed', 0, 0, 0, 0, 'Set Failed', $akey);
                       return false;
                   }      
            }
        } 
        
      }
      // write User key
      $oUser = self::get($uId,'User');
      if(!is_object($oUser))  return false;
      $success = DataProvider::set($uId,'User',$oUser);
        if(!$success)
           {
               Zf_log::write_act_log_new($uId, 0, 10, 'MigrateFailed', 0, 0,'Set Failed', 'User');
               return false;
           }   
      return true;
  }
  
  static function migrateUserFriend($oUser, $newFriendList, $friendKeys)
  {
      $FriendList = $newFriendList;
      $migrateFriendExpire = Common::getSysConfig('migrateFriendExpire');
      $timeStone = $_SERVER['REQUEST_TIME'] - (($oUser->Migrated != 0) ? $oUser->Migrated : $oUser->CreatedTime);
      if(($timeStone < $migrateFriendExpire))
      {
         $oldFriendList = self::get($friendKeys, 'User');
         if(is_array($oldFriendList))
            $FriendList = array_merge($oldFriendList, $FriendList);    
         else if(!$oldFriendList)
         {
            Zf_log::write_act_log_new($oUser->Id, 0, 10, 'MigrateFailed', 0, 0, 0, 0,'OldFriend');
         }
      } 
      return $FriendList;
  } 

  static function warm($key = 'null')
  {
     die('Config DataProvider Wrong key : '.$key) ;  
  }
}

MigrateData::init();
?>
