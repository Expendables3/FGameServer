<?php
class FishTournament extends Model
{
	public $LastJoinByGold = 0;        // thoi diem join = gold trong ngay
	public $LastAchieved = 0;          // so' sao user dat duoc sau tournament
	public $GiftAchieved = 0;          // so' luot chon con` lai
    public $LastJoinTime = 0;     // wave hien tai dang tham gia
	public $GroupId = 0;     // loai group da tham gia
    public $LastCardId = array();            // cardId cua lan chon cuoi cung, su dung khi dc chon 2 lan
	
	public function __construct($uId)
	{
		parent::__construct($uId);
	}
	public static function init($uId){
		return new FishTournament($uId);
	}
	
	public static function getById($uId)
	{
		return DataProvider::get($uId,'FishTournament');
	}
}