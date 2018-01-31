<style>
#Profile{background:url(<?php echo SITE_IMAGES_PATH?>loginbuttonbg.jpg) repeat-x bottom!important;border:1px solid #48515A;}
#Profile a{background-position:8px bottom!important;color:#FFF!important;text-shadow:1px 1px #333;} 
#Profile .menuarrow{background-position:left bottom!important;}
#activationMsg{font-size:14px; text-align:center;}
#errorForTheProfImgUploader{}
</style>
<?php 
	require $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/library/profileMaker.php';
	global $error_in_profileimg_maker;
?>
<div id="allWrapper">
    <div class="PageContent">
	<a id="back-to-rehand" href="<?php echo self::$globalConfig['base_url']?>users/profile/"></a>    
	<?php if (empty($_SERVER['QUERY_STRING'])):?>
        <h3 class="titlelogin">Profile Settings</h3>
		<?php if ((isset($_SESSION['currentUserProfileUpdated'])) && ($_SESSION['currentUserProfileUpdated'])):?>
            <div class="clearH"></div>
            <span class='successMsg'>Your profile has been updated !</span>
            <?php unset($_SESSION['currentUserProfileUpdated']);?> 
        <?php endif;?>
        <div class="clearH"></div>
        <span class="Itemtitle" style="width:130px;font-size:15px;">Profile Picture</span>
        <div id="profileLargeImage">
        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$ViewableData['profileImage'])):?>
            <img src="<?php echo self::$globalConfig['base_url'].$ViewableData['profileImage']?>" class="Border" />
        <?php else:?>
            <img src="<?php echo self::$globalConfig['base_url'].'public/images/defaultlarge.gif';?>" class="Border" />
        <?php endif;?>
            <div class="clearH10"></div>
            <a class="ProfileBut" href="<?php echo self::$globalConfig['base_url']?>users/profile/?view=profpic" style="width:105px;">Change Picture</a>
        </div>
        <div class="clearH"></div>
        <div class="Border2"></div>
        <div class="clearH"></div>
        <form name="updateProfileSettingsForm" id="updateProfileSettingsForm" action="" method="post">
            <span class="Itemtitle">Name</span>
            <input type="hidden" name="profileUpdattion[uid]" id="profileUpdattionUid" value="<?=$ViewableData['profileOtherDetails']['userId']?>" />
            <input type="text" id="profileUpdattionName" name="profileUpdattion[name]" class="InputBg" value="<?php echo ($ViewableData['profileOtherDetails']['firstName']." ".$ViewableData['profileOtherDetails']['lastName'])?>" />
            <?php if ((!isset($_SESSION['fbUser'])) && (!$_SESSION['fbUser']['loggedViaFB'])):?>
                <div class="clearH10"></div>
                <span class="Itemtitle">Password</span>
                <a class="ProfileBut" href="<?php echo self::$globalConfig['base_url']?>users/newpassword">Change Password</a>
            <?php endif;?>
            <div class="clearH10"></div>
            <?php if (isset($_SESSION['fbUser'])):?>
                <span class="Itemtitle">Contact Email</span>
                <input type="text" id="profileUpdattionContactEmail" name="profileUpdattion[contactEmail]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['contactEmail']?>" /></span>
                <div class="clearH10"></div>
            <?php else:?>
                <span class="Itemtitle">Email</span>
                <input type="text" id="profileUpdattionEmail" name="profileUpdattion[email]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['email']?>" /><span id="availability_status" style="margin-left:100px;"></span>
                <div class="clearH10"></div>
            <?php endif;?>    
            <span class="Itemtitle">Date of Birth</span>
            <?php echo CommonFunctions::print_date_selecting_drop_down('profileUpdattion', (($ViewableData['profileOtherDetails']['dateOfBirth'] != "0000-00-00") ? $ViewableData['profileOtherDetails']['dateOfBirth'] : ""));?>
            <div class="clearH10"></div>
            <span class="Itemtitle">Country</span>
            <select name="profileUpdattion[country]" class="DDBg">
                <option value="">Select the country</option>
                <?php foreach(CommonFunctions::$countryList as $key => $value):?>
                <option value="<?=$key?>" <?=($ViewableData['profileOtherDetails']['country'] == $key ? "selected" : "")?>><?=$value?></option>
                <?php endforeach;?>
            </select>
            <div class="clearH10"></div>
            <span class="Itemtitle">Address</span>
            <textarea name="profileUpdattion[address]" class="TextareaBg"><?php echo $ViewableData['profileOtherDetails']['address']?></textarea>
            <div class="clearH10"></div>
            <span class="Itemtitle">Postcode</span>
            <input type="text" name="profileUpdattion[postCode]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['postCode']?>" />
            <div class="clearH10"></div>
            <span class="Itemtitle">Mobile No</span>
            <input type="text" name="profileUpdattion[mobile_no]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['mobile_no']?>" />
            <div class="clearH10"></div>
            <span class="Itemtitle">Home Tel No</span>
            <input type="text" name="profileUpdattion[home_no]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['home_no']?>" />
            <div class="clearH10"></div>
            <span class="Itemtitle">Company Name</span>
            <input type="text" name="profileUpdattion[companyName]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['companyName']?>" />
            <div class="clearH10"></div>
            <span class="Itemtitle">Gender</span>
            <select name="profileUpdattion[gender]" class="DDBg">
                <option value="">Select the gender</option>
                <?php foreach(CommonFunctions::$genderList as $key => $value):?>
                <option value="<?=$key?>" <?=($key == $ViewableData['profileOtherDetails']['gender'] ? "selected" : "")?>><?=$value?></option>
                <?php endforeach;?>
            </select>
            <div class="clearH10"></div>
            <span class="Itemtitle">Paypal ID</span>
            <input type="text" name="profileUpdattion[paypalId]" class="InputBg" value="<?php echo $ViewableData['profileOtherDetails']['paypalId']?>" />
            <div class="clearH10"></div>
            <div id="profileUpdateError"><!-- --></div>
            <div class="clearH15"></div>
            <input type="button" name="updateProfileSubmit" id="updateProfileSubmit" value="Update Profile" class="Button1" style="margin-left:130px;" />  
            <div class="clear"></div>
        </form>
        <div class="clearH"></div>
    <?php else:?>
		<?php if ($_GET['view'] == 'activated'):?>    
			<span id="activationMsg">Thanks ! you have been activated !</span>
        <?php elseif ($_GET['view'] == 'profpic'):
            //Only display the javacript if an image has been uploaded
            if (strlen($large_photo_exists) > 0):
                $current_large_image_width = getWidth($large_image_location);
                $current_large_image_height = getHeight($large_image_location);
            ?>
            <script type="text/javascript">
            function preview(img, selection) { 
                var scaleX = <?php echo $thumb_width;?> / selection.width; 
                var scaleY = <?php echo $thumb_height;?> / selection.height; 
                $('#thumbnail + div > img').css({ 
                    //width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px', 
                    height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
                    marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
                    marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
                });
                $('#x1').val(selection.x1);
                $('#y1').val(selection.y1);
                $('#x2').val(selection.x2);
                $('#y2').val(selection.y2);
                $('#w').val(selection.width);
                $('#h').val(selection.height);
            } 
            $(document).ready(function () { 
				$('#thumbnail').imgAreaSelect({ aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview, x1: 90, y1: 90, x2: 220, y2: 220 }); 										
                $('#save_thumb').click(function() {
                    var x1 = $('#x1').val();
                    var y1 = $('#y1').val();
                    var x2 = $('#x2').val();
                    var y2 = $('#y2').val();
                    var w = $('#w').val();
                    var h = $('#h').val();
                    if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
                        alert("You must make a selection first");
                        return false;
                    }else{
                        return true;
                    }
                });
            }); 
            </script>
            <?php endif;
				//Display error message if there are any
				if (strlen($error_in_profileimg_maker) > 0):?>
                <ul id="errorForTheProfImgUploader">
                    <li><?php echo $error_in_profileimg_maker?></li>
                </ul>
                <?php endif;?>
                <?php if (isset($error_in_profileimg_maker)):?>
                <div class="clearH"></div>
                <?php endif;?>
                <?php
				if (strlen($large_photo_exists) > 0 && strlen($thumb_photo_exists) > 0):
					echo "<h3 class='titlelogin'>New Profile Picture</h3><div class='clearH'></div>";
					echo $large_photo_exists."<div class='clearH'></div>".$thumb_photo_exists;
					echo "<div class='clearH'></div><a href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profImgDelete&t=".$_SESSION['random_key'].$_SESSION['user_file_ext']."' class='RedBut' style='margin-right:15px;'>Delete image</a>";
					echo "<a href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/' class='GreenBut'>Continue</a><div class='clear'></div>";
					//Clear the time stamp session and user file extension
					$_SESSION['random_key']= "";
					$_SESSION['user_file_ext']= "";
				else:
					if (strlen($large_photo_exists) > 0):?>
					<h3 class="titlelogin">Create Thumbnail</h3>
					<div class="clearH"></div>
					Click & Drag image to Crop Thumbnail
					<div class="clearH10"></div>
					<div align="center">
						<img src="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$large_image_name.$_SESSION['user_file_ext'];?>" style="float:left;" id="thumbnail" class="Border" alt="Create Thumbnail" />
						
						<div style="float:left;position:relative;overflow:hidden;width:<?php echo $thumb_width;?>px;height:<?php echo $thumb_height;?>px;margin:25px 200px 0 0;" class="Border">
							<img src="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$large_image_name.$_SESSION['user_file_ext'];?>" style="position: relative;" alt="Thumbnail Preview" />
						</div>
						<div class="clearH15"></div>
						<form name="thumbnail" action="http://<?php echo $_SERVER['HTTP_HOST'].'/www.rehand.com/library/profileMaker.php';?>" method="post">
							<input type="hidden" name="x1" value="" id="x1" />
							<input type="hidden" name="y1" value="" id="y1" />
							<input type="hidden" name="x2" value="" id="x2" />
							<input type="hidden" name="y2" value="" id="y2" />
							<input type="hidden" name="w" value="" id="w" />
							<input type="hidden" name="h" value="" id="h" />
							<input type="submit" name="upload_thumbnail" value="Save Thumbnail" id="save_thumb" class="GreenBut" />
						</form>
					</div>
			<?php elseif ($ViewableData[0]['profile_image_name'] != ""):?>
            <h3 class="titlelogin">Current Profile Picture</h3>
            <div class="clearH"></div>
                  <img src="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$ViewableData[0]['profile_image_name'];?>" style="float:left;max-width:370px;" id="thumbnail" class="Border" alt="Create Thumbnail" />
				  <div class="floatl" style="margin-left:50px;">
                  <span>Thumbnail</span><div class="clearH5"></div>
                  <div style="float:left;position:relative;overflow:hidden;width:<?php echo $thumb_width;?>px;height:<?php echo $thumb_height;?>px;" class="Border">
                      <img src="<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$ViewableData[0]['profile_thumb_image_name'];?>" style="position: relative;" alt="Thumbnail Preview" />
                  </div>
                  </div>
			<?php endif; ?>
            	<div class="clearH"></div>
                <div id="clearGroupMainshadow"></div>
                <div class="clear"></div>
				<h3 class="titlelogin">Change Profile Picture</h3><div class="clearH15"></div>
				<form name="photo" enctype="multipart/form-data" action="http://<?php echo $_SERVER['HTTP_HOST'].'/www.rehand.com/library/profileMaker.php';?>" method="post">
				<input type="file" name="image" class="floatl" /><p class="PicMin" style="margin:5px 0 0 25px;">Maximum size of 700k. JPG, GIF, PNG.</p>
                <div class="clearH15"></div>
                <input type="submit" name="upload" value="Upload Picture" class="Button1" />
				</form>
                <div class="clear"></div>
	<?php endif; 
	endif;        
endif;?>
</div>
</div>