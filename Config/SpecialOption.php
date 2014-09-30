<?php
return array (
  0 => array(
              'Level' => array(1,10),
             'Option' => array(
                                OptionFish::MIXFISH => 1,
                                OptionFish::SPECIAL => rand(1,3)
                                )
            ),
  1 => array(
             'Level' => array(11,20),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(1=>6,2=>4)),
                                OptionFish::SPECIAL => rand(2,5)
                                )
            ),
  2 => array(
             'Level' => array(21,30),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(1=>6,2=>3,3=>1)),
                                OptionFish::SPECIAL => rand(3,7)
                                )
            ),
  3 => array(
             'Level' => array(31,40),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(2=>6,3=>3,4=>1)),
                                OptionFish::SPECIAL => rand(4,9)
                                )
            ),
  4 => array(
             'Level' => array(41,50),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(3=>6,4=>3,5=>1)),
                                OptionFish::SPECIAL => rand(5,11)
                                )
            ),
  5 => array(
             'Level' => array(51,60),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(4=>6,5=>3,6=>1)),
                                OptionFish::SPECIAL => rand(6,13)
                                )
            ),
  6 => array(
             'Level' => array(61,70),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(5=>6,6=>3,7=>1)),
                                OptionFish::SPECIAL => rand(7,15)
                                )
            ),
  7 => array(
             'Level' => array(71,80),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(6=>6,7=>3,8=>1)),
                                OptionFish::SPECIAL => rand(8,17)
                                )
            ),
  8 => array(
             'Level' => array(81,90),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(7=>6,8=>3,9=>1)),
                                OptionFish::SPECIAL => rand(9,19)
                                )
            ),
  9 => array(
             'Level' => array(91,100),
             'Option' => array(
                                OptionFish::MIXFISH => Common::randomIndex(array(8=>6,9=>3,10=>1)),
                                OptionFish::SPECIAL => rand(10,21)
                                )
            ),
 
);
?>
    