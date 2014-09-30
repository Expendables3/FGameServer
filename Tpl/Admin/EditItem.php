<?php
include TPL_DIR . "/head.php";
session_start();
?>
<script language="javascript">
  
function viewItem(type)
{
    var url = '?mod=Index&act=viewItem';
    $.ajax({
        url: url,
        type: 'POST',
        data: {            
            'type':type,
            'pageType':'Edit',
        },
        dataType: 'html',
        beforeSend: function (){                
        },
        success: function(res) {
            //alert(res);
            switch(res)
            {
            case 1:
                alert('Báº¡n cáº§n chá»�n loáº¡i ztype');
                break;
            default:
                $('#szid').html(res);
                break;
            }
        }
    });        
} 
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
      </ul>
</div>      
    <div id="center-column">  
       <form id="form1" name="form1" method="post" action="?mod=Index&act=EditItem">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td  height="23">Zing ID </td>
                <td><input type="text"  name="uid" value=""/>
                <!--<input type="hidden" name="uid" id="uid" value="<?php echo $uId; ?>" />-->
                </td>                
                <td style="color: red"><?php echo($mess) ; ?>
                </td>
              </tr>
              <tr>
                <td width="20%" height="23">Item Type </td>
                <td width="60%">
                <select name="ztype" id="ztype" onchange="viewItem(this.value)">
                <?php
                    foreach($configItem as $Name =>$info)
                    {
                      echo '<option value="'.$Name.'">'.$Name.'</option>';
                    }
                ?>
                </select>
                </td>                
              </tr>
              <tr id="szid">
              </tr>
              <tr>
                <td width="20%" height="23">&nbsp;
                </td>
                <td width="60%">
                    <input type="submit" name="InfoItem" value="View" onchange="EditInfoItem(this.value)"  />
                    <input type="reset" name="Submit" value="Cancel" />
                </td>
              </tr>
              <tr id ="zEditID"></tr>
              </table>
       </form>
       
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
     </div>
  </div>
  <?php
include TPL_DIR . "/foot.php";
?>
<script language="javascript">

viewItem(1);
</script>