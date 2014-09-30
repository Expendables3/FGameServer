<?php
class Equipment
{
    public $Id;             // auto Id
    public $Element;        // Element
    public $Type;  // Weapon, Armor, Helmet...
    public $Rank;           // Type:: Wooden, Golden, ...
    public $Color;
    public $Damage;
    public $Defence;
    public $Critical;
    public $Health;
    public $Vitality;
    public $EnchantLevel;
    public $bonus;
    public $PercentBonus = 0;
    
    public $StartTime;
    public $Durability;
    public $Source;
    public $IsUsed = false;
    public $Author = array('Id' => 0, 'Name' => '');
    public $InUse = true;
    public $NumChangeOption = 0;    
    
    function __construct($id, $ele, $eqType, $rank, $color, $dam, $defence, $critical, $dura, $vital, $source,$numOpt = 0)
    {
        $this->Id = $id;
        $this->Element = $ele;
        $this->Type = $eqType;
        $this->Rank = $rank;
        $this->Color = $color;
        $this->Damage = $dam;
        $this->Defence = $defence;
        $this->Critical = $critical;
        $this->StartTime = $_SERVER['REQUEST_TIME'];
        $this->Durability = $dura;
        $this->Vitality = $vital;
        $this->EnchantLevel = 0;
        $this->Source = $source;
        $this->bonus = Equipment::getBonusEquipment($eqType, $rank, $color,$numOpt);
    }
    
    // check durability
    public function isExpired(){
        return ($this->Durability <= 0);           
    }
    
    // check Expired Time
    public function checkExpiredTime()
    {
        $conf = Common::getConfig('Wars_'.$this->Type,$this->Rank,$this->Color);
        $TimeUse = $conf['TimeUse'];
        $now = $_SERVER['REQUEST_TIME'];
        if($this->StartTime + $TimeUse < $now)
            return false ;
        return true ;
    }
    
    // Time = second
    public function extendTime($seconds)
    {
        if ($seconds<=0)
            return false;
         
        $timeLeft = $this->StartTime + $this->TimeLive - $_SERVER['REQUEST_TIME'];
        $conf_time = Common::getConfig('Param','SoldierEquipment','TimeExtend');
        if ($timeLeft > $conf_time)    
            return false;
        
        if ($timeLeft < 0)
            $timeLeft = 0;
        $this->TimeLive = $timeLeft + $seconds;
        $this->StartTime = $_SERVER['REQUEST_TIME'];
        return true;
    }
    
    public function enchant($successRate, $usingGodCharm)
    {
        $rand = rand(1,10000)/100;        
        if ($rand < $successRate)
        {
            $this->levelIncrease(1,$usingGodCharm);
            return true;
        }
        else 
        {
            $conf_fail = Common::getConfig('EnchantFailedRate', $this->EnchantLevel);
            $levelAfter = Common::randomIndex($conf_fail);
            $this->levelIncrease($levelAfter-$this->EnchantLevel,$usingGodCharm);
            return false;
        }
    }
    
    /**
    * increase or decrease level equipment
    * if $isUp = true: levelUp, else levelDown
    */
    public function levelIncrease($levelIncrease, $usingGodCharm)
    {
        // Get weight
        if ($levelIncrease == 0)
            return;
        else if ($levelIncrease > 0)
            $weigh = 1;
        else 
            if ($usingGodCharm)
                return;
            else $weigh = -1;
        
        // Do action for each level
        $conf_major = Common::getConfig('Param','SoldierEquipment','Major');
        $arrIncrease = Common::getParam('SoldierIndex');
        $conf_equipExist = Common::getConfig('EquipmentRate', $this->Type); 
        if (in_array($this->Type,$conf_major))
            $conf_equip = Common::getConfig('EnchantEquipment_Minor',round($this->Rank%100),$this->Color);
        else $conf_equip = Common::getConfig('EnchantEquipment_Minor',$this->Rank,$this->Color);
        
        for($i=1; $i<=abs($levelIncrease); $i++)
        {
            $this->EnchantLevel += $weigh;
            if ($this->EnchantLevel < 0)
                $this->EnchantLevel = 0;
            else {
                // Base Index
                foreach($arrIncrease as $id => $index)
                {
                    if ($conf_equipExist['Sure'][$index]){
                        if ($levelIncrease > 0)
                            $this->$index += $conf_equip[$this->EnchantLevel][$index]*$weigh; 
                        else 
                            $this->$index += $conf_equip[$this->EnchantLevel+1][$index]*$weigh; 
                    }
                        
                }
                
                // Bonus Index
                foreach($this->bonus as $id => $oBonus)
                {
                    foreach($oBonus as $name =>$value)    
                    {
                        if (in_array($name,$arrIncrease))
                        {
                            if ($levelIncrease > 0)
                                $this->bonus[$id][$name] += $conf_equip[$this->EnchantLevel][$name]*$weigh;
                            else
                                $this->bonus[$id][$name] += $conf_equip[$this->EnchantLevel+1][$name]*$weigh;
                        }
                    }
                }           
            }        
        }
   
    }

    
    public function addDurability($dura)
    {
        $this->Durability += $dura;
        if ($this->Durability < 0)
            $this->Durability = 0;
            
        return true;
    }
    
    public static function getBonusEquipment($type, $rank, $color,$numOpt = 0 )
    {
        $conf_bonus = Common::getConfig('EquipmentRate',$type);
        $conf_equip = Common::getConfig('Wars_'.$type,$rank,$color);
        if(empty($numOpt))
            $numOpt = Common::randomIndex($conf_bonus['Color'][$color]);
        $bonus = array();
        //Debug::log('NumOPt '.$numOpt);
        $arrConver = Common::getParam('ConvertIncreaseEquipment');
        for($i=0; $i<$numOpt;$i++)
        {
            if ($type=='Weapon' && ($color==5 || $color==6 ))
                $index = 'IncreaseDamage';
            else if (($type=='Armor'|| $type=='Helmet' || $type=='Belt' ) && ($color==5 || $color==6 ))
                $index = 'IncreaseVitality';
             else if (($type=='Necklace'|| $type=='Bracelet' ) && ($color==5 || $color==6 ))
                $index = 'IncreaseDefence';
            else if ($type == 'Ring' && ($color==6)&& $rank == 1) // nhan long phung 
                $index = 'IncreaseVitality';
            else if ($type=='Ring' && ($color==5 || $color==6))
                $index = 'IncreaseDamage';
            else
                $index = Common::randomIndex($conf_bonus['Random']);             
            $value = rand($conf_equip[$index]['Min'],$conf_equip[$index]['Max']);
            $bonus[$i][$arrConver[$index]] = $value;
        }
        return $bonus;
    }
    
    /**
    * map from level vs element to rank equipment
    */
    public static function mappingLevelToRankEquipment($listItem, $element)
    {
        $listItemResult = $listItem;
        foreach($listItem as $id => $oItem)        
        {
            if (in_array($oItem['ItemType'],array(SoldierEquipment::Armor, SoldierEquipment::Helmet, SoldierEquipment::Weapon)))
                $listItemResult[$id]['Rank'] = ($element*100+$oItem['Level']);                           
            else if (in_array($oItem['ItemType'], array(SoldierEquipment::Belt, SoldierEquipment::Bracelet, SoldierEquipment::Necklace,SoldierEquipment::Ring)))
                $listItemResult[$id]['Rank'] = $oItem['Level'];                           
        }
        return $listItemResult;
    }
    
    /**
    * update autoId
    *
    */
    public function updateId($autoId)            
    {
       $this->Id = $autoId;
    }
    
    public function getIndex()
    {
        $bonus = array();
        $bonus['Damage'] += $this->Damage;                   
        $bonus['Critical'] += $this->Critical;
        $bonus['Vitality'] += $this->Vitality;
        $bonus['Defence'] += $this->Defence;
       
        foreach($this->bonus as $id => $oIndex)
        {
            foreach($oIndex as $name => $value)
            {
                $bonus[$name] += $value;
            }
        }    
        return $bonus;
    }
    
    // lay cap cua do 
    public function getRank()
    {
        return round($this->Rank%100);
    }
            
}
?>
