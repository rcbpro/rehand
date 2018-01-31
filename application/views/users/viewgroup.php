<div id="GroupsInner">
	<div class="TextWrap"><h2><?php echo $ViewableData['groupDetail']['Group_name'] ?></h2><?php echo $ViewableData['groupDetail']['Group_desc']?></div>
    <a href="<?php echo self::$globalConfig['base_url']?>users/uploader/?view=add">Post an item</a>
</div>
<div class="clear"></div>  
<div id="allWrapper" style="margin-top:0;"> 
    <!--<span id="availability_status"></span>-->
    <div id="searchResult" class="hide"></div>
    <ul id="container"></ul>
    <div id="scrollToTop" class="hide">
    	<a href="javascript:void(0);" class="Scroll">Scroll To Top</a>
    </div>
    <div id="endOfImageLotMessage" class="hide">
		<?php if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))):?>        
        <div id="loadOtherImages" class="hide">Load images from all the groups</div>       
        <?php endif;?>
        <div id="NoMore" class="NoMore">No more images to load</div>
    </div>
    <div id="checkingForImgs" class="hide">&nbsp;&nbsp;&nbsp;<img src="<?php echo self::$globalConfig['base_url']?>public/images/indicator.gif" title="Loading..." />Loading images...</div>
    <!-- End of the image loader div placements -->
    <div class='clear'></div>
</div>