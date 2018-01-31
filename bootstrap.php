<?php

require_once '_define.inc';
$bootstrap = Bootstrap::getInstance();
$bootstrap->checkRoutes($_SERVER['REQUEST_URI']);

if (
	(($bootstrap->currentArchitechture['initialError'] != $stringLts->literalsArray['msg0']) && 
	($bootstrap->currentArchitechture['initialError'] != $stringLts->literalsArray['msg1'])) ||
	($bootstrap->currentArchitechture['initialError'] == '')
	)
	$bootstrap->createArchitecture();
else
	$bootstrap->reportErrorController();

class Bootstrap {

   public static $instance = NULL;
   public static $currentArchitechture = NULL;
   public static $globalConfig = NULL;   
   public static $rssCount = "";
   public $currentController = "";
   public $currentModel = "";
   public $currentView = "";   
   public $globalErrorMessage = "";
   public static $currentBrowser = "";
   public $noNeedToSearchForction = false;
    
   public function __construct() {

        $dir = LIBRARY_PATH;		
        $dh = opendir($dir);
		$fileNotToBeLoadedByDefault = array('.', '..', '.DS_Store', 
											'ajaxFunctions.php', 'profileMaker.php', 'encdec.php', 'simpleImage.php', 'tagMeCall.php', 'uploadCall.php', 'deleteCall.php',
											'base_facebook.php', 'facebook.php'
											);
        while (($file = readdir($dh)) !== false)
			if (!in_array($file, $fileNotToBeLoadedByDefault)) 
				require_once $dir.$file;
		$this->getSiteConfigurtations();
		$this->checkUserAlreadyLogged();
		$currentBrowserDetail = $this->getBrowser();
        self::$currentBrowser = $currentBrowserDetail['name'];
        closedir($dh);
   }

	/* This function will get the base site details */
	public static function getSiteConfigurtations() {

		$Db = database::getInstance();
		$configData = $Db->get("rehand__site_access", array('title', 'description', 'base_url'));	
		self::$globalConfig = $configData[0];
	}
	/* End of the function */
	
   /**
    * @return the instance of this class 
	* @singleton patten used with some modification
   */
   public static function getInstance() {

		if (self::$instance == NULL) self::$instance = new self;
		return self::$instance;	
   }
   
   public function checkRoutes($currentUrl){

		global $stringLts;
		$dir = CONTROLLER_PATH;		
        $dh = opendir($dir);
        while (($file = readdir($dh)) !== false) if ($file != '.' && $file != '..') $controllers[] = str_replace(EXT, "", $file);					

		if (
			($currentUrl == '') || ($currentUrl == '/') || ($currentUrl == '/www.rehand.com/') || 
			((strstr($currentUrl, "searchQ")) && (isset($_GET['searchQ'])) ||
			(strstr($currentUrl, "openKey"))												  
		   )
		   ) { 
			$this->currentController = $this->currentModel = $this->currentView = 'index';
		}else{
			$splittedUrls = explode("/", $currentUrl);
			foreach($splittedUrls as $eachSplit){
				if (($eachSplit != '') && (!empty($eachSplit)) && (isset($eachSplit)) && ($eachSplit != NULL)){
					$newSplittedUrls[] = $eachSplit;
				}
			}
			// Special situation for the public pages
			$publicPages = array("team", "about", "copyright", "help", "privacy_policy", "terms_of_use");
			if (($newSplittedUrls[2] == "users") && (in_array($newSplittedUrls[1], $publicPages))){
				unset($newSplittedUrls[1]);
				$newSplittedUrls = array_values($newSplittedUrls);				
			}
			// spcial situation for the users activation link
			$publicPagesForUserController = array("activation");
			if (
				($newSplittedUrls[1] == "users") && 
				($newSplittedUrls[2] == "activation") && 
				(($newSplittedUrls[4] == "login") || ($newSplittedUrls[4] == "register") || ($newSplittedUrls[4] == "confirmfblogin") || ($newSplittedUrls[4] == "fblogin")) && 
				(in_array($newSplittedUrls[2], $publicPagesForUserController))
			   ){
				$newSplittedUrls[1] = "users";
				$newSplittedUrls[2] = "login";				
				unset($newSplittedUrls[3]);
				unset($newSplittedUrls[4]);
				$newSplittedUrls = array_values($newSplittedUrls);				
			}
			/* This is only for the beta testing site */
			if (in_array($newSplittedUrls[1], $controllers)){
				$this->currentController = $this->currentModel = $newSplittedUrls[1];
				$this->noNeedToSearchForction = false;				
				if (
					(self::$currentBrowser == "Internet Explorer") && 
					($this->currentController == "users") &&
					(($newSplittedUrls[2] == "uploader") && ($_GET['view'] == 'add'))
					){
						$this->currentView = "uploader_ie";
					}else{
						$this->currentView = $newSplittedUrls[2];
					}
				$this->globalErrorMessage = (!file_exists(VIEW_PATH.$this->currentController.DS.$this->currentView.EXT)) ? $stringLts->literalsArray['msg1'] : "";				
			}else{
				$restPagesIndexController = array("about", "contact", "team", "terms_of_use", "privacy_policy", "help", "copyright");
				if (in_array($newSplittedUrls[1], $restPagesIndexController)){
					if (file_exists(VIEW_PATH.'index'.DS.$newSplittedUrls[1].EXT)){
						$this->currentController = $this->currentModel = 'index';			
						$this->currentView = $newSplittedUrls[1];
						$this->noNeedToSearchForction = true;
					}
				}else{
					$this->currentController = $this->currentModel = $this->currentView = NULL;			
					$this->globalErrorMessage = $stringLts->literalsArray['msg0'];	
					$this->noNeedToSearchForction = true;
				}
			}
		}	
		$this->currentArchitechture = array(
											'appArchtecture' => 
															array(
																 'controller' => $this->currentController, 
																 'model' => $this->currentModel, 
																 'view' => $this->currentView
																 ), 
											'initialError' => $this->globalErrorMessage
											);
   }
   
   public function createArchitecture(){

		// Include the correct controller
		require CONTROLLER_PATH.$this->currentArchitechture['appArchtecture']['controller'].EXT;
		// Adjust the controller name for the framework and instantiate it
		$declaredController = $this->currentArchitechture['appArchtecture']['controller'].'Controller';
		$this->currentController = new $declaredController;												
		// Adjust the controller class name for the framework
		$declaredControllerClass = ucfirst($this->currentArchitechture['appArchtecture']['controller']).'Controller';		
		// Check the declared controller objects is an instance of the controller class
		if ($this->currentController instanceof $declaredControllerClass){
			// Include the correct model		
			require MODEL_PATH.$this->currentArchitechture['appArchtecture']['model'].EXT;
			// Execute the correct method
			if (!$this->noNeedToSearchForction){
				$methodName = 'Action'.ucfirst($this->currentArchitechture['appArchtecture']['view']);			
				$ViewableData = $this->currentController->$methodName();
			}else{
				$ViewableData = $this->currentController->ActionIndex();
			}
		}
		require LAYOUT_PATH.'index.html';					   		
   }
   
   public function reportErrorController(){ CommonFunctions::redirect(); }   
   
   public function checkUserAlreadyLogged() {
   
		if (isset($_COOKIE['username'], $_COOKIE['password'])){
			$Db = database::getInstance();
			$Db->where('userId', $_COOKIE['username']);
			$userData = $Db->get("rehand__users", array('email', 'password'));	
			if ($userData){
				if($userData[0]['password'] == $_COOKIE['pass']){
					// The user should be logged in
					$Db->resetWhere();
					$Db->where($wherParam, $fieldValue);		
					$loggedUserParams = $Db->get("rehand__users", array('userId', 'email', 'fbUserID', 'firstName', 'lastName', 'password', 'postCode', 'companyName', 'country', 'gender', 'dateOfBirth'));						
					$this->currentSession = SessionController::getInstance();
					$this->currentSession->createRelateSession($loggedUsersParams[0], 'currentUser');
				}
			}
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
}