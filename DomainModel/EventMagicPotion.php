<?php

/**
 * UserAction
 * @author Toan Nobita
 * 2/9/2010
 */


class EventMagicPotion extends Model
{
    public $LastTimeGenerate = 0;
    public $HerbQuest = array();
    public $RefreshMoney = 0;
    
    public function __construct($uId)
    {
        //$this->checkNewQuest($Level,true,false);
        $this->newListMagicPotion();
        
        $conf_quest = Common::getConfig('MagicPotion_Quest');            
        foreach($conf_quest as $idHerb => $listQuest)
        {
            $this->newMagicPotion($idHerb);
        }
        
        parent :: __construct($uId);
    }
    
    public function newListMagicPotion()
    {
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        $Lastday =  date('Ymd', $this->LastTimeGenerate);
        if ($Today != $Lastday){
            $this->RefreshMoney = 0;    
            $this->LastTimeGenerate = $_SERVER['REQUEST_TIME'];
        }            
    }
    
    public function newMagicPotion($idHerb)
    {
        $conf_quest = Common::getConfig('MagicPotion_Quest',$idHerb);            
        $idQuest = array_rand($conf_quest);
        $this->HerbQuest[$idHerb]['IdQuest'] = $idQuest;
        $this->HerbQuest[$idHerb]['Num'] = rand($conf_quest[$idQuest]['Min'],$conf_quest[$idQuest]['Max']);
        $this->HerbQuest[$idHerb]['Done'] = 0;
        $this->HerbQuest[$idHerb]['GotGift'] = false;
    }
    
    public function getHerbList()
    {
        $this->newListMagicPotion();
        return $this->HerbQuest;
    }
    
    public function isDoneHerbQuest($idHerb)
    {
        return ($this->HerbQuest[$idHerb]['Done'] >= $this->HerbQuest[$idHerb]['Num']);
    }
    
    public function getGift($idHerb)
    {
        if(!isset($this->HerbQuest[$idHerb]))
            return false;
        $this->HerbQuest[$idHerb]['GotGift'] = true;
    }
    
    public function addHerbAction($action, $method)
    {        
        $conf_herb = Common::getConfig('MagicPotion_Quest');
        foreach($this->HerbQuest as $idQuest => $oQuest)            
        {
            if ($oQuest['Done'] < $oQuest['Num'])
            {
                $isAdd = 1;
                foreach($conf_herb[$idQuest][$oQuest['IdQuest']]['Param'] as $name => $herbValue)
                {
                    if ($action[$name]!=$herbValue)
                        $isAdd = 0;   
                }
                if ($method != $conf_herb[$idQuest][$oQuest['IdQuest']]['Action'])
                    $isAdd = 0;
                $this->HerbQuest[$idQuest]['Done'] += $isAdd;    
            }
        }
    }
    
    public function quickDoneHerbJob($idHerb)
    {
        if (($this->HerbQuest[$idHerb]['Done'] < $this->HerbQuest[$idHerb]['Num']) && ($this->HerbQuest[$idHerb]['GotGift']==false))
        {
            $this->HerbQuest[$idHerb]['Done'] = $this->HerbQuest[$idHerb]['Num'];           
            return true;    
        }
        else return false;
    }
    
    public static function getById($uId)
    {   
        $oMagic = DataProvider :: get($uId,__CLASS__) ;
        if (!is_object($oMagic))
        {
            $newMagic = new EventMagicPotion(Controller::$uId);
            return $newMagic;
        }
        return $oMagic;
    }


    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }
}
?>
