<?php
//#NOEL2012
class Noel extends Model {
    //Declare variable    
    private $CurrentBoardId = 0;
    private $NumFishDie = 0;
    private $IsPassBoard = false;
    public $BoardGame = array(); // Sea, Board#, BoardInfo,     
    private $RequestKey = array(-1,-1);    
    private $RetBonus = array('Normal'=>array(),'Equipment'=>array());
            
    // Init method      
    public function __construct($uId)
    {
        parent::__construct($uId);        
    }
    //
    static public function init($uId)
    {
        $res = new Noel($uId);
        return $res;
    }

    static public function getById($uId)
    {
        //DataProvider::delete($uId, 'Noel');
        $oNoel = DataProvider::get($uId, 'Noel');
        if(empty($oNoel))
        {            
            $oNoel = self::init($uId);
        }        
        return $oNoel;    
    }
    
    public function intBoardGame($BoardId) {
        
        $AutoId = rand(1,100);        
        $now = $_SERVER['REQUEST_TIME'];
        $retBoardGame = array();
        // Reset or init in case first start        
        $this->BoardGame = array();
        // get Fish on board info 
        $Fishs = Common::getConfig("Noel_BoardGame",$BoardId);        
        if(!is_array($Fishs)) {
            return Error ::NOT_LOAD_CONFIG;
        }
        $FishConfig = Common::getConfig("Noel_Fish");
        if(!is_array($FishConfig)) {
            return Error ::NOT_LOAD_CONFIG;
        }
                
        // init Fish on Board
        $FishList = array();
        foreach ($Fishs as $Fish) {
            $FishType = $Fish["FishType"];
            $FishId = intval($Fish["FishId"]);
            $Num = intval($Fish["Num"]);
            
            if(empty($FishType) || $FishId <=0 || $Num <0) {
                return Error ::PARAM;
            }
            if($Num >0) {
                for($i=0; $i< $Num; $i++) {                    
                    $FishInfo = array("Id"=>$AutoId,"FishType"=>$FishType,"FishId"=>$FishId,"Blood"=>$FishConfig[$FishType][$FishId]["Blood"],"BonusIndex"=>$this->getFishBonusIndex($FishType,$FishId));
                    $FishList[$AutoId] = $FishInfo;
                    $AutoId++;
                }    
            }
        }
        $retBoardGame = array("FishList"=>$FishList);
        $this->BoardGame[$BoardId] = $retBoardGame;        
        $this->CurrentBoardId = $BoardId;
        $this->NumFishDie = 0;
        $this->IsPassBoard = false;                
        $this->RetBonus = array('Normal'=>array(),'Equipment'=>array());
        return $retBoardGame;
    }
    //
    public function getRetBonus() {
        return $this->RetBonus;
    }
    
    public function setRetBonus($RetBonus) {
        $this->RetBonus = $RetBonus;
    }
    
    // Get Current Board Id
    public function getCurrentBoardId(){
        return $this->CurrentBoardId;
    }
    //
    public function setCurrentBoardId($BoardId){
        $this->CurrentBoardId = $BoardId;
    }
    
    //
    public function getRequestKey(){
        return $this->RequestKey;        
    }
    
    public function setRequestKey($Index, $Key){
        $this->RequestKey[$Index] = $Key;        
    }
    
    public function resetRequestKey() {
        $this->RequestKey = array(-1,-1);
    }
    
    //getBoardGame
    public function getBoardGame($BoardId){
        return $this->BoardGame[$BoardId];
    }
    
    //resetBoardGame
    public function resetBoardGame($BoardId){
        $this->BoardGame = array();
    }
        
    //setBlood    
    public function setBlood($FishId, $Blood){
        $this->BoardGame[$this->CurrentBoardId]["FishList"][$FishId]["Blood"] =  $Blood;
    }
    
    public function getBlood($FishId){
        return $this->BoardGame[$this->CurrentBoardId]["FishList"][$FishId]["Blood"];
    }
    
    //removeFish
    public function removeFish($FishId){
        $now = $_SERVER['REQUEST_TIME'];
        $this->NumFishDie +=1;        
        if($this->CurrentBoardId ==4 && $this->BoardGame[$this->CurrentBoardId]["FishList"][$FishId]["FishType"] =="FishBoss"){
            $this->IsPassBoard = true;
        }
        unset($this->BoardGame[$this->CurrentBoardId]["FishList"][$FishId]);
        if($this->CurrentBoardId ==5 && count($this->BoardGame[$this->CurrentBoardId]["FishList"]) ==0 ) {
            $this->IsPassBoard = true;
        }
    }
    //getFishBonusIndex
    public function getFishBonusIndex($FishType,$FishId) {        
        $Gifts = Common::getConfig("Noel_Bonus","NoelItem",$FishType);        
        $Gifts = $Gifts[$FishId];        
        $index = Common::pickIndex($Gifts);   
        if($Gifts[$index]['ItemType'] =='None') return 0;          
        return $index; 
    }    
    //getNumFishDie
    public function getNumFishDie(){
        return $this->NumFishDie;
    }
    //
    public function setIsPassBoard(){
        $this->IsPassBoard = true;
    }    
    public function getIsPassBoard() {
        return $this->IsPassBoard;
    }
    
        
}  
?>
