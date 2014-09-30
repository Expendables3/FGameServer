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
        <li><a href="?mod=Index&act=ShowOccupyBoard">Check UserRank</a></li>
      </ul>
    </div>
    
    <div id="center-column">
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>    
        <h3>Check User Rank</h3>
        <?php
            if($block == 1){
        ?>
        - Get User Rank on Date GiftBoard <br>
        <br>
        <form id="form1" name="form1" method="post" action="?mod=Index&act=ShowOccupyBoard">   
        
            <b>Date</b> [2012-11-01] <input type="text" name="DateBoard"><br>
            <b>Uid</b> <input type="text" name="UidBoard"><br>
            <br>
            <i>Empty as Current Date</i><br>            
        <span style="margin-left: 400px;"> 
            <input type="submit" name="getRank" value="Get Rank" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <input type="submit" name="cancel" value="Cancel" />  
        </span> 
        </form>
        <?php
            }elseif($block == 2){
               echo "Date: {$strDate} <br>";
               echo "Rank in Cache: {$uRankCache} <br>";
               echo "Rank in Bak: {$uRankBak} <br>";
            }
            elseif($block == 3)
            {
                echo $message;
            }
        ?>
    </div>
</div>
<?php
    include TPL_DIR . "/foot.php";
?>
