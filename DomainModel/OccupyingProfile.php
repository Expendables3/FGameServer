<?php
    class OccupyingProfile extends Model
    {
        public $CurrSoldier;
        public $CurrRank;
        
        public $LastOccupiedTime;
        
        public $LastGiftRank;
        public $GotLastGift;
        public $OccupiedHistory;
        public $LastGotGiftOccupiedCampain;
        
        public $LastRefreshBoard;
        public $LastOccupy;
        public $GetTop10Real;
        public $Items;
        public $LastGiftToken;
        
        public $LastDateForReset;
        public $RemainOccupyCount;
        
        public $LastSystemRefresh;
        
        public $LastOccupyResult;
        
        public function __construct($uId, $firstJoiedTime)
        {
            parent::__construct($uId);
        
            $this->CurrRank = 0;
            $this->LastOccupiedTime = $firstJoiedTime;
            
            $this->LastGiftRank = 0;
            $this->GotLastGift = true;
            $this->LastGotGiftOccupiedCampain = "";
            
            $this->OccupiedHistory = array();
            $this->LastOccupy = 0;
            $this->LastRefreshBoard = 0;
            $this->GetTop10Real = true;
            $this->LastGiftToken = "";
            $this->LastDateForReset = '';
            $this->RemainOccupyCount = 0; 
            
            $this->Items[OccupyFea::TOKEN][OccupyFea::TOKEN_ID_GIFT] = 0;
            
            $this->LastSystemRefresh = 0;
            
            $this->LastOccupyResult = 0; 
            
        }
        
        public static function getByUid($uId)
        {
            return DataProvider::get($uId,__CLASS__);            
        }
        
        public function setCurrSoldier($soldierId, $lakeId, $sProfile)
        {   
            if(!empty($soldierId))
                $this->CurrSoldier['Id'] = $soldierId;
            if(!empty($lakeId))
                $this->CurrSoldier['LakeId'] = $lakeId;
            $this->CurrSoldier['Soldier'] = $sProfile['Soldier'];
            $this->CurrSoldier['Equipment'] =  $sProfile['Equipment'];
            $this->CurrSoldier['Index'] = $sProfile['Index'];
            $this->CurrSoldier['Meridian'] = $sProfile['Meridian'];
        }
        
        public function setNextGiftTime($time)
        {
            $this->NextGiftTime = $time;
        } 
        
        public function resetLast($type)
        {
            switch($type)
            {
                case 'Occupy':                    
                case 'RefreshBoard':
                    $last = 'Last'.$type;
                    $this->$last = 0;
                    break;
                case 'GetTop10':
                    $this->LastGetTop10 = false;
                    break;
            }
        }
        
        public function toggleGetTop()
        {
            $this->GetTop10Real = !$this->GetTop10Real;
        }
        
        public function addItems($Type, $Id, $Num)
        {
            if(!isset($this->Items[$Type][$Id]))
                $this->Items[$Type][$Id] = 0;
            if(($this->Items[$Type][$Id] + $Num ) < 0)
            {
                $this->Items[$Type][$Id] = 0;
                return;   
            }
            $this->Items[$Type][$Id] += $Num;
            return;
        }
        
        public static function getRank($uId)
        {
            $rank = OccupyFea::RANK_END_BOARD + 1;
            $sql = 'select Rank from Occupy_OccupyingBigBoard where Uid = ' . $uId ;
            $res = Common::queryMySql(OccupyFea::CODE, $sql);
            if(!$res)
                return false;
            if($row = mysql_fetch_array($res, MYSQL_ASSOC))
                $rank = ($row['Rank'] > OccupyFea::RANK_END_BOARD) ? $rank : $row['Rank']; 
            return $rank;                               
        }        
               
    }  
?>
