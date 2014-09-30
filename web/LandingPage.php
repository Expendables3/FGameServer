<?php 
    //error_reporting(0); 
    error_reporting(E_ALL & ~E_NOTICE); //=> show all
    ini_set('display_errors', 0);
    ini_set('error_log', '../log/error.log');

    header('Cache-Control: no-cache, must-revalidate, max-age=0',true);
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT',true);
    header('Pragma: no-cache',true);
    define( "IN_INU" , true );

    define( "ROOT_DIR" , '..' );
    define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
    define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
    define( "SER_DIR" , ROOT_DIR ."/Service" );
    define( "LIB_DIR" , ROOT_DIR ."/libs" );
    define( "TPL_DIR" , ROOT_DIR ."/Tpl/Index");

    require LIB_DIR.'/Common.php';
    require LIB_DIR.'/ZingApi.php';
    require LIB_DIR.'/dal/DataProvider.php';
    
    $conf = include('../Config/System/Config.php') ;
    
    Controller::init();
       
    Zf_log::write_act_log_new(Controller::$uId,0,0,'loadLandingPage');  
     
    $keyimage = 'MyFish_image_landingpage' ;  
    $return_1 =  DataProvider::get($keyimage,'designTool');    
    foreach($return_1 as $Order => $conten)
    {
        if(empty($conten)) continue ;
        $Links = "link_".$Order ;
        $Thums = "thum".$Order ;
        $$Links = $conten['Link'];
        $$Thums = $conten['ImageName'];
    }
    
    $keytext = 'MyFish_text_landingpage' ;  
    $return_2 =  DataProvider::get($keytext,'designTool');   
    
    foreach($return_2 as $Order1 => $conten2)
    {
        if(empty($conten2)) continue ;
        $TextLink = "TextLink".$Order1 ;
        $Text = "Text".$Order1 ;
        $Day = "Day".$Order1 ;
        $$TextLink = $conten2['Link'];
        $$Text = $conten2['TextName'];
        $$Day = $conten2['Day'];
    }
  
   /* $link_1 = 'http://me.zing.vn/apps/blog?params=fish_gsn/blog/detail/id/750422986';
    $link_2 = 'http://me.zing.vn/apps/blog?params=fish_gsn/blog/detail/id/750423786';
    $link_3 = 'http://diendan.zing.vn/showthread.php/3230599-Lien-dau-tranh-hang.html';
    $link_4 = 'http://me.zing.vn/apps/blog?params=/fish_gsn/blog/detail/id/750278442?from=meblog';
    
    $link_5 = 'http://me.zing.vn/apps/blog?params=fish_gsn/blog/detail/id/750278964';
    $link_6 = 'http://me.zing.vn/apps/blog?params=fish_gsn/blog/detail/id/750279022';
    
    $link_7 = 'http://me.zing.vn/apps/blog?params=fish_gsn/blog/detail/id/750357082';*/
    
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="<?php echo $conf['cssdir']?>mainsite.css" type="text/css" rel="stylesheet" />
<link href="<?php echo $conf['cssdir']?>style.css" rel="stylesheet" />
<link href="<?php echo $conf['cssdir']?>banner-event.css" rel="stylesheet" />
<link href="<?php echo $conf['cssdir']?>news.css" rel="stylesheet" />
<title>Untitled Document</title>
<script type="text/javascript" src="<?php echo $conf['jsdir']?>mainsite.js"></script>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>jquery.ui.fadegallery.js"></script>
<script type="text/javascript" src="<?php echo $conf['jsdir'] ?>common.js"></script>
</head>

<body>
    <div class="Wrapper">
        <div class="Main">
            <h1><a href="http://fish.apps.zing.vn/web/JoinGame.php" class="Logo" title="My Fish">My Fish</a></h1>
            <div class="BoxFan">
                <a href="http://me.zing.vn/h/fish_gsn" target="_blank" class="Face" title="Fanpage">Fanpage</a>
                <a href="https://hotro1.zing.vn/homepage/index.191.html#tabs10191" target="_blank" class="HoTro" title="Hỗ trợ">Hỗ trợ</a>
            </div>
            <a href="http://fish.apps.zing.vn/web/JoinGame.php" class="ChoiNgay" title="Chơi ngay">Chơi ngay</a>
            <div id="boxEvent">
                <p class="Corner01">corner 1</p>
                <p class="Corner02">corner 2</p>
                <p class="Corner03">corner 3</p>
                <p class="Corner04">corner 4</p>
                <ul id="img">
                    <li class="ActiveBanner"><a href="<?php echo $link_1 ;?>" target="_blank" title=""><img src="<?php echo $conf['imgdir'].$thum1 ?>" width="290" height="210" alt="" title="" /></a></li>
                    <li><a href="<?php echo $link_2 ;?>" target="_blank" title=""><img src="<?php echo $conf['imgdir'].$thum2?>" width="290" height="210" alt="" title="" /></a></li>
                    <li><a href="<?php echo $link_3 ;?>" target="_blank" title=""><img src="<?php echo $conf['imgdir'].$thum3?>" width="290" height="210" alt="" title="" /></a></li>
                    <li><a href="<?php echo $link_4 ;?>" target="_blank" title=""><img src="<?php echo $conf['imgdir'].$thum4?>" width="290" height="210" alt="" title="" /></a></li>
                    <li><a href="<?php echo $link_5 ;?>" target="_blank" title=""><img src="<?php echo $conf['imgdir'].$thum5?>" width="290" height="210" alt="" title="" /></a></li>
                </ul>
                <ul id="imgControl">
                    <li id="item1"><a href="#" title="">1</a></li>
                    <li id="item2"><a href="#" title="">2</a></li>
                    <li id="item3"><a href="#" title="">3</a></li>
                    <li id="item4"><a href="#" title="">4</a></li>
                    <li id="item5"><a href="#" title="">5</a></li>
                </ul>
            </div>
            <div class="BlockNews">
                <h2 class="TinTuc" title="Tin tức"><a href="http://me.zing.vn/bi/fish_gsn/blog/showinfo?v=blog" target="_blank" title="Tin tức">Tin tức</a></h2>
                <ul class="ListNews">
        <!--First news-->
                <li><a href="<?php echo $TextLink1;?>" target="_blank" class="Hot" title=""><span class="Date"><?php echo "[ $Day1 ]"; ?></span><?php echo $Text1;?></a></li>
                <li><a href="<?php echo $TextLink2;?>" target="_blank" title=""><span class="Date"><?php echo "[ $Day2 ]"; ?></span><?php echo $Text2;?></a></li>
                <!--End First news--> 
                <li><a href="<?php echo $TextLink3;?>" target="_blank" title=""><span class="Date"><?php echo "[ $Day3 ]"; ?></span><?php echo $Text3;?></a></li>
                <li><a href="<?php echo $TextLink4;?>" target="_blank" title=""><span class="Date"><?php echo "[ $Day4 ]"; ?></span><?php echo $Text4;?></a></li>
                <li><a href="<?php echo $TextLink5;?>" target="_blank" class="Hot" title=""><span class="Date"><?php echo "[ $Day5 ]"; ?></span><?php echo $Text5;?></a></li>
                <li><a href="<?php echo $TextLink6;?>" target="_blank" title=""><span class="Date"><?php echo "[ $Day6 ]"; ?></span><?php echo $Text6;?></a></li>
                <li><a href="<?php echo $TextLink7;?>" target="_blank" title=""><span class="Date"><?php echo "[ $Day7 ]"; ?></span><?php echo $Text7;?></a></li>
            </ul>
    
            </div>
        </div>
        
    </div>
</body>
</html>
