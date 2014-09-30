<?php
  include TPL_DIR . "/head.php";
?>
<script language="javascript">

</script>
<div id="middle">
    <div id="left-column">
      <h3>Euro Event</h3>
      <ul class="nav">
         <li><a href="?mod=Index&act=euro_modifyFixture">Cap nhat Thi dau</a></li>
        <li><a href="?mod=Index&act=euro_showTop">Show Top</a></li>
        <li><a href="?mod=Index&act=euro_updateResultAgain">Update Lai Ket qua</a></li> 
      </ul>
    </div>      
    <div id="center-column">
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>
        <h3>Top 10</h3>
        <table border="1" style="width: 700px; text-align: center;"> 
        <?php
            
            $fields = array('Order', 'Id', 'Name', 'Medal', 'RightNum', 'BetNum', 'LastBetMatch', 'LastBet', 'Level' );
                    echo "<tr>";
                      
                    for($i = 0 ; $i < count($fields); $i++)
                    {
                        echo "<td>";
                        echo $fields[$i];    
                        echo "</td>";
                    }
                    
                    echo "</tr>";
                    
            foreach($top10 as $order => $profile)
            {
                echo "<tr>";
                
                for($i = 0 ; $i < count($fields); $i++)
                {
                        echo "<td>";
                        if($fields[$i] == 'LastBet')
                            echo date("d/m/Y H:i:s", $profile[$fields[$i]]);    
                        else 
                            echo $profile[$fields[$i]];
                        echo "</td>";
                }
                
                echo "</tr>";
            }
            
            
        ?>
        </table>
    </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>
