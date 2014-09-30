<?php

require ROOT_DIR.'/Execution/Globals.php';

class Debug
{
  static private $Logger ;

  static function init($path)
  {  
    if(Common::getSysConfig('debug'))
    {
      Common::loadLib('DebugLog');
      self::$Logger = DebugLog::getInstance();
      self::$Logger->SetLogPath($path);  // dont forget / in the end
      self::$Logger->Debug('-------------------------------------------------------------------');   
    }   
  }

  static function log($message)
  {
     if(Common::getSysConfig('debug'))
        self::$Logger->Debug(var_export($message,true));
  }
}

Debug::init(ROOT_DIR ."/log/") ;

class Common
{

    /**
    * Get Config cua Game
    * 
    * @param mixed $key
    * @param mixed $index
    * @param mixed $id
    * @return mixed
    */
    
	public static function & getConfig_old($key = 'System/Config', $index = false,$id = false)
    {
        static $Config ;

        if (empty ($Config[$key]))
        {
           if(file_exists(CONFIG_DIR . "/{$key}.php"))
           {
              $Config[$key] = include(CONFIG_DIR . "/{$key}.php") ;
           }
           else return false ;
        }

        if ($index === false)
            return $Config[$key];
        else if( $id === false )
            return $Config[$key][$index];
        else return $Config[$key][$index][$id];
    }
    
    public static function & getWorldConfig($FileName, $index = false,$id = false)
    {
        $key = 'World/'.$FileName;
        static $Conf ;

        if (empty ($Conf[$key]))
        {
           if(file_exists(CONFIG_DIR . "/{$key}.php"))
           {
              $Conf[$key] = include(CONFIG_DIR . "/{$key}.php") ;
           }
           else return false ;
        }

        if ($index === false)
            return $Conf[$key];
        else if( $id === false )
            return $Conf[$key][$index];
        else return $Conf[$key][$index][$id];
    }
    
    public static function & getConfig($FileName= 'Config', $index = false,$id = false)
    {
        static $Conf ;
        
        $DirArr = array(CONFIG_DIR,CONFIG_DIR."/System" ,CONFIG_DIR . "/Event",CONFIG_DIR . "/World",CONFIG_DIR . "/Admin");
        $Path = '';
        foreach($DirArr as $Dir)
        {
            $key = $Dir."/{$FileName}.php" ;
            if (empty ($Conf[$key]))
            {
               if(file_exists($key))
               {
                  $Conf[$key] = include($key) ;
                  $Path = $key ;
                  break ;
               }
            }
            else
            {
                $Path = $key ;
                break ;
            }
        }
        
        if(!empty($Path))
        {
            if ($index === false)
                return $Conf[$Path];
            else if( $id === false )
                return $Conf[$Path][$index];
            else return $Conf[$Path][$index][$id];
        }
        else
            return false ;        
    }
    
    /**
     * Class autoload loader.
     * This method is provided to be invoked within an __autoload() magic method.
     * @param string $className class name
     * @return boolean whether the class has been loaded successfully
     */
    public static function autoload($className)
    {
        $success = Common::loadModel($className);
        if(!$success) 
         $success = Common::loadModel('BusinessObject/'.$className);
        if(!$success)
         $success = Common::loadLib($className);
        if(!$success)
         die('Wrong Class : '.$className);
    }    

	/**
	*
	* @param String $key
	* @return String | Int
	*/


	public static function getParam($key = null,$Key_1 = null,$Key_1_1 = null)
	{
		$config =& self :: getConfig('Param') ;
        if($key == null) return $config ;
        
        if($Key_1 == null) return $config[$key];
        
        if($Key_1_1 == null) return $config[$key][$Key_1];
        
		return $config[$key][$Key_1][$Key_1_1];
	}

    /**
    *    Ham get System Config cua he thong
    * @param String $index
    * @return String | Int
    */
    
    public static function  getSysConfig($index = false)
	{
		$config =& self :: getConfig() ;
        if($index === false ) return $config ;
		else return $config[$index];
	}


	public static function loadModel($name)
	{
		$path = MOD_DIR . '/' . $name . '.php' ;
        if(file_exists($path))
		{
            require_once ($path) ;
            return true ;
        }
        return false;
	}

    public static function loadService($name)
	{
		$path = SER_DIR . '/' . $name . 'Service.php' ;
		require_once ($path) ;
	}

	public static function loadLib($name)
	{
		$path = LIB_DIR . '/' . $name . '.php' ;
        if(file_exists($path))
        {
            require_once ($path) ;
            return true ;
        }
        return false;
	}

    public static function checkTestUser()
	{
		if(Common::getSysConfig('userTest')) return true ;
        $path = BETA_DIR . '/' . Controller::$uId . '.txt' ;
        if(file_exists($path)) return true ;
        else return false ;
	}
    
    public static function getUserId($userName)
    {
		$file = file_get_contents("http://api2.me.zing.vn/api/friend?method=getUserProfile&username={$userName}");
		$info = json_decode($file,true);
		if(isset($info['data']['userid']) && $info['data']['userid'] >0)
		{
			$uId = $info['data']['userid'];
		}
    return $uId;
    }

    /**
	 * redi?ct
	 * @param unknown_type $url
	 * @return unknown_type
	 */
	public static function redirect( $url )
	{
        @header("Location: {$url}");
        $url = json_encode($url);
        die("<script type='text/javascript'>location={$url};</script>");
	}
    
	public static function computePeriod($startTime, $PeriodInfo)
	{
		$lifetime = $startTime ;
		$count = count($PeriodInfo) -1  ;
		for ($i = 0 ; $i < $count  ; $i++)
		{
			if ($lifetime >= $PeriodInfo[$i] && $lifetime < $PeriodInfo[$i + 1])
				return $i ;

		}
        return $count  ;
	}
    
    /**
    * convert object Fish to new object
    * 
    * @param mixed $object
    * @param mixed $NewClassName
    */
	public static function convertObjectFish($object,$NewClassName)
    {
        $oldClassName = get_class($object);
        if(empty($oldClassName))
            return false;
        $replect    = new ReflectionClass($oldClassName);
        $PropertyList = $replect->getProperties();
        foreach($PropertyList as $property)
        {
            $arr_p[] = $property->getName();
        }
        
        try
        {
            $newObject = new $NewClassName($object->Id);
        }
        catch(Exception $e)
        {
            return false ;
        }
        
        foreach($arr_p as $P)
        {
            $newObject->setproperty($P,$object->$P );
        }       
        
        return $newObject ;
        
    }
    
    public static function randomEquipment($autoId,$level = 1,$color,$source,$ItemType = '',$Enchant = 0,$Element = 0,$numOpt = 0 )
    {
        $arr = array();
        if($level > 7)
            return false ;
            
        if(empty($ItemType))
        {
            $arrEquipment = array('Armor'=>10,'Helmet'=>10,'Weapon'=>10,'Ring'=>10,'Bracelet'=>10,'Necklace'=>10,'Belt'=>10);
            $arr['ItemType'] = Common::randomIndex($arrEquipment);
        }
        else
        {
            if(SoldierEquipment::checkExist($ItemType))
                $arr['ItemType'] = $ItemType ;
            else
                return false ;
        }
        
        if(in_array($arr['ItemType'],array('Armor','Helmet','Weapon'),true))
        {
            $rank = array(101=>1,201=>1,301=>1,401=>1,501=>1) ;
            $arr['ItemId']  = array_rand($rank,1);
            $arr['ItemId'] += $level - 1 ;
            if(!in_array($Element,array(1,2,3,4,5),true))
                $arr['Element'] = floor($arr['ItemId']/100) ; 
            else
            {
                $arr['Element'] = $Element ;
                $temp = substr($arr['ItemId'],-2);
                $arr['ItemId']  = intval($Element.$temp);                
            }           
        }
        else
        {
            $arr['ItemId'] = $level ;
            $arr['Element'] = Elements::NEUTRALITY ;
        }
        
        if(empty($color))
            $arr['Color'] = rand(1,4);
        else
            $arr['Color'] = $color ;
        
        if(empty($source))
            $arr['Source'] = SourceEquipment::SHOP ;
        else
            $arr['Source'] = $source ;
            
        $conf = Common::getConfig('Wars_'.$arr['ItemType'],$arr['ItemId'],$arr['Color']);
        $arr['Damage']      = rand($conf['Damage']['Min'],$conf['Damage']['Max']);
        $arr['Defence']     = rand($conf['Defence']['Min'],$conf['Defence']['Max']);
        $arr['Critical']    = rand($conf['Critical']['Min'],$conf['Critical']['Max']);
        $arr['Durability']  = $conf['Durability'];
        $arr['Vitality']    = $conf['Vitality'];
        
        $oEquipment = new Equipment($autoId,$arr['Element'],$arr['ItemType'],$arr['ItemId'],$arr['Color'],
        $arr['Damage'],$arr['Defence'],$arr['Critical'],$arr['Durability'],$arr['Vitality'],$arr['Source'],$numOpt);
        
        if($Enchant > 0)
        {
             for ($i=1; $i<=$Enchant; $i++)
                        $oEquipment->enchant(101,true);
        }  
        return $oEquipment ;

    }
    
    /**
    * Return random index
    * $isBoolean=true : rate of $value > 0 is the same
    * @author hieupt
    * 19/05/2011
    */
    
	public static function randomIndex($param, $isBoolean = false){
    	
    	$total = 0;
        if(empty($param))
            return false;
    	foreach ($param as $index => $value){
            if ($isBoolean)
            {
                if ($value > 0)
                    $param[$index] = 1;
                else $param[$index] = 0;
            }    
            $total += $param[$index];
    	}
    	
    	$ran = mt_rand(1,$total*100)/100;
    	
    	$inc = 0;
		foreach ($param as $index => $value){
			$inc += $value;
			if ($ran <= $inc)	
				return $index;
		}    	
		return ($index);
    }

    /**
    *  Written by: thedg25
    *  Date: 2012/11/22
    *  Desc: return random  an item via weight of element group items
    *  Require: $arrItem have key, rate
    *  Return Item 
    */
    //#NOEL2012
    public static function pickItem($arrItem){
        $Items = array();
        foreach($arrItem as $key => $Item){
            if($Item['Rate'] > 0)
                array_push($Items,array($key,$Item['Rate']));
        }
        $hat = array();
        foreach($Items as $Item){
            $hat = array_merge($hat,array_fill(0,$Item[1],$Item[0]));
        }
        $index = intval($hat[array_rand($hat)]);
        return $arrItem[$index];
    }
    
    public static function pickIndex($arrItem){
        $Items = array();
        foreach($arrItem as $key => $Item){
            if($Item['Rate'] > 0)
                array_push($Items,array($key,$Item['Rate']));
        }
        $hat = array();
        foreach($Items as $Item){
            $hat = array_merge($hat,array_fill(0,$Item[1],$Item[0]));
        }
        $index = intval($hat[array_rand($hat)]);
        return $index;
    }

    
    /**
    * add item to exist list
    * @ItemList: list items existed
    * @arrBonus: array bonus add to itemlist
    * return @ItemList
    */
    
    public function addItemToList($ItemList, $arrBonus)
      {
           foreach($arrBonus as $id => $oBonus)
           {
                $check = true;
                foreach($ItemList as $idB => $curBonus){
                    if ($oBonus[Type::ItemType]==$curBonus[Type::ItemType] && $oBonus[Type::ItemId]==$curBonus[Type::ItemId])
                    {
                        $ItemList[$idB][Type::Num] += intval($arrBonus[$id][Type::Num]);
                        $check = false;
                        break;
                    }    
                }

                if ($check)
                {
                    $ii = count($ItemList);
                    $ItemList[$ii] = $oBonus;    
                }
                
           }
           return $ItemList;
      }
      
      
      
    /**
    * call a defined service
    * @author hieupt
    * 22/09/2011   
    */
    
    public function callService($service, $method, $param)
    {
        require_once SER_DIR . '/' . $service . '.php';
        $ser  = new $service;
        $result = $ser->$method($param);
        StaticCache::forceSaveAll();
        return $result;
    }
    
    
    /**
    * get day different from two timestamps
    * @author hieupt
    * 26/09/2011 
    */
    public function getDayDifferent($timeBefore, $timeAfter)
    {
        $dbefore = getdate($timeBefore);
        $beginBefore = mktime(0,0,0,$dbefore['mon'],$dbefore['mday'],$dbefore['year']);
        return floor(($timeAfter - $beginBefore)/(24*3600));
    }
    
    
    /**
    * execute a query using mysql 
    */
    public static function queryMySql($indexName, $query, $database = '')
    {
         $conf_db = Common::getConfig('DbConfig',$indexName);
         if(empty($conf_db))
            return false;
         $connection = mysql_connect($conf_db['Host'],$conf_db['User'],$conf_db['Pass']); 
         if(!$connection)
            return false;  
         $db = (empty($database)) ? $conf_db['Database'] : $database;
         $aa = mysql_select_db($db);         
         $result = mysql_query($query, $connection);
         
         $errno = mysql_errno($connection);
         $error = mysql_error($connection);

         if(!$result)
         {
             Zf_log::write_act_log(0,0,20,'MysqlError',0,0,$errno, $error, $query);
         }
         return $result;         
    }
    
    /**
    * execute a query using mysqli
    */
    public static function queryMySqli($indexName, $query)
    {     
         $conf_db = Common::getConfig('DbConfig',$indexName); 
         if(empty($conf_db))
            return false;     
         $mysqli = mysqli_connect($conf_db['Host'],$conf_db['User'],$conf_db['Pass'],$conf_db['Database']);
         //echo mysqli_error($mysqli); 
         $result = mysqli_query($mysqli, $query);
         $errno = mysqli_errno($mysqli);
         $error = mysqli_error($mysqli);
                              
         if ($result)         
         {             
             return array('Error' => Error::SUCCESS, 'Data' => $result);
         }
         else
         {             
             Zf_log::write_act_log(0,0,20,'MysqlError',0,0,$errno, $error , $query); 
             
             return array('Error' => $errno, 'ErrorName' => $error);
         }
    }
    
    public static function query_mysqli($indexName, $query)
    {
        $conf_db = Common::getConfig('DbConfig',$indexName);
        if(empty($conf_db))
            return false;
        $mysqli = new mysqli($conf_db['Host'],$conf_db['User'],$conf_db['Pass'],$conf_db['Database']);
        $resultData = $mysqli->query($query, MYSQLI_STORE_RESULT);
        if(!is_object($resultData))
        {                   
            if(!$resultData)
            {
                return array('Error' => $mysqli->errno, 'Desc' => $mysqli->error);     
            }
            else return array('Error' => Error::SUCCESS, 'Data' => $resultData);
        }            
        $dataRow = array();
        
        while($row = $resultData->fetch_array(MYSQLI_ASSOC))
        {
            $dataRow[] = $row;
        }
        $resultData->free();
        $mysqli->close();
        
        return array('Error' => Error::SUCCESS, 'Data' => $dataRow);
    }
    /**
     * check JAV SER request PHP SER
     */
    public static function isJAVSERRequest()
    {
    	$serverList = Common::getSysConfig('socketServerList');

        if (empty($serverList)) return false;

        $inList = false;
        foreach ($serverList as $index => $oneServer)
        {
            $inList = ($inList)||($_SERVER['REMOTE_ADDR'] == $oneServer['ip']);
        }
        return $inList;
    }    	
    
    public static function bonusHappyWeekDay($item)
    {
        $timeComponent = getdate($_SERVER['REQUEST_TIME']);
        $bonus = self::getConfig('HappyWeekDay', $timeComponent['wday'], $item);
        if(empty($bonus))
            return false;
        return $bonus;
    }
    
    public static function checkLucky($Rate)
    {
        if(empty($Rate)) return false;
        $luck = rand(1,100);
        return ($luck <= $Rate) ? true : false;
    }
    
    public static function getLevelFromArrangeMin($num,$arrange)
    {
        if(!is_array($arrange))
            return false;
        arsort($arrange);
        foreach($arrange as $level => $value)
        {
            if($num >= $value)
                return $level;
        }
        
        return false;
    }
    
    /**
    * add & save All Item
    * 
    * @param mixed $ItemConfig: array ('ItemType', 'ItemId', 'Num',...)
    * @return mixed
    */
    public static function addsaveGiftConfig($GiftConfig, $Element, $Source = '', $isSaved = true)
    {
        $NormalItems = array();
        $EquipItems = array();
         
        $oUser = User::getById(Controller::$uId); 
        
        if(empty($GiftConfig)) 
            return array('Normal' => $NormalItems, 'Equipment' => $EquipItems); 
        foreach($GiftConfig as $id => $Gift)
        {
            
            if(!is_array($Gift))
            {
                continue ;
            } 
            
            if(!empty($Gift['Num'])&&is_array($Gift['Num']))
            {
                $index = array_rand($Gift['Num'],1);
                $Gift['Num'] = $Gift['Num'][$index] ;
            }
            
            
            switch($Gift['ItemType'])
            {
                case Type::RandomEquipment:
                    $Element = (empty($Gift['Element'])) ? $Element : $Gift['Element'];
                    $Source = (empty($Gift['Source'])) ? $Source : $Gift['Source'];
                    
                    $oEquip = self::randomEquipment($oUser->getAutoId(), $Gift['Rank'],  $Gift['Color'], $Source, '', $Gift['Enchant'], $Element, $Gift['Option']);
                    $EquipItems[] = $oEquip;
                    break;
                    
                case Type::EquipmentChest:
                case Type::JewelChest:
                case Type::AllChest:
                    $Element = (empty($Gift['Element'])) ? $Element : $Gift['Element'];
                    $Source = (empty($Gift['Source'])) ? $Source : $Gift['Source'];
                    
                    for($i = 0; $i < $Gift['Num']; $i ++)
                        {
                            $EquipSet = Common::getConfig('ChestGift', $Gift['ItemType'], $Gift['Rank']);
                            $EquipSet = $EquipSet[$Gift['Color']];
                            $EquipBasic = $EquipSet[array_rand($EquipSet)];
                            $oEquip = self::randomEquipment($oUser->getAutoId(), $Gift['Rank'],  $Gift['Color'], $Source, $EquipBasic['ItemType'], $Gift['Enchant'], $Element, $Gift['Option']);
                            $EquipItems[] = $oEquip;
                        }
                    break;
                    
                case Type::FullSet:
                case Type::SetEquipment:
                case Type::SetJewel:                    
                    $Element = (empty($Gift['Element'])) ? $Element : $Gift['Element'];
                    $Source = (empty($Gift['Source'])) ? $Source : $Gift['Source'];
                    
                    $Set = self::getConfig('General','TypeSet',$Gift['ItemType']);
                    foreach($Set as $Type)
                    {
                        $oEquip = self::randomEquipment($oUser->getAutoId(), $Gift['Rank'],  $Gift['Color'], $Source, $Type, $Gift['Enchant'], $Element, $Gift['Option']);
                        $EquipItems[] = $oEquip;                                              
                    }
                    break;
                    
               case SoldierEquipment::Armor:
               case SoldierEquipment::Belt:
               case SoldierEquipment::Bracelet:
               case SoldierEquipment::Helmet:
               case SoldierEquipment::Necklace:
               case SoldierEquipment::Ring:
               case SoldierEquipment::Weapon:
                    $Element = (empty($Gift['Element'])) ? $Element : $Gift['Element'];
                    $Source = (empty($Gift['Source'])) ? $Source : $Gift['Source'];
                    
                    $oEquip = self::randomEquipment($oUser->getAutoId(), $Gift['Rank'],  $Gift['Color'], $Source, $Gift['ItemType'], $Gift['Enchant'], $Element, $Gift['Option']);
                    $EquipItems[] = $oEquip;
                    break;
               case SoldierEquipment::Seal:
                    $oEquip = new Seal('Seal', $oUser->getAutoId(), $Gift['Rank'], $Gift['Color']);
                    $EquipItems[] = $oEquip;
                    break;
                    
               case Type::Soldier :
                    $TypeList = array(1=>'Draft',2=>'Paper',3=>'GoatSkin',4=>'Blessing');
                    $RecipeType = empty($Gift['RecipeType'])?$TypeList[$Gift['Rank']]:$Gift['RecipeType'] ;
                    if(!empty($RecipeType))
                    {   
                        $Element = empty($Element)?$Gift['Element']:$Element ;
                        $Element = empty($Element)?rand(1,5):$Element ;
                                                
                        $Gift['ItemId']         = $Element ;
                        $Gift['Element']        = $Element ;
                        $Gift['RecipeType']     = $RecipeType ;
                        $Gift['Num']            = 1 ;
                        $NormalItems[]          = $Gift ;
                    }
                    break;
                    
                default:
                    $NormalItems[] = $Gift;
                    break;
            }
        }
        // add/save Items
        if($isSaved)
        {
            $oUser->saveBonus($NormalItems);

            $oStore = Store::getById(Controller::$uId);
            foreach($EquipItems as $oEquip)
            {
                $oStore->addEquipment($oEquip->Type, $oEquip->Id,$oEquip);
                
                Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip);     
            }
            $oStore->save();    
        }
        $oUser->save(); 
        return array('Normal' => $NormalItems, 'Equipment' => $EquipItems); 
    }
    
    /**
    * add Gift da dc sinh ra tu ham addsaveGiftConfig, save = false 
    * 
    * @param mixed $gifted
    */
    public static function addsaveGifted($gifted)
    {   
        $oUser = User::getById(Controller::$uId);     
        $oUser->saveBonus($gifted['Normal']);
        $oUser->save();
        $oStore = Store::getById(Controller::$uId);
        foreach($gifted['Equipment'] as $oEquip)
        {
            $oStore->addEquipment($oEquip->Type, $oEquip->Id,$oEquip);
            
            Zf_log::write_equipment_log(Controller::$uId, 0, 20, 'saveEquipment', 0, 0, $oEquip);     
        }
        $oStore->save();    
        
        return true;
    }
    
    /**
    * tinh thong ke ti le thanh phan.
    * 
    * @param mixed $rawData key1=30, key2=> 70
    * output: key1=>30%, key2=>70%
    */
    public static function calculateStat($rawData)
    {
        if(!is_array($rawData) || count($rawData) == 0 )
            return false;
        $total = array_sum($rawData) ;
        $total = ($total > 0) ? $total : 100;
        
        foreach($rawData as $key=>$value)
        {
            $stat[$key] = round(floatval($value*100/$total), 2);
        }
        return $stat;
    }
    
    public static function returnError($errorCode)
    {
        return array('Error' => $errorCode);
    }
    
    public static function checkCoolDown($duration_cd, $type_cd, $uModel)
    {
        if(($_SERVER['REQUEST_TIME'] - $uModel->$type_cd) < $duration_cd)
            return false;
        else return true; 
    }
    
    public static function setCoolDown($type_cd, $uModel)
    {
        $uModel->$type_cd = $_SERVER['REQUEST_TIME'];
    }
    
    public static function groupItems($lstItems)
    {
        $Pack = array();
        $groupedItem = array();
        foreach($lstItems as $id => $item)
        {
           $num = $Pack[$item['ItemType']][$item['ItemId']];
           $num = (empty($num)) ? $item['Num'] : ($num + $item['Num']);
           $Pack[$item['ItemType']][$item['ItemId']] = $num;    
        }
        foreach($Pack as $ItemType => $item)
        {
            foreach($item as $itemId => $Num)
            {
                $groupedItem[] = array(
                    'ItemType' => $ItemType,
                    'ItemId' => $itemId,
                    'Num' => $Num
                );
            }
        }
        
        return $groupedItem;        
    }
    
    public static function snapshot_a1()
    {        
        $dataUser = array();
        $dataUser = self::snapshot_udata(Controller::$uId);
        
        if(empty($dataUser)) return false;
        $achieved_data = self::achieve_data($dataUser);
        if(!$achieved_data)
            return false;
        // auto creat category, snapshot file
        try{
            $bak_root = Common::getSysConfig('snapshotA1_rootdir');
            if(!file_exists($bak_root))
                if(!mkdir($bak_root, 0777, true))
                    return false;            
            $date_path = $bak_root.'/'.date('Ymd', $_SERVER['REQUEST_TIME']);
            if(!file_exists($date_path))
                if(!mkdir($date_path, 0777, true))
                    return false;
            $file = $date_path.'/'.Controller::$uId.'_'.date('YmdHis', $_SERVER['REQUEST_TIME']);
        
            $file_handle = fopen($file, 'w');        
            fwrite($file_handle, $achieved_data, strlen($achieved_data));    
            fclose($file_handle);
        }
        catch(Exception $ex)
        {                        
            return false;
        }
        
        return true;
    }
    
    public static function snapshot_udata($uId, $memcache = true)
    {
        global $CONFIG_DATA;
        $allData = array();
        $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3)); 
        foreach($CONFIG_DATA['Key'] as $akey => $oKey)
        {            
            if(!$memcache) 
                if($oKey['Cache']) continue;
                
            if (isset($multiObj[$akey]))
            {
                foreach($multiObj[$akey] as $index => $mid)
                {
                    $allData[$akey][$mid] = DataProvider::get($uId,$akey,$mid);        
                }
            }
            else
            {
                $allData[$akey] = DataProvider::get($uId,$akey);        
            }
        }
        
        return $allData;
    }
    
    public static function delete_udata($uId, $ignore_key = array())
    {
        global $CONFIG_DATA;
        $res = array();
        $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3)); 
        foreach($CONFIG_DATA['Key'] as $akey => $oKey)
        {
            if(in_array($akey, $ignore_key)) continue;            
            if (isset($multiObj[$akey]))
            {
                foreach($multiObj[$akey] as $index => $mid)
                {
                    $res[$akey][$mid] = DataProvider::delete($uId,$akey,$mid);        
                }
            }
            else
            {
                $res[$akey] = DataProvider::delete($uId,$akey);        
            }
        }        
        return $res;
    }
    
    public static function restore_udata($uId, $allData, $ignore_key = array())
    {
        global $CONFIG_DATA;
        $res = array();
        $multiObj = array('Lake' => array(1,2,3), 'Decoration' => array(1,2,3));
        
        foreach($CONFIG_DATA['Key'] as $akey => $oKey)
        {
            if(in_array($akey, $ignore_key)) continue;            
            if (isset($multiObj[$akey]))
            {
                foreach($multiObj[$akey] as $index => $mid)
                {      
                    if (is_object($allData[$akey][$mid])){
                        $allData[$akey][$mid]->setKey($uId,$mid);                        
                        if ($akey=='Lake')
                        {
                            foreach($allData[$akey][$mid]->FishList as $id => $oFish)       
                            {
                                $oFish->updateLakeKey(Model::$appKey.'_'.$uId.'_'.$mid.'__Lake');
                            }
                        }                                                
                        $res[$akey][$mid] = DataProvider::set($uId,$akey,$allData[$akey][$mid],$mid); 
                    }                        
                }
            }
            else
            {      
                if (is_object($allData[$akey]))
                {                    
                    if ($akey=='User')   
                        $allData[$akey]->Id = $uId;
                    $allData[$akey]->setKey($uId);                     
                }                
                $res[$akey] = DataProvider::set($uId,$akey,$allData[$akey]);                         
            }
        }
        
        return $res;
    }
    
    public static function achieve_data($dataObject)
    {
        $php_serial = serialize($dataObject);
        return gzcompress($php_serial, 9);
    }
    
    public static function deachieve_data($achievedData)
    {
        $php_serial = gzuncompress($achievedData);
        return unserialize($php_serial);
    }
}

class StaticCache
{
  const CHANGE = 1;
  const DEL    = 2 ;

  static $data = array();
  static $flag = array();

  public static function save($key)
  {
     self::$flag[$key] = StaticCache::CHANGE;
  }

  public static function delete($key)
  {
     self::$flag[$key] = StaticCache::DEL;
  }
 
  public static function forceSaveAll()
  {
    foreach(self::$flag as $key => $fg)
    {
      if($fg === StaticCache::CHANGE ) self::$data[$key]->forceSave();
      if($fg === StaticCache::DEL)  self::$data[$key]->forceDelete();
    }
    self::$data = array();
    self::$flag = array(); 
  }

  public static function &get($key)
  {
    if(!isset(self::$data[$key]) || self::$flag[$key]== StaticCache::DEL )
        return false ;
    return self::$data[$key];
  }

  public static function check($key)
  {
    return isset(self::$data[$key]);
  }
  
  public static function forceAddAll()
  {
    foreach(self::$flag as $key => $fg)
    {
      if($fg === StaticCache::CHANGE ) $err = self::$data[$key]->forceAdd();
      if($err == false)
      {
          die('Database System Error !!! '.$key);
      }
    }
    self::$data = array();
    self::$flag = array();  
  }
  
  

}

class NonObjectMarket
{
    public $Type;
    public $ItemType;
    public $ItemId;
    public $Num;
    
    function __construct($_type, $_itemType, $_itemId, $_num)
    {
        $this->Type = $_type;
        $this->ItemType = $_itemType;
        $this->ItemId = $_itemId;
        $this->Num = $_num;
    }
}

spl_autoload_register(array('Common','autoload'));

Common::getConfig() ;
Common::loadLib('Controller') ;