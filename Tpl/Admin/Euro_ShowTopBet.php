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
      </ul>
    </div>      
    <div id="center-column">
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>
        <h3>Top Bet</h3>
        <table border="1" style="width: 700px; text-align: center;"> 
        <?php
            $fieldsShow = array('Match', 'uId', 'Time Bet', 'Bet Type','Num Ball Bet', 'Exchange Medal', 'User Bet', 'Ball Vip Remain', 'Ball Ord Remain', 'Medal Total', 'User Level', 'Bet Right or Wrong' );
            $fields = array('match_id', 'uId', 'bettime', 'bet_type','bet_ball', 'medal_bet', 'bet', 'ball_left_vip', 'ball_left_ord', 'medal_left', 'level_bet', 'result_bet' );
                    echo "<tr>";
                      
                    for($i = 0 ; $i < count($fieldsShow); $i++)
                    {
                        echo "<td>";
                        echo $fieldsShow[$i];    
                        echo "</td>";
                    }
                    
                    echo "</tr>";
                    
            foreach($TopBet as $order => $profile)
            {
                echo "<tr>";
                
                for($i = 0 ; $i < count($fields); $i++)
                {
                        echo "<td>";
                        if($fields[$i] == 'bettime')
                            echo date("d/m/Y H:i:s", $profile[$fields[$i]]);    
                        else 
                            echo $profile[$fields[$i]];
                        echo "</td>";
                }
                
                echo "</tr>";
            }
            
            
        ?>
        </table>
        Sap xep theo Exchange Medal
        So Exchange Medal va Num Ball Bell lon hon 15000, so Ball Vip Remain cuc lon hoac -, la cac truong hop can check truoc khi update Result Match
    </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>
