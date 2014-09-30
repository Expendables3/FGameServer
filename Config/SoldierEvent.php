<?php
  return array(
    'Win' => array(
        1=> array(
          'bonus' => array (
              1 =>array(
                'ItemType' => Type::EnergyItem,
                'ItemId' => 4,
                'Num'   => 1 ,
                ),
          ),
          'WinNum'=> 5 ,  
        ),
        2=> array(
          'bonus' => array (
              1 =>array(
                'ItemType' => Type::RecoverHealthSoldier,
                'ItemId' => 3,
                'Num'   => 1 ,
                ),
          ),
          'WinNum'=> 15 ,  
        ),
        3=> array(
          'bonus' => array (
              1 =>array(
                'ItemType' => BuffItem::Samurai,
                'ItemId' => 3,
                'Num'   => 1 ,
                ),
          ),
          'WinNum'=> 30 ,  
        ),
        4=> array(
          'bonus' => array (
              1 =>array(
                'ItemType' => 'LuckyStar',
                'ItemId' => '',
                'Num'   => 1 ,
                ),
          ),
          'WinNum'=> 100 ,  
        ),
    ),
    //------------------------
    'ChangeStar'=> array(
         1=> array(
          'bonus' => array (
                'ItemType' => FormulaType::Draft,
                'ItemId' => rand(1,5),
                'Num'   => 1 ,
          ),
          'StarNum'=> 1 ,  
        ),
        2=> array(
          'bonus' => array (
                'ItemType' => FormulaType::Paper,
                'ItemId' => rand(1,5),
                'Num'   => 1 ,
          ),
          'StarNum'=> 4 ,  
        ),
        3=> array(
          'bonus' => array (
                'ItemType' => FormulaType::GoatSkin,
                'ItemId' => rand(1,5),
                'Num'   => 1 ,
          ),
          'StarNum'=> 6 ,  
        ),
        4=> array(
          'bonus' => array (
                'ItemType' => Type::Soldier,
                'FormulaType' => FormulaType::GoatSkin,
                'ItemId' => rand(1,5),  
                'Num'   => 1 
          ),
          'StarNum'=> 7 ,  
        ),
    ),  
  
  );
?>
