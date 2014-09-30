<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="#" type="image/x-icon" />
		<title>Game VMMS</title>
		<link type="text/css" rel="stylesheet" href="http://img.zing.vn/eventgame/intro/general/css/mainsite.css" />
		<link type="text/css" rel="stylesheet" href="css/style.css"/>
		<link type="text/css" rel="stylesheet" href="css/jselect/jselect.css" />
		<link type="text/css" rel="stylesheet" href="css/jselect/jselect-theme.css" />
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="http://static.me.zing.vn/v3/js/zm.xcall-1.15.min.js"></script>
        <script type="text/javascript">
            function openFullScreen(_url)
            {
                var _width = screen.width;
                var _height = screen.height;                
                zmXCall.getTop(
                    function(resp){
                        _height = resp.height-38;
                        zmXCall.callParent('openFullFrame', {width:_width, height:_height, url:_url});
                    }
                )                
            }
            
            function getRequestParam()
            {
                strHref = window.location.href;
                var requestParam = '';
                if(strHref.indexOf("?") > -1)
                {
                    requestParam = strHref.substr(strHref.indexOf("?") + 1);
                }
                return requestParam;
            }
            
            function combineHref(url)
            {
                requestParam = getRequestParam();
                return url + '?' + requestParam;
            }
            
            function openFullScreenServer(servUrl)
            {
                href = combineHref(servUrl);
                openFullScreen(href);                
            }
            
            function openDefaultScreenServer(servUrl)
            {
                href = combineHref(servUrl);
                window.location.href = href;                
            }
            
        </script>
        
        <script type="text/javascript">
            // config Parm for Cum server            
            var newFishUrl = 'http://id.ohfish.zing.vn/web/index.php';					
            var myFishUrl = 'http://fish.apps.zing.vn/web/index_game.php';			
        </script>
        <script type="text/javascript">
            zmXCall.callParent('resize', {height:1000, id:"fish"});
        </script>
	</head>
	
	<body>
		<div class="Wrapper">
			<div class="Main">
				<h1><a class="Logo" href="#" target="_blank" title="Trở về trang chủ My Fish">Trở về trang chủ My Fish</a></h1>
				<ul class="MenuLeft">
					<li><a onclick="_gaq.push(['_trackEvent', 'Navigation', 'Nap G', 'IDlogin-ZingMe']);" class="Nav01" href="https://pay.zing.vn/zingxu/doizingxu.zfish.html" title="Nạp G"></a></li>
					<li><a onclick="_gaq.push(['_trackEvent', 'Navigation', 'Dien dan', 'IDlogin-ZingMe']);" class="Nav02" href="http://diendan.zing.vn/forumdisplay.php/2340-myFish.html" title="Diễn Đàn">Diễn Đàn</a></li>
					<li><a onclick="_gaq.push(['_trackEvent', 'Navigation', 'Ho tro', 'IDlogin-ZingMe']);" class="Nav03" href="https://hotro1.zing.vn/myfish/san-pham_191__0.html" title="Hỗ trợ">Hỗ trợ</a></li>
				</ul>
				<ul class="ChiaSe">
					<li><a onclick="_gaq.push(['_trackEvent', 'Navigation', 'Zing Me Fanpage', 'IDlogin-ZingMe']);" class="ZM" target="_blank" href="http://me.zing.vn/b/fish_gsn" title="ZingMe">ZingMe</a></li>
					<li><a onclick="_gaq.push(['_trackEvent', 'Navigation', 'FaceBook Fanpage', 'IDlogin-ZingMe']);" class="FB" target="_blank" href="http://www.facebook.com/MyFishNguChienMauLuaHon?fref=ts" title="Facebook">Facebook</a></li>
				</ul>
				<div class="Server">
					<p class="ServerNew"> <a title="Oh!Fish" href="javascript: openFullScreenServer(newFishUrl);"> Oh!Fish</a> </p>
					<p class="User">Chào <span class="UserInfo"><?php echo $_GET['username']; ?></span>, <a href="#" title="Thoát">thoát</a></p>
					<div class="ListServer">
						<h2>Máy chủ</h2>                        
						<ul class="TwoServer">
							<li><a class="Tot" href="javascript: openFullScreenServer(newFishUrl);" title="Oh!Fish">02.Oh!Fish</a></li>
							<li><a class="Day" href="javascript: openDefaultScreenServer(myFishUrl);" title="myFish">01.myFish</a></li>							
						</ul>
					</div>
				</div>
				<div class="BoxIframe">
					<iframe width="760px" height="260" scrolling="no" frameborder="0" allowTransparency="true" src="http://launcher.game.zing.vn/GS3/myfish-eventnew.html"></iframe>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="http://img.zing.vn/eventgame/intro/general/js/mainsite.js"></script> 
		<script type="text/javascript" src="http://img.zing.vn/eventgame/intro/general/call-function/tracking-idlogin.js"></script> 
		<script type="text/javascript" src="http://img.zing.vn/eventgame/intro/general/call-function/call-function.js"></script> 
		<script type="text/javascript" src="js/plugin/jfadegallery/fadegallery.js"></script> 
		<script type="text/javascript" src="js/plugins/tabs/tabs.js"></script> 
		<script type="text/javascript" src="js/popup.js"></script> 
		<script type="text/javascript" src="js/jselect/jselectHome.js"></script> 
		<script type="text/javascript" src="js/jselect/jselect.external.js"></script> 
		<script type="text/javascript" src="js/jselect/ga-idlogin-mfigsn.js"></script>
		<script type="text/javascript" src="js/common.js"></script>
	</body>
</html>
