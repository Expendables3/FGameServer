// JavaScript Document
jQuery(document).ready(function() {
	if(jQuery("#FaceBook").length>0){
		
		jQuery("#FaceBook").click(shareFaceBook);
		
	}
});

function shareFaceBook(){
	var title= document.title;
	if(navigator.userAgent.indexOf('MSIE')!= -1) {
		winDef = 'scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,height=430,width=550,top='.concat((screen.height - 500)/2).concat(',left=').concat((screen.width - 500)/2);
	} else {
		winDef = 'scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=no,height=400,width=550,top='.concat((screen.height - 500)/2).concat(',left=').concat((screen.width - 500)/2);
	}
	var url = 'http://www.facebook.com/sharer/sharer.php?u=' + document.location.href + '&t='+title;
	
	window.open(url,'_blank',winDef);
}