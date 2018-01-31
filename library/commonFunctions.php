<?php

global $CommFuncs;
$CommFuncs = CommonFunctions::getInstance();

class CommonFunctions {
	
    static public $instance = NULL;
	
	static $genderList = array(
								"M" => "Male",
								"F" => "Female",
								"U" => "Uni"
							   );
	static $countryList = array(
	
								"Afghanistan" => "Afghanistan",
								"Aland Islands" => "Aland Islands",
								"Albania" => "Albania",
								"Algeria" => "Algeria",
								"American Samoa" => "American Samoa",
								"Andorra" => "Andorra",
								"Angola" => "Angola",
								"Anguilla" => "Anguilla",
								"Antarctica" => "Antarctica",
								"Antigua and Barbuda" => "Antigua and Barbuda",
								"Argentina" => "Argentina",
								"Armenia" => "Armenia",
								"Aruba" => "Aruba",
								"Australia" => "Australia",
								"Austria" => "Austria",
								"Azerbaijan" => "Azerbaijan",
								"Bahamas" => "Bahamas",
								"Bahrain" => "Bahrain",
								"Bangladesh" => "Bangladesh",
								"Barbados" => "Barbados",
								"Belarus" => "Belarus",
								"Belize" => "Belize",
								"Belgium" => "Belgium",
								"Benin" => "Benin",
								"Bermuda" => "Bermuda",
								"Bhutan" => "Bhutan",
								"Bolivia" => "Bolivia",
								"Bosnia and Herzegovina" => "Bosnia and Herzegovina",
								"Botswana" => "Botswana",
								"Bouvet Island" => "Bouvet Island",
								"Brazil" => "Brazil",
								"British Antarctic Territory" => "British Antarctic Territory",
								"British Indian Ocean Territory" => "British Indian Ocean Territory",
								"British Virgin Islands" => "British Virgin Islands",
								"Brunei" => "Brunei",
								"Bulgaria" => "Bulgaria",
								"Burkina Faso" => "Burkina Faso",
								"Burundi" => "Burundi",
								"Cambodia" => "Cambodia",
								"Cameroon" => "Cameroon",
								"Canada" => "Canada",
								"Canton and Enderbury Islands" => "Canton and Enderbury Islands",
								"Cape Verde" => "Cape Verde",
								"Cayman Islands" => "Cayman Islands",
								"Central African Republic" => "Central African Republic",
								"Chad" => "Chad",
								"Chile" => "Chile",
								"China" => "China",
								"Christmas Island" => "Christmas Island",
								"Cocos (Keeling) Islands" => "Cocos (Keeling) Islands",
								"Colombia" => "Colombia",
								"Comoros" => "Comoros",
								"Congo (Brazzaville)" => "Congo (Brazzaville)",
								"Congo (Kinshasa)" => "Congo (Kinshasa)",
								"Cook Islands" => "Cook Islands",
								"Costa Rica" => "Costa Rica",
								"Croatia" => "Croatia",
								"Cuba" => "Cuba",
								"Cyprus" => "Cyprus",
								"Czech Republic" => "Czech Republic",
								"Denmark" => "Denmark",
								"Djibouti" => "Djibouti",
								"Dominica" => "Dominica",
								"Dominican Republic" => "Dominican Republic",
								"Dronning Maud Land" => "Dronning Maud Land",
								"East Timor" => "East Timor",
								"Ecuador" => "Ecuador",
								"Egypt" => "Egypt",
								"El Salvador" => "El Salvador",
								"Equatorial Guinea" => "Equatorial Guinea",
								"Eritrea" => "Eritrea",
								"Estonia" => "Estonia",
								"Ethiopia" => "Ethiopia",
								"Falkland Islands" => "Falkland Islands",
								"Faroe Islands" => "Faroe Islands",
								"Fiji" => "Fiji",
								"Finland" => "Finland",
								"France" => "France",
								"French Guiana" => "French Guiana",
								"French Polynesia" => "French Polynesia",
								"French Southern Territories" => "French Southern Territories",
								"French Southern and Antarctic Territories" => "French Southern and Antarctic Territories",
								"Gabon" => "Gabon",
								"Gambia" => "Gambia",
								"Germany" => "Germany",
								"Georgia" => "Georgia",
								"Ghana" => "Ghana",
								"Gibraltar" => "Gibraltar",
								"Greece" => "Greece",
								"Greenland" => "Greenland",
								"Grenada" => "Grenada",
								"Guadeloupe" => "Guadeloupe",
								"Guam" => "Guam",
								"Guatemala" => "Guatemala",
								"Guinea" => "Guinea",
								"Guinea-Bissau" => "Guinea-Bissau",
								"Guyana" => "Guyana",
								"Haiti" => "Haiti",
								"Heard Island and McDonald Islands" => "Heard Island and McDonald Islands",
								"Honduras" => "Honduras",
								"Hong Kong S.A.R., China" => "Hong Kong S.A.R., China",
								"Hungary" => "Hungary",
								"Iceland" => "Iceland",
								"India" => "India",
								"Indonesia" => "Indonesia",
								"Ireland" => "Ireland",
								"Italy" => "Italy",
								"Iran" => "Iran",
								"Iraq" => "Iraq",
								"Israel" => "Israel",
								"Ivory Coast" => "Ivory Coast",
								"Jamaica" => "Jamaica",
								"Japan" => "Japan",
								"Johnston Island" => "Johnston Island",
								"Jordan" => "Jordan",
								"Kazakhstan" => "Kazakhstan",
								"Kenya" => "Kenya",
								"Kiribati" => "Kiribati",
								"Kuwait" => "Kuwait",
								"Kyrgyzstan" => "Kyrgyzstan",
								"Laos" => "Laos",
								"Latvia" => "Latvia",
								"Lebanon" => "Lebanon",
								"Lesotho" => "Lesotho",
								"Liberia" => "Liberia",
								"Libya" => "Libya",
								"Liechtenstein" => "Liechtenstein",
								"Lithuania" => "Lithuania",
								"Luxembourg" => "Luxembourg",
								"Macao S.A.R., China" => "Macao S.A.R., China",
								"Macedonia" => "Macedonia",
								"Madagascar" => "Madagascar",
								"Malawi" => "Malawi",
								"Malaysia" => "Malaysia",
								"Maldives" => "Maldives",
								"Mali" => "Mali",
								"Malta" => "Malta",
								"Marshall Islands" => "Marshall Islands",
								"Martinique" => "Martinique",
								"Mauritania" => "Mauritania",
								"Mauritius" => "Mauritius",
								"Mayotte" => "Mayotte",
								"Metropolitan France" => "Metropolitan France",
								"Mexico" => "Mexico",
								"Micronesia" => "Micronesia",
								"Midway Islands" => "Midway Islands",
								"Moldova" => "Moldova",
								"Monaco" => "Monaco",
								"Mongolia" => "Mongolia",
								"Montserrat" => "Montserrat",
								"Morocco" => "Morocco",
								"Mozambique" => "Mozambique",
								"Myanmar" => "Myanmar",
								"Namibia" => "Namibia",
								"Nauru" => "Nauru",
								"Nepal" => "Nepal",
								"Netherlands" => "Netherlands",
								"Netherlands Antilles" => "Netherlands Antilles",
								"New Zealand" => "New Zealand",
								"New Caledonia" => "New Caledonia",
								"Nicaragua" => "Nicaragua",
								"Niger" => "Niger",
								"Nigeria" => "Nigeria",
								"Niue" => "Niue",
								"Norfolk Island" => "Norfolk Island",
								"North Korea" => "North Korea",
								"North Vietnam" => "North Vietnam",
								"Northern Mariana Islands" => "Northern Mariana Islands",
								"Norway" => "Norway",
								"Oman" => "Oman",
								"Outlying Oceania" => "Outlying Oceania",
								"Pacific Islands Trust Territory" => "Pacific Islands Trust Territory",
								"Pakistan" => "Pakistan",
								"Palau" => "Palau",
								"Palestinian Territory" => "Palestinian Territory",
								"Panama" => "Panama",
								"Panama Canal Zone" => "Panama Canal Zone",
								"Papua New Guinea" => "Papua New Guinea",
								"Paraguay" => "Paraguay",
								"People's Democratic Republic of Yemen" => "People's Democratic Republic of Yemen",
								"Peru" => "Peru",
								"Philippines" => "Philippines",
								"Pitcairn" => "Pitcairn",
								"Poland" => "Poland",
								"Portugal" => "Portugal",
								"Puerto Rico" => "Puerto Rico",
								"Qatar" => "Qatar",
								"Reunion" => "Reunion",
								"Romania" => "Romania",
								"Russia" => "Russia",
								"Rwanda" => "Rwanda",
								"Saint Helena" => "Saint Helena",
								"Saint Kitts and Nevis" => "Saint Kitts and Nevis",
								"Saint Lucia" => "Saint Lucia",
								"Saint Pierre and Miquelon" => "Saint Pierre and Miquelon",
								"Saint Vincent and the Grenadines" => "Saint Vincent and the Grenadines",
								"Samoa" => "Samoa",
								"San Marino" => "San Marino",
								"Sao Tome and Principe" => "Sao Tome and Principe",
								"Saudi Arabia" => "Saudi Arabia",
								"Senegal" => "Senegal",
								"Serbia And Montenegro" => "Serbia And Montenegro",
								"Seychelles" => "Seychelles",
								"Sierra Leone" => "Sierra Leone",
								"Singapore" => "Singapore",
								"Slovakia" => "Slovakia",
								"Slovenia" => "Slovenia",
								"Solomon Islands" => "Solomon Islands",
								"Somalia" => "Somalia",
								"South Africa" => "South Africa",
								"South Georgia and the South Sandwich Islands" => "South Georgia and the South Sandwich Islands",
								"South Korea" => "South Korea",
								"Spain" => "Spain",
								"Sri Lanka" => "Sri Lanka",
								"Sudan" => "Sudan",
								"Suriname" => "Suriname",
								"Svalbard and Jan Mayen" => "Svalbard and Jan Mayen",
								"Swaziland" => "Swaziland",
								"Sweden" => "Sweden",
								"Switzerland" => "Switzerland",
								"Syria" => "Syria",
								"Taiwan" => "Taiwan",
								"Tajikistan" => "Tajikistan",
								"Tanzania" => "Tanzania",
								"Thailand" => "Thailand",
								"Togo" => "Togo",
								"Tokelau" => "Tokelau",
								"Tonga" => "Tonga",
								"Trinidad and Tobago" => "Trinidad and Tobago",
								"Tunisia" => "Tunisia",
								"Turkey" => "Turkey",
								"Turkmenistan" => "Turkmenistan",
								"Turks and Caicos Islands" => "Turks and Caicos Islands",
								"Tuvalu" => "Tuvalu",
								"U.S. Miscellaneous Pacific Islands" => "U.S. Miscellaneous Pacific Islands",
								"U.S. Virgin Islands" => "U.S. Virgin Islands",
								"Uganda" => "Uganda",
								"Ukraine" => "Ukraine",
								"United Arab Emirates" => "United Arab Emirates",
								"United States Minor Outlying Islands" => "United States Minor Outlying Islands",
								"United Kingdom" => "United Kingdom",
								"United States" => "United States",
								"Uruguay" => "Uruguay",
								"Uzbekistan" => "Uzbekistan",
								"Vanuatu" => "Vanuatu",
								"Vatican" => "Vatican",
								"Venezuela" => "Venezuela",
								"Vietnam" => "Vietnam",
								"Wake Island" => "Wake Island",
								"Wallis and Futuna" => "Wallis and Futuna",
								"Western Sahara" => "Western Sahara",
								"Yemen" => "Yemen",
								"Zambia" => "Zambia",
								"Zimbabwe" => "Zimbabwe"
							);
   	
	static public function check_empty_folder($folder) {
	
		$files = array();
		if ($handle = opendir($folder)) {
			while(false !== ($file = readdir($handle)))
				if ($file != "." && $file != ".." ) $files [] = $file;
			closedir($handle);
		}
		return (count($files) > 0) ? false : true;
	}
	
	static public function redirect($url = ""){	
		
		header("Location: ".(($url == "") ? WEB_PATH : WEB_PATH.$url));
		exit();
	}

   static public function redirectAfterSomeTime($url = ""){ header("refresh:2; url = ".(($url == "") ? WEB_PATH : WEB_PATH.$url)); }

   static public function getInstance() {

	   if (self::$instance == NULL) self::$instance = new self;
	   return self::$instance;		   
   }	
   
   static public function errorCheckingFields($fieldsArray = array(), $neglectedFields = "") {
   
   		$errorFieldsArray = array();
		$errorsFound = 'true';
		foreach($fieldsArray as $eachField => $eachValue){
			if (!in_array($eachField, $neglectedFields)){
				$newFieldArray[$eachField] = $eachValue;
			}
		}		 
   		foreach($newFieldArray as $field => $value) {
			if ((isset($value)) && (!empty($value)) && ($value != '') && ($value != NULL)) $errorsFound = 'false';
			else $errorFieldsArray[] = $field;
		}
		return array('errorStatus' => $errorsFound, 'errorFields' => $errorFieldsArray);
   }
   
   static public function checkPasswordsAreSame($passwordsParam) { return ($passwordsParam[0] == $passwordsParam[1]) ? true : false; }

   static public function checkEmailAddressValid($email) {

		return (preg_match( "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/", $email)) ? true : false;
	}
	   
   /* These function will be usable with the facebook registration and login things */
   static public function parse_signed_request($signed_request, $secret) {
		
		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
		
		// decode the data
		$sig = self::base64_url_decode($encoded_sig);
		$data = json_decode(self::base64_url_decode($payload), true);
		
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			error_log('Unknown algorithm. Expected HMAC-SHA256');
			return null;
		}
		
		/*	
		// check sig
		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
		if ($sig !== $expected_sig) {
			echo 'Bad Signed JSON signature!';
			return null;
		}
		*/		
		return $data;
	}
		
	static public function base64_url_decode($input) { return base64_decode(strtr($input, '-_', '+/')); }	
	
	/* This fucntion will check the user edntered date is a valid date */
	function check_date_vlidity($dateArgs) { return (checkdate($dateArgs[0], $dateArgs[1], $dateArgs[2]) == 1) ? true : false; }
	/* End of the fucntion */
	
	/* This function will remove the array duplication */
	static function array_multi_unique($resulted_array, $key){
	
		$temp_ary = array();
			foreach($resulted_array as $ele){
			$temp_ary[] = $ele[$key];
		}
		$temp_ary = array_keys(array_unique($temp_ary));
		
		$final_result = array();
		foreach($temp_ary as $id){
			$final_result[] = $resulted_array[$id];
		}
		return $final_result;
	}	
	
	/* This function will prepare the value entered by the user to put it in to the database */
	public static function mysql_preperation($value){		
	
		$magic_quotes_active = get_magic_quotes_gpc();
		$new_enough_php = function_exists("mysql_real_escape_string"); 
		// i.e. PHP >= v4.3.0
		if ($new_enough_php){ 
			// PHP v4.3.0 or higher
			// undo any magic quote effects so mysql_real_escape_string can do the work
			if ($magic_quotes_active){ 
				$value = stripslashes($value); 
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
	
	/* This function will return a html content for print as a drop down select menu for date functionalities */
	static function print_date_selecting_drop_down($selecteGroupName, $currentDob){
	
		if ($currentDob != ""){
			$explodedDate = explode("-", $currentDob);	
			$selectedD = $explodedDate[2]; 
			$selectedM = $explodedDate[1]; 
			$selectedY = $explodedDate[0];
		}
		$htmlDropDown = "";
		$htmlDropDown .= "<div>";				
			// Date Drop Down
			$htmlDropDown .= "<div>";
			$htmlDropDown .= "<select id='".$selecteGroupName."_dd' name='".$selecteGroupName."[day]' class='dd'>";
			$htmlDropDown .= "<option value=''>DD&nbsp;</option>";			
			for($i=1; $i<= 31; $i++){
				$val = str_pad($i, 2, "0", STR_PAD_LEFT);
				$selected = ($selectedD == $i) ? "selected = 'selected'" : ""; 
				$htmlDropDown .= "<option value='{$i}' {$selected}>{$val}</option>";
			}
			$htmlDropDown .= "</select>";
			$htmlDropDown .= "</div>";		
				
			// Month Drop Down
			$htmlDropDown .= "<div>";
			$htmlDropDown .= "<select id='".$selecteGroupName."_mm' name='".$selecteGroupName."[month]' class='mm'>";
			$htmlDropDown .= "<option value=''>&nbsp;&nbsp;&nbsp;MM</option>";			
			for ($i = 1; $i <= 12; $i++){
				/*** get the month ***/
				$mon = date("F", mktime(0, 0, 0, $i+1, 0, 0));
				$selected = ($selectedM == $i) ? "selected = 'selected'" : ""; 			
				$htmlDropDown .= "<option value='{$i}' {$selected}>{$mon}</option>";
			}
			$htmlDropDown .= "</select>";
			$htmlDropDown .= "</div>";				
			
			// Year Drop Down
			$htmlDropDown .= "<div>";
			$htmlDropDown .= "<select id='".$selecteGroupName."_yy' name='".$selecteGroupName."[year]' class='yy'>";
			$htmlDropDown .= "<option value=''>YY&nbsp;</option>";
			for ($i = date("Y"); $i >= date("Y")-75; $i--){
				$selected = ($selectedY == $i) ? "selected = 'selected'" : ""; 					
				$htmlDropDown .= "<option value='{$i}' {$selected}>{$i}</option>";
			}
			$htmlDropDown .= "</select>";
		$htmlDropDown .= "</div>";								
					
		return $htmlDropDown;
	}	
	/* End of the function */	
	
	static function sendMailNotification($mailto, $mailsubject, $mailmessage, $from = ""){
	
		//mail to above user with his login details
		$from = ($from != "") ? $from : "response@rehand.com";
		$to      = $mailto;
		$subject = $mailsubject;
		$message = $mailmessage;
		$headers = "MIME-Version: 1.0\r\n"; 
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
		$headers .= "From: $from" . "\r\n" .
							"Reply-To: $from" . "\r\n" .
							"X-Mailer: PHP/" . phpversion();
		mail($to, $subject, $message, $headers);
	}
	
	static function resizeImage($image,$width,$height,$scale) {

		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source=imagecreatefromjpeg($image); 
				break;
			case "image/png":
			case "image/x-png":
				$source=imagecreatefrompng($image); 
				break;
		}
		imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
		
		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$image); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				imagejpeg($newImage,$image,90); 
				break;
			case "image/png":
			case "image/x-png":
				imagepng($newImage,$image);  
				break;
		}
		
		chmod($image, 0777);
		return $image;
	}
	
	//You do not need to alter these functions
	static function getHeight($image) {
		
		$size = getimagesize($image);
		$height = $size[1];
		return $height;
	}
	//You do not need to alter these functions
	static function getWidth($image) {
		
		$size = getimagesize($image);
		$width = $size[0];
		return $width;
	}
	//You do not need to alter these functions
	static function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
		
		list($imagewidth, $imageheight, $imageType) = getimagesize($image);
		$imageType = image_type_to_mime_type($imageType);
		
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
		switch($imageType) {
			case "image/gif":
				$source=imagecreatefromgif($image); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source=imagecreatefromjpeg($image); 
				break;
			case "image/png":
			case "image/x-png":
				$source=imagecreatefrompng($image); 
				break;
		}
		imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
		switch($imageType) {
			case "image/gif":
				imagegif($newImage,$thumb_image_name); 
				break;
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				imagejpeg($newImage,$thumb_image_name,90); 
				break;
			case "image/png":
			case "image/x-png":
				imagepng($newImage,$thumb_image_name);  
				break;
		}
		chmod($thumb_image_name, 0777);
		return $thumb_image_name;
	}
}