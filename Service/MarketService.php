<?php
/**
* Market Service
* @author hieupt
* 16/12/2011
*/
            
class MarketService
{
    
    public function sellItem($param)
    {
        $Type = $param['Type'];
        $ItemType = $param['ItemType'];
        $ItemId = $param['ItemId'];
        $Num = $param['Num'];        
        $priceTag = $param['PriceTag'];
        $positionSlot = intval($param['Position']);
        $duration = $param['Duration'];
        
        if (empty($Type) || empty($priceTag))
            return array('Error' => Error::PARAM);
        
        $maxItem = Common::getConfig('Param','Market','MaxItemPerUser');
        if ($positionSlot < 1 || $positionSlot > $maxItem)
            return array('Error' => Error::PARAM);
        
        if ($priceTag[Type::Diamond] <= 0)
            return array('Error' => Error::PARAM);
        
        // delete object from store
        $oStore = Store::getById(Controller::$uId);
                
        $pageType = "";
        $itemTT = "";       // itemType
        // get object
        
        // beta version
        if (Common::getSysConfig('isBetaMarket'))
        {
            if (!($Type == Type::SoldierEquipment && ($ItemType==SoldierEquipment::Armor || $ItemType==SoldierEquipment::Helmet || $ItemType==SoldierEquipment::Weapon)))
                return array('Error' => Error::ACTION_NOT_AVAILABLE);    
        }
        
        //check block, if block, log out
        if($Type != Type::SoldierEquipment)
            $pageT = $Type;
        else
            $pageT = $ItemType;
        $currentAutoPM = DataRunTime::get('PageManagement_'.$pageT,true);
        $currentAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
        $oUser = User::getById(Controller::$uId); 
        if(($currentAutoPM != 0) || ($currentAutoMarket != 0))
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);                 
        // end check
        
        switch($Type)
        {
            case Type::Material:                
                if (!$oStore->useItem(Type::Material, $ItemId, $Num))
                    return array('Error' => Error::NOT_ENOUGH_ITEM);
                $oObject = new NonObjectMarket($Type, $Type, $ItemId, $Num);
                $pageType = "Material";
                $itemTT = "Material";
                break;
            
            case Type::SuperFish:
                $oObject = $oStore->getOther($ItemType,$ItemId);
                if (!is_object($oObject))
                    return array('Error' => Error::OBJECT_NULL);
                $oStore->useOther($ItemType,$ItemId);
                $oObject->Type = $ItemType;
                $pageType = "SuperFish";
                $itemTT = $ItemType;
                break;
                
            case Type::SoldierEquipment:
            
                $oObject = $oStore->getEquipment($ItemType,$ItemId);
                if (!is_object($oObject))
                    return array('Error' => Error::OBJECT_NULL);
                $param_canSell = Common::getConfig('Param','CanSellEquipment');
                if (!in_array($oObject->Source,$param_canSell))
                    return array('Error' => Error::SOURCE_INVALID);
                if ($oObject->IsUsed)
                    return array('Error' => Error::EQUIPMENT_USED);
                
                $oStore->removeEquipment($ItemType, $ItemId);
                $pageType = $ItemType;
                $itemTT = $ItemType;
                
                break; 
                
            case Type::Soldier:
                $oObject = $oStore->getFish($ItemId);
                if (!is_object($oObject))
                    return array('Error' => Error::OBJECT_NULL);
                if ($oObject->FishType != FishType::SOLDIER)
                    return array('Error' => Error::OBJECT_NULL);
                
                if (!$oStore->useFish($ItemId))
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                $pageType = "Soldier";
                $itemTT = "Soldier";
                
                break;    
                
            // #SmashEgg
            case Type::Quartz:
                $oObject = $oStore->getQuartz($ItemType, $ItemId);                
                if (!is_object($oObject))
                    return array('Error' => Error::OBJECT_NULL);                 
                if (!$oStore->removeQuartz($ItemType, $ItemId))
                    return array('Error' => Error::NOT_ENOUGH_ITEM);
                $pageType = "Quartz"; // QWhite, QGreen, QYellow, QPurple
                $itemTT = $ItemType;                            
                break;
                       
                
            default :
                $pageType = "Other";
                $itemTT = "Other";
                
                break;
        }

        $oPageManagement = PageManagement::get($pageType); 
        //$preAutoPM = DataRunTime::get('PageManagement',true);
        $currUserPM = DataRunTime::inc('PageManagement_'.$pageType,1,true);
                 
        $idPage = $oPageManagement->selectPage($pageType);

        if ($idPage==0){
            DataRunTime::dec('PageManagement_'.$pageType,1,true); 
            return array('Error' => Error::NO_MORE_SLOT, 'Diamond' => $oUser->Diamond);
        }
        


        // update to PageManagement
        // update to pageM vs page, check fail ???
        $oPage = Page::getById($pageType,$idPage);
        //$preAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$idPage,true);
        // check block
        $currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$idPage,true);
        if(($currentAutoPage != 0))
        {
            DataRunTime::dec('PageManagement_'.$pageType,1,true);
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
        }
        // end check    
            
        $currUserPage = DataRunTime::inc('Page_'.$pageType.'_'.$idPage,1,true);

        
        $position = $oPage->addItem($oObject, $itemTT, $priceTag, Controller::$uId, $oUser->getUserName(), $duration);

        if (!$position)
        {
            DataRunTime::dec('PageManagement_'.$pageType,1,true); 
            DataRunTime::dec('Page_'.$pageType.'_'.$idPage,1,true);     
            return array('Error' => Error::CANT_ADD_PAGE_MARKET, 'Diamond' => $oUser->Diamond);
        }
            

        $oPageManagement->addItem($pageType, $idPage);
        
        // if add success, add to user's market
        $oMarket = Market::getById(Controller::$uId);
        $currUserMarket = DataRunTime::inc('Market_'.Controller::$uId,1,true);
        
        if (count($oMarket->ItemList) >= $maxItem)
        {
            DataRunTime::dec('PageManagement_'.$pageType,1,true); 
            DataRunTime::dec('Page_'.$pageType.'_'.$idPage,1,true); 
            DataRunTime::dec('Market_'.Controller::$uId,1,true);     
            return array('Error' => Error::MAX_SLOT_MARKET, 'Diamond' => $oUser->Diamond);    
        }
        
        $indexMarket = $oMarket->addItem($oObject,$itemTT,$priceTag,$oUser->getUserName(),$duration, $pageType, $idPage, $position, $positionSlot, $oPage->getAutoId());
        if (!$indexMarket)
        {
            DataRunTime::dec('PageManagement_'.$pageType,1,true); 
            DataRunTime::dec('Page_'.$pageType.'_'.$idPage,1,true); 
            DataRunTime::dec('Market_'.Controller::$uId,1,true);     
            return array('Error' => Error::CANT_ADD_MARKET, 'Diamond' => $oUser->Diamond);
        }

        $oPage->updateSellerPosition($position,$indexMarket);
        // save vs return client pageId vs position
        
        $inUseKey = false;
        
//        $currentAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
        if ($currUserMarket != 1)
        {
            $inUseKey = true;                                          
        }
        DataRunTime::dec('Market_'.Controller::$uId,1,true);
                
//        $currentAutoPM = DataRunTime::get('PageManagement_'.$pageType,true);
        if ($currUserPM != 1)
        {
            $inUseKey = true;                                          
        }
        DataRunTime::dec('PageManagement_'.$pageType,1,true);        
        
//        $currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$idPage,true);
        if ($currUserPage != 1)
        {
            $inUseKey = true;                                          
        }
        DataRunTime::dec('Page_'.$pageType.'_'.$idPage,1,true);     
        
        if (!$inUseKey)
        {
            $oMarket->save(); 
            $oPageManagement->save();
            $oPage->save();  
            $oStore->save(); 
            
            Zf_log::write_market_log(Controller::$uId,0,20,'sellMarket',0,0,$idPage,intval($priceTag['Diamond']), $itemTT, $oObject);
            return array('Error' => Error::SUCCESS, 'IdPage' => $idPage, 'Position' => $position, 'AutoId' =>$oPage->getAutoId());                        
        }
        else
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
    
    }
    
    public function getItemBack($param)
    {
        $index = intval($param['Position']);
        $autoId = intval($param['AutoId']);
        
        $oMarket = Market::getById(Controller::$uId);
        // check block
        $currentAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
        if($currentAutoMarket != 0)
           return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
        //$preAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
        $currUserMarket = DataRunTime::inc('Market_'.Controller::$uId,1,true);
        
        if (!is_array($oMarket->ItemList[$index]))
        {
            DataRunTime::dec('Market_'.Controller::$uId,1,true);  
            return array('Error' => Error::OBJECT_NULL);
        }
            
        $oUser = User::getById(Controller::$uId);
        if ($oMarket->ItemList[$index]['isSold'])
        {
            DataRunTime::dec('Market_'.Controller::$uId,1,true);  
            return array('Error' => Error::SOLD_ITEM, 'Diamond' => $oUser->Diamond);
        }
            

        $pageId = $oMarket->ItemList[$index]['PageId'];
        $position = $oMarket->ItemList[$index]['Position'];
        $pageType = $oMarket->ItemList[$index]['PageType'];
        
        // check lock, out immediately        
        $pageT = $pageType;
        $currentAutoPM = DataRunTime::get('PageManagement_'.$pageT,true);
        $currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId,true);
        if(($currentAutoPM != 0) || ($currentAutoPage != 0))
        {
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
        }            
        // end check
        
        // check exist
        $oPage = Page::getById($pageType, $pageId);
        //$preAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId,true);
        $currUserPage = DataRunTime::inc('Page_'.$pageType.'_'.$pageId,1,true); 
        
        $oObject = $oMarket->ItemList[$index]['Object'];
        $oObjectPage = $oPage->Data[$position]['Object'];
        $objectType = $oMarket->ItemList[$index]['Type'];
        
        if (!is_object($oObject))
        {
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            return array('Error' => Error::OBJECT_NULL);  
        }
        
        
            

        $oStore = Store::getById(Controller::$uId);
        //update item to store
        switch($objectType)
        {
            case Type::Material:
                if (!$oStore->addItem($oObject->ItemType, $oObject->ItemId, $oObject->Num))
                {
                    DataRunTime::dec('Market_'.Controller::$uId,1,true);  
                    DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                }
                    
                break;
            
            case Type::Sparta:
            case Type::Swat:
            case Type::Batman:
            case Type::Spiderman:
            case Type::Superman:
            case Type::Ironman:
                if (!$oStore->addOther($objectType, $oObject->Id, $oObject))
                {
                    DataRunTime::dec('Market_'.Controller::$uId,1,true); 
                    DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true);  
                    return array('Error' => Error::ACTION_NOT_AVAILABLE);
                }
                    
                break;
                
            case SoldierEquipment::Armor:
            case SoldierEquipment::Belt:
            case SoldierEquipment::Bracelet:
            case SoldierEquipment::Helmet:
            case SoldierEquipment::Necklace:
            case SoldierEquipment::Ring:
            case SoldierEquipment::Weapon:
                $oStore->addEquipment($oObject->Type, $oObject->Id, $oObject);
                break;          
                
            case Type::Soldier:
                $oStore->addFish($oObject->Id, $oObject); 
                break;
            //#SmashEgg
            case QuartzType::QWhite:
            case QuartzType::QGreen:
            case QuartzType::QYellow:
            case QuartzType::QPurple:
            case QuartzType::QVIP:            
                $oStore->addQuartz($oObject->Type, $oObject->Id, $oObject);                
                break;            
                
            default :
                break;
        }
       
        $oMarket->removeItem($index);

        $inUseKey = false; 
        if (is_object($oObjectPage) && (Controller::$uId == $oPage->Data[$position]['UId']))  // if isn't expired item
        {   
            if ($autoId != $oPage->Data[$position]['AutoId'])
            {
                DataRunTime::dec('Market_'.Controller::$uId,1,true);
                DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
                return array('Error' => Error::AUTOID_INVALID);  
            }           
            
            $oPage->delItem($position); 
            $oPageManagement = PageManagement::get($pageType);
            //$preAutoPM = DataRunTime::get('PageManagement',true);
            $currUserPM = DataRunTime::inc('PageManagement_'.$pageType,1,true);
            $oPageManagement->removeItem($pageType, $pageId);  

//            $currentAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
            if ($currUserMarket != 1)
            {
                $inUseKey = true;                                           
            }
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
                    
            //$currentAutoPM = DataRunTime::get('PageManagement_'.$pageType,true); 
            if ($currUserPM != 1)
            {
                $inUseKey = true;                                    
            }
            DataRunTime::dec('PageManagement_'.$pageType,1,true);        
            
            //$currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId,true);
            if ($currUserPage != 1)
            {
                $inUseKey = true;                                                  
            }
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true);
            
            if (!$inUseKey)
            {
                $oMarket->save();   
                $oPageManagement->save();  
                $oPage->save();   
                $oStore->save(); 
                Zf_log::write_market_log(Controller::$uId,0,23,'getItemBack',0,0,$pageId,0, $objectType, $oObject);
                return array('Error' => Error::SUCCESS);    
            }
            else
                return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);  

        }
        else  // if expired item
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true);  
            //$currentAutoMarket = DataRunTime::get('Market_'.Controller::$uId,true);
            if ($currUserMarket != 1)
            {
                $inUseKey = true;                                           
            }
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
            if (!$inUseKey)
            {
                $oMarket->save(); 
                $oStore->save(); 
                Zf_log::write_market_log(Controller::$uId,0,23,'getItemBack',0,0,$pageId,0, $objectType, $oObject);
                return array('Error' => Error::SUCCESS);    
            }
            else
                return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
    
   
        }
   
    }
    
    public function buyItem($param)
    {
        $pageId = $param['PageId'];                                    
        $position = $param['Position'];
        $pageType = $param['PageType']; 
        $autoIdPosition = $param['AutoId'];     
        if (empty($pageId) || empty($pageType) || empty($position))
            return array('Error' => Error::PARAM);
        
        $oUser = User::getById(Controller::$uId);
        $oPage = Page::getById($pageType, $pageId);
        $oItem = $oPage->getIndex($position);
        // check lock, out immediately        
        $pageT = $pageType;
        $currentAutoPM = DataRunTime::get('PageManagement_'.$pageT,true);
        $currentAutoMarket = DataRunTime::get('Market_'.$oItem['UId'],true);
        $currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId, true); 
        if(($currentAutoPM != 0) || ($currentAutoMarket != 0) || ($currentAutoPage != 0))
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
        // end check
        
        //$preAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId,true);
        $currUserPage = DataRunTime::inc('Page_'.$pageType.'_'.$pageId,1,true);
        
       
        if (!is_object($oItem['Object']))
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            return array('Error' => Error::SOLD_ITEM, 'Diamond' => $oUser->Diamond);  
        }
          
            
        if ($oItem['UId'] == Controller::$uId)
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            return array('Error' => Error::ACTION_NOT_AVAILABLE_MARKET, 'Diamond' => $oUser->Diamond);
        }
        
        if ($oItem['AutoId'] != $autoIdPosition)
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            return array('Error' => Error::ACTION_NOT_AVAILABLE_MARKET, 'Diamond' => $oUser->Diamond);
        }
            
            
        $oFriendMarket = Market::getById($oItem['UId']);
        //$preAutoMarket = DataRunTime::get('Market_'.$oItem['UId'],true);
        $currSellerMarket = DataRunTime::inc('Market_'.$oItem['UId'],1,true);
    
        $itemFriend = $oFriendMarket->getItem($oItem['IndexSellerMarket']);

          
        if (!is_object($itemFriend['Object']))
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            DataRunTime::dec('Market_'.$oItem['UId'],1,true);
            return array('Error' => Error::SOLD_ITEM, 'Diamond' => $oUser->Diamond); 
        }             
    
        if ($itemFriend['isSold'])
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            DataRunTime::dec('Market_'.$oItem['UId'],1,true);
            return array('Error' => Error::SOLD_ITEM, 'Diamond' => $oUser->Diamond);
        }
        
        if (!MarketService::compareObjectMarket($itemFriend,$oItem))
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            DataRunTime::dec('Market_'.$oItem['UId'],1,true);
            return array('Error' => Error::SOLD_ITEM, 'Diamond' => $oUser->Diamond);
        }
            
        if ($oFriendMarket->isExpired($oItem['IndexSellerMarket']))                        
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            DataRunTime::dec('Market_'.$oItem['UId'],1,true);
            return array('Error' => Error::EXPIRED_MARKET, 'Diamond' => $oUser->Diamond);                       
        }
            
        $oldDiamond = $oUser->Diamond ;

        if (!$oUser->addDiamond(-$oItem['PriceTag'][Type::Diamond],DiamondLog::BuyItemInMarket))
        {
            DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
            DataRunTime::dec('Market_'.$oItem['UId'],1,true);
            return array('Error' => Error::NOT_ENOUGH_DIAMOND, 'Diamond' => $oUser->Diamond);
        }

        $oStore = Store::getById(Controller::$uId);
        $itemType = $itemFriend['Type'];
        $itemFriend = $itemFriend['Object'];
        $pageType = $oPage->Type;
        switch($itemType)
        {
            case Type::Material:
                if (!$oStore->addItem($itemFriend->ItemType, $itemFriend->ItemId, $itemFriend->Num))
                {
                    DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
                    DataRunTime::dec('Market_'.$oItem['UId'],1,true);
                    return array('Error' => Error::ACTION_NOT_AVAILABLE_MARKET, 'Diamond' => $oUser->Diamond);
                }
                    
                break;
            
            case Type::Sparta:
            case Type::Swat:
            case Type::Batman:
            case Type::Spiderman:
            case Type::Superman:
            case Type::Ironman:
                $itemFriend->updateId($oUser->getAutoId());
                if (!$oStore->addOther($itemType, $itemFriend->Id, $itemFriend))
                {
                    DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true); 
                    DataRunTime::dec('Market_'.$oItem['UId'],1,true);
                    return array('Error' => Error::ACTION_NOT_AVAILABLE_MARKET, 'Diamond' => $oUser->Diamond);
                }
                    
                break;
                
            case SoldierEquipment::Armor:
            case SoldierEquipment::Belt:
            case SoldierEquipment::Bracelet:
            case SoldierEquipment::Helmet:
            case SoldierEquipment::Necklace:
            case SoldierEquipment::Ring:
            case SoldierEquipment::Weapon:
                $itemFriend->updateId($oUser->getAutoId());
                $oStore->addEquipment($itemFriend->Type, $itemFriend->Id, $itemFriend);
                break;          
                
            case Type::Soldier:
                $itemFriend->updateId($oUser->getAutoId());
                $oStore->addFish($itemFriend->Id, $itemFriend);
                break;

            case QuartzType::QWhite:
            case QuartzType::QGreen:
            case QuartzType::QYellow:
            case QuartzType::QPurple:            
            case QuartzType::QVIP:
                $itemFriend->updateId($oUser->getAutoId());
                $oStore->addQuartz($itemFriend->Type, $itemFriend->Id, $itemFriend);
                break;          
                
            default :
                break;
        }

        $oFriendMarket->soldItem($oItem['IndexSellerMarket'], Controller::$uId);
        
        // update to market's friend
        $conf_fee = Common::getConfig('Param','Market','Fee');
        //$oCurrency = array();
        //$oCurrency[Type::ItemType] = Type::Diamond;
        //$oCurrency[Type::ItemId] = 1;
        //$oCurrency[Type::Num] = $oItem['PriceTag'][Type::Diamond] - ceil($conf_fee*$oItem['PriceTag'][Type::Diamond]);
        //$oFriendMarket->addCurrencyReceive($oCurrency);      

        // update pageM
         
        $oPage->delItem($position);

        $oPageManagement = PageManagement::get($pageType);
        //$preAutoPM = DataRunTime::get('PageManagement',true);
        $currUserPM = DataRunTime::inc('PageManagement_'.$pageType,1,true);
        
        $oPageManagement->removeItem($pageType, $pageId);
        
        $inUseKey = false;
        
        //$currentAutoMarket = DataRunTime::get('Market_'.$oItem['UId'],true);
        if ($currSellerMarket != 1)
        {
            $inUseKey = true;                                            
        }
        DataRunTime::dec('Market_'.$oItem['UId'],1,true);
                
        //$currentAutoPM = DataRunTime::get('PageManagement_'.$pageType,true); 
        if ($currUserPM != 1)
        {
            $inUseKey = true;                                      
        }
        DataRunTime::dec('PageManagement_'.$pageType,1,true);       
        
        //$currentAutoPage = DataRunTime::get('Page_'.$pageType.'_'.$pageId,true);
        if ($currUserPage != 1)
        {
            $inUseKey = true;                                     
        }
        DataRunTime::dec('Page_'.$pageType.'_'.$pageId,1,true);
        
        if (!$inUseKey)
        {
            $oFriendMarket->save();
            $oPageManagement->save(); 
            $oPage->save();  
            $oStore->save();        
            $oUser->save();   
            
             $DiamondExchange= $oUser->Diamond - $oldDiamond ;
            
            Zf_log::write_market_log(Controller::$uId,intval($oItem['UId']),23,'buyItem',0,0,$pageId,$DiamondExchange, $itemType, $itemFriend); 
            return array('Error' => Error::SUCCESS);               
        }
        else
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);
   
    } 
    
    public function getListItem($param) 
    {
        $PageType = $param['PageType'];
        $PageId = $param['PageId'];
        
        if (empty($PageType) || empty($PageId))
            return array('Error' => Error::PARAM);
            
        $oPage = Page::getById($PageType,$PageId);
        DataRunTime::inc('Page_'.$PageType.'_'.$PageId,1,true);  
        
        // update expired item
        $conf_delta = Common::getConfig('Param','Market','TimeUpdateExpiredPage');
        //$LastTime = DataProvider::getMemcache()->get('LastUpdate_Page_'.$PageType.'_'.$PageId);
        
        $oPageManagement = PageManagement::get($PageType);
        DataRunTime::inc('PageManagement_'.$pageType,1,true); 

        if ($oPage->LastTimeUpdate + $conf_delta < $_SERVER['REQUEST_TIME'])
        {
            $countItemExpired = 0;
            foreach($oPage->Data as $id =>$oData)       
            {
                if ($oPage->isExpiredItem($id))
                {
                    $oPage->delItem($id);
                    //$oPageManagement->removeItem($PageType,$PageId);             
                    $countItemExpired++;
                    Zf_log::write_market_log(Controller::$uId,0,23,'expiredItemMarket',0,0,$PageId,0, $oData['Type'],$oData['Object']);
                }
            }
       
            $inUseKey = false;
            $currentAutoPage = DataRunTime::get('Page_'.$PageType.'_'.$PageId,true);
            if ($currentAutoPage != 1)
            {
                $inUseKey = true;                                     
            }
            DataRunTime::dec('Page_'.$PageType.'_'.$PageId,1,true);
            
            $currentAutoPM = DataRunTime::get('PageManagement_'.$pageType,true); 
            if ($currentAutoPM != 1)
            {
                //$inUseKey = true;                                      
            }
            DataRunTime::dec('PageManagement_'.$pageType,1,true);

            if (!$inUseKey)    
            {
                $oPage->LastTimeUpdate = $_SERVER['REQUEST_TIME'];
                $oPage->save();
                $countPage = count($oPage->Data);
                if ($oPageManagement->pageItems[$PageId] != $countPage)
                {
                    $oPageManagement->setItem($PageType,$PageId,$countPage);
                    $oPageManagement->save();    
                }
            }
        }
        else
        {
            DataRunTime::dec('Page_'.$PageType.'_'.$PageId,1,true); 
            DataRunTime::dec('PageManagement_'.$pageType,1,true);       
        }
        //Zf_log::write_market_log(Controller::$uId,0,20,'getListMarket',0,0);
        return array('Error' => Error::SUCCESS, 'ItemList' => $oPage);
    }
    
    public function getMarket()
    {
        $oMarket = Market::getById(Controller::$uId);
        return array('Error' => Error::SUCCESS, 'Market' => $oMarket);
    }
    
    /**
    * Buy item in blackmarket's shop
    * 
    */
    public function buyItemBlackMarket($param)
    {
        $Tab = $param['Tab'];
        $SubTab = $param['SubTab'];
        $itemId = intval($param['idItemShop']);
        
        if (empty($Tab) || empty($itemId))    
            return array('Error' => Error::PARAM);
        if ($Tab=='Grocery')
            $conf_shop = Common::getConfig('BlackMarketShop',$Tab);                                                                        
        else $conf_shop = Common::getConfig('BlackMarketShop',$Tab,$SubTab);
        if (!is_array($conf_shop[$itemId]))
            return array('Error' => Error::OBJECT_NULL);
            
        $oUser = User::getById(Controller::$uId);
        if ($conf_shop[$itemId][Type::ZMoney] != 0)
        {
            $info = $itemId.':buyItemBlackMarket:'.$conf_shop[$itemId][Type::ZMoney];
            if (!$oUser->addZingXu(-$conf_shop[$itemId][Type::ZMoney],$info))
                return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        }
        else
        {
            if (!$oUser->addDiamond(-$conf_shop[$itemId][Type::Diamond],DiamondLog::BuyItemInBlackShop))
                return array('Error' => Error::NOT_ENOUGH_DIAMOND);    
        }
        
        
        $oUser->saveBonus(array($conf_shop[$itemId]));
        $oUser->save();
        if ($conf_shop[$itemId][Type::ZMoney] != 0){
            if ($conf_shop[$itemId][Type::ItemType]=='Diamond')
                Zf_log::write_act_log(Controller::$uId,0,23,'exchangeDiamond'.$conf_shop[$itemId][Type::ItemId],0,-$conf_shop[$itemId][Type::ZMoney],0,$oUser->Diamond,$conf_shop[$itemId][Type::ItemId]);    
            else
                Zf_log::write_act_log(Controller::$uId,0,23,'buyItemBlackMarket',0,-$conf_shop[$itemId][Type::ZMoney],0,$Tab,$SubTab,$itemId,$oUser->Diamond);
        }
        else
            Zf_log::write_act_log(Controller::$uId,0,23,'buyItemBlackMarket',0,0,-$conf_shop[$itemId]['Diamond'],$Tab,$SubTab,$itemId,$oUser->Diamond);
        return array('Error' => Error::SUCCESS);
    }
    
    /**
    * get diamond from sold item
    * 
    */
    public function getDiamond($param)
    {
        $index = intval($param['Position']); 
        $oMarket = Market::getById(Controller::$uId);
        DataRunTime::inc('Market_'.Controller::$uId,1,true);     
        
        $oItem = $oMarket->ItemList[$index];
        if (!is_array($oItem))
            return array('Error' => Error::OBJECT_NULL);
        if (!$oItem['isSold'])
            return array('Error' => Error::ID_INVALID); 
            
        $conf_fee = Common::getConfig('Param','Market','Fee');    
        $oCurrency = array();
        $oCurrency[Type::ItemType] = Type::Diamond;
        $oCurrency[Type::ItemId] = 1;
        $oCurrency[Type::Num] = $oItem['PriceTag'][Type::Diamond] - ceil($conf_fee*$oItem['PriceTag'][Type::Diamond]);    
        
        if ($oCurrency[Type::Num] < 0)
            $oCurrency[Type::Num] = 0;
        
        $idBuyer = $oItem['buyer'];
        $oUser = User::getById(Controller::$uId);
        $oMarket->removeItem($index);
        
        $currentAuto = DataRunTime::get('Market_'.Controller::$uId,true);
        if ($currentAuto == 1)
        {
            $oUser->saveBonus(array($oCurrency));    
            $oMarket->save();
            $oUser->save();
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
            Zf_log::write_act_log(Controller::$uId,0,23,'getDiamond',0,0,$oCurrency[Type::Num],$oUser->Diamond,$idBuyer);
            return array('Error' => Error::SUCCESS);            
        }
        else
        {
            DataRunTime::dec('Market_'.Controller::$uId,1,true);
            return array('Error' => Error::INUSE_MARKET_KEY,'Diamond' => $oUser->Diamond);                           
        }
                   
    }
    
    public function exchangeDiamond($param)
    {
        $packId = intval($param['PackId']);
        
        $conf_exchange = Common::getConfig('DiamondExchange');
        if (!is_array($conf_exchange[$packId]))
            return array('Error' => Error::PARAM);
            
        $oUser = User::getById(Controller::$uId);
        $info = $packId.':exchangeDiamond:'.$conf_exchange[$packId]['ZMoney'];
        if (!$oUser->addZingXu(-$conf_exchange[$packId]['ZMoney'],$info))
            return array('Error' => Error::NOT_ENOUGH_ZINGXU);
        $oUser->addDiamond($conf_exchange[$packId]['Diamond'],DiamondLog::exchangeDiamond);
           
        $oUser->save();
        // log
        Zf_log::write_act_log(Controller::$uId,0,23,'exchangeDiamond'.$packId,0,-$conf_exchange[$packId]['ZMoney'],0,$oUser->Diamond,$packId);
        
        return array('Error' => Error::SUCCESS);
    }
    
    public function getObject($param)
    {
        
        $pageId = $param['PageId'];                                    
        $position = $param['Position'];
        $pageType = $param['PageType']; 
        $autoIdPosition = $param['AutoId'];     
        if (empty($pageId) || empty($pageType) || empty($position))
            return array('Error' => Error::PARAM);
        
        $oPage = Page::getById($pageType, $pageId);
        $oItem = $oPage->getIndex($position); 
        if(empty($oItem))
            return array('Error' => Error::SOLD_ITEM);
		$oFriendMarket = Market::getById($oItem['UId']);
		$itemFriend = $oFriendMarket->getItem($oItem['IndexSellerMarket']);
		$itemFriend['UId'] = $oItem['UId'];
        return array('Error' => Error::SUCCESS, 'Object' => $itemFriend);
    }
    
    public static function compareObjectMarket($object1, $object2)  
    {
        if ($object1['AutoId'] != $object2['AutoId'])
            return false;
        if ($object1['StartTime'] != $object2['StartTime'])
            return false;
        if ($object1['Type'] != $object2['Type'])
            return false;
        return true;
    }
}
?>
