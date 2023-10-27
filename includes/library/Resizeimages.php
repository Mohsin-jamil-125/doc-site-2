<?php
namespace App\includes\library;


class Resizeimages {
   public $phwidth;  
   public $phheight;
   public $phsize;
   public $image;
   public $image_type;

   public function __construct($mins) {
      $this->phwidth = $mins->width;
		$this->phheight = $mins->height;
      $this->phsize = $mins->size;     
   }

   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }

   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }

   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }

   function resize($width, $height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   } 
   
   
   function doimagesaaa($html_element_name, $target_dir, $new_img_width, $new_img_height) { 
    $target_file = $target_dir . basename($_FILES[$html_element_name]["name"]);
    $fileName = time().'_'.basename($_FILES[$html_element_name]["name"]);
   
    $targetFilePath = $target_dir. $fileName;
	 $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
	 $allowTypes = array('jpg','jpeg','png','JPG','JPEG');
   
   if(in_array($fileType, $allowTypes)){
      $this->load($_FILES[$html_element_name]['tmp_name']);
      $this->resize($new_img_width, $new_img_height);
      $this->save($target_file);
      return $target_file; //picfotoname
    }else{
      return 1;
    } 
} 


function check_imagesbefore($html_element_name){
   // $fileinfo = getimagesize($_FILES[$html_element_name]["tmp_name"]);
   if(!$fileinfo = getimagesize($_FILES[$html_element_name]["tmp_name"])){
      return array("type" => 2, "message" => "aproblemocc");
   }
    $width = $fileinfo[0];
    $height = $fileinfo[1];
    $allowed_image_extension = array("png", "PNG", "jpg", "JPG", "jpeg", "JPEG");
    
    //Get image file extension
    $file_extension = pathinfo($_FILES[$html_element_name]["name"], PATHINFO_EXTENSION);
    //Validate file input to check if is not empty
    if (! file_exists($_FILES[$html_element_name]["tmp_name"])) {
        $response = array(
            "type" => 2,
            "message" => "plselphot"
        );
    }    //Validate file input to check if is with valid extension 
    else if (! in_array($file_extension, $allowed_image_extension)) {
        $response = array(
            "type" => 2,
            "message" => "plsonlygif"
        );
        echo $result;
    }    // Validate image file size
    else if (($_FILES[$html_element_name]["size"] > $this->phsize )) {  //was5000000
        $response = array(
            "type" => 2,
            "message" => "photobigg"
        );
    }    // Validate image file dimension
    else if ($width < $this->phwidth || $height < $this->phheight) {
        $response = array(
            "type" => 2,
            "message" => "pictoosmall"
        );
    } else {
        $response = array(
                "type" => 1,
                "message" => "ok"
            );
       
      }
      return $response;
  }
  
function genRandomName($length = 5) {
    return substr(sha1(rand()), 0, $length);
}  

function doimages($html_element_name, $target_dir, $new_img_width, $new_img_height) {
 
    $fileName = time().'_'.str_replace(' ', '', basename($_FILES[$html_element_name]["name"]));
	 $target_file = $target_dir. $fileName;
    
    $chk = $this->check_imagesbefore($html_element_name);
    
    if($chk['type'] == 1 && $chk['message'] == "ok"){

    $this->load($_FILES[$html_element_name]['tmp_name']);

    $this->resize($new_img_width, $new_img_height);

    $this->save($target_file);
    //return $target_file; //picfotoname
    $response = array(
                "type" => 1,
                "message" => $fileName
            );
    
    } else {
       $response = $chk; 
    }
    
    return $response;

}
    

}

?>