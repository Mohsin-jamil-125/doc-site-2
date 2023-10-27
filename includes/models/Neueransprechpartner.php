<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;
use App\includes\middlewares\Middleware;                                                                                                                           

class Neueransprechpartner extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];  

    $this->users = Main::user();

    $this->text = ["title"=>e("Neuer Ansprechpartner"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }



  public function addNeuerAnsprecher($params){
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

       
        $chPasswords = Main::comparePasswords($frm['ansppassword'], $frm['ansppasswordrep']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }
 
      $frm['usids'] = $thisUser;        


      $register = new Middleware($this->config, $this->db);
      if(!$r = $register->ansprechPartnerRegister($frm)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Registrierung fehlgeschlagen'))));
      }
      if($r && is_array($r) && array_key_exists("user_error", $r)){
        return $r;
      }

      return array("user_error"=>1, "user_reason"=>1, "regs"=>$r, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>"Ok!")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler') );
    }
  }


  public function editsAnsprech($params){  
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


      if(!$thisAnsprech = $this->prufAnsprechpartner($frm['frm_token'], TRUE)){ 
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }
  
  
      if($thisUser != $thisAnsprech[1]){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

 
      if($frm['ansppassword'] && $frm['ansppasswordrep']){
        $chPasswords = Main::comparePasswords($frm['ansppassword'], $frm['ansppasswordrep']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }
      }
 


      $frm['usids'] = $thisUser;        
      $frm['ansprechID'] = $thisAnsprech[0];

     // return array("user_error"=>1, "user_reason"=>2, "msg"=>$frm['ansprechID']." ".$frm['usids']);


      $register = new Middleware($this->config, $this->db);
      if(!$r = $register->ansprechPartnerEdit($frm)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Update fehlgeschlagen'))));
      }
      if($r && is_array($r) && array_key_exists("user_error", $r)){
        return $r;
      }

      return array("user_error"=>1, "user_reason"=>1, "regs"=>$r, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>"Ok!")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler'));
    }
  }



  

 
  public function ansprechUploadAvatar($files, $posted){
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

    $aiTokenAnsprech = filter_var($posted['regtoken'], FILTER_SANITIZE_STRING);
    if(!$thisAnsprech = $this->prufAnsprechpartner($aiTokenAnsprech)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_ANSPRECHPARTNERS.$thisAnsprech."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('ansprechpartners', array('select'=>'avatar', 'where' => array('crt'=>$thisAnsprech, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
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

    if($updPic = $this->db->update("ansprechpartners", array("avatar"=>$fileNamePhoto), array("crt"=>$thisAnsprech, 'usid'=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Logo erfolgreich gespeichert').";ansprechpartner")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
    
  }

 
    
 
 
 
 


}
?>