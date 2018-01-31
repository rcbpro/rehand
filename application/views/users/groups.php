<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<style>
#Groups{background:url(<?php echo SITE_IMAGES_PATH?>loginbuttonbg.jpg) repeat-x bottom!important;border:1px solid #48515A;}
#Groups a{background-position:8px bottom!important;color:#FFF!important;text-shadow:1px 1px #333;} 
#Groups .menuarrow{background-position:left bottom!important;}
</style>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	var $containerForGroups = $('#GroupsMain');
	$containerForGroups.imagesLoaded(function(){
	  $containerForGroups.masonry({
		itemSelector: '.box',
		isAnimated: true
	  });
	});
});	
</script>    
<div id="GroupsMenu">
	<strong>Search Group</strong>
    <input type="text" name="groupNameForSearch" id="groupNameForSearch" class="GroupSearch" value="Enter keyword here..." onBlur="if (this.value == '') {this.value = 'Enter keyword here...'; this.type='text'}" onFocus="if (this.value == 'Enter keyword here...') {this.value = '';}" />
    <input type="button" name="searchGroupName" id="searchGroupName" value="Search" />
    <input type="button" name="resetSearchGroupName" id="resetSearchGroupName" value="Reset" />
    <a id="createYourGroupLink" name="createYourGroupLink" href="#group_dialog">Create your own group</a>
    
    <?php if (isset( $_GET['continue'] )) :?>
    <a  class="GreenBut" style="float:right;margin-right:25px;" href="<?php echo WEB_PATH ?>">Take me to the home page</a>
    <?php endif;?>
    
</div>
<div class="clear"></div>
<div id="allWrapper" style="margin-top:0;">
	<span id="searchGroupResults" class="hide"></span>
    <div id="searchResultsShower" class="hide"></div>
    <?php if ($ViewableData['groupErrors']['finalResult'] != "No Errors"):?>
        <?php if ($ViewableData['groupErrors']['errorsInForm'] != ""):?>
        <span style="color:#F00;float: left;margin-top:79px;">Please fill the group name !</span>
        <?php endif;?>
    <?php endif;?>
    <ul id="GroupsMain">
    	<?php foreach($ViewableData['groups']['allGroupNames'] as $eachGroup):?>
        <li class="thumb box" id="group_<?php echo $eachGroup['GroupId']?>"><h2 class="GroupsTitle"><span class="floatl"><?php echo $eachGroup['Group_name']?></span>
                <?php if (!$eachGroup['joinedStatus']):?>
                <a id="groupLink_<?php echo $eachGroup['GroupId']?>" href="javascript:void(0);" class="Join groupJoinLink"></a>
                <?php else:?>
                <a id="groupLink_<?php echo $eachGroup['GroupId']?>" href="javascript:void(0);" class="Joined leaveGroupLink"></a>            
                <?php endif;?>
            </h2>
            <div class="GalMain">
                <a href="javascript:void(0);" class="floatl"><div class="GalImg"><img height="220" width="300" src="<?php echo self::$globalConfig['base_url'].(($eachGroup['profilePic'] != '' && !file_exists(self::$globalConfig['base_url'].$eachGroup['profilePic'])) ? $eachGroup['profilePic'] : 'public/images/group.jpg')?>"></div></a><div class="clear"></div>
                <div style="position:relative;float:left;">
                    <div class="LocationMain"><span class="noOfMembers" title="<?php echo $eachGroup['noOfMembers']?> followers"><?php echo $eachGroup['noOfMembers']?></span><span class="noOfItems" title="<?php echo $eachGroup['noOfItems']?> items for sale"><?php echo $eachGroup['noOfItems']?></span></div>
                </div>
                <div class="clearH10"></div>
                <a href="<?php echo self::$globalConfig['base_url']?>users/viewgroup/<?php echo $eachGroup['Group_name']?>/" class="ViewGroup"></a>
                <?php echo $eachGroup['Group_desc']?>
            </div>    
        </li>
    	<?php endforeach;?>
	</ul>
</div>