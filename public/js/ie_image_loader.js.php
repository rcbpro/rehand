<?php 
	header('Content-type: text/javascript');
    session_start();
	defined('WEB_PATH') ? NULL : define('WEB_PATH', 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/');
?>
$(document).ready(function(){
	
	var notifyLinkClick = false,
    	contactButtonClick = true;
    
	loadIamges();

    function loadIamges() {
    	
		var content = "";
		var selectedTagId = null;
        var qString = '<?php echo WEB_PATH?>library/ajaxFunctions.php?action=loadImages';        
        $.getJSON(qString, function(data) {
           
			if (data){
			
                $.each(data, function(i) {	
				
                    content = "";
                    content += "<li class='box photo col3'><div class='GalMain'><strong><!-- Image title should goes here --></strong><a href='#' class='GalShareSmall'></a><a href='<?php echo WEB_PATH?>"+ data[i].uploadLImgLocation + "' id='" + data[i].idhash + "' rel='example_group'";
					content += " class='GalViewSmall'></a><div class='clearH10'></div><div class='GalImg'><div class='hoverlinks'><a href='<?php echo WEB_PATH?>"+ data[i].uploadLImgLocation + "' id='" + data[i].idhash + "' rel='example_group'"; 
					content += " class='GalView'></a><a href='#' class='GalShare'></a></div>";
					content += "<img id='"+data[i].idhash+"' src='<?php echo WEB_PATH?>" + data[i].uploadTImgLocation + "'/></div>";			                                      
					content += "<div class='clearH15'></div>";
					content += "<div class='floatr'>";
					if (data[i].postCode != ""){
						content += "<span class='PostCode'>post code <strong>" + data[i].postCode + "</strong></span>";
					}
					content += "<div class='clearH10'></div><span class='Intrested'><strong><span id='noOfInterests'>" + data[i].wholeNoOfInterests + "</span></strong> Interested</span></div>";
					content += "<div class='ownedby'>";
                    //content += "<img style='width:36px; height:36px;' src='<?php echo WEB_PATH?>" + data[i].profileImage + "' />";
                    content += "<div class='ownedbymain'><div class='clear'></div><span><a href='#' rel='" + data[i].idhash + "' class='sellerName'>message seller</a></span></div></div><div class='clear'></div></div>";
                    content += "<div id='sellerInfoWrapper_" + data[i].idhash + "' class='sellerContactInfoWrapper hide'></div>";

                    if (data[i].pictureRelatedTags){
                    	n = 0;
                        $.each(data[i].pictureRelatedTags, function(n) {
                            content += "<a href='#' class='TagsH'><span lang='" + data[i].pictureRelatedTags[n].tagId + "' class='TagHName " + data[i].idhash + "'>" + data[i].pictureRelatedTags[n].tagName + "</span><span lang=' " + data[i].pictureRelatedTags[n].tagId + "' class='Price " + data[i].idhash + "'>" + data[i].pictureRelatedTags[n].price + "</span></a>";
                        });
                    }
					content += "<div class='clearH5'></div></li>";                                 
                    $(content).hide().appendTo('#container').fadeIn(500);
					
					$container = $('#container');
                    $container.imagesLoaded(function(){
                      $container.masonry({
                        itemSelector: '.box',
                        isAnimated: true
                      });
                    })
                    
                    $("a[rel=example_group]").fancybox({
                        'transitionIn'		: 'elastic',
                        'transitionOut'		: 'elastic',
                        'titlePosition'		: 'outside',
                        'overlayColor'		: '#000',
                        'overlayOpacity'	: 0.8,
                        'hideOnContentClick': false,
                        'autoScale':false,
                        'showNavArrows':false,
                        'cyclic':false,
                        'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
                            fancyContent = "";
                            
                        },
                        onCleanup:function(ele){
                            ele.find(".tm-tagbox").remove();
                            ele.find(".tm-tagpanel").remove();
                            ele.find(".tagItem-1").remove();
                        },
                        onComplete:function(ele,x1,x2,e){
                        	
                            $($(document).find("#fancybox-outer")).TagMe({
                                id:$(e.orig).attr('id'),
                                loadTags:true,
                                loadTagsAction:{
                                                    url:"<?php echo WEB_PATH?>library/tagMeCall.php",
                                                    onProgress:function(ele){},
                                                    onSuccess:function(ele){
                                                    	//$("#".selectedTagId).trigger('click');
                                                        $(ele).find("#"+selectedTagId).trigger('mouseover');
                                                    },
                                                    onFail:function(ele){},
                                                    json:true		
                                },
                                onTagAdded:function(ele,data){
                                	
                                    ele.removeClass();
                                    ele.addClass("tagItem-1");
                                	switch(data.itemStatus){
                                    	case "available":
                                        	ele.html(data.itemPrice);
                                            ele.addClass("itemToSell");
                                        break;    
                                        case "free":
                                        	ele.addClass("itemFree");
                                        break;
                                        case "sold":
                                        	ele.addClass("itemSold");    
                                    	break;
                                    }
                                },
                                
                                tagpanelAction:{
                                                    url:"<?php echo WEB_PATH?>library/tagMeCall.php",
                                                    submitFormId:'tm-tagpanel-form',
                                                    onProgress:function(ele){},
                                                    onSuccess:function(ele){
                                                    },
                                                    onFail:function(ele){},
                                                    json:true		
                                },
                                tagpanelElement: '<div class="tm-tagpanel">'+
                                    '<form id="tm-tagpanel-form">'+
                                        '<span class="tagpanelTitle">Item Name</span>:<span id="itemName"></span><div class="clearH10"></div>'+
                                        '<span class="tagpanelTitle">Description</span>:<span id="itemDescription"></span><div class="clearH10"></div>'+									 
                                        '<span class="tagpanelTitle">Price</span>:<span id="itemPrice"></span><div class="clearH10"></div>'+
                                        '<span class="tagpanelTitle">Qty</span>:<span id="itemQty" ></span><div class="clearH"></div>'+
										<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?> 
                                        '<input type="button" id="buybtn" value="Buy Now" class="GreenBut buyNowBtn" style="float:right;" />' + 
                                        <?php endif;?>
                                        '<div class="clearH15"></div><span class="Intrested" style="min-width:inherit;"><span id="noOfInterests"><strong></strong></span> Interested</span>'+
                                    '</form>'+
                                '</div>',
                                tagpanelOnAfterInitPanel:function(ele,data){
                                	
                                    if (data.tagData.itemStatus == "sold"){
                                    	ele.find('.GreenBut').remove();
                                    }else if (data.tagData.itemStatus == "free"){
                                    	ele.find('.GreenBut').val('I am Interested');
                                    }
                                    // This is for the notifications sending after clicking the buy now button
                                    $(".buyNowBtn").click(function(){
                                        var notificationSub = "";
                                        $.ajax({  
                                            type: "POST",
                                            url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=buyNotify", 
                                            data: "tagId=" + data.tagData.tagId + "&itemStatus=" + data.tagData.itemStatus, 
                                            //async: true,
                                            success: function(notifyRes){				
                                                if (notifyRes != ''){
                                                    $("#notiticationsDiv").html("");
                                                    loadAllNotifications();
                                                }
                                            }
                                        });
                                    });
                                },
                            });
                        }
                    }); 
                });
				
                // This is to load the related image with pointing tag when some one clicks on tag
                $(".TagHName").click(function(){
                    var aId = $(this).attr('class').replace('TagHName', '');
                    var tId = $(this).attr('lang');
                    selectedTagId = $.trim(tId);
                    $("#"+$.trim(aId)).trigger('click');
                });
                $(".Price").click(function(){
                    var aId = $(this).attr('class').replace('Price', '');
                    var tId = $(this).attr('lang');
                    selectedTagId = $.trim(tId);
                    $("#"+$.trim(aId)).trigger('click');
                });
                    // This is for the message seller functionality
					$(".sellerName").click(function(){
						var imageId = $(this).attr('rel');
					<?php if ((!isset($_SESSION['currentUser'])) && (!isset($_SESSION['fbUser']))):?>
						$.ajax({  
							type: "POST",
							url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sellerInfo", 
							success: function(server_response){
								if (server_response != ''){
									$(".sellerContactInfoWrapper").addClass("hide");
									$("#sellerInfoWrapper_" + imageId).removeClass("hide");
									$("#sellerInfoWrapper_" + imageId).html(server_response);
									//This is to show and hide email text
									$(".buyerInfoEmailWrite").addClass("hide");
									$(".buyerInfoEmailView").focus(function(){
										$(".buyerInfoEmailView").addClass("hide");
										$(".buyerInfoEmailWrite").removeClass("hide");
										$(".buyerInfoEmailWrite").focus();
									});
									$(".buyerInfoEmailWrite").focusout(function(){
										if ($(".buyerInfoEmailWrite").val() ==""){
											$(".buyerInfoEmailView").removeClass("hide");
											$(".buyerInfoEmailWrite").addClass("hide");
										}
									});
									//This is to show and hide message text
									$(".buyerInfoMessageWrite").addClass("hide");
									$(".buyerInfoMessageView").focus(function(){
										$(".buyerInfoMessageView").addClass("hide");
										$(".buyerInfoMessageWrite").removeClass("hide");
										$(".buyerInfoMessageWrite").focus();
									});
									$(".buyerInfoMessageWrite").focusout(function(){
										if ($(".buyerInfoMessageWrite").val() ==""){
											$(".buyerInfoMessageView").removeClass("hide");
											$(".buyerInfoMessageWrite").addClass("hide");
										}
									});
									// This is to send the mail to the seller from the buyer
									$(".contactBtn").click(function(){
										contactButtonClick = true;
										if (
											(($(".buyerInfoMessageWrite").val() != "") && ($(".buyerInfoMessageWrite").val() != "Message")) &&
											(($(".buyerInfoEmailWrite").val() != "") && ($(".buyerInfoEmailWrite").val() != "Email")) 
										   ){
											$.ajax({  
													type: "POST",
													data: "buyerEmail=" + $(".buyerInfoEmailWrite").val() + "&messageFromBuyer=" + $(".buyerInfoMessageWrite").val(),
													url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
													success: function(server_response){				
														if (server_response == '1'){
															$("#tagPanelMesseging").removeClass("hide");
															$("#tagPanelMesseging").removeClass("errorMsgInTag");
															$("#tagPanelMesseging").addClass("successMsgInTag");
															$("#tagPanelMesseging").fadeIn("slow");
															$("#tagPanelMesseging").html("Your message has been delivered to the seller !");
															setTimeout(function(){
																$("#tagPanelMesseging").fadeOut("slow");
															}, 3000);
														}
													}
												});
										}else{
											  $("#tagPanelMesseging").removeClass("hide");
											  $("#tagPanelMesseging").removeClass("successMsgInTag");
											  $("#tagPanelMesseging").addClass("errorMsgInTag");
											  $("#tagPanelMesseging").fadeIn("slow");
											  $("#tagPanelMesseging").html("Please fill the form !");
											  setTimeout(function(){
												  $("#tagPanelMesseging").fadeOut("slow");
											  }, 3000);
										} 	        
									})
								}
							}
						});
					<?php else:?>
						$.ajax({  
							type: "POST",
							url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sellerInfoSendWhenLogged", 
							success: function(server_response){
								if (server_response != ''){
									$(".sellerContactInfoWrapper").addClass("hide");
									$("#sellerInfoWrapper_" + imageId).removeClass("hide");
									$("#sellerInfoWrapper_" + imageId).html(server_response);
									//This is to show and hide message text
									$(".buyerInfoMessageWrite").addClass("hide");
									$(".buyerInfoMessageView").focus(function(){
										$(".buyerInfoMessageView").addClass("hide");
										$(".buyerInfoMessageWrite").removeClass("hide");
										$(".buyerInfoMessageWrite").focus();
									});
									$(".buyerInfoMessageWrite").focusout(function(){
										if ($(".buyerInfoMessageWrite").val() ==""){
											$(".buyerInfoMessageView").removeClass("hide");
											$(".buyerInfoMessageWrite").addClass("hide");
										}
									});
									// This is to send the mail to the seller from the buyer
									$(".contactBtn").click(function(){
										contactButtonClick = true;
										if (($(".buyerInfoMessageWrite").val() != "") && ($(".buyerInfoMessageWrite").val() != "Message")){
											$.ajax({  
													type: "POST",
													data: "messageFromBuyer=" + $(".buyerInfoMessageWrite").val(),
													url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
													success: function(server_response){				
														if (server_response == '1'){
															$("#tagPanelMesseging").removeClass("hide");
															$("#tagPanelMesseging").removeClass("errorMsgInTag");
															$("#tagPanelMesseging").addClass("successMsgInTag");
															$("#tagPanelMesseging").fadeIn("slow");
															$("#tagPanelMesseging").html("Your message has been delivered to the seller !");
															setTimeout(function(){
																$("#tagPanelMesseging").fadeOut("slow");
															}, 3000);
														}
													}
												});
										}else{
											  $("#tagPanelMesseging").removeClass("hide");
											  $("#tagPanelMesseging").removeClass("successMsgInTag");
											  $("#tagPanelMesseging").addClass("errorMsgInTag");
											  $("#tagPanelMesseging").fadeIn("slow");
											  $("#tagPanelMesseging").html("Please fill the form !");
											  setTimeout(function(){
												  $("#tagPanelMesseging").fadeOut("slow");
											  }, 3000);
										} 	        
									})
								}
							}
						});
					<?php endif;?>    
					});
            }else{
				content = "<span class=searchError>No matched results found !</span>";
				$('#container').html(content);
				$('#load-more').css({"display" : "none"});			
			}	
        });		
    }
	
    $("#SearchT").keypress(function(event) {
  		if (event.which == 13) {
   			$("#SearchB").trigger("click");		
        }
	});

    // This is for the search filtering process
    $("#SearchB").click(function(){
    
    	if (($("#SearchT").val() != "Search Rehands...") && ($("#SearchT").val() != "")){
			$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" align="absmiddle">&nbsp;Checking availability...');		
           	$("#container").html("");
            qString = '<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImagesAfterFiltering&searchQ=' + $("#SearchT").val();        
            $.getJSON(qString, function(data) {
                if (data){
                
                    $.each(data, function(i) {	
                    
                        content = "";
                        content += "<li class='box photo col3'><div class='GalMain'><strong><!-- Image title should goes here --></strong><a href='#' class='GalShareSmall'></a><a href='<?php echo WEB_PATH?>"+ data[i].uploadLImgLocation + "' id='" + data[i].idhash + "' rel='example_group'";
                        content += " class='GalViewSmall'></a><div class='clearH10'></div><div class='GalImg'><div class='hoverlinks'><a href='<?php echo WEB_PATH?>"+ data[i].uploadLImgLocation + "' id='" + data[i].idhash + "' rel='example_group'"; 
                        content += " class='GalView'></a><a href='#' class='GalShare'></a></div>";
                        content += "<img id='"+data[i].idhash+"' src='<?php echo WEB_PATH?>" + data[i].uploadTImgLocation + "'/></div>";			                                      
                        content += "<div class='clearH15'></div>";
                        content += "<div class='floatr'>";
                        if (data[i].postCode != ""){
                            content += "<span class='PostCode'>post code <strong>" + data[i].postCode + "</strong></span>";
                        }
						content += "<div class='clearH10'></div><span class='Intrested'><strong><span id='noOfInterests'>" + data[i].wholeNoOfInterests + "</span></strong> Interested</span></div>";
						content += "<div class='ownedby'>";
						//content += "<img style='width:36px; height:36px;' src='<?php echo WEB_PATH?>" + data[i].profileImage + "' />";
						content += "<div class='ownedbymain'><div class='clear'></div><span><a href='#' rel='" + data[i].idhash + "' class='sellerName'>message seller</a></span></div></div><div class='clear'></div></div>";
						content += "<div id='sellerInfoWrapper_" + data[i].idhash + "' class='sellerContactInfoWrapper hide'></div>";
                        if (data[i].pictureRelatedTags){
                            n = 0;
                            $.each(data[i].pictureRelatedTags, function(n) {
                                content += "<a href='#' class='TagsH'><span lang='" + data[i].pictureRelatedTags[n].tagId + "' class='TagHName " + data[i].idhash + "'>" + data[i].pictureRelatedTags[n].tagName + "</span><span lang=' " + data[i].pictureRelatedTags[n].tagId + "' class='Price " + data[i].idhash + "'>" + data[i].pictureRelatedTags[n].price + "</span></a>";
                            });
                        }
                        content += "<div class='clearH5'></div></li>";                                 
                        $(content).hide().appendTo('#container').fadeIn(500);
                        $container = $('#container');
                        $container.imagesLoaded(function(){
                          $container.masonry({
                            itemSelector: '.box',
                            isAnimated: true
                          });
                        })
                        
                        $("a[rel=example_group]").fancybox({
                            'transitionIn'		: 'elastic',
                            'transitionOut'		: 'elastic',
                            'titlePosition'		: 'outside',
                            'overlayColor'		: '#000',
                            'overlayOpacity'	: 0.8,
                            'hideOnContentClick': false,
                            'autoScale':false,
                            'showNavArrows':false,
                            'cyclic':false,
                            'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
                                fancyContent = "";
                                
                            },
                            onCleanup:function(ele){
                                ele.find(".tm-tagbox").remove();
                                ele.find(".tm-tagpanel").remove();
                                ele.find(".tagItem-1").remove();
                            },
                            onComplete:function(ele,x1,x2,e){
                                
                                $($(document).find("#fancybox-outer")).TagMe({
                                    id:$(e.orig).attr('id'),
                                    loadTags:true,
                                    loadTagsAction:{
                                                        url:"<?php echo WEB_PATH?>library/tagMeCall.php",
                                                        onProgress:function(ele){},
                                                        onSuccess:function(ele){
                                                            //$("#".selectedTagId).trigger('click');
                                                            $(ele).find("#"+selectedTagId).trigger('mouseover');
                                                        },
                                                        onFail:function(ele){},
                                                        json:true		
                                    },
                                    onTagAdded:function(ele,data){
                                        
                                        ele.removeClass();
                                        ele.addClass("tagItem-1");
                                        switch(data.itemStatus){
                                            case "available":
                                                ele.html(data.itemPrice);
                                                ele.addClass("itemToSell");
                                            break;    
                                            case "free":
                                                ele.addClass("itemFree");
                                            break;
                                            case "sold":
                                                ele.addClass("itemSold");    
                                            break;
                                        }
                                    },
                                    
                                    tagpanelAction:{
                                                        url:"<?php echo WEB_PATH?>library/tagMeCall.php",
                                                        submitFormId:'tm-tagpanel-form',
                                                        onProgress:function(ele){},
                                                        onSuccess:function(ele){
                                                        },
                                                        onFail:function(ele){},
                                                        json:true		
                                    },
                                    tagpanelElement: '<div class="tm-tagpanel">'+
                                        '<form id="tm-tagpanel-form">'+
                                            '<span class="tagpanelTitle">Item Name</span>:<span id="itemName"></span><div class="clearH10"></div>'+
                                            '<span class="tagpanelTitle">Description</span>:<span id="itemDescription"></span><div class="clearH10"></div>'+									 
                                            '<span class="tagpanelTitle">Price</span>:<span id="itemPrice"></span><div class="clearH10"></div>'+
                                            '<span class="tagpanelTitle">Qty</span>:<span id="itemQty" ></span><div class="clearH"></div>'+
                                            <?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?> 
                                            '<input type="button" id="buybtn" value="Buy Now" class="GreenBut buyNowBtn" style="float:right;" />' + 
                                            <?php endif;?>
                                            '<div class="clearH15"></div><span class="Intrested" style="min-width:inherit;"><span id="noOfInterests"><strong></strong></span> Interested</span>'+
                                        '</form>'+
                                    '</div>',
                                    tagpanelOnAfterInitPanel:function(ele,data){
                                        
                                        if (data.tagData.itemStatus == "sold"){
                                            ele.find('.GreenBut').remove();
                                        }else if (data.tagData.itemStatus == "free"){
                                            ele.find('.GreenBut').val('I am Interested');
                                        }
                                        // This is for the notifications sending after clicking the buy now button
                                        $(".buyNowBtn").click(function(){
                                            var notificationSub = "";
                                            $.ajax({  
                                                type: "POST",
                                                url: "<?php echo WEB_PATH?>library/ajaxFunctions.php?action=buyNotify", 
                                                data: "tagId=" + data.tagData.tagId + "&itemStatus=" + data.tagData.itemStatus, 
                                                //async: true,
                                                success: function(notifyRes){				
                                                    if (notifyRes != ''){
                                                        $("#notiticationsDiv").html("");
                                                        loadAllNotifications();
                                                    }
                                                }
                                            });
                                        });
                                    },
                                });
                            }
                        }); 
                    });
                    // This is to load the related image with pointing tag when some one clicks on tag
                    $(".TagHName").click(function(){
                        var aId = $(this).attr('class').replace('TagHName', '');
                        var tId = $(this).attr('lang');
                        selectedTagId = $.trim(tId);
                        $("#"+$.trim(aId)).trigger('click');
                    });
                    $(".Price").click(function(){
                        var aId = $(this).attr('class').replace('Price', '');
                        var tId = $(this).attr('lang');
                        selectedTagId = $.trim(tId);
                        $("#"+$.trim(aId)).trigger('click');
                    });
                   // This is for the message seller functionality
					$(".sellerName").click(function(){
						var imageId = $(this).attr('rel');
					<?php if ((!isset($_SESSION['currentUser'])) && (!isset($_SESSION['fbUser']))):?>
						$.ajax({  
							type: "POST",
							url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sellerInfo", 
							success: function(server_response){
								if (server_response != ''){
									$(".sellerContactInfoWrapper").addClass("hide");
									$("#sellerInfoWrapper_" + imageId).removeClass("hide");
									$("#sellerInfoWrapper_" + imageId).html(server_response);
									//This is to show and hide email text
									$(".buyerInfoEmailWrite").addClass("hide");
									$(".buyerInfoEmailView").focus(function(){
										$(".buyerInfoEmailView").addClass("hide");
										$(".buyerInfoEmailWrite").removeClass("hide");
										$(".buyerInfoEmailWrite").focus();
									});
									$(".buyerInfoEmailWrite").focusout(function(){
										if ($(".buyerInfoEmailWrite").val() ==""){
											$(".buyerInfoEmailView").removeClass("hide");
											$(".buyerInfoEmailWrite").addClass("hide");
										}
									});
									//This is to show and hide message text
									$(".buyerInfoMessageWrite").addClass("hide");
									$(".buyerInfoMessageView").focus(function(){
										$(".buyerInfoMessageView").addClass("hide");
										$(".buyerInfoMessageWrite").removeClass("hide");
										$(".buyerInfoMessageWrite").focus();
									});
									$(".buyerInfoMessageWrite").focusout(function(){
										if ($(".buyerInfoMessageWrite").val() ==""){
											$(".buyerInfoMessageView").removeClass("hide");
											$(".buyerInfoMessageWrite").addClass("hide");
										}
									});
									// This is to send the mail to the seller from the buyer
									$(".contactBtn").click(function(){
										contactButtonClick = true;
										if (
											(($(".buyerInfoMessageWrite").val() != "") && ($(".buyerInfoMessageWrite").val() != "Message")) &&
											(($(".buyerInfoEmailWrite").val() != "") && ($(".buyerInfoEmailWrite").val() != "Email")) 
										   ){
											$.ajax({  
													type: "POST",
													data: "buyerEmail=" + $(".buyerInfoEmailWrite").val() + "&messageFromBuyer=" + $(".buyerInfoMessageWrite").val(),
													url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
													success: function(server_response){				
														if (server_response == '1'){
															$("#tagPanelMesseging").removeClass("hide");
															$("#tagPanelMesseging").removeClass("errorMsgInTag");
															$("#tagPanelMesseging").addClass("successMsgInTag");
															$("#tagPanelMesseging").fadeIn("slow");
															$("#tagPanelMesseging").html("Your message has been delivered to the seller !");
															setTimeout(function(){
																$("#tagPanelMesseging").fadeOut("slow");
															}, 3000);
														}
													}
												});
										}else{
											  $("#tagPanelMesseging").removeClass("hide");
											  $("#tagPanelMesseging").removeClass("successMsgInTag");
											  $("#tagPanelMesseging").addClass("errorMsgInTag");
											  $("#tagPanelMesseging").fadeIn("slow");
											  $("#tagPanelMesseging").html("Please fill the form !");
											  setTimeout(function(){
												  $("#tagPanelMesseging").fadeOut("slow");
											  }, 3000);
										} 	        
									})
								}
							}
						});
					<?php else:?>
						$.ajax({  
							type: "POST",
							url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sellerInfoSendWhenLogged", 
							success: function(server_response){
								if (server_response != ''){
									$(".sellerContactInfoWrapper").addClass("hide");
									$("#sellerInfoWrapper_" + imageId).removeClass("hide");
									$("#sellerInfoWrapper_" + imageId).html(server_response);
									//This is to show and hide message text
									$(".buyerInfoMessageWrite").addClass("hide");
									$(".buyerInfoMessageView").focus(function(){
										$(".buyerInfoMessageView").addClass("hide");
										$(".buyerInfoMessageWrite").removeClass("hide");
										$(".buyerInfoMessageWrite").focus();
									});
									$(".buyerInfoMessageWrite").focusout(function(){
										if ($(".buyerInfoMessageWrite").val() ==""){
											$(".buyerInfoMessageView").removeClass("hide");
											$(".buyerInfoMessageWrite").addClass("hide");
										}
									});
									// This is to send the mail to the seller from the buyer
									$(".contactBtn").click(function(){
										contactButtonClick = true;
										if (($(".buyerInfoMessageWrite").val() != "") && ($(".buyerInfoMessageWrite").val() != "Message")){
											$.ajax({  
													type: "POST",
													data: "messageFromBuyer=" + $(".buyerInfoMessageWrite").val(),
													url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
													success: function(server_response){				
														if (server_response == '1'){
															$("#tagPanelMesseging").removeClass("hide");
															$("#tagPanelMesseging").removeClass("errorMsgInTag");
															$("#tagPanelMesseging").addClass("successMsgInTag");
															$("#tagPanelMesseging").fadeIn("slow");
															$("#tagPanelMesseging").html("Your message has been delivered to the seller !");
															setTimeout(function(){
																$("#tagPanelMesseging").fadeOut("slow");
															}, 3000);
														}
													}
												});
										}else{
											  $("#tagPanelMesseging").removeClass("hide");
											  $("#tagPanelMesseging").removeClass("successMsgInTag");
											  $("#tagPanelMesseging").addClass("errorMsgInTag");
											  $("#tagPanelMesseging").fadeIn("slow");
											  $("#tagPanelMesseging").html("Please fill the form !");
											  setTimeout(function(){
												  $("#tagPanelMesseging").fadeOut("slow");
											  }, 3000);
										} 	        
									})
								}
							}
						});
					<?php endif;?>    
					});
                }else{
                    content = "<span class=searchError>No matched results found !</span>";
                    $('#container').html(content);
                    $('#load-more').css({"display" : "none"});			
                }	
        	});	
        }    	               	
    });

    <?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
    loadAllNotifications();
    <?php endif;?>
    
    // This is for the notifications loading at page loading
    function loadAllNotifications() {
    
    	content = "";
        qString = '<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadAllNots';        
        $.getJSON(qString, function(data) {
            if (data){
            	$("#notiticationsDiv").html("");
            	$.each(data.allNotifications, function(i) {	
                	content += "<div>" + data.allNotifications[i].notificationText + "</div>";
                });
                content += "<a href='<?php echo WEB_PATH?>users/notifications/' class='NotificationsShowA'>Show All Notifications</a>";
               	$("#notiticationsDiv").html(content);
                if (data.notificationUnviewedCount != "0"){
                	$("#unreadableNotificationsCount").addClass("Notifications");        
                	$("#unreadableNotificationsCount").html(data.notificationUnviewedCount);
				}else{
                	$("#unreadableNotificationsCount").html("");
					$("#unreadableNotificationsCount").removeClass("Notifications");                
                }
            }
        });
        setTimeout(function(){
    		loadAllNotifications();
    	}, 5000);        
    }
    
    // This is to show notification panel when click the notification link
    $("#myNotifications").click(function(e){
    	// Unreaded Notifications count clearing
        notifyLinkClick = true;
        if ($("#unreadableNotificationsCount").html() != ""){
        	$.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=clearingTheNotificationsCount", 
                    success: function(server_response){				
                        if (server_response == '1'){
                            $("#unreadableNotificationsCount").removeClass("Notifications");
   	     					$("#unreadableNotificationsCount").html("");
                        }
                    }
                });
        }
        e.stopPropagation();
        if (notifyLinkClick){
            $("#notiticationsDiv").removeClass("hide");
            $("#notiticationsDiv").removeAttr("class");
        	//$(".notificationSub").removeClass("hide");
            //$(".notificationSub").removeAttr("class");
        	notifyLinkClick = true;
        }else{
        	$("#notiticationsDiv").addClass("hide");
            //$(".notificationSub").addClass("hide");
        	notifyLinkClick = false;
        }
    });
	$(document).click(function(){
    	if (!contactButtonClick){
	    	$(".sellerContactInfoWrapper").removeClass("hide");
            contactButtonClick = false;
    	}else{
	    	$(".sellerContactInfoWrapper").addClass("hide");
            contactButtonClick = true;
        }
        $("#notiticationsDiv").addClass("hide");
        if (!notifyLinkClick){
        	notifyLinkClick = true;
        }else{
        	notifyLinkClick = false;
        }
    });
});