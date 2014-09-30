<?php
include TPL_DIR . "/head.php";
?>
<div id="middle">
    <div id="left-column">
      <h3>GM tool</h3>
      <ul class="nav">
        <li><a href="?mod=Index&act=gmtool">Xem thông tin user</a></li>
        <li><a href="?mod=Index&act=gmtools">Lấy danh sách user</a></li>
      </ul>
</div>      
    <div id="center-column">  
    <?php if($block == 1) {?>
    <form id="form1" name="form1" method="post" action="?mod=Index&act=gmtools">
    <table width="50%" border="0" cellspacing="0" cellpadding="0">
    <tr><td colspan="3" style="color:red"><?php echo $mess;?></td></tr>
	  <tr>
	    <td width="20%" height="23">Zing ID </td>
	    <td width="60%"><textarea rows="3"  style="width:400px" name="uids"></textarea> <td>
	    <td width="20%"><input type="submit" name="submit" value="Xem thông tin" /></td>
	  </tr>
	  </table>
	</form>
	  <?php }else if($block ==2){ ?>
	  <table width="100%" border="0" cellspacing="0" cellpadding="2">
	  <?php foreach($userDatas as $key=>$value){?>
	  <tr>
	  <td style="border-bottom: solid #cccccc 1px"><?php echo $key;?></td>
	  <td style="border-bottom: solid #cccccc 1px">
	  	Username		:	<?php echo $value['username'];?><br>
	  	Tên hiển thị	:	<?php echo $value['displayname'];?><br>
	  </td>
	  </tr>
	  <?php }?>
	  </table>
	  <?php }?>
</div>
  <?php
include TPL_DIR . "/foot.php";
?>