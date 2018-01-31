<?php

class IndexModel extends database {

	public $DbObject = NULL;
	public $userId = "";
	
	public function __construct() { 
	
		if (isset($_SESSION['currentUser'])){ 
			$this->userId = $_SESSION['currentUser']['userId'];
		}else if (isset($_SESSION['fbUser'])){ 
			$this->userId = $_SESSION['fbUser']['userId'];
		}
		$this->DbObject = database::getInstance(); 
	}

	public function Index() {

  		if ((!empty($_SESSION['currentUser'])) || (!empty($_SESSION['fbUser']))){
   			$this->DbObject->where('userId', $this->userId); 
   			$profileImage = $this->DbObject->get("rehand__users", array('profile_thumb_image_name')); 
			if ($profileImage[0]['profile_thumb_image_name'] != ""){
    			$profileImage = "uploaded/profiles/".$profileImage[0]['profile_thumb_image_name'];
   			}else{
    			$profileImage = "public/images/defaulttiny.gif";
   			}
   			return array('profileImage' => $profileImage);
  		}
 	}
	
	public function loadProfileImage() {
	
		$this->DbObject->where("userId", $this->userId);		
		$profileImage = $this->DbObject->get("rehand__users", array('profile_thumb_image_name'));
		if ($profileImage[0]['profile_thumb_image_name'] != ""){
			$profileImage = "uploaded/profiles/".$profileImage[0]['profile_thumb_image_name'];
		}else{
			$profileImage = "public/images/defaultlarge.gif";
		}
		return array('profileImage' => $profileImage);
	}
	
	public function LoadUploadedImages() {
	
		$this->DbObject->where('tagged', 0);	
		return $this->DbObject->get("tagging__pictures", array('uploadLImgLocation', 'uploadTImgLocation'));	
	}
}