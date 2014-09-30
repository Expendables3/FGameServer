<html>
  
  <head>
  </head>
  
  
  <body>
    
    <form method="POST" action="">
        
        
        <textarea cols="50" rows="5" id="list" name="list">
        </textarea>
        
        
        <input id="sub" name="submit" type=submit value="GetInfo">
        </input>
    
    </form>

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
    
        if(isset($_REQUEST['submit'])){
            $aa = $_REQUEST['list'];
            $len = strlen($aa);
            $cc = strrev(substr($aa,0, $len-2));
            $dd = substr($aa, $len-2,2);
            $args2 = $cc.$dd;
            
            $args2 = base64_decode($args2); 
            $args3 = json_decode($args2,true);
            var_dump($args3);
        }
    ?>
    
  
  
  </body>


</html>