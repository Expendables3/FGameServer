<?php

/**
 * Old User Model
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
 $LastGiftTime = 0 ;        // thoi gian cua lan gui qua cuoi cung cua user
 $AutoId = 10 ;             // AutoId
 $LakeNumb = 1 ;            // so luong ho nuoi ca
 $FoodCount ;               // tong so luong thuc an dang co
 $NumReceiver = 0 ;         // so luong nguoi da duoc nhan qua trong ngay cua User
 $LastGetGiftDay = 0;       // time lan cuoi nhan qua hang ngay  cung xac dinh thoi gian login luon
 $NumOnline = 0 ;           // so ngay online lien tuc
 $TotalZMoney = 0 ;            // 
 $DataVersion = 0 ;
 $MaxFishLevelUnlock = 1 ;
 $SlotUnlock = 4;
 */

class OldUser
{
    public static $Name;
    public static $AvatarPic;
    
    public static $oldMembase;
    
    public static function getLevel($uId)
    {   
        $oldUser =  DataProvider :: getPure('User_User_'.$uId) ;
        if(is_object($oldUser)){
            self::$Name = $oldUser->Name;
            self::$AvatarPic = $oldUser->AvatarPic;  
            return $oldUser->Level;
        }
        else {
            return 0;
        };      
      
    }
}
?>
