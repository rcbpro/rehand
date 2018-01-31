<?php
class Encdec{
	private $salt = 'rehandEncriptionKey',
		$search  = array( '/', '+',"="),
		$replace = array('-', '_','');	
	
	function encrypt($text) 
	{ 
		return str_replace($this->search, $this->replace,base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->salt), $text, MCRYPT_MODE_CBC, md5(md5($this->salt)))));
		//return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
	} 
	
	function decrypt($text) 
	{ 
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->salt), base64_decode(str_replace($this->replace, $this->search,$text)."="), MCRYPT_MODE_CBC, md5(md5($this->salt))), "\0");
		//return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
	} 
}
?>