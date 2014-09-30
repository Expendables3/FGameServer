<?php
class Magnet
{
    public $Id;
    public $Level;
    public $NumUseLeft;
    public $LastTimeUse;
    
    function __construct($id, $level, $numUse)
    {
        $this->Id = $id;
        $this->Level = $level;
        $this->NumUseLeft = $numUse;
        $this->LastTimeUse = $_SERVER['REQUEST_TIME'];
    }
    
    public function useMagnet()
    {
        if ($this->NumUseLeft <=0)
            return false;
        else $this->NumUseLeft--;

        $this->LastTimeUse = $_SERVER['REQUEST_TIME'];
        return true;
    }
    
    public function update()
    {
        $conf_freeUse = Common::getConfig('Param','Magnet','FreeUse');
        $dayDiff = Common::getDayDifferent($this->LastTimeUse,$_SERVER['REQUEST_TIME']);
        if ($dayDiff>=1)
            $this->NumUseLeft += $conf_freeUse;
        $this->LastTimeUse = $_SERVER['REQUEST_TIME'];    
    }
    
    public function addNumUse($num)
    {
        if ($num<=0)
            return false;
        $this->NumUseLeft += $num;
        return true;
    }
}
?>
