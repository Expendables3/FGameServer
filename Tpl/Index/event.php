<?php
include TPL_DIR . "/head.php";
?>
<div id="zf_template">
	<div id="zf_global">
		<div id="zf_head">
			<div class="zf_none"></div>
		</div>
		<div id="zf_body">
			<div id="zf_content">
				<div class="zf_content_block">
					<div style="float: left; width: 500px;background-image:url(<?php echo $conf['imgdir'] ?>HTW1_59.gif);height: 36px;background-repeat: no-repeat; background-position: right;">
						<div class="zf_content_title" style="width: 400px;">Thời gian</div>
					</div>
					<div class="zf_content_text">
						<span style="padding-left: 30px;">Từ <span style="color:#FF0000;">10h</span> ngày <span style="color:#FF0000;">4/9</span> đến <span style="color:#FF0000;">10h</span> ngày <span style="color:#FF0000;">27/9</span></span>					</div>
				</div>
				<div class="zf_content_block">
					<div style="float: left; width: 580px;background-image:url(<?php echo $conf['imgdir'] ?>HTW1_59.gif);height: 36px;background-repeat: no-repeat; background-position: right;">	
						<div class="zf_content_title">Điều kiện tham gia</div>
					</div>
					<div class="zf_content_text">
						<span style="padding-left: 30px;">Đạt <span style="font-weight:bold">cấp độ 5</span> trở lên trong zingfarm</span>					</div>
				</div>
				<div class="zf_content_block">
					<div style="float: left; width: 660px;background-image:url(<?php echo $conf['imgdir'] ?>HTW1_59.gif);height: 36px;background-repeat: no-repeat; background-position: right;">	
						<div class="zf_content_title">Yêu cầu</div>
					</div>
					<div class="zf_content_text">
						<p style="padding-left: 30px;">Bạn hãy mời bạn bè của mình vào chơi zingfarm theo <a href="#">hướng dẫn</a>. Bạn bè được xem là mời thành công khi họ chưa vào chơi ZingFarm bao giờ và phải đăng nhập vào ZingFarm lần đầu thông qua đường Link bạn gửi hoặc đăng lên tường.
						  Tất cả những người bạn mà bạn mời thành công sẽ được cập nhật trong bảng nội dung nhiệm vụ.<br>
					  </p>
				  <center><img src="<?php echo $conf['imgdir'] ?>HTW1_15.gif" width="339" height="245" alt="" style="margin: 0px auto;"></center><br>					</div>
				</div>
				<div class="zf_content_block">
					<div style="float: left; width: 660px;background-image:url(<?php echo $conf['imgdir'] ?>HTW1_59.gif);height: 36px;background-repeat: no-repeat; background-position: right;">	
						<div class="zf_content_title">Phần thưởng</div>
					</div>
					<div class="zf_content_text">
						<div class="text1">Nhiệm vụ 1: <span style="color:#000000; font-weight: bold">5000$</span></div>
						<br><center><img class="img" src="<?php echo $conf['imgdir'] ?>HTW1_64.gif"></center>
						<div class="text1">Nhiệm vụ 2: <span style="color:#000000; font-weight: bold">1 chú cá Chép (hiện tại nuôi cá không bị ăn trộm nha)</span></div>
						<br><center><img class="img" src="<?php echo $conf['imgdir'] ?>HTW1_63.gif"></center>
						<span style="padding-top: 30px;padding-bootom: 30px;">Lưu ý là nếu bạn đã có đủ 3 chú cá ở trong ao thì bạn sẽ nhận được tiền thay cho cá, số tiền bằng đúng giá mua 1 chú cá (10.000$)</span>
						
						<div class="text1">Nhiệm vụ 3: <span style="color:#000000; font-weight: bold">1 chú mèo múp và 5 túi thức ăn Chó & Mèo</div>
						<center><img class="img" src="<?php echo $conf['imgdir'] ?>HTW1_62.gif"></center>						
						<span style="padding-top: 30px;padding-bootom: 30px;">Chú mèo này khi được cho ăn sẽ giúp bạn ăn trộm được nhiều nông sản hơn (30% khả năng ăn trộm được gấp 2)</span>
						<center><img class="img" src="<?php echo $conf['imgdir'] ?>HTW1_61.gif"></center>
					</div>
				</div>
				<div class="zf_content_block">
					<div style="float: left; width: 660px;background-image:url(<?php echo $conf['imgdir'] ?>HTW1_59.gif);height: 36px;background-repeat: no-repeat; background-position: right;">	
						<div class="zf_content_title">Hướng dẫn cách mời bạn</div>
					</div>
					<div class="zf_content_text">
						<div class="text1">Cách 1: Mời bạn bè bằng link</div>		
						<span style="padding: 30px;">
						Hãy copy link này và gửi cho bạn bè của bạn<br>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<?php 
							$encodeID = base64_encode($this->uId);
				         	$invite_link = $conf['domain_api'].'?mod=Invite&act=ProcessInvite&invite='.$encodeID;
						?>
						<input type="text" value="<?php echo $invite_link;?>" class="input_text" onfocus="this.select()">
						</span>
						<!--
						<div class="text1">Cách 2: Sử dụng bảng mời bạn bè</div>		
						<span><br>
						<center><img src="<?php echo $conf['imgdir'] ?>HTW1_69.gif"></center><br>
						</span>-->	
<div class="text1">Cách 3: Dùng nút sẵn có trong game</div>		
						<span>
Ấn nút mời bạn bè trong bảng nội dung nhiệm vụ trong màn hình chơi Zing Farm <br><br>		
						<center><img src="<?php echo $conf['imgdir'] ?>HTW1_68.gif"></center><br>
Hoặc nhấn vào avatar "Mời bạn bè" trong danh sách bạn cùng chơi<br><br>
						<center><img src="<?php echo $conf['imgdir'] ?>HTW1_67.gif"></center><br>
Chọn nội dung có sẵn hoặc tự có và ấn nút "Đăng lên tường".<br><br>
<center><img src="<?php echo $conf['imgdir'] ?>HTW1_66.gif".gif"></center><br>
Sau đó lời mời sẽ được đăng lên tường. Bạn bè của bạn chỉ cần ấn vào lời mời này để bắt đầu chơi ZingFarm<br><br>
<center><img src="<?php echo $conf['imgdir'] ?>HTW1_65.gif".gif"></center><br>
						</span>					
					</div>
				</div>
				<div class="clear">&nbsp;</div>
			</div>
			<div id="zf_footer_top"></div>
		</div>		
	</div>
</div>
<?php
include TPL_DIR . "/foot.php";
?>