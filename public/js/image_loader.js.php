<?php
	session_start();
	ini_set('display_errors',0);
	error_reporting(E_ALL);
	require $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/library/encdec.php';

	unset($_SESSION['images_start']);
	$number_of_images = 20; //20 at a time

	$_SESSION['images_start'] = $_SESSION['images_start'] ? $_SESSION['images_start'] : $number_of_images;
	defined('WEB_PATH') ? NULL : define('WEB_PATH', 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/');
	defined('HASHKEY') ? NULL : define('HASHKEY', 'mysecretkey');

	global $connection;
	global $pictureIds;
	$connection = mysql_connect('localhost', 'root', 'hellow0mbat!');
	mysql_select_db('rehand') or die(mysql_error());

	if ((empty($_SESSION['searchQuery'])) && (isset($_GET['start']))) {
		if (isset($_SESSION['searchQuery'])) unset($_SESSION['searchQuery']);
		echo get_images($_GET['start'], $_GET['desiredImages'], $connection);
		$_SESSION['images_start'] += $_GET['desiredImages'];
		exit();
	}else if (!empty($_SESSION['searchQuery'])){
		$enc = new Encdec();
		// This is for the full text search
		$fullTextSearchQry = "SELECT * FROM tagging__taggeditems WHERE MATCH(`tagName`, `desc`) AGAINST('".trim($_SESSION['searchQuery'])."')";
		//$likeQuery = "SELECT * FROM tagging__taggeditems WHERE tagName LIKE '%".trim($_SESSION['searchQuery'])."%'";
		$result = mysql_query($fullTextSearchQry, $connection);
		$i = 0;
		while($row = mysql_fetch_array($result)) {
			$tags[$i]['tagId'] = $enc->encrypt($row['tagId']);
			$tags[$i]['pictureId'] = $row['pictureId'];
			$tags[$i]['tagName'] = $row['tagName'];
			$i++;
		}
		$pictureIds = array();
		foreach($tags as $eachTag){
			if (!in_array($eachTag['pictureId'], $pictureIds)){
				$pictureIds[] = $eachTag['pictureId'];
			}
		}
		$_SESSION['tagRelatedPics'] = $pictureIds;
		echo get_images_for_search_result($_GET['start'], $_GET['desiredImages'], $connection, $pictureIds);
		$_SESSION['images_start'] += $_GET['desiredImages'];
		if (isset($_GET['start'])) exit();
	}

	function loadNoOfInterestsForAPicture($pictureOwner, $pictureIdHashed, $connection){
	
		$pictureId = $_SESSION['recentLoads'][$pictureIdHashed];
		$query = "SELECT noOfInterests FROM tagging__taggeditems WHERE pictureId = ".$pictureId . " AND userId = " . $pictureOwner;
		$result = mysql_query($query, $connection);
		$i = 0;
		while($row = mysql_fetch_array($result)) {
			$noOfInterests[] += $row['noOfInterests'];
		}
		foreach($noOfInterests as $eachNoOfInterests){
			$noOfInterestsFinal += $eachNoOfInterests;
		}
		return $noOfInterestsFinal;
	}

	function loadTheSuburbOfPicture($postCode, $connection){
		
		$query = "SELECT Locality FROM rehand__postalCodes WHERE Pcode = '".$postCode . "'";
		$suburbName = mysql_result(mysql_query($query, $connection), 0);
		if (str_word_count($suburbName) >= 3){
			$tempNameOfSuburb = explode(" ", $suburbName);
			foreach ($tempNameOfSuburb as $value) {
    			$newNameOfSuburb .= substr($value, 0, 1);
			}
			$newNameOfSuburb = strtoupper($newNameOfSuburb);
		}else{
			$newNameOfSuburb = strtolower($suburbName);
		}
		return ($newNameOfSuburb != "") ? $newNameOfSuburb : "";
	}
	
	function getThePictureIdsWhichUserBelongToTheseGroups($connection){
	
		$groups = array();
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];			
		$query = "SELECT `GroupId` FROM `rehand__group_members` WHERE `memberId` = " . $userId;
		$result = mysql_query($query, $connection);
		while($row = mysql_fetch_array($result)) {
			$groups[] = $row['GroupId'];
		}
		$sql = "SELECT DISTINCT(`PictureId`) FROM `rehand__group_pictures` WHERE `GroupId` IN (";
		$i = count($groups);
		foreach($groups as $eachGroupId){
			$sql .= ($i != 1) ?  $eachGroupId . "," : $eachGroupId;
			$i--;
		}
		$sql .= ")";
		$result = mysql_query($sql, $connection);
		while($row = mysql_fetch_array($result)) {
			$imagesBelongsToOwnedGroups[] = $row['PictureId'];
		}
		return $imagesBelongsToOwnedGroups;
	}
	
	function get_images($start = 0, $number_of_images = 20, $connection) {
		/*
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			$imagesBelongsToOwnedGroups = getThePictureIdsWhichUserBelongToTheseGroups($connection);
			$images = array();		
			$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0 AND pictureId IN(";
			$i = count($imagesBelongsToOwnedGroups);
			foreach($imagesBelongsToOwnedGroups as $eachPicId){
				$query .= ($i != 1) ?  $eachPicId . "," : $eachPicId;
				$i--;
			}
			$query .= " ) ORDER BY pictureId DESC LIMIT $start, $number_of_images";
		}else{
			$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0";		
		}	
		*/
		$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0";		
		
		$result = mysql_query($query, $connection);
		while($row = mysql_fetch_array($result)) {
			$images[] = $row;
		}
		$i = 0;
		foreach($images as $temObj){
			//$photos[$i]['pictureId'] = $temObj['pictureId'];
			$photos[$i]['title'] = $temObj['title'];
			$photos[$i]['location'] = $temObj['location'];
			$photos[$i]['owner'] = $temObj['userId'];
			$photos[$i]['pictureRelatedTags'] = loadRelatedTags($temObj['userId'], md5($temObj['pictureId'].HASHKEY.$temObj['userId']), $connection);
			$photos[$i]['ownerName'] = loadProfileName($temObj['userId'], $connection);		
			$photos[$i]['postCode'] = loadThePostCodeOfPictureOwner($temObj['userId'], $connection);
			$photos[$i]['profileImage'] = loadProfileImage($temObj['userId'], $connection);
			$photos[$i]['uploadLImgLocation'] = $temObj['uploadLImgLocation'];						
			$photos[$i]['uploadTImgLocation'] = $temObj['uploadTImgLocation'];
			$photos[$i]['wholeNoOfInterests'] = loadNoOfInterestsForAPicture($temObj['userId'], md5($temObj['pictureId'].HASHKEY.$temObj['userId']), $connection);
			$photos[$i]['idhash'] = md5($temObj['pictureId'].HASHKEY.$temObj['userId']);
			$_SESSION['recentLoads'][$photos[$i]['idhash']] = $temObj['pictureId'];
			$photos[$i]['Locality'] = loadTheSuburbOfPicture($photos[$i]['postCode'], $connection);
			$i++;
		}
		return json_encode($photos);
	}

	function get_images_for_search_result($start = 0, $number_of_images = 20, $connection, $pictureIds = NULL) {

		/*
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			$imagesBelongsToOwnedGroups = getThePictureIdsWhichUserBelongToTheseGroups($connection);
			foreach($pictureIds as $eachPicId){
				if (in_array($eachPicId, $imagesBelongsToOwnedGroups)){
					$modifiedPicIds[] = $eachPicId;
				}
			}
			$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0 AND pictureId IN (";
			$i = 0;
			foreach($modifiedPicIds as $eachPictureId){
				$query .= $i != 0 ? (", ".$eachPictureId) : $eachPictureId;
				$i++;
			}
			$query .= ") ORDER BY pictureId DESC LIMIT $start, $number_of_images";
		}else{
			$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0 AND pictureId IN (";
			$i = 0;
			foreach($pictureIds as $eachPictureId){
				$query .= $i != 0 ? (", ".$eachPictureId) : $eachPictureId;
				$i++;
			}
			$query .= ") ORDER BY pictureId DESC LIMIT $start, $number_of_images";
		}	
		*/
		$query = "SELECT * FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0 AND pictureId IN (";
		$i = 0;
		foreach($pictureIds as $eachPictureId){
			$query .= $i != 0 ? (", ".$eachPictureId) : $eachPictureId;
			$i++;
		}
		$query .= ") ORDER BY pictureId DESC LIMIT $start, $number_of_images";
		
		$result = mysql_query($query, $connection);
		if ($result){
			$n = 0;
			while($row = mysql_fetch_array($result)) {
				//$images[$n]['pictureId'] = $row['pictureId'];
				$images[$n]['title'] = $row['title'];
				$images[$n]['location'] = $row['location'];
				$images[$n]['owner'] = $row['userId'];
				$images[$n]['pictureRelatedTags'] = loadRelatedTags($row['userId'], md5($row['pictureId'].HASHKEY.$row['userId']), $connection);
				$images[$n]['ownerName'] = loadProfileName($row['userId'], $connection);		
				$images[$n]['postCode'] = loadThePostCodeOfPictureOwner($row['userId'], md5($row['pictureId'].HASHKEY.$row['userId']), $connection);		
				$images[$n]['profileImage'] = loadProfileImage($row['userId'], $connection);
				$images[$n]['uploadLImgLocation'] = $row['uploadLImgLocation'];						
				$images[$n]['uploadTImgLocation'] = $row['uploadTImgLocation'];
				$images[$n]['wholeNoOfInterests'] = loadNoOfInterestsForAPicture($row['userId'], md5($row['pictureId'].HASHKEY.$row['userId']), $connection);
				$images[$n]['idhash'] = md5($row['pictureId'].HASHKEY.$row['userId']);
				$_SESSION['recentLoads'][$images[$n]['idhash']] = $row['pictureId'];
				$images[$n]['Locality'] = loadTheSuburbOfPicture($images[$n]['postCode'], $connection);
				$n++;
			}
			return json_encode($images);
		}
	}
	
	function loadProfileName($pictureOwner, $connection) {
	
		$query = "SELECT firstName, lastName FROM rehand__users WHERE userId = ".$pictureOwner;
		$result = mysql_query($query, $connection);
		while($row = mysql_fetch_array($result)) {
			$profileNames['firstName'] = $row['firstName'];
			$profileNames['lastName'] = $row['lastName'];
		}
		return $profileNames['firstName'] . " " . $profileNames['lastName'];
	}

	function loadRelatedTags($pictureOwner, $pictureIdHashed, $connection){
	
		$pictureId = $_SESSION['recentLoads'][$pictureIdHashed];
		if (!empty($_SESSION['searchQuery'])){
			$where = " AND tagName LIKE '%" . trim($_SESSION['searchQuery']). "%'";
		}else{
			$where = "";
		}
		$query = "SELECT tagId, tagName, price, currentStatus FROM tagging__taggeditems WHERE pictureId = ".$pictureId . " AND userId = " . $pictureOwner . $where;
		$result = mysql_query($query, $connection);
		$i = 0;
		$enc = new Encdec();
		while($row = mysql_fetch_array($result)) {
			switch($row["currentStatus"]){
				case "available" : $price = "$".$row["price"]; break;
				case "free" : $price = "free"; break;
				case "sold" : $price = "$".$row["price"]; break;				
			}	
			$relatedTags[$i]['tagId'] = $enc->encrypt($row['tagId']);
			$relatedTags[$i]['tagName'] = $row['tagName'];
			$relatedTags[$i]['price'] = $price;
			$i++;
		}
		return $relatedTags;
	}

	function loadProfileImage($pictureOwner, $connection) {
	
		$query = "SELECT profile_thumb_image_name FROM rehand__users WHERE userId = ".$pictureOwner;
		$profileImage = mysql_result(mysql_query($query, $connection), 0);
		if ($profileImage != ""){
			$profileImage = "uploaded/profiles/".$profileImage;
		}else{
			$profileImage = "public/images/defaulttiny.gif";
		}
		return $profileImage;
	}

	function loadThePostCodeOfPictureOwner($pictureOwner, $connection) {
		
		$query = "SELECT postCode FROM rehand__users WHERE userId = ".$pictureOwner;
		return mysql_result(mysql_query($query, $connection), 0);
	}
?>
$(document).ready(function(){

	var $container,
    	initialImages,
		npoOfNewNotifications,
    	notifyLinkClick = false,
        content = "",
        contactButtonClick = true,
        selectedTagId = null,
        sellerNameClick = false,
		emailReg = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,   
        domain = '<?php echo WEB_PATH?>';
    //settings on top
    <?php if (!empty($_SESSION['searchQuery'])):?>
    	initialImages = <?php echo (get_images_for_search_result(0, $_SESSION['images_start'], $connection, $pictureIds) ? get_images_for_search_result(0, $_SESSION['images_start'], $connection, $pictureIds) : "null"); ?>;
    <?php else:?>
    	initialImages = <?php echo (get_images(0, $_SESSION['images_start'], $connection) ? get_images(0, $_SESSION['images_start'], $connection) : "null"); ?>;
    <?php endif;?>
    //function that creates images
    var imageHandler = function(data) {
       if (data != null){
       		<?php if (!empty($_SESSION['searchQuery'])):?>
       		$("<span class=searchSuccess><b>" + data.length + ((data.length > 1) ? ' rehands found !' : ' rehand found !') + " </b></span>").appendTo($('#allWrapper'));
            $("#container").css({"top" : "55px"});
            <?php endif;?>
            $.each(data,function(i,tmpImg) {
                //image url
                var imageURL = '' + domain + tmpImg.uploadTImgLocation;
                var id = 'image-' + tmpImg.ID;
                content = "";
                content += "<li class='box photo col3'><div class='GalMain'><strong>" + ((tmpImg.title.length > 20) ? tmpImg.title.substring(0, tmpImg.title.length - 20) + "..." : tmpImg.title) + "</strong><a href='javascript:vold(0);' class='GalShareSmall'></a><a href='<?php echo WEB_PATH?>"+ tmpImg.uploadLImgLocation + "' id='" + tmpImg.idhash + "' rel='example_group'";
                content += " title='" + tmpImg.title + "'"; 
                content += " class='GalViewSmall'></a><div class='clearH10'></div><div class='imageWrapperForScroll'><a href='<?php echo WEB_PATH?>"+ tmpImg.uploadLImgLocation + "' id='" + tmpImg.idhash + "' rel='example_group'"; 
                content += " title='" + tmpImg.title + "'"; 
                content += " class='floatl'>";
                content += "<div class='GalImg'><img id='"+tmpImg.idhash+"' src='<?php echo WEB_PATH?>" + tmpImg.uploadTImgLocation + "' /></div></a>";			                                      
                content += "<div class='clearH15'></div>";
                content += "<div class='floatr'>";
                if (tmpImg.Locality != ""){
                    content += "<span class='PostCode'><strong>" + tmpImg.Locality + "</strong></span>";
                }
                content += "<div class='clearH10'></div><span class='Intrested'><strong><span id='noOfInterests'>" + tmpImg.wholeNoOfInterests + "</span></strong> Interested</span></div>";
                content += "<div class='ownedby'><img style='width:36px; height:36px;' src='<?php echo WEB_PATH?>" + tmpImg.profileImage + "' /><div class='ownedbymain'><a id='contatLink_" + tmpImg.idhash + "' class='contactLink' href='javascript:vold(0);'>Contact<div class='clear'></div><span>" + tmpImg.ownerName + "</span></a></div><div class='clear'></div></div>";
                //content += "<a href='#' rel='" + tmpImg.idhash + "' class='sellerName'><img src='<?php echo WEB_PATH?>public/images/message.png' /> Message Seller</a></div><div class='clear'></div>";
                content += "<div id='sellerInfoWrapper_" + tmpImg.idhash + "' class='sellerContactInfoWrapper hide'></div></div>";
                content += "</div>";
                if (tmpImg.pictureRelatedTags){
                    $.each(tmpImg.pictureRelatedTags, function(n) {
                        content += "<a href='javascript:vold(0);' class='TagsH'><span lang='" + tmpImg.pictureRelatedTags[n].tagId + "' class='TagHName " + tmpImg.idhash + "'>" + tmpImg.pictureRelatedTags[n].tagName + "</span><span lang=' " + tmpImg.pictureRelatedTags[n].tagId + "' class='Price " + tmpImg.idhash + "'>" + tmpImg.pictureRelatedTags[n].price + "</span></a>";
                    });
                }
                content += "<div class='clearH5'></div></li>";                
                $(content).appendTo($('#container')).hide().slideDown(250, function() {
                    $container = $('#container');
                    $container.imagesLoaded(function(){
                      $container.masonry({
                        itemSelector: '.box',
                        isAnimated: true
                      });
                    });
                });
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
                        	
                            $("#fancybox-title").css({"display" : "block"});
                            $("#fancybox-title").html(((e.title.length > 20) ? e.title.substring(0, e.title.length - 20) + "..." : e.title) + "<label style='position:absolute; right:10px; color:#FFF; cursor:pointer;'><input type='checkbox' class='hideTagsLink' />Hide tags</label>");
                            
                            $($(document).find("#fancybox-outer")).TagMe({
                                id:$(e.orig).attr('id'),
                                loadTags:true,
                                loadTagsAction:{
                                                    url:"<?php echo WEB_PATH?>library/tagMeCall.php<?php echo (!empty($_SESSION['searchQuery']) ? "?searchTag=".$_SESSION['searchQuery'] : "")?>",
                                                    onProgress:function(ele){},
                                                    onSuccess:function(ele){
                                                    	//$("#".selectedTagId).trigger('click');
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
                                        '<span id="itemName"></span><div class="clearH5"></div>'+
                                        '<span id="itemDescription"></span><div class="clearH15"></div>'+									 
                                        '<input type="button" id="buybtn" value="Buy Now" class="GreenBut buyNowBtn" style="float:right;" />' + 
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
                 $("#container").preloader();      
            });
        }else{
        	content = "<span class=searchError>No matched results found !</span>";
            $('#container').html(content);
            $('#load-more').css({"display" : "none"});
        }
    };
    //place the initial images in the page
    imageHandler(initialImages);
    //first, take care of the "load more"
    //when someone clicks on the "load more" DIV
    var start = <?php echo $_SESSION['images_start']; ?>;
    var desiredImages = <?php echo $number_of_images; ?>;
    var loadMore = $('#load-more');
    //load event / ajax
    loadMore.click(function(){
        //add the activate class and change the message
        loadMore.addClass('activate').text('Loading...');
        //begin the ajax attempt
        $.ajax({
            url: '<?php echo WEB_PATH?>public/js/image_loader.js.php',
            data: {
                'start': start,
                'desiredImages': desiredImages
            },
            type: 'get',
            dataType: 'json',
            cache: false,
            success: function(responseJSON) {
            	if (responseJSON){
                    //reset the message
                    loadMore.text('Load More...');
                    //increment the current status
                    start += desiredImages;
                    //add in the new images
                    imageHandler(responseJSON);
                    $container.masonry('reload');
				}else{
	               loadMore.addClass('disabled');
                   loadMore.text("That's all...");
                }
            },
            //failure class
            error: function() {
                //reset the message
                loadMore.text('Oops! Try Again.');
            },
            //complete event
            complete: function() {
                //remove the spinner
                loadMore.removeClass('activate');
            }
        });
    });
    
    $("#SearchB").click(function(){
   		if (($("#SearchT").val() != "") && ($("#SearchT").val() != "Search Rehands...")){
        	location.href= "<?php echo WEB_PATH?>?searchQ=" + $("#SearchT").val();
        }
    });

    $("#SearchT").keypress(function(event) {
  		if (event.which == 13) {
   			$("#SearchB").trigger("click");		
        }
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
    <?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>
		<?php if ((isset($_SESSION['selectedItemStatusNotLogged'])) && (isset($_SESSION['selectedTagIdNotLogged'])) && (isset($_SESSION['selectedLinkNotLogged']))):?>
            var aId = '<?php echo $_SESSION['selectedLinkNotLogged'];?>';
            var tId = '<?php echo $_SESSION['selectedTagIdNotLogged'];?>';
            selectedTagId = $.trim(tId);
            $("#"+$.trim(aId)).trigger('click');
        <?php endif;?>
    <?php endif;?>
    
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
        setTimeout(function(){
    		loadAllNotifications();
    	}, 5000);        
    }
    
    // This is to show notification panel when click the notification link
    $("#myNotifications").click(function(e){
    	// Unreaded Notifications count clearing
        if ($("#unreadableNotificationsCount").html() != ""){
        	$.ajax({  
                    type: "POST",
                    url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=clearingTheNotificationsCount", 
                    success: function(server_response){				
                        if (server_response == '1'){
                            $("#unreadableNotificationsCount").removeClass("Notifications");
   	     					$("#unreadableNotificationsCount").html("");
                            $("#notiticationsDiv").removeClass("hide");
                            $(".notificationSub").removeClass("hide");
                            notifyLinkClick = true;
                        }
                    }
                });
        }
        e.stopPropagation();
        if (!notifyLinkClick){
            $("#notiticationsDiv").removeClass("hide");
        	$(".notificationSub").removeClass("hide");
        	notifyLinkClick = true;
        }else{
        	$("#notiticationsDiv").addClass("hide");
            $(".notificationSub").addClass("hide");
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
        if (notifyLinkClick){
        	notifyLinkClick = false;
        }else{
        	notifyLinkClick = true;
        }
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
         
         //This is for show and hide the email and messge text in the contact box
        $("#buyerContactEmail").addClass("hide");
        $("#buyerContactEmailReadOnly").focus(function(){
            $("#buyerContactEmailReadOnly").addClass("hide");
            $("#buyerContactEmail").removeClass("hide");
            $("#buyerContactEmail").focus();
        });
        $("#buyerContactEmail").focusout(function(){
            if ($("#buyerContactEmail").val() ==""){
                $("#buyerContactEmailReadOnly").removeClass("hide");
                $("#buyerContactEmail").addClass("hide");
            }
        });
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
       
         // This is to get the currently selected link ID
         selectedAID = $(this).attr('id').replace("contatLink_", "");   
         $("#contactSeller").click(function(){
            var errorInContactForm = "";
            var submittingData = "";
        <?php if ((!isset($_SESSION['currentUser'])) && (!isset($_SESSION['fbUser']))):?>
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
                }else{
                    submittingData = "buyerEmail=" + $("#buyerContactEmail").val() + "&messageFromBuyer=" + $("#buyerContactMessage").val() + "&relAId=" + selectedAID;
                }
            }
        <?php else:?>
            if ($("#buyerContactMessage").val() == ""){
                errorInContactForm += "Please correct this error<br />";
                errorInContactForm += "Message<br />";
            }else{
                submittingData = "messageFromBuyer=" + $("#buyerContactMessage").val() + "&relAId=" + selectedAID;
            }
        <?php endif;?>
            if (errorInContactForm == ""){
            	  $("#contactSellerWaitingMsg").html('<img src="<?php echo WEB_PATH?>/public/images/loader.gif" class="Floatmargin"> <span>Wait ...</span>');		
                  $.ajax({  
                          type: "POST",
                          data: submittingData,
                          url: "<?php echo WEB_PATH?>/library/ajaxFunctions.php?action=sendMailNotForSellerFromBuyer", 
                          success: function(server_response){				
                              if (server_response == '1'){
                                  $("#tagPanelMesseging").removeClass("hide");
                                  $("#tagPanelMesseging").removeClass("errorMsgInTag");
                                  $("#tagPanelMesseging").addClass("successMsgInTag");
                                  $("#tagPanelMesseging").fadeIn("slow");
                                  $("#tagPanelMesseging").html("Successfully sent the message : Thanks!");
                                  setTimeout(function(){
                                      $("#tagPanelMesseging").fadeOut("slow");
                                  }, 3000);
                                  $("#contactSellerWaitingMsg").addClass('hide');
                                  $("#buyerContactEmail").val('');
                                  $("#buyerContactMessage").val(''); 
                                  $('#contact__mask').trigger("click");
                              }
                          }
                      });
            }else{
                  $("#tagPanelMesseging").removeClass("hide");
                  $("#tagPanelMesseging").removeClass("successMsgInTag");
                  $("#tagPanelMesseging").addClass("errorMsgInTag");
                  $("#tagPanelMesseging").fadeIn("slow");
                  $("#tagPanelMesseging").html(errorInContactForm);
                  setTimeout(function(){
                      $("#tagPanelMesseging").fadeOut("slow");
                  }, 3000);
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
});