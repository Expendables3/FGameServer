<?php
$GLOBALS['THRIFT_ROOT'] = LIB_DIR.'/thrift' ;

require LIB_DIR.'/log/zf_log.php';


class Arrow
{
    const  TOP      = 1 ;
    const  DOWN     = 2 ;
    const  LEFT     = 3 ;
    const  RIGHT    = 4 ;
    const  KEY      = 5 ;
}
class CellStatus
{
    const CELL_START        = 1;
    const CELL_END          = 2;
    const CELL_FATE         = 3;
    const CELL_TREASURE     = 4;
    const CELL_SPECIAL_TREASURE  = 5;

    const CELL_EXP          = 10;
    const CELL_MATERIAL     = 11;
    const CELL_ENERGYITEM   = 12;
    const CELL_BABYFISH     = 13;
    
    const GIFT              = 20 ;
    const TORNADO           = 21 ;
    const QUESTION          = 22 ;
}

// cac loai minigame
class GameType
{
    const LuckyMachine = 'LuckyMachine';
    
    static public function check($GameType)
    {
      if($GameType == GameType::LuckyMachine)
      return true ;
      return false ;  
    } 
    
}

class RoadState
{
    const NORMAL    = 0 ;   //  binh thuong
    const GOING     = 1 ;   // dang buoc di 
    const TORNADO   = 2 ;  // dang bi loc xoay
    const DICE      = 3 ;  // dang do xuc xac
    const ANSWER    = 4 ;  // dang tra loi cau hoi    
}

 class CheckType
 {
   const BeforeCheck = 1 ;
   const AfterCheck = 2 ;
 }
 
class OptionFish
{
    const MONEY = 'Money';
    const TIME = 'Time';
    const EXP = 'Exp';
    const MIXFISH = 'MixFish';
    const SPECIAL = 'MixSpecial';
}

class Skill
{
    const Money = 'MoneySkill';
    const Level  = 'LevelSkill' ;
    const Special = 'SpecialSkill';
    const Rare  = 'RareSkill';
    
    static public function check($Skill)
    {
      if($Skill == Skill::Money || $Skill == Skill::Level 
      || $Skill == Skill::Special || $Skill == Skill::Rare)
      return true ;
      return false ;  
    } 
}

class Rank
{
  const BINH_BET  = 1;
  const BINH_NHI  = 2;
  const BINH_NHAT = 3;
  const HA_SI     = 4;
  const TRUNG_SI  = 5;
  const THUONG_SI = 6;
    
}

class Battle
{
    const WIN = 1;
    const LOSE = 0;
    const CRITICAL = 2;
}

class SoldierStatus
{
    const HEALTHY = 1;
    const CLINICAL = 2;
    const DIED   = 3;
}

class Elements
{
  const  NEUTRALITY = 0;
  const  KIM = 1;
  const  MOC = 2;
  const  THO = 3;
  const  THUY = 4;
  const  HOA = 5; 
}

class SeaRound
{
    const ID_ROUND_1 = 1;
    const ID_ROUND_2 = 2;
    const ID_ROUND_3 = 3;
    const ID_ROUND_4 = 4;
}

class SeaType
{
    
    const SEA_1 = 1 ; 
    const SEA_1_HARD = 101 ; 
    const SEA_2 = 2 ;   // bien kim
    const SEA_2_HARD = 102 ;   // bien kim mode kho
    const SEA_3 = 3 ;   // bien bang
    const SEA_3_HARD = 103 ;   // bien bang mode kho
    const SEA_4 = 4 ;   // bien Moc
    const SEA_4_HARD = 104 ;   // bien Moc mode kho
    const SEA_5 = 5 ;
    const SEA_6 = 6 ; 
    
    static public function check($SeaId)
    {
      if($SeaId == SeaType::SEA_1 || $SeaId == SeaType::SEA_2 || $SeaId == SeaType::SEA_3 
      || $SeaId == SeaType::SEA_4 || $SeaId == SeaType::SEA_5 || $SeaId == SeaType::SEA_6)
        return true ;
      return false ;  
    } 
    
}

class SceneAttack
{
    const NORMAL = 0;
    const CRITICAL = 1;
    const MISS = 2;
}

class SoldierType
{
    const MATE = 1;
    const BUYSHOP = 2;
    const GIFT_SERIES = 3;                                   
}


class FormulaType
{
  const  Draft    = 'Draft';
  const  Paper    = 'Paper';
  const  GoatSkin = 'GoatSkin';
  const  Blessing = 'Blessing';
  const  Rent     = 'Rent' ;
  
  static public function checkExist($Type)
    {
      if($Type == FormulaType::GoatSkin || $Type == FormulaType::Paper 
      || $Type == FormulaType::Draft || $Type == FormulaType::Blessing || $Type == FormulaType::Rent )
        return true ;
      return false ;  
    }
}

class BuffItem
{
  const Samurai     = 'Samurai';
  const Resistance  = 'Resistance';
  const BuffExp     = 'BuffExp';
  const BuffMoney   = 'BuffMoney';
  const BuffRank    = 'BuffRank';
  const StoreRank   = 'StoreRank';
  const Dice        = 'Dice';
  
  static public function checkExist($Type)
    {
      if($Type == BuffItem::Samurai || $Type == BuffItem::Resistance || $Type == BuffItem::BuffRank 
      || $Type == BuffItem::BuffExp || $Type == BuffItem::BuffMoney || $Type == BuffItem::StoreRank
      || $Type == Type::Ginseng     || $Type==Type::RecoverHealthSoldier || $Type == BuffItem::Dice )
        return true ;
      return false ;  
    }
  
}

class SoldierEquipment
{
    const Armor = 'Armor';
    const Helmet = 'Helmet';
    const Weapon = 'Weapon';
    const Ring = 'Ring';
    const Bracelet = 'Bracelet';
    const Necklace = 'Necklace';
    const Belt = 'Belt';
    const Mask = 'Mask';
    const Seal = 'Seal';
    static public function checkExist($Type)
    {
      if($Type == SoldierEquipment::Armor 
      || $Type == SoldierEquipment::Helmet 
      || $Type == SoldierEquipment::Weapon 
      || $Type == SoldierEquipment::Ring 
      || $Type == SoldierEquipment::Bracelet 
      || $Type == SoldierEquipment::Necklace
      || $Type == SoldierEquipment::Belt 
      || $Type == SoldierEquipment::Mask) // ko cho seal vao vi Seal duoc tao o class khac
        return true ;
      return false ;  
    }
}


// param
class PARAM{
    const FoodAmount = 'pa_1';
    const MoneyFishSick = 'pa_2';
    const MoneyCareFish = 'pa_3';
    const MoneySellItem = 'pa_4';
    const TimeSick = 'pa_5';
    const TimeDirty = 'pa_6';
    const MoneyMate = 'pa_7';
    const TimeCare = 'pa_8';
    const NumFriendGift = 'pa_9';
    const MateFail = 'pa_10';
    const MaxDirty = 'pa_11';
    const ExpireMail = 'pa_12';
    const TimeRecoverEnergy = 'pa_13';
    const MaxEnergy = 'pa_14';
    const TimeCollectStar = 'pa_15';
    const MaxExp = 'pa_16';
    const MaxMoney = 'pa_17';
    const MaxTime = 'pa_18';
    const OverLevel1 = 'pa_19';
    const MaxLevelOverUser = 'pa_20';
    const TimeRareFish = 'pa_21';
    const OptionSpecialFish = 'pa_22';
    const ZMoneyQuest2 = 'pa_23';
    const FishOverLevel = 'pa_24';
    const MaxFishLevel = 'pa_25';
    const MyEnergyBonus = 'pa_26';
    const FriendEnergyBonus = 'pa_27';
    const MaxEnergyBonus = 'pa_28';
    const NumResetDailyQuest = 'pa_29';
    const SpartaInfo  = 'SpartaInfo';  
    const SwatInfo  = 'SwatInfo';
    const BatmanInfo = 'BatmanInfo';
    const SpidermanInfo = 'SpidermanInfo';
    const SupermanInfo = 'SupermanInfo';
}

// cac loai ca 
class FishType{
    const     NORMAL_FISH     = 0 ;
    const     SPECIAL_FISH     = 1 ;
    const     RARE_FISH         = 2 ; 
    const     SOLDIER    = 3;   
}
// cac mau cua ca
class ColorType
{
    const     EMPTY_COLOR     = 0 ;
    const     FIRST_COLOR     = 1 ;
    const     SECOND_COLOR     = 2 ;
}

class Type
{
    const Exp = 'Exp'   ;
    const Money = 'Money' ;
    const ZMoney = 'ZMoney';
    const ItemType = 'ItemType';
    const ItemId = 'ItemId';
    const Num = 'Num';
    const Food = 'Food'; 
    const FishGift = 'FishGift';
    const Material= 'Material' ;
    const Other = 'Other' ;
    const OceanTree = 'OceanTree';
    const OceanAnimal = 'OceanAnimal';
    const BabyFish = 'BabyFish';
    const Medicine = 'Medicine';
    const Fish = 'Fish';
    const EnergyItem = 'EnergyItem';
    const License  = 'License';
    const Energy  = 'Energy';
    const Event = 'Event';
    const Nothing = 'Nothing';
    const Sparta = 'Sparta';
    const Swat  = 'Swat' ;
    const EnergyMachine = 'EnergyMachine';
    const Petrol = 'Petrol';
    const Viagra = 'Viagra';
    const Batman = 'Batman';
    const Superman = 'Superman';
    const Spiderman = 'Spiderman';
    const RebornMedicine = 'RebornMedicine';
    const MagicBag = 'MagicBag';
    const Mermaid = 'Mermaid';
    const MixFormula ='MixFormula';
    const Rank = 'Rank';
    const Ginseng     = 'Ginseng';
    const BuffItem    = 'BuffItem';
    const Recipe = 'Recipe';
    const SuperFish = 'SuperFish';
    const Depression = 'Depression'; 
    const Happiness = 'Happiness'; 
    const Fierce   = 'Fierce'; 
    const Soldier = 'Soldier';
    const RecoverHealthSoldier = 'RecoverHealthSoldier';
    const FairyDrop    = 'FairyDrop' ;
    const Firework = 'Firework';
    const Gem = 'Gem';
    const BonusEnergy = 'BonusEnergy';
    const SpecialFish = 'SpecialFish';
    const RareFish = 'RareFish';  
    const DragonBall = 'DragonBall' ; 
    const Ironman   = 'Ironman' ;
    const Arrow     = 'Arrow' ; // mui ten di chuyen
    const PearFlower = 'PearFlower'; // hoa le 
    const BackGround = 'BackGround';
    const Magnet = 'Magnet';
    const MagnetItem = 'MagnetItem';
    const ItemCollection = 'ItemCollection';
    const ItemTrunk = 'ItemTrunk';
    const Visa      = 'Visa' ;     // key use to open sea in Fish world
    const NoelFish = 'Santa';
    const SoldierEquipment = 'SoldierEquipment';
    const LotusFlower   = 'LotusFlower' ;   // hoa sen
    const Ticket    = 'Ticket';     // ve quay so
    const LockTicket = 'LockTicket'; // ve quay so loai khoa
    const RankPointBottle = 'RankPointBottle'; // binh chien cong cho ca linh 
    const GodCharm = 'GodCharm';
    const Oyster     = 'Oyster'; 
    const Event_8_3_Flower = 'Event_8_3_Flower';
    const Diamond = 'Diamond';  // xu giao dich trong cho den
    const VipMedal = 'VipMedal'; // huy truong vip
    const Herb = 'Herb';
    const HerbPotion = 'HerbPotion';
    const HerbMedal = 'HerbMedal';
    const RandomEquipment = 'RandomEquipment';
    const PowerTinh = 'PowerTinh';
    const JadeSeal = 'Seal';
    const Mask = 'Mask';
    const BirthDayItem = 'BirthDayItem';
    const IceCreamItem = 'IceCreamItem';
    const GotEquipMainQuest = 'Armor';
    const EuroBall = 'EuroBall';
    const Jade  = 'Jade';
    const Iron  = 'Iron';
    const SixColorTinh  = 'SixColorTinh';
    const SoulRock  = 'SoulRock'; // hon thach
    const Meridian  = 'Meridian';       // kinh mach
    
    const EquipmentChest = 'EquipmentChest';
    const JewelChest = 'JewelChest';
    const AllChest  = 'AllChest';
    
    const FullSet = 'FullSet';
    const SetEquipment = 'SetEquipment';
    const SetJewel = 'SetJewel';
    const Island_Item = 'Island_Item';
    
    const RankPoint = 'RankPoint';
    const OpenKeyItem = 'OpenKeyItem';
    //thedg25 added midmoon event material type 
    const Propeller = 'Propeller';
    const SpaceCraft = 'SpaceCraft';
    const GasCan = 'GasCan';
    const Paperburn = 'Paperburn';
    const MoonCollection = 'Collection';
    const HalItem = 'HalItem';
    // #SmashEgg
    const Quartz = "Quartz";
    const OccupyToken  = "OccupyToken" ;
    const VipTag    = 'VipTag';
    //---

}

class ItemCollection
{
    const GUOM_ANH_SANG = 1;
    const TRUONG_SAM_SET = 2;
    const THUONG_HOANG_KIM = 3;
    const HUYET_THIET_TRAO = 4;
    const HAI_LONG_CHAU = 5;
    
    const BONG_BIEN = 6;
    const VIT_PHAO = 7;
    const VAN_TRUOT = 8;
    const TOM_THAN = 9;
    const CUA_THAN = 10;
    
    const TUI_NILON = 11;
    const VO_LON = 12;
    const XUONG_CA = 13;
    const GIAY_CU = 14;
    const DO_CO = 15;
    
    const TRUNG_THAN = 16;   
}

class SourceEquipment
{
    const SHOP = 1;     // do mua trong shop - ko ban
    const FISHWORLD = 2; // do nhan duoc tu the gioi ca  - co ban 
    const DAILYGIFT = 3;  // do nhan duoc tu dailygift  - co ban
    const COLLECTION = 4; // do doi duoc tu bo suu tap  - co ban
    const DAILYQUEST = 5; // do nhan duoc tu dailyquest - co ban
    const EVENT = 6;        // do nhan duoc tu event    - co ban
    const LUCKYMACHINE  = 7; // do nhan duoc tu may quay so - co ban
    const CRAFT = 8;        // do co duoc do che tao    - co ban
    const OPEN_BOX = 9;     // do nhan duoc tu viec mo hop qua vip - ko ban
    const GIVE = 10; // do tang - ko ban 
    const TOURNAMENT = 11;	// phan thuong tournament
    const OCCUPY = 12;
	const NPC = 13;
    const MAINQUEST = 14;
    
    static public function checkExist($Type)
    {
      if($Type == SourceEquipment::SHOP 
      || $Type == SourceEquipment::FISHWORLD 
      || $Type == SourceEquipment::DAILYGIFT
      || $Type == SourceEquipment::COLLECTION 
      || $Type == SourceEquipment::DAILYQUEST 
      || $Type == SourceEquipment::EVENT
      || $Type == SourceEquipment::LUCKYMACHINE
      || $Type == SourceEquipment::CRAFT
      || $Type == SourceEquipment::OPEN_BOX
      || $Type == SourceEquipment::GIVE
      || $Type == SourceEquipment::OCCUPY 
      || $Type == SourceEquipment::NPC
      || $Type == SourceEquipment::MAINQUEST )
        return true ;
      return false ;  
    }
    
}

class Trunk
{
    const GOLD = 1;
    const SILVER = 2;
    const BRONZE = 3;   
}

class UnlockType
{
    const  Level = 1 ;
    const  Mix = 2 ;
    const  Quest = 3 ;
    const  ZMoney = 4 ;
    const  Gift   = 5 ;
    const  Unused = 6 ;   
}


class EventInGameFW
{
    const RANDOM = 1;
    const WEEK = 2;
    const MONTH = 3;
}

class Error
{
    const SUCCESS         = 0 ;
    const LOGIN         = 1 ;
    const NO_REGIS         = 2 ;
    const CREADTED         = 3 ;
    const NO_FOUND         = 4 ;
    const NOT_FRIEND     = 5 ;
    const PARAM         = 6 ;
    const FISH_NOT_EXITS       = 7 ;
    const NOT_ENOUGH_MONEY     = 8 ;
    const FISH_NOT_SICK     = 9 ;
    const NOT_ENOUGH_ENERGY = 10 ;
    const NOT_ENOUGH_FOOD     = 11 ;
    const NAME_INVALID         = 12 ;
    const TYPE_INVALID         = 13 ;
    const NOT_ENOUGH_LEVEL     = 14 ;
    const LAKE_INVALID         = 15 ;
    const LAKE_FULL         = 16 ;
    const FISH_NO_BIRTH     = 17 ;
    const NOT_ENOUGH_ITEM     = 18 ;
    const FISH_MATURE         = 19 ;
    const CANT_TURN         = 20 ;
    const CANT_FISHING             = 21 ;
    const NOT_ENOUGH_ZINGXU     = 22 ;
    const KEPT_IN_INVENTORY     = 23 ;
    const OBJECT_NULL             = 24 ;
    const CANT_UPGRADE_LAKE     = 25 ;
    const OVER_NUMBER            = 26 ;
    const CANT_CARE_FISH         = 27 ;
    const ACTION_NOT_AVAILABLE     = 28 ;
    const RECIEVER_NULL         = 29 ;
    const GIFT_SENT_TODAY        = 30 ;
    const ARRAY_NULL            = 31 ;
    const NOT_GIFT                = 32 ;
    const SLOT_ERROR            = 33 ;
    const NOT_ENOUGH_TIME       = 34 ;
    const ID_INVALID            = 35 ;
    const NOT_LOAD_CONFIG       = 36 ;
    const MIX_FALSE             = 37 ;      // lai ca that bai
    const NOT_LOAD_INVENTORY    = 38 ;
    const MIXLAKE_INVALID       = 39 ;      // khong co be lai nay
    const MIXLAKE_IS_RESETTING  = 40 ;      // be lai dang reset
    const MIXLAKE_EXITS         = 41 ;      // be lai loai nay da co roi
    const NOT_ENOUGH_EXP         = 42 ;
    const THE_SAME_SEX          = 43 ;      // cung gioi tinh
    const FULL_LEVEL            = 44 ;      // da du level khong nang cap duoc nua
    const GOT_GIFT              = 45 ;      // nguoi nay da nhan qua roi
    const NOT_COMPLETE_TASK     = 46 ;      // nhiem vu chua hoan thanh
    const FEEDED_ON_WALL        = 47 ;      // da feed len tuong 1 lan roi
    const SAVE_BONUS_FAIL       = 48 ;      // khong the luu thong tin qua tang
    const NOT_BETA_USER         = 49 ;
    const NO_HAVE_MONEY_POCKET  = 50 ;
    const CANT_STEAL_MONEY      = 51 ;
    const ALREADY_STEAL_MONEY   = 52 ;
    const FISH_OVER_LEVEL       = 53 ;      // level unlock cua ca vuot qua level gioi han cua be lai
    const NOT_ENOUGH_MATERIAL   = 54 ;      // khong du nguyen lieu
    const NOT_EGG               = 55 ;      // khong phai la trung
    const FULL_NODE             = 56 ;      // >19 dot tre
    const CANT_LINK             = 57 ;      //  Khong the noi cay tre
    const BOOST_FAIL            = 58 ;       // ep do khong thanh cong
    const SLOT_NOT_UNLOCK        = 59 ;        // chua unlock slot
    const QUEST_DONE            = 60 ;        // quest da hoan thanh
    const QUEST_NOT_COMPLETE    = 61 ;         // quest chua hoan thanh
    const CREATED_GIFT_FOR_FISH = 62 ;        // da khoi tao qua cho ca 
    const NOT_UNLOCK_QUEST        = 63 ;        // chua unlock quest2
    const QUEST_UNLOCKED        = 64 ;        // quest2 da unlock
    const SLOT_UNLOCKED         = 65;         // slot lai ca da unlock
    const NOT_ENOUGH_LICENSE  = 66 ;    // ko du giay phep mo rong ho 
    const CAN_NOT_USE_ENERGYITEM = 67 ;   // ko the su dung them EnergyItem trong kho vi da vuot qua so luong
    const NO_FRIEND_INVITED        = 68;  // chua moi duoc ban nao
    const NO_ENERGY_MACHINE       =69;    // user chua co may tang nang luong
    const NOT_ENOUGH_MASTERY_LEVEL = 70; // khong du mastery level
    const NOT_ENOUGH_MASTERY_POINT = 71;  // khong du diem mastery
    const CAN_NOT_LEVELUP         = 72; // khong the level up tiep (maxlevel)
    const CANT_USE_VIAGRA         =73;    // khong the su dung viagra
    const CANT_FILL_ENERGY        =74;    // da du so lan su dung fill day nang luong
    const NO_EXPIRED              =75;     // chua het han
    const CANT_TAKE_PICTURE       =76;    // vua chup anh
    const FISH_NOT_DIE            =77;      // ca SpartaFamily chua chet
    const FISH_TYPE               =78;      // khong dung fish type
    const NOT_ENOUGH_FAIRYDROP    = 79;     // khong du fairy drop
    const CANT_REBORN             = 80;     // khong the hoi sinh
    const NO_FISH                 = 81;     // trong ho khong co ca
    const DIFF_OPTION             = 82;     // khac nhau ve option
    const NOT_ENOUGH_FORMULA      = 83;     // Ko du bi kip lai
    const CANT_ATTACK             = 84;      // khong the tan cong
    const CANT_RECOVER            = 85;    // khong the hoi phuc suc khoe     
    const NOT_EXIST_SEA           = 86;   // ko ton tai bien nay
    const NOT_ENOUGH_POINT        = 87;   // ko du diem len level
    const NOT_ENOUGH_WIN_TOTAL    = 88;   // ko du so tran thang
    const NOT_ENOUGH_STAR		  = 89;		// ko du star
    const NOT_ENOUGH_HEALTH       = 90;     // khong du suc khoe de tan cong hay def  
    const NOT_ACTION_MORE         = 91;     // khong thuc hien duoc action nay nua
    const NO_MORE_TWO_ELEMENTS    = 92;     // mot ho khong tha nhieu hon 2 he
    
    // event hoa mua thu
    const WRONG_ROAD              = 93; // Sai duong
    const NOT_ANSWER              = 94; // chua tra loi cau hoi 
    const BEING_TORNADO           = 95; // dang bi loc xoay
    const NOT_DICE                = 96; // ban chua tung xuc xac 
    
    const EXPIRED                 = 97; // da het han   
    const NOT_SELECTED_SOLDIER    = 98; // khong phai ca chien binh manh nhat hien tai
    const EXIST                   = 99 ; // da ton tai  
    const NOT_ENOUGH_FRIEND       = 100 ; // ko du ban 
    const NOT_ENOUGH_VISA         = 101 ; // ko du Visa
    
    const NOT_ENOUGH_FRIEND_HEALTH = 102;   // ca nha ban ko du suc khoe
    const FISH_WAS_DIED            = 103; // ca linh da het mau 
    const SOLDIER_EXPIRED          = 104;   // ca linh nha minh het han
    const SOLDIER_FRIEND_EXPIRED   = 105;   // ca linh nha ban het han 
    const YOU_NOT_STAY_IN_WORLD    = 106;   // ban dang ko o trong the gioi ca
    const NOT_GEM                  = 107;   // ko co gem     
    const NO_MORE_SLOT             = 108;   // khong con slot du trong cho
    const NOT_ENOUGH_DIAMOND      = 109;   // khong du xu trong cho den
    const EQUIPMENT_USED           = 110;   // item da su dung, khong the ban 
    const SOURCE_INVALID           = 111;   // item khong the ban trong cho 
	const NOT_ENOUGH_CONDITION    = 112 ; // ko du dieu kien 
	const INTERNAL_PROCESS_FAIL		= 113;
	const EVENT_EXPIRED		        = 114;  // event het han
    const INUSE_MARKET_KEY          = 115; // key dang duoc su dung
    const SOLD_ITEM                 = 116;  // sold item
    const MAX_DISCOUNT              = 117; // max item discount trong dot giam gia nay
    const UID_INVALID               = 118;  // uid khong hop le
    const AUTOID_INVALID               = 119;  // autoid khong hop le

    const NOT_COMPLETE_HERB_QUEST   = 120;  // not done herb quest
    const GOT_GIFT_HERB_QUEST       = 121;  // 
    const COMPLETE_HERB_QUEST       = 122;
    const MAX_TIME_HERB_QUEST       = 123;
    const WRONG_CODE                = 124; // sai ma code
    const NOT_ME                    = 125; // ko phai cho ban 
    const NOT_ENOUGH_POWERTINH_POINT = 126; // khong du point de doi powertinh
    const MIGRATE_FAIL              = 127;
    const BILLING_MAINTAINANCE      = 128;
    const ACTION_NOT_AVAILABLE_MARKET = 129; 
    const EXPIRED_MARKET              = 130;
    const CANT_ADD_PAGE_MARKET         = 131;
    const MAX_SLOT_MARKET              = 132;
    const CANT_ADD_MARKET              = 133;
    const NO_ROOM                   = 134;
    const MAX_SLOT_TOURNAMENT       = 135;
    const CANT_USE_GOLD_TO_JOIN     = 136;
    
    //severboss
    const BOSS_IS_EXIST     = 137;    // boss da ton tai
    const BOSS_NOT_EXIST    = 138;    // boss chua ton tai
    const BOSS_WAS_DIE      = 139;    // boss da chet
    const CANT_CREATE_BOSS  = 140;    // ko the tao boss
    //password
    const NOT_UNLOCK        = 141;//Chua mo khoa de thuc hien hanh dong
    const SIGN_ERROR        = 142;//Mo khoa ko thanh cong
    const CANT_UPDATE_DATA  = 143;// update ko thanh cong    
    const CELL_ERROR        = 144; // loi o 
	const USER_IN_TOURNAMENT           = 145;
    const NO_IN_FOREST_WORLD        = 146;
    const NO_IN_ROUND4_FOREST_WORLD        = 147;
    const DEFAULT_FISH_OCCUPY_NOT_EXIST = 148;
    
    const SET_GIFT_UNFINISHED = 1062;
    const GET_GIFT_EXPIRED = 150;
    
    // fish skill
    const WRONG_IQ = 151;       // gui len loi giai sai cho quest tim duong
    const WRONG_SLOT = 152;     // slot hoc skill ko dung thu tu
    const CANT_ADD_SKILL = 153; // ko the them skill
    
    // #NOEL2012
    const WAIT_ENOUGHT_TIME = 154;
    const OVER_TIME_ALLOW = 155;
    const OVER_LIMIT = 156;
    // RankPointBottle 500
    const CAN_NOT_USE_RANK_POINT_BOTTLE = 157;
    
}

class ActionQuest{

    public $value;
    public static $instance;
  
    function __construct(){
        $this->value = array();
        $this->value['Money'] = 0;
        $this->value['Energy'] = 0;
        $this->value['NumMaterial'] = 0;
        $this->value['NumFish'] = 0;
    }
    
    public function getActionQuest(){
        return $this->value;
    }
    
    public function getInstance(){
        if (!isset(self::$instance)){
            self::$instance = new ActionQuest();
        }
        return self::$instance;
    }
    
    public function add($type, $num){
        $this->value[$type] += $num;
    }
    
    public function resetAction(){
        $this->value['Money'] = 0;
        $this->value['Energy'] = 0;
        $this->value['NumMaterial'] = 0;
        $this->value['NumFish'] = 0;
    }
    

} 

class EventType
{
    
    const EventActive = 'Hal2012'; 
        
    const BirthDay = 'BirthDay';
    const IceCream = 'IceCream';
    const EURO = 'EventEuro';
    const Event_8_3_Flower = 'Event_8_3_Flower';
    const PearFlower = 'PearFlower';
    const TreasureIsland = 'TreasureIsland';
    const MidMoon = 'MidMoon';
    const Halloween = 'Hal2012';
    const KeepLogin = 'KeepLogin';
    
    const CollectPattern = 'CollectPattern';
    //#NOEL2012
    const Noel = 'Event_Tet_2013';    
    // Use in case, add items event in FishWorld, Opcupy, ServerBoss, FishTournamen    
}

class EventEuro
{
   const EURO_BETTYPE_VIP = 'VIP';
    const EURO_BETTYPE_ORD = 'ORD';
    const EURO_TOP = 100;
    const EURO_END_TOP_1ST = 1; 
    const EURO_END_TOP_2ND = 2;
    const EURO_END_TOP_3RD = 3;
    const EURO_END_TOP_4_100TH = 4;
    const EURO_END_TOP_ANUI = 5;
    const EURO_END_TOP_NOTHING = 6;
    const EURO_RESULT_WIN_TEAM1 = 1;
    const EURO_RESULT_GIFT = 2;
    const EURO_RESULT_WIN_TEAM2 = 3;
    const EURO_BET_RIGHT = 1;
    const EURO_BET_WRONG = 2;
    const EURO_NOT_HAVING_RESULT = 0;
    const EURO_MATCH_TYPE_BOARD = 'BOARD';
    const EURO_MATCH_TYPE_QUAD = 'QUAD';
    const EURO_MATCH_TYPE_SEMI = 'SEMI';
    const EURO_MATCH_TYPE_FINAL = 'FINAL';
    const EURO_BALL_TYPE = 'EUROBALL';
    const EURO_BALL = 'Balls';
    const EURO_MEDAL = 'Medal';
    const TIME_MATCH = 5400;
    const MAX_VIP_BALL_FROM_ACTION = 50;
    const BONUS_BALL_ACTION = 1;
    const BONUS_BALL_GIFT = 2;
}

class ForestParam
{
    const MAX_MONSTER_ROUND_1 = 3;
    const MAX_MONSTER_ROUND_2 = 4;
    const MAX_MONSTER_ROUND_3 = 6;        
    const ID_MONSTER_4_ROUND_2 = 4;
    const PERCENT_BOSS_ROUND_2_QUIT = 10;
    const NUM_HIDE_MODE_NORMAL = 2;
    const NUM_HIDE_MODE_HARD = 3;
    const TIME_SWAP_MODE_NORMAL = 0.5;
    const TIME_SWAP_MODE_HARD = 0.1;
}
class OccupyFea
{
    const RANK_END_BOARD = 1000;
    const RANK_TOP_1st = 1;
    const RANK_SHOW_TOP10_REAL = 21;
    const TOKEN = 'OccupyToken';
    const TOKEN_ID_DEFAULT = 1;
    const TOKEN_ID_GIFT = 2;
    const DAYS_GET_GIFT = 7;
    const CODE = 'Occupy';
    const DATABASE_TOP = 'FishDataGame';
    const NOT_ATTEND = 0;
    
    const FULL_BOARD = 'FULL';
}

class MidMonType {    
    const DROP_OF_WATER = 'DropOfWater';
    const CYCLONE = 'Cyclone';
    const PAPER_BURN = 'PaperBurn';
    const GASCAN = 'GasCan';
    const SPACECRAFT = 'SpaceCraft';
    const PROPELLER = 'Propeller';
    const MAGNETIC = 'Magnetic';
    const PROTECTOR = 'Protector';
    const SPEEDUPER = 'Speeduper';     
    const COLLECTION = 'Collection';
    const DISASTER = 'Disaster';
    const MISS_MOON_HOME = 759;
    const LANTERN_GROUND = 0;
    const HEALTH = 'Health';
    const REBORN = 'Reborn';
    const MEDAL = 'Medal';
    const MAX_DISASTER = 5;
    const STEP_VIP = 10;
    const NUM_VIP = 5;
    const INITIAL_VIP = 9;
    const MAX_HEALTHY = 5;
}

class PasswordState
{
    const NO_PASSWORD = 'NoPassword';   //Chua co mat khau
    const IS_LOCK = 'IsLock';           //Dang khoa
    const IS_UNlOCK = 'IsUnlock';       //Dang mo khoa
    const IS_CRACKING = 'IsCracking';   //Dang xin pha khoa
    const IS_BLOCKED = 'IsBlocked';       //Dang bi block vi nhap sai mat khau 5 lan
    const IS_UNAVIABLE = 'IsUnavailable';  //Chua mua chuc nang nay

}
class TypeEffectDeity
{
    const   TYPE_DAMAGE_INCREASE = 'IncreaseDamage';
    const   TYPE_DAMAGE_DECREASE = 'DecreaseDamage';
    const   TYPE_HEALTHY_INCREASE = 'IncreaseHealthy';
    const   TYPE_HEALTHY_DECREASE = 'DecreaseHealthy';
    const   TYPE_DEFENCE_INCREASE = 'IncreaseDefence';
    const   TYPE_DEFENCE_DECREASE = 'DecreaseDefence';
    const   TYPE_BOLT = 'Bolt';
}

class MainQuest
{
    const END_SERIES_ID = 6;
    const FishWarLevel = 7;
    const BeforeFishWarSeriesId = 1;
    const FishWar_Greeting = 1;
    const FishWorld = 2;
    const TrainingGround = 3;
    const Collection = 4;
    const Gem = 5;
    const Enchant = 6;
    const Craft = 7;
}

class NPC
{
    const NPC_SIGN = '-';
}

class LandState
{
    const GOTGIFT   = -2 ; // da lay qua
    const DIGED     = -1 ; // da dao
    const WATER     = 0 ;  // mat nuoc
    const LAND      = 1 ;  // d?t thu?ng
    const SMALL_ROCK = 2 ;  // d?t c? d? v?n
    const CRAB      = 3 ;  // con cua
    const SNAIL     = 4 ;  // ?c bi?n
    const PAINT     = 5 ;  // b?i c?y
    const SMALL_COCONUT  = 6 ;  // c?y d?a nh?
    const BIG_COCONUT    = 7 ;  // c?y d?a l?n
    const BIG_ROCK   = 8 ;  // t?ng d?
    const STATUE    = 9 ;  // tu?ng d?
}

class MySqlCode
{
     const ERROR_INSERT_DUPLICATE = 1062;
     const CONNECTION_FAIL = 1;
     const QUERY_FAIL = 'FAIL';
}

class EventMidMoon
{
    const CODE = 'MidMoon';
    const EMPTY_ITEM_STATE = 0;
}

class EventHal2012
{
    const SIZE = 10;
    
    const FREEZE_STATE = 2;
    const LOCK_STATE = 1;
    const UNLOCK_STATE = 0;
    const KIDDING_STATE = 1;
    
    const MAX_UNLOCKMAP = 20;
    const COOLDOWN_MAP = 1800;
    
    const ORD_CHEST = 15;
    const GOD_CHEST = 16;
    const ORD_GIFT = 1;
    const RATE_GIFT = 2;
    const GOD_GIFT = 3; 
    const NUM_ORD_GIFT = 6;
    
    const STARTMAP_STEP = 0;   
    const ENDMAP_STEP = 31;
    const TYPE_KEY = 'HalItem';
    const MAX_FREEZE = 10;
    const MAX_GHOST = 3;
}

// #SmashEgg
class QuartzType {
    const QWhite = "QWhite";
    const QGreen = "QGreen";
    const QYellow = "QYellow";
    const QPurple = "QPurple";
    const QVIP = 'QVIP'; 
}

//#NOEL2012
class BulletType {
    const Bullet = "Bullet";
    const BulletGold = "BulletGold";
    const Bomb = "Bomb";
    const RainIce = "RainIce";
}
