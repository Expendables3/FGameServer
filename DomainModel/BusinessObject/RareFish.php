<?php

/*
 * class ca quy hiem
 * date : 23_2_2011
 */
class RareFish extends SpecialFish
{

    function __construct($Id, $fishTypeId = 1, $sex = 1,$rateOption = null,$color = 0)
    {
        parent :: __construct($Id,$fishTypeId,$sex,$rateOption,$color);
        $this->FishType = FishType::RARE_FISH ;
    }
}
