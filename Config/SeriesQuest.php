<?php return array (
  1 => 
  array (
    'Id' => 1,
    'SeriesName' => 'So nhap Ngu Chien',
    'Desc' => 'MainQuest',
    'LevelRequire' => 1,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'Bonus'=> array(),
      ),
      2 => 
      array (
        'Id' => 2,
        'Name' => 'Quest2',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 2,
            'Desc' => 'Cho ca an',
            'Action' => 'feed',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 290,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 500,
          ),
          3 => 
          array (
            'ItemType' => 'Material',
            'ItemId' => 1,
            'Num' => 2,
          ),
        ),
      ),
      3 => 
      array (
        'Id' => 3,
        'Name' => 'Quest3',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 3,
            'Desc' => 'Ban ca',
            'Action' => 'sell',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 290,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 1000,
          ),
          3 => 
          array (
            'ItemType' => 'EnergyItem',
            'ItemId' => 2,
            'Num' => 1,
          ),
        ),
      ),
      4 => 
      array (
        'Id' => 4,
        'Name' => 'Quest4',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 4,
            'Desc' => 'Mua ca',
            'Action' => 'buy',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 290,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 1000,
          ),
          3 => 
          array (
            'ItemType' => 'EnergyItem',
            'ItemId' => 2,
            'Num' => 1,
          ),
        ),
      ),
      5 => 
      array (
        'Id' => 5,
        'Name' => 'Quest5',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 5,
            'Desc' => 'Su dung nang luong',
            'Action' => 'useItem',
            'Num' => 1,
            'Param'=>array(
                  'ItemList'=>array(
                        0=>array('ItemType' => Type::EnergyItem,)
                    )
            ),
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 350,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 1500,
          ),
          3 => 
          array (
            'ItemType' => 'EnergyItem',
            'ItemId' => 3,
            'Num' => 1,
          ),
        ),
      ),
      6 => 
      array (
        'Id' => 6,
        'Name' => 'Quest6',
        'Bonus' => 
        array (
          5 => 
          array (
            'ItemType' => 'Soldier',
            'RecipeType' => 'Paper',
            'Num' => 1,
          ),
        ),
      ),
      7 => 
      array (
        'Id' => 7,
        'Name' => 'Quest7',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 7,
            'Desc' => 'Tha ca linh ra ho',
            'Action' => 'useBabyFish',
            'Num' => 1,
            'Param'=>array(
                'TypeFish' => FishType::SOLDIER,
            ),
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 400,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 2000,
          ),
          4 => 
          array (
            'ItemType' => 'Weapon',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
      8 => 
      array (
        'Id' => 8,
        'Name' => 'Quest8',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 8,
            'Desc' => 'Trang bi cho Ngu Thu',
            'Action' => 'useEquipmentSoldier',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 100,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 2500,
          ),
          5 => 
          array (
            'ItemType' => 'Soldier',
            'RecipeType' => 'Paper',
            'Num' => 1,
          ),
        ),
      ),
      9 => 
      array (
        'Id' => 9,
        'Name' => 'Quest9',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 9,
            'Desc' => 'Tan cong Bach Tuoc Muoi Tieu',
            'Action' => 'attackFriendLake',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 100,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 3000,
          ),
          3 => 
          array (
            'ItemType' => 'RecoverHealthSoldier',
            'ItemId' => 1,
            'Num' => 5,
          ),
        ),
      ),
      10 => 
      array (
        'Id' => 10,
        'Name' => 'Quest10',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 10,
            'Desc' => 'Su dung thuoc suc khoe',
            'Action' => 'recoverHealthSoldier',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 260,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 4000,
          ),
          5 => 
          array (
            'ItemType' => 'Soldier',
            'RecipeType' => 'Paper',
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
  2 => 
  array (
    'Id' => 2,
    'SeriesName' => 'The Gioi Ca',
    'Desc' => 'MainQuest',
    'LevelRequire' => 7,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 1,
            'Desc' => 'Tan cong ngu thu voi bien Hoang So',
            'Action' => 'acttackMonster',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 1040,
          ),
          2 => 
          array (
            'ItemType' => 'ZMoney',
            'Num' => 1,
          ),
          4 => 
          array (
            'ItemType' => 'Weapon',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
  3 => 
  array (
    'Id' => 3,
    'SeriesName' => 'Len Cap Ngu Thu',
    'Desc' => 'MainQuest',
    'LevelRequire' => 8,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 1,
            'Desc' => 'Tu luyen Ngu thu',
            'Action' => 'getGiftTraining',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 500,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 6000,
          ),
          3 => 
          array (
            'ItemType' => 'RankPointBottle',
            'ItemId' => 3,
            'Num' => 1,
          ),
        ),
      ),
      2 => 
      array (
        'Id' => 2,
        'Name' => 'Quest2',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 2,
            'Desc' => 'Da thong Ngu mach',
            'Action' => 'upgradeMeridian',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 600,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 7000,
          ),
          4 => 
          array (
            'ItemType' => 'Armor',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
  4 => 
  array (
     'Id' => 4,
    'SeriesName' => 'Bo Suu Tap',
    'Desc' => 'MainQuest',
    'LevelRequire' => 9,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 1,
            'Desc' => 'Chien thang Ngu thu nha ban be',
            'Action' => 'attackFriendLake',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 100,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 8000,
          ),
          4 => 
          array (
            'ItemType' => 'Helmet',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
      2 => 
      array (
        'Id' => 2,
        'Name' => 'Quest2',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 2,
            'Desc' => 'Tang qua ban be',
            'Action' => 'sendGift',
            'Num' => 1,
            'Param'=>array(
                        'GiftId' => 13                      
            ),
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 120,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 9000,
          ),
          3 => 
          array (
            'ItemType' => 'RankPointBottle',
            'ItemId' => 3,
            'Num' => 10,
          ),
        ),
      ),
      3 => 
      array (
        'Id' => 3,
        'Name' => 'Quest3',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 3,
            'Desc' => 'Cau duoc vo lon nha Ban be',
            'Action' => 'fishing',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 400,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 10000,
          ),
          3 => 
          array (
            'ItemType' => 'StampPack',
            'ItemId' => 1,
            'Num' => 1,
          ),
        ),
      ),
      4 => 
      array (
        'Id' => 4,
        'Name' => 'Quest4',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 4,
            'Desc' => 'Doi bo Suu tap',
            'Action' => 'exchangeItemCollection',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 500,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 12000,
          ),
          4 => 
          array (
            'ItemType' => 'Armor',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
  5 => 
  array (
     'Id' => 5,
    'SeriesName' => 'Tu luyen Dan',
    'Desc' => 'MainQuest',
    'LevelRequire' => 11,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 1,
            'Desc' => 'Chien thang Ngu thu nha ban be 3 lan',
            'Action' => 'attackFriendLake',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 1000,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 14000,
          ),
          4 => 
          array (
            'ItemType' => 'Helmet',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
      2 => 
      array (
        'Id' => 2,
        'Name' => 'Quest2',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 2,
            'Desc' => 'Luyen Dan',
            'Action' => 'upgradeGem',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 1200,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 16000,
          ),
          4 => 
          array (
            'ItemType' => 'Weapon',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
      3 => 
      array (
        'Id' => 3,
        'Name' => 'Quest3',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 3,
            'Desc' => 'Su dung Dan',
            'Action' => 'useGem',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 1770,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 18000,
          ),
          4 => 
          array (
            'ItemType' => 'Armor',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
  6 => 
  array (
    'Id' => 6,
    'SeriesName' => 'Cuong hoa ',
    'Desc' => 'MainQuest',
    'LevelRequire' => 13,
    'Quest' => 
    array (
      1 => 
      array (
        'Id' => 1,
        'Name' => 'Quest1',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 1,
            'Desc' => 'Ghep Ngu thach',
            'Action' => 'boostItem',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 2000,
          ),
          2 => 
          array (
            'ItemType' => 'Money',
            'Num' => 20000,
          ),
          3 => 
          array (
            'ItemType' => 'Material',
            'ItemId' => 4,
            'Num' => 1,
          ),
        ),
      ),
      2 => 
      array (
        'Id' => 2,
        'Name' => 'Quest2',
        'TaskList' => 
        array (
          1 => 
          array (
            'Id' => 2,
            'Desc' => 'Cuong hoa Do',
            'Action' => 'enchantEquipment',
            'Num' => 1,
          ),
        ),
        'Bonus' => 
        array (
          1 => 
          array (
            'ItemType' => 'Exp',
            'Num' => 2800,
          ),
          3 => 
          array (
            'ItemType' => 'Material',
            'ItemId' => 5,
            'Num' => 2,
          ),
          4 => 
          array (
            'ItemType' => 'Helmet',
            'Rank' => 1,
            'Color' => 4,
            'Num' => 1,
          ),
        ),
      ),
    ),
  ),
);