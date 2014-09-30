<?php
include TPL_DIR . "/head.php";
?>
<script language="javascript">

</script>
<div id="middle">
    <div id="left-column">
      <h3>GM tool</h3>
      <ul class="nav">
        <li><a href="?mod=Index&act=AddItem">Them Item vao User</a></li>
        <li><a href="?mod=Index&act=DeleteItem">Xoa Item cua User</a></li>
        <li><a href="?mod=Index&act=EditItem">Sua Item cho User</a></li>
        <li><a href="?mod=Index&act=DeletePassword">Xóa Password</a></li>
        <li><a href="?mod=Index&act=ResetUser">Xóa UserData</a></li>     
        <li>Lien dau</li>
        <li><a href="?mod=Index&act=ResetOccupyBoard">Xóa OccupyBoard</a></li>
        <li><a href="?mod=Index&act=ShowOccupyBoard">Check UserRank</a></li>
        <li>Huy hieu - Dap trung</li>
        <li><a href="?mod=Index&act=Config6Star">Cau hinh HH 6 sao</a></li>
        
        <li><a href="?mod=Index&act=UpdateStore">Update Store</a></li>           
      </ul>
</div>      
    <div id="center-column">    
    <?php if($block == 1) {?>
    <form id="form1" name="form1" method="post" action="?mod=Index&act=gmtool">
    <table width="50%" border="0" cellspacing="0" cellpadding="0">
    <tr><td colspan="3" style="color:red"><?php echo $mess;?></td></tr>
      <tr>
        <td width="20%" height="23">Zing ID </td>
        <td width="60%"><input type="text" name="uid" /></td>
        <td width="20%"><input type="submit" name="Submit" value="Ok" /></td>
      </tr>
      </table>
    </form>
      <?php }else if($block ==2){ ?>
      
       </table>
       <br>
        <table width="100%" border="1" cellspacing="0" cellpadding="2" style="font-size: 14px"> 
       <tr >
           <td  valign="top">
               <?php                       
                       echo "<span> username        : {$ViewData['User']['username']}</span><br>";
                       echo "<span> TÃªn hiá»ƒn thá»‹    : {$ViewData['User']['Name']}</span><br>";
                       echo "<span> Avatar <img src='{$ViewData['User']['Avatar']}' width=80/></span><br>";
               ?>
           </td>
        </tr>
       </table><br>
<!--bc farm status -->
            <?php 
            

            foreach( $ViewData as $cId => $item )
            {     echo '<pre/>' ;
                  echo ' <table width="100%" border="1" cellspacing="0" cellpadding="2" style="font-size: 11px">
            <tr style="background-color: green">
              <td  height="23">'.$cId.'</td>

            </tr>
            <tr>
            <td  height="23" valign="top">' ;
                  if(is_array($item))
                  {
                     foreach($item as $anItem=>$anItemValue)
                     {
                         if(is_array($anItemValue))
                         {
                             echo "<span> ".$anItem.":&nbsp&nbsp&nbsp</span>";
                             print_r($anItemValue);
                             echo "<span></span><br>";
                         }
                         else
                         {
                            echo "<span>".$anItem.":&nbsp&nbsp&nbsp</span><span> (".$anItemValue.")</span><br>";                                         
                         }
                     }
                     
                     echo "<span></span><br>";                    
                     echo "<span></span><br>";
                     
                  }
                  else
                  {
                       echo "<span> (".$item.")</span><br>";                    
                  }
                echo '
                 </td>
                </tr>

                </table>';
            }
 ?>
<!--ec farm status -->
      <?php }elseif ($block==3){?>         

      <?php
      }
      ?>
     </div>
  </div>
  <?php
include TPL_DIR . "/foot.php";
?>