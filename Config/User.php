<?php
/**
 * @author AnhBV
 * @version 1.0
 * @created 31-8-2010
 * @Description : thong tin user luc khoi tao ban dau
 */


return array
(
// User Info
'User'=>array(
	'Level' 	    => 1,
	'Money' 	    => 500,
	'ZMoney' 	    => 0,
	'Exp' 		    => 0,
	'Energy'	    => 50,
	'FoodCount'	    => 50*5,	// thuc an loai thuong   // 50 lan rac
	'CreateTime' 	=> time(),
    'NewMessage'    => false,
    'NewGift'       => false,
    'LastEnergyTime'    => $_SERVER['REQUEST_TIME'] ,
    'AutoId'            => 100,
    'LakeNumb'          => 1,
	),

// Fish
'Fish'=>array(
	1=>array(
		    'Sex'			=> 1,
		    'FeedAmount'	=> 3,
		    'LastBirthTime'	=> 0,
		    'LastPeriodStim'	=> 0,
            'LastPeriodCare'   => 0,
            'FishTypeId'		=>2,
		    'StartTime'		        => $_SERVER['REQUEST_TIME']- 120*60,
		    'OriginalStartTime'		=>$_SERVER['REQUEST_TIME']- 120*60,
			),
    2=>array(
		    'Sex'			=> 0,
		    'FeedAmount'	=> 3,
		    'LastBirthTime'	=> 0,
		    'LastPeriodStim'	=> 0,
            'LastPeriodCare'   => 0,
            'FishTypeId'		=>3,
		    'StartTime'		        => $_SERVER['REQUEST_TIME']- 150*60,
		    'OriginalStartTime'		=>$_SERVER['REQUEST_TIME']- 150*60,
			),
    3=>array(
			'Sex'			=> 1,
		    'FeedAmount'	=> 2,
		    'LastBirthTime'	=> 0,
		    'LastPeriodStim'	=> 0,
            'LastPeriodCare'   => 0,
            'FishTypeId'		=>1,
		    'StartTime'		        => $_SERVER['REQUEST_TIME']- 90*60,
		    'OriginalStartTime'		=>$_SERVER['REQUEST_TIME']- 90*60,
			),
	4=>array(
		    'Sex'			=> 0,
		    'FeedAmount'	=> 2,
		    'LastBirthTime'	=> 0,
		    'LastPeriodStim'	=> 0,
            'LastPeriodCare'   => 0,
            'FishTypeId'		=>1,
		    'StartTime'		        => $_SERVER['REQUEST_TIME']- 66*60,
		    'OriginalStartTime'		=>$_SERVER['REQUEST_TIME']- 66*60,
			),
    5=>array(
			'Sex'			=> 0,
		    'FeedAmount'	=> 1,
		    'LastBirthTime'	=> 0,
		    'LastPeriodStim'	=> 0,
            'LastPeriodCare'   => 0,
            'FishTypeId'		=>4,
		    'StartTime'		        => $_SERVER['REQUEST_TIME']- 65*60,
		    'OriginalStartTime'		=>$_SERVER['REQUEST_TIME'] - 65*60,
			),
	),
//Lake
'Lake'=>array(
	'1'=> array(
			 'CleanAmount'		=> 0,
             'Level'			=> 1,
			 'StartTime'		=>$_SERVER['REQUEST_TIME']- 4*3600,
             'StarTimeOriginal' =>$_SERVER['REQUEST_TIME'] - 4*3600, // int - thoi gian bat dau unlock ho, khong doi
			),
    '2'=> array(
             'CleanAmount'        => 0,
             'Level'            => 1,
             'StartTime'        =>$_SERVER['REQUEST_TIME']- 4*3600,
             'StarTimeOriginal' =>$_SERVER['REQUEST_TIME'] - 4*3600, // int - thoi gian bat dau unlock ho, khong doi
            ),
	),

'Store' => array(
     2=> array(
         'ItemType'         =>'Material',
    	 'ItemId'           =>1 ,
         'Num'              =>10,
         ),
    ),
'Item' => array(
    2=> array(
         'ItemType'         =>'BackGround' ,
           'ItemId'         =>1 ,
           'Num'            =>1,
           'X'              =>0,
           'Y'              =>0 ,
           'Z'              =>0 ,
        ),

    ),
'LogFishWarNum'=>1,

);
?>