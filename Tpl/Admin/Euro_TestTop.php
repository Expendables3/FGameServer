<?php
  include TPL_DIR . "/head.php";
?>
<script language="javascript">

</script>
<div id="middle">
    <div id="left-column">
      <h3>Euro Event</h3>
      <ul class="nav">
        <li><a href="?mod=Index&act=euro_adminEvent">Lich thi dau</a></li>
      </ul>
    </div>      
    <div id="center-column">
    <?php if($block == 1) {?>
       <form id="form1" name="form1" method="post" action="?mod=Index&act=euro_testtop">
       Hay parselog cho file log gan nhat vua tao ra.<br/>
       Flush all data about top <br>
       
       <input type="submit" name="flush" value="Flush Top" />
       </form>
    <?php } else if($block == 2) {?>
        <?php echo $message; ?>
        Bat dau 1 qui trinh test top moi. <br/>
        ------------------
        Lich thi dau <br/>
        Change date log -> bet matches -> parselog -> update result -> show top <br/>
        Change date log -> bet matches -> parselog -> update result -> show top <br/>
        ...
        Flush data
        ------------------
        ...
    <?php } else if($block == 3) {?> 
        <?php echo $message; ?>
    <?php }?>
    </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>