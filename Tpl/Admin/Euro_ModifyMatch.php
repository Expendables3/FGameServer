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
      <?php if($block == 1) {?> 
            <form id="form1" name="form1" method="post" action="?mod=Index&act=euro_modifyMatch">
                Match 
                <select name='chooseMatch'>
                    <?php
                        foreach($ModifyAvailable as $idMatch => $match)
                        {
                            //M1.Poland - Hungari. Vong bang. 2012-06-08 23:00
                            $matchInfo = 'M' . $idMatch . '. ' . $match['MatchType'] . '. ' .$Teams[$match['Team1']] . ' - ' .$Teams[$match['Team2']] . '. ' . date("d/m/Y H:i:s", $match['MatchTimeBegin']);  
                            echo '<option value="'.$idMatch.'">'.$matchInfo.'</option>';
                        }
                    ?>
                </select>
                
                <?php if(count($ModifyAvailable) > 0) {?>
                
                <input type="submit" name="choseModify" value="Chon"/>
                
                <?php }?>
                
            </form>
      <?php }else if($block == 2){ ?>
            <span style="margin-left: 520px;">
                Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
            </span>
        
           <h3> Chinh sua </h3> 
           <h3> <?php echo date("d/m/Y H:i:s", $Fixture[$MatchId]['MatchTimeBegin']);?>  </h3>
           <h3><?php echo $MatchType[$Fixture[$MatchId]['MatchType']] ;?></h3> 
           <h2><?php echo $Teams[$Fixture[$MatchId]['Team1']] . ' - ' .  $Teams[$Fixture[$MatchId]['Team2']];?></h2>
           <br>
            <form id="form2" name="form2" method="post" action="?mod=Index&act=euro_modifyMatch">
            
            <table border="1" style="width: 700px; text-align: center; height: 50px;">
            <?php
                $fields = array('Star', 'MatchTimeBegin', 'BetTimeBegin');
                echo "<tr>";
                  
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
              
                  echo "<tr>";
                  
                  $match = $Fixture[$MatchId];
                  
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
                  echo "</tr>";
              ?>
              </table>
            <br>  
        
            <p>
                <?php if($typeModify == 'flexTeam') {?> 
                <b>Team1</b> &nbsp; 
                <select name='Team1'>
                    <?php
                        foreach($Teams as $code => $plainName)
                        {
                            echo '<option value="'.$code.'">'.$plainName.'</option>';
                        }
                    ?>
                </select> &nbsp;&nbsp;
                <b>Team2 </b> &nbsp;
                <select name='Team2'>
                    <?php
                        foreach($Teams as $code => $plainName)
                        {
                            echo '<option value="'.$code.'">'.$plainName.'</option>';
                        }
                    ?>
                </select> <br>
                
                <?php 
                    echo '<input type="hidden" name="typeModify" value="'.$typeModify.'" />' ; 
                ?>
                
                <?php }?>
                
                <br/>
                
                <b>Do hap dan </b> &nbsp;
                <select name='Star'>
                    <option value=""></option>
                    <?php
                        foreach($Star as $index => $star)
                        {
                            echo '<option value="'.$star.'">'.$star.' Star</option>';
                        }
                    ?>
                </select> &nbsp; &nbsp; &nbsp;
                
                <b>Thoi gian dien ra</b> &nbsp;
                <input type="text" name="MatchTimeBegin" /> &nbsp; &nbsp;
                <b>Thoi gian bat dau cuoc </b> &nbsp;
                <input type="text" name="BetTimeBegin" /> <br/>           
            
            </p>
            
            <?php 
                echo '<input type="hidden" name="MatchId" value="'.$MatchId.'" />' ; 
            ?>
            
            <br>
            
             <span style="margin-left: 538px;">
                <input type="submit" name="submitModify" value="Submit" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
                <input type="submit" name="cancelModify" value="Cancel" />  
              </span>
            
            </form>
      <?php }else if($block == 3){ ?>            
            <?php echo $message ; ?>
            
      <?php }?>    
     </div>
  </div>
  <?php
include TPL_DIR . "/foot.php";
?>
