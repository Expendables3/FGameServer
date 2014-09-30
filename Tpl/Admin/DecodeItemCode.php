<?php
include TPL_DIR . "/head.php";
?>

<html>

  <head>
  </head>
  
  <body>
  <div id="middle" >
    <form id="ICForm" name="ICForm" method="post" action="?mod=Index&act=decodeItemCode">
      <div align="center">
        <div>chon loai Item code muon tao</div>
        <div>
            <select name="ItemCodeType">
                <option value="1" selected="selected" >'loai Config'</option>
                <option value="2">'loai tu dong'</option>
                <option value="3">'loai list User'</option>
            </select>
        </div>  
        <div>
           Code :<textarea cols="30" rows="5" name="Code"></textarea>
        </div>
        <div>
           <input type="submit" name="Decode" value="Decode">
           <input type="reset" name="Cancel" value="Cancel">
        </div>
        
    </form>
    <div align="center">
    <div>Code: <?Php echo "<pre>" ; var_dump($arr);  echo "</pre>";?></div>
    <?php
        if($notify)
            echo 'send mail success !';
        else
            echo 'send mail fail !';
    ?>
    </div>
  </div> 
  </body>


</html>
<?php
include TPL_DIR . "/foot.php";
?>