<?php
    error_reporting(0); //=> show all

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';

    Controller::init();
  
    
  function test()
  {
    $list = array('Armor','Helmet') ;
    foreach($list as $name) 
    {
        $oPageM = PageManagement::get($name);
        
        $conf_max = Common::getConfig('Param','Market','ItemPerPage'); 
        for($i=1;$i<=$conf_max;$i++)
        {
            $oPage = Page::getById($name,$i);
            $oPageM->pageItems[$i] = count($oPage->Data);    
        }

        $oPageM->save();
    }
    
    
    
  }

  test();
  StaticCache::forceSaveAll();
  
?>
