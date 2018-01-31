<?php

global $sessionObj; 
$sessionObj = SessionController::getInstance();				
		
class SessionController {

	public static $instance = NULL;

	public function __construct() {
	
		session_start();
	}
	
	public static function getInstance() {
	
		if (self::$instance == NULL) self::$instance = new self;
		return self::$instance;		   
	}
	
	public function createRelateSession($currentSessionParams, $paramName) {
	
		$_SESSION[$paramName] = $currentSessionParams;
	}
	
	public function getCurrentSession($sessionName) {
	
		return $_SESSION[$sessionName];
	} 

	public function getAllSessionsAvailable() {
	
		return $_SESSION;
	} 
	
	public function unsetSession($sessionName) {
		
		unset($_SESSION[key($sessionName)]);
	}
}