<?php

Class BabyFish
{
    public $Id ;
    public $FishTypeId ;
    public $ColorLevel;
    public $RateOption;
    public $FishType;
    public $Sex;    // gioi tinh cua ca 1-duc, 0- cai

    public function __construct($fishTypeId,$autoId, $fishType, $rateOption, $sex, $colorLevel)
    {
        $this->Id = $autoId ;
        $this->FishTypeId = $fishTypeId ;
        $this->FishType= $fishType ;
        $this->RateOption = $rateOption ;
        $this->ColorLevel = $colorLevel;
        $this->Sex = $sex ;
    }
}