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
            $list = $_REQUEST['list'];
            $listUser = explode("\n",$list);
            $data[0] = 0;
            $ii = 1;
            $data2 = "";
            $data3 = "";

            foreach($listUser as $index => $uid)
            { 
                
                $oUser = User::getById(intval(trim($uid)));
                if(is_object($oUser))
                {
                    $dt2[] = $oUser->Name;
                    $dt3[] = $oUser->getUserName();
                }
                else {
                    $dt2[] = "";
                    $dt3[] = "";
                }
                echo "-".intval(trim($uid))."+";
            }
              

            echo "-----------------". "<br/>";
            echo "---username---". "<br/>";
            echo "-----------------". "<br/>";
            
            foreach($dt2 as $index => $kk){
                echo $kk;
                echo "<br/>";
            }
            
            echo "<br/>";echo "<br/>";echo "<br/>";
            
            echo "-----------------". "<br/>";
            echo "----displayname-----". "<br/>";
            echo "-----------------". "<br/>";
            
            foreach($dt3 as $index => $kk){
                echo $kk;
                echo "<br/>";
            }
        }
    ?>
    
  
  
  </body>


</html>