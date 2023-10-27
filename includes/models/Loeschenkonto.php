<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\middlewares\Middleware;

class Loeschenkonto extends Model  {

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['kandidat'];

    $this->users = Main::user();

    $this->text = ["title"=>e("Account löschen"), "company"=>$this->accessLevel, "color"=>"dark", "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token']];
  }



  public function kontLoeschen($params = false){
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

        if(!array_key_exists("delmotiv", $frm) || (!$frm['delmotiv'] || !$frm['delmotiv'] >= 1)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Warum möchten Sie Ihren Account löschen?"))));
        } 

        $logged = new Middleware($this->config, $this->db);  
        if($actionCheck = $logged->checkUserPassword($thisUser, filter_var($frm['passwdLoesh'], FILTER_SANITIZE_STRING) )){
          if($actionCheck['result'] != 1) return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$actionCheck['msg']))); 
        }

        

        $pdo =  $this->db->getConnection();
        try {
          $pdo->beginTransaction();
          $frma = []; 
          $frma["usid"] = $thisUser;
          $frma["leveltype"] = $this->users[4];
          $frma["motiv"] = $frm['delmotiv'];
          $frma["descrmotiv"] = $frm['andereLoesh'] ? filter_var($frm['andereLoesh'], FILTER_SANITIZE_STRING) : '---';

          $insertReq = $this->db->insert("req_delete", $frma);

          $updusers = $this->db->update("allusers", array("on_line"=>2, 'banned'=>1), array("crt"=>$thisUser));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          die($e->getMessage());
        }

        if($insertReq){
          if($user = $this->db->get("allusers", array('select'=>'userfstname, userlstname, usermail, userphone', 'where'=>array('crt'=>$thisUser),'limit'=>1,'return_type'=>'single'))){								
            if(Main::websettings("loeschenmail") == 1){ $this->sendmail($user['usermail'], "New account delete", "WE ARE DOCSITE....account request delete ...OK"); }
              if(Main::websettings('mailKandidateDelete') == 1){ 
                  if($user['userphone']){$userphone = str_replace('_normal', '', $user['userphone']); }else{ $userphone = ""; } 
                  $emailDo = Main::emailCreate('email', array(     
                    'customsubject'=>'Doc-Site.: Ein Kandidat hat sein Konto gelöscht',
                    'username'=>$user['userfstname'] ." ".$user['userlstname'], 
                    'l1'=>"Grund.: ".$frma["motiv"]."<br/>Grunddetails.: ".$frma["descrmotiv"],
                    'l2'=>"Tel.: ".$userphone."<br/>Email.: ".$user['usermail'],
                    'b1'=>e('Hier anmelden'),
                    'b1link'=>Main::config('url').'/anmeldung',     
                    'l5'=>Main::config('title')
                  ));
              }

              //  $this->sendmail(Main::config('email'), "Kandidat delete request", "KANDIDAT....account request delete ...OK account ID.:".$thisUser);  mailKandidateDelete
              unset($_COOKIE['loowt']); 
              // setcookie("loowt", "", time() - 3600, "/", DOMAIN, true, true);  
              setcookie("loowt","", [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => DOMAIN,
                'secure' => TRUE,
                'httponly' => TRUE,
                'samesite' => 'None',
              ]);    
              session_destroy();
          
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Konto gelöscht!')))); 
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }

}



}
