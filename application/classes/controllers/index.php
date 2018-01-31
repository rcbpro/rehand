<?php

class IndexController {

	public static $model = NULL;
	public $commFunctions = NULL;
	public $currentSession = NULL;

	public function __construct() {
	
		global $CommFuncs;
		$this->commFunctions = $CommFuncs;	
		require CONTROLLER_PATH.'sessions'.EXT;	
	}
	
	static public function getModelInstance() {
	
		if (self::$model == NULL)
			self::$model = new IndexModel;
		return self::$model;	
	}

   	public function ActionIndex() {
		
		if ("POST" != $_SERVER['REQUEST_METHOD']) {
		   if ((isset($_GET['searchQ'])) && (($_GET['searchQ'] != ""))){
			  unset($_SESSION['searchQuery']);
			  $this->currentSession = SessionController::getInstance();		
			  $this->currentSession->createRelateSession($_GET['searchQ'], 'searchQuery');	
		   }else{
			   if (isset($_SESSION['searchQuery'])) unset($_SESSION['searchQuery']);		
		   }
		   return self::getModelInstance()->Index(); 
        }else{ 
        	$this->commFunctions->redirect("users/register");
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
				 return self::getModelInstance()->loadProfileImage();
		   }
		}		
    }	
}