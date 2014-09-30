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
    <?php if($block == 1) {?>
       <form id="form1" name="form1" method="post" action="?mod=Index&act=euro_updateResult">
                Match 
                <select name='chooseMatch'>
                    <?php
                        foreach($ModifyAvailable as $idMatch => $match)
                        {
                            //M1.Poland - Hungari. Vong bang. 2012-06-08 23:00
                            $matchInfo = 'M' . $idMatch . '. ' . $match['MatchType'] . '. ' .$Teams[$match['Team1']] . ' - ' .$Teams[$match['Team2']] . '. ' . date("d/m/Y H:i:s", $match['MatchTimeBegin']);
                            echo '<option value="'.$idMatch.'">'.$matchInfo.'</option>';
                        }?>
                </select>
                
                <?php if(count($ModifyAvailable) > 0) {?>
                
                <input type="submit" name="choseModify" value="Chon"/>
                
                <?php }?>
                
       </form>
    <?php }else if($block == 2){ ?>
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>
        
       <h3> Update Ket qua </h3> 
       <h3> <?php echo date("d/m/Y H:i:s", $Fixture[$MatchId]['MatchTimeBegin']);?>  </h3>
       <h3><?php echo $MatchType[$Fixture[$MatchId]['MatchType']] ;?></h3>
        <table border="1" style="width: 700px; text-align: center; height: 50px;">
                <tr>
               <?php
                    echo "<td><h2>".$Team1."</h2></td>";                    
                    echo "<td><h2>".$Team2."</h2></td>";
                ?>
                </tr>
        </table>
       <form id="form2" name="form2" method="post" action="?mod=Index&act=euro_updateResult">
            
            <h2>Goals</h2>
            <b><?php echo $Team1; ?></b> <input type="text" name="Team1Goal" /> 
            <b>-</b>
            <input type="text" name="Team2Goal" />&nbsp; <b><?php echo $Team2; ?></b>
            &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
            <b>SpecialResult</b>
            <select name="specialResult">
                <?php
                    foreach($SpecialResult as $index => $value)
                    {
                        echo '<option value="'.$index.'">'.$value.'</option>'; 
                    }
                ?>
            </select>
            
            <?php 
                echo '<input type="hidden" name="MatchId" value="'.$MatchId.'" />' ; 
            ?>
            <input type="submit" name="preview" value="Preview"  style="margin-left: 630px; margin-top: -18px;"/>
            
       </form>
    
    <?php }else if($block == 3){ ?> 
    
        <span style="margin-left: 520px;">
            Current Time:  <?php echo date("d/m/Y H:i:s");?> <br/>
        </span>
        
       <h3> Update Ket qua </h3>  
       <h3> <?php echo date("d/m/Y H:i:s", $Fixture[$MatchId]['MatchTimeBegin']);?>  </h3>
       <h3><?php echo $MatchType[$Fixture[$MatchId]['MatchType']] ;?></h3> 
       
       <table border="1" style="width: 700px; text-align: center; height: 50px;">
        <tr>
       <?php
            echo "<td><h2>".$Team1."</h2></td>";
            echo "<td><h1>".$Team1Goal."</h1></td>";
            
            echo "<td><h1>".$Team2Goal."</h1></td>";
            echo "<td><h2>".$Team2."</h2></td>";
        ?>
        </tr>
        </table>
        <span style="margin-left: 600px;">* &nbsp; <?php echo $SpecialResult[$SpecialResultIndex]?> </span>
     <form id="form3" name="form3" method="post" action="?mod=Index&act=euro_updateResult">   
       <?php
                echo '<input type="hidden" name="MatchId" value="'.$MatchId.'" />' ;
                echo '<input type="hidden" name="Team1Goal" value="'.$Team1Goal.'" />' ; 
                echo '<input type="hidden" name="Team2Goal" value="'.$Team2Goal.'" />' ; 
                echo '<input type="hidden" name="specialResult" value="'.$SpecialResultIndex.'" />' ; 
       ?>
       <br/>
       <br/>
       <span style="margin-left: 538px;">
              <input type="submit" name="submitResult" value="Submit" /> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
              <input type="submit" name="cancelResult" value="Cancel" />  
      </span> 
      
     </form>
    
    <?php }else if($block == 4){ ?>
    
        <?php echo $message ; 
            if($error)
            {
                ?>
         <form id="form4" name="form4" method="post" action="?mod=Index&act=euro_updateResult"> 
         <?php
                echo '<input type="hidden" name="MatchId" value="'.$MatchId.'" />' ; 
         ?>        
            <input type="submit" name="back" value="Back" />  
          </form>
        <?php        
            }
        ?>
            
    <?php }?>
    
     </div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>
