<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

class Ansprechpartner extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];  

    $this->users = Main::user();

    $this->text = ["title"=>e('Mein Profil'), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'],
    "titel"=>array(e('Aussetzen'), e('Loeschen'), e('Konto aktivieren')), "info"=>array(e('Dieses Konto wird gesperrt und ist im Doc-Site-System nicht aktiv'), e('Dieses Konto wird gelöscht und alle zugehörigen Daten gehen verloren!'), e('Dieses Konto wird aktiviert'))];
  }


  public function suspendAnsprechModal($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");
       

      if(!$thisUser = $this->prufAnsprechpartner($params, TRUE)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($thisUser[1] != $this->users[0]){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($upd = $this->db->update("ansprechpartners", array("activated"=>0), array('crt'=>$thisUser[0]))){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>"Erfolg;ansprechpartner")));
      }
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }


  public function enableAnsprechModal($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");
       

      if(!$thisUser = $this->prufAnsprechpartner($params, TRUE)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($thisUser[1] != $this->users[0]){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($upd = $this->db->update("ansprechpartners", array("activated"=>1), array('crt'=>$thisUser[0]))){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";ansprechpartner")));
      }

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }


  public function deleteAnsprechModal($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");
       
      if(!$thisUser = $this->prufAnsprechpartner($params, TRUE)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($thisUser[1] != $this->users[0]){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      $targetDir = UPLOAD_PATH_ANSPRECHPARTNERS.$thisUser[0]."/";  

      $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();

        if( is_dir(ROOT."/".$targetDir) && $this->db->get('ansprechpartners', array('select'=>'avatar', 'where' => array('crt'=>$thisUser[0]), 'limit' => 1, 'return_type' => 'single'))  ){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }


        if($allInAnzeigens = $this->db->get("anzeigen", array("select"=>"crt, ansprechs", 'where'=>array('usid'=>$this->users[0]), 'return_type'=>'all'))){
          foreach($allInAnzeigens as $key => $vals){
            if($anspr = unserialize($vals['ansprechs'])){
              foreach($anspr as $k => $val){
                if($params == $val){ unset($anspr[$k]); }
              }
              $anspr = array_values($anspr);
              $anspr = serialize($anspr);

              $putBack = $this->db->update("anzeigen", array("ansprechs"=>$anspr), array('crt'=>$vals['crt']));
            }
          }
        }

        $dell = $this->db->delete("ansprechpartners", array('crt'=>$thisUser[0]));


        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        die($e->getMessage());
      }
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";ansprechpartner"))); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }



  public function deleteAnsprechBild($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"Access denied1")));
    }

 
    if(!$thisAnsprech = $this->prufAnsprechpartner($params, TRUE)){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if($this->users[0] != $thisAnsprech[1]){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if($stmt = $this->db->update("ansprechpartners", array("avatar"=>NULL), array('crt'=>$thisAnsprech[0], 'usid'=>$thisAnsprech[1]))){
      $targetDir = UPLOAD_PATH_ANSPRECHPARTNERS.$thisAnsprech[0]."/";  
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";/edit-ansprechpartner/".$params)));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }



  public function ansprechEditBild($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $photosElToken = filter_var($alte[2], FILTER_SANITIZE_STRING);
    if(!$thisAnsprech = $this->prufAnsprechpartner($photosElToken)){ 
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." b")));
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
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." xx ".$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("ansprechpartners", array("avatar"=>$fileNamePhoto), array('crt'=>$thisAnsprech, 'usid'=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich gespeichert').";/edit-ansprechpartner/".$photosElToken)));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." e")));  
    }
    
  }




  






}
?>