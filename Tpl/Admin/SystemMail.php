<?php
include TPL_DIR . "/head.php";
?>

<html>

  <head>
  </head>
  
  <body>
  <div id="middle" >
    <form id="sysMailForm" name="sysMailForm" method="post" action="?mod=Index&act=systemMail">
      <?php if($block == 1 ){ ?>
      <div align="center">
        <div>chon loai thu muon gui</div>
        <div>
            <select name="MailType">
                <option value="1">'toan he thong'</option>
                <option value="2">'tung ca nhan'</option>
                <option value="3">'Delete toan he thong'</option>
            </select>
            <input type="submit" name="Ok" value="Ok">
        </div>
      </div>
      <?php }else if($block == 2 ){ ?>   
      <div align="center">
      
        <div>Gui toan he thong </div>
        <div>noi dung thu </div>
        <div>
            <input type="text" style="width: 300px; height: 200px;" name="Content">
        </div>
        <div>
            <input type="submit" name="SendMail" value="SendMail">
            <input type="hidden" name="MailType" value=<?php echo $MailType;?> >
        </div>
      </div>
      <?php }else if($block == 3 ){ ?>   
      <div align="center">
        <div>danh sach UserId</div>
        <div><textarea cols="20" rows="5" name="UserList"></textarea></div>
        <div>noi dung thu </div>
        <div>
            <input type="text" style="width: 300px; height: 200px;" name="Content">
        </div>
        <div>
            <input type="submit" name="SendMail" value="SendMail">
            <input type="hidden" name="MailType" value=<?php echo $MailType;?> >
        </div>
        
      </div>
      <?php }else if($block == 4 ){ ?>   
      <div align="center">
        <div>danh sach Mail he thong </div>
        
        <div>
        <table align="center" border="1" cellpadding="3" cellspacing="3">
            <?php
                foreach($AllSystemMail as $key => $content_1)
                {
                    if(is_string($key)) continue ;
                    echo "<tr><td>$key</td>";
                    echo "<td>".$content_1['Content']."</td>";
                    echo "<td><input type='submit' name='DeleteMail' value=".$key."></td>";
                    echo '</tr>';
                }
            ?>
        </table>
        </div>
      </div>
      <?php } ?>     
    </form>
    <div align="center">
    <?php
    if($block == 2 || $block == 3)
    {
        if($notify)
            echo 'send mail success !';
        else
            echo 'send mail fail !';
    }
    ?>
    </div>
  </div> 
  </body>


</html>
<?php
include TPL_DIR . "/foot.php";
?>