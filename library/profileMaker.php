<?php
session_start();
error_reporting (E_ALL ^ E_NOTICE);
ini_set('display_errors', 'off');
//unset($_SESSION['user_file_ext']);
//if ((isset($_GET['view'])) && ($_GET['view'] == "profpic")){
//only assign a new timestamp if the session variable is empty
if (!isset($_SESSION['random_key']) || strlen($_SESSION['random_key'])==0){
    $_SESSION['random_key'] = strtotime(date('Y-m-d H:i:s')); 
	$_SESSION['user_file_ext']= "";
}

include '../library/commonFunctions.php';

$upload_dir = "uploaded/profiles"; 			
$upload_path = $upload_dir."/";				
$large_image_prefix = "resize_"; 			
$thumb_image_prefix = "thumbnail_";			
$large_image_name = $large_image_prefix.$_SESSION['random_key'];
$thumb_image_name = $thumb_image_prefix.$_SESSION['random_key'];
$max_upload = (int)(ini_get('upload_max_filesize'));
// max file size in bytes
$sizeLimit = ($max_upload - 2);

$max_file = $sizeLimit; 							
$max_width = "500";							
$thumb_width = "100";						
$thumb_height = "100";						
// Only one of these image types should be allowed for upload
$allowed_image_types = array('image/pjpeg' => "pjpeg", 'image/jpeg' => "jpeg", 'image/jpg' => "jpg", 'image/png' => "png", 'image/x-png' => "png", 'image/gif' => "gif", 'image/tif' => "tif");
$allowed_image_ext = array_unique($allowed_image_types); // do not change this
$image_ext = "";	// initialise variable, do not change this.

foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}

//Image Locations
$large_image_location = $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_path.$large_image_name.$_SESSION['user_file_ext'];
$thumb_image_location = $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_path.$thumb_image_name.$_SESSION['user_file_ext'];

//Create the upload directory with the right permissions if it doesn't exist
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_dir)){
	mkdir($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_dir, 0777);
	chmod($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_dir, 0777);
}

//Check to see if any images with the same name already exist
if (file_exists($large_image_location)){
	if(file_exists($thumb_image_location)){
		$thumb_photo_exists = "<img src=\"".'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$thumb_image_name.$_SESSION['user_file_ext']."\" alt=\"Thumbnail Image\"/>";
	}else{
		$thumb_photo_exists = "";
	}
   	$large_photo_exists = "<img src=\"".'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$upload_path.$large_image_name.$_SESSION['user_file_ext']."\" alt=\"Large Image\"/>";
} else {
   	$large_photo_exists = "";
	$thumb_photo_exists = "";
}

if (isset($_POST["upload"])) { 
	//Get the file information
	$userfile_name = $_FILES['image']['name'];
	$userfile_tmp = $_FILES['image']['tmp_name'];
	$userfile_size = $_FILES['image']['size'];
	$userfile_type = $_FILES['image']['type'];
	$filename = basename($_FILES['image']['name']);
	$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	//Only process if the file is a JPG, PNG or GIF and below the allowed limit
	if ((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {
		
		foreach ($allowed_image_types as $mime_type => $ext) {
			//loop through the specified image types and if they match the extension then break out
			//everything is ok so go and check file size
			//if ($file_ext==$ext && $userfile_type==$mime_type){			
			if ($userfile_type == $mime_type){
				$_SESSION['error_in_profileimg_maker'] = "";
				break;
			}else{
				$_SESSION['error_in_profileimg_maker'] = "Only the following image file types can be uploaded<strong>".$image_ext."</strong><br />";
				echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
				//exit();
				//break;
			}
		}
		//check if the file size is above the allowed limit
		if ($userfile_size > 1048576) {
			$_SESSION['error_in_profileimg_maker'] .= "Profile picture has to be less than 1MB.";
			echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
			exit();
		}
		/*
		//check if the file size is above the allowed limit
		if ($userfile_size < 204800) {
			$_SESSION['error_in_profileimg_maker'] .= "Images must be at least " . (204800 /  1024) . "KB in size";
			echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
			exit();
		}
		*/
		
	}else{
		$_SESSION['error_in_profileimg_maker'] = "Select an image for upload";
		echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
		exit();
	}
	//Everything is ok, so we can upload the image.
	if (strlen($_SESSION['error_in_profileimg_maker']) == 0){
		
		if (isset($_FILES['image']['name'])){
			// grab the user id from the session
			if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
				if (isset($_SESSION['currentUser'])){
					$userId = $_SESSION['currentUser']['userId'];
				}
				if (isset($_SESSION['fbUser'])){
					$userId = $_SESSION['fbUser']['userId'];
				}			
			}
			// Update the database with profile image
			include '../_define.inc';
			include 'database.php';
			$DbObject = database::getInstance();	
			$imageNames = $DbObject->get("rehand__users", array('profile_image_name', 'profile_thumb_image_name'));			
			// Delete the previous image and clean the profile image folder
			$previous_l_image_location = $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_path.$imageNames['profile_image_name'];
			$previous_t_image_location = $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$upload_path.$imageNames['profile_thumb_image_name'];
			if ((file_exists($previous_l_image_location)) && (file_exists($previous_t_image_location))) {
				unlink($previous_l_image_location);
				unlink($previous_t_image_location);
			}
			//this file could now has an unknown file extension (we hope it's one of the ones set above!)
			$large_image_location = $large_image_location.".".$file_ext;
			$thumb_image_location = $thumb_image_location.".".$file_ext;
			//put the file ext in the session so we know what file to look for once its uploaded
			$_SESSION['user_file_ext']=".".$file_ext;
			//die($userfile_tmp . " => " . $large_image_location);
			move_uploaded_file($userfile_tmp, $large_image_location);
			chmod($large_image_location, 0777);
			
			$width = CommonFunctions::getWidth($large_image_location);
			$height = CommonFunctions::getHeight($large_image_location);
			//Scale the image if it is greater than the width set above
			if ($width > $max_width){
				$scale = $max_width/$width;
				$uploaded = CommonFunctions::resizeImage($large_image_location,$width,$height,$scale);
			}else{
				$scale = 1;
				$uploaded = CommonFunctions::resizeImage($large_image_location,$width,$height,$scale);
			}
			$DbObject->where('userId', $userId);
			$DbObject->update('rehand__users', array('profile_image_name' => end(explode("/", $large_image_location)), 'profile_thumb_image_name' => end(explode("/", $thumb_image_location))));
		}
		//Refresh the page to show the new uploaded image
		echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
		exit();
	}else{
		echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
		exit();
	}
}

if (isset($_POST["upload_thumbnail"]) && strlen($large_photo_exists) > 0) {
	//Get the new coordinates to crop the image.
	$x1 = $_POST["x1"];
	$y1 = $_POST["y1"];
	$x2 = $_POST["x2"];
	$y2 = $_POST["y2"];
	$w = $_POST["w"];
	$h = $_POST["h"];
	//Scale the image to the thumb_width set above
	$scale = $thumb_width/$w;
	$cropped = CommonFunctions::resizeThumbnailImage($thumb_image_location, $large_image_location,$w,$h,$x1,$y1,$scale);
	//Reload the page again to view the thumbnail
	echo "<script type='text/javascript' language='javascript'>location.href='http://".$_SERVER['HTTP_HOST']."/www.rehand.com/users/profile/?view=profpic';</script>";
	exit();
}
?>