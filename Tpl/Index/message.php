<style type="text/css">
.ms_box_center {
width:426px;
padding:0px;
}
</style>
<!--[if IE]>
<style type="text/css">
.ms_box_center {
width:427px;
}
</style>
<![EndIf]-->
<?php
$c = "select friend";
include TPL_DIR . "/head.php";
?>
<div id="gift" align="center">
	<div class="ms_box" style="padding-top:0px;">
		<div class="ms_box_top" style="height:4px; background-position:bottom;"></div>
		<div class="ms_box_center">
			<img  style="margin-left:10px;" alt="Thông báo" src="<?php echo $conf['imgdir'] ?>ms_icon.gif" border="0" align="middle"> <?php echo $mess ?>
			<div class="ms_bt" align="right"><input style="margin-right:10px" type="button" name="ok" value="Đồng ý" onclick="window.location='<?php echo $this->url ?>'"></div>
		</div>
		<div class="ms_box_bottom" style="vertical-align:top;height:4px; background-position: top;"></div>
	</div>
	<div></div>	
</div>
<?php
include TPL_DIR . "/foot.php";
?>