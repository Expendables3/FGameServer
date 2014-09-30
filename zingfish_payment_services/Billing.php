<?php
/**
 * Billing Proccess
 * @author Toan Nobita
 */
include("BillingAPI.php");
class Billing
{
	static public $ip = "10.30.44.10";	//VNG
	static public $port = 30002;
	static public $timeout = 30;
    
    /**
	 * Get money of user
	 * @param Username
	 */
	static function balance($uId = 0,$username = 'toannobi')
	{
		if($uId == 0 )
			return -1;

        if(strlen($username)==0)
			return -1;

        $billingSocket = new BillingSocket(self::$ip, self::$port);
		$sk=$billingSocket->openSocket(self::$timeout, $errnum, $errstr);
		if ($sk === null) {
			return -1;
		}
		$balanceInq = new BalanceInq(BILLING_BALANCE_INQUIRY, $username, "", 0);
		$data = $balanceInq->socketDataPack();
		//Send request
		$billingSocket->writeSocket($data);
		//Receive request
		$data="";
		while (!$billingSocket->endOfSocket()) {
			$token = $billingSocket->readSocket(1024);
			$data.= $token;
		}

		$balanceInq->socketDataUnpack($data);
		if ($balanceInq->RetCode === 1) {
			$money 	= hexdec($balanceInq->CashRemain); //money user
		}
		else {
			$money	=	 -2;
		}
		unset($balanceInq);
		$billingSocket->closeSocket();
		unset($billingSocket);
		return $money;

	}

	static function purchase($uId = 0,$username = 'toannobi',$nMoney = 0, $aArrayInfo = '')
	{

        if($uId ==0 || intval($nMoney)==0)
			return -1;

        if(strlen($username)==0)
			return -1;

		$Item		=	explode(':',$aArrayInfo);
		$ItemQua	=	$Item[2];
		$ItemName	=	$Item[1];
		$ItemID		=	$Item[0];

		$billingSocket = new BillingSocket(self::$ip, self::$port);
		$sk=$billingSocket->openSocket(self::$timeout, $errnum, $errstr);
		if ($sk === null) {
			return -1;
		}
		$purchaseIDInq = new PurchaseIDInq(BILLING_PURCHASEID_INQUIRY, $username, "", 0);
		$data = $purchaseIDInq->socketDataPack();
		$billingSocket->writeSocket($data);
		$data = null;
		$data="";
		while (!$billingSocket->endOfSocket()) {
			$token = $billingSocket->readSocket(1024);
			$data.= $token;
		}

		if($data)
		{
			$purchaseIDInq->socketDataUnpack($data);
		}
		if ($purchaseIDInq->RetCode === 1 && strlen($purchaseIDInq->PurchaseID)>0)
		{   

			$billingSocket = new BillingSocket(self::$ip, self::$port);
			$sk=$billingSocket->openSocket(self::$timeout, $errnum, $errstr);
			if ($sk === null) {
				return -1;
			}

			$nTransaction	=md5($uId.$username.microtime());
			$itemPurchase = new ItemPurchase(
				BILLING_ITEM_PURCHASE,
				$purchaseIDInq->PurchaseID,
				$username,
				dechex($nMoney),
				$ItemID,
				$ItemQua,
				$ItemName,
				dechex($nMoney),
				$nTransaction,
				0,
				0);
			$data = $itemPurchase->socketDataPack();
			$billingSocket->writeSocket($data);

			$data="";
			while (!$billingSocket->endOfSocket()) {
				$token = $billingSocket->readSocket(1024);
				$data.= $token;
			}
			$itemPurchase->socketDataUnpack($data);

			if ($itemPurchase->RetCode === 1) {

				$money	=	intval(hexdec($itemPurchase->CashRemain));
			}
			else
			{
				$money	= -2;
			}
		}
		else
		{
			$money	= -3;
		}
		unset($purchaseIDInq);
		$billingSocket->closeSocket();
		unset($billingSocket);
		return $money;
	}
    
    static function promo($uId,$username = 'toannobi',$nMoney = 1,$campain = 1,$accountNumb = 1,$admin = 'ToanTN')
    {

        if($uId ==0 || intval($nMoney)==0)
            return -1;

        if(strlen($username)==0)
            return -1;

        $billingSocket = new BillingSocket(self::$ip, self::$port);
        $sk=$billingSocket->openSocket(self::$timeout, $errnum, $errstr);
        if ($sk === null) {
            return -1;
        }
        $cashIDInq = new CashIDInq(BILLING_PAY_PACK_CASHID, $username, "", 0);
        $data = $cashIDInq->socketDataPack();
        $billingSocket->writeSocket($data);
        $data = null;
        $data="";
        while (!$billingSocket->endOfSocket()) {
            $token = $billingSocket->readSocket(1024);
            $data.= $token;
        }

        if($data)
        {
            $cashIDInq->socketDataUnpack($data);
        }
        if ($cashIDInq->RetCode === 1 && strlen($cashIDInq->CashID)>0)
        {
            $billingSocket = new BillingSocket(self::$ip, self::$port);
            $sk=$billingSocket->openSocket(self::$timeout, $errnum, $errstr);
            if ($sk === null) {
                return -1;
            }

            $nTransaction    =md5($uId.$username.microtime());
            
            $cashPromo = new PromoCash(
                BILLING_PAY_PACK_PROMO,
                $cashIDInq->CashID,
                $username,
                dechex($nMoney),
                $accountNumb,
                dechex($nMoney),
                $nTransaction,
                $admin,
                $campain,
                0);
           
            $data = $cashPromo->socketDataPack();
            $billingSocket->writeSocket($data);
            $data="";
            while (!$billingSocket->endOfSocket()) {
                $token = $billingSocket->readSocket(1024);
                $data.= $token;
            }
            $cashPromo->socketDataUnpack($data);

            if ($cashPromo->RetCode === 1) {

                $money    =    intval(hexdec($cashPromo->CashRemain));
            }
            else
            {
                $money    = -2;
            }
        }
        else
        {
            $money    = -3;
        }
		var_dump($cashPromo->RetCode);
        unset($cashIDInq);
        $billingSocket->closeSocket();
        unset($billingSocket);
        return $money;
    }
}

//Billing::init();