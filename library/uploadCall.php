<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 'off');
include '../_define.inc';
include '../library/simpleImage.php';
include '../library/database.php';

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path,$filename,$ext,$thumbfolder) {
		    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path.$filename."_tmp.".$ext, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
		
		$imageinfo = getimagesize($path.$filename."_tmp.".$ext);
		$imageHeight = $imageinfo[1];
		$imageWidth = $imageinfo[0];
		
		if ($imageWidth < 300) return false;
		
		$image = new SimpleImage();
   		$image->load($path.$filename."_tmp.".$ext);
		unlink($path.$filename."_tmp.".$ext);
		
		if($imageWidth > 900)
			$image->resizeToWidth(900);
        $image->save($path.$filename.".".$ext);
		$image->resizeToWidth(300);
		$image->save($thumbfolder.$filename.".".$ext);
		$image = NULL;
		
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path,$filename,$ext,$thumbfolder) {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path.$filename."_tmp.".$ext)){
            return false;
        }
		
		$imageinfo = getimagesize($path.$filename."_tmp.".$ext);
		$imageHeight = $imageinfo[1];
		$imageWidth = $imageinfo[0];
		
		if ($imageWidth < 300) return false;
		
		$image = new SimpleImage();
   		$image->load($path.$filename."_tmp.".$ext);
		unlink($path.$filename."_tmp.".$ext);
		if($imageWidth > 900)
			$image->resizeToWidth(900);			
        $image->save($path.$filename.".".$ext);
		$image->resizeToWidth(300);
		$image->save($thumbfolder.$filename.".".$ext);
		
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 6291456;
    private $file,$userId,$connection;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 6291456){  
		
		$allowedExtensions = array_map("strtolower", $allowedExtensions);
        
		$this->DbObject = database::getInstance();
		/*
		$this->connection = mysql_connect("localhost", "root", "root");
		if ($this->connection) mysql_select_db("rehand") or die(mysql_error());
		*/
   		
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
		
		if ((isset($_SESSION['currentUser'])) || (isset($_SESSION['fbUser']))){
			if (isset($_SESSION['currentUser'])){
				$userId = $_SESSION['currentUser']['userId'];
			}
			if (isset($_SESSION['fbUser'])){
				$userId = $_SESSION['fbUser']['userId'];
			}			
		}else{
			return array('error' => 'User not authorized.');
		}
		
		$uploadDirectory = $uploadDirectory.$userId.DS;
		$oriUrl = $uploadDirectory;
		$oriUrl = str_replace($_SERVER['DOCUMENT_ROOT'].DS."www.rehand.com".DS,"",$oriUrl);
		$oriUrl = str_replace(DS,PS,$oriUrl);
		if (!is_dir($uploadDirectory))
			mkdir($uploadDirectory,0755,true);
				
		$thumbnailDirectory = $uploadDirectory."thumbnails".DS;
		$thumbUrl = $oriUrl."thumbnails".PS;
		if (!is_dir($thumbnailDirectory))
			mkdir($thumbnailDirectory,0755,true);
		
		if (!is_dir($uploadDirectory)){
			return array('error' => "Server error. No directory @. -".$uploadDirectory);
		}
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable. -".$uploadDirectory);
        }
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }		
        
        $pathinfo = pathinfo($this->file->getName());
		
		$Max_Pic_IdTemp = $this->DbObject->query("SELECT MAX(pictureId) AS Max_Pic_Id FROM tagging__pictures ORDER BY uploadedDateTime DESC");
		$Max_Pic_Id = $Max_Pic_IdTemp[0]['Max_Pic_Id'];
		//$Max_Pic_Id = mysql_result(mysql_query("SELECT MAX(pictureId) AS Max_Pic_Id FROM tagging__pictures ORDER BY uploadedDateTime DESC", $this->connection), 0);
		
        //$filename = $pathinfo['filename'];
        $filename = "pic_".($Max_Pic_Id+1);
		
		$ext = $pathinfo['extension'];

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if (!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory , $filename , $ext,$thumbnailDirectory)){
            $res['success'] = true;
			
			$query = "INSERT INTO tagging__pictures(title, userId, uploadLImgLocation, uploadTImgLocation, uploadedDateTime) 
					  VALUES('" . $filename . "', " . $userId . " ,'" . mysql_real_escape_string($oriUrl.$filename.".".$ext) . "' ,'" . mysql_real_escape_string($thumbUrl . $filename.".".$ext) . "', '".date('Y-m-d')."')";
					  
			//mysql_query($query, $this->connection) or die(mysql_error($this->connection));
			$this->DbObject->query($query);
			$lastId = $this->DbObject->getLastInisertedId();
			//$lastId = mysql_insert_id($this->connection);
			$res['imageId'] = md5($filename . $lastId);
			$_SESSION['recentUploads'][$res['imageId']] = $lastId;		
			return $res;
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array("jpeg","jpg","gif","png","bmp");
// max file size in bytes
$max_upload = (int)(ini_get('upload_max_filesize'));
$sizeLimit = ($max_upload - 2) * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload($_SERVER['DOCUMENT_ROOT'].DS."www.rehand.com".DS."uploaded".DS."taggings".DS);
// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);