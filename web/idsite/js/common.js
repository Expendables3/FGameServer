jQuery(document).ready(function () {
	
	// Call scroll popup item
	if (jQuery(".Content_BoiCanh").length > 0) {
		jQuery(".Content_BoiCanh").jScrollPane({
			scrollbarWidth : 13,
			scrollbarMargin : 10
		});
	}
	
	// tabs content
	/*if(jQuery("#boxTab_1").length >0 ){
	jQuery("#boxTab_1").tabs({
	event: 'mouseover',
	selected: 0
	});
	}
	if(jQuery("#boxTab_2").length >0 ){
	jQuery("#boxTab_2").tabs({
	event: 'mouseover',
	selected: 0
	});
	}
	if(jQuery("#boxTab_3").length >0 ){
	jQuery("#boxTab_3").tabs({
	event: 'mouseover',
	selected: 0
	});
	}
	if(jQuery("#boxTab_4").length >0 ){
	jQuery("#boxTab_4").tabs({
	event: 'mouseover',
	selected: 0
	});
	}*/
	
	jQuery("#boxTab_3 >a.ViewMore").hide();
	jQuery("#boxTab_3 >a.ViewMore").eq(0).show();
	
	if (jQuery(".TabServer").length > 0) {
		jQuery('.TabServer').initScrollItem();
		jQuery(".ListServer").eq(0).show();
		jQuery(".TabServer ul.RowServer > li").eq(0).addClass("ui-state-active");
		jQuery(".TabServer  ul.RowServer > li > a").click(function (e) {
			e.preventDefault();
			if (jQuery(this).parent().hasClass("ui-state-active")) {
				return false;
			}
			var curActiveId = jQuery(this).attr("rel");
			if (curActiveId != "") {
				jQuery(".ListServer").hide();
				jQuery(curActiveId).show();
				jQuery(".TabServer ul.RowServer li.ui-state-active").removeClass("ui-state-active");
				jQuery(this).parent().addClass("ui-state-active");
			}
		});
	}
	
	/*
	var eventImgAct = jQuery("ul#eventImg > li").eq(0);
	eventImgAct.show();
	if(jQuery("ul.ListEvent").length > 0){
	jQuery("ul.ListEvent").createSlidePanel({
	animation: true,
	actEvent: "mouseover",
	handle: "li > a.TitleEvent", //selector in context of parent object
	accordion: true,
	serialize: true,
	client_callback: function () {
	var actEventIndex = jQuery("ul.ListEvent > li > a.SlidePanelHandleActive").parent().prevAll().length;
	eventImgAct.hide();
	jQuery("ul#eventImg > li").eq(actEventIndex).show();
	eventImgAct = jQuery("ul#eventImg > li").eq(actEventIndex);
	}
	});
	}
	 */
	
	/*even header*/
	if (jQuery("#img").length > 0) {
		new FadeGallery(jQuery("#img"), {
			control_event : "mouseover",
			auto_play : true,
			control : jQuery("ul#imgControl"),
			delay : 2
		});
	}
	
	if (jQuery("#char").length > 0) {
		var flashvars = {};
		var attributes = {};
		var params = {};
		params.wmode = "transparent";
		params.allowfullscreen = "true";
		params.scale = "noscale";
		params.quality = "high";
		params.allowScriptAccess = "always";
		
		new FadeGallery(jQuery("#char"), {
			control_event : "click",
			auto_play : false,
			control : jQuery("ul#ControlNhanVat"),
			cbFunction : function (index) {
				jQuery("p.IntroLink").css({
					top : "-9999px",
					left : "-9999px"
				});
				jQuery("p.IntroLink").eq(index).css({
					top : "0px",
					left : "0px"
				});
				jQuery(".VideoHome object").remove();
				jQuery(".VideoHome > div").hide();
				jQuery(".VideoHome > div").eq(index).show();
				jQuery(".VideoHome > div > a").eq(index).show();
			}
		});
		jQuery(".VideoHome > div > a").live("click", function () {
			var flashPos = jQuery(this).parent().prevAll().length;
			var flashPath = jQuery(this).attr("rel");
			var flashID = "boxFlash" + flashPos;
			jQuery(this).before("<div id='boxFlash" + flashPos + "'>");
			jQuery(this).hide();
			swfobject.embedSWF(flashPath, flashID, "340", "160", "8.0.0", "http://img.zing.vn/volam2/images/flashheader/home/expressInstall.swf", flashvars, params, attributes);
		});
		
	}
	try {
		DD_belatedPNG.fix('#header ul#ControlNhanVat li a');
	} catch (exp) {}
	
	if (jQuery("#quickLink").length > 0) {
		jQuery("#quickLink").addScrollControl({
			initTop : 140,
			offsetTop : 10,
			animation : true,
			offsetToScroll : 690,
			offsetLeft : 820,
			RelativeID : "content"
		});
	}
	if (jQuery("#subContent #quickLink").length > 0) {
		jQuery("#subContent #quickLink").addScrollControl({
			initTop : 140,
			offsetTop : 10,
			animation : true,
			offsetToScroll : 510,
			offsetLeft : 800,
			RelativeID : "subContent"
		});
	}
	if (jQuery('#mycarousel').length > 0) {
		jQuery('#mycarousel').jcarousel();
		try {
			DD_belatedPNG.fix('.jcarousel-next-horizontal,.jcarousel-prev-horizontal');
		} catch (exp) {}
	}
	jQuery(".SmallImg").mouseover(function (evt) {
		
		var imgpos = jQuery(this).attr('id').split("_");
		imgpos = imgpos[1];
		var imgLeft = 0;
		jQuery(".ImgToolTip").hide();
		jQuery("#largeImage_" + imgpos).parent().show();
		if (jQuery(this).hasClass("ItemLeft")) {
			imgLeft = jQuery(this).offset().left - jQuery("#subContent").eq(0).offset().left - jQuery("#largeImage_" + imgpos).width() - 32;
		} else {
			imgLeft = jQuery(this).outerWidth() + jQuery(this).offset().left - jQuery("#subContent").eq(0).offset().left;
		}
		var imgTop = jQuery(this).offset().top - jQuery("#subContent").eq(0).offset().top;
		
		jQuery("#largeImage_" + imgpos).parent().css({
			top : imgTop,
			left : imgLeft
			
		});
		if (jQuery(this).hasClass("ItemLeft")) {
			jQuery("#largeImage_" + imgpos).css("background", "url(images/nhanvat-tip-top.png) 0 0 no-repeat");
			
		}
		
		evt.stopPropagation();
	});
	jQuery(".LargeImg").mouseover(function (evt) {
		jQuery(this).parent().show();
		evt.stopPropagation();
	});
	jQuery(document).mouseover(function () {
		jQuery(".LargeImg").parent().hide();
	});
	/*input search */
	if (jQuery('.TextInput').length > 0) {
		
		jQuery('.TextInput').bind('focus', function () {
			if (jQuery(this).val() == 'Tìm kiếm') {
				jQuery(this).val('');
			}
		});
		jQuery('.TextInput').bind('blur', function () {
			if (jQuery(this).val() == '') {
				jQuery(this).val('Tìm kiếm');
			}
		});
	}
	/*input search */
	if (jQuery('.TextInput01').length > 0) {
		
		jQuery('.TextInput01').bind('focus', function () {
			if (jQuery(this).val() == 'Tìm kiếm') {
				jQuery(this).val('');
			}
		});
		jQuery('.TextInput01').bind('blur', function () {
			if (jQuery(this).val() == '') {
				jQuery(this).val('Tìm kiếm');
			}
		});
	}
	/* end Box dang nhap */
	/* Box dang nhap */
	if (jQuery("#u").length > 0) {
		
		jQuery('#u').bind('focus', function () {
			if (jQuery(this).val() == 'Tài khoản Zing ID') {
				jQuery(this).val('');
			}
		});
		jQuery('#u').bind('blur', function () {
			if (jQuery(this).val() == '') {
				jQuery(this).val('Tài khoản Zing ID');
			}
		});
	}
	/*--------------------  */
	
	if (jQuery("#p").length > 0) {
		
		jQuery('#p').bind('focus', function () {
			if (jQuery(this).val() == 'Mật khẩu') {
				jQuery(this).val('');
			}
		});
		jQuery('#p').bind('blur', function () {
			if (jQuery(this).val() == '') {
				jQuery(this).val('Mật khẩu');
			}
		});
	}
	
	/* end Box dang nhap */
	/*
	if(jQuery(".jcarousel-list").length > 0){
	
	jQuery(".jcarousel-list > li").click(function(){
	jQuery(".jcarousel-list > li.Active").removeClass("Active");
	jQuery(this).addClass("Active");
	
	})
	}*/
	/*if(jQuery("a.DangKy").length >0 ){
	
	jQuery("a.DangKy").click(function(){
	var urlsharelink=jQuery(this).attr("href");
	var addclas = 'survey_popuphome';
	callSurvey2(urlsharelink,430,addclas);
	return false;
	});
	
	}*/
	if (jQuery(".SelectUI").length > 0) {
		jQuery(".SelectUI").addSelectUI({
			scrollbarWidth : 10 //default is 10
		});
	}
});

function callSurvey2(url, size, id) {
	if (size == undefined) {
		size = 430;
	}
	if (id == undefined) {
		id = 'SpamLinkPopup';
	}
	createOverlays("sub_spamlink");
	jQuery("body").append('<div id="sub_spamlink" class="survey_popup ' + id + '"><a class="SurveyClose" title="Đóng" href="#">Đóng</a><div class="SurveyContent"><iframe height="450" frameborder="0" width="' + size + '" allowtransparency="1" src=' + url + '></iframe> </div></div>');
	
	jQuery('.SurveyClose').bind('click', function () {
		closeVideo('sub_spamlink');
		jQuery("#sub_spamlink").remove();
		return false;
	});
	jQuery('#overlays').bind('click', function () {
		closeVideo('sub_spamlink');
		jQuery("#sub_spamlink").remove();
		return false;
	});
	
	return false;
	
}
function checking() {
	var cidbt = $('#User').val();
	var cpsbt = $('#Pass').val();
	if (cidbt == '' || cpsbt == '') {
		alert('Bạn chưa nhập Tài khoản hay Mật khẩu');
		return false;
	}
	if (cidbt == 'Tài khoản Zing ID' || cpsbt == 'Password' || cpsbt == 'Tài khoản') {
		alert('Bạn chưa nhập Tài khoản hay Mật khẩu');
		return false;
	}
	jQuery('#User').val('Tài khoản Zing ID');
	jQuery('#Pass').val('Password');
}
$.fn.initScrollItem = function (options) {
	var defaults = {
		duration : 500,
		itemShow : 10
	};
	options = $.extend(defaults, options);
	return this.each(function () {
		var that = $(this);
		var scrollContent = null;
		var nextButton = null;
		var prevButton = null;
		var currentIndex = 0;
		var currentView = 0;
		
		scrollContent = that.find('ul.RowServer');
		nextButton = that.find('.next');
		prevButton = that.find('.prev');
		
		var countLiTag = 0;
		var scrollContentWidth = 0;
		var aniComplete = true;
		var scrollItems = scrollContent.children();
		var liTags = scrollItems.find('li');
		prevButton.addClass('disabled');
		scrollContent.css('margin-left', 0);
		while (countLiTag < scrollItems.length) {
			scrollContentWidth += scrollItems.eq(countLiTag).outerWidth(true);
			countLiTag++;
		}
		scrollContent.css({
			'width' : scrollContentWidth
		});
		
		prevButton.unbind('click').bind('click', function (e) {
			if (e)
				e.preventDefault();
			nextButton.removeClass('disabled');
			if (!aniComplete)
				return;
			if (currentView == 1) {
				prevButton.addClass('disabled');
			}
			if (parseInt(scrollContent.css('margin-left')) != 0) {
				aniComplete = false;
				scrollContent.stop().animate({
					'margin-left' : parseInt(scrollContent.css('margin-left')) + scrollItems.eq(currentView - 1).outerWidth(true)
				},
					options.duration,
					function () {
					aniComplete = true;
					currentView--;
				});
			}
		});
		nextButton.unbind('click').bind('click', function (e) {
			if (e)
				e.preventDefault();
			prevButton.removeClass('disabled');
			if (!aniComplete)
				return;
			if (currentView == scrollItems.length - 1 - options.itemShow) {
				nextButton.addClass('disabled');
			}
			if (currentView <= scrollItems.length - 1 - options.itemShow) {
				aniComplete = false;
				scrollContent.stop().animate({
					'margin-left' : parseInt(scrollContent.css('margin-left')) - (scrollItems.eq(currentView).outerWidth(true))
				}, options.duration, function () {
					aniComplete = true;
					currentView++;
				});
			}
		});
	});
};
