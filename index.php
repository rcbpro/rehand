<?php 
	error_reporting(E_ALL);
	ini_set('display_errors', 0);			
	
	/*if (!strstr($_SERVER['HTTP_HOST'],"www.")){
		header ('HTTP/1.1 301 Moved Permanently');
		header("Location: http://www.seedinnovations.com".$_SERVER['REQUEST_URI']);
		die();
	}	*/
	include 'bootstrap.php';