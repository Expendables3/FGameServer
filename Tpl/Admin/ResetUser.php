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
      </ul>
    </div>      
    <div id="center-column">    
       <form id="form2" name="form2" method="post" action="?mod=Index&act=ResetUser">
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
                <?php if($block == 1){?>
                <td width="60%">
                    <input type="submit" name="ResetData" value="ResetData" />
                </td>
                <?php }elseif($block == 2){?>
                <td width="60%">
                    <label>hay click Ok de xac nhan</label><br>
                    <input type="submit" name="Ok" value="Ok" />
                    <input type="reset" name="Submit" value="Cancel" />
                </td>
                <?php }?>
              </tr>
              </table>
            </form>
     </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>