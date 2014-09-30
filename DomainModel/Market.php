<?php
/**
* Black market
* @author hieupt
* 05/12/2011
*/
class Market extends Model
{
    public $autoIncre = 0;
    public $ItemList = array();
    private $CurrencyReceive = array();
    
    public function __construct($uId)
    {                                    
        parent::__construct($uId);
    }
     
        
    public function save()
    {
        parent::save();
    }
    
    public function addItem($object, $itemTT, $priceTag, $username, $duration, $pageType, $idPage, $position, $posSlot, $autoId)
    {
        if (isset($this->ItemList[$posSlot]))
            return false;
                          
                        
        $this->ItemList[$posSlot]['Username'] = $username;
        $this->ItemList[$posSlot]['PriceTag'] = $priceTag;
        $this->ItemList[$posSlot]['Object'] = $object;
        $this->ItemList[$posSlot]['Type'] = $itemTT;
        $this->ItemList[$posSlot]['StartTime'] = $_SERVER['REQUEST_TIME'];
        $this->ItemList[$posSlot]['Duration'] = $duration;
        $this->ItemList[$posSlot]['PageType'] = $pageType;
        $this->ItemList[$posSlot]['PageId'] = $idPage;
        $this->ItemList[$posSlot]['Position'] = $position;
        $this->ItemList[$posSlot]['AutoId'] = $autoId;
        $this->ItemList[$posSlot]['isSold'] = false;
        return $posSlot;
    }
    
    public function soldItem($index, $buyerId)
    {
        if (!is_array($this->ItemList[$index]))
            return false;
        else {
            $this->ItemList[$index]['isSold'] = true;    
            $this->ItemList[$index]['buyer'] = $buyerId;    
        }
    }
    
    public function getItem($index)
    {
        return $this->ItemList[$index];
    }
    
    public function removeItem($index)
    {
        unset($this->ItemList[$index]);
    }
    
    public function addCurrencyReceive($newCurrency)
    {
        $countCurrency = count($this->CurrencyReceive);
        $this->CurrencyReceive[$countCurrency] = $newCurrency;
    }
    
    public function getCurrencyReceive()
    {
        return $this->CurrencyReceive;
    }
    
    public function deleteCurrencyReceive()
    {
        unset($this->CurrencyReceive);
    }
    
    public function isExpired($index)
    {
        return ($this->ItemList[$index]['Duration'] + $this->ItemList[$index]['StartTime'] < $_SERVER['REQUEST_TIME']);
    }
    
    public static function getById($uId)
    {   
        $oMarket = DataProvider :: get($uId,__CLASS__);
        if(!is_object($oMarket))
        {
            $newObject = new Market($uId);
            return  $newObject ;
        }
        return $oMarket;
    }
    
    public static function del($uId)
    {
        return DataProvider :: delete($uId,__CLASS__);
    }
} 
?>
