<?php
include TPL_DIR . "/head.php";
session_start();
?>

<div id="middle">
    <div id="left-column">
      <h3>GM tool</h3>
      <ul class="nav">
        <li>Huy hieu - Dap trung</li>
        <li><a href="?mod=Index&act=Config6Star">Cau hinh HH 6 sao</a></li>
      </ul>
</div>      

<div id="center-column">    
   <form id="form2" name="form2" method="post" action="?mod=Index&act=Config6Star">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="20%" height="23">So luong HH 6 sao</td>
            <td width="60%"><input type="text" name="Num6Star" value="<?php echo $Num6Star;?>" readonly="readonly" /></td>                
            <td style="color: red"><?php echo($message) ; ?>
            </td>
          </tr>
          <tr>
            <td width="20%" height="23">So luong Quota 6 sao</td>
            <td width="60%"><input type="text" name="NumView6Star" value="<?php echo $NumView6Star;?>" /></td>                
            <td style="color: black">Edit</td>
          </tr>
          <tr>
            <td width="20%" height="23">Quota tu 0h -> 6h</td>
            <td width="60%"><input type="text" name="Quota_6" value="<?php echo $Quota_6;?>" /></td>                
            <td style="color: black">Edit</td>
          </tr>
          <tr>
            <td width="20%" height="23">Quota tu 6h -> 12h</td>
            <td width="60%"><input type="text" name="Quota_12" value="<?php echo $Quota_12;?>" /></td>                
            <td style="color: black">Edit</td>
          </tr>
          <tr>
            <td width="20%" height="23">Quota tu 12h -> 18h</td>
            <td width="60%"><input type="text" name="Quota_18" value="<?php echo $Quota_18;?>" /></td>                
            <td style="color: black">Edit</td>
          </tr>
          <tr>
            <td width="20%" height="23">Quota tu 18h -> 24h</td>
            <td width="60%"><input type="text" name="Quota_24" value="<?php echo $Quota_24;?>" /></td>                
            <td style="color: black">Edit</td>
          </tr>

          <tr>
            <td width="20%" height="23">Num tu 0h -> 6h</td>
            <td width="60%"><input type="text" name="Num_6" value="<?php echo $Num_6;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>
          </tr>
          <tr>
            <td width="20%" height="23">Num tu 6h -> 12h</td>
            <td width="60%"><input type="text" name="Num_12" value="<?php echo $Num_12;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>
          </tr>
          <tr>
            <td width="20%" height="23">Num tu 12h -> 18h</td>
            <td width="60%"><input type="text" name="Num_18" value="<?php echo $Num_18;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>
          </tr>
          <tr>
            <td width="20%" height="23">Num tu 18h -> 24h</td>
            <td width="60%"><input type="text" name="Num_24" value="<?php echo $Num_24;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>
          </tr>
          <tr>
            <td width="20%" height="23">Date Key</td>
            <td width="60%"><input type="text" name="DateKey" value="<?php echo $DateKey;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>            
          </tr>
          <tr>
            <td width="20%" height="23">Number VIP Max</td>
            <td width="60%"><input type="text" name="QuotaVipMax" value="<?php echo $QuotaVipMax;?>" readonly="readonly" /></td>                
            <td style="color: black">View</td>            
          </tr>
          
              <tr>
                <td width="20%" height="23">&nbsp;
                </td>
                <td width="60%">
                    <input type="submit" name="Submit" value="Save" />
                </td>
              </tr>
          
        </table>
   </form>
</div>        
<?php
include TPL_DIR . "/foot.php";
?>