<?php 
	header('Content-type: text/javascript');
    session_start();
	defined('WEB_PATH') ? NULL : define('WEB_PATH', 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com');
	/*$expire = 60 * 60 * 24 * 7;
	header('Pragma: public');
	header('Cache-Control: maxage='.$expire);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expire) . ' GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	*/
?>
$(document).ready(function() {

	var piclinkClicked = false,
    	usersProfLinkClicked = false,
        loginClicked = false,
        regClicked = false,
        emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,    
    	userIdAvailability = false,
        userIdAvailability2 = false,
        currentRound = 0,
        linkClicked = false,
		linkHovered = false,	
		start = 0,
		limit = 30,
		nextStart = start,
        VievedGroupName,
		$container,
    	initialImages,
		npoOfNewNotifications,
    	notifyLinkClick = false,
        content = "",
        groupsOwnedStr = "",
        contactButtonClick = true,
        selectedTagId = null,
        sellerNameClick = false,
        loadOtherImgsClicked = false,
        searchQuery = "",
        searchQueryParams = "",
		emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,   
        domain = '<?php echo WEB_PATH?>';
        
    $("#wrapper2, #title_container, #authenticateDiv, #photo_wrapper, #newUserTable, #directLoginDiv, #directLoginError, #formContainer, #formContainer2, #formContainer3, #formContainer4, #tagsPanel, #tagPanelMesseging, #uploadedPicsPanel, #userProfileAdminArea").corner("keep");
    $("#fileupload-content").html(''); 
    $("#container").html('');
	$("label").inFieldLabels();    
    $("#userGroups").tabs();

	// This is to activate the keypress event for the search input box
    $("#SearchT").keypress(function(event) {
  		if (event.which == 13) {
   			$("#SearchB").trigger("click");		
        }
    });
	// This is for the image search thing related to the tag search
    $('#SearchB').click(function(){
    	if (($('#SearchT').val() != 'Search Items or Groups...') || ($('#SearchT').val() != '')){
        	searchQueryParams = "&searchQ=" + $('#SearchT').val();
        }else{
        	searchQueryParams = "";
        } 
        $('#searchResult').removeClass('hide');
		<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
        // First check the user have joined any groups if not load default
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkHasJoinedAnyGrps', function(data){
            if (data == '1'){
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=true&start=' + start + '&limit=' + limit + searchQueryParams, function(data, e){
        			generateSearchResults(data);                                
                });
            }else{
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=false&start=' + start + '&limit=' + limit + searchQueryParams, function(data, e){
        			generateSearchResults(data);                
                });
            }
        });
        <?php else:?>
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&start=' + start + '&limit=' + limit + searchQueryParams, function(data, e){
        	generateSearchResults(data);
        });
        <?php endif;?>	       
    });
    
    function generateSearchResults(data){
    
        $("#container").html('');
        $("#checkingForImgs").removeClass('hide');
        if ((data.endOfImgLot != true) && (data.imageSet != null)){
            $("#checkingForImgs").addClass('hide');
            $("#NoMore").addClass('hide');
            $("#loadOtherImages").removeClass('hide');
        }else{
            $("#endOfImageLotMessage").removeClass('hide');
            $("#checkingForImgs").addClass('hide');
            $("#NoMore").removeClass('hide');
            $("#loadOtherImages").addClass('hide');
        }
        if (data.imageSet != null){
            if (data.imageSet.length == 1){
                lastChar = '';
            }else{
                lastChar = 's';                
            }
            loadImagesAutomatically(data.imageSet, true);                
            $('#searchResult').addClass('successSearchResult');
            $('#searchResult').html(data.imageSet.length + ' item' + lastChar + ' found.');
        }else{
            $('#searchResult').addClass('errorSearchResult');            
            $('#searchResult').html('No matched results !');
        }
        //$("#loadOtherImages").removeClass('hide');
        nextStart = data.nextStart;
    }
    
    if ((window.location.pathname == '/www.rehand.com/') || (window.location.pathname.indexOf('www.rehand.com/users/viewgroup') != -1)){
        // Load images initially when the page is loaded
		<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
        if (window.location.pathname.indexOf('www.rehand.com/users/viewgroup') != -1){
        	VievedGroupName = "&vievedGroupName=" + window.location.pathname.replace('/www.rehand.com/users/viewgroup/', '');
            VievedGroupName = VievedGroupName.replace('/', '');
        }else{
        	VievedGroupName = "";
        }
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkHasJoinedAnyGrps', function(data){
            if (data == '1'){
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=true&start=' + start + '&limit=' + limit + VievedGroupName, function(data, e){
                    $("#loadOtherImages").removeClass('hide');                    
                    var isGroupOnly = true;
                    commonFuncBodyForAumaticImageLoad(isGroupOnly, data);
                });
            }else{
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=false&start=' + start + '&limit=' + limit + VievedGroupName, function(data, e){
                    $("#loadOtherImages").removeClass('hide');
                    var isGroupOnly = true;
                    commonFuncBodyForAumaticImageLoad(isGroupOnly, data);                    
                });
            }
        });        
        <?php else:?>
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&start=' + start + '&limit=' + limit, function(data, e){
        	var isGroupOnly = false;
            commonFuncBodyForAumaticImageLoad(isGroupOnly, data);                    
        });
        <?php endif;?>	       
    }    
    function commonFuncBodyForAumaticImageLoad(isGroupOnly, data){
    
        $("#imageContainer").html('');
        $("#checkingForImgs").removeClass('hide');
        $("#NoMore").addClass('hide');
        if (data.endOfImgLot != true){
            $("#checkingForImgs").addClass('hide');
        }else{
            $("#endOfImageLotMessage").removeClass('hide');
            $("#checkingForImgs").addClass('hide');
            if (!isGroupOnly) $("#NoMore").removeClass('hide');
        }
        if (data.imageSet != null){
            loadImagesAutomatically(data.imageSet, false);
        }    
        $("#endOfImageLotMessage").removeClass('hide');            
        nextStart = data.nextStart;
    }
    if (window.location.pathname == '/www.rehand.com/'){
        if (searchQuery != ''){
        	searchQueryParams = "searchQ=" + $('#SearchT').val();
        }else{
        	searchQueryParams = "";
        } 
        $(window).scroll(function(){
            if ($(window).scrollTop() == $(document).height() - $(window).height()){
                $("#scrollToTop").removeClass('hide');
                // Load next images
				<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkHasJoinedAnyGrps', function(data){
                    var isGroupOnly = true;
                    if (data == '1'){
                        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=true&start=' + start + '&limit=' + limit, function(data, e){
                            commonFuncBodyForWindowScrollForImgLoad(data);                    
                        });
                    }else{
                        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=false&start=' + start + '&limit=' + limit, function(data, e){
                            commonFuncBodyForWindowScrollForImgLoad(data);                    
                        });
                   	}
                });        
                <?php else:?>
                $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&start=' + start + '&limit=' + limit, function(data, e){
                	var isGroupOnly = false;
            		$("#imageContainer").html('');
                    $("#checkingForImgs").removeClass('hide');
                    $("#NoMore").addClass('hide');
                    if (data.endOfImgLot != true){
                        loadImagesAutomatically(data.imageSet, true);
                        $("#checkingForImgs").addClass('hide');
                    }else{
                        $("#endOfImageLotMessage").removeClass('hide');
                        if(!isGroupOnly)
                        	$("#NoMore").removeClass('hide');                        
                        $("#checkingForImgs").addClass('hide');
                    }
                    nextStart = data.nextStart;
                });
                <?php endif;?>	        
            }
        });
        $("#scrollToTop").click(function(){
            $('html, body').animate({scrollTop: $("#imageHeader").offset().top - 100}, 500);	
            $("#scrollToTop").addClass('hide');
            $("#endOfImageLotMessage").addClass('hide');
        });
    }    
	function commonFuncBodyForWindowScrollForImgLoad(data){

        $("#imageContainer").html('');
        $("#loadOtherImages").removeClass('hide');
        $("#checkingForImgs").removeClass('hide');
        $("#NoMore").addClass('hide');
        if (data.endOfImgLot != true){
            loadImagesAutomatically(data.imageSet, true);
            $("#checkingForImgs").addClass('hide');
        }else{
            $("#endOfImageLotMessage").removeClass('hide');
            if(!isGroupOnly)
                $("#NoMore").removeClass('hide');                        
            $("#checkingForImgs").addClass('hide');
        }
        nextStart = data.nextStart;
    }
	$("#loadOtherImages").click(function(){
        if (searchQuery != ''){
        	searchQueryParams = "searchQ=" + $('#SearchT').val();
        }else{
        	searchQueryParams = "";
        } 
    	loadOtherImgsClicked = true;
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=loadImgs&forGroupsOwned=false&start=' + start + '&limit=' + limit, function(data, e){
            //$("#imageContainer").html('');
            $("#checkingForImgs").removeClass('hide');
            if (data.endOfImgLot != true){
                $("#checkingForImgs").addClass('hide');
                $("#loadOtherImages").removeClass('hide');
                $("#NoMore").addClass('hide');
            }else{
                $("#endOfImageLotMessage").removeClass('hide');
                $("#checkingForImgs").addClass('hide');
                $("#loadOtherImages").addClass('hide');
                $("#NoMore").removeClass('hide');
            }
            loadImagesAutomatically(data.imageSet, true);
            //$("#loadOtherImages").addClass('hide');
            
            nextStart = data.nextStart;
        });
    });

	function loadImagesAutomatically(data, shouldBeAppended){

        $.each(data,function(i,tmpImg) {
          	//image url
            var imageURL = '' + domain + tmpImg.uploadTImgLocation;
            var id = 'image-' + tmpImg.ID;
            content = "";
            content += "<li class='box photo col3'><div class='GalMain'>"; 
            content += "<div class='imageWrapperForScroll'><a href='<?php echo WEB_PATH?>/" + tmpImg.uploadLImgLocation + "' id='" + tmpImg.idhash + "' rel='example_group' title='" + tmpImg.title + "' class='floatl'><div class='GalImg'><img id='" + tmpImg.idhash + "' src='<?php echo WEB_PATH?>/" + tmpImg.uploadTImgLocation + "' /></div></a>";
            content += "<div class='clear'></div><div style='position:relative;float:left;'><div class='LocationMain'>";
            if (tmpImg.Locality != ""){
                content += "<span class='PostCode'>" + tmpImg.Locality + "</span>";
            }
            content += "<span id='noOfInterests'>" + tmpImg.wholeNoOfInterests + "</span></div></div>";
            content += "<div id='sellerInfoWrapper_" + tmpImg.idhash + "' class='sellerContactInfoWrapper hide'></div></div><div class='clearH10'></div><a id='contatLink_" + tmpImg.idhash + "' class='contactLink' href='javascript:void(0);'><img style='width:23px;height:23px;' src='<?php echo WEB_PATH?>/" + tmpImg.profileImage + "' />Message Seller</a><a id='" + tmpImg.title + "' rel='<?php echo WEB_PATH?>/" + tmpImg.uploadTImgLocation + "' href='javascript:void(0);' class='GalShareSmall'></a><a href='<?php echo WEB_PATH?>/"+ tmpImg.uploadLImgLocation + "' id='" + tmpImg.idhash + "' rel='example_group' title='" + tmpImg.title + "' class='GalViewSmall'></a></div><div class='clearitemshadow'></div><div style='float:left;margin-right:10px;'>";
            if (tmpImg.pictureRelatedTags){
                $.each(tmpImg.pictureRelatedTags, function(n) {
                    content += "<a href='javascript:void(0);' class='TagsH'><span lang='" + tmpImg.pictureRelatedTags[n].tagId + "' class='TagHName " + tmpImg.idhash + "'>" + ((tmpImg.pictureRelatedTags[n].tagName.length > 21) ? (tmpImg.pictureRelatedTags[n].tagName.substring(0, 21) + '...') : tmpImg.pictureRelatedTags[n].tagName) + "</span><span lang=' " + tmpImg.pictureRelatedTags[n].tagId + "' class='Price " + tmpImg.idhash + "'>" + tmpImg.pictureRelatedTags[n].price + "</span></a>";
                });
            }
            content += "</div></li>"; 
            if (!shouldBeAppended){   
                $(content).appendTo($('#container')).hide().slideDown(250, function() {
                    $container = $('#container');
                    $container.imagesLoaded(function(){
                      $container.masonry({
                        itemSelector: '.box',
                        isAnimated: true
                      });
                    });
                });
            }else{
            	$('#container').append(content).masonry('reload');
            }    
            $(function(){
                $("a[rel=example_group]").fancybox({
                    'transitionIn'		: 'elastic',
                    'transitionOut'		: 'fade',
                    'titlePosition'		: 'outside',
                    'overlayColor'		: '#000',
                    'overlayOpacity'	: 0.8,
                    'hideOnContentClick': false,
                    'speedIn' : 300,
                    'speedOut' : 20,
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
                        
                        $("#fancybox-bg-n").css({"display" : "block"});
                        $("#fancybox-bg-n").html("<label style='cursor:pointer;float:left;margin-right:10px;'><input type='checkbox' class='hideTagsLink' />Hide tags</label>");
                        
                        $($(document).find("#fancybox-outer")).TagMe({
                            id:$(e.orig).attr('id'),
                            loadTags:true,
                            loadTagsAction:{
                                url:"<?php echo WEB_PATH?>/library/tagMeCall.php<?php echo (!empty($_SESSION['searchQuery']) ? "?searchTag=".$_SESSION['searchQuery'] : "")?>",
                                onProgress:function(ele){},
                                onSuccess:function(ele){
                                    $(ele).find("#"+selectedTagId).trigger('mouseover');
                                    
                                    var hideAllTags = false;                                    
                                    // This is to show and hide tags
                                    $(".hideTagsLink").change(function(){
                                        if (!hideAllTags){
                                            hideAllTags = true;
                                            elements = $('div.tagItem-1');
                                            elements.each(function() {
                                                $(this).addClass("hide");
                                            });
                                        }else{
                                            hideAllTags = false;
                                            elements = $('div.tagItem-1');
                                            elements.each(function() {
                                                $(this).removeClass("hide");
                                            });
                                        }
                                    });
                                },
                                onFail:function(ele){},
                                json:true		
                            },
                            onTagAdded:function(ele,data){
                            	ele.removeClass();
								ele.addClass("tagItem-1");
                                var htmlTemp = "<span class='TagItemName'>" + data.itemName + "</span>";
													
                                switch(data.itemStatus){
                                    case "available":
                                        ele.addClass("itemToSell");
                                        htmlTemp += "<span class='TagItemPrice'>" + data.itemPrice +"</span>";
                                    break;    
                                    case "free":
                                        ele.addClass("itemFree");
                                        htmlTemp += "<span class='TagItemPrice'>Free</span>";									
                                    break;
                                    case "sold":
                                        ele.addClass("itemSold");    
                                        htmlTemp += "<span class='TagItemPrice'>Sold</span>";									
                                    break;
                                }
                                ele.html(htmlTemp);
                            },
                            tagpanelAction:{
                                  url:"<?php echo WEB_PATH?>/library/tagMeCall.php",
                                  submitFormId:'tm-tagpanel-form',
                                  onProgress:function(ele){},
                                  onSuccess:function(ele){},
                                  onFail:function(ele){},
                                  json:true		
                            },
                            tagpanelElement: '<div class="tm-tagpanel Hometag">'+
                                '<form id="tm-tagpanel-form">'+
                                    '<span id="itemName"></span><div class="clearH5"></div>'+
                                    '<span id="itemDescription"></span><div class="clearH15"></div>'+									 
                                    '<input type="button" id="buybtn" value="Grab It!" class="GreenBut buyNowBtn" style="width:117px;" />' + 
                                    '<div class="clearH5"></div>'+
                                '</form>'+
                            '</div>',
                            tagpanelOnAfterInitPanel:function(ele,data){
                            
                                if (data.tagData.itemStatus == "sold"){
                                	ele.find('.GreenBut').val('Sold');
                                    ele.find('.GreenBut').remove();
                                    ele.append('<div class="GreyButton">Item Sold !</div>');
                                }
                                // This is for the notifications sending after clicking the buy now button
                                $(".buyNowBtn").click(function(){
                                <?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?> 
                                    var notificationSub = "";
                                    $.ajax({  
                                        type: "POST",
                                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=buyNotify", 
                                        data: "tagId=" + data.tagData.tagId + "&itemStatus=" + data.tagData.itemStatus, 
                                        //async: true,
                                        success: function(notifyRes){				
                                            if (notifyRes != ''){
                                                $("#notiticationsDiv").html("");
                                                loadAllNotifications();
                                            }
                                        }
                                    });
                                <?php else:?>
                                    $.ajax({  
                                        type: "POST",
                                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=keepThePreviousClickedData", 
                                        data: "tagId=" + data.tagData.tagId + "&itemStatus=" + data.tagData.itemStatus + "&linkId=" + data.tagData.imageId 
                                    });
                                    $.fancybox.close();
                                    $('#directLogin').trigger("click");
                                <?php endif;?>
                                });
                            }
                        });
                    }
                });   
            }); 
           	//$("#container").preloader(); 
        }); 
        // This is to show the share screen for the images in the main page
        $(".GalShareSmall").click(function(e){

            //Get the screen height and width
            var maskHeight = $(document).height();
            var maskWidth = $(window).width();
         
            //Set height and width to mask to fill up the whole screen
            $('#social__mask').css({'width':maskWidth,'height':maskHeight});
             
            //transition effect    
            $('#social__mask').fadeIn(1000);   
            $('#social__mask').fadeTo("slow",0.8); 
         
            //Get the window height and width
            var winH = $(window).height();
            var winW = $(window).width();
                   
            //Set the popup window to center
            $("#social_dialog").css('top', winH / 2 - $("#social_dialog").height() / 2);
            $("#social_dialog").css('left', winW / 2 - $("#social_dialog").width() / 2);

            //transition effect
            $("#social_dialog").fadeIn(2000); 
        });    
         //if close button is clicked
         $('.social_window .close').click(function (e) {
             //Cancel the link behavior
             e.preventDefault();
             $('#social__mask, .social_window').hide();
         });    
         //if mask is clicked
         $('#social__mask').click(function () {
            $("#social_dialog").hide();          
             $('#social__mask').hide();
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
        // select all the a tag with name equal to modal
        $('.contactLink').click(function(e) {
              var selectedAID = "";
             //Cancel the link behavior
             e.preventDefault();
             
             //Get the screen height and width
             var maskHeight = $(document).height();
             var maskWidth = $(window).width();
             
             //Set height and width to mask to fill up the whole screen
             $('#contact__mask').css({'width':maskWidth,'height':maskHeight});
                 
             //transition effect    
             $('#contact__mask').fadeIn(1000);   
             $('#contact__mask').fadeTo("slow",0.8); 
             
             //Get the window height and width
             var winH = $(window).height();
             var winW = $(window).width();
                       
             //Set the popup window to center
             $("#contact_dialog").css('top',  winH/2-($("#contact_dialog").height())/2);
             $("#contact_dialog").css('left', winW/2-($("#contact_dialog").width())/2);
             
             //transition effect
             $("#contact_dialog").fadeIn(2000);
             // This is for the click event of the contact seller form 
             
             // This is to get the currently selected link ID
             selectedAID = $(this).attr('id').replace("contatLink_", "");   
			<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
                <?php if ((isset($_SESSION['selectedItemStatusNotLogged'])) && (isset($_SESSION['selectedTagIdNotLogged'])) && (isset($_SESSION['selectedLinkNotLogged']))):?>
                    var aId = '<?php echo $_SESSION['selectedLinkNotLogged'];?>';
                    var tId = '<?php echo $_SESSION['selectedTagIdNotLogged'];?>';
                    selectedTagId = $.trim(tId);
                    $("#"+$.trim(aId)).trigger('click');
                <?php endif;?>
            <?php endif;?>
             $("#contactSeller").click(function(){
                var errorInContactForm = "";
                var submittingData = "";
                $("#messageSellerMessageWaiting").removeClass("hide");
                $("#messageSellerMessageWaiting").html("&nbsp;&nbsp;&nbsp;Please wait...");
                if (($("#buyerContactEmail").val() == "") && ($("#buyerContactMessage").val() == "")){
                    errorInContactForm += "Please correct the form error(s)<br />";
                    errorInContactForm += "Email<br />";
                    errorInContactForm += "Message<br />";
                }else{
                    if ($("#buyerContactEmail").val() == ""){
                        errorInContactForm += "Please correct the form error(s)<br />";
                        errorInContactForm += "Email<br />";
                    }else if (!emailReg.test($("#buyerContactEmail").val())){
                        errorInContactForm += "Please correct the form error(s)<br />";
                        errorInContactForm += "Invalid Email Address<br />";
                    }else if ($("#buyerContactMessage").val() == ""){
                        errorInContactForm += "Please correct the form error(s)<br />";
                        errorInContactForm += "Message<br />";
                    }    
                }
                if (errorInContactForm == ""){
                	  submittingData = "buyerEmail=" + $("#buyerContactEmail").val() + "&messageFromBuyer=" + $("#buyerContactMessage").val() + "&relAId=" + selectedAID;
                      $("#contactSellerWaitingMsg").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Wait ...</span>');		
                      $.ajax({  
                              type: "POST",
                              data: submittingData,
                              url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
                              success: function(server_response){				
                                  if (server_response == '1'){
                                      $("#messageSellerMessageWaiting").html("");
                                      $("#messageSellerMessageWaiting").addClass("hide");
                                      $("#messageSellerMessageSuccess").removeClass('hide');
                                      $("#messageSellerMessageSuccess").html('Your message sent !');                                      
                                      setTimeout(function(){
                                          $("#contactSellerWaitingMsg").addClass('hide');
                                          $("#buyerContactMessageReadOnly").removeClass("hide");
                                          $("#buyerContactMessage").addClass("hide");
                                          $("#buyerContactMessage").val(''); 
										  <?php if ((!isset($_SESSION['currentUser'])) && (!isset($_SESSION['fbUser']))):?>                                          
                                          $("#buyerContactEmail").val('Your email address');
                                          <?php endif;?>                                          
                                          $('#contact__mask').trigger("click");
                                          $("#messageSellerMessageSuccess").addClass('hide');
                                          $("#messageSellerMessageSuccess").html('');                                      
                                      }, 2000);
                                  }
                              }
                          });
                  }else{
                        $("#messageSellerMessageError").removeClass('hide');
                        $("#messageSellerMessageError").html(errorInContactForm);                                      
                        $("#messageSellerMessageWaiting").html("");
                        $("#messageSellerMessageWaiting").addClass("hide");
                        setTimeout(function(){                    
                            $("#messageSellerMessageError").addClass('hide');
                            $("#messageSellerMessageError").html('');                                      
                        }, 2000);
                  }     	
             });
         });
         //if close button is clicked
         $('.contact_window .close').click(function (e) {
             //Cancel the link behavior
             e.preventDefault();
             $('#contact__mask, .contact_window').hide();
         });    
         //if mask is clicked
         $('#contact__mask').click(function () {
             $(this).hide();
             $('.contact_window').hide();
         }); 
        if (loadOtherImgsClicked){
        	$('#loadOtherImages').remove();
        }
    }

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
                    content += "<div class='notificationSub'>" + data.allNotifications[i].notificationText + "</div>";
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
        //setTimeout(function(){
            //loadAllNotifications();
        //}, 5000);        
    }

    // This is for the click event of the contact seller form 
    $("#buyerContactMessage").addClass("hide");
    $("#buyerContactMessageReadOnly").focus(function(){
        $("#buyerContactMessageReadOnly").addClass("hide");
        $("#buyerContactMessage").removeClass("hide");
        $("#buyerContactMessage").focus();
    });
    $("#buyerContactMessage").focusout(function(){
        if ($("#buyerContactMessage").val() == "Your message here..."){
            $("#buyerContactMessageReadOnly").removeClass("hide");
            $("#buyerContactMessage").addClass("hide");
        }
    });

	// This is for the Jquery login box
    $('#directLogin').click(function() {

		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
        	
        $("#login_recover_dialog").css({"display" : "none"});
    	$("#login_boxes").css({"display" : "block"});
    
        //Get the A tag
        id = $("#login_dialog");
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#login_mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#login_mask').fadeIn(1000);   
        $('#login_mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
     
    });
    //if close button is clicked
    $('.login_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#login_mask, .login_window').hide();
    });    
    //if mask is clicked
    $('#login_mask').click(function () {
        $(this).hide();
        $('.login_window').hide();
    });        
   // This is for the secondary login and registration buttons 
    $("#secondaryLoginLink").click(function(){
    	$('.window .close').trigger("click");
    	$('#directLogin').trigger("click");
   	});
    $("#secondaraySignUpLink").click(function(){
    	$('.login_window .close').trigger("click");
    	$('#RegisterBut').trigger("click");
    });
    // This is for the top left login and register text links
    $("#textLogin").click(function(){
    	$('#directLogin').trigger("click");
    });
    $("#textRegister").click(function(){
    	$('#RegisterBut').trigger("click");
    });
    
	// This is for the Jquery registration box
    // select all the a tag with name equal to modal
    $('#RegisterBut').click(function() {
		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
        	
        //Get the A tag
        id = $("#dialog");
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#mask').fadeIn(1000);   
        $('#mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
     
    });
    //if close button is clicked
    $('.window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#mask, .window').hide();
    });    
    //if mask is clicked
    $('#mask').click(function () {
        $(this).hide();
        $('.window').hide();
    });        
  	// If someone clicks the tag pictures then navigate the current panel to right and shows the next panel
    $("#tagPictures").click(function() {
          window.location ='<?php echo WEB_PATH?>/index/uploader/?view=uploaded';
    });

    // This is for load sub menus for the piucture uploading thing
    $("#picUploadAreaLink").click(function() {
        if (!piclinkClicked){
        	piclinkClicked = true;
        	$("#picUpload, #uploadedImgs").removeClass("hide");
        	$("#myProfile, #logout").addClass("hide");
        }else{
        	piclinkClicked = false;
        	$("#picUpload, #uploadedImgs").addClass("hide");
        }
    });
    
    // This is for load sub menus for the profile submenus
    $("#userProfilePageLinks").click(function() {
        if (!usersProfLinkClicked){
        	usersProfLinkClicked = true;
        	$("#myProfile, #logout").removeClass("hide");
        	$("#picUpload, #uploadedImgs").addClass("hide");
        }else{
        	usersProfLinkClicked = false;
        	$("#myProfile, #logout").addClass("hide");
        }
    });
	
	$("#directLogin").click(function() {
        if ($("#directLoginError").css("display") == "block"){
           $("#directLoginError").css({"display" : "none"});            
        }
		if ((!loginClicked) && (!regClicked)){
			loginClicked = true;            
			$("#directLoginDiv").show(1000);
            $("#fbLoginUrl").css({"position" : "absolute", "top" : "121px", "left" : "11px"});                   	                                    
            $("#fbRegisterButton").css({"position" : "absolute", "top" : "23px", "left" : "112px", "margin-top" : "125px"});                   	                                                
            $("#forgotPassUrl").css({"position" : "absolute", "top" : "52px", "left" : "37px", "margin-top" : "125px"});                   	                                                                        
            $("#directLoginDiv").css({"right": "15px", "top" : "53px"});                   	            
            $("#authenticateDiv").css({"height": "175px"});
		}else{
			loginClicked = false;		
			$("#directLoginDiv").hide(1000);
            $("#fbLoginUrl").css({"position" : "absolute", "top" : "26px", "left" : "9px"});                   	                                    
            $("#fbRegisterButton").css({"position" : "absolute", "top" : "-70px", "left" : "111px", "margin-top" : "125px"});                   	                                                
            $("#forgotPassUrl").css({"position" : "absolute", "top" : "-40px", "left" : "35px", "margin-top" : "125px"});                   	                                                                                    
            $("#directLoginDiv").css({"right": "15px", "top" : "53px"});                   	            
            $("#authenticateDiv").css({"height": "92px"});
		}	
	}); 
    $("#directLoginPassword").keypress(function(event) {
  		if (event.which == 13) {
   			$("#directLoginSubmit").trigger("click");		
        }
    });
    
    $("#directLoginSubmit").click(function() {

		var login_errors = "We were unable to sign you in. If you forgot your password you can <a href='javascript:void(0)' id='ResetPass'>reset it</a>";                        
        if (
        	($("#directLoginEmail").val() == '') || 
            ($("#directLoginPassword").val() == 'Email Address') ||
            ($("#directLoginPassword").val() == '') ||
            ($("#directLoginPassword").val() == 'Password')
           ){
                $("#directLoginError").html(login_errors);                        
                $("#directLoginError").show(500);            
                setTimeout(function(){
                    $("#directLoginError").hide(500);    	                                                            
                    $("#directLoginDiv").show(500);    	                                
                }, 8000);
                // This is for the reset link
                $("#ResetPass").click(function(){
                    $('#login_mask').trigger('click');
                    $('#ForgotPassword').trigger('click');	
                })

                return false;
        }else{
            $.ajax({  
                type: "POST",
                url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkLogin", 
                data: "username=" + $("#directLoginEmail").val() + "&password=" + $("#directLoginPassword").val(), 
                async: true,
                success: function(server_response){				
                    if (server_response == '0'){
                        $("#directLoginError").html(login_errors);                        
                        $("#directLoginError").show(500);            
                        setTimeout(function(){
                            $("#directLoginError").hide(500);    	                                                            
                            $("#directLoginDiv").show(500);    	                                
                        }, 8000);
                        return false;
                    }else{
                		$("#directLoginForm").submit();
                    }
                }
            });
        } 
    });
     
	$("#fbRegisterButton").click(function () {
		if ((!regClicked) && (!loginClicked)){  
			regClicked = true;		
			$("#fbRegister").show(1000);
            $("#fbRegister").css({"position" : "absolute", "top" : "60px", "left" : "843px"});                   	                                    
		}else{
			regClicked = false;		
			$("#fbRegister").hide(1000);
		}	
	});     

	$("#user_id").change(function(){ 
		var username = $("#user_id").val();
		$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
        $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});        
		$.ajax({  
			type: "POST",
			url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkUname", 
			data: "username=" + username, 
			success: function(server_response){	
				if (server_response == '0'){
					if (!emailReg.test(username)){                
                        $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                    
						$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin"><div class="clear"></div> <span>Oops. Email address is invalid</span>');	  
                        $("#requiredForEmailImg").addClass("hide");                  
                    }else{
						userIdAvailability = true;                    
                        $("#registrationFormWrapper").css({"width": "500px", "padding-left" : "50px"});                                            
						$("#availability_status").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin"><div class="clear"></div> <span class="Green">Yay! This email address is Available.</span>');	
                    	$("#requiredForEmailImg").addClass("hide");
                    }    
				}else{
					$("#registrationFormWrapper").css({"width": "525px", "padding-left" : "75px"});                                                            
					$("#availability_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png"><div class="clear"></div> <span>Oops. This email address is Not Available</span>');                                        
					$("#requiredForEmailImg").addClass("hide");
                }
                $("#registerErrorMsg").hide();
		}});
	});

	$("#password").change(function(){ 
        if ($("#password").val().length < 6){
            $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                   	        
        	$("#password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin"><div class="clear"></div> <span>Oops. Your password is too short</span>'); 
            $("#requiredForPasswordImg").addClass("hide");
        }else{
			$("#password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin"><div class="clear"></div> <span class="Green">Yay! Your password is accepted.</span>');
            $("#requiredForPasswordImg").addClass("hide");	        	                       
        }
        $("#registerErrorMsg").hide();        
    });       

    $("#password").keypress(function(event) {
        if (event.which == 13) {
            $("#newUserRegister").trigger("click");		
        }
    });

	$("#name").change(function(){ 
        if ($("#name").val() == ''){
            $("#registrationFormWrapper").css({"width": "600px", "padding-left" : "100px"});                   	        
        	$("#name_status").html('<img src="<?php echo WEB_PATH?>/public/images/not_available.png" class="Floatmargin">'); 
            $("#requiredForNameImg").addClass("hide");
        }else{
			$("#name_status").html('<img src="<?php echo WEB_PATH?>/public/images/available.png" class="Floatmargin">');
            $("#requiredForNameImg").addClass("hide");	        	                       
        }
      
        $("#registerErrorMsg").hide();        
    });       

	$("#newUserRegister").click(function () {
        var errors = "",
        	errMsg = "",
            errorCount = 0;
        $("#registerErrorMsg").html("");	
        
        if ( 
        	(($("#name").val() == "Name") || ($("#name").val() == "")) &&
        	(($("#password").val() == "Password") || ($("#password").val() == "")) && 
            (($("#user_id").val() == "Email Address") || ($("#user_id").val() == ""))  
           ){
                errorCount += 1;                
        		errors = "These fields are empty <br /><div>Name<br />Email<br />Password</div>";
        }else{
        	if (($("#user_id").val() == "Email Address") || ($("#user_id").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Email field is empty<br />";
            }else{
                if (!emailReg.test($("#user_id").val())){                        	
                    errors += "<span>Oops. Email address is invalid</span><br />";        
                    errorCount += 1;                                            	
                }else{
                    if (!userIdAvailability){
                        errors += "<span>Oops. This email address is already registered.</span><br />";        
                        errorCount += 1;                                            	                	
                    }            	
                }
           	}
        	if (($("#password").val() == "Password") || ($("#password").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Password field is empty<br />";
           	}else{
                  if ($("#password").val().length < 6){
                      errors += "<span>Oops. Your password is too short</span><br />";        
                      errorCount += 1;                                
                  }            	
            }
            if (($("#name").val() == "Name") || ($("#name").val() == "")){
                errorCount += 1;                
	        	errors += "<span>Oops. Name field is empty</span>";
            }  
        }        
		if (errors != ""){
        	errMsg = errors;
			$("#registerErrorMsg").html(errMsg);	
            switch (errorCount) {
                case '1': $("#registerErrorMsg").css({"height" : "10px"}); break;
                case '2': $("#registerErrorMsg").css({"height" : "20px"}); break;
                case '3': $("#registerErrorMsg").css({"height" : "30px"}); break;
                case '4': $("#registerErrorMsg").css({"height" : "40px"}); break;
                case '5': $("#registerErrorMsg").css({"height" : "50px"}); break;
                case '6': $("#registerErrorMsg").css({"height" : "60px"}); break;                    
            }            
			$("#requiredForEmailImg").addClass("hide");    
            $("#requiredForNameImg").addClass("hide");
            $("#requiredForPasswordImg").addClass("hide");
            
            $("#name_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Name Required" >');                                        
			$("#availability_status").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Email Address Required" >');                                        
			$("#password_status2").html('<img class="notAvailable Floatmargin" src="<?php echo WEB_PATH?>/public/images/not_available.png" title="Password Required" >');                                        
            
            $("#registerErrorMsg").slideDown("slower");
            setTimeout(function(){
	            $("#registerErrorMsg").slideUp("slower");    	                                
            }, 8000);                                      
            return false;                                
        }else{
        	$("#newUser").submit();
        }
    });	
	$("#curemail").keypress(function(event){
  		if (event.which == 13) {
   			$("#request_Password").trigger("click");
            return false;		
        }
    });
    $("#request_Password").click(function(){
		$("#recover_password_status").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
    	var errors = "",
        	errorCount = 0;
        if (($("#curemail").val() == '') || ($("#curemail").val() == 'Your email address')){
        	errors += "Please enter an email address";
            $("#curemail").val('Your email address');
            $("#forgotPassError").html(errors);	                
            $("#forgotPassError").slideDown("slower");
			$("#recover_password_status").html('');		
            setTimeout(function(){
                $("#forgotPassError").slideUp("slower");    	                                
            }, 8000);                                      
            return false;
        }else{
			$("#recover_password_status").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
            if (emailReg.test($("#curemail").val())){       
                $.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=chkCurEmail", 
                    data: "curemail=" + $("#curemail").val(),
                    success: function(data){
                        if (data == '1'){
				            $("#curemail").val('Your email address');
                            $("#recover_password_status").html('');
                            $("#forgotPassSuccess").html("Please check your mail : Password Restting link has been mailed to you !");	                
                            $("#forgotPassSuccess").slideDown("slower");
                            $("#recover_password_status").html('');		
                            setTimeout(function(){
                                $("#forgotPassSuccess").slideUp("slower");    	                                
                            }, 8000);                                      
                        }else{
				            $("#curemail").val('Your email address ');
                            $("#recover_password_status").html('');
                            errors += "We couldn't find your email address";
                            $("#forgotPassError").html(errors);	                
                            $("#forgotPassError").slideDown("slower");
                            setTimeout(function(){
                                $("#forgotPassError").slideUp("slower");    	                                
                            }, 8000);                                      
                            return false;
                        }
                    }
                });
             }else{
                  $("#curemail").val('Your email address');
                  $("#recover_password_status").html('');
                  errors += "Oops this is not avalid email address !";
                  $("#forgotPassError").html(errors);	                
                  $("#forgotPassError").slideDown("slower");
                  setTimeout(function(){
                      $("#forgotPassError").slideUp("slower");    	                                
                  }, 8000);                                      
                  return false;
             }           
        }
    });
    
    $("#recover_Password").click(function(){
    	var errors = "",
        	errorCount = 0;
        if (($("#curpassword").val() == '') || ($("#curpassword").val() == 'Current Password')){
        	errors += "&nbsp;&nbsp;&nbsp; -- Current Password<br />";
            errorCount += 1;
        }
    	if (($("#newpassword").val() == '') || ($("#newpassword").val() == 'New Password')){ 
        	errors += "&nbsp;&nbsp;&nbsp; -- New Password<br />";
            errorCount += 1;
        }    
        if (errors != ""){
        	errors += "These fields are empty <br />" + errors;
            switch (errorCount) {
                case '1': $("#forgotPassError").css({"height" : "10px"}); break;
                case '2': $("#forgotPassError").css({"height" : "20px"}); break;
            }            
            $("#forgotPassError").html(errors);	                
            $("#forgotPassError").slideDown("slower");
            setTimeout(function(){
	            $("#forgotPassError").slideUp("slower");    	                                
            }, 3000);                                      
            return false;
        }else{
         	$.ajax({  
                type: "POST",
                url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=chkCurPass", 
                data: "curpass=" + $("#curpassword").val(),
                success: function(data){
					if (data == 'not ok'){
                    	errors = "";
                        errors += "Oops. Please check your password again.<br />";
                        $("#forgotPassError").html(errors);	                
                        $("#forgotPassError").slideDown("slower");
                        setTimeout(function(){
                            $("#forgotPassError").slideUp("slower");    	                                
                        }, 8000);                                      
                        $("#curpassword").val('Current Password');
                        $("#newpassword").val('New Password');
                        return false;
                    }else{
                    	if ($("#newpassword").val().length < 6){
                            errors += "Your new passwords are not in a valid length<br />";
                            $("#forgotPassError").html(errors);	                
                            $("#forgotPassError").slideDown("slower");
                            setTimeout(function(){
                                $("#forgotPassError").slideUp("slower");    	                                
                            }, 8000);                                      
                            $("#curpassword").val('Current Password');
                            $("#newpassword").val('New Password');
                            return false;
                        }else{
                             $.ajax({  
                                type: "POST",
                                url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=newpass", 
                                data: "newpassval=" + $("#newpassword").val(),
                                success: function(data){
                                    if (data == 'ok'){
                                        $("#newpassword_text").removeClass("hide");
                                        $("#curpassword_text").removeClass("hide");
                                        $("#newpassword").addClass("hide");
                                        $("#curpassword").addClass("hide");
                                        $("#tagPanelMesseging").removeClass("hide");
                                        $("#tagPanelMesseging").addClass("successMsgInTag");
                                        $("#tagPanelMesseging").fadeIn("slow");
                                        $("#tagPanelMesseging").html("Your password has been changed !");
                                        setTimeout(function(){
                                            $("#tagPanelMesseging").fadeOut("slow");
                                        }, 8000);                                                  
                                    }
                                }
                             });
                        }
                    }
                }
            });  
        }
    });
    
	$("#updateProfileSubmit").click(function () {
        var errors = "",
        	errMsg = "",
            errorCount = 0;

  		<?php if (isset($_SESSION['fbUser'])):?>
              if ($("#profileUpdattionName").val() == ""){
                  errors += "<span>Oops. Name field is empty</span><br />";
              }  
         <?php elseif (isset($_SESSION['currentUser'])):?> 
            if ( 
                ($("#profileUpdattionName").val() == "") &&
                ($("#profileUpdattionEmail").val() == "")  
               ){
                    errorCount += 1;                
                    errors = "These fields are empty <br />Name<br />Email<br />Password";
            }else{
                if ($("#profileUpdattionName").val() == ""){
                    errorCount += 1;                
                    errors += "<span>Oops. Name field is empty</span><br />";
                }  
                if ($("#profileUpdattionEmail").val() == ""){
                    errorCount += 1;                
                    errors += "<span>Oops. Email field is empty</span><br />";
                }else{
                    if (!emailReg.test($("#profileUpdattionEmail").val())){                        	
                        errors += "<span>Oops. Email address is invalid</span><br />";        
                        errorCount += 1;                                            	
                    }else{
                        // This is to check the email is already is there in the db
                        var username = $("#profileUpdattionEmail").val();
                        
                        $.ajax({  
                            type: "POST",
                            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=checkUnameWithUid", 
                            data: "username=" + username, 
                            success: function(server_response){				
                                if (server_response == '1'){
                                   userIdAvailability2 = true;                    
                                }
                            }
                        });
                        if (userIdAvailability2){
                            errors += "<span>Oops. This email address is already registered.</span><br />";        
                            errorCount += 1;                                            	                	
                        }            	
                    }
                }    
            }
		<?php endif;?>
		if (errors != ""){
        	errMsg = errors;
			$("#profileUpdateError").html(errMsg);	
            switch (errorCount) {
                case '1': $("#profileUpdateError").css({"height" : "10px"}); break;
                case '2': $("#profileUpdateError").css({"height" : "20px"}); break;
                case '3': $("#profileUpdateError").css({"height" : "30px"}); break;
            }            
            $("#profileUpdateError").slideDown("slower");
            setTimeout(function(){
	            $("#profileUpdateError").slideUp("slower");    	                                
            }, 8000);                                      
            return false;                                
        }else{
       		$("#updateProfileSettingsForm").submit();
        }
    });	
    // This is for the show hidden password text in the registration box
    $("#password").addClass("hide");
    $("#password_text").focus(function(){
    	$("#password_text").addClass("hide");
    	$("#password").removeClass("hide");
        $("#password").focus();
    });
    $("#password").focusout(function(){
    	if ($("#password").val() ==""){
            $("#password_text").removeClass("hide");
            $("#password").addClass("hide");
        }
    });
    // This is for the show hidden password text in the login box
    $("#directLoginPassword").addClass("hide");
    $("#directLoginPassword_text").focus(function(){
    	$("#directLoginPassword_text").addClass("hide");
    	$("#directLoginPassword").removeClass("hide");
        $("#directLoginPassword").focus();
    });
    $("#directLoginPassword").focusout(function(){
    	if ($("#directLoginPassword").val() ==""){
            $("#directLoginPassword_text").removeClass("hide");
            $("#directLoginPassword").addClass("hide");
        }
    });
    // This is for the change password fields
    $("#curpassword").addClass("hide");
    $("#curpassword_text").focus(function(){
        $("#curpassword_text").addClass("hide");
        $("#curpassword").removeClass("hide");
        $("#curpassword").focus();
    });
    $("#curpassword").focusout(function(){
        if ($("#curpassword").val() ==""){
            $("#curpassword_text").removeClass("hide");
            $("#curpassword").addClass("hide");
        }

    });
    // New password field
    $("#newpassword").addClass("hide");
    $("#newpassword_text").focus(function(){
        $("#newpassword_text").addClass("hide");
        $("#newpassword").removeClass("hide");
        $("#newpassword").focus();
    });
    $("#newpassword").focusout(function(){
        if ($("#newpassword").val() ==""){
            $("#newpassword_text").removeClass("hide");
            $("#newpassword").addClass("hide");
        }
    });
    // This is for the forget password change
    $("#new_Password").click(function(){
		//$("#recover_password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
        var errors = "";
        if ( 
        	($("#newpassword").val() == "") ||
            ($("#newpassword").val() == "New Password")  
           ){
        		errors = "Password is empty";
                $("#forgotPassError").html(errors);	                
                $("#forgotPassError").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassError").slideUp("slower");    	                                
                }, 8000);                                      
                $("#newpassword").addClass("hide");
                $("#newpassword_text").removeClass("hide");
                return false;
        }else{
        	if ($("#newpassword").val().length < 6){
	        	errors += "<span>Password is too short !</span><br />";
                $("#forgotPassError").html(errors);	                
                $("#forgotPassError").slideDown("slower");
                setTimeout(function(){
                    $("#forgotPassError").slideUp("slower");    	                                
                }, 8000);                                      
                $("#newpassword").addClass("hide");
                $("#newpassword_text").removeClass("hide");
                $("#recover_password_status2").html('');	
                return false;
            }else{
            	//$("#recover_password_status2").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');		
                $.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=newpassforpassreset", 
                    data: "forgotpassword=" + $("#newpassword").val(), 
                    success: function(server_response){				
                        if (server_response == '1'){
                			$("#newpassword").addClass("hide");
                			$("#newpassword_text").removeClass("hide");
                            $("#tagPanelMesseging").removeClass("hide");
                            $("#tagPanelMesseging").addClass("successMsgInTag");
                            $("#tagPanelMesseging").fadeIn("slow");
                            $("#tagPanelMesseging").html("Your password has been changed !");
                            setTimeout(function(){
                                $("#tagPanelMesseging").fadeOut("slow");
                                location.href = "<?php echo WEB_PATH?>";
                            }, 3000);
                            $("#recover_password_status2").html('');                                                  
                        }
                    }
                });
            }    
        }        
    });
    // This is for the Jquery login recovery box
    $('#ForgotPassword').click(function(e) {
		var id = "",
        	maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
    
    	$("#login_boxes").css({"display" : "none"});
        $("#login_recover_dialog").css({"display" : "block"});
        //Cancel the link behavior
        e.preventDefault();
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#login_recover_mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#login_recover_mask').fadeIn(1000);   
        $('#login_recover_mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
        
        //Set the popup window to center
        $('#login_recover_dialog').css('top',  winH/2-$('#login_recover_dialog').height()/2);
        $('#login_recover_dialog').css('left', winW/2-$('#login_recover_dialog').width()/2);
     
        //transition effect
        $('#login_recover_dialog').fadeIn(2000);
     
    });
    //if close button is clicked
    $('.login_recover_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#login_recover_mask, .login_recover_window').hide();
    });    
    //if mask is clicked
    $('#login_recover_mask').click(function () {
        $(this).hide();
        $('.login_recover_window').hide();
    });
    $('a[name=createYourGroupLink]').click(function(e) {
        //Cancel the link behavior
        e.preventDefault();
        //Get the A tag
        var id = $(this).attr('href');
     
        //Get the screen height and width
        var maskHeight = $(document).height();
        var maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#group__mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#group__mask').fadeIn(1000);   
        $('#group__mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        var winH = $(window).height();
        var winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
     
    });
    //if close button is clicked
    $('.group_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#group__mask, .group_window').hide();
    });    
    //if mask is clicked
    $('#group__mask').click(function () {
        $(this).hide();
        $('.group_window').hide();
    }); 
	// This is to create group name section and validation
    $('#usergroupsubmit').click(function(){
    	if (($('#usergroupname').val() == '') || ($('#usergroupname').val() == 'Group Name')){
        	$('#messageSellerMessageError').removeClass('hide');
            $('#clearDivAfterErrorMsg').removeClass('hide');
            $('#messageSellerMessageError').html('Please fill the group name !');                                
            setTimeout(function(){
                $('#messageSellerMessageError').html('');                                
                $('#messageSellerMessageError').addClass('hide');
                $('#clearDivAfterErrorMsg').addClass('hide');
            }, 2000);
            return false;            
        }else{
       		location.reload('<?php echo WEB_PATH?>users/createnewgroup/');
        }
    });
    function resetGroupModelBox(){
        $("#usergroupname").val('');
        $("#usergroupname").addClass('hide');
        $("#usergroupdesc").val('');
        $("#usergroupdesc").addClass('hide');
        $("#usergroupnameReadOnly").removeClass('hide');
        $("#usergroupdescReadOnly").removeClass('hide');
        $("#groupCreationStatus").html('');                                                          
        $('#group__mask').trigger('click');                        
    }
	$('.groupLetter').click(function(){
    	var groupNamesForLetters = "";
        $("#groupSearchByLetterStatus").removeClass("hide");
		$("#groupSearchByLetterStatus").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Checking availability...</span>');        
        $("#groupNameByAlphabetLetter").html("");
    	$(".groupLetter").removeClass("selectedGroupName");
    	$(this).addClass("selectedGroupName");
    	$.getJSON("<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=getGroupNameByLetter&letterG=" + $(this).find("span").html(), function(data) {
        	 if ((data != null) && (data != "")){
                 $.each(data, function(i) {
                    groupNamesForLetters += "<a title='" + data[i].Group_desc + "' href='javascript:void(0);' id=" + data[i].GroupId + " class='groupLetterRepresentGroup'><span style='color:#069;'>" + data[i].Group_name + "</span></a>";
					if (data[i].groupProfilePic != ''){
                    	groupNamesForLetters += "<img style='width:50px; height:50px;' src='<?php echo WEB_PATH?>/uploaded/groups/" + data[i].groupProfilePic + "' /><br />";                    
					}else{
                    	groupNamesForLetters += "<img style='width:50px; height:50px;' src='<?php echo WEB_PATH?>/public/images/defaultlarge.gif' /><br />";                    
                    }
                    if (data[i].leaveStatus == false){
	                    groupNamesForLetters += "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnk_" + data[i].GroupId + "' class='joinGroupLink hide'>Join Group</a><br /><br />";                    
                 	}else{
						groupNamesForLetters += "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnk_" + data[i].GroupId + "' class='leaveGroupLink hide'>Leave Group</a><br /><br />";                    
                    }
                 });
                 $("#groupNameByAlphabetLetter").html(groupNamesForLetters);
                // This is to show and hide Join group label in the whole group area
                $(".groupLetterRepresentGroup").mouseover(function(){
                    $(".joinGroupLink").addClass('hide');
                    $("#joinGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');
                    $("#leaveGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');
                });
                $(".joinGroupLink").mouseout(function(){
                    $(".joinGroupLink").addClass('hide');
                });
                $(".leaveGroupLink").mouseout(function(){
                    $(".leaveGroupLink").addClass('hide');
                });
                // This is to work on leave group
                $(".leaveGroupLink").click(function(){
                    var toBeLeaveGroupId = $(this).attr('id').replace('leaveGrpLnk_', '');
                    $.ajax({  
                        type: "POST",
                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=leaveThisGroup", 
                        data: "leaveGroupId=" + toBeLeaveGroupId, 
                        success: function(server_response){				
                            if ((server_response != false) && (server_response != null)){
                                $("#leaveGrpLnk_" + server_response).replaceWith("<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnk_" + server_response + "' class='joinGroupLink hide'>Join Group</a>");
                                location.reload('<?php echo WEB_PATH?>');
                            }
                        }
                    });
                });
                // This is to work on join group
                $(".joinGroupLink").click(function(){
                    var toBeJoinedGroupId = $(this).attr('id').replace('joinGrpLnk_', '');
                    $.ajax({  
                        type: "POST",
                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=joinToThisGroup", 
                        data: "groupIdToJoin=" + toBeJoinedGroupId, 
                        success: function(server_response){				
                            if ((server_response != false) && (server_response != null)){
                                $("#joinGrpLnk_" + server_response).replaceWith("<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnk_" + server_response + "' class='leaveGroupLink hide'>Leave Group</a>");
                                location.reload('<?php echo WEB_PATH?>');
                            }
                        }
                    });
                });
             }else{
                 $("#groupNameByAlphabetLetter").html("No Groups !");
             }  
			 $("#groupSearchByLetterStatus").html('');                                        
        });
    });
	if (window.location.pathname == '/www.rehand.com/users/groups/'){    
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=autoComTextsForGroups', function(data) {
            if ((data != "") && (data != null)){
            	$("#groupNameForSearch").focus().autocomplete({source: data});
            }	
        });					
	}    
    // This is for the search group button function
    $("#searchGroupName").click(function(){    
    	if ($("#groupNameForSearch").val() != ""){
            $.ajax({  
                type: "POST",
                url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=searchForGroup", 
                data: "searchGrpName=" + $("#groupNameForSearch").val(), 
                success: function(server_response){				
                    if ((server_response != '') && (server_response != null)){
                    	$("#searchGroupResults").removeClass('hide');
                        $("#searchGroupResults").html('Your search result is as follows !');
                        $("#searchResultsShower").removeClass('hide');
                        $("#searchResultsShower").html(server_response);
                    }
                    $(".groupLetterRepresentGroup").mouseover(function(){
                        $("#joinGrpLnkForSearch_"+ $(this).attr('id').replace('group_res_', '')).removeClass('hide');
                        $("#leaveGrpLnkForSearch_"+ $(this).attr('id').replace('group_res_', '')).removeClass('hide');
                    });
                    $(".leaveGroupLinkForSearch").mouseout(function(){
                    	$(".leaveGroupLinkForSearch").addClass('hide');
                    });
                    $(".joinGroupLinkForSearch").mouseout(function(){
                    	$(".joinGroupLinkForSearch").addClass('hide');
                    });
                    // This is for the search results
                    $(".leaveGroupLinkForSearch").click(function(){
                        var toBeLeaveGroupIdInSearch = $(this).attr('id').replace('leaveGrpLnkForSearch_', '');
                        $.ajax({  
                            type: "POST",
                            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=leaveThisGroup", 
                            data: "leaveGroupId=" + toBeLeaveGroupIdInSearch, 
                            success: function(server_response){				
                                if ((server_response != false) && (server_response != null)){
                                    $("#resetSearchGroupName").trigger('click'); 
                                    $("#tagPanelMesseging").removeClass("hide");
                                    $("#tagPanelMesseging").removeClass("errorMsgInTag");
                                    $("#tagPanelMesseging").addClass("successMsgInTag");
                                    $("#tagPanelMesseging").fadeIn("slow");
                                    $("#tagPanelMesseging").html("You have left this group");
                                    setTimeout(function(){
                                        $("#tagPanelMesseging").fadeOut("slow");
                                    }, 3000);
                                }
                            }
                        });
                    });
                    // This is to work on join group
                    $(".joinGroupLinkForSearch").click(function(){
                        var toBeJoinedGroupIdInSearch = $(this).attr('id').replace('joinGrpLnkForSearch_', '');
                        $.ajax({  
                            type: "POST",
                            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=joinToThisGroup", 
                            data: "groupIdToJoin=" + toBeJoinedGroupIdInSearch, 
                            success: function(server_response){				
                                if ((server_response != false) && (server_response != null)){
                                    $("#resetSearchGroupName").trigger('click'); 
                                    $("#tagPanelMesseging").removeClass("hide");
                                    $("#tagPanelMesseging").removeClass("errorMsgInTag");
                                    $("#tagPanelMesseging").addClass("successMsgInTag");
                                    $("#tagPanelMesseging").fadeIn("slow");
                                    $("#tagPanelMesseging").html("You have joined to this group");
                                    setTimeout(function(){
                                        $("#tagPanelMesseging").fadeOut("slow");
                                    }, 3000);
                                }
                            }
                        });
                    });
                }
            });
        }else{
            $("#tagPanelMesseging").removeClass("hide");
            $("#tagPanelMesseging").removeClass("successMsgInTag");
            $("#tagPanelMesseging").addClass("errorMsgInTag");
            $("#tagPanelMesseging").fadeIn("slow");
            $("#tagPanelMesseging").html("Please enter the group name !");
            setTimeout(function(){
                $("#tagPanelMesseging").fadeOut("slow");
            }, 3000);
        }
    });
    $("#resetSearchGroupName").click(function(){    
    	$("#groupNameForSearch").val('');
        $("#searchGroupResults").addClass('hide');
        $("#searchGroupResults").html('');
        $("#searchResultsShower").addClass('hide');
        $("#searchResultsShower").html('');
    });
	$("#groupNameForSearch").keypress(function(event){
  		if (event.which == 13) {
   			$("#searchGroupName").trigger("click");
            return false;		
        }
    });
    // This is to show and hide leave group label in the logged user area
    $(".ownGroupRepresentGroupOfYour").mouseover(function(){
        $(".leaveGroupLinkOfYours").addClass('hide');
        $("#leaveGrpLnkOfYours_" + $(this).attr('id').replace('own_group_ofYour_', '')).removeClass('hide');
    });
    // This is to show and hide Join group label in the whole group area
    $(".groupLetterRepresentGroup").mouseover(function(){
        $(".joinGroupLink").addClass('hide');
        $(".leaveGroupLink").addClass('hide');        
        $("#joinGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');                
		$("#leaveGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');                                    
    });
    $(".joinGroupLink").mouseout(function(){
        $(".joinGroupLink").addClass('hide');
    });
    $(".leaveGroupLinkOfYours").mouseout(function(){
        $(".leaveGroupLinkOfYours").addClass('hide');
    });
    $(".leaveGroupLink").mouseout(function(){
    	$(".leaveGroupLink").addClass('hide');
    });
    $(".allGroupTab").click(function(){
    	var groupNamesForLetters = "", correResults = "", groupLetter = "";
        $(".joinGroupLink").addClass('hide'); 
        $(".leaveGroupLink").addClass('hide');   
        // Check the which letter having the groups and show them to front
		$.getJSON("<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=initShowtheAllGroups", function(data) {        
            if ((data.results != '') && (data.results != null)){
            	correResults = data.results;
                $(".groupLetter").removeClass('selectedGroupName');
                groupLetter = $(".groupLetter");
                $(groupLetter).each(function(i){
                    if ($(this).find("span").html() == data.whichLetter){
                        $(this).addClass('selectedGroupName');
                    }
                });
                $.each(correResults, function(i) {
                    groupNamesForLetters += "<a title='" + correResults[i].Group_desc + "' href='javascript:void(0);' id=" + correResults[i].GroupId + " class='groupLetterRepresentGroup'><span style='color:#069;'>" + correResults[i].Group_name + "</span></a>";
					if (correResults[i].groupProfilePic != ''){
                    	groupNamesForLetters += "<img style='width:50px; height:50px;' src='<?php echo WEB_PATH?>/uploaded/groups/" + correResults[i].groupProfilePic + "' /><br />";                    
					}else{
                    	groupNamesForLetters += "<img style='width:50px; height:50px;' src='<?php echo WEB_PATH?>/public/images/defaultlarge.gif' /><br />";                    
                    }
                    if (correResults[i].leaveStatus == false){
						groupNamesForLetters += "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnk_" + correResults[i].GroupId + "' class='joinGroupLink hide'>Join Group</a><br /><br />";                    
					}else{
						groupNamesForLetters += "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnk_" + correResults[i].GroupId + "' class='leaveGroupLink hide'>Leave Group</a><br /><br />";                    
					}
                });
				$("#groupNameByAlphabetLetter").html(groupNamesForLetters);
                // This is to show and hide Join group label in the whole group area
                $(".groupLetterRepresentGroup").mouseover(function(){
                    $(".joinGroupLink").addClass('hide');
                    $("#joinGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');
                    $("#leaveGrpLnk_" + $(this).attr('id').replace('group_', '')).removeClass('hide');                    
                });
                $(".joinGroupLink").mouseout(function(){
                    $(".joinGroupLink").addClass('hide');
                });
                $(".leaveGroupLink").mouseout(function(){
                    $(".leaveGroupLink").addClass('hide');
                });
                // This is to work on leave group
                $(".leaveGroupLink").click(function(){
                    var toBeLeaveGroupId = $(this).attr('id').replace('leaveGrpLnk_', '');
                    $.ajax({  
                        type: "POST",
                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=leaveThisGroup", 
                        data: "leaveGroupId=" + toBeLeaveGroupId, 
                        success: function(server_response){				
                            if ((server_response != false) && (server_response != null)){
                                $("#leaveGrpLnk_" + server_response).replaceWith("<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnk_" + server_response + "' class='joinGroupLink hide'>Join Group</a>");
                                location.reload('<?php echo WEB_PATH?>');
                            }
                        }
                    });
                });
                // This is to work on join group
                $(".joinGroupLink").click(function(){
                    var toBeJoinedGroupId = $(this).attr('id').replace('joinGrpLnk_', '');
                    $.ajax({  
                        type: "POST",
                        url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=joinToThisGroup", 
                        data: "groupIdToJoin=" + toBeJoinedGroupId, 
                        success: function(server_response){				
                            if ((server_response != false) && (server_response != null)){
                                $("#joinGrpLnk_" + server_response).replaceWith("<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnk_" + server_response + "' class='leaveGroupLink hide'>Leave Group</a>");
                                location.reload('<?php echo WEB_PATH?>');
                            }
                        }
                    });
                });
            }else{
                 $("#groupNameByAlphabetLetter").html("No Groups !");
            }
        });
    });
    $(".yourGroupTab").click(function(){
        $(".joinGroupLink").addClass('hide'); 
        $(".leaveGroupLinkOfYours").addClass('hide');           	
    });
    // This is to work on join group
    $(".joinGroupLink").click(function(){
    	var toBeJoinedGroupId = $(this).attr('id').replace('joinGrpLnk_', '');
        $.ajax({  
            type: "POST",
            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=joinToThisGroup", 
            data: "groupIdToJoin=" + toBeJoinedGroupId, 
            success: function(server_response){				
                if ((server_response != false) && (server_response != null)){
                    $("#joinGrpLnk_" + server_response).replaceWith("<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnk_" + server_response + "' class='leaveGroupLink hide'>Leave Group</a>");
                	location.reload('<?php echo WEB_PATH?>');
                }
            }
        });
    });
    // This is to work on leave group in the your group tab
    $(".leaveGroupLinkOfYours").click(function(){
    	var toBeLeaveGroupId = $(this).attr('id').replace('leaveGrpLnkOfYours_', '');
        $.ajax({  
            type: "POST",
            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=leaveThisGroup", 
            data: "leaveGroupId=" + toBeLeaveGroupId, 
            success: function(server_response){	
                if ((server_response != false) && (server_response != null)){
                	$("#own_group_ofYour_" + toBeLeaveGroupId).remove();
                    $("#leaveGrpLnkOfYours_" + toBeLeaveGroupId).remove();
                	location.reload('<?php echo WEB_PATH?>');
                    //$(".allGroupTab").trigger('click');
                }
            }
        });
    });
    // This is for the drop down menu for the my rehands button
    $('.myMenu > li').bind('mouseover', openSubMenu); 
    $('.myMenu > li').bind('mouseout', closeSubMenu); 
    function openSubMenu() { 
        $(this).find('ul').css('visibility', 'visible'); 
    }; 
    function closeSubMenu() { 
    	$(this).find('ul').css('visibility', 'hidden'); 
    };    
    // This is to trigger my group tab click function when user selects the my groups link
    if (document.location.search.substr(1) == 'yourGroupTab=true/'){
        $(".yourGroupTab").trigger('click');
    }
    // This is for the user notification page : when the user goes in to the notification page the notification count will be reset
    if (window.location.pathname == '/www.rehand.com/users/notifications/'){
        // This is to show notification panel when click the notification link
        // Unreaded Notifications count clearing
        $("#unreadableNotificationsCount").html('');
        $.ajax({  
            type: "POST",
            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=clearingTheNotificationsCount", 
            success: function(server_response){				
                if (server_response == '1'){
                    $("#unreadableNotificationsCount").removeClass("Notifications");
                    $("#unreadableNotificationsCount").html("");
                    $(".notificationSub").removeClass("hide");
                }
            }
        });
    }
    $(document).click(function(){
        if (!contactButtonClick){
            $(".sellerContactInfoWrapper").removeClass("hide");
            contactButtonClick = false;
        }else{
            $(".sellerContactInfoWrapper").addClass("hide");
            contactButtonClick = true;
        }
        $("#notiticationsDiv").addClass("hide");
        if (notifyLinkClick){
            notifyLinkClick = false;
        }else{
            notifyLinkClick = true;
        }
    });
    // This is to show and hide the join / joined labels
    $(".groupJoinLink").live("click", function(){
    	var selectedAId = $(this).attr('id');
    	var groupIdRelatedTemp = $(this).closest('li').attr('id');
        var groupIdRelated = groupIdRelatedTemp.replace('group_', '');
        $.ajax({  
            type: "POST",
            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=joinToThisGroup", 
            data: "groupIdToJoin=" + groupIdRelated, 
            success: function(server_response){				
                if ((server_response != false) && (server_response != null)){
                	$("#" + selectedAId).removeClass('Join groupJoinLink').addClass('Joined leaveGroupLink');
                }
            }
        });
    });
    // This is to leave group and show hide the label    
    $(".leaveGroupLink").live("click", function(){  
    	var selectedAId = $(this).attr('id');
    	var groupIdRelatedTemp = $(this).closest('li').attr('id');
        var groupIdRelated = groupIdRelatedTemp.replace('group_', '');
        $.ajax({  
            type: "POST",
            url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=leaveThisGroup", 
            data: "leaveGroupId=" + groupIdRelated, 
            success: function(server_response){				
                if ((server_response != false) && (server_response != null)){
                	$("#" + selectedAId).removeClass('Joined leaveGroupLink').addClass('Join groupJoinLink');
                }
            }
        });
    });
    // This is for the notification reply
    $('.Reply').click(function(){
   		var rootNotificationId = $(this).attr('id').replace('notificationReplyId_', '');
        var orgNotificationIdInHtml = $(this).attr('id');
        // This is for the Jquery registration box
        // select all the a tag with name equal to modal
        var id = "",
            maskHeight = "",
            maskWidth = "",
            winH = "",
            winW  = "";
            
        //Get the A tag
        id = $("#notification_dialog");
     
        $.getJSON('<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=getTheOriginalNotifuyerName&orgNotifiId=' + rootNotificationId, function(data) {
            if ((data != "") && (data != null)){
            	$("#notification_boxes .titlelogin").html(data.replyHtml);
            }
        });					
     
        //Get the screen height and width
        maskHeight = $(document).height();
        maskWidth = $(window).width();
     
        //Set height and width to mask to fill up the whole screen
        $('#notification__mask').css({'width':maskWidth,'height':maskHeight});
         
        //transition effect    
        $('#notification__mask').fadeIn(1000);   
        $('#notification__mask').fadeTo("slow",0.8); 
     
        //Get the window height and width
        winH = $(window).height();
        winW = $(window).width();
               
        //Set the popup window to center
        $(id).css('top',  winH/2-$(id).height()/2);
        $(id).css('left', winW/2-$(id).width()/2);
     
        //transition effect
        $(id).fadeIn(2000);
        
         $("#contactSeller").click(function(){
            var errorInContactForm = "";
            var submittingData = "";
            $("#messageSellerMessageWaiting").removeClass("hide");
            $("#messageSellerMessageWaiting").html("&nbsp;&nbsp;&nbsp;Please wait...");
            if (($("#buyerContactEmail").val() == "") && ($("#buyerContactMessage").val() == "")){
                errorInContactForm += "Please correct the form error(s)<br />";
                errorInContactForm += "Email<br />";
                errorInContactForm += "Message<br />";
            }else{
                if ($("#buyerContactEmail").val() == ""){
                    errorInContactForm += "Please correct the form error(s)<br />";
                    errorInContactForm += "Email<br />";
                }else if (!emailReg.test($("#buyerContactEmail").val())){
                    errorInContactForm += "Please correct the form error(s)<br />";
                    errorInContactForm += "Invalid Email Address<br />";
                }else if ($("#buyerContactMessage").val() == ""){
                    errorInContactForm += "Please correct the form error(s)<br />";
                    errorInContactForm += "Message<br />";
                }    
            }
            if (errorInContactForm == ""){
                  submittingData = "buyerEmail=" + $("#buyerContactEmail").val() + "&messageFromBuyer=" + $("#buyerContactMessage").val() + "&relAId=" + rootNotificationId;
                  $("#contactSellerWaitingMsg").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Wait ...</span>');		
                  $.ajax({  
                          type: "POST",
                          data: submittingData,
                          url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendNotForSellerFromBuyerAsAReply", 
                          success: function(server_response){				
                              if (server_response == '1'){
                                  $("#messageSellerMessageWaiting").html("");
                                  $("#messageSellerMessageWaiting").addClass("hide");
                                  $("#messageSellerMessageSuccess").removeClass('hide');
                                  $("#messageSellerMessageSuccess").html('Your message sent !');                                      
                                  setTimeout(function(){
                                      $("#contactSellerWaitingMsg").addClass('hide');
                                      $("#buyerContactMessageReadOnly").removeClass("hide");
                                      $("#buyerContactMessage").addClass("hide");
                                      $("#buyerContactMessage").val(''); 
                                      $('#contact__mask').trigger("click");
                                      $("#messageSellerMessageSuccess").addClass('hide');
                                      $("#messageSellerMessageSuccess").html('');                                      
                                      $("#notification__mask").trigger('click');
                                  }, 2000);
                              }
                          }
                      });
              }else{
                    $("#messageSellerMessageError").removeClass('hide');
                    $("#messageSellerMessageError").html(errorInContactForm);                                      
                    $("#messageSellerMessageWaiting").html("");
                    $("#messageSellerMessageWaiting").addClass("hide");
                    setTimeout(function(){                    
                        $("#messageSellerMessageError").addClass('hide');
                        $("#messageSellerMessageError").html('');                                      
                    }, 2000);
              }     	
         });
        
    });    
    //if close button is clicked
    $('.notification_window .close').click(function (e) {
        //Cancel the link behavior
        e.preventDefault();
        $('#notification__mask, .notification_window').hide();
    });    
    //if mask is clicked
    $('#notification__mask').click(function () {
        $(this).hide();
        $('.notification_window').hide();
    }); 
     //This is for show and hide the email and messge text in the contact box
    $("#buyerContactMessage").addClass("hide");
    $("#buyerContactMessageReadOnly").focus(function(){
        $("#buyerContactMessageReadOnly").addClass("hide");
        $("#buyerContactMessage").removeClass("hide");
        $("#buyerContactMessage").focus();
    });
    $("#buyerContactMessage").focusout(function(){
        if ($("#buyerContactMessage").val() ==""){
            $("#buyerContactMessageReadOnly").removeClass("hide");
            $("#buyerContactMessage").addClass("hide");
        }
    });
    // This is for the user activation page and when the first time visits the page user will be stay for a while and then redirects to the profile page
	if (document.location.search.substr(1) == 'view=activated'){
        setTimeout(function(){                    
            location.href = '<?php echo WEB_PATH?>/users/profile';
        }, 2000);
    }
});