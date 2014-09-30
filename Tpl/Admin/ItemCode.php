<?php
ini_set('memory_limit',-1);
ini_set('max_execution_time',180);

include TPL_DIR . "/head.php";
?>

<html>

  <head>
  </head>
  
  <body>
  <div id="middle" >
    <form id="ICForm" name="ICForm" method="post" action="?mod=Index&act=createItemCode">
      <?php if($block == 1 ){ ?>
      <div align="center">
        <div>chon loai Item code muon tao</div>
        <div>
            <select name="ItemCodeType">
                <option value="1">'loai Config'</option>
                <option value="2">'loai tu dong'</option>
                <option value="3">'loai list User' </option>
            </select>
            <input type="submit" name="Ok" value="Ok">
        </div>
      </div>
      <?php }else if($block == 2 ){ ?>   
      <div align="center">
        <div>loai Config </div>
        <div>
            <table>
                <tr><td>ConfigId:<input type="text" size="10" name="ConfigId" value=""></td></tr>
                <tr><td>So luong key :<input type="text" size="10" name="Num" value=""></td></tr>
                <tr><td>chuoi dau vao :<input type="text" size="10" name="TextInput" value=""></td></tr>
                <tr><td>chọn hệ ?:<select name="Element"><option value="0">ko chọn hệ </option><option value="1">có chọn hệ</option></select></td></tr>
            </table>
        </div>
        <div>
            <input type="submit" name="Create" value="Create">
            <input type="hidden" name="ItemCodeType" value=<?php echo $ItemCodeType;?> >
            <input type="submit" name="checkkey" value="kiem tra chung key">
        </div>
        
        <div>Code: <?Php echo $output.'<hr>'?></div>
      </div>
      <?php }else if($block == 3 ){ ?>  
      <div align="center"> 
        <div>loai tu dong  </div>
        <div>
            <table>
                <tr>
                    <td>Id :<input type="text" size="6" name="ItemCodeId" value="<?php echo $IdCode ;?>"></td><td><label style="color: red;" >Id < 1.000.000 </label>
                </tr>
                <tr>
                    <td>UserType :<input type="text" size="14" name="UserType" value="111111111111"></td>
                    <td><label style="color: red;" >UserType : 111111111111 is for All User </label>
                </tr>
                <tr>
                    <td>FromTime:<input type="text" size="10" name="FromTime" value="<?php echo date("Ymd",time()) ?>"></td></br>
                    <td>ToTime:<input type="text" size="10" name="ToTime" value="<?php echo date("Ymd",time()+10*24*3600) ?>"></td>
                </tr>
                <!--<tr>
                    <td>Noi dung :<div><textarea cols="20" rows="5" name="Content"></textarea></div></td>
                </tr>-->
                <tr>
                    <td>Mat ma them vao : cai nay bi mat va fix trong code</td>
                </tr>
            </table>
        </div>
        <div>
            <input type="submit" name="Create1" value="Create">
            <input type="hidden" name="ItemCodeType" value=<?php echo $ItemCodeType;?> >
        </div>
        
        <div>Code:<textarea cols="30" rows="5" name="output"><?Php echo $output;?></textarea> </div>
        </div>
      <?php }else if($block == 4 ){ ?> 
      <div align="center"> 
        <div>loai list User  </div>
        <div>
            <table>
                <tr>
                    <td>FromTime:<input type="text" size="10" name="FromTime" value="<?php echo date("Ymd",time()) ?>"></td></br>
                    <td>ToTime:<input type="text" size="10" name="ToTime" value="<?php echo date("Ymd",time()+10*24*3600) ?>"></td>
                </tr>
            </table>
        </div>
        <div>
            <input type="submit" name="Create2" value="Create">
            <input type="hidden" name="ItemCodeType" value=<?php echo $ItemCodeType;?> >
        </div>
        <?php
        foreach($codeList as $uId =>$_code)
        {
            
            echo '<div>'.$uId.'</div>' ;
            echo '<div><textarea cols="70" rows="4" name="output">'.$_code.'</textarea> </div>' ;
            echo '<br>';
        }
        ?>
        <!--<div>Code:<textarea cols="30" rows="5" name="output"><?Php // echo $output;?></textarea> </div>-->
        </div> 
    <?php } ?>     
    </form>
    <div align="center">
    <?php
    if($block == 2 || $block == 3)
    {
        if($notify)
            echo 'success !';
        else
            echo 'fail !';
    }
    ?>
    </div>
  </div> 
  </body>


</html>
<?php
include TPL_DIR . "/foot.php";
?>