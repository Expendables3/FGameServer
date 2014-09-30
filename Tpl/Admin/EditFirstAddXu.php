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
        <li><a href="?mod=Index&act=EditFirstAddXu">sửa nap thẻ lần đầu</a></li>        
        <li>Lien dau</li>
        <li><a href="?mod=Index&act=ResetOccupyBoard">Xóa OccupyBoard</a></li>
        <li><a href="?mod=Index&act=ShowOccupyBoard">Check UserRank</a></li>
        <li>Huy hieu - Dap trung</li>
        <li><a href="?mod=Index&act=Config6Star">Cau hinh HH 6 sao</a></li>
      </ul>
</div>      
    <div id="center-column">    
       <form id="form2" name="form2" method="post" action="?mod=Index&act=EditFirstAddXu">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="20%" height="23">Zing ID </td>
                <td width="60%"><input type="text" name="uid" /></td>  
                <td style="color: red"><?php echo($mess) ; ?>
                </td>
              </tr>
              <tr>
                <td width="20%" height="23">First Add Xu</td>
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
              <tr id="szid"></tr>
              <tr>
                <td width="20%" height="23">&nbsp;
                </td>
                <td width="60%">
                    <input type="submit" name="Edit" value="Edit" />
                    <input type="reset" name="Submit" value="Cancel" />
                </td>
              </tr>
              </table>
            </form>

       </table>
       <br>
        <table width="100%" border="1" cellspacing="0" cellpadding="2" style="font-size: 14px"> 
       <tr >
           <td  valign="top">
               <?php                       
                       echo "<span> Username        : {$ViewData['User']['username']}</span><br>";
                       echo "<span> NickName    : {$ViewData['User']['Name']}</span><br>";
                       echo "<span> Avatar <img src='{$ViewData['User']['Avatar']}' width=80/></span><br>";
               ?>
           </td>
        </tr>
       </table><br>

     </div>
  </div>
  <?php
include TPL_DIR . "/foot.php";
?>
<script language="javascript">

viewItem(1);
</script>
?>
