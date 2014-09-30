<?php
include TPL_DIR . "/head.php";
?>

<html>

  <head>
  </head>
  
  <body>
  <div id="middle" >
    <form id="sysMailForm" name="sysMailForm" method="post" action="?mod=Index&act=updateEquipment">
      <div align="center">
        <div>danh sach UserId</div>
        <div><textarea cols="30" rows="5" name="UserList"></textarea></div>
        <div>
            <input type="submit" name="Update" value="Repair">
            <input type="reset" name="Cancel" value="Cancel">
        </div>
      </div>  
    </form>
    <div align="center">
    <?php
        if(empty($notify))
            echo 'Update success !';
        else
        {
            echo '<pre>';
            var_dump($notify);
            echo '</pre>';
        }   
    ?>
    </div>
  </div> 
  </body>


</html>
<?php
include TPL_DIR . "/foot.php";
?>