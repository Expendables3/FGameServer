<?php
include TPL_DIR . "/head.php";
?>
<div id="gift" align="center">
	<div class="ms_box">
	<form action="?mod=Index&act=sendSu" method="post" name="frmCaptcha" id="frmCaptcha">
		<div class="ms_box_top"></div>
		<div class="ms_box_center1">
			<h3><img alt="Góp ý" src="<?php echo $conf['imgdir'] ?>ms_icon.gif" border="0" align="middle">Góp ý</h3>
			
			<div class="suggestions_left">Email của bạn</div>
			<div class="suggestions_right">
				<input type="text" name="email"  style="width: 200px" maxlength="100" class="required email"></input>
			</div>	
			<div class="suggestions_left">Tiêu đề</div>
			<div class="suggestions_right">
				<input type="text" name="title" class="required" maxlength="100" style="width: 200px"></input>
			</div>	            		
			<div class="suggestions_left1" style="height:107px;">Nội dung góp ý</div>
			<div class="suggestions_right1" style="height:112px;">
				<textarea rows="5" cols="22" id="content" class="required" name="content"></textarea>
			</div>			
			<div class="suggestions_left"></div>
			<div class="suggestions_right"><input type="submit" value="Gửi" onclick="return getParam(document.frmCaptcha)"></input><input type="reset" value="Viết lại"></input></div>
		</div>
		<div class="ms_box_bottom"></div>
		</form>
	</div>
	<div></div>	
</div>
<?php
include TPL_DIR . "/foot.php";
?>