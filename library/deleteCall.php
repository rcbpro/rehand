<?php
session_start();
include '../_define.inc';
include 'database.php';
class Image{
	private $id;
	private $userId;
	private $tmpObj;
	private $db;
	function __construct($imgId){
		$this->db = database::getInstance();
		$this->id = @$_SESSION['recentUploads'][$imgId];
	}
	function delete(){
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$this->userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$this->userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'Delete error: User not authorized.');
		}		
		if(empty($this->id))
			return array('error' => 'Delete error: No image found.');
		if(!$this->authenticateImage())
			return array('error' => 'Delete error: This is not your image.');
		
		$imagepath = $this->tmpObj[0]['uploadLImgLocation'];
		$imagethumbpath = $this->tmpObj[0]['uploadTImgLocation'];
		
		if($this->db->query("UPDATE tagging__pictures SET deletedFlag = 1 WHERE pictureId=".$this->id." AND userId=".$this->userId))
			return array('error' => false,'success'=>true);
		else
			return array('error' => 'Delete error: User not authorized.');
	}
	function deleteTags(){
	}
	function authenticateImage(){
		$this->tmpObj = $this->db->query("SELECT * FROM tagging__pictures WHERE pictureId=".$this->id." AND userId=".$this->userId);
		if($this->db->num_rows() > 0)
			return true;
		return false;
	}
}
$image = new Image($_POST['imageHash']);
$res = $image->delete();

echo json_encode($res);

?>