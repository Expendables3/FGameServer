jQuery(document).ready(function(){
	/* Box dang nhap */
	if ( jQuery("#user").length >0){	
		jQuery('#user').bind('focus',function(){
			if(jQuery(this).val()=='Tài khoản Zing ID') {
				jQuery(this).val('');
			}
		});
		jQuery('#user').bind('blur',function(){
			if(jQuery(this).val()=='') {
				jQuery(this).val('Tài khoản Zing ID');
			}
		});
    }
	/*--------------------  */
	
	if ( jQuery("#pass").length >0){		
		jQuery('#pass').bind('focus',function(){
			if(jQuery(this).val()=='Mật khẩu') {
				jQuery(this).val('');
			}
		});
		jQuery('#pass').bind('blur',function(){
			if(jQuery(this).val()=='') {
				jQuery(this).val('Mật khẩu');
			}
		});
	}
	
});

	function checkingtop(){
		var cid = $('#user' ).val();
		var cidTitle = $('#user' ).attr( "title" );
		var cps = $('#pass' ).val();
		var cpsTitle = $('#pass' ).attr( "title" );
				
			if(cid =='' || cps=='' ){
				alert('Bạn chưa nhập Tài khoản hay Mật khẩu' );
				return false;		
			}
			if(cid ==cidTitle || cps==cpsTitle){
				alert('Bạn chưa nhập Tài khoản hay Mật khẩu' );
				return false;		
			}
	}
