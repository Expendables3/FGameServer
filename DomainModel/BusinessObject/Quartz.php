<?php                       
class Quartz { // Linh thach 
    public $Id;    // auto Id in store
    public $ItemId; // itemId of item
    public $Type;  // ex = QCommon, QSpecial, QRare, QGod
    /*
    public $OptionDamage; // So dong cong
    public $Damage; // Cong    
    public $OptionDefence; // So dong thu
    public $Defence; // thu    
    public $OptionCritical; // So dong Chi mang
    public $Critical;  // Chi mang    
    public $OptionVitality; // So dong Chi mau
    public $Vitality;  // Mau    
    */
    public $Level=1;    
    //public $Source = "SmashEgg";
    //public $StartTime;
    //public $Durability = 1;
    public $IsUsed = false;
    //public $Author = array('Id' => 0, 'Name' => '');
    public $InUse = true;
    public $Point = 0; // Diem hap thu
    
    // $params is array("OptionDamage"=>1,"Damage"=>100,"OptionDefence"=>0,"Defence"=>0,...)
    function __construct($id, $itemid, $type)
    {
        $this->Id = $id;
        $this->ItemId = $itemid;
        $this->Type = $type;        
    }    
    // check durability
    public function isExpired(){
        return false;           
    }    

    public function getIndex()
    {
        $Option = array("OptionDamage"=>0,"Damage"=>0,"OptionDefence"=>0,"Defence"=>0,"OptionCritical"=>0,"Critical"=>0,"OptionVitality"=>0,"Vitality"=>0);
        $ItemConfig = Common::getConfig("SmashEgg_Quartz",$this->Type,$this->ItemId);
        $ItemConfig = array_merge($Option, $ItemConfig);
        
        if($this->Level >1) {
            
            for($i=2; $i<=$this->Level; $i++) {
                $LevelConfig = Common::getConfig("SmashEgg_QuartzLevel",$this->Type,$i);
                if($ItemConfig["Damage"] >0){
                    $ItemConfig["Damage"] +=  $LevelConfig["Damage"];
                }    
                if($ItemConfig["Defence"] >0){
                    $ItemConfig["Defence"] +=  $LevelConfig["Defence"];
                }    
                if($ItemConfig["Critical"] >0){
                    $ItemConfig["Critical"] +=  $LevelConfig["Critical"];
                }    
                if($ItemConfig["Vitality"] >0){
                    $ItemConfig["Vitality"] +=  $LevelConfig["Vitality"];
                }                    
            }
        }      
        $bonus = array();
        $bonus['Damage'] = $ItemConfig["OptionDamage"]*$ItemConfig["Damage"];                   
        $bonus['Defence'] = $ItemConfig["OptionDefence"]*$ItemConfig["Defence"];
        $bonus['Critical'] = $ItemConfig["OptionCritical"]*$ItemConfig["Critical"];
        $bonus['Vitality'] = $ItemConfig["OptionVitality"]*$ItemConfig["Vitality"];
               
        return $bonus;
    }

    public function updateId($autoId)            
    {
       $this->Id = $autoId;
    }
    
        
    
}  
?>
