<?php
class FishTourManager extends Model
{
	public $championList = array(
        'normal' => array(),
        'nightmare' => array(),
        'hell' => array(),
    );
	public $TourManagement = array(
		'LastDate' => '',
		'EndTours' => array()
	);
	public $CurrentTour = 0;
	
	public function __construct()
	{
		parent::__construct('shared');
	}
	public static function init()
	{
		return  new FishTourManager();
	}
	public static function getById()
    {
        return DataProvider::get('shared',__CLASS__);
	}
	
	public function endTour($date)
	{
		if($this->TourManagement['LastDate'] != $date)
			return false;
		$this->TourManagement['EndTours'][] = $this->CurrentTour;
		$this->CurrentTour = 0; 
		return true;
	}
    
}