var urlArray = ['gunny.zing.vn','volam.zing.vn','pk.net.vn','thankhuc.com.vn','vlcm.zing.vn','ngoalong.zing.vn','tienthe.vn','thathung.com.vn','hungba.com.vn','tienlo.com.vn','longtuong.com.vn','vlnb.com.vn','loanthe.com.vn','vltc.com.vn','3q.com.vn','thapdien.vn']; // Direct url


jQuery(document).ready(function(){		
	showTabLogin("tabQuickReg");	
	if ( jQuery("ul#tabIdLogin li a").length > 0 ) {
		jQuery("ul#tabIdLogin li a").bind ("click", function () {
			var liTab= jQuery(this).attr("rel");		
			jQuery("ul#tabIdLogin li").removeClass("Active");
			jQuery(this).parent().addClass("Active");
			jQuery(".TabContent").hide();
			jQuery("#" +liTab).show();
			return false;	
		})
	}
	if ( jQuery("a.ChangeTab").length > 0 ) {
		jQuery("a.ChangeTab").bind ("click", function () {
			var liTab= jQuery(this).attr("rel");		
			showTabLogin(liTab);	
			return false;		
		})
	}
	
})

function showTabLogin(liTab){
	jQuery("ul#tabIdLogin li").removeClass("Active");
	var linkDoc= jQuery('ul#tabIdLogin li a');
		
	jQuery.each(linkDoc, function(i, val) { 
		if( jQuery(this).attr("rel")==liTab){
			jQuery(this).parent().addClass("Active");
		}
	})	
	jQuery(".TabContent").hide();
	jQuery("#" +liTab).show();		

}