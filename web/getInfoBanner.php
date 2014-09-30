<?php

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );
//define( "TPL_DIR" , ROOT_DIR ."/Tpl/Admin");

require LIB_DIR.'/Common.php';
require LIB_DIR.'/ZingApi.php';
require LIB_DIR.'/dal/DataProvider.php';
Controller::init();
$key = $_REQUEST['namegame'] ;
if(!empty($key))
  {
    // get du lieu ra              
    $keyword = 'designTool' ;  
    $return =  DataProvider::get($key,$keyword);

    if($return == False)
    { 
       return false ;
    } 
    echo json_encode($return);   
  }
  else
  { 
    return false ;          
  }
  
?>
