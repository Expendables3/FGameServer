<?php 
error_reporting(E_ALL & ~E_NOTICE); //=> show all
define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
$conf = include(CONFIG_DIR.'/System/Config.php');

$bannerName = 'banner_caro1.swf';
$bannerName = $conf['imgdir'].$bannerName;
?>﻿

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>myFish</title>

<link rel="stylesheet" href="<?php echo $conf['cssdir']?>dhtmlwindow.css" type="text/css"/>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>dhtmlwindow.js"></script>
<script src="<?php echo $conf['jsdir'] ?>swfobject/swfobject.js" type="text/javascript"></script>
<script src="<?php echo $conf['jsdir'] ?>jquery.min.js" type="text/javascript"></script> 
<link rel='stylesheet' type='text/css' href='http://banner.static.game.zing.vn/library/G6BannerStyle.css?ver=2.4'/>
<script type=text/javascript src="http://banner.static.game.zing.vn/library/SliderEngine.js?ver=2.4"></script>
<script type="text/javascript">
    var banner= "<?php echo  $bannerName ; ?>" ;          
    var params = {
        wmode: "transparent"
    };
</script>
</head>
<body style="margin: 0px;">
<div style="top:0;left:0px;" align="center">
    <div class="banner" style="margin:0px 0px 2px 0px; "> 
        <div class="wrapper"> 
            <ul>
            </ul>
        </div>
        <a class="button next"/></a>
        <a class="button prev"/></a>
    </div>
</div>
<table id="Table_01" width="810"  border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
    <tr>
        <td valign="top"  colspan="8">
          <div id="myBanner" style="visibility: visible"></div>   
        </td>
    </tr>
</table>

<script type="text/javascript">
swfobject.embedSWF(banner, "myBanner", "810", "100", "10.0.0", "expressInstall.swf", 0, params, {id:"banner"});
</script>
</body>
</html>