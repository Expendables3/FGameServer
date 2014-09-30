<?php
  
  error_reporting(E_ALL & ~E_NOTICE); //=> show all  

        define( "ROOT_DIR" , '..' );
        define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
        define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
        define( "SER_DIR" , ROOT_DIR ."/Service" );
        define( "LIB_DIR" , ROOT_DIR ."/libs" );

        require LIB_DIR.'/Common.php';
        require LIB_DIR.'/ZingApi.php';
        require LIB_DIR.'/dal/DataProvider.php';

        Controller::init();


  function checkAdmin()
  {
      return in_array(Controller::$uId, array(4451094,951854,284847,10121272));
  }

  function checkConnect()
  {
      if (checkAdmin()){
          $conf_db = Common::getConfig('DbConfig','EventInGameFW_Live');
          $connection = mysql_connect($conf_db['Host'],$conf_db['User'],$conf_db['Pass']);    
          if (mysql_errno($connection))
            echo mysql_error($connection);
          mysql_select_db('CommonEvent'); 
          if (mysql_errno($connection))
            echo mysql_error($connection);         
          if (!mysql_errno($connection))
            echo 'Okay';
      }
      else {
          echo 'Not permission!';
      }
  }
  
  checkConnect();

?>
