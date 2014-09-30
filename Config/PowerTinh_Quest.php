<?php return array (
  1 => 
  array (
    'Action' => 'sendGift',
    'Require' => 10,
    'Point' => 20,
  ),
  2 => 
  array (
    'Action' => 'getGiftDay',
    'Require' => 1,
    'Point' => 20,
  ),
  3 => 
  array (
    'Action' => 'completeDailyQuest',
    'Require' => 1,
    'Param' => 
    array (
      'QuestId' => 1,
    ),
    'Point' => 40,
  ),
  4 => 
  array (
    'Action' => 'completeDailyQuest',
    'Require' => 1,
    'Param' => 
    array (
      'QuestId' => 2,
    ),
    'Point' => 60,
  ),
  5 => 
  array (
    'Action' => 'payToResetDailyQuest',
    'Require' => 1,
    'Point' => 10,
  ),
);