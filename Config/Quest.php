<?php
return array(
'SeriesQuest' => array(
  1 => array(
    'Id'    => 1,
    'Name'  =>'lam quen voi ZingFish',
    'ExpireDate'   => mktime(0,0,0,12,20,2010),
    'Npc'   =>'Tienca.swf',
    'LevelRequire'  => 5,
    'Decription'    =>'day la quest so nhap 1',
    'Quest' => array(
        1=> array(
            'Id'            => 1,
            'Name'          => 'huong dan',
            'Decription'    =>'ban hay ra nhap ZingFish nhe va lam cac viec tiep theo nhe',
            'Bonus'=> array(
                ),
            ),
        2=> array(
            'Id'            => 2,
            'Name'          => 'lam sach ho',
            'Decription'    =>'muon ca song khoe thi ho phai sach...',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'don sach ho',
                    'Action'        =>'CleanLake',
                    'Num'        => 10,
                    'Param'=>array(
                        'UserId' => 'Self',
                    ),

                ),
            ),
        ),
        3=> array(
            'Id'            => 3,
            'Name'          => 'chua benh cho ca',
            'Decription'    =>' ca bi bo be lau ngay ko an se bi benh ...',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'chua benh cho ca',
                    'Action'        =>'CureFish',
                    'Num'        => 2,
                    'Param'=>array(
                    ),

                ),
            ),
        ),
        4=> array(
            'Id'            => 4,
            'Name'          => 'cho ca an',
            'Decription'    =>'',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'cho ca an',
                    'Action'        =>'FeedFish',
                    'Num'        => 5,
                    'Param'=>array( ),

                ),
            ),

        ),
        5=> array(
            'Id'            => 5,
            'Name'          => 'ban ca',
            'Decription'    =>'',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'ban ca',
                    'Action'        =>'SellFish',
                    'Num'        => 2,
                    'Param'=>array(
                    ),

                ),
            ),

        ),
        6=> array(
            'Id'            => 6,
            'Name'          => 'mua ca',
            'Decription'    =>'',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'mua ca',
                    'Action'        =>'BuyFish',
                    'Num'        => 5,
                    'Param'=>array(
                    ),

                ),
            ),

        ),
        7=> array(
            'Id'            => 7,
            'Name'          => 'trang tri ho ca',
            'Decription'    =>'',
            'Bonus'=> array(
                1=> array(
                  'ItemType'    => 'Money',
                  'ItemId'      => '',
                  'Num'         => 200,
                ),
                2=> array(
                  'ItemType'    => 'Exp',
                  'ItemId'      => '',
                  'Num'         => 50,
                ),
            ),
            'TaskList'=> array(
                1=> array (
                    'Id'            => 1,
                    'Decription'    =>'mua cay rong bien',
                    'Action'        =>'BuyDecorate',
                    'Num'        => 1,
                    'Param'=>array(
                        'DecoList' => array(
                            'ItemId' =>1,
                            'Type'   =>'OceanTree',
                        ),

                    ),

                ),
            ),

        ),
    ),

  ),
  2 => array(
  ),

),

);
?>