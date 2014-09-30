<?php

return array(
    
    'Sparta' => array(
        'NumOption' => array(1=>60,2=>30,3=>10),
        'Buff' => array(19=>1, 18=>19, 17=>30, 16=>50),
        'Expired' => array(7=>499,30=>1),
        ),
    'FishDaily' => Type::Superman,
    'RateSparta' => array('Total'=> 1000, 'UserLimit'=>2),
    'RateSwat' => array('Total'=> 1000, 'UserLimit'=>2),    
    'RateSuperman' => array('Total'=> 1000, 'UserLimit'=>2),
    'OptionSuperman' => array(1=>45,2=>45,3=>10),
    'RateDayGift' => array(  // random bonus theo ngay online
          1 => array(1=>25 ,2=> 25 ,3=> 25 ,4=> 25 ,5=> 0 ,6=> 0) , 
          2 => array(
              0=> array (1=> 34 ,2=> 33 ,3=> 33 ,4=> 0,5=>0),
              1=> array (1=> 33 ,2=> 33 ,3=> 33 ,4=> 1,5=>0),
              ), 
          3 => array(1=> 40 ,2=> 40 ,3=> 19, 4=> 1),
          4 => array(1=> 60 ,2=> 30 ,3=> 10),
        ),
    'BattleBonus' => array(
        1=> array(Type::Money=>2, Type::Exp=>2, Type::Material=> array(1=>50,2=>50)),
        2 => array(Type::Money=>5, Type::Exp=>5, Type::Material=> array(1=>40,2=>30,3=>30)),
        3 => array(Type::Money=>7, Type::Exp=>7, Type::Material=> array(2=>40,3=>30,4=>30)),
        4 => array(Type::Money=>10, Type::Exp=>10, Type::Material=> array(2=>40,3=>30,4=>20,5=>10)),
        5 => array(Type::Money=>15, Type::Exp=>15, Type::Material=> array(3=>40,4=>35,5=>20,6=>5)),
        6 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        7 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        8 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        9 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        10 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        11 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        12 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        13 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        14 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        15 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        16 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        17 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        18 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        19 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
        20 => array(Type::Money=>30, Type::Exp=>30, Type::Material=> array(4=>60,5=>30,6=>10)),
    ),
    'BattleRate' => array('Recipe'=>1, 'Material'=>5, 'ExpRate' => 2, 'Gem1Rate' => 1),
    'Soldier' => array(
        'FishMaxAttack' => 50,
        'ValueOfLake' => 5,
        'HealthAttack' => 1,
        'DiedValue' => array(Type::Exp => 1, Type::Money =>1),
        'MinCritical' => 40
    ),
    'SoldierType' => array(
        SoldierType::MATE => array('growup' => true, 'promo' => true, 'reborn' => true, 'clinical' => true, 'biochemical' => true, 'takeEquip' => true),
        SoldierType::BUYSHOP => array('growup' => true, 'promo' => false, 'reborn' => false, 'clinical' => false),
        SoldierType::GIFT_SERIES => array('growup' => true)
    ),
    
    'ActionOnday' => array(
      'BuySuperFishTime' => array(
        Type::Sparta => 1 ,
        Type::Swat => 1 ,
        Type::Superman => 1 ,
        Type::Spiderman => 1 ,
        Type::Batman => 1 ,
      ),
    ),
    'EventND' => array(0,1,20,40,80,100,100,100,100,100,100),
    // hoa mua thu
    'PearFlowerInfo' =>array(
        'FailRewardsPercent' => 10 , // neu ko ra duoc map thi se nhan duoc 10% cac vat pham 
        'rateRandomArrow'=>array(Arrow::TOP=>8,Arrow::DOWN=>10,Arrow::LEFT=>8,0=>75),
        'rateInFate'=>array(CellStatus::GIFT=>30,CellStatus::TORNADO=>20,CellStatus::QUESTION=>50),
        'maxgetPearFlower' => 3 ,
    ),
	'ItemCollection' => array( // item collection for weapon
        'Conflict' => array(
            ItemCollection::HAI_LONG_CHAU => 25,
            Type::Nothing => 75,    
        ),
        'NoConflict' => array(
            ItemCollection::GUOM_ANH_SANG => 35,
            ItemCollection::TRUONG_SAM_SET => 35,
            ItemCollection::THUONG_HOANG_KIM => 25,
            ItemCollection::HUYET_THIET_TRAO => 5,
        ),
    ),
    'rateRandomFishLevelGift' => array(1=>40,2=>40,3=>15,4=>5),
    'EnchantEquipment' => array(
        'SureFail' => 15,
    ),   
    'MaxEnchantLevel'=>100, 
    'PromoXu'=>array(
        'BeginTime'   => mktime(0,0,0,5,8,2012),
        'ExpireTime'   => mktime(0,0,0,5,14,2012),
        'PromoXuRate'=>20,
    ),
    'TypeSet' => array(
        'FullSet' => array('Armor','Helmet','Weapon','Ring','Ring','Bracelet', 'Bracelet', 'Necklace','Belt'),
        'SetEquipment' => array('Armor','Helmet','Weapon'),
        'SetJewel' => array('Ring','Ring','Bracelet','Bracelet','Necklace','Belt'), 
        'AvailableSet' => array('Armor','Helmet','Weapon', 'Ring','Bracelet','Necklace','Belt'),
    ),
    'QuartzTypes' => array('QWhite', 'QGreen', 'QYellow', 'QPurple', 'QVIP'),            
);

?>
