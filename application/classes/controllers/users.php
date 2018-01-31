<?php
class UsersController {

	public $commFunctions = NULL;
	public $currentSession = NULL;
	public static $model = NULL;	
	public static $groupInfo = array();
	
	public function __construct() {
	
		global $CommFuncs;
		$this->commFunctions = $CommFuncs;
		require CONTROLLER_PATH.'sessions'.EXT;		
	}
	
	static public function getModelInstance() {
	
		if (self::$model == NULL) self::$model = new UsersModel;
		return self::$model;	
	}

	public function ActionFblogin() {

		$app_id = FACEBOOK_APP_ID_DEV;
		$app_secret = FACEBOOK_SECRET_DEV;
		$my_url = WEB_PATH."users/confirmfblogin/";	
		if (empty($code)) {
			$dialog_url = "http://www.facebook.com/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)."&scope=email";	
			echo("<script>top.location.href='" . $dialog_url . "'</script>");
		}		
	}

	public function clean_url($text){
	
		$text = strtolower($text);
		$code_entities_match = array(' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','_','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','.','/','*','+','~','`','=');
		$code_entities_replace = array('-','-','','','','','','','','','','','','','','','','','','','','','','','','');
		$text = str_replace($code_entities_match, $code_entities_replace, $text);
		return $text;
	} 

	public function ActionConfirmfbLogin() {	

		$this->currentSession = SessionController::getInstance();		
		$this->currentSession->createRelateSession($_REQUEST["code"], 'access_token');		
		if ($_GET['error']){
		    header("Location: ".WEB_PATH."users/fblogin/");
			exit;
		}
		$my_url = WEB_PATH."users/confirmfblogin/";			
		$token_url = "https://graph.facebook.com/oauth/access_token?client_id=" . FACEBOOK_APP_ID_DEV . "&redirect_uri=" . urlencode($my_url) . "&client_secret=" . FACEBOOK_SECRET_DEV . "&code=" . $_REQUEST["code"];

		// request access token
		// use curl and not file_get_contents()
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $token_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$access_token = curl_exec($ch);
		curl_close($ch);
		
		$graph_url = "https://graph.facebook.com/me?" . $access_token;		
	
		// request user data using the access token
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL, $graph_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$temp_user = curl_exec($ch);
		curl_close($ch);
		
		// decode the json array to get user data
		$loggedFbUDetails = self::getModelInstance()->confirmFbLogin(json_decode($temp_user));	
		
		if (($loggedFbUDetails[0]['email'] != '') || ($loggedFbUDetails[0]['contactEmail'] != '')){
			$tempVal = json_decode($temp_user)->gender;
			switch($tempVal){
				case "male": $gender = "M"; break;
				case "female": $gender = "F"; break;				
			}
			$email = ($loggedFbUDetails[0]['email'] != '') ? $loggedFbUDetails[0]['email'] : $loggedFbUDetails[0]['contactEmail'];
			$loggedFbUDetails[0]['profileImgUrlLarge'] = "http://graph.facebook.com/".json_decode($temp_user)->id."/picture?type=large";
			$loggedFbUDetails[0]['profileImgUrlThumb'] = "http://graph.facebook.com/".json_decode($temp_user)->id."/picture";
			self::getModelInstance()->updateFBDetails($email, array('fbUserID' => json_decode($temp_user)->id, 'gender' => $gender));
			self::getModelInstance()->LogLastLogin($loggedFbUDetails[0]['email'], array('lastLogin' => date("Y-m-d H:i:s")));			
			$this->currentSession = SessionController::getInstance();
			$modifiedFbLoggedDetails = $loggedFbUDetails[0];			
			$modifiedFbLoggedDetails['fbUserID'] = json_decode($temp_user)->id;
			$modifiedFbLoggedDetails['loggedViaFB'] = true;
			$this->currentSession->createRelateSession($modifiedFbLoggedDetails, 'fbUser');		
			$this->keepUserPerspectiveRecord();
			// Check the logged user's contact email value is null : if null update it with the logged fb email
			/*
			if ($loggedFbUDetails[0]['contactEmail'] == ''){
				self::getModelInstance()->updateContactEmailForFBUsers($modifiedFbLoggedDetails['fbUserID'], json_decode($temp_user)->email);
			}
			*/
			echo "<script type='text/javascript' language='javascript'>location.href='".'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/'."';</script>";
		}else{
			if (self::getModelInstance()->RegisterForFbUser(json_decode($temp_user))){
				$loggedFbUDetails = self::getModelInstance()->confirmFbLogin(json_decode($temp_user));	
				$loggedFbUDetails[0]['profileImgUrlLarge'] = "http://graph.facebook.com/".json_decode($temp_user)->id."/picture?type=large";
				$loggedFbUDetails[0]['profileImgUrlThumb'] = "http://graph.facebook.com/".json_decode($temp_user)->id."/picture";
				self::getModelInstance()->LogLastLogin(json_decode($temp_user)->email, array('lastLogin' => date("Y-m-d H:i:s")));			
				$this->currentSession = SessionController::getInstance();
				$modifiedFbLoggedDetails = $loggedFbUDetails[0];			
				$modifiedFbLoggedDetails['fbUserID'] = json_decode($temp_user)->id;
				$modifiedFbLoggedDetails['loggedViaFB'] = true;
				$this->currentSession->createRelateSession($modifiedFbLoggedDetails, 'fbUser');		
				$this->keepUserPerspectiveRecord();
				echo "<script type='text/javascript' language='javascript'>location.href='".'http://'.$_SERVER['HTTP_HOST'].'/www.rehand.com/users/profile/'."';</script>";
			}else{
				$this->commFunctions->redirect("users/profile/");
			}
		}
	}

   	public function ActionLogin() {	   	

		if ("POST" == $_SERVER['REQUEST_METHOD']){
			if (isset($_POST['directLogin'])){
				$errors = $this->commFunctions->errorCheckingFields($_POST['directLogin']);				
				if (($errors['errorStatus'] == 'false') || (!$errors['errorStatus'])){
					$ModifiedReqsUserParams = array();
					foreach($_POST['directLogin'] as $eachField => $eachVal){						
						if ($eachField == 'password') $ModifiedReqsUserParams['password'] = md5($eachVal);
						else $ModifiedReqsUserParams[$eachField] = $eachVal;
					}
					$loggedUsersParams = self::getModelInstance()->Login($ModifiedReqsUserParams);	

					if (!empty($loggedUsersParams)){
						$_SESSION['firstLogin'] = $loggedUsersParams[0]['firstLogin'];

						if (
						   ($loggedUsersParams[0]['email'] == $ModifiedReqsUserParams['email']) && 
						   ($loggedUsersParams[0]['password'] == $ModifiedReqsUserParams['password'])
						   ){
							  $previousDeactivateStatus = self::getModelInstance()->checkUserPreviouslyDeactivatedOrNot($loggedUsersParams[0]['userId']);							
							  if ($previousDeactivateStatus[0]['deactivateStauts']){
							  		self::getModelInstance()->rollBackDeactivateStatus($loggedUsersParams[0]['userId']);
									$loggedUsersParams[0]['welcomeMsgForReactivation'] = "Welcome back to rehand";
							  }
							  $this->currentSession = SessionController::getInstance();
							  $this->currentSession->createRelateSession($loggedUsersParams[0], 'currentUser');
							  self::getModelInstance()->LogLastLogin($loggedUsersParams[0]['email'], array('lastLogin' => date("Y-m-d H:i:s")));
					
							  // If the user has checked the keep me logged in tick box
							  if (isset($_POST['keep_me_logged_in'])){
								 $expire = time() + 1728000; // Expire in 20 days
								 $cookie_pass = md5($loggedUsersParams[0]['password']);
									
								 setcookie('username', $loggedUsersParams[0]['email'], $expire);
								 setcookie('password', $cookie_pass, $expire);
							  }  
					
							  $this->keepUserPerspectiveRecord();
							  $this->commFunctions->redirect();
							}else{	
								$this->commFunctions->redirectAfterSomeTime();										
							}	
					}else{
						$this->commFunctions->redirectAfterSomeTime();										
					}
				}else{				
					$this->commFunctions->redirectAfterSomeTime();						
					return $errors['errorFields'];													
				}				
			}else{
				$this->commFunctions->redirectAfterSomeTime();						
			}
		}else{
			return "public/images/defaulttiny.gif";
		}
   	}	

	public function ActionNewpassword() {
	
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){	
			if (isset($_SESSION['currentUser'])){ 
				$userId = $_SESSION['currentUser']['userId'];
			}else if (isset($_SESSION['fbUser'])){ 
				$userId = $_SESSION['fbUser']['userId'];
			}
			$profileImage = self::getModelInstance()->getProfilePicture($userId);
			return array('profileImage' => "uploaded/profiles/".$profileImage[0]['profile_thumb_image_name']);
		}else{
			$this->commFunctions->redirect();									
		}
	}

	public function ActionForgotpass() {	
	
		global $stringLts;
		if ("POST" == $_SERVER['REQUEST_METHOD']){		
			if (isset($_POST['forgotUser'])){
				if (CommonFunctions::checkEmailAddressValid($_POST['forgotUser']['email'])){
					if (self::getModelInstance()->checkUserIdAvalable($_POST['forgotUser']['email'])){
						$userDetails = self::getModelInstance()->getUserDetails($_POST['forgotUser']['email']);
						$to      = $_POST['forgotUser']['email'];
						$subject = $stringLts->literalsArray['msg7'];
						$message = 'Hi '.$userDetails[0]['firstName'].' '.$stringLts->literalsArray['msg8'];
						$headers = $stringLts->literalsArray['msg9'];				
						mail($to, $subject, $message, $headers);
					}else{
						$this->commFunctions->redirectAfterSomeTime();									
						return $stringLts->literalsArray['msg2'];												
					}	
				}else{
					$this->commFunctions->redirectAfterSomeTime();									
					return $stringLts->literalsArray['msg2'];						
				}	
			}
		}
	}
	
	public function keepUserPerspectiveRecord() {
		
		if (isset($_SESSION['currentUser'])) $userId = $_SESSION['currentUser']['userId'];
		else if (isset($_SESSION['fbUser'])) $userId = $_SESSION['fbUser']['userId'];			
		//$usersProfileSeller = self::getModelInstance()->checkUserHasSellerPerspective($userId);
		//$usersProfileBuyer = self::getModelInstance()->checkUserHasBuyerPerspective($userId);
		if ($usersProfileSeller[0]['count'] > 0){
			if (isset($_SESSION['currentUser'])){ 
				$_SESSION['currentUser']['sellerPerspective'] = true;
			}else if (isset($_SESSION['fbUser'])){ 
				$_SESSION['fbUser']['sellerPerspective'] = true;			
			}
		}
		if ($usersProfileBuyer[0]['count'] > 0){
			if (isset($_SESSION['currentUser'])){ 
				$_SESSION['currentUser']['buyerPerspective'] = true;
			}else if (isset($_SESSION['fbUser'])){ 
				$_SESSION['fbUser']['buyerPerspective'] = true;			
			}
		}
	}
	
	public function ActionProfile() {
	
		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{
			if ("POST" != $_SERVER['REQUEST_METHOD']){
				//if (isset($_SESSION['error_in_profileimg_maker'])) unset($_SESSION['error_in_profileimg_maker']);
				
				if (isset($_SESSION['currentUser'])){ 
					$userId = $_SESSION['currentUser']['userId'];
				}else if (isset($_SESSION['fbUser'])){ 
					$userId = $_SESSION['fbUser']['userId'];
				}
				if (empty($_SERVER['QUERY_STRING'])){
					$userProfileInformations = self::getModelInstance()->getUserProfileInformations($userId);
				}else{
					if ((isset($_GET['view'])) && ($_GET['view'] == 'profpic')){
						$userProfileInformations = self::getModelInstance()->getProfilePicture($userId);
						if ($userProfileInformations[0]['profile_image_name'] != ""){
							$userProfileInformations[0]['largeProfileImage'] = "uploaded/profiles/".$userProfileInformations[0]['profile_image_name']; 
							$userProfileInformations[0]['smallProfileImage'] = "uploaded/profiles/".$userProfileInformations[0]['profile_thumb_image_name']; 							
						}else{
							$userProfileInformations[0]['smallProfileImage'] = "public/images/defaulttiny.gif"; 
							$userProfileInformations[0]['largeProfileImage'] = "public/images/defaultlarge.gif"; 							
						}
					}else if ((isset($_GET['view'])) && ($_GET['view'] == 'profImgDelete')){
						$large_image_location = UPLOADED_PROFILE_IMAGES_PATH."resize_".$_GET['t'];
						$thumb_image_location = UPLOADED_PROFILE_IMAGES_PATH."thumbnail_".$_GET['t'];
						if ((file_exists($large_image_location)) && (file_exists($thumb_image_location))) {
							unlink($large_image_location);
							unlink($thumb_image_location);
							self::getModelInstance()->deleteProfileImage($userId);
						}
						$this->commFunctions->redirect();			
					}
				}
			}else{
				if (isset($_POST['profileUpdattion'])){
					if (isset($_SESSION['currentUser'])){ 
						$userId = $_SESSION['currentUser']['userId'];
						$contactEmail = $email = $_SESSION['currentUser']['email'];
					}else if (isset($_SESSION['fbUser'])){ 
						$userId = $_SESSION['fbUser']['userId'];
						$contactEmail = $email = $_SESSION['fbUser']['email'];						
					}
					$userProfileInformations = self::getModelInstance()->getUserProfileInformations($userId);
					if (($_POST['profileUpdattion']['name'] != "") || ($_POST['profileUpdattion']['email'] != "")){
						if (strstr($_POST['profileUpdattion']['name'], " ")){
							$splittedNames = explode(" ", $_POST['profileUpdattion']['name']);
							$firstName = $splittedNames[0];
							$lastName = $splittedNames[1];
						}else{
							$firstName = $_POST['profileUpdattion']['name'];
							$lastName = "";
						}
						if (
							(!empty($_POST['profileUpdattion']['day'])) && 
							(!empty($_POST['profileUpdattion']['month'])) && 
							(!empty($_POST['profileUpdattion']['year'])) 
						   ){
							$dd = $_POST['profileUpdattion']['day'];
							$mm = $_POST['profileUpdattion']['month'];
							$yy = $_POST['profileUpdattion']['year'];
							if (!checkdate($mm, $dd, $yy)){
								$dateOfBirth = $userProfileInformations['profileOtherDetails']['dateOfBirth'];			
							}else{
								$dateOfBirth = $yy."-".$mm."-".$dd;
							}
							$profileUpdation['dateOfBirth'] = $dateOfBirth;
						}
						foreach($_POST['profileUpdattion'] as $key => $value){
							if (($key != "name") && ($key != "uid") && ($key != "dateOfBirth")){
								$profileUpdation[$key] = $value;
							}
						}
						$profileUpdation['firstName'] = $firstName;
						$profileUpdation['lastName'] = $lastName;
						$profileUpdation['email'] = $email;
						$profileUpdation['contactEmail'] = $contactEmail;
						$profileUpdation['firstLogin'] = 0;

						self::getModelInstance()->updateProfileOtherInformation($userId, $profileUpdation);	
						$this->currentSession = SessionController::getInstance();
						$this->currentSession->createRelateSession(true, 'currentUserProfileUpdated');
						
						if (isset($_POST['firstLogin'])){
							unset($_SESSION['firstLogin']);
							$this->commFunctions->redirect("users/groups/?continue=1");
						}else{
							$this->commFunctions->redirect("users/profile/");		
						}
					}
				}
			}
		}	
		return $userProfileInformations;
	}

   	public function ActionLogout() {	   			

		$this->currentSession = SessionController::getInstance();						
		$tempSessionsAll = $this->currentSession->getAllSessionsAvailable();
		if (empty($tempSessionsAll)){
			$this->commFunctions->redirectAfterSomeTime();								
			return array('errorStatus' => 'There is no user currently logged with Rehand !!!');			
		}else{
			if ($this->currentSession->getCurrentSession('currentUser') != NULL){
				$this->currentSession->unsetSession(array('currentUser' => $this->currentSession->getCurrentSession('currentUser')));	
				$this->currentSession->unsetSession(array('commonUser' => $this->currentSession->getCurrentSession('commonUser')));
			}elseif ($this->currentSession->getCurrentSession('fbUser') != NULL){				
				$fbLogout = "<div id='fb-root'></div>		
				<script src='http://connect.facebook.net/en_US/all.js'></script>
				<script>
					FB.init({ apiKey: '";$fbLogout .= FACEBOOK_APP_ID_DEV . "' });		
					FB.getLoginStatus(handleSessionResponse);
					function handleSessionResponse(response) {
						if (!response.session) {
							window.location = ";$fbLogout .= "'".WEB_PATH."';
							return;
						}		
					FB.logout(handleSessionResponse);
				}
				</script>";echo $fbLogout;		
				$this->currentSession->unsetSession(array('fbUser' => $this->currentSession->getCurrentSession('fbUser')));				
			}
			$this->commFunctions->redirect();			
		}	
   	}	
	
	public function ActionActivation() {}
	
   	public function ActionRegister() {	   	

		global $stringLts;
		if ((!isset($_SESSION['currentUser'])) && (!isset($_SESSION['fbUser']))){		
			if ("POST" == $_SERVER['REQUEST_METHOD']){
				if (isset($_POST['newUser'])){
					$notErrorInEmail = $this->commFunctions->checkEmailAddressValid($_POST['newUser']['email']);
					$notErrorInUserid = (self::getModelInstance()->checkUserIdAvalable($_POST['newUser']['email'])) ? false : true;				
					if (($notErrorInEmail) && ($notErrorInUserid)) {			
						$newUserParam = array();
						foreach($_POST['newUser'] as $eachParam => $eachVal){ 
							if ($eachParam == "name"){ 
								$splittedName = explode(" ", $eachVal);
								$newUserParam["firstName"] = $splittedName[0];
								$newUserParam["lastName"] = $splittedName[1];
							}else{
								$newUserParam[$eachParam] = $eachVal;
							}
						}
						$newUserParam['createdAt'] = date('Y-m-d');
						$newUserParam['password'] = md5($_POST['newUser']['password']);					
						$newUserParam['country'] = "Australia";
						if (self::getModelInstance()->Register($newUserParam)){	
							$hashedemail = md5("mynewemail").base64_encode(trim($newUserParam["email"]));
							$this->sendRegistrationActivationMail($newUserParam["email"], $newUserParam["firstName"]. " " . $newUserParam["lastName"], $hashedemail);
							$this->commFunctions->redirect("users/activation/");	
						}else{
							$this->commFunctions->redirect();			
						}
					}else{				
						if (!$notErrorInEmail) $errors = $stringLts->literalsArray['msg2'];
						if (!$notErrorInUserid) $errors = $stringLts->literalsArray['msg4'];										
					}
				}else{
					if (isset($_REQUEST['signed_request'])){
						$response = CommonFunctions::parse_signed_request($_REQUEST['signed_request'], FACEBOOK_SECRET_DEV);						
						$retrievedFBDetails['email'] = $response['registration']['email'];																		
						$retrievedFBDetails['fbUserID'] = $response['user_id'];				 										
						$names = explode(' ', $response['registration']['name']);						
						$retrievedFBDetails['firstName'] = $names[0];
						$retrievedFBDetails['lastName'] = $names[1];						
						$retrievedFBDetails['postCode'] = $response['registration']['zip'];						
						$retrievedFBDetails['country'] = "Australia";																
						$retrievedFBDetails['companyName'] = $response['registration']['employer'];						
						$retrievedFBDetails['gender'] = $response['registration']['gender'];											
						$tempBDay = explode('/', $response['registration']['birthday']);
						$retrievedFBDetails['dateOfBirth']	= $tempBDay[2].'-'.$tempBDay[1].'-'.$tempBDay[0];				 
						$retrievedFBDetails['createdAt'] = date("Y-m-d H:i:s");
						$result = self::getModelInstance()->checkUserIdAvalable($retrievedFBDetails['email']);
						if (empty($result)){
							self::getModelInstance()->Register($retrievedFBDetails);	
							self::getModelInstance()->LogLastLogin($retrievedFBDetails['email'], array('lastLogin' => date("Y-m-d-H:i:s")));
							$this->commFunctions->redirect("users/profile/");	
						}else{
							$this->commFunctions->redirectAfterSomeTime();									
							return $stringLts->literalsArray['msg5'];	
						}
					}else{
						$this->commFunctions->redirectAfterSomeTime();									
						return $stringLts->literalsArray['msg6'];	
					}	
				}
			}else{
				$this->commFunctions->redirect();			
			}
		}else{
			$this->commFunctions->redirectAfterSomeTime();
		}
   }
   
   public function ActionUploader() {

		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{
			if ((!isset($_GET['view'])) && ($_GET['view'] != 'uploaded')){
				if (self::getModelInstance()->IndexUploader()){
					$this->commFunctions->redirect('index/uploader/?view=uploaded');
				}
			}else{
				if (isset($_SESSION['currentUser'])){ 
					$userId = $_SESSION['currentUser']['userId'];
				}else if (isset($_SESSION['fbUser'])){ 
					$userId = $_SESSION['fbUser']['userId'];
				}
				if ((isset($_GET['view'])) && ($_GET['view'] == 'uploaded')){
					$uploaderPanelInfo['taggedImages'] = self::getModelInstance()->loadImagesForTheUploadPanel($userId, true);
					$uploaderPanelInfo['untaggedImages'] = self::getModelInstance()->loadImagesForTheUploadPanel($userId, false);
				}
				$uploaderPanelInfo['profileImage'] = self::getModelInstance()->loadProfileImage($userId);
				return $uploaderPanelInfo;
			}
		}		
   }
   
   public function ActionUploader_ie() {
   
		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{
			if ((!isset($_GET['view'])) && ($_GET['view'] != 'uploaded')){
				if (self::getModelInstance()->IndexUploader()){
					$this->commFunctions->redirect('index/uploader/?view=uploaded');
				}
			}else{
				if (isset($_SESSION['currentUser'])){ 
					$userId = $_SESSION['currentUser']['userId'];
				}else if (isset($_SESSION['fbUser'])){ 
					$userId = $_SESSION['fbUser']['userId'];
				}
				if ((isset($_GET['view'])) && ($_GET['view'] == 'uploaded')){
					$uploaderPanelInfo['taggedImages'] = self::getModelInstance()->loadImagesForTheUploadPanel($userId, true);
					$uploaderPanelInfo['untaggedImages'] = self::getModelInstance()->loadImagesForTheUploadPanel($userId, false);
				}
				if ((isset($_GET['view'])) && ($_GET['view'] == 'add')){
					if ((isset($_FILES['pictureUpload'])) && ($_FILES['pictureUpload']['error'] == 0)){
						$newPicId = self::getModelInstance()->getMaximumPicNoNextToUpload();
						// This is to rename thew small image
						$pictureTUploadPath = $_SERVER['DOCUMENT_ROOT'].'www.rehand.com/uploaded/taggings/'.$userId.'/thumbnails/'.basename($_FILES['pictureUpload']['name']);
						$imageNameSmall = end(explode('/', $pictureTUploadPath));
						$extention = end(explode('.', $imageNameSmall));
						$newSmalImageName = str_replace($_SERVER['DOCUMENT_ROOT'].'www.rehand.com/', '', str_replace($imageNameSmall, "pic_".($newPicId + 1).".".$extention, $pictureTUploadPath));
						// This is to rename the large image
						$pictureLUploadPath = $_SERVER['DOCUMENT_ROOT'].'www.rehand.com/uploaded/taggings/'.$userId.'/'.basename($_FILES['pictureUpload']['name']);
						$imageNameLarge = end(explode('/', $pictureLUploadPath));
						$extention = end(explode('.', $imageNameLarge));
						$newLargeImageName = str_replace($_SERVER['DOCUMENT_ROOT'].'www.rehand.com/', '', str_replace($imageNameLarge, "pic_".($newPicId + 1).".".$extention, $pictureLUploadPath));
						// Insert new image
						if (self::getModelInstance()->uploadImagesOnlyForIeUsers(($newPicId + 1), 'pic_'.($newPicId + 1), $userId, $newLargeImageName, $newSmalImageName)){
							if (move_uploaded_file($_FILES['pictureUpload']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'www.rehand.com/'.$newLargeImageName)){
								$this->resizeForIeUploadedImages(300, $_SERVER['DOCUMENT_ROOT'].'www.rehand.com/'.$newSmalImageName, 
										$_SERVER['DOCUMENT_ROOT'].'www.rehand.com/'.$newLargeImageName);
							}
							$this->commFunctions->redirect("users/uploader/?view=uploaded");							
						}else{
							$this->commFunctions->redirect();
						}	
					}
				}
				$uploaderPanelInfo['profileImage'] = self::getModelInstance()->loadProfileImage($userId);
				return $uploaderPanelInfo;
			}
		}		   
   }
   
   public function resizeForIeUploadedImages($newWidth, $targetFile, $sourceFile) {
   
		if (empty($newWidth) || empty($targetFile)) {
			return false;
		}
		$src = imagecreatefromjpeg($sourceFile);
		list($width, $height) = getimagesize($sourceFile);
		$newHeight = ($height / $width) * $newWidth;
		$tmp = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		if (file_exists($targetFile)) {
			unlink($targetFile);
		}
		imagejpeg($tmp, $targetFile, 85); // 85 is my choice, make it between 0 – 100 for output image quality with 100 being the most luxurious
   }
   
   public function ActionResetpass($email) {

   		$emailHashedCleaned = array();	
   		$emailHashed = explode("/", $_SERVER['REQUEST_URI']);
   		foreach($emailHashed as $key => $val){
			if ((isset($key)) && (!empty($val))){
				$emailHashedCleaned[$key] = $val;
			}
		}
		$hashedEmail = str_replace(md5("myemail"), "", end($emailHashedCleaned));
		// Check this email exist in the system
		$exists = self::getModelInstance()->checkEmailExistsWithTheSystem(base64_decode($hashedEmail));
		if (empty($exists)){
			$this->commFunctions->redirect();									
		}else{
			$this->currentSession = SessionController::getInstance();
			$this->currentSession->unsetSession('forgotton_password_rel_email');
			$this->currentSession->createRelateSession(base64_decode($hashedEmail), 'forgotton_password_rel_email');
		}
   } 
   
   public function ActionActivate() {
   
   		$emailHashedCleaned = array();	
   		$emailHashed = explode("/", $_SERVER['REQUEST_URI']);
   		foreach($emailHashed as $key => $val){
			if ((isset($key)) && (!empty($val))){
				$emailHashedCleaned[$key] = $val;
			}
		}
		$hashedEmail = str_replace(md5("mynewemail"), "", end($emailHashedCleaned));
		// Check this email exist in the system
		$exists = self::getModelInstance()->checkEmailExistsWithTheSystem(base64_decode($hashedEmail));
		if (empty($exists)){
			$this->commFunctions->redirect();									
		}else{
			self::getModelInstance()->activatNewlyAddedUser(base64_decode($hashedEmail));
			$loggedUsersParams = self::getModelInstance()->getLoginDetailsForloginAfterActivation(base64_decode($hashedEmail));					
			if (!empty($loggedUsersParams)){					
				$this->currentSession = SessionController::getInstance();
				$this->currentSession->createRelateSession($loggedUsersParams[0], 'currentUser');
				$_SESSION['firstLogin'] = $loggedUsersParams[0]['firstLogin'];
				self::getModelInstance()->LogLastLogin($loggedUsersParams[0]['email'], array('lastLogin' => date("Y-m-d-H:i:s")));
				$this->commFunctions->redirect("users/profile/?view=activated");										
			}else{
				$this->commFunctions->redirect();										
			}
		}
   }
   
   public function ActionNotifications() {
   
	  $this->currentSession = SessionController::getInstance();			
	  if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
		  $this->commFunctions->redirect();					
	  }else{
		  $pagination_obj = new Pagination();
		  if (isset($_SESSION['currentUser'])){ 
			  $userId = $_SESSION['currentUser']['userId'];
		  }else if (isset($_SESSION['fbUser'])){ 
			  $userId = $_SESSION['fbUser']['userId'];
		  }
		  $allNotifications = self::getModelInstance()->loadAllNotifications($userId);
		  $i = 0;
		  foreach($allNotifications as $eachNotification){
		  	  $AllNotificationModified[$i]['notificationId'] = $eachNotification['notificationId'];			 
		  	  $AllNotificationModified[$i]['fromUserId'] = $eachNotification['fromUserId'];			 
		  	  $AllNotificationModified[$i]['toUserId'] = $eachNotification['toUserId'];			 	
		  	  $AllNotificationModified[$i]['notificationLinkId'] = $eachNotification['notificationLinkId'];
		  	  $AllNotificationModified[$i]['notificationType'] = $eachNotification['notificationType'];			 
		  	  $AllNotificationModified[$i]['notificationText'] = $eachNotification['notificationText'];			 
		  	  $AllNotificationModified[$i]['notificationDate'] = $eachNotification['notificationDate'];			 			  
		  	  $AllNotificationModified[$i]['notificationTime'] = $eachNotification['notificationTime'];			 			  
		  	  $AllNotificationModified[$i]['notificationViewed'] = $eachNotification['notificationViewed'];			 			  			  
			  $AllNotificationModified[$i]['notifiyedPersonProfPic'] = self::getModelInstance()->getProfilePicture($eachNotification['fromUserId']);
			  $AllNotificationModified[$i]['encryptedId'] = base64_encode($eachNotification['notificationId']);
			  $i++;
		  }
		  $profileImage = self::getModelInstance()->loadProfileImage($userId);
		  $num_of_row = self::getModelInstance()->loadAllNotificationsCount($userId);
		  $pagination = $pagination_obj->generate_pagination($num_of_row, $_SERVER['REQUEST_URI'], NO_OF_RECORDS_PER_PAGE);				
		  $tot_page_count = ceil($num_of_row/NO_OF_RECORDS_PER_PAGE);				
		  // If no records found or no pages found
		  $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		  if ((($page > $tot_page_count) || ($page == 0)) && ($_GET['page'] != 1)){
			  $invalidPage = true;	
		  }else if ((($page > $tot_page_count) || ($page == 0)) && ($_GET['page'] == 1)){
		  	  $invalidPage = "No_notifications";	
		  }
		  return array('profileImage' => $profileImage, 'allNotifications' => $AllNotificationModified, 'invalidPage' => $invalidPage, 'pagination' => $pagination);
	   }
	}
	
	public function sendRegistrationActivationMail($toEmail, $name, $hashedemail){
	
		//mail to above user with his login details
		$to      = $toEmail;
		$subject = "Activate your Rehand Account";
		$message = "Hi $name,<br /><br />";
		$message .= "Thank you for registering with www.rehand.com. Please click on the following link or copy and paste it in your favourite browser to activate your rehand account.<br />";
		$message .= "<a href='".WEB_PATH."users/activate/".$hashedemail."/'>" . WEB_PATH."users/activate/".$hashedemail . '/</a><br /><br />'; 
		$message .= "We look forward to see you rehanding often. :)<br /><br />";
		$message .= "Warm regards,<br /><br />";
		$message .= "Rehand Team";
		$this->commFunctions->sendMailNotification($to, $subject, $message, "Rehand - Account activation");
	}
	
	public function ActionViewgroup(){
	
		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{	
			if (isset($_SESSION['currentUser'])){ 
				$userId = $_SESSION['currentUser']['userId'];
			}else if (isset($_SESSION['fbUser'])){ 
				$userId = $_SESSION['fbUser']['userId'];
			}
			// get the all images that subscribed to this group
			return array(
						'profileImage' => self::getModelInstance()->loadProfileImage($userId), 
						'picturesOwnedForThisGroup' => self::getModelInstance()->getAllPicturesWhichsSubscribedToThisGroup($userId)
						);
		}	
	}
	
	public function ActionGroups() {

		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{	
			if (isset($_SESSION['currentUser'])){ 
				$userId = $_SESSION['currentUser']['userId'];
			}else if (isset($_SESSION['fbUser'])){ 
				$userId = $_SESSION['fbUser']['userId'];
			}
			if (isset($_POST['Usergroupssubmit'])){
				if (empty($_POST['Usergroups']['groupname'])){
					$groupErrors['errorsInForm'] = array('groupName' => 'Group name is missing !');
				}else{
					$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname']);
					if (isset($_POST['Usergroups']['desc']) && (!empty($_POST['Usergroups']['desc'])) && ($_POST['Usergroups']['desc'] != "Group Description")) 
						$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname'], 'GroupFileDesc' => $_POST['Usergroups']['desc']);
					else
						$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname'], 'GroupFileDesc' => '');
						
					$groupErrors['finalResult'] = self::getModelInstance()->createNewGroup($groupParams, $userId);
				}
			}
			if ($groupErrors['finalResult'] == "No Errors"){
				$this->commFunctions->redirect('users/groups/');			
			}
			return array(
						'groupErrors' => $groupErrors,
						'profileImage' => self::getModelInstance()->loadProfileImage($userId), 
						'groups' => self::getModelInstance()->getGroupNamesAllPurposes($userId)
						);
		}	
	}
	
	static function checkUserAlreadyInThisGroup($groupId) {
	
		if (isset($_SESSION['currentUser'])){ 
			$userId = $_SESSION['currentUser']['userId'];
		}else if (isset($_SESSION['fbUser'])){ 
			$userId = $_SESSION['fbUser']['userId'];
		}
		$joinedGroups = self::getModelInstance()->checkUserAlreadyInThisGroup($userId, $groupId);
		foreach($joinedGroups as $eachGroup){
			$allGroups[] = $eachGroup['GroupId'];
		}
		return (in_array($groupId, $allGroups) ? true : false);
	}
	
	public function ActionMygroups(){
	
		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{	
			if (isset($_SESSION['currentUser'])){ 
				$userId = $_SESSION['currentUser']['userId'];
			}else if (isset($_SESSION['fbUser'])){ 
				$userId = $_SESSION['fbUser']['userId'];
			}
			if (isset($_POST['Usergroupssubmit'])){
				if (empty($_POST['Usergroups']['groupname'])){
					$groupErrors['errorsInForm'] = array('groupName' => 'Group name is missing !');
				}else{
					$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname']);
					if (isset($_POST['Usergroups']['desc']) && (!empty($_POST['Usergroups']['desc'])) && ($_POST['Usergroups']['desc'] != "Group Description")) 
						$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname'], 'GroupFileDesc' => $_POST['Usergroups']['desc']);
					else
						$groupParams = array('GroupFileName' => $_POST['Usergroups']['groupname'], 'GroupFileDesc' => '');
						
					$groupErrors['finalResult'] = self::getModelInstance()->createNewGroup($groupParams, $userId);
				}
			}
			if ($groupErrors['finalResult'] == "No Errors"){
				$this->commFunctions->redirect('users/groups/');			
			}
			return array(
						'profileImage' => self::getModelInstance()->loadProfileImage($userId), 
						'myGroups' => self::getModelInstance()->loadMyOwnGroups($userId)
						);
		}	
	}
	
	public function ActionDeactivate(){
	
		$this->currentSession = SessionController::getInstance();			
		if (($this->currentSession->getCurrentSession('currentUser') == NULL) && ($this->currentSession->getCurrentSession('fbUser') == NULL)){				
			$this->commFunctions->redirect();					
		}else{	
			if (isset($_SESSION['currentUser'])){ 
				$userId = $_SESSION['currentUser']['userId'];
			}else{
				$this->commFunctions->redirect();					
			}
			if ("POST" == $_SERVER['REQUEST_METHOD']){
				$errorStauts = self::getModelInstance()->checkMyPasswordCorrectness($userId, trim($_POST['your_Password']['password']));

				if ($errorStauts){
					return array(
								 'profileImage' => self::getModelInstance()->loadProfileImage($userId),
								 'error' => $errorStauts
								);
				}else{
					self::getModelInstance()->deactivateMyAccount($userId);
					$this->ActionLogout();
				}
			}
		}
	}
}