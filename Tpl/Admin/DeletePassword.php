<?php
include TPL_DIR . "/head.php";
?>
<script language="javascript">
</script>

<div id="middle">
    <div id="left-column">
      <h3>GM tool</h3>
      <ul class="nav">
      </ul>
      </div>      
    <div id="center-column">
    <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>    
        <h3>Delete Password</h3>
        <?php
            if($block == 1){
        ?>
            <form id="form1" name="form1" method="post" action="?mod=Index&act=DeletePassword">
                UID &nbsp; <input type="text" name="uId"/>
                <input type = "submit" name="uIdSubmitted" value="Submit"/>            
            </form>
        <?php
            }elseif($block == 2){
        ?>
            Delete Password for User: <?php echo $uId ;?>
            <form id="form2" name="form2" method="post" action="?mod=Index&act=DeletePassword">   
           <?php
                    echo '<input type="hidden" name="uIdFinal" value="'.$uId.'" />' ;                
           ?>
           <br/>
           <br/>
           <span style="margin-left: 538px;">
                  <input type="submit" name="submitFinal" value="Final" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                  <input type="submit" name="cancel" value="Cancel" />  
          </span> 
      <?php
            }elseif($block == 3){
                echo $message;
            }
        ?>
    </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>