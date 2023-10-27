<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

class Companyprofil extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['firma'];  

    $this->text = ["title"=>e("Mein Profil"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }


  public function frmUploadBild($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    // alte  cropimage 


    $targetDir = UPLOAD_PATH_LOGO.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('firmen', array('select'=>'frmavatar', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }

    $resize = new ResizeImages(json_decode($this->config["minUpload"]));   
    $ismoved = $resize->doimages("cropimage", ROOT."/".$targetDir, 350, 350);
    if($ismoved['type'] == 2){                        
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." ".$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("firmen", array("frmavatar"=>$fileNamePhoto), array("usid"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich gespeichert').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }


  public function firmaEinrichtungEdit($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!Main::atLeastOneChecked($frm,  ["intersartze", "intersnurse", "interspharma"])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Welche Interessen haben Sie?').";company-profil")));
        }


        $intersartze = array_key_exists("intersartze", $frm) ? $frm['intersartze'] : 2;
        $intersnurse = array_key_exists("intersnurse", $frm) ? $frm['intersnurse'] : 2;
        $interspharma = array_key_exists("interspharma", $frm) ? $frm['interspharma'] : 2;

        $medsfachs = array_key_exists("medsfachs", $frm) ? $frm['medsfachs'] : 2;
        $therafachs = array_key_exists("therafachs", $frm) ? $frm['therafachs'] : 2;
        $verwafach = array_key_exists("verwafach", $frm) ? $frm['verwafach'] : 2;
        $techfach = array_key_exists("techfach", $frm) ? $frm['techfach'] : 2;
        $sonstiges = array_key_exists("sonstiges", $frm) ? $frm['sonstiges'] : 2;

        if(array_key_exists("ufrmsocials", $frm) && $frm['ufrmsocials']){
         $ufrmLinks = $frm['ufrmsocials']; 
        }else{
          $ufrmLinks = ''; 
        }

        if(trim($frm['ufrmlinkk'])){
          if(!$ufrmlinkk = Main::is_url_extended($frm['ufrmlinkk'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler! Ungültige URL!').";company-profil")));
          }
          $parse = parse_url(rtrim($ufrmlinkk,"/"));
          if(array_key_exists("path", $parse) || array_key_exists("query", $parse)){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Geben Sie Ihren Domainnamen ein!').";company-profil")));
          }

         }else{
          $ufrmlinkk = "";
         }

 
        $firmen = array("frmname"=>$frm['frmfirmaname'], "frmsteuer"=>$frm['frmsteuer'], "frmregister"=>$frm['frmregister'] ?? "", "frmeinricht"=>$frm['frmeinricht'], "intersartze"=>$intersartze, "intersnurse"=>$intersnurse, "interspharma"=>$interspharma, "medsfachs"=>$medsfachs, "therafachs"=>$therafachs, "verwafach"=>$verwafach, "techfach"=>$techfach, "sonstiges"=>$sonstiges, "frmbeschtitl"=>$frm['frmbeschrttl'], "frmbeschreibb"=>$frm['frmbeschreibb'], "ufrmlinkk"=>$ufrmlinkk, "ufrmlinksocc"=>$ufrmLinks); 
         

        if($updFirmen = $this->db->update("firmen", $firmen, array('usid'=>$thisUser))){
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg aktualisiert').";company-profil"))); 
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }

  public function firmAnsprechEdit($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

      
        $phone = $frm['frmphcode']."-#@@#-".$frm['frmphone'];  

        $frmpreffs = array("anrede"=>$frm['anrede'], "vorname"=>$frm['vorname'], "name"=>$frm['name'], "position"=>$frm['position'], "telefon"=>$phone, "hausnr"=>$frm['hausnr'], "strasse"=>$frm['strasse'], "citycode"=>$frm['citycode'], "city"=>$frm['city']); 

        if(Main::websettings('showbankverbindung') == 1){
          $bankDetails = ["inhaber"=>$frm['inhaber'], "bankname"=>$frm['bankname'], "swift"=>$frm['swift'], "iban"=>$frm['iban']];
          array_merge($frmpreffs, $bankDetails);
        }

   
        $usersTable = array("userfstname"=>$frm['vorname'], "userlstname"=>$frm['name'], "userphone"=>$phone);

        $pdo =  $this->db->getConnection();
        try {
          $pdo->beginTransaction();

          $updPersonal = $this->db->update("allusers", $usersTable, array('crt'=>$thisUser));

          $updatePreffs = $this->db->update("frmpreffs", $frmpreffs, array('usid'=>$thisUser));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
        }
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg aktualisiert')))); 
    }else{
      //return array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr");
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


  
  public function frmUploadVid($files, $posted){
    if(!isset($_FILES['viddeoFirma']['name']) || empty($files['viddeoFirma']['name']) || empty($files['viddeoFirma']['tmp_name'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    }

    if(!$files['viddeoFirma']['size'] || $files['viddeoFirma']['size'] > 10485760){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('max.Dateigröße 10 MB'))));
    }

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $onyoutube = isset($_POST['onyoutube']) ? filter_var($_POST['onyoutube'], FILTER_SANITIZE_NUMBER_INT) : 2;
    $theTitle = isset($_POST['frmvideotitel']) ? filter_var($_POST['frmvideotitel'], FILTER_SANITIZE_STRING) : "";

    $targetDir = UPLOAD_PATH_VIDEOFIRM.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('firmen', array('select'=>'frmvideo', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }


    $video_ex = pathinfo($files['viddeoFirma']['name'], PATHINFO_EXTENSION);
    $video_ex_lc = strtolower($video_ex);
    $allowed_exs = array('mp4');

    if (in_array($video_ex_lc, $allowed_exs)) {   
      $new_video_name = uniqid("video-", true).time().'.'.$video_ex_lc;
      $targetDir = UPLOAD_PATH_VIDEOFIRM.$thisUser."/";  
      if(!is_dir(ROOT."/".$targetDir)) {
        mkdir(ROOT."/".$targetDir);
      }else{
        if( $stmt = $this->db->get('firmen', array('select'=>'videotube', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }
      }
      if(!move_uploaded_file($files['viddeoFirma']['tmp_name'], ROOT."/".$targetDir."/".$new_video_name)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler beim Hochladen der Videodatei!'))));
      }
    }else {
    	return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Erlaubtes Format: MP4'))));
    }

    if($updPic = $this->db->update("firmen", array("videotube"=>$onyoutube, "frmvideotitel"=>$theTitle, "frmvideo"=> $new_video_name), array("usid"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolgreich gespeichert'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }

 /*
  public function frmUploadLogo($files, $posted){
    if(!isset($files['logoFirma']['name']) || empty($files['logoFirma']['name']) || !$files['logoFirma']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_LOGO.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('firmen', array('select'=>'frmavatar', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }

    $resize = new ResizeImages(json_decode($this->config["minUpload"]));   
    $ismoved = $resize->doimages("logoFirma", ROOT."/".$targetDir, 350, 350);
    if($ismoved['type'] == 2){                        
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." ".$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("firmen", array("frmavatar"=>$fileNamePhoto), array("usid"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich gespeichert').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }  */

 
  public function frmUploadAvatar($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_AVATARS.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('allusers', array('select'=>'avatar', 'where' => array('crt'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }

    $resize = new ResizeImages(json_decode($this->config["minUpload"]));   
    $ismoved = $resize->doimages("cropimage", ROOT."/".$targetDir, 350, 350);
    if($ismoved['type'] == 2){                        
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!').$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("allusers", array("avatar"=>$fileNamePhoto), array("crt"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich gespeichert').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }  


  public function frmUploadWall($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_WALL.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('firmen', array('select'=>'frmawall', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }

    $resize = new ResizeImages(json_decode($this->config["minWallUpload"]));   
    $ismoved = $resize->doimages("cropimage", ROOT."/".$targetDir, 1920, 768);
    if($ismoved['type'] == 2){                        
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!').$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("firmen", array("frmawall"=>$fileNamePhoto), array("usid"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich gespeichert').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }

  public function deletefrmwall($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    if(!$stmt = $this->db->get("firmen", array('select'=>'frmawall', 'where'=>array('usid'=>$id),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if($stmt = $this->db->update("firmen", array("frmawall"=>NULL), array("usid"=>$id))){
      $targetDir = UPLOAD_PATH_WALL.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }

  

  public function deletefrmlogo($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    if(!$stmt = $this->db->get("firmen", array('select'=>'frmavatar', 'where'=>array('usid'=>$id),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if($stmt = $this->db->update("firmen", array("frmavatar"=>NULL), array("usid"=>$id))){
      $targetDir = UPLOAD_PATH_LOGO.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


  public function deletefrmavatar($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
    }

    if(!$stmt = $this->db->get("allusers", array('select'=>'avatar', 'where'=>array('crt'=>$id),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!').$id)));
    }

    if($stmt = $this->db->update("allusers", array("avatar"=>NULL), array("crt"=>$id))){
      $targetDir = UPLOAD_PATH_AVATARS.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg').";company-profil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }











}
?>