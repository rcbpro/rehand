<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'off');			

include '../_define.inc';
include 'database.php';
include 'encdec.php';

if ((isset($_GET['action'])) && ($_GET['action'] != "")){

	$ajaxFuncs = new ajaxFunctions;

	switch($_GET['action']){
		case "reportMisUse" : return $ajaxFuncs->reportMisUse(); break;
		case "checkUname" : return $ajaxFuncs->checkUserNameExist($_POST['username']); break;
		case "checkUnameWithUid" : return $ajaxFuncs->checkUserNameExistWithOthersUserId($_POST['username'], $_POST['uid']); break;
		case "loadImages" : $ajaxFuncs->return_image_set(); break;
		case "loadImagesAfterFiltering" : $ajaxFuncs->return_image_set_after_searching($_GET['searchQ']); break;
		case "chkCurPass" : $ajaxFuncs->checkCurrentPassword(); break;
		case "newpass" : $ajaxFuncs->saveNewPassword(); break;
		case "checkLogin" : $ajaxFuncs->checkLoginAndReturnError(); break;
		case "chkCurEmail" : $ajaxFuncs->checkCurrentEmailIsAvailable(); break;
		case "newpassforpassreset" : $ajaxFuncs->saveNewPasswordForForgotton(); break;
		case "buyNotify" : $ajaxFuncs->sendBuyNotificationToSeller(); break;
		case "loadAllNots" : $ajaxFuncs->loadAllNotifications(); break;
		case "clearingTheNotificationsCount" : $ajaxFuncs->clearingTheNotificationsCount(); break;
		case "sellerInfo" : $ajaxFuncs->getSellerInfoAndTheFeedbackForm(); break;
		case "sellerInfoSendWhenLogged" : $ajaxFuncs->getSellerInfoAndSendToBuyerWhenLogged(); break; 
		case "sendMailNotForSellerFromBuyer" : $ajaxFuncs->sendMailNotificationForSellerFromBuyer(); break;
		case "sendNotForSellerFromBuyerAsAReply" : $ajaxFuncs->sendNotificationForSellerFromBuyerAsAReply(); break;
		case "getTheSuburbInputForm" : $ajaxFuncs->returnSuburbInputForm(); break;
		case "submitFeedbackformdata" : $ajaxFuncs->submitFeedbackFormData(); break;
		case "getGroupNameByLetter" : $ajaxFuncs->searchForAGroupNameByItsLetter(); break;
		case "autoComTextsForGroups" : $ajaxFuncs->grabGroupNamesForAutoSuggestions(); break;
		case "searchForGroup" : $ajaxFuncs->searchForASpecificGroup(); break;
		case "initShowtheAllGroups" : $ajaxFuncs->checkWhichLetterHasTheGroupsAlready(); break;
		case "joinToThisGroup" : $ajaxFuncs->joinToThisGroup(); break;
		case "leaveThisGroup" : $ajaxFuncs->leaveThisGroup(); break;
		case "loadAllOwnedGroupsForSubscrition" : $ajaxFuncs->loadAllOwnedGroupsForSubscriptions(); break;
		case "checkForTheGroupNameByLetter" : $ajaxFuncs->GetTheGroupNameForTheLetter(); break;
		case "resetTheGroupNamesList" : $ajaxFuncs->resetTheGroupNamesListInHtml(); break;
		case "subscribeToThisPic" : $ajaxFuncs->subscribeToThisPicture(); break;
		case "checkThesePicsGrouped" : $ajaxFuncs->checkThesePicturesGroups(); break;
		case "newSubscriptionsForUploadedPic" : $ajaxFuncs->addNewSubscriptionsForThisPic(); break;
		case "removeCurSubscriptionsForUploadedPic" : $ajaxFuncs->removeCurrentSubscriptionsForThisPic(); break;	
		case "loadImgs" : $ajaxFuncs->loadImageAutomatically(); break;
		case "saveLocationData" : $ajaxFuncs->saveLocationData(); break;
		case "subscribeTheseGrpsToRehand" : $ajaxFuncs->subscribeTheseGrpsToRehand(); break;
		case "getTheOriginalNotifuyerName" : $ajaxFuncs->getTheOriginalNotifierName(); break;
		case "checkHasJoinedAnyGrps" : $ajaxFuncs->checkThisUserHasJoinedToAnyGroups(); break; 
		case "shareThisLinkViaEmail" : $ajaxFuncs->shareThisLinkViaEmailToAFreind(); break;
		case "loadTopThreeGroupSubscriptionWhichUserIsNotRegisteredYet" :$ajaxFuncs->loadTopThreeGroupSubscriptionWhichUserIsNotRegisteredYet(); break;
		case "getUserInfo" : $ajaxFuncs->getUserInfo(); break;
	}		
}

class ajaxFunctions{

	public static $instance;
	private $DbObject = NULL;
	private $userId;
	
	public function __construct() {

		$this->DbObject = database::getInstance();
		$_SESSION['imageLaod']['limit'] = 200;
		if (isset($_SESSION['currentUser'])) $this->userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $this->userId = $_SESSION['fbUser']['userId'];			
	}	
	
	static function getInstance() { if (self::$instance == NULL) return new self; }
	
	
	public function getUserInfo()
	{
		$userDetails = array();
		
		if( !isset( $_GET['uid'] ) )
			return;
		
		$userId = intval($_GET['uid']);
		
		$userDetails = $this->DbObject->query(" SELECT firstName, lastName, email, mobile_no, home_no
												FROM rehand__users
												WHERE userId =".$userId."
												LIMIT 1 ");
		
		echo json_encode($userDetails[0]);
		
		
	}
	
	function loadProfileImage($pictureOwner) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('userId', $pictureOwner);	
		$profileImageDetails = $this->DbObject->get("rehand__users", array('profile_thumb_image_name'));		
		if ($profileImageDetails[0]['profile_thumb_image_name'] != ""){
			$profileImage = "uploaded/profiles/".$profileImageDetails[0]['profile_thumb_image_name'];
		}else{
			$profileImage = "public/images/defaulttiny.gif";
		}
		return $profileImage;
	}
	
	public function loadProfileName($pictureOwner) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('userId', $pictureOwner);	
		$ownerNames = $this->DbObject->get("rehand__users", array('firstName', 'lastName'));		
		return $ownerNames[0]['firstName'] . " " . $ownerNames[0]['lastName'];
	}
	
	public function loadThePostCodeOfPictureOwner($pictureOwner) {
		
		$this->DbObject->resetWhere();
		$this->DbObject->where('userId', $pictureOwner);	
		$owner = $this->DbObject->get("rehand__users", array('postCode'));		
		if ($owner[0]['postCode'] != ""){
			return $owner[0]['postCode'];
		}else{
			$this->DbObject->resetWhere();
			$this->DbObject->where('userId', $pictureOwner);	
			$owner = $this->DbObject->get("rehand__users", array('postCodeAsStr'));		
			return $owner[0]['postCodeAsStr'];
		}
	}
	
	public function return_image_set() {

		$this->DbObject->resetWhere();
		$this->DbObject->where('tagged', 1);	
		$this->DbObject->where('deletedFlag', 0);	
		$results = $this->DbObject->get("tagging__pictures", array('pictureId', 'title', 'userId', 'uploadLImgLocation', 'uploadTImgLocation'), NULL, NULL, "pictureId DESC");	
		$i = 0;
		foreach($results as $temObj){
			$photos[$i]['pictureId'] = $temObj['pictureId'];
			$photos[$i]['title'] = $temObj['title'];
			$photos[$i]['owner'] = $temObj['userId'];
			$photos[$i]['pictureRelatedTags'] = $this->loadRelatedTags($temObj['pictureId']);
			$photos[$i]['ownerName'] = $this->loadProfileName($temObj['userId']);		
			$photos[$i]['postCode'] = $this->loadThePostCodeOfPictureOwner($temObj['userId']);		
			$photos[$i]['profileImage'] = $this->loadProfileImage($temObj['userId']);
			$photos[$i]['uploadLImgLocation'] = $temObj['uploadLImgLocation'];						
			$photos[$i]['uploadTImgLocation'] = $temObj['uploadTImgLocation'];
			$photos[$i]['wholeNoOfInterests'] = $this->loadNoOfInterestsForAPicture($temObj['pictureId']);
			$photos[$i]['idhash'] = md5($temObj['pictureId'].HASHKEY.$temObj['userId']);
			$_SESSION['recentLoads'][$photos[$i]['idhash']] = $temObj['pictureId'];
			$i++;
		}
		echo json_encode($photos);		
	} 
	
	function loadNoOfInterestsForAPicture($pictureId){
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('pictureId', $pictureId);	
		$result = $this->DbObject->get("tagging__taggeditems", array('noOfInterests'));	
		foreach($result as $eachRes){
			$noOfInterests[] += $eachRes['noOfInterests'];
		}
		foreach($noOfInterests as $eachNoOfInterests){
			$noOfInterestsFinal += $eachNoOfInterests;
		}
		return ($noOfInterestsFinal != null) ? $noOfInterestsFinal : 0;
	}
	
	function loadRelatedTags($pictureId){
	
		$relatedTags = array();
		$result = $this->DbObject->query("SELECT * FROM tagging__taggeditems WHERE pictureId = " . $pictureId);
		$i = 0;
		$enc = new Encdec();
		foreach($result as $eachTagDetails){
			switch($eachTagDetails["currentStatus"]){
				case "available" : $price = "$".$eachTagDetails["price"]; break;
				case "free" : $price = "free"; break;
				case "sold" : $price = "$".$eachTagDetails["price"]; break;				
			}	
			$relatedTags[$i]['tagId'] = $enc->encrypt($eachTagDetails['tagId']);
			$relatedTags[$i]['tagName'] = $eachTagDetails['tagName'];
			$relatedTags[$i]['price'] = $price;
			$relatedTags[$i]['desc'] = $eachTagDetails['desc'];			
			$i++;
		}
		return $relatedTags;
	}
	
	public function return_image_set_after_searching($searchQ){
	
		if ($searchQ != ""){
			// load the tags which related to this search query
			$searchedTags = $this->DbObject->query("SELECT `tagId`, `tagName`, `price`, `pictureId` FROM tagging__taggeditems WHERE MATCH(`tagName`, `desc`) AGAINST('".trim($searchQ)."')");
			foreach($searchedTags as $eachTagDetail){
				if (!in_array($eachTagDetail['pictureId'], $pictureIds))
					$pictureIds[] = $eachTagDetail['pictureId']; 
			}
			// Load the images for each picture ids
			foreach($pictureIds as $eachPictureId){
				$this->DbObject->resetWhere();
				$this->DbObject->where('pictureId', $eachPictureId);	
				$results = $this->DbObject->get("tagging__pictures", array('pictureId', 'title', 'userId', 'uploadLImgLocation', 'uploadTImgLocation'));
				$newResult[] = $results[0];
			}
			// Now load the tags related to each pictures with other picture details
			$i = 0;
			foreach($newResult as $temObj){
				$photos[$i]['pictureId'] = $temObj['pictureId'];
				$photos[$i]['title'] = $temObj['title'];
				$photos[$i]['owner'] = $temObj['userId'];
				$photos[$i]['pictureRelatedTags'] = $this->loadRelatedTags($temObj['pictureId']);
				$photos[$i]['ownerName'] = $this->loadProfileName($temObj['userId']);		
				$photos[$i]['postCode'] = $this->loadThePostCodeOfPictureOwner($temObj['userId']);		
				$photos[$i]['profileImage'] = $this->loadProfileImage($temObj['userId']);
				$photos[$i]['uploadLImgLocation'] = $temObj['uploadLImgLocation'];						
				$photos[$i]['uploadTImgLocation'] = $temObj['uploadTImgLocation'];
				$photos[$i]['idhash'] = md5($temObj['pictureId'].HASHKEY.$temObj['userId']);
				$i++;
			}
		}else{
			$photos = "";
		}
		echo json_encode($photos);		
	}
	
	public function checkUserNameExist($usernameParam) {

		$this->DbObject->resetWhere();
		$this->DbObject->where('email', $usernameParam);
		$result = $this->DbObject->get("rehand__users", array("email"));	
		echo ($result[0]['email'] == $usernameParam) ? '1' : '0';
	}

	public function checkUserNameExistWithOthersUserId($usernameParam, $userIdParam) {

		$results = $this->DbObject->query("SELECT email FROM rehand__users WHERE userId != ".$userIdParam);
		foreach($results as $eachRecordKey => $eachRecordDetails){
			$emails[] = $eachRecordDetails['email'];
		}
		if (in_array($usernameParam, $emails)) echo '1';
		else echo '0';
	}

	public function checkCurrentPassword(){
	
		if (isset($_POST['curpass'])){
			$this->DbObject->resetWhere();
			$this->DbObject->where('userId', $this->userId);
			$password_in_db = $this->DbObject->get("rehand__users", array('password'));	
			echo ($password_in_db[0]['password'] == $this->mysql_preperation(md5($_POST['curpass'])) ? 'ok' : 'not ok'); 
		}
	}
	
	public function saveNewPassword(){
	
		if (isset($_POST['newpassval'])){
			$this->DbObject->resetWhere();
			$this->DbObject->where('userId', $this->userId);
			echo $this->DbObject->update('rehand__users', array("password" => $this->mysql_preperation(md5($_POST['newpassval'])))) ? 'ok' : 'not ok';
		}
	}
	
	public function checkLoginAndReturnError(){
	
		if ((isset($_POST['username'])) && (isset($_POST['password']))){
			$this->DbObject->resetWhere();
			$this->DbObject->where('email', $_POST['username']);
			$this->DbObject->where('activte_status', 1);			
			$this->DbObject->where('password', md5($_POST['password']));
			$results = $this->DbObject->get("rehand__users", array('email', 'password'));
			echo (empty($results)) ? '0' : '1';
		}
	}
	
	public function checkCurrentEmailIsAvailable(){
		
		if (isset($_POST['curemail'])){
			$this->DbObject->resetWhere();
			$this->DbObject->where('email', $_POST['curemail']);
			$results = $this->DbObject->get("rehand__users", array('email', 'firstName', 'lastName'));
			if (empty($results[0]['email'])){
				echo '0';
			}else{
				$this->DbObject->resetWhere();
				$this->DbObject->where('email', $results[0]['email']);
				// Get the email address and attach md5 hash key to that and wrap it
				$hashedemail = md5("myemail").base64_encode($results[0]['email']);
				//mail to above user with his login details
				$to      = $results[0]['email'];
				$subject = "Reset your password";
				$message = "Hi, " . $results[0]['firstName'] . " " . $results[0]['lastName'] . "You are receiving this email because you or someone else using your email address has sent a password reset request to us. If you didn't send this email, please ignore it. If you requested a password reset, you can add a new password by clicking on the following link. Or else, you can copy the link and paste it in your favourite web browser.<br /><br />";
				$message .= "<a href='".WEB_PATH."users/resetpass/".$hashedemail."/'>" . WEB_PATH."users/resetpass/".$hashedemail .'/' . "</a>.<br /><br />";
				//$message .= WEB_PATH."users/resetpass/".$hashedemail."/";
				
				$message .= "Warm regards,<br /><br />";
				$message .= "Rehand Team";
				echo ($this->sendMailNotification($to, $subject, $message, "Rehand - Reset password") ? '1' : '0');
			}
		}
	}
	
	public function saveNewPasswordForForgotton(){

		if (isset($_POST['forgotpassword'])){
			$results = $this->DbObject->query("UPDATE rehand__users SET password = '" . md5(trim($_POST['forgotpassword'])) . "' WHERE email = '" . $_SESSION['forgotton_password_rel_email'] . "'");
			echo ($results == '1') ? '1' : '0';
		}
	}
	
	public function sendMailNotification($mailto, $mailsubject, $mailmessage, $from = "", $type = ""){
	
		//mail to above user with his login details
		$from = ($from != "") ? $from : (($type != "") ? $type : "");
		$to      = $mailto;
		$subject = $mailsubject;
		$message = $mailmessage;
		$headers = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
		$headers .= "From: $mailsubject : $from" . "\r\n" .
					"Reply-To: $from" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();
		return (mail($to, $subject, $message, $headers) ? true : false);
	}
	
	public function sendBuyNotificationToSeller(){
		
		if ((isset($_POST['tagId'])) && (isset($_POST['itemStatus']))){
			$encDec = new Encdec();
			$tagId = $encDec->decrypt(trim($_POST['tagId']));
			// Get the tag owner Id and name and the no of interests
			$this->DbObject->resetWhere();
			$this->DbObject->where('tagId', $tagId);
			$tagOwnerDetails = $this->DbObject->get("tagging__taggeditems", array('userId', 'tagName', 'noOfInterests'));
			$tagOwnerId = $tagOwnerDetails[0]['userId'];
			$tagName = $tagOwnerDetails[0]['tagName'];
			$noOfInterests = $tagOwnerDetails[0]['noOfInterests'];
			// Get the tag owner Name
			$this->DbObject->resetWhere();
			$this->DbObject->where('userId', $tagOwnerId);
			$tagOwnerDetails = $this->DbObject->get("rehand__users", array('firstName', 'lastName', 'email'));
			$nameOfSeller = $tagOwnerDetails[0]['firstName'] . " " . $tagOwnerDetails[0]['lastName'];
			$emailOfSeller = $tagOwnerDetails[0]['email'];
			// Get the current buyer id
			if (isset($_SESSION['currentUser'])) $this->userId = $_SESSION['currentUser']['userId'];
			else if (isset($_SESSION['fbUser'])) $this->userId = $_SESSION['fbUser']['userId'];			
			// Get the person who did the notification
			if (isset($_SESSION['currentUser'])) $nameOfBuyer = $_SESSION['currentUser']['firstName']." ".$_SESSION['currentUser']['lastName'];
			else if (isset($_SESSION['fbUser'])) $nameOfBuyer = $_SESSION['fbUser']['firstName']." ".$_SESSION['fbUser']['lastName'];			
			// According to your item status the notification text will be changed
			switch($_POST['itemStatus']){
				case "available":
					$notificationText = "<span class='BuyerN'>" . $nameOfBuyer . "</span> would like to buy <span class='TagN'>" . $tagName . "</span>";
				break;
				case "free":
					$notificationText = "<span class='BuyerN'>" . $nameOfBuyer . "</span> would like to grab <span class='TagN'>" . $tagName . "</span> for Free";
				break;
			}
			// Inserting a new notification
			$tagNotificationDetails = array('fromUserId' => $this->userId, 'toUserId' => $tagOwnerId, 'notificationLinkId' => 0, 'notificationType' => trim($_POST['itemStatus']), 'notificationText' => $notificationText, 'notificationDate' => date("Y-m-d"), 'notificationTime' => date("H:i:s"));
			if ($this->DbObject->insert("tagging__notification", $tagNotificationDetails)){
				// This is to send the mail
				if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
					if (isset($_SESSION['currentUser'])){ 
						$buyerEmail = $_SESSION['currentUser']['email'];
						$userId = $_SESSION['currentUser']['userId'];
					}else if (isset($_SESSION['fbUser'])){ 
						$buyerEmail = $_SESSION['fbUser']['email'];
						$userId = $_SESSION['fbUser']['userId'];					
					}
				}
				// Grab the seller email for related product
				$hashedImageId = trim($_POST['relAId']);
				$imageId = $_SESSION['recentLoads'][$hashedImageId];
				// Get the user email of the picture owner
				$to      = $emailOfSeller;
				$from    = $buyerEmail;
				$subject = "New message regarding your product !";
				$message = $notificationText;
				// send the notification
				echo ($this->sendMailNotification($to, $subject, $message, $from) ? '1' : '0');
				// Get the tag owner Id
				$this->DbObject->resetWhere();
				$this->DbObject->where('tagId', $tagId);
				$this->DbObject->update("tagging__taggeditems", array('noOfInterests' => ($noOfInterests + 1)));
				// return the notification text
				echo $notificationText;
			}else{
				echo "";
			}
		}
	}
	
	public function loadAllNotifications(){

		if (isset($_SESSION['currentUser'])) $this->userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $this->userId = $_SESSION['fbUser']['userId'];			
		// Get the count of unreadable notifications
		$unreadableNotificationsCount = $this->DbObject->query('SELECT COUNT(notificationViewed) AS notificationViewedCount FROM tagging__notification WHERE toUserId = '.$this->userId.' AND notificationViewed = 0');
		$notificationUnviewedCount = $unreadableNotificationsCount[0]['notificationViewedCount'];
		// Get all the notifications
		$allNotifications = $this->DbObject->query("SELECT * FROM tagging__notification WHERE toUserId = " . $this->userId . " ORDER BY notificationDate DESC LIMIT 10");
		echo json_encode(array('notificationUnviewedCount' => $notificationUnviewedCount, 'allNotifications' => $allNotifications));
	}
	
	public function clearingTheNotificationsCount(){
	
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];			
		// Clear the notifications unreadable count
		$this->DbObject->resetWhere();
		$this->DbObject->where('notificationViewed', 0);
		$this->DbObject->where('toUserId', $userId);		
		echo ($this->DbObject->update("tagging__notification", array('notificationViewed' => 1)) ? '1' : '0');
	}
	
	public function getSellerInfoAndTheFeedbackForm(){
	
		$sellerContactInfoHtml = "";
		$sellerContactInfoHtml .= '<div class="clearH10"></div><form name="getBuyerInfoForm" action="" method="post">
										<input type="text" name="buyerInfo[email]" value="Email" readonly="readonly" class="buyerInfoEmailView" />
										<input type="text" name="buyerInfo[email]" value="" class="buyerInfoEmailWrite" />
										<div class="clearH5"></div>
										<input type="text" name="buyerInfo[message]" value="Message" readonly="readonly" class="buyerInfoMessageView" />
										<input type="text" name="buyerInfo[message]" value="" class="buyerInfoMessageWrite" />
										<div class="clearH5"></div>
										<input type="button" value="Send" class="contactBtn GreenBut" />
								   </form><div class="clear"></div>';
		echo $sellerContactInfoHtml;						   
	}
	
	public function getSellerInfoAndSendToBuyerWhenLogged(){
	
		$sellerContactInfoHtml = "";
		$sellerContactInfoHtml .= '<div class="clearH10"></div><form name="getBuyerInfoForm" action="" method="post">
										<input type="text" name="buyerInfo[message]" value="Message" readonly="readonly" class="buyerInfoMessageView" />
										<div class="clearH5"></div>
										<input type="text" name="buyerInfo[message]" value="" class="buyerInfoMessageWrite" />
										<div class="clearH5"></div>
										<input type="button" value="Send" class="contactBtn GreenBut" />
								   </form><div class="clear"></div>';
		echo $sellerContactInfoHtml;						   
	}
	
	public function sendMailNotificationForSellerFromBuyer(){

		if (isset($_POST['buyerEmail'])){
			// Get the user email
			if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
				if (isset($_SESSION['currentUser'])){ 
					$userId = $_SESSION['currentUser']['userId'];
				}else if (isset($_SESSION['fbUser'])){ 
					$userId = $_SESSION['fbUser']['userId'];					
				}
			}
			// Grab the seller email for related product
			$hashedImageId = trim($_POST['relAId']);
			$imageId = $_SESSION['recentLoads'][$hashedImageId];
			// Get the user email of the picture owner
			$pictureOwnerDetails = $this->DbObject->query("SELECT rehand__users.email FROM rehand__users JOIN tagging__pictures ON tagging__pictures.userId = rehand__users.userId WHERE tagging__pictures.pictureId = ". $imageId);
			
			if( !is_array($pictureOwnerDetails) || !isset($pictureOwnerDetails[0]['email']) )
			{
				echo json_encode( array('error' => 'No seller found') );	
				return;
			}
			
			if( !isset($_POST['messageFromBuyer']) || (trim ($_POST['messageFromBuyer']) == ""))
			{
				echo json_encode( array('error' => 'No Message') );	
				return;
			}
			
			$sellerEmail = $pictureOwnerDetails[0]['email'];
			$to      = $sellerEmail;
			$from    = trim($_POST['buyerEmail']);
			$subject = "New message regarding your product !";
			if ($_POST['contactNo'] != "") $message .= 'Buyer contact no: ' . trim($_POST['contactNo']) . '<br />';
			if ($_POST['address'] != "") $message .= 'Buyer address: ' . trim($_POST['address']) . '<br /><br />';			
			$message .= trim($_POST['messageFromBuyer']);
			// send the notification
			$toUserId = $this->getTheUserIdByUserEmail($to);
			
			
			
			$qry = "INSERT INTO tagging__notification (`fromUserId`, `toUserId`, `notificationLinkId`, `notificationType`, `notificationText`, `notificationDate`, `notificationTime`, `notificationViewed`) 
																			VALUES(".$userId.", ".$toUserId.", 0, '', '".trim($_POST['messageFromBuyer'])."', '".date("Y-m-d")."', '".date("H:i:s")."', 0)";			
			echo ($this->DbObject->query($qry)) ? ($this->sendMailNotification($to, $subject, $message, $from) ? '1' : '0') : '0';
		}
	}

	public function getTheUserIdByUserEmail($email){
	
		$userIdTemp = $this->DbObject->query("SELECT userId FROM rehand__users WHERE email = '" . $email ."'");
		return $userIdTemp[0]['userId'];
	}

	public function getTheUserEmailByUserId($userId){

		$useremailTemp = $this->DbObject->query("SELECT email FROM rehand__users WHERE userId = " . $userId);
		return $useremailTemp[0]['email'];
	}
	
	public function sendNotificationForSellerFromBuyerAsAReply(){

		if (isset($_POST['buyerEmail'])){
			// Get the user email
			if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
				if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
				else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];			
			}
			$originalRootNotificationId = base64_decode($_POST['relAId']);
			// get the original details for this root id
			$resultsTemp = $this->DbObject->query("SELECT tagging__notification.*, rehand__users.firstName, rehand__users.lastName FROM tagging__notification JOIN rehand__users ON tagging__notification.toUserId = rehand__users.userId WHERE notificationId = " .$originalRootNotificationId);
			// Inserting a new notification
			//$tagNotificationDetails = array('fromUserId' => $userId, 'toUserId' => $resultsTemp[0]['toUserId'], 'notificationLinkId' => $originalRootNotificationId, 'notificationType' => $resultsTemp[0]['notificationType'], 'notificationText' => trim($_POST['messageFromBuyer']), 'notificationDate' => date("Y-m-d"), 'notificationTime' => date("H:i:s"));
			$qry = "INSERT INTO tagging__notification (`fromUserId`, `toUserId`, `notificationLinkId`, `notificationType`, `notificationText`, `notificationDate`, `notificationTime`, `notificationViewed`) 
																			VALUES(".$userId.", ".$resultsTemp[0]['fromUserId'].", ".$originalRootNotificationId.", '".$resultsTemp[0]['notificationType']."', '".trim($_POST['messageFromBuyer'])." , by : ".$resultsTemp[0]['firstName'] . " " . $resultsTemp[0]['lastName']."', '".date("Y-m-d")."', '".date("H:i:s")."', 0)";			
			$to      = $this->getTheUserEmailByUserId($resultsTemp[0]['fromUserId']);
			$from    = trim($_POST['buyerEmail']);
			$subject = "New reply for your message !";
			$message = trim($_POST['messageFromBuyer']);
			echo ($this->DbObject->query($qry)) ? ($this->sendMailNotification($to, $subject, $message, $from) ? '1' : '0') : '0';
		}
	}

	public function returnSuburbInputForm(){
		
		$suburbInpputForm = "";
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];			
		$suburbInfoTemp = $this->DbObject->query('SELECT rehand__users.mobile_no, rehand__postalCodes.Locality FROM rehand__users JOIN rehand__postalCodes ON rehand__postalCodes.Pcode = rehand__users.postCode WHERE rehand__users.userId = '. $userId);
		$suburbInpputForm .= '<div class="UploadInfo hide" style="width:752px;"><form name="saveSuburbFomr" action="" method="post">
										<span class="locationNameSpan">Location</span><input type="text" name="locationNameWrite" class="locationInputWrite" value="'.$suburbInfoTemp[0]['Locality'].'" />
										<input type="text" name="contactNameWrite" value="'.$suburbInfoTemp[0]['mobile_no'].'" class="ContactInputWrite" /><span class="ContactNameSpan">Contact</span>
							 </form><div class="clearH20"></div>To tag an item, click on the item you want to sell.<div class="clearH5"></div></div>';
		echo $suburbInpputForm;						   
	}
	
	public function saveLocationData(){
	
		if (isset($_POST['imgId'])){ 
			$originalImageId = $_SESSION['recentUploads'][$_POST['imgId']];
			$this->DbObject->query("UPDATE tagging__pictures SET location = '" .trim($_POST['location'])."', contactNo = '".trim($_POST['contactNo'])."' WHERE pictureId = ".$originalImageId);
		}
	}
	
	public function getBrowser(){
	
		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}
	   
		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}elseif(preg_match('/Firefox/i',$u_agent)){
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}elseif(preg_match('/Chrome/i',$u_agent)){
			$bname = 'Google Chrome';
			$ub = "Chrome";
		}elseif(preg_match('/Safari/i',$u_agent)){
			$bname = 'Apple Safari';
			$ub = "Safari";
		}elseif(preg_match('/Opera/i',$u_agent)){
			$bname = 'Opera';
			$ub = "Opera";
		}elseif(preg_match('/Netscape/i',$u_agent)){
			$bname = 'Netscape';
			$ub = "Netscape";
		}
	   
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
	   
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}else {
				$version= $matches['version'][1];
			}
		}else {
			$version= $matches['version'][0];
		}
	   
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
	   
		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	}
	
   public function checkEmailAddressValid($email) {

		return (preg_match( "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $email)) ? true : false;
	}
	
	public function submitFeedbackFormData(){
	
		$feedbackName = ""; 
		$feedbackEmail = ""; 
		$mainCategory = ""; 
		$subCategory = ""; 
		$message = ""; 
		$rating = ""; 
		$dateGivenFeedback = date("Y-m-d H:i:s"); 
		$whichBrowser = ""; 
		$browserVersion = "";
		$currentBrowserDetail = $this->getBrowser();
		$whichBrowser = $currentBrowserDetail['name'];
		$browserVersion = $currentBrowserDetail['version'];
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$feedbackName = $_SESSION['currentUser']['firstName'] . " " . $_SESSION['currentUser']['lastName'];
			}else if (isset($_SESSION['fbUser'])){
				$feedbackName = $_SESSION['fbUser']['firstName'] . " " . $_SESSION['fbUser']['lastName'];
			}			
		}else{
			if (isset($_POST['feedbackName'])){
				$feedbackName = trim($_POST['feedbackName']);
			}
		}
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$feedbackEmail = $_SESSION['currentUser']['email'];
			}else if (isset($_SESSION['fbUser'])){
				$feedbackEmail = $_SESSION['fbUser']['email'];
			}			
		}else{
			if (isset($_POST['feedbackEmail'])){
				$feedbackEmail = trim($_POST['feedbackEmail']);
			}
		}
		if (isset($_POST['mainTop'])){
			$mainCategory = trim($_POST['mainTop']);
		}
		if (isset($_POST['curTop'])){
			$subCategory = trim($_POST['curTop']);
		}
		if (isset($_POST['message'])){
			$message = trim($_POST['message']);
		}
		if (isset($_POST['rating'])){
			$rating = trim($_POST['rating']);
		}
		$sql = "INSERT INTO rehand__feedbacks (`feedbackEmail`, `feedbackName`, `mainCategory`, `subCategory`, `message`, `rating`, `dateGivenFeedback`, `whichBrowser`, `browserVersion`) VALUES('".$feedbackEmail."', '".$feedbackName."', '".$mainCategory."', '".$subCategory."', '".$message."', '".$rating."','".date("Y-m-d H:i:s")."', '".$whichBrowser."', '".$browserVersion."')";
		if ($this->DbObject->query($sql)){
			// multiple recipients
			$mailto  = 'aloka@socialseedmedia.com.au, uditha@socialseedmedia.com.au, heroly@socialseedmedia.com.au, feedback@rehand.com, ruchira@socialseedmedia.com.au, afzal@socialseedmedia.com.au, janaka@socialseedmedia.com.au';
			//$mailto  = 'ruchira@socialseedmedia.com.au';
			if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){			
				$origianlMessage .= "Email Heading: User Feedback (Registered User)<br /><br />";
			}else{
				$origianlMessage .= "Email Heading: User Feedback (Unregistered User)<br /><br />";
			}
			$origianlMessage .= "User's Name: ".$feedbackName."<br />";
			$origianlMessage .= "Email Address: ".$feedbackEmail."<br />";
			$origianlMessage .= "Browser: ".$whichBrowser."<br />";
			$origianlMessage .= "Browser Version: ".$browserVersion."<br />";
			$origianlMessage .= "Page URL: ".$_SERVER['HTTP_REFERER']."<br />";
			$origianlMessage .= "Rating: ".$rating."<br />";
			$origianlMessage .= "Feedback Main Category: ".$mainCategory."<br />";
			$origianlMessage .= "Feedback Sub Category: ".$subCategory."<br />";
			$origianlMessage .= "Message: ".$message. "<br /><br />";						
			echo ($this->sendMailNotification($mailto, "Feedback message", $origianlMessage, "", "Rehand - Feedback") ? '1' : '0');
		}else{
			echo '2';
		}
	}
	
	/* This function will prepare the value entered by the user to put it in to the database */
	static function mysql_preperation($value){		
	
		$magic_quotes_active = get_magic_quotes_gpc();
		$new_enough_php = function_exists("mysql_real_escape_string"); 
		// i.e. PHP >= v4.3.0
		if ($new_enough_php){ 
			// PHP v4.3.0 or higher
			// undo any magic quote effects so mysql_real_escape_string can do the work
			if ($magic_quotes_active){ 
//				$value = stripslashes($value); 
			}
			$value = mysql_real_escape_string($value);
		}else{ 
			// before PHP v4.3.0
			// if magic quotes aren't already on then add slashes manually
			if (!$magic_quotes_active){ 
				$value = addslashes( $value ); 
			}
			// if magic quotes are active, then the slashes already exist
		}
		return $value;
	}
	/* End of the function */
	
	public function grabGroupNamesForAutoSuggestions(){
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('Active', 1);
		$results = $this->DbObject->get("rehand__groups", array('Group_name'), NULL, NULL, "CreatedAt DESC");	
		$i = count($results);
		foreach($results as $eachResult){
			if ($i != 1){
				$groupNames .= '"'.$eachResult['Group_name'].'",';
			}else{
				$groupNames .= '"'.$eachResult['Group_name'].'"';
			}
			$i--;
		}
		echo "[".$groupNames."]";
	}

	// This is function is to get the group profile picture
	public function getTheProfilePicture($groupId){
	
		// Get the pictureIds for each group id
		$picIdsWhichHaveSubscribed = $this->DbObject->query("SELECT PictureId FROM rehand__group_pictures WHERE GroupId = " . $groupId);
		foreach($picIdsWhichHaveSubscribed as $each){
			$picIdsWhichHaveSubscribedMod[] = $each['PictureId'];
		}
		if ($picIdsWhichHaveSubscribedMod){
			foreach($picIdsWhichHaveSubscribedMod as $eachPicId){
				$resultTemp = $this->DbObject->query("SELECT SUM(noOfInterests) AS totCountOfNoOfInt FROM tagging__taggeditems WHERE PictureId = " . $eachPicId);
				$finalResult[$eachPicId] = $resultTemp[0];
			}
			if ($finalResult){
				// Now get the maximum count from these image id array collection
				$PicIdForTheGroupProfilePic = array_keys($finalResult, max($finalResult));
				$theGroupProfilePicTemp = $this->DbObject->query("SELECT uploadMImgLocation FROM tagging__pictures WHERE pictureId = " . $PicIdForTheGroupProfilePic[0]);
				return $theGroupProfilePicTemp[0]['uploadMImgLocation'];
			}else{
				return NULL;
			}
		}else{
			return NULL;			
		}	
	}

	public function searchForASpecificGroup(){
	
		$htmlResult = "";
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		if (isset($_POST['searchGrpName'])){
			$sql = "SELECT 
						rehand__groups.GroupId,
						rehand__groups.Group_name, 
						rehand__groups.Group_desc,
						rehand__groups.number_of_members
					FROM rehand__groups
					WHERE rehand__groups.Group_name like '%".addslashes(trim($_POST['searchGrpName']))."%' 
					AND rehand__groups.Active = 1 
					ORDER BY rehand__groups.GroupId DESC";	
			$searchResult = $this->DbObject->query($sql);
			// Get the user specific group ids
			$this->DbObject->resetWhere();
			$this->DbObject->where('Membership_active', 1);
			$this->DbObject->where('memberId', $userId);		
			$groupsBelongsToYou = $this->DbObject->get('rehand__group_members', array('GroupId'));
			foreach($groupsBelongsToYou as $eachGroup){
				$allGroups[] = $eachGroup['GroupId'];
			}
			if (count($searchResult) > 1){
				foreach($searchResult as $eachResult){
					$htmlResult .= "Group Name: <a id='group_res_".$eachResult['GroupId']."' href='javascript:void(0);' class='groupLetterRepresentGroup'>".$eachResult['Group_name']."</a>";
					$htmlResult .= "<img style='width:50px; height:50px;' src='http://". $_SERVER['HTTP_HOST'].'/www.rehand.com/'.(($eachGroup['profilePic'] != '') ? $this->getTheProfilePicture($eachResult['GroupId']) : 'public/images/group.jpg') ."' /><br />";
					if (!in_array($eachResult['GroupId'], $allGroups)){
						$htmlResult .= "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnkForSearch_".$eachResult['GroupId']."' class='joinGroupLinkForSearch hide'>Join Group</a><br />";                                                        
					}else{
						$htmlResult .= "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnkForSearch_".$eachResult['GroupId']."' class='leaveGroupLinkForSearch hide'>Leave Group</a><br />";                                                        					
					}
					$htmlResult .= "Group Description: <span>".$eachResult['Group_desc']."</span><br />";
					$htmlResult .= "Number of Rehanders in this group: <span>".$eachResult['countOfRehanders']."</span><br /><div class='clearH10'><hr />";										
				}	
			}elseif ((count($searchResult) == 1) && ($searchResult[0]['Group_name'] != "")){
				foreach($searchResult as $eachResult){
					$htmlResult .= "Group Name: <a id='group_res_".$eachResult['GroupId']."' href='javascript:void(0);' class='groupLetterRepresentGroup'>".$eachResult['Group_name']."</a>";
					$htmlResult .= "<img style='width:50px; height:50px;' src='http://". $_SERVER['HTTP_HOST'].'/www.rehand.com/'.(($eachGroup['profilePic'] != '') ? $this->getTheProfilePicture($eachResult['GroupId']) : 'public/images/group.jpg') ."' /><br />";
					if (!in_array($eachResult['GroupId'], $allGroups)){
						$htmlResult .= "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='joinGrpLnkForSearch_".$eachResult['GroupId']."' class='joinGroupLinkForSearch hide'>Join Group</a><br />";                                                        
					}else{
						$htmlResult .= "<a style='text-decoration:underline; color:#069;'  href='javascript:void(0);' id='leaveGrpLnkForSearch_".$eachResult['GroupId']."' class='leaveGroupLinkForSearch hide'>Leave Group</a><br />";                                                        					
					}
					$htmlResult .= "Group Description: <span>".$eachResult['Group_desc']."</span><br />";
					$htmlResult .= "Number of Rehanders in this group: <span>".$eachResult['countOfRehanders']."</span>";	
				}
			}else{
				$htmlResult .= "No Results found !";
			}
			echo $htmlResult;
		}
	}
	
	public function checkWhichLetterHasTheGroupsAlready(){
	
		foreach(range('A', 'Z') as $letter){		
			$results = $this->DbObject->query("SELECT `GroupId`,`Group_name`, `Group_desc` FROM `rehand__groups` WHERE `Group_name` like '{$letter}%' AND `Active` = 1");
			$whichLetter = $letter;
			if (!empty($results)) break;
		}
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		// Get the user specific group ids
		$this->DbObject->resetWhere();
		$this->DbObject->where('Membership_active', 1);
		$this->DbObject->where('memberId', $userId);		
		$groupsBelongsToYou = $this->DbObject->get('rehand__group_members', array('GroupId'));
		foreach($groupsBelongsToYou as $eachGroup){
			$allGroups[] = $eachGroup['GroupId'];
		}
		// Check whether each group is in this list
		$i = 0;
		foreach($results as $eachGroup){
			$newResult[$i]['GroupId'] = $eachGroup['GroupId'];
			$newResult[$i]['Group_name'] = $eachGroup['Group_name'];
			$newResult[$i]['Group_desc'] = $eachGroup['Group_desc'];			
			$newResult[$i]['groupProfilePic'] = $eachGroup['groupProfilePic'];			
			$newResult[$i]['leaveStatus'] = (in_array($eachGroup['GroupId'], $allGroups)) ? true : false;
			$i++;
		}
		echo json_encode(array('results' => $newResult, 'whichLetter' => $whichLetter));
	}
	
	public function searchForAGroupNameByItsLetter(){

		if (isset($_GET['letterG'])){
			$searchedGroups = $this->DbObject->query("SELECT `GroupId`,`Group_name`, `Group_desc`, `groupProfilePic` FROM `rehand__groups` WHERE `Group_name` like '".addslashes(trim($_GET['letterG']))."%' AND `Active` = 1 ORDER BY GroupId DESC");
			if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
			else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
			// Get the user specific group ids
			$this->DbObject->resetWhere();
			$this->DbObject->where('Membership_active', 1);
			$this->DbObject->where('memberId', $userId);		
			$groupsBelongsToYou = $this->DbObject->get('rehand__group_members', array('GroupId'));
			foreach($groupsBelongsToYou as $eachGroup){
				$allGroups[] = $eachGroup['GroupId'];
			}
			// Check whether each group is in this list
			$i = 0;
			foreach($searchedGroups as $eachGroup){
				$newResult[$i]['GroupId'] = $eachGroup['GroupId'];
				$newResult[$i]['Group_name'] = $eachGroup['Group_name'];
				$newResult[$i]['Group_desc'] = $eachGroup['Group_desc'];			
				$newResult[$i]['groupProfilePic'] = $eachGroup['groupProfilePic'];			
				$newResult[$i]['leaveStatus'] = (in_array($eachGroup['GroupId'], $allGroups)) ? true : false;
				$i++;
			}
			echo json_encode($newResult);
		}
	}
	
	public function joinToThisGroup(){
	
		if (isset($_POST['groupIdToJoin'])){
			$number_of_members = $this->getTheNumberOfRehandersForASpecificGroupName($_POST['groupIdToJoin']);			
			$this->DbObject->query("UPDATE `rehand__groups` SET `number_of_members` = " . ($number_of_members + 1) . " WHERE `GroupId` = " . $_POST['groupIdToJoin']);
			
			
			if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
			else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
			
			echo ($this->DbObject->query("INSERT INTO rehand__group_members (`GroupId`, `memberId`, `JoinedDate`, `Membership_active`) VALUES(".$_POST['groupIdToJoin'].", ".$userId.", '".date("Y-m-d H:i:s")."', 1)") ? $_POST['groupIdToJoin'] : false);
		}
	}
	
	public function leaveThisGroup(){

		if (isset($_POST['leaveGroupId'])){
			$number_of_members = $this->getTheNumberOfRehandersForASpecificGroupName($_POST['leaveGroupId']);			
			$this->DbObject->query("UPDATE `rehand__groups` SET `number_of_members` = " . ($number_of_members - 1) . " WHERE `GroupId` = " . $_POST['leaveGroupId']);
			if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
			else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
			echo ($this->DbObject->query("DELETE FROM `rehand__group_members` WHERE `Membership_active` = 1 AND `GroupId` = ".$_POST['leaveGroupId']." AND memberId = ".$userId) ? $_POST['leaveGroupId'] : false);
		}
	}
	
	public function loadTopThreeGroupSubscriptionWhichUserIsNotRegisteredYet()
	{
		
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		$qry = "SELECT * FROM ( SELECT `GroupId` , count( `Membership_active` ) AS memberCount
								FROM `rehand__group_members`
								WHERE `GroupId` NOT IN (SELECT GroupId FROM rehand__group_members WHERE memberId = ".$userId." )
								GROUP BY `GroupId`
								ORDER BY memberCount ASC , GroupId ASC
								) AS tbl, rehand__groups rg
						WHERE rg.GroupId = tbl.GroupId
						LIMIT 3";
			
				
						
		$groups = $this->DbObject->query($qry);
		
		$qry = "SELECT *
					FROM  rehand__groups rg
					WHERE rg.GroupId NOT IN (SELECT GroupId FROM rehand__group_members)
					order by rand()
					LIMIT 3";
					
		$groupsWithZeroCount = $this->DbObject->query($qry);
		
		
		
		$groupDetails = array();
		
		
		
		foreach($groups as $group){
			
			$groupImageUrl = $_GET['webpath'].(($this->getTheProfilePicture($group['GroupId']) != "" || $this->getTheProfilePicture($group['GroupId']) != NULL) ? ($this->getTheProfilePicture($group['GroupId'])) : ('public/images/group.jpg'));
			$noOfRehanders = $this->getTheMemberCountForThisGroup($group['GroupId']);
			
			$groupDetails[] = array(
									'groupId' => $group['GroupId'],
								  	'groupName' => $group['Group_name'],
									'numberOfUsers' => $noOfRehanders,
									'groupImageUrl' => $groupImageUrl
								   );
		}
		
		$groupDetailsCount = count( $groupDetails );
		
		$upperLimit = count($groupsWithZeroCount);
		
		if( count($groupsWithZeroCount) > 3)
			$upperLimit = 3;
		
		
		
		if(  $groupDetailsCount < 3 )
		{
			for($i = 0; $i< ($upperLimit-$groupDetailsCount); $i++)
			{
				
				$groupImageUrl = $_GET['webpath'].(($this->getTheProfilePicture($groupsWithZeroCount[$i]['GroupId']) != "" || $this->getTheProfilePicture($groupsWithZeroCount[$i]['GroupId']) != NULL) ? ($this->getTheProfilePicture($groupsWithZeroCount[$i]['GroupId'])) : ('public/images/group.jpg'));
				
				$groupDetails[] = array(
										'groupId' => $groupsWithZeroCount[$i]['GroupId'],
										'groupName' => $groupsWithZeroCount[$i]['Group_name'],
										'numberOfUsers' => 0,
										'groupImageUrl' => $groupImageUrl
									   );
			}
		}
		
		echo json_encode($groupDetails);
	}
	
	public function loadAllOwnedGroupsForSubscriptions(){

		
		$limit = "";
		if ( isset($_GET['limit']) )
			$limit = " LIMIT ".$_GET['limit']." ";
		
		$random = "";
		if ( isset($_GET['random']) )
			$random = " ORDER BY RAND() ";	
		
		$groupsLimitPerSectionDiv = 3;
		$groupDetails = array();
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		$qry = "SELECT rehand__groups.GroupId, rehand__groups.Group_name, rehand__groups.Group_desc
														FROM rehand__groups 
														JOIN rehand__group_members ON rehand__groups.GroupId = rehand__group_members.GroupId 
														WHERE rehand__group_members.memberId = " . $userId . " AND rehand__group_members.Membership_active = 1".$random.$limit;
		$groupNames = $this->DbObject->query($qry);
		$noOfEachGroupWrapperDivs = floor(count($groupNames) / 2);
		$newShowingInputCheckBoxesAfterQuickSearch = "";
		
		
				
		foreach($groupNames as $eachEachGroupName){
			
			$noOfRehanders = $this->getTheMemberCountForThisGroup($eachEachGroupName['GroupId']);
			$groupImageUrl = $_GET['webpath'].(($this->getTheProfilePicture($eachEachGroupName['GroupId']) != "" || $this->getTheProfilePicture($eachEachGroupName['GroupId']) != NULL) ? ($this->getTheProfilePicture($eachEachGroupName['GroupId'])) : ('public/images/group.jpg'));
			
			$newShowingInputCheckBoxesAfterQuickSearch = "";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<div class='eachGroupWrapper'>";			
			$newShowingInputCheckBoxesAfterQuickSearch .= "<label lang='" . $eachEachGroupName['Group_name'] . "'>" . $eachEachGroupName['Group_name'] . "</label><div class='clearH10'></div>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<img class='thirdStepImgSizes' src='". $groupImageUrl. "' /><div style='position:relative;float:left;'>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<div class='UploadLocationMain'><span class='noOfMembers' title='" . $noOfRehanders . " followers'>" . $noOfRehanders . "</span><span class='noOfItems' title='".$this->getTheNumberOfPicturesSubscribedForThisGroup($eachEachGroupName['GroupId'])." items for sale'>".$this->getTheNumberOfPicturesSubscribedForThisGroup($eachEachGroupName['GroupId'])."</span></div>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "</div><div class='clearH10'></div>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<p>".$eachEachGroupName['Group_desc']."</p>";			
			$newShowingInputCheckBoxesAfterQuickSearch .= "<input name='grpNameSubscribe' value='Subscribe' type='button' id='subscribeToThisGroupId_" . $eachEachGroupName['GroupId'] . "' class='PosttoGroup' /></div>";	
			$groupDetails[] = array(
									'groupId' => $eachEachGroupName['GroupId'],
								  	'groupName' => $eachEachGroupName['Group_name'],
									'numberOfUsers' => $noOfRehanders,
									'groupImageUrl' => $groupImageUrl,
								  	'html' => $newShowingInputCheckBoxesAfterQuickSearch
									
								   );
		}
		echo json_encode($groupDetails);
	}

	public function getTheNumberOfPicturesSubscribedForThisGroup($groupId){

		$result = $this->DbObject->query("SELECT COUNT(`PictureId`) AS numberOfPicsSubscribed FROM `rehand__group_pictures` WHERE `GroupId` = ". $groupId);
		return $result[0]['numberOfPicsSubscribed'];
	}

	public function getGroupName($groupId){

		$group = $this->DbObject->query("SELECT `Group_name` FROM rehand__groups WHERE `GroupId` = ".$groupId);
		return $group[0]['Group_name'];
	}
	
	public function getTheNumberOfRehandersForASpecificGroupName($groupId){

		$groupsBelongsToYou = $this->DbObject->query("SELECT number_of_members FROM rehand__groups WHERE `GroupId` = ".$groupId);
		return $groupsBelongsToYou[0]['number_of_members'];
	}
	
	
	public function GetTheGroupNameForTheLetter(){

		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		if (isset($_GET['letterWasPressed'])){
			$qry = "SELECT rehand__groups.GroupId, rehand__groups.Group_name, rehand__groups.Group_desc
														FROM rehand__groups 
														JOIN rehand__group_members ON rehand__groups.GroupId = rehand__group_members.GroupId 
														WHERE rehand__groups.Group_name LIKE '".$_GET['letterWasPressed']."%' 
														AND rehand__group_members.memberId = " . $userId . " AND rehand__group_members.Membership_active = 1";

			$groupDetails = array();
			foreach($groupNames as $eachEachGroupName){
				$newShowingInputCheckBoxesAfterQuickSearch = "";
				$newShowingInputCheckBoxesAfterQuickSearch .= "<label lang='" . $eachEachGroupName['Group_name'] . "'>" . $eachEachGroupName['Group_name'] . "&nbsp;&nbsp;<input type='checkbox' id='grpId_" . $eachEachGroupName['GroupId'] . "' />";
				$newShowingInputCheckBoxesAfterQuickSearch .= "<div class='clearH5'></div><span>" . $this->getTheNumberOfRehandersForASpecificGroupName($eachEachGroupName['GroupId']) . " Followers</span></label>";
				$groupDetails[] = array(
									  'groupName' => $eachEachGroupName['Group_name'], 
									  'html' => $newShowingInputCheckBoxesAfterQuickSearch
									  );
			}
			echo json_encode($groupDetails);
		}
	}
	
	public function resetTheGroupNamesListInHtml(){
	
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		$groupNames = $this->DbObject->query('SELECT rehand__groups.Group_name, rehand__groups.Group_desc, rehand__groups.GroupId FROM rehand__groups JOIN rehand__group_members ON rehand__groups.GroupId =  rehand__group_members.GroupId 
											WHERE  rehand__group_members.memberId = 1 AND rehand__group_members.Membership_active = 1');
		if (count($groupNames) >  1){
			foreach($groupNames as $eachGroupName){
				$newGroupNames[] = $eachGroupName;	
				if (in_array($eachGroupName, $newGroupNames)){
					$duplicateOfResultForALetterFound = true;
					break;
				}else{
					$duplicateOfResultForALetterFound = false;
				}
			}
		}
		$groupDetails = array();
		foreach($groupNames as $eachEachGroupName){
			$newShowingInputCheckBoxesAfterQuickSearch = "";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<div class='eachGroupWrapper'>";			
			$newShowingInputCheckBoxesAfterQuickSearch .= "<label lang='" . $eachEachGroupName['Group_name'] . "'>" . $eachEachGroupName['Group_name'] . "</label><div class='clearH10'></div>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<img class='thirdStepImgSizes' src='".$_GET['webpath'].$this->getTheProfilePicture($eachEachGroupName['GroupId'])."' /><div style='position:relative;float:left;'>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "<div class='UploadLocationMain'><span class='noOfMembers' title='" . $this->getTheNumberOfRehandersForASpecificGroupName($eachEachGroupName['GroupId']) . " followers'>" . $this->getTheNumberOfRehandersForASpecificGroupName($eachEachGroupName['GroupId']) . "</span><span class='noOfItems' title='".$this->getTheNumberOfPicturesSubscribedForThisGroup($eachEachGroupName['GroupId'])." items for sale'>".$this->getTheNumberOfPicturesSubscribedForThisGroup($eachEachGroupName['GroupId'])."</span></div>";
			$newShowingInputCheckBoxesAfterQuickSearch .= "</div><div class='clearH10'></div>";			
			$newShowingInputCheckBoxesAfterQuickSearch .= "<p>".$eachEachGroupName['Group_desc']."</p>";	
			
			$newShowingInputCheckBoxesAfterQuickSearch .= "<input name='grpNameSubscribe' value='Subscribe' type='button' id='subscribeToThisGroupId_" . $eachEachGroupName['GroupId'] . "' class='PosttoGroup' /></div>";			
			$groupDetails[] = array(
								  	'groupName' => $eachEachGroupName['Group_name'], 
								  	'html' => $newShowingInputCheckBoxesAfterQuickSearch
								   );
		}
		
		
		echo json_encode($groupDetails);
	}
	
	public function subscribeToThisPicture(){
	
		if ((isset($_POST['picId'])) && (isset($_POST['subscriptionsAsStr']))){
		  	$originalPicId = $_SESSION['recentUploads'][$_POST['picId']];
			if (strstr($_POST['subscriptionsAsStr'], ',')){
				$subscriptionsAsStrSplitted = explode(',', $_POST['subscriptionsAsStr']);
				foreach($subscriptionsAsStrSplitted as $eachValue){
					if (($eachValue != '') && (!empty($eachValue)) && (isset($eachValue))){
						$this->DbObject->query("INSERT INTO rehand__group_pictures (`GroupId`, `PictureId`) VALUES (".$eachValue.", ".$originalPicId.")");
					}
				}
			}else{
				$this->DbObject->query("INSERT INTO rehand__group_pictures (`GroupId`, `PictureId`) VALUES (".$_POST['subscriptionsAsStr'].", ".$originalPicId.")");
			}
		}else{
			echo '0';
		}			
	}
	
	public function checkThesePicturesGroups(){

		if (isset($_GET['picId'])){
			$subscriptedGroupHtml = "";
			// check this picture has subscribed to any groups which this user has belongs to
			// Get the user specific group ids
			if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
			else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
			$this->DbObject->resetWhere();
			$this->DbObject->where('Membership_active', 1);
			$this->DbObject->where('memberId', $userId);		
			$groupsBelongsToYou = $this->DbObject->get('rehand__group_members', array('GroupId'));
			if (empty($groupsBelongsToYou)) $noGroupsJoined = true; else $noGroupsJoined = false; 
			foreach($groupsBelongsToYou as $eachGroup){
				$allGroups[] = $eachGroup['GroupId'];
			}
			// Get the groups this picture has subscribed to
			$this->DbObject->resetWhere();
			$this->DbObject->where('PictureId', $_SESSION['recentUploads'][$_GET['picId']]);
			$groupsBelongsToThisPicture = $this->DbObject->get('rehand__group_pictures', array('GroupId'));
			foreach($groupsBelongsToThisPicture as $eachGroup){
				$allGroupsForThisPicture[] = $eachGroup['GroupId'];
			}
			// Check which are my own groups out from these
			foreach($allGroupsForThisPicture as $eachGroupId){
				if (in_array($eachGroupId, $allGroups)){
					$ownedSubscriptedGroups[] = $eachGroupId;
				}
			}
			// Get the rest of the details for the each group id
			foreach($ownedSubscriptedGroups as $eachGroupId){
				$this->DbObject->resetWhere();
				$this->DbObject->where('GroupId', $eachGroupId);
				$this->DbObject->where('Active', 1);		
				$groupsInDetails = $this->DbObject->get('rehand__groups', array('GroupId', 'Group_name', 'Group_desc'));
				$subscriptedGroupHtml .= "<label lang='" . $groupsInDetails[0]['Group_name'] . "'>" . $groupsInDetails[0]['Group_name'] . "&nbsp;&nbsp;<input name='grpName[]' checked='checked' type='checkbox' id='grpId_" . $groupsInDetails[0]['GroupId'] . "' />";
				$subscriptedGroupHtml .= "<div class='clearH5'></div><span>" . $this->getTheNumberOfRehandersForASpecificGroupName($groupsInDetails[0]['GroupId']) . " Followers</span></label>";
			}
			// Get the user other groups which he/she has joined with
			foreach($allGroups as $eachGrp){
				if (!in_array($eachGrp, $ownedSubscriptedGroups)){
					$subscriptedGroupHtml .= "<label lang='" . $this->getGroupNameByGroupId($eachGrp) . "'>" . $this->getGroupNameByGroupId($eachGrp) . "&nbsp;&nbsp;<input name='grpName[]' type='checkbox' id='grpId_" . $eachGrp . "' />";
					$subscriptedGroupHtml .= "<div class='clearH5'></div><span>" . $this->getTheNumberOfRehandersForASpecificGroupName($eachGrp) . " Followers</span></label>";
				}
			}
			$subscriptedGroupHtml .= "<div class='clearH5'></div><input type='button' name='subscribeToTheseGroupsInUploaded' id='subscribeToTheseGroupsInUploaded' value='Add to Group' />";			
			echo json_encode(array('subscriptedGroupHtml' => $subscriptedGroupHtml, 'ownedSubscriptedGroups' => $ownedSubscriptedGroups, 'noGroupsJoined' => $noGroupsJoined));
		}
	}
	
	public function getGroupNameByGroupId($groupId){
	
		  $this->DbObject->resetWhere();
		  $this->DbObject->where('Active', 1);
		  $this->DbObject->where('GroupId', $groupId);
		  $groupName = $this->DbObject->get('rehand__groups', array('Group_name'));
		  return $groupName[0]['Group_name'];
	}
	
	public function addNewSubscriptionsForThisPic(){
		
		if ((isset($_POST['picId'])) && (isset($_POST['subscriptionsAsStr']))){
		  	$originalPicId = $_SESSION['recentUploads'][$_POST['picId']];
			if (strstr($_POST['subscriptionsAsStr'], ',')){
				$subscriptionsAsStrSplitted = explode(',', $_POST['subscriptionsAsStr']);
				foreach($subscriptionsAsStrSplitted as $eachValue){
					if (($eachValue != '') && (!empty($eachValue)) && (isset($eachValue))){
						$this->DbObject->query("INSERT INTO rehand__group_pictures (`GroupId`, `PictureId`) VALUES (".$eachValue.", ".$originalPicId.")");
					}
				}
			}else{
				$this->DbObject->query("INSERT INTO rehand__group_pictures (`GroupId`, `PictureId`) VALUES (".$_POST['subscriptionsAsStr'].", ".$originalPicId.")");
			}
			echo $this->checkThisJustUploadedPictureHaveAnyTags($originalPicId);
		}else{
			echo '0';
		}			
	}
	
	public function checkThisJustUploadedPictureHaveAnyTags($pictureId){

		$pictureId = $this->DbObject->query('SELECT COUNT(*) AS PicCountJustAuploaded FROM tagging__taggeditems WHERE pictureId = ' . $pictureId);
		return ($pictureId[0]['PicCountJustAuploaded'] != 0 ? true : false);
	}
	
	public function removeCurrentSubscriptionsForThisPic(){
	
		if ((isset($_POST['picId'])) && (isset($_POST['removeSubscriptionsAsStr']))){
		  	$originalPicId = $_SESSION['recentUploads'][$_POST['picId']];
			if (strstr($_POST['removeSubscriptionsAsStr'], ',')){
				$removeSubscriptionsAsVarSplitted = explode(',', $_POST['removeSubscriptionsAsStr']);
				foreach($removeSubscriptionsAsVarSplitted as $eachValue){
					if (($eachValue != '') && (!empty($eachValue)) && (isset($eachValue))){
						$this->DbObject->query("DELETE FROM rehand__group_pictures WHERE `GroupId` = ".$eachValue." AND `PictureId` = ".$originalPicId);
					}
				}
			}else{
				$this->DbObject->query("DELETE FROM rehand__group_pictures WHERE `GroupId` = ".$_POST['removeSubscriptionsAsStr']." AND `PictureId` = ".$originalPicId);
			}
		}else{
			echo '0';
		}
	}
	
	function getThePictureIdsWhichUserBelongToTheseGroups($userId){

		$groups = array();
		$result = $this->DbObject->query("SELECT `GroupId` FROM `rehand__group_members` WHERE `memberId` = " . $userId);
		if ($result){
			foreach($result as $eachGroupId) {
				$groups[] = $eachGroupId['GroupId'];
			}
			$sql = "SELECT DISTINCT(`PictureId`) FROM `rehand__group_pictures` WHERE `GroupId` IN (";
			$i = count($groups);
			foreach($groups as $eachGroupId){
				$sql .= ($i != 1) ?  $eachGroupId . "," : $eachGroupId;
				$i--;
			}
			$sql .= ")";
			$secResults = $this->DbObject->query($sql);
			foreach($secResults as $eachRes) {
				$imagesBelongsToOwnedGroups[] = $eachRes['PictureId'];
			}
			// Check each picture has been tagged and not deleted : If these two conditions are approved then return the rest of images
			foreach($imagesBelongsToOwnedGroups as $eachImageId){
				if ($this->checkEachImageHasBeenTaggedAndNotDeleted($eachImageId)){
					$newFilteredImageSet[] = $eachImageId;
				}
			}
		}
		return $newFilteredImageSet;
	}
	
	public function checkEachImageHasBeenTaggedAndNotDeleted($pictureId){
	
		$result = $this->DbObject->query("SELECT `tagged`, `deletedFlag` FROM `tagging__pictures` WHERE `pictureId` = " . $pictureId);
		if (($result[0]['tagged'] == 1) && ($result[0]['deletedFlag'] == 0)) return true; else return false;
	}
	
	public function loadPictureIdsWhichAreRelatedToSearchedTagName($searchParams){

		$relatedPicIds = $this->DbObject->query("SELECT `pictureId` FROM `tagging__taggeditems` WHERE MATCH(`tagName`, `desc`) AGAINST('".trim($searchParams)."')");
		foreach($relatedPicIds as $eachPicId){
			if (!in_array($eachPicId['pictureId'], $modifiedArray)){
				$modifiedArray[] = $eachPicId['pictureId'];
			}
		}
		return $modifiedArray;
	}
	
	public function checkLoggedUserAlreadyJoinedForTheGivenGroupIdIdForTestingPurpose($userId, $groupId){
	
		// Grab this user joined groups and check whther he is in there
		$membersJoinedForThisGroup = $this->DbObject->query("SELECT memberId FROM `rehand__group_members` WHERE `GroupId` = " . $groupId);
		return (in_array($userId, $membersJoinedForThisGroup[0])) ? true : false;
	}
	
	public function getTheMemberCountForThisGroup($groupId){

		$result = $this->DbObject->query("SELECT COUNT(`memberId`) AS numberOfMembers FROM `rehand__group_members` WHERE `GroupId` = ". $groupId);
		return $result[0]['numberOfMembers'];
	}

	public function loadImageAutomatically(){
	
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		if ((isset($_GET['searchQ'])) && ($_GET['searchQ'] != '')){
			$relatedPicIdsForSearchedTagNames = $this->loadPictureIdsWhichAreRelatedToSearchedTagName($_GET['searchQ']);			
		}
		if (!empty($userId)){
			if (!isset($_GET['vievedGroupName'])){
				// First load images which belongs to the groups which this logged user has been joined on
				$imagesBelongsToOwnedGroups = $this->getThePictureIdsWhichUserBelongToTheseGroups($userId);	
				if (empty($imagesBelongsToOwnedGroups)) $noGroupsHaveBeenJoined = true; else $noGroupsHaveBeenJoined = false; 
				/*
				// If the search result not empty then filter the search resulted picture id array with the group related p[icture id array
				if (!empty($relatedPicIdsForSearchedTagNames)){																											
					foreach($relatedPicIdsForSearchedTagNames as $eachPicId){
						if (in_array($eachPicId, $imagesBelongsToOwnedGroups)){
							$imagesBelongsToOwnedGroupsForSerachResult[] = $eachPicId;
						}
					}
					$imagesBelongsToOwnedGroups = $imagesBelongsToOwnedGroupsForSerachResult;
				}
				*/
				if (!isset($_GET['searchQ'])){
					if ($imagesBelongsToOwnedGroups){
						if ((isset($_GET['forGroupsOwned'])) && ($_GET['forGroupsOwned'] == 'true')){
							// Grab the group ids for the logged user
							$concatSql = "SELECT `tagged`, `deletedFlag`, `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 AND `pictureId` IN (";
							$i = count($imagesBelongsToOwnedGroups);
							foreach($imagesBelongsToOwnedGroups as $eachPictureId){
								$concatSql .= ($i != 1) ? $eachPictureId . "," : $eachPictureId;
								$i--;
							}
							$concatSql .= ") ORDER BY `pictureId` DESC";
						}else{
							// Load the rest of the images which are not belongs to the above groups
							$concatSql = "SELECT `tagged`, `deletedFlag`, `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 AND `pictureId` NOT IN (";
							$i = count($imagesBelongsToOwnedGroups);
							foreach($imagesBelongsToOwnedGroups as $eachPictureId){
								$concatSql .= ($i != 1) ? $eachPictureId . "," : $eachPictureId;
								$i--;
							}
							$concatSql .= ") ORDER BY `pictureId` DESC";
						}
					}else{
						if ((isset($_GET['forGroupsOwned'])) && ($_GET['forGroupsOwned'] == 'false')){
							// Grab the group ids for the logged user
							$concatSql = "SELECT `tagged`, `deletedFlag`, `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 ORDER BY `pictureId` DESC";
						}
					}
				}else{
					// This is for the items search against the tag name
					if (!empty($relatedPicIdsForSearchedTagNames)){
						$concatSql = "SELECT `tagged`, `deletedFlag`, `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 AND `pictureId` IN (";
						$i = count($relatedPicIdsForSearchedTagNames);
						foreach($relatedPicIdsForSearchedTagNames as $eachPictureId){
							$concatSql .= ($i != 1) ? $eachPictureId . "," : $eachPictureId;
							$i--;
						}
						$concatSql .= ") ORDER BY `pictureId` DESC";
					}
				}
			}else{
				$concatSql = "SELECT tagging__pictures.tagged, tagging__pictures.deletedFlag, tagging__pictures.pictureId, tagging__pictures.title, tagging__pictures.userId, tagging__pictures.uploadLImgLocation, tagging__pictures.uploadTImgLocation
							  FROM tagging__pictures
							  JOIN rehand__group_pictures
							  ON rehand__group_pictures.PictureId = tagging__pictures.pictureId
							  JOIN rehand__groups
							  ON rehand__groups.GroupId = rehand__group_pictures.GroupId
							  WHERE tagging__pictures.tagged = 1 AND tagging__pictures.deletedFlag = 0 AND tagging__pictures.pictureId AND rehand__groups.Group_name = '".$_GET['vievedGroupName']."'";
			}
			if ($concatSql != ""){
				$pictureDetails = $this->DbObject->query($concatSql);
				foreach($pictureDetails as $eachPicture){
					if (!empty($eachPicture)){ 
						$images[] = $eachPicture;
					}
				}
			}
			$COUNT_OF_PICS = count($images);
			// In here check the user can see rest of the images
			$tmpSql = "SELECT COUNT(*) AS AllPicCount FROM tagging__pictures";
			$allPicCountTemp = $this->DbObject->query($tmpSql);			
			$allPicCount = $allPicCountTemp[0]['AllPicCount'];
			if ($allPicCount > $COUNT_OF_PICS) $canLoadRestImgs = true; else $canLoadRestImgs = false;
		}else{
			if (isset($_GET['searchQ'])){
				// This is to get the count of all pictures
				if (!empty($relatedPicIdsForSearchedTagNames)){
					$concatSql = "SELECT COUNT(*) AS COUNT_OF_PICS FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 AND `pictureId` IN (";
					$i = count($relatedPicIdsForSearchedTagNames);
					foreach($relatedPicIdsForSearchedTagNames as $eachPictureId){
						$concatSql .= ($i != 1) ? $eachPictureId . "," : $eachPictureId;
						$i--;
					}
					$concatSql .= ")";	
					$results = $this->DbObject->query($concatSql);
					$COUNT_OF_PICS = $results[0]['COUNT_OF_PICS'];
					// This is to get the image lot
					$concatSql = "SELECT `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 AND `pictureId` IN (";
					$i = count($relatedPicIdsForSearchedTagNames);
					foreach($relatedPicIdsForSearchedTagNames as $eachPictureId){
						$concatSql .= ($i != 1) ? $eachPictureId . "," : $eachPictureId;
						$i--;
					}
					$concatSql .= ") ORDER BY `pictureId` DESC";
					$pictureDetails = $this->DbObject->query($concatSql);	
				}else{
					$pictureDetails = array();
				}
			}else{
				// This is to get the count of all pictures
				$results = $this->DbObject->query("SELECT COUNT(*) AS COUNT_OF_PICS FROM tagging__pictures WHERE tagged = 1 AND deletedFlag = 0");
				$COUNT_OF_PICS = $results[0]['COUNT_OF_PICS'];
				// This is to get the image lot
				$pictureDetails = $this->DbObject->query("SELECT `pictureId`, `title`, `userId`, `uploadLImgLocation`, `uploadTImgLocation` FROM `tagging__pictures` WHERE `tagged` = 1 AND `deletedFlag` = 0 ORDER BY `pictureId` DESC");	
			}
		}
		// This is for other search results generations
		if (isset($_GET['searchQ'])){
			$_SESSION['searchHasBeenRequested'] = true;
			// This is for groups
			$sqlForGroupNamesSearch = "SELECT * FROM rehand__groups WHERE ACTIVE = 1 AND Group_name LIKE '%".trim($_GET['searchQ'])."%'";
			$resultsForGroups = $this->DbObject->query($sqlForGroupNamesSearch);
			if (!empty($resultsForGroups)){ 
				$i = 0;
				foreach($resultsForGroups as $eachGroup){
					$groupProfilePic = $this->getTheProfilePicture($eachGroup['GroupId']);
					if ($groupProfilePic != ""){
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/www.rehand.com/'.$groupProfilePic)){
							$groupProfilePic = 'public/images/group.jpg';
						}else{
							$groupProfilePic = 'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'.$groupProfilePic;
						}
					}else{
						$groupProfilePic = 'public/images/group.jpg';
					}
					$resultsForGroupsModified[$i]['GroupId'] = $eachGroup['GroupId'];					
					$resultsForGroupsModified[$i]['Group_name'] = $eachGroup['Group_name'];					
					$resultsForGroupsModified[$i]['Group_desc'] = $eachGroup['Group_desc'];					
					$resultsForGroupsModified[$i]['profilePic'] = $groupProfilePic;										
					$resultsForGroupsModified[$i]['noOfMembers'] = $this->getTheMemberCountForThisGroup($eachGroup['GroupId']);
					$resultsForGroupsModified[$i]['noOfItems'] = $this->getTheNumberOfPicturesSubscribedForThisGroup($eachGroup['GroupId']);			
					$resultsForGroupsModified[$i]['joinedStatus'] = $this->checkLoggedUserAlreadyJoinedForTheGivenGroupIdIdForTestingPurpose($userId, $eachGroup['GroupId']);						
					$i++;
				}
				$groups = $resultsForGroupsModified;
			}else{
				$groups = NULL;
			}
		}
		if ($pictureDetails){
			$i = 0;
			foreach($pictureDetails as $temObj){
				if (!empty($temObj)){				
					$photos[$i]['pictureId'] = $temObj['pictureId'];
					$photos[$i]['title'] = $temObj['title'];
					$photos[$i]['owner'] = $temObj['userId'];
					$photos[$i]['pictureRelatedTags'] = $this->loadRelatedTags($temObj['pictureId']);
					$photos[$i]['ownerName'] = $this->loadProfileName($temObj['userId']);		
					$photos[$i]['postCode'] = $this->loadThePostCodeOfPictureOwner($temObj['userId']);		
					$photos[$i]['profileImage'] = $this->loadProfileImage($temObj['userId']);
					$photos[$i]['uploadLImgLocation'] = $temObj['uploadLImgLocation'];						
					$photos[$i]['uploadTImgLocation'] = $temObj['uploadTImgLocation'];
					$photos[$i]['wholeNoOfInterests'] = $this->loadNoOfInterestsForAPicture($temObj['pictureId']);
					$photos[$i]['idhash'] = md5($temObj['pictureId'].HASHKEY.$temObj['userId']);
					$_SESSION['recentLoads'][$photos[$i]['idhash']] = $temObj['pictureId'];
					$photos[$i]['Locality'] = (is_numeric($photos[$i]['postCode'])) ? $this->loadTheSuburbOfPicture($photos[$i]['postCode']) : $photos[$i]['postCode'];
					$photos[$i]['subscribedGroups'] = $this->getOwnedGroupIdForTheGivenPicId($temObj['pictureId']);
					$i++;
				}
			}
		}
		if ($COUNT_OF_PICS <= ($_GET['start'] + $_GET['limit'])) $endOfImgLot = true; else $endOfImgLot = false; 
		echo json_encode(array(
								'nextStart' => ($_GET['start'] + $_GET['limit']), 
								'imageSet' => $photos, 
								'groupSet' => $groups,
								'endOfImgLot' => $endOfImgLot, 
								'canLoadRestImgs' => $canLoadRestImgs, 
								'noGroupsHaveBeenJoined' => $noGroupsHaveBeenJoined
								)
						);
	}
	
	public function getUserProfilePicture($userId) {
	
		$this->DbObject->resetWhere();
		$this->DbObject->where("userId", $userId);		
		return $this->DbObject->get("rehand__users", array('profile_image_name', 'profile_thumb_image_name'));					
	}
	
	public function loadTheSuburbOfPicture($postCode){
		
		$result = $this->DbObject->query("SELECT Locality FROM rehand__postalCodes WHERE Pcode = '".$postCode . "'");
		$suburbName = $result[0]['Locality'];
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
	
	public function loadAllGroupsForLoggedUser($UserId){
	
		$this->DbObject->resetWhere();
		$this->DbObject->where('memberId', 1);	
		$this->DbObject->where('Membership_active', 1);	
		$results = $this->DbObject->get("rehand__group_members", array('GroupId'));	
		foreach($results as $eachRes){
			$groupIds[] = $eachRes['GroupId'];
		}
		return $groupIds;
	}
	
	public function getOwnedGroupIdForTheGivenPicId($picId){

		$this->DbObject->resetWhere();
		$this->DbObject->where('PictureId', $picId);	
		$results = $this->DbObject->get("rehand__group_pictures", array('GroupId'));	
		if ($results){
			foreach($results as $eachRes){
				$subscribedGroups[] = $eachRes['GroupId'];
			}
		}
		return $subscribedGroups;
	}
	
	public function getTheOriginalNotifierName(){
	
		if (isset($_GET['orgNotifiId'])){
			$originalNotificationId = base64_decode($_GET['orgNotifiId']);
			$results = $this->DbObject->query('SELECT rehand__users.firstName, rehand__users.lastName FROM rehand__users JOIN tagging__notification ON rehand__users.userId = tagging__notification.fromUserId WHERE tagging__notification.notificationId = ' . trim($originalNotificationId));
			echo json_encode(array('replyHtml' => 'Respond to ' . $results[0]['firstName']));
		}
	}
	
	public function checkThisUserHasJoinedToAnyGroups(){
	
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];	
		$results = $this->DbObject->query('SELECT COUNT(memberId) AS NoOfOccursForThisUser FROM rehand__group_members WHERE memberId = ' . $userId);
		echo (($results[0]['NoOfOccursForThisUser'] > 0) ? '1' : '0');
	}
	
	public function shareThisLinkViaEmailToAFreind(){
	
		if (
			(isset($_POST['friendName'])) &&
			(isset($_POST['yourEmail'])) &&
			(isset($_POST['yourName'])) &&
			(isset($_POST['friendEmail'])) &&
			(isset($_POST['messageFromYou'])) &&
			(isset($_POST['shareLinkId']))
		   ){
			
			$mailsubject = "Sharing rehand link";
			$originalMsg = str_replace("Dear " . $_POST['friendName'] . ",", "Dear " . $_POST['friendName'] . ",<br /><br />", $_POST['messageFromYou']);
			$originalMsg = str_replace("Best Regards", "", $originalMsg);
			$originalMsg .= "<br /><br />".$_POST['shareLinkId'];
			$originalMsg .= "<br /><br />Best Regards,<br />" . $_POST['yourName'] . ".";
			$mailmessage = $originalMsg;
			$from   = $_POST['yourEmail'];
			$mailto = $_POST['friendEmail'];
				
			echo ($this->sendMailNotification($mailto, $mailsubject, $mailmessage, $from) ? '1' : '0');
		}
	}
	
	public function reportMisUse(){
	
		if (isset($_POST['adminMessage'])){
			$mailsubject = "Report misuse of rehand items";
			$mailmessage = "This item is misusing the rehand<br /><br />";
			$mailmessage .= trim($_POST['urlForImg']) . "<br /><br />";			
			$mailmessage .= "Reason: " . trim($_POST['adminMessage']);
			if (isset($_SESSION['currentUser'])){ 
				$from = $_SESSION['currentUser']['email'];
			}else if (isset($_SESSION['fbUser'])){ 
				$from = $_SESSION['fbUser']['email'];	
			}else{ 
				$from = trim($_POST['yourEmail']);
			}
			$mailto  = 'aloka@socialseedmedia.com.au, uditha@socialseedmedia.com.au, heroly@socialseedmedia.com.au, ruchira@socialseedmedia.com.au, afzal@socialseedmedia.com.au, janaka@socialseedmedia.com.au';
				
			echo ($this->sendMailNotification($mailto, $mailsubject, $mailmessage, $from) ? '1' : '0');
		}
	}
}