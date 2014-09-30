<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Game VMMS</title>
<link rel="stylesheet" rev="stylesheet" href="<?php echo $conf['cssdir'] ?>gamepage1.css" type="text/css" media="screen"/>
<link href="<?php echo $conf['jsdir'] ?>facebox/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
<link href="<?php echo $conf['cssdir']?>mainPage.css" media="screen" rel="stylesheet" type="text/css"/>
<?php 
  Common::loadService('Admin') ;

  $objectAdmin = new Admin();
  $aa = $objectAdmin->result_text();
?>

  <?php 
              
              // banner
              $bannerName = $objectAdmin->result_BannerName();
              $banner = $conf['imgdir'].$bannerName;

              $TextLink = '';
              $Link_text = '';              
              if(!empty($aa))
              {
                 $TextLink = $aa['content'];
                 $Link_text = $aa['Link'];  
              }
                    
?>

<script src="<?php echo $conf['jsdir'] ?>swfobject/swfobject.js" type="text/javascript"></script>
<script src="<?php echo $conf['jsdir'] ?>jquery.min.js" type="text/javascript"></script> 
<script src="<?php echo $conf['jsdir'] ?>facebox/facebox.js" type="text/javascript"></script> 
<script src="<?php echo $conf['jsdir'] ?>payment.js" type="text/javascript"></script>

<link rel="stylesheet" href="<?php echo $conf['cssdir']?>dhtmlwindow.css" type="text/css"/>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>dhtmlwindow.js"></script>
<script type="text/javascript">
var wNapXu=null;
zmXCall.register('callbackPayment',function(data){
	console.log("close payment");
	if(wNapXu!=null)
	{
		wNapXu.close();
	}
	console.log("close payment end");
});
function openVZM(msg){
	wNapXu=dhtmlwindow.open('vizingme','iframe', msg+'&_v=3',"Đổi Ví Zing Me cho game myFish","width=810px,height=655px,resize=0,scrolling=1,center=1");
	wNapXu.isResize(false);
	wNapXu.moveTo(12, 165);
	wNapXu.onclose=function(){
			//swfobject.getObjectById("mainFlash").SendToFlash('updateG');
			hideGuiPay();
			return true;
		}
}

function payment(nickname)
{
    //showPaymentDialog('https://quickpay.zing.vn/client/index.html?pid=75&acc='+nickname+',540,232');
	$("#divBlockPayment").css("visibility","visible");
	//$("#iframePay").attr("src", "../Tpl/Index/napthe.html");
	$("#iframePay").attr("src", 'https://quickpay.zing.vn/client/index.html?pid=75&acc='+nickname+'$serid=1');
}

function showZingWallet(link)
{
    //$("#divZingWallet").css("visibility","visible");
    //$("#iframeZingWallet").css("visibility","visible");
    //$("#iframeZingWallet").attr("src", link);
	console.log("loi trc");
	openVZM(link);
	console.log("loi sau");
}

function hideGuiPay()
{
	$("#divBlockPayment").css("visibility","hidden");
	var mainFlash = getMovieById("mainFlash");
	mainFlash.updateG();
}

function updateG()
{
    var mainFlash = getMovieById("mainFlash");
    mainFlash.updateG();
}
function enableButton()
{
    var mainFlash = getMovieById("mainFlash");
    mainFlash.enableButton();
}

function visibleAllLayer(visible)
{
	var mainFlash = getMovieById("mainFlash");
	if(visible == true)
	{
		mainFlash.showAllLayer();
	}
	else
	{
		mainFlash.hideAllLayer();
	}
}

function napG()
{
    var mainFlash = getMovieById("mainFlash");
    mainFlash.napG();
} 

function reRun()
{
    var mainFlash = getMovieById("mainFlash");
    mainFlash.reRun();
} 

function showGuiCongrat(champion)
{
    var mainFlash = getMovieById("mainFlash");
    mainFlash.showGuiCongrat(champion);
}

function tournamentFee(payType, price)
{
	var mainFlash = getMovieById("mainFlash");
	mainFlash.tournamentFee(payType, price);
}

function getMovieById(id){
	if(navigator.appName.indexOf("Microsoft") != -1)
	{
		return window[id];
	}
	else
	{
		return document[id];
	}
}
</script>
 
<?php 
	echo '<script type="text/javascript" src="http://static.me.zing.vn/v3/js/zm.xcall-1.12.min.js"></script>';
	echo '<script type="text/javascript" src="http://static.me.zing.vn/feeddialog/js/feed-dialog-1.01.js"></script>';
?>

<script type="text/javascript"> 
            try
            {
                var pHost = parent.window.location.host;
                var useplatform = "<?php echo  $conf['platform'] ?>" ;
                if(useplatform != '')
                if(pHost && pHost != useplatform )
                    window.location.href = "<?php echo  $conf['applink'] ?>";
            }
            catch(e)
            {
            }
    

 var url_static = "<?php echo  $conf['flashDir'] ; ?>" ;
 var url_gateway= "<?php echo  $conf['domain']; ?>" ;
 var notice= "<?php echo  $notice ; ?>" ; 
 var banner= "<?php echo  $banner ; ?>" ;
 var uId= "<?php echo  Controller::$uId ; ?>" ;

 function viewPop()
  {

    var str = "";
    str+= '<span style="color:#466DA4;">Mọi thắc mắc xin liên hệ: <br/>';
    str+= 'Hotline : <span style="color:red">1900561558';
    str+= '&nbsp;&nbsp;&nbsp;Website : <a href="http://www.hotro.zing.vn" target="_blank" class="zf_link">hotro.zing.vn</a>&nbsp;&nbsp;Diễn đàn game: <a href="http://diendan.zing.vn/myfish" target="_blank" class="zf_link">myFish</a><br/>';
    str+= 'Hội Nhóm : <a href="http://me.zing.vn/apps/group?params=groupwall/317941" target="_blank" class="zf_link">myFish GSN</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/323492" target="_blank" class="zf_link">Fanclub</a>,';    
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/323496" target="_blank" class="zf_link">Teenclub</a>';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/268279" target="_blank" class="zf_link">My love</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/323897" target="_blank" class="zf_link">myFish NDTT</a>,';    
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/261357" target="_blank" class="zf_link">OffZFarmHN</a>';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/308920" target="_blank" class="zf_link">LukLakHN</a>,';
    
    str+= '<a href=": http://me.zing.vn/apps/group?params=groupwall/325992" target="_blank" class="zf_link">myFish – VIP </a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/328294" target="_blank" class="zf_link">myFish Lovely</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/329976" target="_blank" class="zf_link">myFish – Super Star</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/330576" target="_blank" class="zf_link">myFish – lai ca´Pro</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/285659" target="_blank" class="zf_link">nho´m HNZF</a>,&nbsp;&nbsp;';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/331040" target="_blank" class="zf_link">FC Zing Farm Ða` Na~ng</a>,';
    str+= '<a href=": http://me.zing.vn/apps/group?params=groupwall/261354" target="_blank" class="zf_link">FC Zing Farm Sa`i Go`n</a>,';
    str+= '<a href="http://me.zing.vn/apps/group?params=groupwall/329030" target="_blank" class="zf_link">myCaro – Vip Fansite</a>,';
    
      jQuery.facebox(str);

      //window.open(str);

 }
 
 function getDialog_ffs(PubKey, SignKey, ActionId, UserIdTo, ObjectId, AttachName, AttachHef, AttachCaption, AttachDes, MediaType, MediaImage, MediaSrc, ActLinkText, ActLinkHref, TplId, Suggestion) 
 {
    zmf.ui(
        {
        pub_key:PubKey,
        sign_key:SignKey,
        action_id:ActionId,
        uid_to: UserIdTo,
        object_id: ObjectId,
        attach_name: AttachName,
        attach_href: AttachHef,
        attach_caption: AttachCaption,
        attach_des: AttachDes,
        media_type:MediaType,
        media_img:MediaImage,
        media_src:MediaSrc,
        actlink_text:ActLinkText,
        actlink_href:ActLinkHref,
        tpl_id:TplId,
        suggestion: Suggestion
        });
  }

 function redirect()
 {
    window.location.href="http://login.me.zing.vn/login/logout";
 }
 
</script>

<script type="text/javascript">

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() 
  {
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];
   }
  }
   
function showMe()
{
        var imgPath = new String();
        imgPath = document.getElementById("div1").style.backgroundImage;
  
        if(imgPath == 'url("<?php echo $conf['imgdir']?>newImage/text.png")')
        {
            document.getElementById("div1").style.backgroundImage = "url(<?php echo $conf['imgdir']?>newImage/text_over.png)";
        }                                                                           
        else
        {
            document.getElementById("div1").style.backgroundImage = "url(<?php echo $conf['imgdir']?>newImage/text.png)";
        } 
}

</script>
<!--
<link rel='stylesheet' type='text/css' href='http://banner.static.game.zing.vn/library/G6BannerStyle.css?ver=2.2'/>
<script type=text/javascript src="http://banner.static.game.zing.vn/library/SliderEngine.js?ver=2.2"></script>
-->
</head>

<script type="text/javascript"> 
	zmXCall.callParent('resize', {height:900, id:"fish"});
</script>
	
<body style="margin: 0px;">
<div style="top:0px ; left:0px;">
<!--<div class="banner" style="margin:0px 0px 2px 0px; "> 
	<div class="wrapper"> 
		<ul> 
		</ul>
	</div>
	<a class="button next"/></a>
	<a class="button prev"/></a>
</div>-->
<!--<iframe src="http://g6-banner.apps.zing.vn/g6-banner.html?game=fish" width="866" height="54" frameborder="0"></iframe>-->
</div>
<table id="Table_01" width="810"  border="0" cellpadding="0" cellspacing="0" valign="top" align="center">
<tr>
        <td valign="top"  colspan="8">
          <!--<div id="myBanner" style="visibility: visible">   -->
        </td>
 </tr>
    <tr>
        <td>
            <!--<img src='<?php echo $conf['imgdir']?>newImage/logo.png' width="125" height="46" alt=""></td>-->
<!--
<td><a href="https://pay.zing.vn/zingxu/doizingxu.zfish.html" target="_blank" onMouseOver="MM_swapImage('nap g','','<?php echo $conf['imgdir']?>newImage/nap-g_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/nap-g.png' name="nap g" width="73" height="46" border="0" title="N&#7841;p G"></a></td>
<td><a href="http://diendan.zing.vn/vng/forumdisplay.php?f=2340" target="_blank" onMouseOver="MM_swapImage('diendan','','<?php echo $conf['imgdir']?>newImage/diendan_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/diendan.png' name="diendan" width="75" height="46" border="0" title="Di&#7877;n &#273;&agrave;n"></a></td>
<td><a href="http://diendan.zing.vn/vng/forumdisplay.php?f=2347" target="_blank" onMouseOver="MM_swapImage('thongbao','','<?php echo $conf['imgdir']?>newImage/thongbao_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/thongbao.png' name="thongbao" width="80" height="46" border="0" title="Th&ocirc;ng b&aacute;o"></a></td>
<td><a href="https://hotro1.zing.vn/homepage/index.191.html#tabs10191" target="_blank" onMouseOver="MM_swapImage('hotro','','<?php echo $conf['imgdir']?>newImage/hotro_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/hotro.png' name="hotro" width="74" height="46" border="0" title="H&#7895; tr&#7907;"></a></td>
<td><a href="http://me.zing.vn/fish_gsn" target="_blank" onMouseOver="MM_swapImage('chanhot','','<?php echo $conf['imgdir']?>newImage/chanhot_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/chanhot.png' name="chanhot" width="126" height="46" border="0" title="Th&#259;m Chanh &#7898;t"></a></td>
<td><a href="http://me.zing.vn/apps/myplay?_src=m" target="_blank" onMouseOver="MM_swapImage('myplay','','<?php echo $conf['imgdir']?>newImage/myplay_over1.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/myplay1.png' name="myplay" width="129" height="46" border="0" title="myPlay"></a></td>
<td><a href="http://fish.apps.zing.vn/web/hit.php?site=myfish.topbar.myfarm&link=http://me.zing.vn/apps/myfarm" target="_blank" onMouseOver="MM_swapImage('myfarm','','<?php echo $conf['imgdir']?>newImage/myfarm_over.png',1)" onMouseOut="MM_swapImgRestore()"><img src='<?php echo $conf['imgdir']?>newImage/myfarm.png' name="myfarm" width="128" height="46" border="0" title="myfarm"></a></td>
  -->
  </tr>
    <tr>
	<!--
        <td colspan="8">
            <img src='<?php echo $conf['imgdir']?>newImage/myfish-topbar_09.png' width="810" height="3" alt=""></td>
			-->
    </tr>
    <tr>
        <td colspan="8" align="center">
            <a id="newsLink" href="<?php echo $Link_text ; ?>" target="_blank" style="text-decoration: none; color: #5f9ea0;">
                <!--<div style="width:810px; height:26px; background-color: #d4dee8; cursor: pointer; padding-top: 0px;"><span style="font-size: 16px; font-family:'Times New Roman'; font-weight: bold;" id="newsText"><?php echo $TextLink ; ?></span></div>-->
            </a>
        </td>
    </tr>
 <tr>        
  <td align="center" valign="bottom" colspan="8">
        <?php if($notice) 
          { ?>
            <div id="myContent" style="height:624px; width: 810px; background-image: url(<?php echo $conf['imgdir']?>notice.jpeg); position:relative; float:left; background-repeat:no-repeat; border: 1px solid #000000; display: block;">
            <div style="position:absolute; top: 520px; margin: 0px; width: 100%;">
            <div style="height:86px; width:226px;  float:left; cursor: pointer; margin-left: 150px;"><a href="http://me.zing.vn/apps/zf" target="_blank"><img src="<?php echo $conf['imgdir'] ?>1.png" style="border:0;" /></a></div>
            <div style="height:86px; width:226px;  float:right; cursor: pointer; margin-right: 150px;"><a href="http://me.zing.vn/fish_gsn/profile" target="_blank"><img src="<?php echo $conf['imgdir'] ?>2.png" style="border:0;" /></a></div>
            </div>
            
         <?php }
          else
          {  ?>
             <div id="myContent"></div>
         <?php };   
        ?>
        
        </div>
  </td> 
</tr>
</table>
  <script type="text/javascript">
        var flashVars = {};
        var strHref = window.location.href;
        if (strHref.indexOf("?") > -1)
        {
            var strQueryString = strHref.substr(strHref.indexOf("?")+1);
            var aQueryString = strQueryString.split("&");
            for (var iParam = 0; iParam < aQueryString.length; iParam++) 
            {
                var aParam = aQueryString[iParam].split("=");
                flashVars[aParam[0]] = aParam[1];
            }
        }
        
        if(flashVars['sign_user'] != null && (flashVars['sign_user'] != uId)) 
            redirect();
        
        var params = {
            wmode: "transparent"
        };
        swfobject.embedSWF(banner, "myBanner", "810", "110", "10.0.0", "expressInstall.swf", 0, params, {id:"banner"});
         
        var params = {

            menu: "false",

            scale: "noscale",

            allowFullscreen: "true",

            allowScriptAccess: "always",

            bgcolor: "#000000" ,
            wmode: "window" 
        };
        var attributes = {
            id:"mainFlash"
        };
        //config param

        var strParams = "configUrl=" + url_static ;   

        //gateway & version

        strParams += "&gatewayUrl=" + url_gateway ;
        strParams += "&version=" + "<?php echo $conf['version'] ?>" ;
        strParams += "&versionJson=" + "<?php echo $conf['versionJson'] ?>" ;
        strParams += "&versionLocalization=" + "<?php echo $conf['versionLocalization'] ?>" ;
        strParams += "&tournamentVersion=" + "<?php echo $conf['tournamentVersion'] ?>" ;
        strParams += "&socketIp=" + "<?php echo $conf['socketIp'] ?>" ;
        strParams += "&socketPort=" + "<?php echo $conf['socketPort'] ?>" ;
        strParams += "&codeID=" + "<?php echo urlencode($_GET['code']) ;?>" ;
        
        
        flashFile = "myFish" + "<?php echo $conf['version'] ?>" +".swf?" ;

        //if(!notice)
        {
            swfobject.embedSWF(url_static + flashFile +strParams, "myContent", "810", "624", "10.0.0", "expressInstall.swf", flashVars, params, attributes);
        }
        
</script>

<center>
	<div id="divBlockPayment" style="width:567px; height:0px; position:relative; left:0; top:-534; visibility:hidden;">
			<iframe name = 'iframePay' id = 'iframePay' allowTransparency="true" style='background-color:transparentcy;' frameborder = '0' scrolling = 'no' width = '565px' height = '265px'></iframe>
	</div>
</center>
<script type="text/javascript" src="TienThanhTrace.js"></script>
<script type="text/javascript">
window.onbeforeunload = function()
{
	return "";
}
</script>
<!-- Google Analytics -->
<!-- <script type="text/javascript" src="<?php echo $conf['jsdir'] ?>WsaGa.js"></script>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>ga-all.js"></script> -->
</body>
</html>