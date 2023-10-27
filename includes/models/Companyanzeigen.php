<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Videoupload;  
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

class Companyanzeigen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    
    $this->accessLevel = $this->config['accessLevel']['firma'];  


    $this->text = ["title"=>e("Add anzeigen"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }



  public function insertNeuAnzeige($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if($selectsAnsprechers = Main::formMultiSelect($params, 'selectsAnsprechers')){
          unset($params->selectsAnsprechers);
        }else{
          $selectsAnsprechers = null;
        }
       
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }

        if(!array_key_exists('ai_token_plan', $frm)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }else{
          $tokenNr = explode('-@-', $frm['ai_token_plan']); // 0 billnr  [1] plan nr
          if(!$tokenNr[0] >= 1 || !$tokenNr[1] >= 1){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
        }

        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

       if(trim($frm['linkanzeigg'])){
        if(!$linkanzeigg = Main::is_url_extended($frm['linkanzeigg'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler! Ungültige URL!'))));
        }

        $parse = parse_url(rtrim($linkanzeigg,"/"));
        if(array_key_exists("path", $parse) || array_key_exists("query", $parse)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Geben Sie Ihren Domainnamen ein!'))));
        }

       }else{
        $linkanzeigg = "";
       }
        

        $selectsAnsprechers = $selectsAnsprechers ? serialize($selectsAnsprechers) : '';
        
        if($thecoords = Main::get_lat_long($frm['strasse'].' '.$frm['hausnr'].' '.$frm['citycode'].' '.$frm['city'], $frm['chooselandanzz'])){
          $thecoords = explode(',', $thecoords);
        } else{
          $thecoords = ['',''];
        } 

        $bnds = Main::derbundes($frm['chooserpp'], TRUE);

        $anzeige = array("usid"=>$thisUser, "ansprechs"=>$selectsAnsprechers, "titel"=>$frm['whatanztitel'], "berufsfeld"=>$frm['choosejob'], "positionen"=>$frm['positionjob'], "fachbereich"=>$frm['subjectjob'], "arts"=>$frm['chooseanstellung'], "vertrag"=>$frm['choosevetrag'], "filiale"=> $frm['whatfiliale'] ?? "",  "starts"=>Main::dbdate($frm['whatstartdate']), "citycode"=>$frm['citycode'], "city"=>$frm['city'], "derbundes"=>$bnds, "derland"=>$frm['chooselandanzz'], "latit"=>$thecoords[0], "longit"=>$thecoords[1], "anznr"=>$frm['hausnr'], "anzstr"=>$frm['strasse'], "beschreibung"=>$frm['bescreibung'], "besherwartet"=>$frm['besherwartet'] ?? "", "beshbieten"=>$frm['beshbieten'] ?? "", "beshwirerw"=>$frm['beshwirerw'] ?? "", "urlink"=>$linkanzeigg);  // "soclink"=>$frm['linksoccanzeigg']

        if(!$insAnzeige = $this->db->insert("anzeigen", $anzeige)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$insAnzeigeStattus = $this->db->insert("paystats", array("usid"=>$thisUser, "anzid"=>$insAnzeige, "ison"=>2, "planid"=>$tokenNr[1], "billnr"=>$tokenNr[0]))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        return array("user_error"=>1, "user_reason"=>1, "anzg"=>$insAnzeige, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg")))); 
        //  $doP = $this->prufPlans($thisUser, $insAnzeige);  /// ce facem daca e erroaaaare ??? 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


  public function anzUploadVideo($files, $posted){
    if(!isset($_FILES['viddeoFrmAnz']['name']) || empty($files['viddeoFrmAnz']['name']) || empty($files['viddeoFrmAnz']['tmp_name'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    }

    if(!$files['viddeoFrmAnz']['size'] || $files['viddeoFrmAnz']['size'] > 10485760){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('max.Dateigröße 10 MB'))));
    }

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    $thisIdNr = filter_var($_POST['anzregtoken'], FILTER_SANITIZE_NUMBER_INT);
    if(!$thisIdNr || !$thisIdNr >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." aqa")));
    }  

    $onyoutube = isset($_POST['onyoutube']) ? filter_var($_POST['onyoutube'], FILTER_SANITIZE_NUMBER_INT) : 2;

    $targetDir = UPLOAD_PATH_VIDEO.$thisIdNr."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }
   
    $video_ex = pathinfo($files['viddeoFrmAnz']['name'], PATHINFO_EXTENSION);
    $video_ex_lc = strtolower($video_ex);
    $allowed_exs = array('mp4');

    if (in_array($video_ex_lc, $allowed_exs)) {   
      $new_video_name = uniqid("video-", true).time().'.'.$video_ex_lc;
      $targetDir = UPLOAD_PATH_VIDEO.$thisIdNr."/";  
      if(!is_dir(ROOT."/".$targetDir)) {
        mkdir(ROOT."/".$targetDir);
      }else{
        if( $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }
      }
      if(!move_uploaded_file($files['viddeoFrmAnz']['tmp_name'], ROOT."/".$targetDir."/".$new_video_name)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler beim Hochladen der Videodatei!'))));
      }
    }else {
    	return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Erlaubtes Format: MP4'))));
    }

    if($updPic = $this->db->update("anzeigen", array("anzvideo"=>$new_video_name, "ytpromo"=>$onyoutube), array('crt'=>$thisIdNr, 'usid'=>$thisUser))){
      $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'));
      if( $stmt && strlen($stmt['anzvideo']) >= 5 ){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Video erfolgreich gespeichert').";company-anzeigen")));
      }else{
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Video erfolgreich gespeichert'))));
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }



  public function anzUploadLogo($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if(!$posted['regtoken'] || empty($posted['regtoken'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $thisIdNr = filter_var($posted['regtoken'], FILTER_SANITIZE_NUMBER_INT);
    if(!$thisIdNr || !$thisIdNr >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_ANZEIGE.$thisIdNr."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('anzeigen', array('select'=>'anzlogo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
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

    if($updPic = $this->db->update("anzeigen", array("anzlogo"=>$fileNamePhoto), array('crt'=>$thisIdNr, 'usid'=>$thisUser))){
      $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'));
      if( $stmt && strlen($stmt['anzvideo']) >= 5 ){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Logo erfolgreich gespeichert').";company-anzeigen")));
      }else{
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Logo erfolgreich gespeichert'))));
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }




}
?>