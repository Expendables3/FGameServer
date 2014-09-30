<?php
class PowerTinhQuest extends Model
{
    public $Quest;
    public $LastTimeAccess;
    public $PointReceived = 0;
    
    public function __construct($uId)
    {
        $this->newPowerTinhQuest();
        parent :: __construct($uId);
    }
    
    public function newPowerTinhQuest()
    {
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        $Lastday =  date('Ymd', $this->LastTimeAccess);
        
        if ($Today != $Lastday){
            $conf_quest = Common::getConfig('PowerTinh_Quest');  
            $this->Quest = array();
            $this->PointReceived = 0;
            foreach($conf_quest as $idQuest => $listQuest)
            {
                $this->Quest[$idQuest]['Action'] = $listQuest['Action'];
                $this->Quest[$idQuest]['Num'] = $listQuest['Require'];
                $this->Quest[$idQuest]['Done'] = 0;                
                //$this->Quest[$idQuest]['IsCal'] = false;                
            }
            $this->LastTimeAccess = $_SERVER['REQUEST_TIME'];
            $this->save();
        }    
    }
    
    public function calculatePointReceived()
    {
        $conf_power = Common::getConfig('PowerTinh_Quest');
        foreach($this->Quest as $id => $oQuest)
        {
            if (($oQuest['Done'] >= $oQuest['Num'])&&!$oQuest['IsCal'])          
            {
                $this->Quest[$id]['IsCal'] = true;
                $this->PointReceived += $conf_power[$id]['Point'];
            }
        }
        return $this->PointReceived;
    }
    
    public function usePoint($amount)
    {
        $amount = intval($amount);
        if ($amount < 1)
            return false;
        if ($this->PointReceived < $amount)
            return false;
        $this->PointReceived -= $amount;
        return true;
    }
    
    public function updateAction($output, $method)
    {
        // cho tach tu dong <=> tach bang tay
        if($method == 'autoRefineIngredient')
        {
            $method = 'refineIngredient';
        }
        //-------------
        
        $conf_power = Common::getConfig('PowerTinh_Quest');
        foreach($this->Quest as $idQuest => $oQuest)            
        {
            if ($oQuest['Done'] < $oQuest['Num'])
            {
                $isAdd = 1;
                if ($oQuest['Action']=='sendGift')
                    $isAdd = $output['NumSuccess'];
                if ($method != $oQuest['Action'])
                    $isAdd = 0;
                if(is_array($conf_power[$idQuest]['Param']))
                    foreach($conf_power[$idQuest]['Param'] as $name => $herbValue)
                    {
                        if ($output[$name]!=$herbValue)
                            $isAdd = 0;   
                    }
                $this->Quest[$idQuest]['Done'] += $isAdd;
                if ($this->Quest[$idQuest]['Done'] >=$this->Quest[$idQuest]['Num'])    
                {
                    $this->PointReceived += $conf_power[$idQuest]['Point']; 
                }
            }
        }
    }
    
    public function getQuest()
    {
        $this->newPowerTinhQuest();
        return $this;
    }
    
    public static function getById($uId)
    {   
        $oPower = DataProvider :: get($uId,__CLASS__) ;
        if (!is_object($oPower))
        {
            $newPower = new PowerTinhQuest(Controller::$uId);
            return $newPower;
        }
        return $oPower;
    }


    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }    
}  
?>
