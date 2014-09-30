<?php
  return array(
    Type::Firework => array(
        'Sure' => array(
            0 => array(
                'ItemType' => Type::Exp,
                'ItemId' => 1,
                'Num' => 100
            ),    
            1 => array(
                'ItemType' => Type::Money,
                'ItemId' => 1,
                'Num' => 1000
            ),
            2 => array(
                'ItemType' => Type::DragonBall,
                'ItemId' => 1,
                'Num' => 1
            ),
        ),
    ),
    Type::NoelFish => array(
        'Sure' => array(
            0 => array(
                'ItemType' => Type::Exp,
                'ItemId' => 1,
                'Num' => 100
            ),    
            1 => array(
                'ItemType' => Type::Money,
                'ItemId' => 1,
                'Num' => 1000
            ),
            2 => array(
                'ItemType' => Type::Sock,
                'ItemId' => 1,
                'Num' => Common::randomIndex(array(1=>70,2=>20,3=>10)),
            ),
        ),
    ),
  )
?>
