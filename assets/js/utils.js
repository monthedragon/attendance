/**
PUT ANY fx JS HERE!!
**/


var pollStartButton ='';
var global_return_url = '';
var BrowserDetect =
{
    init: function () 
    {
        this.browser = this.searchString(this.dataBrowser) || "Other";
        this.version = this.searchVersion(navigator.userAgent) ||       this.searchVersion(navigator.appVersion) || "Unknown";
    },

    searchString: function (data) 
    {
        for (var i=0 ; i < data.length ; i++)   
        {
            var dataString = data[i].string;
            this.versionSearchString = data[i].subString;

            if (dataString.indexOf(data[i].subString) != -1)
            {
                return data[i].identity;
            }
        }
    },

    searchVersion: function (dataString) 
    {
        var index = dataString.indexOf(this.versionSearchString);
        if (index == -1) return;
        return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
    },

    dataBrowser: 
    [
        { string: navigator.userAgent, subString: "Chrome",  identity: "Chrome" },
        { string: navigator.userAgent, subString: "MSIE",    identity: "Explorer" },
        { string: navigator.userAgent, subString: "Firefox", identity: "Firefox" },
        { string: navigator.userAgent, subString: "Safari",  identity: "Safari" },
        { string: navigator.userAgent, subString: "Opera",   identity: "Opera" }
    ]

};
BrowserDetect.init();


function calendar_date(){
	$('.cal-date').datepicker({'dateFormat':'yy-mm-dd', 'minDate': 0});
    $('.sw-date').datepicker({'dateFormat':'yy-mm-dd'});
}



//no_return if you add any text other than the conditions it will pop to user screen
function do_ajax(url,type,data,objID,no_return){
	$.support.cors = true;
	if(type==undefined)
		type='POST';
	if(data==undefined)	
		data = '';
	if(objID==undefined)
		objID='main-panel';
	if(no_return==undefined)
		no_return=0;

//    alert(url +  ' ' + objID);

//
//    var find = '/';
//    var re = new RegExp(find, 'g');
//    var replacedUrl  = '';
//    replacedUrl = url.replace(re, '');
//
//
//    var match = replacedUrl.match(/lessonsview|time_slot_expiry|get_contact_schedules|save_student_eval|testi_form|update_show_public|studentview_list|teacher_lesson_list|view_list|delete|teacher_incentives_list|usersindex/);
//
//    if (match == '' || match == null){
//
//        if(type == 'SERIALIZED')
//            data = data;
//        else if(data != 'undefined')
//            data = $.param(data);
//        else
//            data = '';
//
//
//        window.location = url+'?'+data;
//    }


	if(BrowserDetect.browser == 'Explorer'){
		// Use Microsoft XDR
		var xdr = new XDomainRequest();
		xdr.open("get", url);
		xdr.onload = function() {
			// XDomainRequest doesn't provide responseXml, so if you need it:
			var dom = new ActiveXObject("Microsoft.XMLDOM");
			dom.async = false;
			dom.loadXML(xdr.responseText);
				if(no_return==99){ //for debugging purposes!
					alert(data);
				}else if(no_return==1){
					//do nothing!!
				}else if(no_return==0){
					$('#'+objID).html(xdr.responseText);
				}else{
					alert(no_return);
				}
		};
		xdr.send();
		
	}else{
		//url = 'main/menu';
		var request = $.ajax({
			url:url,
			data:data,
			type:type,
			 crossDomain: true,
			//dataType: "jsonp",
			//cache: false,
			success:function(data){
				if(no_return==99){ //for debugging purposes!
                    console.log(data);
					alert(data);
				}else if(no_return==1){
					//do nothing!!
				}else if(no_return==0){
					$('#'+objID).html(data);
				}else{
					alert(no_return);
				}
				
				
			} 
		});	
		
	}	
	
} 


function do_ajax_with_return(url,return_url,type,data,objID,no_return,objWait){ 
		
	//reset_user_msg();
	if(type==undefined)
		type='POST';
	if(data==undefined)	
		data = '';
	if(objID==undefined)
		objID='main-panel';
	
	var origVal = $('#'+objWait).val();
	
	//	alert(url);
	
	if(BrowserDetect.browser == 'Explorer'){
		// Use Microsoft XDR
		var xdr = new XDomainRequest();
		xdr.open("get", url);
		xdr.onload = function() {
			// XDomainRequest doesn't provide responseXml, so if you need it:
			var dom = new ActiveXObject("Microsoft.XMLDOM");
			dom.async = false;
			dom.loadXML(xdr.responseText);
				//for debugging purposes!!
				if(no_return==99)
					alert(xdr.responseText);
				else{
					//if no_return =1 meaning ther xdr.responseText should be empty before it process the return_url else it will alert and notice the user
					if(no_return==1 && xdr.responseText != ''){ //expecting a return from first ajax
						alert(xdr.responseText);
					}else{
						do_ajax(return_url,type,data,objID);
					}
				}
		};
		xdr.send();
		
	}else{
	
		$.ajax({
			url:url,
			data:data,
			type:type,
			beforeSend:function(){
				$('#'+objWait).prop('disabled',true);
				$('#'+objWait).val('please wait . . . ');
			},
			complete:function(){
				$('#'+objWait).removeProp('disabled');
				$('#'+objWait).val(origVal);
			},
			error:function(){
				alert('Please try again');
				$('#'+objWait).prop('disabled',true);
				$('#'+objWait).val(origVal);
			},
			success:function(retData){
				//for debugging purposes!!
				if(no_return==99)
					alert(retData);
				else{
					//if no_return =1 meaning ther retData should be empty before it process the return_url else it will alert and notice the user
					if(no_return==1 && retData != ''){ //expecting a return from first ajax
						alert(retData);
					}else{
						do_ajax(return_url,type,data,objID);
					}
				}
			} 
		});
		
	}
	
}


function ajax_modal(url,height,width,return_url,return_obj_id,data_return){
	
	if(height == undefined)
		height = 300; 
		
	if(width == undefined)
		width = 300;


    if(return_obj_id==undefined)
        return_obj_id='main-panel';
	
	$.ajax({
		url:url,
		success:function(data){
			$("#main-modal-panel").html(data);
			$("#main-modal-panel").modal({
				containerCss: { height:height,width: width},
				onOpen:function(dialog){  
						dialog.overlay.fadeIn('fast', function () {
							dialog.container.slideDown('slow', function () {
								dialog.data.fadeIn('slow');
							});
						});
					},
					onClose: function(dialog){
						$("#main-modal-panel").html(''); 
						dialog.container.slideUp('slow', function () {  
							$.modal.close(); // must call this! 

                            //global_return_url set this as empty everytimeyou call!
                            if(global_return_url!=''){
                                do_ajax(global_return_url,'POST',data_return,return_obj_id);
                                global_return_url='';
                            }else if(return_url != undefined){
								do_ajax(return_url,'POST',data_return,return_obj_id);
							}
						}); 
				}
			});
		}
		
	}) 
}



function renderPage(pageNumber,page_selector,selection_class)
{

   if(page_selector==undefined)    page_selector = 'page';
   if(selection_class==undefined)    selection_class = 'selection';

  var page="."+page_selector+'-'+pageNumber;
  $('.'+selection_class).hide();
  $(page).show();

}

//selector div holder to view
// itemCounts number of items
//item_per_page located in config
//page_selector class associated with page number (selection_class)
function a_link_fx(selector,itemCounts,item_per_page,page_selector,selection_class){

		var itemPerAge = Math.round(item_per_page);

		if(itemCounts > itemPerAge)
		{

			renderPage(1,page_selector,selection_class);
			$('#'+selector).pagination({
				items: itemCounts,
				itemsOnPage: item_per_page,
				cssStyle: 'compact-theme',
                prevText: 'Previous',
				onPageClick: function(pageNumber){renderPage(pageNumber,page_selector,selection_class)}
			});

		}

        hover_out_tr_fx('tr-list');
//		$(".tr-list").mouseover(function(){$(this).addClass('tr_highlight');})
//		$(".tr-list").mouseout(function(){$(this).removeClass('tr_highlight');})

}


function hover_out_fx(domClass){
		$("."+domClass).mouseover(function(){$(this).addClass('highlight_blue');})
		$("."+domClass).mouseout(function(){$(this).removeClass('highlight_blue');})
}


function hover_out_tr_fx(domClass){
		$("."+domClass).mouseover(function(){$(this).addClass('tr_highlight');})
		$("."+domClass).mouseout(function(){$(this).removeClass('tr_highlight');})
}


function input_auto_complete(idlabel,idVal,jsonData){
	$('#'+idlabel).autocomplete({
			source: jsonData,
			focus: function(event, ui) {
					// prevent autocomplete from updating the textbox
					event.preventDefault();
					// manually update the textbox
					$(this).val(ui.item.label);
			},
			select: function(event, ui) {
				// prevent autocomplete from updating the textbox
				event.preventDefault();
				// manually update the textbox and hidden field
				$(this).val(ui.item.label);
				$('#'+idVal).val(ui.item.value);
			}
		}); 
}