<?php
return array (
      'pa_1' => 5,
      'pa_2' => 2,
      'pa_3' => 10,
      'pa_4' => 2,
      'pa_5' => 24*60*60,
      'pa_6' => 2,
      'pa_7' => 4,
      'pa_8' => 10,
      'pa_9' => 10,
      'pa_10' => 20,
      'pa_11' => 3,
      'pa_12' => 6*24*60*60,
      'pa_13' => 3*60,
      'pa_14' => 50,
      'pa_15' => 2*60*60,
      'pa_16' => 500,
      'pa_17' => 50,
      'pa_18' => 50,
      'pa_19' => 60,
      'pa_20' => 5,
      'pa_21' => 10,
      'pa_22' => 1,
      'pa_23' => 10,
      'pa_24' => 5,
      'pa_25' => 100,
      'pa_26' => 10,
      'pa_27' => 3,
      'pa_28' => 17,
      'pa_29' => 1 ,   //NumResetDailyQuest
    'SpartaInfo' => array(
        'Active' =>array('Exp'=>3000,'Money'=>100000),
        'Disable' =>array('Exp'=>90,'Money'=>9000),  
        ),
    'SwatInfo' => array(
        'Active' =>array('Exp'=>5000,'Money'=>150000),
        'Disable' =>array('Exp'=>100,'Money'=>10000),  
    ),
    'BatmanInfo' => array(
        'Active' =>array('Exp'=>7777,'Money'=>200000),
        'Disable' =>array('Exp'=>77,'Money'=>2000),  
    ),
    'SpidermanInfo' => array(
        'Active' =>array('Exp'=>7777,'Money'=>200000),
        'Disable' =>array('Exp'=>77,'Money'=>2000),  
    ),
    'SupermanInfo' => array(
        'Active' =>array('Exp'=>7777,'Money'=>200000),
        'Disable' =>array('Exp'=>77,'Money'=>2000),  
    ),
    'FireworkInfo' => array(
        'Active' =>array('Exp'=>5000,'Money'=>150000),
        'Disable' =>array('Exp'=>90,'Money'=>9000),  
    ),
    'SantaInfo' => array(
        'Active' =>array('Exp'=>5000,'Money'=>150000),
        'Disable' =>array('Exp'=>90,'Money'=>9000),  
    ),
    'TimeGiftSparta' => array(Type::Firework => 8*60*60),
    'EnergyMachine' => 40,
    'TakePicture'  => 60*30,
    'Level_DailyBonus' => array (0,21,31,41,51,61,71,81,91,101,201,301),
    'Level_DailyQuest' => array (0,6,11,16,21,26,31,36,41,46,51,56,61,66,71,76,81,86,91,96,101,200,300),
    'FillEnergy' => array(
          1=>22,2=>66,3=>77,4=>88,5=>99
        ),
    'UseViagra' => 1,
    'SpartaFamily' => array(Type::Sparta,Type::Swat,Type::Batman,Type::Spiderman, Type::Superman, Type::Firework,Type::Ironman, Type::NoelFish),
    'LevelLimit' => 200,
    'FMRequireLevel' => 20,
    'MagicBag' => array(Type::Material, Type::EnergyItem, Type::RebornMedicine,Type::MagicBag),
    'DeltaTime' => 5,
    'ClinicalTime' => 5*24*3600,
    'SickTimeSoldier' => 3*24*3600,
    'ManaRecoverTime' => 5*60,
    //'MinLevelFairyDrop' => 20,
    'PercentLuckyFM' => 10,
    'MaxReborn' => 20,
    'Elements' => array(
        'Conflict' => array(
            Elements::HOA => Elements::KIM,
            Elements::KIM => Elements::MOC,
            Elements::MOC => Elements::THO,
            Elements::THO => Elements::THUY,
            Elements::THUY => Elements::HOA
        ),
    ),
	'PocketTime' => 300, // giay  
    'NguHanh' => 10,
    'RankPoint' => array(-2=>0,-1=>1,0=>2,1=>4,2=>6,3=>6,4=>6,5=>6),
    'MaxAttackFish' => 60,
    'ExpGetAttack' => 100,    // 80% exp base
    'MaxElementOneLake' => 2,
    'MaxSoldier' => 3, // max soldier in lake
    'ND' => array(mktime(0,0,0,9,2,2011),mktime(0,0,0,9,5,2011)),
    'ExpiredGemDay' => 7,   // max day to recover gem
    'NumGemDay' => 7,       // day in use
    'MaxUpgradingGem' => 3,     // max gem upgrading at a time
    'MaxGemSoldier' => 1,       // max gem for a soldier
    'Gem' => array(
        Elements::KIM => array('Max'=>1,'Self'=>true,'Friend'=>true,'Limit'=>100),
        Elements::MOC => array('Max'=>1000,'Self'=>false,'Friend'=>true,'Limit'=>50),
        Elements::THUY => array('Max'=>1,'Self'=>true,'Friend'=>true,'Limit'=>100),
        Elements::HOA => array('Max'=>1,'Self'=>true,'Friend'=>true,'Limit'=>100),
        Elements::THO => array('Max'=>1,'Self'=>true,'Friend'=>true,'Limit'=>100),
    ),
    'MaxTimesAttackLake' => 5,
    'MaxTimesTakePicture' => 3,
    'MaxBonusMachine' => 5000,
    'DailyEnergy' => array(
        'Exp' => array(Type::ItemType => Type::Exp,Type::ItemId => 1, Type::Num => 10),
        'Energy' => array(Type::ItemType => Type::BonusEnergy, Type::ItemId => 1, Type::Num => 5),
        'Special' => array(Type::ItemType => Type::BonusEnergy, Type::ItemId => 1, Type::Num => 100), 
        'MaxTimesDailyEnergy' => 30,
    ),
    
    // event hoa mua thu 
    'DiceXu' => 1 ,
    'MapCoolDown'=>3600,
    'PlayLimit'=>40,
    'FailRewards'=>5000, // so exp nhan duoc khi khong thoat khoi map
    'SuccessRewards'=>20000, // so exp nhan duoc khi thoat khoi map thanh cong
    'AutoMap'=>array(
        1=>119,
        2=>19,       
        ),
        // qua tang khi lan dau vao map
    'ArrowBonus'=> array(
        1=>array('ItemType'=>'Arrow','ItemId'=>1,'Num'=>2),
        2=>array('ItemType'=>'Arrow','ItemId'=>2,'Num'=>2),
        3=>array('ItemType'=>'Arrow','ItemId'=>3,'Num'=>2),
        4=>array('ItemType'=>'Arrow','ItemId'=>4,'Num'=>2),
    ),
    //------------
    
    //gia han do trang tri
    'MaxExpiredItem'=>5 ,
    'ExtensionXuPercent' => 0.8,
    'SoldierEquipment' => array(
        'MaxEquipment' => array(
            SoldierEquipment::Armor => 1,
            SoldierEquipment::Helmet => 1,
            SoldierEquipment::Weapon => 1,
            SoldierEquipment::Belt => 1,
            SoldierEquipment::Bracelet => 2,
            SoldierEquipment::Necklace => 1,
            SoldierEquipment::Ring => 2,
            SoldierEquipment::Mask => 1,
            SoldierEquipment::Seal => 1,
            
        ),
        'Major' => array(
            SoldierEquipment::Armor,SoldierEquipment::Helmet,SoldierEquipment::Weapon 
        ),
        'Minor' => array(
            SoldierEquipment::Belt,SoldierEquipment::Bracelet,SoldierEquipment::Necklace,SoldierEquipment::Ring
        ),
        'TimeExtend' => 2*24*3600,
    ),
    'Magnet' => array(
        'FreeUse' => 3,  
    ),
    'MaxLevelUseMaterial' => 50 , 
    'EquipmentDurability' => 20,
    
    //fish world
    'PercentEffectDeity'=> 50 ,
    'EnergyKillBoss'=> 50 ,
    'EnergyKillMonster'=> 10 ,
    'JoinSeaTime'=>array(1=>0,2=>1800,3=>1800,4=>1800,5=>3600,6=>3600,7=>3600,8=>3600,9=>3600,10=>3600,11=>3600),
    'JoinSeaZingxu'=>array(1=>0,2=>5,3=>5,4=>5,5=>8,6=>8,7=>8,8=>10,9=>10,10=>10,11=>10),
    'BonusRate'=> 10 ,
    //----------
    'TurnAttack' => array('Min' => 0,'Max'=>50),
    'ConvertIncreaseEquipment' => array('IncreaseDamage' => 'Damage','IncreaseDefence' => 'Defence', 'IncreaseVitality' => 'Vitality', 'IncreaseCritical' => 'Critical'),
    'SoldierIndex' => array('Damage','Defence','Critical','Vitality'),
    'CriticalThreshold' => 5000,
    'ConflictDamageRate' => 20,
    'Fighting' => array(
        'CriticalRateElement' => array(
            Elements::KIM => 1,
            Elements::MOC => 1,
            Elements::THO => 50/35,
            Elements::THUY => 50/35,
            Elements::HOA => 1,
        ),
        'AttackFirst' => array(1=>60,0=>40),                                              
    ),
    'EventInGame' => array(
        'MaxTop' => 5,      // top user
        'Loop' => 5,        // loop for set data using same key
        'TimeUpdate' => 15*60,
        'DeltaTimeLuckyUser' => 15*60,
        'Condition' => array(
            1 => array(
                'During' => array(mktime(0,0,0,01,15,2012),mktime(23,59,59,01,21,2012)),
                'MinMatch' => 1500,
                'WinRate' => 50,
            ),        
            2 => array(
                'During' => array(mktime(0,0,0,01,22,2012),mktime(23,59,59,01,28,2012)),
                'MinMatch' => 1500,
                'WinRate' => 50,
            ),
            3 => array(
                'During' => array(mktime(0,0,0,01,29,2012),mktime(23,59,59,02,04,2012)),
                'MinMatch' => 1500,
                'WinRate' => 50,
            ),
            4 => array(
                'During' => array(mktime(0,0,0,02,05,2012),mktime(23,59,59,02,11,2012)),
                'MinMatch' => 1500,
                'WinRate' => 50,
            ),
        )
    ),
    'ProtetedRank' => array(1,2),
    'Market' => array(
        'Fee' => 5/100,
        'MaxPage' => 50,
        'ItemPerPage' => 200,
        'MaxItemPerUser' => 3,
        'TimeUpdateExpiredPage' => 30,
    ),
    'ArmorPillarDefence' => 2000, 
    //bien bang
    'IceSea'=>array(
        'EffectBuff'=>10,
        'HeadNum'   => 5,
    ),
    'CanSellEquipment' => array(
        SourceEquipment::FISHWORLD,
        SourceEquipment::DAILYQUEST,
        SourceEquipment::DAILYGIFT,
        SourceEquipment::EVENT,
        SourceEquipment::CRAFT,
        SourceEquipment::COLLECTION,
        SourceEquipment::LUCKYMACHINE,
        SourceEquipment::TOURNAMENT,
        SourceEquipment::OCCUPY,
    ),
    'CraftingEquipSkills' => array('Armor', 'Weapon', 'Helmet', 'Jewel', 'Magic'),
    'CraftingBuyPower' => array(
    	'Money' => 1000000,
    	'ZMoney' => 1,
    	'Num' => 100,
    	'LimitGold' => 5,
        'LimitG' => 1500,
    ),
    
    'DefenceBlockDamage' => array(
        'Damage' => 40,
        'MaxPercent' => 90,
    ),
    'DeltaTimeLogEquipment' => 1*60*60,
    'ExpireSystemMail' => 14*24*60*60,
    'FullSetJadeSeal' => 9,
    'TrainingGround' => array(
        'TrainingTimeLimit' => 96*3600, // thoi gian max nhat cua mot con ca khi tu luyen trong 1 ngay.
        
        // nhan doi qua theo event
        'BeginTime'   => mktime(0,0,0,8,19,2013),
        'ExpireTime'   =>mktime(23,59,0,8,29,2013),
        'multi'        => 2 ,        // ti le nhan 
    ),
    'ServerBoss'=>array(
        'BeginLevel'=>1,
        'EndLevel'=>500,
        'BeginTime'=>mktime(0,0,0,5,22,2012),
        'ExpireTime'=>mktime(23,59,0,7,27,2019),
        'DiceXu'=>2 ,
        'FreeDice' =>1,
        'TurnAttackMax' => array('Max'=>5,'Min'=>0),
        'WaitTime'=>10, // thoi gian tro hoi sinh 
        'TopUserMax' => 3, // so luong user dung top
        'JoinTime' =>array(
                9=>array('BeginTime'=> '11-00-00','EndTime'=>'12-00-02'),
                18=>array('BeginTime'=> '20-00-00','EndTime'=>'21-00-02'),
            ),
        'IncreaseRate' =>array(1=>0,2=>30,3=>60,4=>80,5=>100,6=>150),
        ),
    'Occupy' => array(
        'BuyToken' => array(
            1 => array('ZMoney' => 1,),
        ),
        'TimeEndInDay' => '10:00:00',
        'CoolDown' => array(
            'AutoRefresh' => 15,
            'RefreshBoard' => 5,
            'Occupy' => 15,
            'SystemRefresh' => 1,
        ),
        'GiftToken' => 10,
        'SetGiftDuration' => 5*60, 
        'MaxOccupy' => 100,
        'Active' => 1,              
    ),
    'Password' =>array(
        'TimeCrackingPassword'=>864000,
        'TimeBlockingPassword'=>1800,
        'MaxTimesInput'=>5,
        'Cost'=>array(
            'ZMoney'=>10,
            'Diamond'=>30
        )
    ),
    'LockMethod' =>array(
        1 =>'sellItem',
        2 =>'buyItem',
        3 =>'refineIngredient',
        4 =>'deleteEquipment',
        5 =>'boostItem',
        6 =>'enchantEquipment',
        7 =>'sell',
        8 =>'addMaterialIntoFish',
        9 =>'useBabyFish',
        10 =>'craftEquipment',
        
    ),
    
    'TreasureIsland'=>array(
        'MaxJoinNum'=>40 , // so lan vao dao / ngay 
        'CellNum'=>array('Min'=>46,'Max'=>56), // so o dat
        'RemainGift'=>array('ItemType'=>'Exp','ItemId'=>'','Num'=>10000),
        'MaxLostNum'=> 3, // so luong qua mat khi bi dua roi vao dau
        'MaxKey'=>1,
        'MaxTreasure'=>2,
        'MaxCoconut'=>1,
        'MaxRockRain'=>3,
        'MaxLucky'=>1,
    ),
    
    //item mo khoa
    'OpenKeyItem'=>array(
        'ZMoney'    => 2,
        'Diamond'   => 4,
    ),
    
//    'MidMoon' => array(
//        'PaperBurn' => 5,
//        'Reborn' => array(
//            'ZMoney' => 10,
//        ),
//        'Healthy' => 3,
//        'MissMoonHome' => MidMonType::MISS_MOON_HOME,        
//    ),
    
    //hammerman
    'HammerManOption' => array(
        '2' => 2,
        '3' => 4,
        '4' => 5,
    ),
    // define number change option allow
    'LimitChangeOption' => array(
        '2' => 2,
        '3' => 4,
        '4' => 5,
    ),
    'NumberPointChangeItem'=>10000,

    'HammerManTime'=>array(
        'StartTime'   => mktime(0,0,0,11,15,2012),
        'EndTime'   => mktime(23,59,57,11,25,2014),        
    ),

    //thedg25 added #accumulationpoint
    'AccumulationPoint'=>array(
        'StartTime'   => mktime(0,0,0,8,19,2013),
        'EndTime'   => mktime(23,59,57,8,29,2013),        
    ),

    'Halloween' => array(
        'SpeedupJoin' => array(
            'ZMoney' => 15,
        ),
        'BuyPack' => array(
            'ZMoney' => 9,
            'Total'  => 10,
        ),
        'BuyKey' => array(
            'ZMoney' => 9,
        ),
        'MakeSweet' =>array(
            'ZMoney' => 5,
        ),
        'AutoUnlock' => array(
            1=> array('ZMoney' =>119 ,),
            2=> array('ZMoney' => 19,),
        ),
        'WaitTime' => 30*60,
        
     ),
          
     'ReputationAward'=>array(
        'FromTime'=>mktime(0,0,0,8,19,2013),
        'ToTime'=>mktime(23,59,57,8,29,2013),
        'Rate'=>2,
     ),

     //SmashEgg
     'EggType'=>array(  'WhiteEgg'=>'WhiteEgg',
                        'GreenEgg'=>'GreenEgg',
                        'YellowEgg'=>'YellowEgg',
                        'PurpleEgg'=>'PurpleEgg'),
     'LimitWhiteEgg'=> 10,
     'TimeEggFree'=>array(  'WhiteEgg'=>10*60, // 10 minus
                            'GreenEgg'=>12*60*60,  // half-day
                            'YellowEgg'=>24*60*60, // 1 day
                            'PurpleEgg'=>3*24*60*60), // 3 day
     
     //---
     'FishSkillInfo'=>array(
        'CatchSkillPer'=>array(-2=>20,-1=>20,0=>30,1=>40,2=>60,3=>80,4=>100),
     ),
     'ServerFea_Disable' => array(
        1 => array(),
        2 => array('OccupyService'),
     ),
     
     'WorldMap'=>array(
        'MoreAttack'=>array("1"=>1,"2"=>2), // tien xu de danh tiep trong world map
        'choseGift' =>array(1=>1,2=>3), // so xu de chon lai qua 
     ),
     
     'TrainingEquipment' =>array(
        'DefaultTemp'   => 50 , // 50 do 
        'EffectFire'    => 5 , // 5 do 
        'EffectWater'   => 5 ,
        'changeTempTime'=> 5*60 , // 5 phut 

     ),
     
    
)                   
?>
