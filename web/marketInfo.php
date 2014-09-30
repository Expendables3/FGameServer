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
  
    
  function test($pagetype)
  {
    if (empty($pagetype))
        return ;
    $oPageM = PageManagement::get($pagetype);
    var_dump($oPageM->pageItems);
    $conf_max = Common::getConfig('Param','Market','MaxPage'); 
    for($i=1;$i<=$conf_max;$i++)
    {
        $oPage = Page::getById($pagetype,$i);
        echo "<br/> ".$i. "  " ;
        var_dump(count($oPage->Data));    
    }
    
  }

  test($_REQUEST['type']);

?>
