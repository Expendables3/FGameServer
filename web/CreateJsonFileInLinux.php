<?php
error_reporting(E_ALL & ~E_NOTICE); //=> show all
ini_set('display_errors', 0);
//ini_set('error_log', '../log/error.log');
ini_set("memory_limit", "-1"); // php limit allocated executing memory causes memory leaks, expand or unlimit

header('Cache-Control: no-cache, must-revalidate, max-age=0',true);
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT',true);
header('Pragma: no-cache',true);
define( "IN_INU" , true );

define( "ROOT_DIR" , '/home/www/webroot/myFish' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
 define( "XML_DIR" , ROOT_DIR ."/imgcache/xml" );
/*define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );

define( "BETA_DIR" , ROOT_DIR ."/BetaTest" );
define( "TPL_DIR" , ROOT_DIR ."/Tpl/Index");*/

require LIB_DIR.'/Common.php';
/*require LIB_DIR.'/ZingApi.php';
require LIB_DIR.'/dal/DataProvider.php';*/

$a = array(       
          "OceanAnimal",
          "Lake",
          "Fish",
          "Food",
          "Gift",
          "OceanTree",
          "Other",
          "UserLevel",
          "Energy",
          "Experience",
          "MaxEnergy",
          "DayGift",
          "Material",
          "EnergyItem","UpgradeMaterial",
          "LevelUnlockSlot","FishGift",
          "RareOption",
          "DailyQuestGift","XuDailyQuest",
          "SeriesQuest",
          "ActionGift","Fishing",
          "LevelUpUser",
          "MixFish","Param",
          "MateFishCost","DailyQuest",
          "License","LevelSkill","MoneySkill",
          "SpecialSkill","RareSkill","RateOfMaterial",
          "MixFishBonus","Viagra",
          "EnergyMachine",
          "Event","Petrol",
          "M_MasteryPoint",
          "M_MaterialSkill",
          "Material","FishIdFollowLevel",
          "RebornMedicine","MagicBag",
          "FishMachineExchange",
          "MixFormula","Damage","RankPoint",
          "BuffItem","Ginseng","RecoverHealthSoldier",
          "DefenceSoldier","SuperFish",
          "Gem",
          "BackGround", "Wars_Weapon","Wars_Helmet","Wars_Armor","MagnetItem",
          "Wars_Ring","Wars_Belt","Wars_Necklace","Wars_Bracelet","Wars_Mask", "Wars_Seal",
          "NewUserGiftBag","ItemCollectionExchange","ItemCollectionTrunk","Equipment_Extend",
          "EnchantEquipment_Minor","EnchantSlot","EquipmentRate",
          
          "LotusFlower","M_GiftContent","RankPointBottle","Ticket",
          "GodCharm","ItemCollection","BlackMarketShop",
          "IngredientsRefining","CraftingEquip", "Crafting_Exp", "Soldier","DiamondExchange",
          
           "HappyWeekDay",'CustomTraining',
           "PowerTinh_Quest","PowerTinhQuest_Reward","MagicPotion_Auto","MagicPotion_AutoCost",
           "MeridianPointRequire", "ActiveMeridian", 
            
           "Tournament", "Tournament_Card", "Tournament_Reward",
           "Skill_Map", "Skill_Challenge", "Skill_Monster", "Skill_Question", "Skill_Ways", "Skill_Skill", "Skill_Effect", "Skill_Boss",
           "ServerBossInfo","ServerBossTime","ServerBossGift", "ServerBossBonus",'ServerBossDice',
           "Occupy_Gifts",
          // "ExpeditionGift","ExpeditionQuest","SilkRoad","ExpeditionChest","ExpeditionChance",
           "ChangeEnchantLevel","PowerTinhRequireEnchant",'FirstAddXuGift','HammerMan_Lookup','HammerMan_Option','HammerMan_Finish','Accumulation_Point','Accumulation_Gift',
            "ReputationInfo","ReputationBuff",
           'VipBox_Item','VipBox_Bonus','VipBox_BonusDetail',
           "SmashEgg_Hammer","SmashEgg_EggHammer","SmashEgg_Quartz","SmashEgg_QuartzLevel","SmashEgg_Bonus","SmashEgg_Slot","SmashEgg_Discount","WorldMap_Monster","WorldMap_Gift",
           
    );
$EventList = array(    
    //"Event_8_3_Flower","GiftBox",
    //"HerbBossReward","HerbPotion","MagicPotion_Cost","MagicPotion_Quest","HerbBoss","MagicPotion_QuickDoneQuest","MagicPotion_ExchangeJadeSeal",
    //"MeridianPosition",
    //"BirthDayCandle","BirthDayGiftBox","BirthDayItem","BirthDayLight", 
    //'EventEuro_Teams', 'EventEuro_BetLevel', 'EventEuro_TopGifts','EventEuro_BetGifts',
    //'IceCreamItem',"EventDiscount",
    ///"Island_GiftMap","Island_StateMap","Island_Item","Island_Map","IsLand_AutoDig","IsLand_Collection","Island_GiftMedal",
    //"MidMoon_GroupGiftMap", "MidMoon_MoveItem", "MidMoon_GenMap","MidMoon_Collection","MidMoon_Lookup","MidMoon_GiftMedal"
    //"Arrow","FinishRewards","MapRewards","PearFlower","TreasureRewards","Map","Question","LuckyRewards","CoralTree","AutoMap",'RefreshRockMazeMap',"VipMedalBox",
    'Hal2012_MapItemId', 'Hal2012_AutoMap', 'Hal2012_GiftMedal', 'Hal2012_GhostGift', 
   // 'ColP_BuyItem', 'ColP_ExchangeGift', 
    //'CoralTree',
    "KeepLogin_Gift",
    //"Noel_Candy","Noel_Bullet","Noel_Make","Noel_Fish","Noel_BoardGame","Noel_BoardConfig","Noel_Bonus","Noel_Tree"
);

$a_1 = array(
    "Sea","IceWave","SeaBonus",'ForestEffect','ForestGift',
);

$a_2 = array("SeaMonster");

$b = array();

//$b['header']= file_get_contents(XML_DIR."/header.xml")."\n";// phan header

$file = fopen(XML_DIR . "/json.txt", 'w');  
$ver = Common::getSysConfig('versionJson');
$file2 = fopen(XML_DIR . "/json".$ver.".txt", 'w');  
$json = '{';
fwrite($file, $json, strlen($json)); 
fwrite($file2, $json, strlen($json));  

foreach ($EventList as $value)
{
    $json = '';
    $b[$value] = Common::getConfig($value);         
    $encode = json_encode($b);
    //Cat dau
    $encode = substr($encode, 1);
    //Cat duoi
    $encode = substr($encode, 0, -1);
    $json .= $encode.', ';
    $b = array();
    fwrite($file, $json, strlen($json));     
    fwrite($file2, $json, strlen($json));  
}

foreach ($a_1 as $value)
{
    $json = '';
    $b[$value] = Common::getWorldConfig($value);    
    $encode = json_encode($b);   
    //Cat dau  
    $encode = substr($encode, 1);
    //Cat duoi 
    $encode = substr($encode, 0, -1);
    $json .= $encode.', ';
    $b = array();       
    fwrite($file, $json, strlen($json));  
    fwrite($file2, $json, strlen($json));    
} 

foreach ($a as $value)
{
    $json = '';
	$b[$value] = Common::getConfig($value);         
    $encode = json_encode($b);
    //Cat dau
    $encode = substr($encode, 1);
    //Cat duoi
    $encode = substr($encode, 0, -1);
    $json .= $encode.', ';
    $b = array();
    fwrite($file, $json, strlen($json));     
    fwrite($file2, $json, strlen($json));  
}
 
foreach ($a_2 as $value)
{
    $json = '';
    $b[$value] = Common::getConfig($value);    
    $encode = json_encode($b);   
    //Cat dau  
    $encode = substr($encode, 1);
    //Cat duoi 
    $encode = substr($encode, 0, -1);
    $json .= $encode.', ';
    $b = array();    
} 

$json = substr($json, 0, -2);                 
$json .= '}';  
fwrite($file, $json, strlen($json));  
fwrite($file2, $json, strlen($json));             


fclose($file);
echo "OK_json_dev <br>";


fclose($file2);
echo "OK_json_ver"; 
