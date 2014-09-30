<?php
    include TPL_DIR . "/head.php";
?>
<script language="javascript">

</script>
<div id="middle">
    <div id="left-column">
    <h3>GM tool</h3>
      <ul class="nav">
        <li class=<?php echo $this->class_tab3;?>><span><span><a href="?mod=Index&act=gmtool">Back GM tool</a></span></span></li> 
        <li><a href="?mod=Index&act=ResetOccupyBoard">Xóa OccupyBoard</a></li>
      </ul>
    </div>
    
    <div id="center-column">
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>    
        <h3>Reset OccupyBoard</h3>
        <?php
            if($block == 1){
        ?>
        - Xoa Bang hien tai <br>
        - Cho phep user bat dau join lai bang
        <form id="form2" name="form2" method="post" action="?mod=Index&act=ResetOccupyBoard">   
        <span style="margin-left: 538px;">
            <input type="submit" name="submitFinal" value="Final" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <input type="submit" name="cancel" value="Cancel" />  
        </span> 
        </form>
        <?php
            }elseif($block == 2){
                echo $message;
            }
        ?>
    </div>
</div>
<?php
    include TPL_DIR . "/foot.php";
?>