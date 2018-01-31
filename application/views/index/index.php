<div id="allWrapper">
	<?php
	/* 
		IE detect code 
		Added by Janaka - 2012/9/24
	*/
	
	if (isset($_SERVER['HTTP_USER_AGENT']) &&  (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) :
	?>
	<div id="Old-Browsers"><a id="Old-Browsers-Close">Close</a> Oops, this browser does not support Rehand that well.<br />To experience the full potential of Rehand, please use Chrome, Firefox or Safari.</div>
	<?php
	endif;
	?>
	<h3 id="imageHeader"></h3>
    <div id="commonSearchResultTitle">
        <div id="searchResult" class="hide"></div>
        <div id="searchResultForGroups" class="hide"></div>
    
    	<a id="itemsSearchLink" href="javascript:void(0);" class="hide">Items</a>
    	<span id="itemsSearchSpan" class="hide">Items</span>        
    	<a id="groupsSearchLink" href="javascript:void(0);" class="hide"3>Groups</a>
    	<span id="groupsSearchSpan" class="hide">Groups</span>
        
        <div id="searchResultTitleForItems" class="hide"></div>
        <div id="searchResultTitleForGroups" class="hide"></div>     
    </div>
    <div class="clear"></div>
       
    
    <ul id="container"></ul>
 
    <ul id="GroupsMain"></ul>
    <div class="clearH10"></div>   
    <!-- End of the search result result viewer -->
    <div id="scrollToTop">
    	<a href="javascript:void(0);" class="Scroll">Scroll To Top</a>
    </div>
    <div id="endOfImageLotMessage" class="hide">
		<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>        
			<?php if (!$_SESSION['searchHasBeenRequested']):?>
                <div id="loadOtherImages" class="hide">Show items from all groups</div>        
                <div id="NoMore" class="NoMore">No more images to load</div>        
            <?php unset($_SESSION['searchHasBeenRequested']);?>
            <?php endif;?>
        <?php endif;?>
    </div>
    <div id="checkingForImgs" class="hide">&nbsp;&nbsp;&nbsp;<img src="public/images/indicator.gif" title="Loading..." />Loading images...</div>
    <!-- End of the image loader div placements -->
    <div class='clear'></div>
</div>