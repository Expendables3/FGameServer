<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>myFish</title>
<link rel="stylesheet" rev="stylesheet" href="<?php echo $conf['cssdir'] ?>gift1.css" type="text/css" media="screen"/>
<link rel="stylesheet" rev="stylesheet" href="<?php echo $conf['cssdir'] ?>messagebox.css" type="text/css" media="screen"/>
<link rel="stylesheet" rev="stylesheet" href="<?php echo $conf['cssdir'] ?>event.css" type="text/css" media="screen"/>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>jquery.js" ></script>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>jquery.quicksearch.js" ></script>
<script src="<?php echo $conf['jsdir'] ?>facebox/facebox.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>jquery.validate.js" ></script>
<link href="<?php echo $conf['jsdir'] ?>facebox/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
<script src="<?php echo $conf['jsdir'] ?>swfobject/swfobject.js" type="text/javascript"></script>
<script type="text/javascript">
        document.domain = "zing.vn";
</script>
<script type="text/javascript"> 
  $(document).ready(function(){
    $("#frmCaptcha").validate();
  }); 
  function viewPop()
  {
	var str = "";
	str+= '<p style="font-size:11px;color:#466DA4;">Mọi thắc mắc về ZingFarm xin vui lòng liên hệ <br/>';
	str+= 'Hotline : <span style="color:red">1900561558 </span> <br/>';
	str+= 'Hoặc Website : <a href="http://www.hotro.zing.vn" target="_blank">hotro.zing.vn</a></p>';
  	jQuery.facebox(str);
  	//window.open(str);
  }
  </script>
</head>
<body>
<table id="Table_01" width="810"  border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
		<td valign="top">
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="810" height="110">
			  <param name="movie" value="<?php 
			  $num = rand(1,25);
			  $bannerName = "banner";
			  switch (true)
			  {
			  	case $num <= 25 :
			  		 $bannerName = "banner_fish";
			  		 break;
			  	case $num <= 50 :
			  		 $bannerName = "banner_xu_1";
			  		 break;
			  	case $num <= 100 :
			  		 $bannerName = "banner_ZF4";
			  		 break;
			  }
			  $banner = $conf['imgdir'].$bannerName.".swf";
			  echo $banner;
			  ?>" />
			  <param name="quality" value="high" />
			  <param name="allowScriptAccess" value="always">
			   <param name="wmode" value="transparent">
			  <embed src="<?php echo $banner;?>" wmode="transparent" quality="high" allowScriptAccess="always" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="810" height="110"></embed>
			</object>
			</td>
	</tr>
	<tr>		
		<td align="center" valign="top">	
		<div id="zfapps">
		<div class="zf_top">
		<div class="zf_top_link2"><a href="?mod=Index" class="zf_link" >myFish</a></div>
		<div class="zf_top_link2">Nạp G</div>
		<div class="zf_top_link2"><a href="http://me.zing.vn/apps/group?params=groupprofile/268279" class="zf_link"  target="_blank">Hội nhóm</a></div>
		<div class="zf_top_link2"><a href="http://me.zing.vn/apps/group?params=groupforum/268279" class="zf_link" target="_blank" >Diễn Đàn</a></div>
        <div class="zf_top_link2"><a href="http://me.zing.vn/fish_gsn/profile" target="_blank"  class="zf_link" >Chanh Ớt</a></div>
        <div class="zf_top_link2"><a href="#"  class="zf_link" onclick="return viewPop();" >Hỗ trợ</a></div>
		<div class="zf_top_link1"><img src="<?php echo $conf['imgdir'] ?>logo.png" width="132" height="30" alt=""></div>
		</div>		
