<?php
include TPL_DIR . "/head.php";
?>
<div id="middle">
    <div id="left-column">
      <h3>GM tool</h3>
      <ul class="nav">
        <li><a href="?mod=Index&act=AddItem">Them Item vao User</a></li>
        <li><a href="?mod=Index&act=DeleteItem">Xoa Item cua User</a></li>
        <li><a href="?mod=Index&act=EditItem">Sua Item cho User</a></li>
        <li><a href="?mod=Index&act=DeletePassword">Xóa Password</a></li>
        <li><a href="?mod=Index&act=ResetUser">Xóa UserData</a></li>     
        <li><a href="?mod=Index&act=ResetWorldMap">Reset WorldMap</a></li>  
      </ul>
    </div>      
    <div id="center-column">   
    
    <?php if($block == 1){?>
       <form id="form2" name="form2" method="post" action="?mod=Index&act=UpdateStore">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="20%" height="23">Zing ID </td>
                <td width="60%"><input type="text" name="uid" /></td>  
                <td style="color: red"><?php echo($mess) ; ?>
                </td>
              </tr>
              <tr>
                <td width="20%" height="23">&nbsp;
                </td>
                <td width="60%">
                    <input type="submit" name="Update" value="ResetData" />
                </td>
              </tr>
              </table>
            </form>
     <?php }else{?> 
        <p style="color: red"><?php echo($mess) ; ?> </p>
     <?php }?> 
     </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>
?>
