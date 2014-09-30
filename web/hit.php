<?php

define( "ROOT_DIR" , '..' );
define( "CONFIG_DIR" , ROOT_DIR . "/Config" );
define( "MOD_DIR" , ROOT_DIR ."/DomainModel" );
define( "SER_DIR" , ROOT_DIR ."/Service" );
define( "LIB_DIR" , ROOT_DIR ."/libs" );

require LIB_DIR.'/Common.php';

$site =    $_REQUEST['site'];
$link =    $_REQUEST['link'];

if(strlen($site)>0)
{   
    
    // log link soure 
    if($link != 'http://me.zing.vn/apps/fish') // chi log cho cac game khac , Fish se log trong Index.php
    {
        $site = str_replace("'",'',$site);
        $p = explode(".", $site);
        $p0 = isset($p[0])?$p[0]:0;
        $p1 = isset($p[1])?$p[1]:0;
        $p2 = isset($p[2])?$p[2]:0;
        $p3 = isset($p[3])?$p[3]:0;
        Zf_log::write_act_log_new(0,0,0,'SourceLink',0,0,0,0,$site,$p0,$p1,$p2,$p3);  
    }
    
    if(strpos($link,'?') == false)
    {
        header("Location: $link?site=$site");
    }
    else
    {
        header("Location: $link&site=$site");
    }
    
}
else
{
    header("Location: http://me.zing.vn/apps/fish");
  
}