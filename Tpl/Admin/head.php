<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Quản lý ZingFish Pro</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" rev="stylesheet" href="<?php echo $this->config['cssdir_admin'] ?>all.css" type="text/css" media="screen"/>
<script type="text/javascript" src="<?php echo $this->config['jsdir'] ?>jquery.js" ></script>
<script type="text/javascript" src="<?php echo $this->config['jsdir'] ?>jquery.validate.js" ></script>
<script src="<?php echo $this->config['jsdir_admin'] ?>facebox/facebox.js" type="text/javascript"></script>
<link href="<?php echo $this->config['jsdir_admin'] ?>facebox/facebox.css" media="screen" rel="stylesheet" type="text/css"/>
</head>
<body>
<div style="float:right; margin-top: 20px; margin-right: 100px;">
    <form action="admin.php">
        <input type="submit" name="logout" value="LogOut"/>
    </form>
</div>
<div id="main">
  <div id="header"> <a href="<?php echo $this->url;?>" class="logo"><img src="<?php echo $this->config['imgdir'] ?>logo.png" width="101" height="29" alt="" /></a>
    <ul id="top-navigation">
      <li class=<?php echo $this->class_tab1;?> ><span><span><a href="<?php echo $this->url;?>">Trang chu</a></span></span></li>
      <li><span><span><a href="#">Quản lý maintenance</a></span></span></li>
      <li class=<?php echo $this->class_tab3;?>><span><span><a href="?mod=Index&act=gmtool">GM tool</a></span></span></li>
      <li class=<?php echo $this->class_tab4;?>><span><span><a href="?mod=Index&act=BannerManager">Banner Management</a></span></span></li>
      <li><span><span><a href="?mod=Index&act=design_index">Design</a></span></span></li>
      <li><span><span><a href="?mod=Index&act=systemMail">SystemMail</a></span></span></li>
      <li><span><span><a href="?mod=Index&act=updateEquipment">UpdateEquipment</a></span></span></li>
      <li><span><span><a href="?mod=Index&act=createItemCode">CreateItemCode</a></span></span></li>
      <li><span><span><a href="?mod=Index&act=decodeItemCode">DecodeItemCode</a></span></span></li>
      <!-- <li><span><span><a href="?mod=Index&act=euro_adminEvent">EventEuro</a></span></span></li> -->
      <!--<li class=<?php echo $this->class_tab7;?>><span><span><a href="?mod=Index&act=traogiai">Trao Giải</a></span></span></li> -->
    </ul>
  </div>
