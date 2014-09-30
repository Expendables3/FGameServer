<?php
include TPL_DIR . "/head.php";
?>

<script language="javascript">

</script>

<style type="text/css">
.aaa 
{ 
        border-bottom: black 1px solid ;
        border-right: black 1px solid ;
}
.bbb 
{       border-top: black 1px solid ;  
        border-bottom: black 1px solid ;
        border-right: black 1px solid ;
        background-color: gray;
        font-size: 14px;
}
/*td      { 
        border-bottom: black 1px solid ;
        border-right: black 1px solid ;
        }   */
</style> 

<div id="middle">  
<div style="text-align:center; min-height:400px">  
    <div>
      <?php if( $this->side == 1) {?>   
      <form name="form_1" method="post" action="?mod=Index&act=design_index">
      <select name="GameName">
        <option value="Fish" selected="selected">myFish</option>;
        <option value="Farm">myFarm</option>;
        <option value="Caro">myCaro</option>;
      </select>
      <input type="submit" name="submit_1" value="xac nhan">
      </form>
      <?php }else if($this->side == 2) { ?>
      <form name="form_2" action="?mod=Index&act=design" method="post"> 
       <table cellpadding="5" cellspacing="0" border="0" width="100%">
       <tr>
                  <td class='bbb'>stt</td> 
                  <td class='bbb'>noi dung</td>
                  <td class='bbb'>link</td> 
                  <td class='bbb'>phan tram</td> 
                  <td class='bbb'> xoa </td> 
       </tr>
        <?php
          $i =1 ;
          if(empty($ViewData))
          {
            echo 'ko co du lieu' ;
          }
          else
          {  
            foreach($ViewData as $key => $conten)
            {
              echo "<tr>
                    <td class='aaa'>{$i}</td> 
                    <td class='aaa'>{$conten['content']}</td>
                    <td class='aaa'>{$conten['Link']}</td> 
                    <td class='aaa'>{$conten['Per']}</td> 
                    <td class='aaa'><input type='submit' name='deleteField' value='del{$key}'></td> 
                    </tr>";
              $i++ ;
            }
          }
        ?>
        </table> 
        <div>
          <br><br>
          <input type="submit" name="submit_add" value="them">
          <input type="hidden" name="namegame" value="<?php echo $this->nameGame ; ?>">
        </div>
  
        </form>    
      <?php }else if($this->side == 3) {?>
      <form name = "form_3" method="post" action="?mod=Index&act=design">
      <table>
      <tr>
        <td> noi dung :<input type="text" name ="TextContent"></td>
        <td> Link :<input type="text" name = "LinkContent"</td>
        <td> phan tram :<input type="text" name = "Percent"></td>
        <td> <input type="submit" name = "addNewField" onclick="kiemtra()" value="save"></td>
        <input type="hidden" name="namegame" value="<?php echo $this->nameGame ; ?>">
      </tr>
      </table>
      </form> 
       <?php }?>
       <?php  echo '<br>'.$messge ?> 
         
    </div>
</div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>