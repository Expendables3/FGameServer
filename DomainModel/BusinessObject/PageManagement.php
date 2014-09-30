<?php
class PageManagement extends Model 
{
    public $pageItems = array(); // num item remain
    public $pageType;
    
    function __construct($pageType)
    {
        $this->pageType = $pageType;
        parent::__construct($pageType);                            
    }
    
    public function addItem($preKey,$idPage)
    {        
        $this->pageItems[$idPage]++;       
    }
    
    public function setItem($preKey, $idPage, $num)
    {        
        $this->pageItems[$idPage] = $num;       
    }
    
    public function selectPage($preKey)
    {
        $conf_market = Common::getConfig('Param','Market');
            
        $arrFree = array();
        for ($i=1; $i<=$conf_market['MaxPage']; $i++)
        {
            if ($this->pageItems[$i] < $conf_market['ItemPerPage']-10)
            {
                return $i;
            }
        }  
        return 0;
    }
    

    public function removeItem($preKey, $idPage)
    {
        $this->pageItems[$idPage]--; 
    }
    
    public static function get($pageType)
    {
        $oPageManagement = DataProvider :: get($pageType,__CLASS__) ;
        if(!is_object($oPageManagement))
        {
            $newObject = new PageManagement($pageType);
            return  $newObject ;
        }
        return $oPageManagement;
                
        //$this->autoIncre = DataRunTime::get('incPageManagement');
        //DataRunTime::inc('incPageManagement',1);
    }
    
    public static function del($pageType)
    {
        return DataProvider :: delete($pageType,__CLASS__);
    }
}
?>
