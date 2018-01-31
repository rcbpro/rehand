<?php

class UsersModel extends database {

	public $DbObject = NULL;
	public $commFunctions = NULL;
	
	public function __construct() { 
		global $CommFuncs;
		$this->commFunctions = $CommFuncs;
		$this->DbObject = database::getInstance(); 
	}

	public function Login($userParams) { return $this->commonLoginProcedure($userParams['email']); }
	
	public function confirmFbLogin($fbuserParams) { return $this->commonLoginProcedure($fbuserParams->email); }
	
	public function commonLoginProcedure($fieldValue, $wherParam = 'email') {

		$sql = "SELECT userId, email, contactEmail, fbUserID, firstName, lastName, mobile_no,password, address, postCode, companyName, country, gender, dateOfBirth, firstLogin FROM rehand__users WHERE " . $wherParam . " = '" . $fieldValue . "'";
		return $this->DbObject->query($sql);						
	}	
	
	public function Register($newUserParams) {

		$query = "INSERT INTO rehand__users(`firstName`, `lastName`, `country`, `email`, `password`, `createdAt`) 
				  VALUES('" . $newUserParams["firstName"] . "', '" . $newUserParams["lastName"] . "' ,'" . $newUserParams["country"] . "' ,'" . $newUserParams["email"] . "', '".$newUserParams["password"]."', '".$newUserParams["createdAt"]."')";
		$this->DbObject->resetWhere();
		return $this->DbObject->query($query);	
	}
	
	public function LogLastLogin($user_id, $lastLoginParams) {

		$this->DbObject->resetWhere();
		$this->DbObject->where("email", $user_id);			
		$this->DbObject->update("rehand__users", $lastLoginParams);
	}	

	public function updateFBDetails($user_id, $fbParams) {
		
		$sql = "UPDATE rehand__users SET email = '". $user_id . "', contactEmail = '" . $user_id . "', fbUserID = " . $fbParams['fbUserID'] . ", gender = '" . $fbParams['gender'] . "' WHERE email = '" . $user_id . "' OR contactEmail = '" . $user_id . "'";
		$this->DbObject->query($sql);		
	}

	public function checkUserIdAvalable($userid) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("email", $userid);		
		return $this->DbObject->get("rehand__users", array('email'));					
	}
	
	public function getUserDetails($user_id) { return $this->commonLoginProcedure($user_id); }
	
	public function checkUserHasSellerPerspective($userid) {
		
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userid);		
		return $this->DbObject->get("tagging__pictures", array('count(*) as count'));					
	}
	
	public function checkUserHasBuyerPerspective($userid) {
		
		$this->DbObject->resetWhere();
		$this->DbObject->where("buyerUserId", $userid);		
		return $this->DbObject->get("tagging__purchases", array('count(*) as count'));					
	}
	
	public function getUserProfileInformations($userId) {

		// Get the profile other details
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$profileOtherDetails = $this->DbObject->get("rehand__users", array('userId', 'firstName', 'lastName', 'dateOfBirth', 'postCode', 'postCodeAsStr', 'country', 'companyName', 'gender', 'email', 'contactEmail', 'address', 'mobile_no', 'home_no', 'paypalId'));	
		// Get the profile image name
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$profileImageName = $this->DbObject->get("rehand__users", array('profile_image_name', 'profile_thumb_image_name'));	
		if ($profileImageName[0]['profile_image_name'] != ""){
			$profileImageName = "uploaded/profiles/".$profileImageName[0]['profile_image_name'];
		}else{
			$profileImageName = "public/images/defaultlarge.gif";
		}
		// Get the count of uploaed images
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$uploadedImagesCount = $this->DbObject->get("tagging__pictures", array('count(*) as count'));					
		// Get the number of images rest to tag
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$this->DbObject->where("tagged", 0);		
		$uploadedRESTImagesCount = $this->DbObject->get("tagging__pictures", array('count(*) as count'));					
		// Get the number of purchases done by this user
		$this->DbObject->resetWhere();
		$this->DbObject->where("buyerUserId", $userId);		
		$numbersOfPurchases = $this->DbObject->get("tagging__purchases", array('count(*) as count'));					
		// Get the number of bargains done by this user
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$numberOfBargains = $this->DbObject->get("tagging__ono_prices", array('count(*) as count'));
		return array(
					'numberOfUploadedImages' => $uploadedImagesCount[0]['count'], 'numberOfImagesRestToTag' => $uploadedRESTImagesCount[0]['count'], 
					'numberOfPurchases' => $numbersOfPurchases[0]['count'], 'numberOfBargains' => $numberOfBargains[0]['count'],
					'profileImage' => $profileImageName, 'profileOtherDetails' => $profileOtherDetails[0]
					 );
	}
	
	public function getUserBuyerPurchaseDetails($userId) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$uploadedImagesCount = $this->DbObject->get("tagging__purchases", array('count(*) as count'));					
	}
	
	public function getProfilePicture($userId) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		return $this->DbObject->get("rehand__users", array('profile_image_name', 'profile_thumb_image_name'));					
	}
	
	public function deleteProfileImage($userId) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$this->DbObject->update('rehand__users',array("profile_image_name" => "", "profile_thumb_image_name" => ""));
	}
	
	public function updateProfileOtherInformation($userId, $userProfileUpdation) {

		if ($userProfileUpdation['postCode'] != ""){
			// check the post code value not numeric
			if (!is_numeric($userProfileUpdation['postCode'])){
				// check the user entered value actuallayh exist in the db
				if ($this->checkWhetherThisPostCodeStringExists($userProfileUpdation['postCode'])){
					// If the user entered value is exists then update the profile saving session like this
					$postCodeUpdateAsStr = true;
				}
			}else{
				$postCodeUpdateAsStr = false;
			}
		}
		if (!$postCodeUpdateAsStr){
			$sql = "UPDATE rehand__users SET 
								   firstName = '" . $userProfileUpdation['firstName'] . "' ,lastName = '" . $userProfileUpdation['lastName'] . "' ,dateOfBirth = '" . $userProfileUpdation['dateOfBirth'] 
								   . "', postCode = '" . $userProfileUpdation['postCode'] . "', country = '" . $userProfileUpdation['country'] . "', companyName = '" . $userProfileUpdation['companyName']
								   . "', gender = '" . $userProfileUpdation['gender'] . "', email = '" . $userProfileUpdation['email'] . "', contactEmail = '" . $userProfileUpdation['contactEmail'] . "', address = '" . $userProfileUpdation['address']
								   . "', mobile_no = '" . $userProfileUpdation['mobile_no'] . "', home_no = '" . $userProfileUpdation['home_no'] 
								   . "', paypalId = '" . $userProfileUpdation['paypalId'] . "', firstLogin = '" . $userProfileUpdation['firstLogin'] . "' WHERE userId = " . $userId;
		}else{
			$sql = "UPDATE rehand__users SET 
								   firstName = '" . $userProfileUpdation['firstName'] . "' ,lastName = '" . $userProfileUpdation['lastName'] . "' ,dateOfBirth = '" . $userProfileUpdation['dateOfBirth'] 
								   . "', postCodeAsStr = '" . $userProfileUpdation['postCode'] . "', country = '" . $userProfileUpdation['country'] . "', companyName = '" . $userProfileUpdation['companyName']
								   . "', gender = '" . $userProfileUpdation['gender'] . "', email = '" . $userProfileUpdation['email'] . "', contactEmail = '" . $userProfileUpdation['contactEmail'] . "', address = '" . $userProfileUpdation['address']
								   . "', mobile_no = '" . $userProfileUpdation['mobile_no'] . "', home_no = '" . $userProfileUpdation['home_no'] 
								   . "', paypalId = '" . $userProfileUpdation['paypalId'] . "', firstLogin = '" . $userProfileUpdation['firstLogin'] . "' WHERE userId = " . $userId;
		}
		$this->DbObject->query($sql);
	}
	
	public function checkWhetherThisPostCodeStringExists($postCodeAsStr){
	
		$postCode = $this->DbObject->query("SELECT Pcode FROM rehand__postalCodes WHERE Locality LIKE '%" . strtoupper($postCode) . "%'");		
		return ($postCode != "") ? true : false;
	}
	
	public function loadProfileImage($userId) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		$profileImage = $this->DbObject->get("rehand__users", array('profile_thumb_image_name'));
		if ($profileImage[0]['profile_thumb_image_name'] != ""){
			$profileImage = "uploaded/profiles/".$profileImage[0]['profile_thumb_image_name'];
		}else{
			$profileImage = "public/images/defaultlarge.gif";
		}
		return $profileImage;
	}
	
	public function IndexUploader() {

		$this->DbObject->resetWhere();
		$this->DbObject->where('tagged', 0);	
		return ($this->DbObject->get("tagging__pictures", array('count(*) as count')) > 0) ? true : false;	
	}
	
	public function loadImagesForTheUploadPanel($userId, $status) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('userId', $userId);
		$this->DbObject->where('deletedFlag', 0);
		$this->DbObject->where('tagged', $status);	
		return $this->DbObject->get("tagging__pictures", array('pictureId', 'title', 'userId', 'uploadLImgLocation', 'uploadTImgLocation'));	
	}
	
	public function checkEmailExistsWithTheSystem($email) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('email', $email);
		$userEmailDetail = $this->DbObject->get("rehand__users", array('email'));
		return ($userEmailDetail[0]['email'] != "") ? $userEmailDetail[0]['email'] : NULL;
	}
	
	public function activatNewlyAddedUser($email) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('email', $email);
		$this->DbObject->update("rehand__users", array('activte_status' => 1));
	}
	
	public function getLoginDetailsForloginAfterActivation($email) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('email', $email);
		return $this->DbObject->get("rehand__users", array('userId', 'email', 'fbUserID', 'firstName', 'lastName', 'password', 'postCode', 'companyName', 'country', 'gender', 'dateOfBirth', 'firstLogin'));						
	}
	
	public function loadAllNotificationsCount($userId) {
	
		$countOnAllNotificationsTemp = $this->DbObject->query("SELECT COUNT(*) AS countOfAllNotifications FROM tagging__notification WHERE toUserId = " . $userId);
		return $countOnAllNotificationsTemp[0]['countOfAllNotifications'];
	}
	
	public function loadAllNotifications($userId) {
	
		$limit = "";
		$display_items = NO_OF_RECORDS_PER_PAGE;	
		$curr_page_no = ((isset($_GET['page'])) && ($_GET['page'] != "") && ($_GET['page'] != 0)) ? $_GET['page'] : 1; 								
		if ($curr_page_no != NULL){
			if ($curr_page_no == 1){
				$start_no_sql = 0;
				$end_no_sql = $display_items;
			}else{							
				$start_no_sql = ($curr_page_no - 1) * $display_items;
				$end_no_sql = $display_items;				
			}
		}else{
			 $start_no_sql = 0;
			 $end_no_sql = $display_items;		
		}
		$limit = " Limit {$start_no_sql}, {$end_no_sql}";				
		return $this->DbObject->query("SELECT * FROM tagging__notification WHERE toUserId = " . $userId . " ORDER BY notificationDate DESC " . $limit);
	}
	
	public function RegisterForFbUser($newUserParams) {

		$countrySplitted = explode(",", $newUserParams->hometown->name);
		$country = end($countrySplitted);
		$birthday = $newUserParams->birthday;
		$birthday = explode("/", $birthday);
		$birthday = $birthday[2] . "-" . $birthday[0] . "-" . $birthday[1];
		switch($newUserParams->gender){
			case "male": $gender = "M"; break;
			case "female": $gender = "F"; break;				
		}
		$query = "INSERT INTO rehand__users(`fbUserId`, `firstName`, `lastName`, `dateOfBirth`, `country`, `companyName`, `gender`, `email`, `password`, `createdAt`, `contactEmail`) 
				  VALUES(" . $newUserParams->id .", '" . $newUserParams->first_name . "', '" . $newUserParams->last_name . "' ,'" . $birthday . "' ,'" . $country . "' , '', '" . $gender . "', '" . $newUserParams->email . "', '', '".date('Y-m-d')."', '".$newUserParams->email."')";
		return $this->DbObject->query($query);
	}

	public function getMaximumPicNoNextToUpload() {
	
		$pictureIdDetails = $this->DbObject->query("SELECT MAX(pictureId) AS newPicId FROM tagging__pictures");
		return $pictureIdDetails[0]['newPicId'];
	}
	
	public function uploadImagesOnlyForIeUsers($pictureId, $title, $userId, $largeImage, $smallImage) {

		$query = "INSERT INTO tagging__pictures(`pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation`, `uploadedDate`, `tagged`, `deletedFlag`) 
				  VALUES(" . $pictureId .", '" . $title . "', " . $userId . ", '" . $largeImage . "' ,'" . $smallImage . "', '" . date('Y-m-d') . "', 0, 0)";
		return $this->DbObject->query($query);	
	}
	
	public function updateContactEmailForFBUsers($fbUserId, $contactEmail){
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('fbUserId', $fbUserId);
		return $this->DbObject->update("rehand__users", array('contactEmail' => $contactEmail));						
	}

	public function createNewGroup($groupParams, $userId){

		if (!empty($groupParams)){
			// Check this group already exists in the db
			$sql = "SELECT `Group_name` FROM rehand__groups WHERE `Group_name` = '".addslashes(trim($_POST['groupName']))."'";
			$this->DbObject->query($sql);
			if ($this->DbObject->num_rows() <= 0){
				$sql = "INSERT INTO rehand__groups (`Group_name`, `CreatedAt`, `CreatedBy`, `Group_desc`, `Active`, `number_of_members`) VALUES('".addslashes(trim($groupParams['GroupFileName']))."', '".date("Y-m-d H:i:s")."', ".$userId.", '".$groupParams['GroupFileDesc']."', 1, 0)";
				$this->DbObject->query($sql);
				$groupId = $this->DbObject->getLastInisertedId();
				$this->DbObject->query("UPDATE rehand__groups SET number_of_members = " . ($number_of_members + 1) . " WHERE GroupId = " . $groupId);
				$sql = "INSERT INTO rehand__group_members (`GroupId`, `memberId`, `JoinedDate`, `Membership_active`) VALUES(".$groupId.", ".$userId.", '".date("Y-m-d H:i:s")."', 1)";
				$this->DbObject->query($sql);
				return 'No Errors';				
			}else{
				return 'This group already exist in the system !';
			}	
		}
	}
	
	public function getGroupNamesAllPurposes($userId) {
	
		$groupInfo = array();
		// Get the group names for letter A
		$groupInfo['groupNamesForLetterA'] = $this->DbObject->query("SELECT `GroupId`,`Group_name`, `Group_desc` FROM `rehand__groups` WHERE `Group_name` like 'A%' AND `Active` = 1");
		// If groups are empty for the letter A then load other group names which belongs to other letters
		if (empty($groupInfo['groupNamesForLetterA'])){
			foreach(range('B', 'Z') as $letter){		
				$results = $this->DbObject->query("SELECT `GroupId`,`Group_name`, `Group_desc` FROM `rehand__groups` WHERE `Group_name` like '{$letter}%' AND `Active` = 1");
				$whichLetter = $letter;
				if (!empty($results)) break;
			}	
			$groupInfo['groupNamesForOtherLetters'] = array('results' => $results, 'whichLetter' => $whichLetter);
		}
		// Get all group names
		$this->DbObject->resetWhere();
		$this->DbObject->where('Active', 1);
		$groupInfo['allGroupNames'] = $this->DbObject->get('rehand__groups', array('GroupId' ,'Group_name', 'Group_desc'));
		foreach($groupInfo['allGroupNames'] as $eachGroup){
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['GroupId'] = $eachGroup['GroupId'];
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['Group_name'] = $eachGroup['Group_name'];
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['Group_desc'] = $eachGroup['Group_desc'];
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['profilePic'] = $this->getTheProfilePicture($eachGroup['GroupId']);
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['noOfMembers'] = $this->getTheMemberCountForThisGroup($eachGroup['GroupId']);
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['noOfItems'] = $this->getTheNumberOfPicturesSubscribedForThisGroup($eachGroup['GroupId']);			
			$groupInfo['allGroupNamesModified'][$eachGroup['GroupId']]['joinedStatus'] = $this->checkLoggedUserAlreadyJoinedForTheGivenGroupIdIdForTestingPurpose($userId, $eachGroup['GroupId']);						
		}
		$groupInfo['allGroupNames'] = $groupInfo['allGroupNamesModified'];
		// Get the group names that belongs to you 
		$this->DbObject->resetWhere();
		$this->DbObject->where('Membership_active', 1);
		$this->DbObject->where('memberId', $userId);
		$groupIdsBelongsToYou = $this->DbObject->get('rehand__group_members', array('GroupId'));
		foreach($groupIdsBelongsToYou as $each){
			$Modified[] = $each['GroupId'];
		}
		$i = 0;
		foreach($Modified as $eachGrpId){
			$this->DbObject->resetWhere();
			$this->DbObject->where('Active', 1);
			$this->DbObject->where('GroupId', $eachGrpId);
			$groupNamesForYou = $this->DbObject->get('rehand__groups', array('GroupId' ,'Group_name', 'Group_desc'));
			$groupNamesForYouModifyed[$i] = $groupNamesForYou[0];
			$i++;
		}
		$groupInfo['groupNamesForYou'] = $groupNamesForYouModifyed;
		return $groupInfo;
	}
	
	public function checkLoggedUserAlreadyJoinedForTheGivenGroupIdIdForTestingPurpose($userId, $groupId){
	
		// Grab this user joined groups and check whther he is in there
		$membersJoinedForThisGroup = $this->DbObject->query("SELECT memberId FROM `rehand__group_members` WHERE `GroupId` = " . $groupId);
		return (in_array($userId, $membersJoinedForThisGroup[0])) ? true : false;
	}
	
	public function getTheNumberOfPicturesSubscribedForThisGroup($groupId){

		$result = $this->DbObject->query("SELECT COUNT(`PictureId`) AS numberOfPicsSubscribed FROM `rehand__group_pictures` WHERE `GroupId` = ". $groupId);
		return $result[0]['numberOfPicsSubscribed'];
	}
	
	public function getTheMemberCountForThisGroup($groupId){

		$result = $this->DbObject->query("SELECT `number_of_members` FROM `rehand__groups` WHERE `GroupId` = ". $groupId);
		return $result[0]['number_of_members'];
	}
	
	public function checkUserAlreadyInThisGroup($userId, $groupId) {
		
		// Grab this user joined groups and check whther he is in there
		$this->DbObject->resetWhere();
		$this->DbObject->where('Membership_active', 1);
		$this->DbObject->where('memberId', $userId);		
		return $this->DbObject->get('rehand__group_members', array('GroupId'));
	}
	
	// This is function is to get the group profile picture
	public function getTheProfilePicture($groupId){
	
		// Get the pictureIds for each group id
		$picIdsWhichHaveSubscribed = $this->DbObject->query("SELECT PictureId FROM rehand__group_pictures WHERE GroupId = " . $groupId);
		foreach($picIdsWhichHaveSubscribed as $each){
			$picIdsWhichHaveSubscribedMod[] = $each['PictureId'];
		}
		foreach($picIdsWhichHaveSubscribedMod as $eachPicId){
			$resultTemp = $this->DbObject->query("SELECT SUM(noOfInterests) AS totCountOfNoOfInt FROM tagging__taggeditems WHERE PictureId = " . $eachPicId);
			$finalResult[$eachPicId] = $resultTemp[0];
		}
		if ($finalResult){
			// Now get the maximum count from these image id array collection
			$PicIdForTheGroupProfilePic = array_keys($finalResult, max($finalResult));
			$theGroupProfilePicTemp = $this->DbObject->query("SELECT uploadLImgLocation FROM tagging__pictures WHERE pictureId = " . $PicIdForTheGroupProfilePic[0]);
			// Now have to upload the medium size image for this result and then insert the medium size imgae details to the database
			$explodedImageName = explode("/", $theGroupProfilePicTemp[0]['uploadLImgLocation']);
			$mediumSizeImageUploadedPathTemp = $explodedImageName[0].DS.$explodedImageName[1].DS.$explodedImageName[2].DS.'medium'.DS.$explodedImageName[3];
			$mediumSizeImageUploadedPath =  $_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$mediumSizeImageUploadedPathTemp;
			$theGroupProfileMPicTemp = $this->DbObject->query("SELECT uploadMImgLocation FROM tagging__pictures WHERE pictureId = " . $PicIdForTheGroupProfilePic[0]);
			if ((!file_exists($mediumSizeImageUploadedPath)) || ($theGroupProfileMPicTemp[0]['uploadMImgLocation'] == "")){
				mkdir($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$explodedImageName[0].DS.$explodedImageName[1].DS.$explodedImageName[2].DS.'medium', 0777);
				copy($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$theGroupProfilePicTemp[0]['uploadLImgLocation'], $mediumSizeImageUploadedPath);
				$max_width = "500";
				$width = $this->commFunctions->getWidth($mediumSizeImageUploadedPath);
				$height = $this->commFunctions->getHeight($mediumSizeImageUploadedPath);
				//Scale the image if it is greater than the width set above
				if ($width > $max_width){
					$scale = $max_width/$width;
					$uploaded = $this->commFunctions->resizeImage($mediumSizeImageUploadedPath, $width, $height, $scale);
				}else{
					$scale = 1;
					$uploaded = $this->commFunctions->resizeImage($mediumSizeImageUploadedPath, $width, $height, $scale);
				}
				$this->DbObject->query("UPDATE tagging__pictures SET uploadMImgLocation = '" . $mediumSizeImageUploadedPathTemp . "' WHERE pictureId = " . $PicIdForTheGroupProfilePic[0]);
			}
			return $mediumSizeImageUploadedPathTemp;
		}
	}
	
	public function getAllPicturesWhichsSubscribedToThisGroup($groupId){
	
		$sql = "SELECT tagging__pictures.title, tagging__pictures.uploadTImgLocation, tagging__pictures.uploadLImgLocation FROM tagging__pictures JOIN rehand__group_pictures ON tagging__pictures.pictureId = rehand__group_pictures.pictureId WHERE rehand__group_pictures.GroupId = " . $groupId;
		$resultTemp = $this->DbObject->query($sql);
		return $resultTemp[0];
	}
	
	public function loadMyOwnGroups($userId){
	
		$sql = "SELECT rehand__groups.Group_name, rehand__groups.Group_desc, rehand__groups.number_of_members, rehand__groups.GroupId 
				FROM rehand__groups 
				JOIN rehand__group_members ON rehand__group_members.GroupId = rehand__groups.GroupId
				WHERE rehand__group_members.memberId = " . $userId;
		$myGroupsTemp = $this->DbObject->query($sql);
		foreach($myGroupsTemp as $eachGroup){
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['GroupId'] = $eachGroup['GroupId'];
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['Group_name'] = $eachGroup['Group_name'];
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['Group_desc'] = $eachGroup['Group_desc'];
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['profilePic'] = $this->getTheProfilePicture($eachGroup['GroupId']);
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['noOfMembers'] = $this->getTheMemberCountForThisGroup($eachGroup['GroupId']);
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['noOfItems'] = $this->getTheNumberOfPicturesSubscribedForThisGroup($eachGroup['GroupId']);			
			$myGroups['allGroupNamesModified'][$eachGroup['GroupId']]['joinedStatus'] = $this->checkLoggedUserAlreadyJoinedForTheGivenGroupIdIdForTestingPurpose($userId, $eachGroup['GroupId']);						
		}
		return $myGroups;
	}
	
	public function checkMyPasswordCorrectness($userId, $password){

		$tempPass = $this->DbObject->query("SELECT password FROM rehand__users WHERE password = '" . md5($password) . "' AND userId = " . $userId);
		return ($tempPass[0]['password'] == "") ? true : false;
	}
	
	public function deactivateMyAccount($userId){
	
		$this->DbObject->query("UPDATE rehand__users SET deactivateStauts = 1 WHERE userId = " . $userId);
	}
	
	public function checkUserPreviouslyDeactivatedOrNot($userId){
	
		return $this->DbObject->query("SELECT deactivateStauts FROM rehand__users WHERE userId = " . $userId);
	}
	
	public function rollBackDeactivateStatus($userId){
	
		$this->DbObject->query("UPDATE rehand__users SET deactivateStauts = 0 WHERE userId = " . $userId);		
	}
	/*
	public function getThePostcodeForTheName($postCode, $codeNotCodeStr){
		
		if ($codeNotCodeStr){
			$postCode = $this->DbObject->query("SELECT Pcode FROM rehand__postalCodes WHERE Pcode = " . $postCode);		
		}else{
			$postCode = $this->DbObject->query("SELECT Pcode FROM rehand__postalCodes WHERE Locality LIKE '%" . strtoupper($postCode) . "%'");					
		}
		return $postCode[0]['Pcode'];
	}
	*/
}