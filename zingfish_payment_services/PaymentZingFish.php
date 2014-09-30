<?php
error_reporting(E_ALL & ~E_NOTICE); //=> show all
ini_set('soap.wsdl_cache_enabled', "0");
//2 line code below for Linux only
putenv('FREETDSCONF=/etc/freetds.conf');
define("RelativePath", "");
define("ERR_PREFIX", "ZingFish");
define("WSDLPath", RelativePath . "wsdl/");
define( "ROOT_DIR" , '..' );

$GLOBALS['THRIFT_ROOT'] ='thrift' ;
require 'config_data.php' ;
require 'DataProvider.php';
require 'log/zf_log.php';



/**
0:   Successful
-6:  Duplicate transaction
-7   DB Exception
-1:  Account is not existed
-2:  Sig param is incorrect (Authenticate failed)
-3:  Money is illegal
-4:  Parameters error
-5:  Money<=  0       
* class PaymentZingFarm
* 
* ### FUNCTIONS LIST ####
* GetUser($zingID,$servicepassword)
* AddMoney($zingID, $nMoney, $transactionID, $servicepassword)
*/

class PaymentZingFish
{

	/**
	 * function GetUser
	 * @param integer $zingID
	 * @param string $servicepassword
	 * @return array $result
	 */
	function GetUser($zingID,$servicepassword)
	{
		global $_SYS_CONFIG;
		if ( md5($zingID.$_SYS_CONFIG['secretkey'])!=$servicepassword)
		{
			$result['0'] = -2;
			$result['1'] = 0;
			return  $result;
		}
		try
		{
			$oUser = User::getById($zingID);
			if(!is_object($oUser) )
			{
				$result['0'] = -1;
				$result['1'] = 0;
				return  $result;
			}
			else
			{

					$result['0'] = 0;
					$result['1'] = $zingID;
			}
		}
		catch(Exception $pdoe)
		{
			$result['0'] = -7;
			$result['1'] = 0;
			return  $result;
		}
		return  $result;
	}

	/**
	 * function AddMoney
	 * @param integer $zingID
	 * @param integer $nMoney
	 * @param string $transactionID
	 * @param string $servicepassword
	 * @return array $result
	 */
	function AddMoney($zingID, $nMoney, $transactionID, $servicepassword)
	{
		global $_SYS_CONFIG;
		if ( md5($nMoney.$zingID.$transactionID.$_SYS_CONFIG['secretkey'])!=$servicepassword)
		{
			$result['0'] = -2;
			$result['1'] = 0;
			return  $result;
		}
		try
		{
      $oUser = User::getById($zingID);      
      $oldMoney = $oUser->ZMoney;
			if(!is_object($oUser) )
			{
				$result['0'] = -1;
				$result['1'] = 0;
				return  $result;
			}
			else
			{
				if($nMoney < 0)
				{
					$result['0'] = -5;
					$result['1'] = 0;
					return  $result;
				}

				if(is_int($nMoney)==false)
				{
					$result['0'] = -3;
					$result['1'] = 0;
					return  $result;	
				}                
				$oUser->addZingXu($nMoney);
   			    $oUser->addTotalZMoney($transactionID);
            
                // luu lai so tien user nap sang 1 key khac
                $xu = $nMoney - $oldMoney ;
                if($xu > 0)
                {
                    $oUser->saveAddXuInfo($xu);
                    $oUser->saveGiftFlag($xu);
                    $oUser->saveSnapShot($xu);
                }
                

                $oUser->save();

				$result['0'] = '0';
				$result['1'] = "$nMoney";
				$result['2'] = "$zingID";
				$result['3'] = "$transactionID";			        
                Zf_log::write_act_log($zingID,$zingID,10,'ConvertXu',0,$nMoney - $oldMoney ,$oUser->Level,$oldMoney,0,0,0,$nMoney);
				return  $result;
			}
		}
		catch(Exception $pdoe)
		{
			$result['0'] = -7;
			$result['1'] = 0;
			return  $result;	
		}
		$result['0'] = -7;
		$result['1'] = 0;
		return  $result;
	}

	private function write_log_file($somecontent)
	{
		$filename = "logfile.txt";
		if(file_exists($filename))
		{
			if (is_writable($filename)) {
			    if (!$handle = fopen($filename, 'a')) {       
			         exit;
			    }			
			    // Write $somecontent to our opened file.
			    if (fwrite($handle, $somecontent." | ") === FALSE) {
			        exit;
			    }
	    		fclose($handle);	
			} 
		}		
	}
}
//$uId = 951854;
//$time  =  time();
//$key   = "5002".$uId.$time."1647737a6091f41cd0f4e02a00e80ea2";
//$o = new PaymentZingFish();
//print_r($o->AddMoney("$uId",5002,$time ,md5($key )));
// ============================================================
$server = new SoapServer(WSDLPath . 'PaymentZingFish.wsdl', array('encoding' => 'ISO-8859-1'));

$server->setClass('PaymentZingFish');
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$server->handle();
}

?>
