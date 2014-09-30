<?php
    error_reporting(E_ALL ); //=> show all
    ini_set("memory_limit", "-1");
    ini_set("max_execution_time", "1236");
?>



<html>
<head>
</head>
<body>
    <div align="center" style="color:blue; font-size:30px;">Check ItemCode của User</div>
    
    <form id="form1" name="form1" method="post" action="">
        <table align="center" width="500" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td  height="23">Code :</td>
            <td><input type="text" name="Code" /></td>                            
          </tr>
          <tr>
            <td  height="23">UserId :</td>
            <td><input type="text" name="uId" /> <p style="color: green; font-size: 10px;">hãy nhập uId nếu muốn kiểm tra với User</p></td>                            
          </tr>
          <tr>
            <td>
                <input type="submit" name="Check" value="Check"/>
                <input type="reset" name="Reset" value="Cancel" />
            </td>
          </tr>
          <tr><td style="color: red"><?php echo($error) ; ?></td></tr>
          </table>
    </form>
    
</body>
</html>

<?php
    header('Cache-Control: no-cache, must-revalidate, max-age=0',true);
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT',true);
    header('Pragma: no-cache',true);
    define( "IN_INU" , true );

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "TPL_DIR" , ROOT_DIR ."/Tpl/Index");

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';
    
    //Controller::init();
    
    $config = & Common :: getConfig() ;        
    Model::$appKey = $config['appKey'];
    
    
    $Code = '';
    $uId = '';
    $error = '';
    $Code = $_REQUEST['Code'];
    $uId = $_REQUEST['uId'];
    
    
    if(!$_REQUEST['Check'])
        exit;
    if(empty($Code))
    {
        echo '<div align="center" style="color: red;">ban chua nhap code</div>' ;
        exit();
        
    }
    else
    {      
        // kiem tra xem tren he thong code nay da dung hay chua   
        $arr_Info = DataProvider::get($Code,'ConfigItemCode');
        
        if(empty($arr_Info))
        {
            echo '<div align="center" style="color: red;">code ko hop le</div>' ;
            exit();
        }
        
        if(is_array($arr_Info))
        {
            $IdConfigGift   = $arr_Info['IdConfig'];
            $UserId         = $arr_Info['UserId'];
        }
        else
        {
            $IdConfigGift = $arr_Info ;
        }
        
        
        if($IdConfigGift < 1 )
        {
            echo '<div align="center" style="color: red;">code da duoc su dung'.$UserId.' </div>' ;
            exit();
        }
           
        $conf = array()  ;
        $conf = common::getConfig('ItemCodeContent',$IdConfigGift);
        // kiem tra han su dung code
        if(empty($conf))
        {
            echo '<div align="center" style="color: red;">ko co goi qua cho code nay</div>' ;
            exit();
        }
        $today = $_SERVER['REQUEST_TIME'];
        if($conf['FromDay'] > $today || $conf['ToDay'] < $today)
        {
            echo '<div align="center" style="color: red;">code da het han su dung</div>' ;
            exit();
        }

        if(!empty($uId))
        {
            $oItemCode = ItemCode::getById($uId);

            // kiem tra xem da dung ItemCode nay chua
            if(isset($oItemCode->ItemCode['Code'][$Code]))
            {
                echo '<div align="center" style="color: red;">code da duoc ban su dung roi</div>' ;
                exit();
            }
            
            // kiem tra xem da dung IdConfig nay chua    
            if(isset($oItemCode->ItemCode['ConfigId'][$IdConfigGift]))
            {
                echo '<div align="center" style="color: red;">qoi qua nay ban da nhan roi </div>' ;
                exit();
            }      
            
            //check userid
            if(!empty($conf['UserId'])&& $uId != $conf['UserId'])
            {
                echo '<div align="center" style="color: red;">code ko ranh cho ban</div>' ;
                exit();
            }
        }
        
        echo '<div align="center" style="color: red;">code hop le </div>' ;
    }
?>
