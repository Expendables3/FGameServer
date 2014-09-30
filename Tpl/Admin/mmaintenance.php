<?php
include TPL_DIR . "/head.php";
?>
<script type="text/javascript">
$(document).ready(function() {
	 $("#lockMaintenance").click(function(event){
			jQuery.facebox({ ajax: '?mod=Index&act=mMPopup' });						   
		});				   					   
 });
</script>
<div id="middle">
    <div id="left-column">
      <h3>Quản lý bảo trì</h3>
      <ul class="nav">
            </ul>
</div>
      
    <div id="center-column">
      <div class="top-bar"> 
        <h1>Quản lý</h1>
        <div class="breadcrumbs"><a href="<?php echo $this->url ;?>">Trang chủ</a> / <a href="">Quản lý bảo trì</a></div>
      </div>
      <br />
      <div class="select-bar">
      </div>
      <div class="table" style="width:736px"> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-left.gif" width="8" height="7" alt="" class="left" /> <img src="<?php echo $this->config['imgdir_admin'] ?>bg-th-right.gif" width="7" height="7" alt="" class="right" />
        <table class="listing" cellpadding="0" cellspacing="0" style="width:736px">
          <tr>
            <th class="first" width="367">Nội dung</th>
            <th width="100">Thời gian thay đổi lần cuối</th>
            <th width="63">Trạng thái</th>
            <th class="last" width="60">Hành động</th>
          </tr>
            <?php if(is_array($list_main)&& count($list_main)>0){
				$status =isset($list_main['status'])? $list_main['status']:0;
				?>
              <tr>
                <td class="first style1"><?php if(isset($list_main['content'])) echo $list_main['content']; ?></td>
                <td><?php if(isset($list_main['timemodifi'])) echo date('d-m-Y',$list_main['timemodifi']); ?></td>
                <td><?php if($status==1) echo 'Đang khóa'; else echo "Không khóa"; ?></td>
                <td class="last">
                    <a href="javascript:void(0);" id="lockMaintenance">Thay đổi</a>
                 </td>
              </tr>
            <?php } else {  ?>
              <tr>
                <td class="first style1">Chưa có dữ liệu </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="last"><a href="javascript:void(0);" id="lockMaintenance">Khóa</a></td>
              </tr>
			<?php }?>
        </table>

      </div>
    </div>
  </div>
<?php
include TPL_DIR . "/foot.php";
?>