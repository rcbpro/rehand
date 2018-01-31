<?php 
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
include '../_define.inc';
include 'database.php';
include	'encdec.php';

class tagMeCall{

    private $userId,$connection,$encdec, $DbObject;
	
    function __construct(){
		$this->DbObject = database::getInstance();
		$this->connection = mysql_connect("localhost", "root", "root");
		$this->encdec = new Encdec();
		if ($this->connection) mysql_select_db("rehand") or die(mysql_error());
	}
	
	function tagit($tagData){
		$errorMes = "Please fix these error(s)<br />";
		$errorInputData = false;	
		$imageHash = $tagData['imageId'];
		
		//validate user
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$this->userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$this->userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'User not authorized.');
		}
		
		if(!isset($_SESSION['recentUploads'][$imageHash]))
			return array("error"=>"No image for this hash ".$imageHash);
			
		$imageId_real =  $_SESSION['recentUploads'][$imageHash];
		if(!$this->authenticateImage($imageId_real))
			return array('error' => 'This is not your image, upload an image your self and tag.');
		
		foreach($tagData as $postkey => $postvalue){
			$$postkey = mysql_real_escape_string($postvalue);
		}
		/*
		if (!is_numeric($itemQty)){
			$errorInputData = true;
			$errorMes .= "Invalid value for item qunatity<br />";
		}
		*/
		if (($itemPrice != "") && (isset($itemPrice))){
			if ((!strstr($itemPrice, "$0.00")) && (!strstr($itemPrice, "0.00"))){
				if (!is_numeric($itemPrice)){
					$errorInputData = true;
					$errorMes .= "Invalid value for item price";
				}else{
					$itemStatus = "available";
					$tagData['itemStatus'] = $itemStatus;
				}
			}else{
				$itemPrice = 0.00;
				$itemStatus = "free";
				$tagData['itemStatus'] = $itemStatus;
			}	
		}
		
		if (!$errorInputData){
			$qry = "INSERT INTO tagging__taggeditems 
								(`tagName`,`pictureId`,`userId`,`desc`,`price`,`currentStatus`,`tagPositionX`,`tagPositionY`,`width`,`height`) VALUES
								('".$itemName."',".$imageId_real.",".$this->userId.",'".$itemDescription."',".$itemPrice.",'".$itemStatus."','".$tagX."','".$tagY."','".$tagW."','".$tagH."')";	
			$res = mysql_query(	$qry,$this->connection) or die(mysql_error());	
			$tagData['tagId'] = $this->encdec->encrypt(mysql_insert_id($this->connection));
			$this->updateImageState($imageId_real);
			return array("error" => NULL, "obj" => $tagData);
		}else{
			return array("error" => $errorMes, "obj"=>$tagData);
		}
	}
	function editit($tagData){
		$errorInUpdate = false;
		$errMsg = "";
		$imageHash = $tagData['imageId'];
		
		//validate user
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$this->userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$this->userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'User not authorized.');
		}
		
		if(!isset($_SESSION['recentUploads'][$imageHash]))
			return array("error"=>"No image for this hash ".$imageHash);
			
		$imageId_real =  $_SESSION['recentUploads'][$imageHash];
		$tmp_tagId = $this->encdec->decrypt($tagData['tagId']);
		
		$tagId_real = intval($tmp_tagId);
		if(!$this->authenticateImage($imageId_real))
			return array('error' => 'This is not your image, upload an image your self and tag.');
			
		if(!$this->authenticateTag($tagId_real,$imageId_real))
			return array('error' => 'This is not your tag, upload an image your self and tag.');
		
		foreach($tagData as $postkey => $postvalue){
			$$postkey = mysql_real_escape_string($postvalue);
		}
		// Get the current item status and if the current status is sold or free we are not giving the chance to update the stauts
		$this->DbObject->resetWhere();
		$this->DbObject->where('tagId', $tmp_tagId);
		$tagOwnerDetails = $this->DbObject->get("tagging__taggeditems", array('currentStatus'));
		$curentTagStatus = $tagOwnerDetails[0]['currentStatus'];
		if (($curentTagStatus == "free") && ($itemStatus == "sold")){
			$finalItemStatus = $curentTagStatus;
			$errorInUpdate = true;
		}else if (($curentTagStatus == "free") && ($itemStatus == "available")){
			if ((strstr($itemPrice, "$")) || ($itemPrice == "0.00")){
				$finalItemStatus = $curentTagStatus;
				$errorInUpdate = true;		
				$errMsg = "Price should not be 0.00";
			}else{
				if (strstr($itemPrice, "$")){
					$itemPrice = str_replace("$", "", $itemPrice);
				}
				$finalItemStatus = $itemStatus;
				$errorInUpdate = false;		
			}
		}else if (($curentTagStatus == "sold") && ($itemStatus == "free")){
			$finalItemStatus = $curentTagStatus;
			$errorInUpdate = true;			
			$errMsg = "Cannot change the status from ".$curentTagStatus." to ".$itemStatus;
		}else if (($curentTagStatus == "sold") && ($itemStatus == "available")){
			$finalItemStatus = $curentTagStatus;
			$errorInUpdate = true;			
			$errMsg = "Cannot change the status from ".$curentTagStatus." to ".$itemStatus;
		}else if (($curentTagStatus == "available") && ($itemStatus == "sold")){
			if ((strstr($itemPrice, "$")) || ($itemPrice == "0.00")){
				$finalItemStatus = $curentTagStatus;
				$errorInUpdate = true;		
				$errMsg = "Price should not be 0.00";
			}else{
				if (strstr($itemPrice, "$")){
					$itemPrice = str_replace("$", "", $itemPrice);
				}
			}
			$finalItemStatus = $itemStatus;
			$errorInUpdate = false;	
		}else if (($curentTagStatus == "available") && ($itemStatus == "free")){
			if ((strstr($itemPrice, "$")) || ($itemPrice == "0.00")){
				$finalItemStatus = $curentTagStatus;
				$errorInUpdate = true;		
				$errMsg = "Price should not be 0.00";
			}else{
				if (strstr($itemPrice, "$")){
					$itemPrice = str_replace("$", "", $itemPrice);
				}
			}
			$finalItemStatus = $itemStatus;
			$errorInUpdate = false;			
		}else{
			if ((strstr($itemPrice, "$")) || ($itemPrice == "0.00")){
				$finalItemStatus = $curentTagStatus;
				$errorInUpdate = true;		
				$errMsg = "Price should not be 0.00";
			}else{
				if (strstr($itemPrice, "$")){
					$itemPrice = str_replace("$", "", $itemPrice);
				}
			}
			$finalItemStatus = $curentTagStatus;
            $errorInUpdate = false;	
		}
		if (!$errorInUpdate){
			$qry = "UPDATE tagging__taggeditems SET 	`tagName` = '".$itemName."',
														`pictureId`=".$imageId_real.",
														`userId`=".$this->userId.",
														`desc`='".$itemDescription."',
														`price`=".(($itemPrice != "") || (!empty($itemPrice)) ? $itemPrice : "0.00").",
														`currentStatus`='".$finalItemStatus."',
														`tagPositionX`='".$tagX."',
														`tagPositionY`='".$tagY."',
														`width`='".$tagW."',
														`height`='".$tagH."' 
														
					WHERE tagId=".$tagId_real." AND userId=".$this->userId." AND pictureId=".$imageId_real;	
			foreach($tagData as $key => $val){
				if ($key == "itemStatus"){
					$modifiedTagData['itemStatus'] = $finalItemStatus;
					//break;
				}else{
					$modifiedTagData[$key] = $val;
				}
			}
			$res = mysql_query(	$qry,$this->connection) or die(mysql_error());		
			foreach($modifiedTagData as $key => $val){
				if ($key == "itemPrice"){
					$modifiedTagData2["itemPrice"] = str_replace("$", "", $val);
				}else{
					$modifiedTagData2[$key] = $val;
				}
			}	
			return array("error"=>NULL,"obj"=>$modifiedTagData2);
		}else{
			return array("error"=>$errMsg,"obj"=>$tagData);
		}
	}
	function loadit($tagData){
		$imageHash = $tagData['imageId'];
		
		//validate user
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$this->userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$this->userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'User not authorized.');
		}
		
		if(!isset($_SESSION['recentUploads'][$imageHash]))
			return array("error"=>"No image for this hash ".$imageHash);
			
		$imageId_real =  $_SESSION['recentUploads'][$imageHash];		
		$qry = mysql_query("SELECT * FROM tagging__taggeditems WHERE pictureId=".$imageId_real." AND userId=".$this->userId,$this->connection) or die(mysql_error());
		$tagList = array();
		while($tmp = mysql_fetch_assoc($qry))
		{
			switch($tmp["currentStatus"]){
				case "available" : $currentStatus = "available"; break;
				case "free" : $currentStatus = "free"; break;
				case "sold" : $currentStatus = "sold"; break;				
			}	
			$tagId = $this->encdec->encrypt($tmp["tagId"]);
			$tmpArry = array(
			"imageId"=>$imageHash,
			"itemDescription"=>$tmp["desc"],
			"itemName"=>$tmp["tagName"],
			"itemPrice"=>sprintf("%01.2f", $tmp["price"]),
			"itemQty"=>$tmp["qty"],
			"itemStatus"=>$currentStatus,
			"tagH"=>$tmp["height"],
			"tagId"=>$tagId,
			"tagW"=>$tmp["width"],
			"tagX"=>$tmp["tagPositionX"],
			"tagY"=>$tmp["tagPositionY"]);
			$tagList[] = $tmpArry;
		}
		return array("error"=>NULL,"tags"=>$tagList);
	}
	function publoadit($tagData){
	
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$userId = $_SESSION['fbUser']['userId'];
			}			
		}
	
		$imageHash = $tagData['imageId'];
		
		if(!isset($_SESSION['recentLoads'][$imageHash]))
			return array("error"=>"No image for this hash ".$imageHash);
			
		$imageId_real =  $_SESSION['recentLoads'][$imageHash];		
		if (!empty($_GET['searchTag'])){
			$where = " AND tagName LIKE '%" . trim($_GET['searchTag']). "%'";
		}else{
			$where = "";
		}
		$qry = mysql_query("SELECT * FROM tagging__taggeditems WHERE pictureId=".$imageId_real . $where,$this->connection) or die(mysql_error());
		$tagList = array();
		while($tmp = mysql_fetch_assoc($qry))
		{
                        $price = "";
			switch($tmp["currentStatus"]){
				case "available" : $price = "$".sprintf("%01.2f", $tmp["price"]); break;
				case "free" : $price = "free"; break;
				case "sold" : $price = "$".sprintf("%01.2f", $tmp["price"]); break;				
			}	
			$tagId = $this->encdec->encrypt($tmp["tagId"]);
			$tmpArry = array(
			"tagOwnerId" =>$tmp["userId"], 
			"currLoggedUId" => $userId,
			"imageId"=>$imageHash,
			"itemDescription"=>stripslashes($tmp["desc"]),
			"itemName"=>$tmp["tagName"],
			"itemPrice"=>$price,
			"itemQty"=>$tmp["qty"],
			"itemStatus"=>$tmp["currentStatus"],
			"tagH"=>$tmp["height"],
			"tagId"=>$tagId,
			"noOfInterests" => $tmp["noOfInterests"],
			"tagW"=>$tmp["width"],
			"tagX"=>$tmp["tagPositionX"],
			"tagY"=>$tmp["tagPositionY"]);
			$tagList[] = $tmpArry;
		}
		return array("error"=>NULL,"tags"=>$tagList);
	}
	function deleteit($tagData){
		$imageHash = $tagData['imageId'];
		//validate user
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$this->userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$this->userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'User not authorized.');
		}
		
		if(!isset($_SESSION['recentUploads'][$imageHash]))
			return array("error"=>"No image for this hash ".$imageHash);
			
		$imageId_real =  $_SESSION['recentUploads'][$imageHash];
		$tmp_tagId = $this->encdec->decrypt($tagData['tagId']);
		
		$tagId_real = intval($tmp_tagId);
		if(!$this->authenticateImage($imageId_real))
			return array('error' => 'This is not your image, upload an image your self and tag.');
			
		if(!$this->authenticateTag($tagId_real,$imageId_real))
			return array('error' => 'This is not your tag, upload an image your self and tag.');
			
		$qry = "DELETE FROM tagging__taggeditems WHERE tagId=".$tagId_real." AND userId=".$this->userId." AND pictureId=".$imageId_real;
		mysql_query($qry,$this->connection) or die(mysql_error());
		
		$this->updateImageState($imageId_real);
		
		return array("error"=>NULL,"obj"=>$tagData);
		
	}
	function updateImageState($imageId)
	{
		$qry = mysql_query("SELECT * FROM tagging__taggeditems WHERE pictureId=".$imageId." AND userId=".$this->userId,$this->connection) or die(mysql_error());
		if(mysql_num_rows($qry) > 0)
			mysql_query("UPDATE tagging__pictures SET tagged=1 WHERE pictureId=".$imageId." AND userId=".$this->userId,$this->connection) or die(mysql_error());	
		else
			mysql_query("UPDATE tagging__pictures SET tagged=0 WHERE pictureId=".$imageId." AND userId=".$this->userId,$this->connection) or die(mysql_error());
	}
	function authenticateImage($imageId){
		$res = mysql_query("SELECT * FROM tagging__pictures WHERE pictureId=".$imageId." AND userId=".$this->userId,$this->connection);
		if(mysql_num_rows($res) > 0)
			return true;
		return false;
	}
	function authenticateTag($tagId,$imageId){
		$res = mysql_query("SELECT * FROM tagging__taggeditems WHERE pictureId=".$imageId." AND tagId=".$tagId." AND userId=".$this->userId,$this->connection);
		if(mysql_num_rows($res) > 0)
			return true;
		return false;
	}
}

$tag = new tagMeCall();
if($_POST['action'] == 'newTag')
$res = $tag->tagit($_POST);
else if ($_POST['action'] == 'editTag')
$res = $tag->editit($_POST);
else if ($_POST['action'] == 'deleteTag')
$res = $tag->deleteit($_POST);
else if ($_POST['action'] == 'loadTag')
$res = $tag->loadit($_POST);
else if ($_POST['action'] == 'publoadTag')
$res = $tag->publoadit($_POST);
echo json_encode($res);
?>