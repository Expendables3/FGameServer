<?php
include TPL_DIR . "/head.php";
?>
<script type="text/javascript">
$(document).ready(function() {
	   $("img[rel='delete_comment']").click(function(event){
           var ok=confirm('Bạn có thật sự muốn xóa góp ý này không?'); 
		   if (!ok)
		    return false;
           var url = '?mod=Index&act=deleteComment';
		   var userid=$(this).attr('name');
		   var key   = $(this).attr('id');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'userid': $(this).attr('name'),
                    'key': $(this).attr('id')
                   },                
                dataType: 'html',
                beforeSend: function (){
                       $('#waiting_result').html("<b>Đang xử lý</b>");
                },
                success: function(res) {
                   res=$.trim(res);
				   //alert(res); //test
                   switch(res){                               
                   case "Successfull": 
				       $('#'+userid+'_'+key).remove();
                	    alert("Xóa thành công!"); 
                       break;
                   case "Unsuccessfull": 
                	    alert("Chưa xóa được góp ý này.Vui lòng thử lại"); 
                       break;					   
                   case "notData": 
                	    alert("Dữ liệu không hợp lệ.Vui lòng thử lại"); 
                       break;           
                   default:
                	     alert('Có lỗi trong quá trình xử lý.Vui lòng thử lại');
                        break;     
                   }
                }
            });
            return false;
	   });						   
 });
</script>
<div id="middle">
    <div id="left-column">
      <h3>Góp ý</h3>
      <ul class="nav">
        <li><a href="">Danh sách góp ý</a></li>

      </ul>
</div>
      
    <div id="center-column">
      <div class="top-bar"> <!--<a href="" class="button">Thêm mới </a>-->
        <h1>Quản lý góp ý</h1>
        <div class="breadcrumbs"><a href="<?php echo $this->url ;?>">Trang chủ</a> / <a href="">Quản lý góp ý</a></div>
      </div>
      <br />
      <div class="select-bar">
      </div>
      <div class="table" style="width:736px"> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-left.gif" width="8" height="7" alt="" class="left" /> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-right.gif" width="7" height="7" alt="" class="right" />
        <table class="listing" cellpadding="0" cellspacing="0" style="width:736px">
          <tr>
            <th class="first" width="367">Nội dung</th>
            <th width="130">Email người gửi</th>
            <th width="136">Ngày gửi</th>
            <th width="63">Trạng thái</th>
            <th class="last" width="30">Xóa</th>
          </tr>
          <?php if($comments){
				  foreach($comments as $rs){
					  $uId =$rs['uId'];
					  $key =$rs['key'];
			  ?>
              <tr id="<?php echo $uId; ?>_<?php echo $key; ?>">
                <td class="first style1"><a title="<?php echo $rs['title']; ?>" href="?mod=Index&act=detailComments&userid=<?php echo $uId; ?>&key=<?php echo $key; ?>">
                <?php  if(isset($rs['title'])) {
                	if(strlen($rs['title']) > 50)
                	{	
                 		echo substr($rs['title'],0,50)." ...";
                	}
                	else
                	{
                		echo $rs['title'];
                	}
                }
                                
                 ?></a></td>
                <td><?php if(isset($rs['mail'])){ ?><a href="mailto:<?php echo $rs['mail'];?>?Subject=<?php  if(isset($rs['title'])) echo "RE: " ;?>"><?php  echo $rs['mail'];?></a><?php }?></td>
                <td><?php if(isset($rs['timesend'])) echo date('d-m-Y',$rs['timesend']); ?></td>
                <td>
                  <?php if(isset($rs['read']) && $rs['read']==0){?>
                    <b> Chưa đọc </b>
                  <?php } else { ?>
                   <span> Đã đọc </span>
                  <?php }?>
                </td>
                <td class="last"><img rel="delete_comment" name="<?php echo $uId; ?>" id="<?php echo $key; ?>" style="cursor:pointer" name="btn_create" src="<?php echo $this->config['imgdir_admin'] ?>hr.gif" width="16" height="16" alt="add" /></td>
              </tr>
          <?php } } ?>
        </table>
        <div class="select"> <strong>Trang: </strong>
         <?php echo  $link;?>
        </div>
      </div>
    </div>
  </div>
<?php
include TPL_DIR . "/foot.php";
?>