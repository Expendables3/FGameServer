<?php
class Page extends Model
{
    public $Type;
    public $Id;
    public $Data;    
    private $AutoId = 0;
    public $LastTimeUpdate = 0;     // auto update once a day
    
    /*
    * data = array[0->99]
    * data[i] : ['UserName'] = owner
    *           ['Object'] = object
    *           ['PriceTag'] = price
    *           ['StartTime'] = timestamp
    *           ['Duration'] = time
    */
    
    function __construct($pageType, $pageId)
    {
        $this->Type = $pageType;
        $this->Id = $pageId;
        $Data = array();
        parent::__construct($pageType,$this->Id);
    }
    
    public function getData()  
    {
        return $this->Data;
    }
    
    public function delItem($idItem)
    {
        if (!is_array($this->Data[$idItem]))
            return false;
        else {
            unset($this->Data[$idItem]);
            return true;
        }    
    }
    
    // random position
    public function addItem($oItem,$itemTT, $priceTag, $uid, $username, $duration)
    {
        $maxItem  = Common::getConfig('Param','Market','ItemPerPage');
        $position = -1;
        for($i=1;$i<=$maxItem;$i++)
        {
            if (empty($this->Data[$i]))
            {
                $position = $i;
                break;
            }
        }
        if ($position==-1)
            return false;
        
        $this->Data[$position]['Object'] = $oItem;
        $this->Data[$position]['Type'] = $itemTT;
        $this->Data[$position]['PriceTag'] = $priceTag;
        $this->Data[$position]['UId'] = $uid;
        $this->Data[$position]['Username'] = $username;
        $this->Data[$position]['Duration'] = $duration;
        $this->Data[$position]['AutoId'] = ++$this->AutoId;
        if ($this->AutoId >= 1000000)
            $this->AutoId = 0;
        $this->Data[$position]['StartTime'] = $_SERVER['REQUEST_TIME'];
        
        return $position;
        
    }
    
    public function isExpiredItem($index)
    {
        if (empty($this->Data[$index]))
            return true;
        return ($this->Data[$index]['StartTime'] + $this->Data[$index]['Duration'] < $_SERVER['REQUEST_TIME']);
    }
    /*
    public function updateExpiredItem()
    {
        $Today = date('Ymd',$_SERVER['REQUEST_TIME']);
        $LastDay = date('Ymd',$this->LastTimeAutoUpdate);
        $countItemExpired = 0;
        if ($Today != $LastDay)
        {
            foreach($this->Data as $id =>$oData)       
            {
                if (isExpiredItem($id))
                {
                    $countItemExpired++; 
                    break;      
                }
            }
        }
        
        // if exist expired item
        if ($countItemExpired > 0)
        {
            $arrMarket = array();
            foreach($this->Data as $id => $oData)                    
            {
                if(isExpiredItem($id))
                {
                    $oMarket = Market::getById($oData['UId']);
                }
            }
        }
    }
    */
    public function updateSellerPosition($position, $indexMarket){
        $this->Data[$position]['IndexSellerMarket'] = $indexMarket;
    }
    
    public function getIndex($index)
    {
        return $this->Data[$index];
    }
    
    public function getAutoId()
    {
        return $this->AutoId;
    }
    
    public static function getById($type, $id)
    {   
        $oPage = DataProvider :: get($type,__CLASS__,$id) ;
        if(!is_object($oPage))
        {
            $newObject = new Page($type, $id);
            return  $newObject ;
        }
        
        // update expired item
        $currentAutoPage = DataRunTime::get('Page_'.$type.'_'.$id,true);
        if ($currentAutoPage==0)
        {
            //$oPage->updateExpiredItem();
            $oPage->save();
        }
        
                
        
        return $oPage;
        
    }
    
    public static function del($pageType, $pageId)
    {                                      
        return DataProvider :: delete($pageType,'Page',$pageId) ;
    }
    
}

?>
