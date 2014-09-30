<?php

class Model
{
	public $Id ;
	protected $uId ;
	protected $exKey ;
	protected $keyWord ;

	public function save()
	{
		DataProvider :: set($this->uId . $this->exKey, $this->keyWord,$this->Id,$this) ;
	}


	public function delete()
	{
		DataProvider :: delete($this->uId . $this->exKey, $this->keyWord, $this->Id) ;
	}

}

/**
 * User Model
 * @author Toan Nobita
 * 2/9/2010
 */
 /*
 	 $Name ;                    // ten user
	 $AvatarPic ;               // avatar user
	 $Exp ;                     // diem kinh nghiem
     $Level = 1 ;               // level
     $NewMessage = FALSE ;      // thu moi
	 $NewGift = FALSE ;         // qua moi
     $NewDailyQuest = TRUE ;    // daily quest moi
	 $Money ;                   // tien
	 $ZMoney ;                  // zing xu
	 $AvatarType ;              // loai avatar (boy or Girl)
	 $Energy ;                  // nang luong
	 $LastEnergyTime ;          // lan cuoi update nang luong
	 $LastLuckyTime = 0 ;       // lan cuoi
     $LastGiftTime = 0 ;        // thoi gian cua lan gui qua cuoi cung cua user
	 $AutoId = 10 ;             // AutoId
	 $LakeNumb = 1 ;            // so luong ho nuoi ca
	 $FoodCount ;               // tong so luong thuc an dang co
 //  $MixLakeCount =0 ;         // so luong ho lai co
     $NumReceiver = 0 ;         // so luong nguoi da duoc nhan qua trong ngay cua User
     $LastGetGiftDay = 0;       // time lan cuoi nhan qua hang ngay  cung xac dinh thoi gian login luon
     $NumOnline = 0 ;           // so ngay online lien tuc
    */

class User extends Model
{
	public $Name ;
	public $AvatarPic ;
	public $Exp ;
    public $Level = 1 ;
    public $NewMessage = FALSE ;
	public $NewGift = FALSE ;
    public $NewDailyQuest = TRUE ;
	public $Money ;
	public $ZMoney ;
	public $AvatarType ;
	public $Energy ;
	public $LastEnergyTime ;
	public $LastLuckyTime = 0 ;
    public $LastGiftTime = 0 ;
	public $AutoId = 10 ;
	public $LakeNumb = 1 ;
	public $FoodCount ;
   // public $MixLakeCount =0 ;
    public $NumReceiver = 0 ;
    public $LastGetGiftDay = 0;
    public $NumOnline = 0 ;
    private $TotalZMoney = 0 ;
	private  $DataVersion ;
    public $FirstAddXu = 0 ; // xac dinh trang thai nap xu lan dau , 0 la chua nap , 1 la da nap , 2 la da nhan qua


    public function addTotalZMoney($total)
    {
      $this->TotalZMoney = $total ;
    }


    public function addZingXu($xu,$aArrayInfo = '')
	{
        if($xu == 0 ) return false ;

        $this->ZMoney = $xu ;

        return true ;
	}
    
    public function saveGiftFlag($xu)
    {
        $this->FirstAddXu += $xu;
    }

	public static function getById($uId)
	{
		return DataProvider :: get('User', 'User',$uId ) ;
	}

}
?>
