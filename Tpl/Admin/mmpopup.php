<script type="text/javascript">
$(document).ready(function() {			   
	   $("input[name='lock']").click(function(event){
           var url = '?mod=Index&act=lockM';
		   var status=$(this).attr('id');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'status_main': status,
                    'content_main': $('#content_main').val()
                   },                
                dataType: 'html',
                beforeSend: function (){
                       $('#waiting_result').html("<b>Đang xử lý</b>");
                },
                success: function(res) {
                   res=$.trim(res);
				//  alert(res);
                   switch(res){                              
                   case "Successfull": 
                	    $('#waiting_result').html("Thực hiện thành công!"); 
                         window.location.reload();
                         document.frm_submit.reset();
                      break; 
                   default:
                	    $('#waiting_result').html("<span style='color:red'>"+res+"</span>");
                        break;     
                   }
                }
            });
            return false;
	   });						   
 });
</script>
<?php
$content_main='';
$status_main=0;
if(is_array($l_main) && count($l_main)>0)
{
	$content_main=isset($l_main['content'])? $l_main['content']:''; 
	$status_main=isset($l_main['status'])? $l_main['status']:0; 
}
?>
<form id="frm_submit" name="frm_submit">
<table>
 <tr>
     <td width="100px">Nội dung </td>
     <td width="320px"> <textarea name="content_main" id="content_main" style="width:305px"><?php echo $content_main;?></textarea> </td>
 </tr>
  <tr>
     <td>&nbsp; </td>
     <td> 
         <input  value="Khóa" type="button" name="lock" id="1" /> 
         <input  value="Mở khóa" type="button" name="lock" id="0"/>         	
         <input  onclick="$.facebox.close();"  value="Đóng" type="button"/> 
	</td>
 </tr>
 <tr><td colspan="2"><div id=waiting_result></div></td></tr>
</form>