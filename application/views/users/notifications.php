<?php
	foreach($ViewableData['allNotifications'] as $eachNotification){
		if ($eachNotification['notificationDate'] == date("Y-m-d")){
			$todayNotifications[] = $eachNotification;
		}else if ($eachNotification['notificationDate'] == date("Y-m-d", strtotime("-1 days",time()))){
			$yesterdayNotifications[] = $eachNotification;			
		}else {
			$oldDaysNotification[$eachNotification['notificationDate']][] = $eachNotification;			
		}
	}
?>
<style>
#Inbox{background:url(<?php echo SITE_IMAGES_PATH?>loginbuttonbg.jpg) repeat-x bottom!important;border:1px solid #48515A;}
#Inbox a{background-position:8px bottom!important;color:#FFF!important;text-shadow:1px 1px #333;} 
#Inbox .menuarrow{background-position:left bottom!important;}
</style>
<div id="allWrapper">
    <div class="PageContent">
        <h3 class="titlelogin">Notification Center</h3><a id="back-to-rehand" href="<?php echo self::$globalConfig['base_url']?>"></a>
        <div class="clearH"></div>
            <ul id="NotificationsMain">
			<?php if ((!$ViewableData['invalidPage']) && (is_numeric($_GET['page']))):?>            
            <!-- This is for today -->
            <?php if (!empty($todayNotifications)):?>
           	<h2>Sent Today</h2>		
				<?php foreach($todayNotifications as $eachTodayNotifiation):?>
                    <li>
                        <img src="<?php echo self::$globalConfig['base_url'].(($eachTodayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name'] != "") ? 
                                                                             (file_exists($_SERVER['DOCUMENT_ROOT'].DS.'www.rehand.com'.DS.'uploaded'.DS.'profiles'.DS.$eachTodayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) ? ('uploaded'.DS.'profiles'.DS.$eachTodayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) : ('public'.DS.'images'.DS.'defaulttiny.gif')) :
                                                                             ('public'.DS.'images'.DS.'defaulttiny.gif'));
                                    ?>" />
						<?php echo $eachTodayNotifiation['notificationText'];?>
                        <span class="NDate"><?php echo date("g:m:s", strtotime($eachTodayNotifiation['notificationTime'])) . ' ' . date("a", strtotime($eachTodayNotifiation['notificationTime']));?></span>
                        <a id="notificationReplyId_<?php echo $eachTodayNotifiation['encryptedId']?>" href="javascript:void(0);" class="Reply"></a>
                    </li>
                <?php endforeach;?>    
            <?php endif;?>
            <!-- This is for yesterday -->
            <?php if (!empty($yesterdayNotifications)):?>
            <h2>Sent Yesterday</h2>		            
				<?php foreach($yesterdayNotifications as $eachYesterdayNotifiation):?>
                    <li>
                        <img src="<?php echo self::$globalConfig['base_url'].(($eachYesterdayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name'] != "") ? 
                                                                             (file_exists($_SERVER['DOCUMENT_ROOT'].DS.'www.rehand.com'.DS.'uploaded'.DS.'profiles'.DS.$eachYesterdayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) ? ('uploaded'.DS.'profiles'.DS.$eachYesterdayNotifiation['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) : ('public'.DS.'images'.DS.'defaulttiny.gif')) :
                                                                             ('public'.DS.'images'.DS.'defaulttiny.gif'));
                                    ?>" />
                        <?php echo $eachYesterdayNotifiation['notificationText'];?>
                        <span class="NDate"><?php echo date("g:m:s", strtotime($eachTodayNotifiation['notificationTime'])) . ' ' . date("a", strtotime($eachYesterdayNotifiation['notificationTime']))?></span>
                        <a id="notificationReplyId_<?php echo $eachYesterdayNotifiation['encryptedId']?>" href="javascript:void(0);" class="Reply"></a>
                    </li>
                <?php endforeach;?>
            <?php endif;?>
            <!-- This is for other old days -->
            <?php foreach($oldDaysNotification as $eachOldDayNotifiationKey => $eachOldDayNotifiationDetails):?>
            	<h2>Sent <?php echo $eachOldDayNotifiationKey?></h2>
                <?php foreach($eachOldDayNotifiationDetails as $eachSingleOldDayNotification):?>
                <li>
                    <img src="<?php echo self::$globalConfig['base_url'].(($eachSingleOldDayNotification['notifiyedPersonProfPic'][0]['profile_thumb_image_name'] != "") ? 
																		 (file_exists($_SERVER['DOCUMENT_ROOT'].DS.'www.rehand.com'.DS.'uploaded'.DS.'profiles'.DS.$eachSingleOldDayNotification['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) ? ('uploaded'.DS.'profiles'.DS.$eachSingleOldDayNotification['notifiyedPersonProfPic'][0]['profile_thumb_image_name']) : ('public'.DS.'images'.DS.'defaulttiny.gif')) :
																		 ('public'.DS.'images'.DS.'defaulttiny.gif'));
								?>" />
                    <?php echo $eachSingleOldDayNotification['notificationText'];?>
                    <span class="NDate"><?php echo date("g:m:s", strtotime($eachSingleOldDayNotification['notificationTime'])) . ' ' . date("a", strtotime($eachSingleOldDayNotification['notificationTime']))?></span>
                    <a id="notificationReplyId_<?php echo $eachSingleOldDayNotification['encryptedId']?>" href="javascript:void(0);" class="Reply"></a>
                </li>
                <?php endforeach;?>
			<?php endforeach;?>   
            <?php endif;?>
            </ul>
            <div class="clear"></div>
        	<div id="clearGroupMainshadow" style="margin-left:5px;"></div>
            <div class="clear"></div>
			<?php if ((!$ViewableData['invalidPage']) && (is_numeric($_GET['page']))):?>
            <div class="paginationContainer"><?php echo $ViewableData['pagination'];?></div>
            <?php elseif (($_GET['page'] == 1) && ($ViewableData['invalidPage'] == "No_notifications")):?>
            <span>No notifications!</span>
            <?php else:?>
            <span>Invalid page!</span>
            <?php endif;?>
    	<div class="clearH10"></div>
    </div>
</div>