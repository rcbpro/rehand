<?php

global $stringLts;
$stringLts = StringLiterals::getInstance();

class StringLiterals {
	
	public static $literalsArray = NULL;
	static public $instance = NULL;
	
	public function __construct(){
	
		$this->literalsArray = array(	
									'msg0' => 'Error in Controller',
									'msg1' => 'Error in View',
									'msg2' => 'Invalid Email Address',											
									'msg3' => 'Passwords are not equal',											
									'msg4' => 'Invalid User ID',
									'msg5' => 'This email address has been already been registered with Rehand.com',
									'msg6' => 'Facebook Request is empty !',																																																																			
									'msg7' => 'Recover your Password',
									'msg8' => 'Please click this link '.WEB_PATH.'users/newPassword to ensure your new passwords.',																																																																												
									'msg9' => 'From: hellow@socialseedmedia.com.au' . "\r\n" . 'Reply-To: hellow@socialseedmedia.com.au' . "\r\n" . 'X-Mailer: PHP/' . phpversion(),
									'msg10' => 'Invalid Date',
									'msg11' => 'Invalid link for the password reset !'
									);
	}
	
   /**
    * @return the instance of database class
    */
   public static function getInstance() {

	   if (self::$instance == NULL) self::$instance = new self;
	   return self::$instance;		   
   }	
}