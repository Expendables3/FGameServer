// JavaScript Document


jQuery(document).ready(function () {

  //ajax tab control
  
    if (jQuery("#boxTab_1").length > 0) {
		
        applyAjaxTabControl("#boxTab_1 > ul#newsTab > li > a");
    }
	


   if (jQuery("div.PagingWrapper  div.CenterWrapper").length > 0) {
		
        
		loadBlockPage("#block_news_tin_dadang div.PagingWrapper  div.CenterWrapper  a");
		loadBlockPage("#block_news_tin_lienquan div.PagingWrapper  div.CenterWrapper  a");
		loadBlockPage(".BlockListNews div.PagingControl  div.CenterWrapper  a");
		
    }


});

    //apply ajax tab control function
    function loadBlockPage(selectorPattern) { //selectorPattern must have format ' [selector] ul[class|id] > li > a '
        //jTab
        jQuery(selectorPattern).each(function(index) {
            var a = jQuery(this);
			
            a.bind("click", function (evt) {
				
				//a.parent().parent().find("> li").removeClass("ui-state-active");
               // a.parent().addClass("ui-state-active");
				
				
				
	//uZ2RhbnxQSFA&amp;token=07fd86969878e4damp&amp;firstname&amp;getData.php
				var params = a.attr("rel").replace(/\r\n\s/g,"").toString(); // Thay the xuong hang
				
                var splitParams = params.split("&");
                var postData = "block={\"" + splitParams[0] + "\":{}}\&"+ splitParams[1];
                var urlInput = splitParams[2] == "''" ? '' : splitParams[2];
				var idBlogOutput=splitParams[0];
				//alert(postData);
                var clientCallback = function (targetID, response) {
                   // 
                };
               
			   var tabContent = {};

			   $.ajax({
			    type: "POST",
			    url: urlInput,
			    dataType: "json",
			    data: postData,
			    success: function(msg){
			    	
					
					//tabContent[tabName] = msg.items[tabName];
					
					
			    	$("#"+idBlogOutput).html(msg[idBlogOutput]);
					loadBlockPage(selectorPattern);
			    },error :function(err){
					//alert(err.message);
				}
			});

				
                return false;
            });
        });
    }
/* END. Tab control */
    //apply ajax tab control function
    function applyAjaxTabControl (selectorPattern) { //selectorPattern must have format ' [selector] ul[class|id] > li > a '
        //jTab
		
        jQuery(selectorPattern).each(function(index) {
            var a = jQuery(this);
			 var tabContent = {};
            a.bind("mouseover", function (evt) {
										 
				
				a.parent().parent().find("> li").removeClass("ui-state-active");
                a.parent().addClass("ui-state-active");
				
	//uZ2RhbnxQSFA&amp;token=07fd86969878e4damp&amp;firstname&amp;getData.php
				var params = a.attr("rel").replace(/\r\n\s/g,"").toString(); // Thay the xuong hang
				//alert(params);
                var splitParams = params.split("&");
                var postData = "block={\"" + splitParams[0] + "\":{}}\&"+ splitParams[1];
                var urlInput = splitParams[3] == "''" ? '' : splitParams[3];
				var idBlogOutput=splitParams[0];
				var tabName=splitParams[2];
				                
               
                var clientCallback = function (targetID, response) {
                   // 
                };
               
			  
				
			   if( !tabContent[tabName] ){
						$.ajax({
						type: "POST",
						url: urlInput,
						dataType: "json",
						data: postData,
						success: function(msg){
							
							//alert(msg.items.firstname);
							//tabContent[tabName] = msg.items[tabName];
							
							tabContent[tabName]=msg[idBlogOutput];
							
							$("#"+idBlogOutput).html(tabContent[tabName]);
						},error :function(err){
							//alert(err.message);
						}
						});
		
			   }
			   else {
					$("#"+idBlogOutput).html(tabContent[tabName]);
			   }
				return false;
			});
			
        });
    }
/* END. Tab control */

    //apply ajax paging event list
    function loadEventPage(selectorPattern) { 
	
        jQuery(selectorPattern).each(function(index) {
            var a = jQuery(this);
			
            a.bind("click", function (evt) {

				var params = a.attr("rel").replace(/\r\n\s/g,"").toString(); 
				
                var splitParams = params.split("&");
				//data: "module={$config["moduleOuputId"]}&moduleParams={}&token={$config["token"]}",
                var postData = "module={\"" + splitParams[0] + "\":{}}\&"+ splitParams[1]+"&moduleParams={}";
                var urlInput = splitParams[2] == "''" ? '' : splitParams[2];
				var idBlogOutput=splitParams[0];
				var tabContent = {};

			   $.ajax({
					type: "POST",
					url: urlInput,
					dataType: "json",
					data: postData,
					success: function(msg){
					//	$("#"+idBlogOutput).html(msg[idBlogOutput]);
						/*
						 if($("#"+idBlogOutput)){
                   			 $("#"+idBlogOutput).html(msg.idBlogOutput['content']);
							 }
               			 if($("#paging_"+idBlogOutput)){
                  			  $("#paging_"+idBlogOutput).html(msg.{$config[idBlogOutput]}['paging']);               						                         }
							  */
						loadEventPage(selectorPattern);
					},error :function(err){
						//alert(err.message);
					}
				});

				
                return false;
            });
        });
    }
/* END.apply ajax paging event list*/
/* load tab library */
function libraryLoadTab_home(selectorPattern){
	
	     
		jQuery(selectorPattern).each(function(index) {
		
			var a = jQuery(this);
			var tabContent_library = {};
            a.bind("mouseover", function (evt) {
										 
				
				a.parent().parent().find("> li").removeClass("ui-state-active");
                a.parent().addClass("ui-state-active");
				
	//MTkwODBA&token=04b5d384f6024ef2fc252e9112309daf&&amp;http://gunnynew.zing.vn/home/danh-sach.hinh-nen.html
				var params = a.attr("rel").replace(/\r\n\s/g,"").toString(); // Thay the xuong hang
				//alert(params);
                var splitParams = params.split("&");
                var postData = "block={\"" + splitParams[0] + "\":{}}\&"+ splitParams[1];
                var urlInput = splitParams[3] == "''" ? '' : splitParams[3];
				var idBlogOutput=splitParams[0];
				var tabName=splitParams[2];
				                
			
				 if( !tabContent_library[tabName] ){
					$.ajax({
						type: "POST",
						url: urlInput,
						dataType: "json",
						data: postData,
						success: function(msg){
							tabContent_library[tabName]=msg[idBlogOutput]['content'];
							$("#"+idBlogOutput).html(tabContent_library[tabName]);
							
						}
					});
				}else{
					$("#"+idBlogOutput).html(tabContent_library[tabName]);
				}
			
			});
		});
}


/*end load tab library */