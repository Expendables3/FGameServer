<?php
class FishWorld extends Model
{
  //public $CloneSeaList = array();
  public $SeaList   = array () ;
  public $SeaNum  = 0 ;
  public $ErrorFlag = 0;
  public $LastTime = 0 ;
  public $IsInWorld = true ; 

  public function __construct($uId)
  { 
     $this->LastTime = $_SERVER['REQUEST_TIME'] ;
     parent :: __construct($uId) ;

  }
  
  public function addSea($SeaId)
  {
      switch ($SeaId)
      {
          case SeaType::SEA_1 :// bien chung tinh
            $oSea  = new Sea($SeaId);
            break ;
          case SeaType::SEA_2 :// bien kim
            $oSea  = new MetalSea($SeaId);
            break ;
          case SeaType::SEA_3 :// bien bang
            $oSea  = new IceSea($SeaId);
            break ;
          case SeaType::SEA_4 :
            $oSea = new ForestSea($SeaId);
            break ;
      }
      $this->SeaList[$SeaId] = $oSea;
      $this->SeaNum += 1 ;
  }
  
  public function getSea($SeaId)
  {
    if(is_object($this->SeaList[$SeaId]))
        return $this->SeaList[$SeaId] ;
    else
        return false ;
  }
  
  public static function getById($uId)
  {
    return DataProvider :: get($uId,__CLASS__) ;
  }
  
  // reset so lan vao ben 
  public function resetAllSea()
  {
      foreach($this->SeaList as $SeaId => $oSea)
      {
          if(!is_object($oSea))
            continue ;
          $oSea->LastJoinTime = 0;
          $oSea->JoinNum = 0;
          $oSea->KillBossNumOnDay = 0 ;
      }
  }
  
  // reset so lan vao ben 
  public function resetAllMonster()
  {
      foreach($this->SeaList as $SeaId => $oSea)
      {
          if(!is_object($oSea))
            continue ;
          $oSea->Monster = array();
          if($SeaId == SeaType::SEA_4)
          {
            $oSea->sequenceRedUp = array();
            $oSea->sequenceYellowDown = array();
            $oSea->arrHideInGreenDown = array();
            $oSea->arrGift = array();
            $oSea->arrRandomBuff = array();
            $oSea->currentMonster = array();
          }
      }
  }
  
  public function checkInWorld()
  {
      return $this->IsInWorld ;
  }
  
  // update thong tin cac bien sau tran danh 
  public function updateInfoAfterMatch($SeaId)
  {
      $oSea = $this->getSea($SeaId);
      if(!is_object($oSea)) return false ;
      switch($SeaId)
        {
            case SeaType::SEA_3:
                $oSea->updateEffectNum();
            break;
            
        }
  }

}
?>
