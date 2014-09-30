<?php
include TPL_DIR . "/head.php";
?>
<script language="javascript">

</script>
<div id="middle">
    <div id="left-column">
      <h3>Euro Event</h3>
      <ul class="nav">
<!--        <li><a href="?mod=Index&act=euro_addMatch">Them tran thi dau</a></li>
        <li><a href="?mod=Index&act=euro_delMatch">Xoa tran thi dau</a></li> -->
        <li><a href="?mod=Index&act=euro_modifyFixture">Cap nhat Thi dau</a></li>
        <li><a href="?mod=Index&act=euro_showTop">Show Top</a></li>
        <li><a href="?mod=Index&act=euro_updateResultAgain">Update Lai Ket qua</a></li>
        <li><a href="?mod=Index&act=euro_updateingame">Update Only In Game</a></li>
        <li><a href="?mod=Index&act=euro_refreshTop">Refresh Top</a></li>
        <li><a href="?mod=Index&act=euro_resetData">Reset User Event</a></li>
        
      </ul>
    </div>      
    <div id="center-column">
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>    
        <h3>Fixture</h3>
        <table border="1" style="width: 700px; text-align: center; height: 500px;">
        <?php
                $fields = array('Team1', 'Team2', 'MatchType', 'Star', 'MatchTimeBegin', 'BetTimeBegin', 'Goal');
                echo "<tr>";
                  
                echo "<td>";
                echo "Match";    
                echo "</td>";
                for($i = 0 ; $i < count($fields); $i++)
                {
                    echo "<td>";
                    switch($fields[$i])
                    {
                        case 'MatchTimeBegin':
                            echo 'Thoi gian dien ra';
                            break;
                        case 'BetTimeBegin':
                            echo 'Bat dau cuoc';
                            break;
                        default:
                            echo $fields[$i];     
                    }
                       
                    echo "</td>";
                }
                
                echo "</tr>";
              foreach($Fixture as $idMatch => $match)
              {
                  echo "<tr>";
                  
                  echo "<td>";
                  echo 'M.'.$idMatch;    
                  echo "</td>";
                  
                  for($i = 0 ; $i < count($fields); $i++)
                  {
                    echo "<td>";
                    switch($fields[$i])
                    {
                        case "MatchTimeBegin":
                        case "BetTimeBegin":
                            echo date("d/m/Y H:i:s", $match[$fields[$i]]);
                            break;
                        case "Goal":
                            echo (count($match[$fields[$i]]) > 0) ? $match[$fields[$i]][0].','.$match[$fields[$i]][1] : '';
                            break;
                        case "Team1":
                        case "Team2":
                            echo $Teams[$match[$fields[$i]]];
                            break;
                        case "MatchType":
                            echo $MatchType[$match[$fields[$i]]];
                            break;
                        default:
                            echo $match[$fields[$i]];    
                            break;
                    }                    
                    echo "</td>";
                  }
                  
                  
                  echo "<tr/>";
              }
        ?>
        </table>
        
     </div>
        
  </div>
  <?php
include TPL_DIR . "/foot.php";
?>