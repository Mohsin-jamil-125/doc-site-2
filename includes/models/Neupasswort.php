<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Neupasswort extends Model {

   public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];  

    $this->text = array("title"=>e("Neu Passwort"), "bottomPic"=>"/static/images/banners/doctors.png", "precovers"=>isset($_COOKIE['precovers']) ? $_COOKIE['precovers']: null, "public_token"=>$this->config['public_token']);
  }


  public function neuPassword($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }

        if(!$user_browser = $_SERVER['HTTP_USER_AGENT']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($this->config['public_token'] != $frm['publictoken']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


       if(!$frm['tokenpublic'] ||  !$tokenpublic = substr($frm['tokenpublic'], 5)){
         return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
       } 

       if(!$thisUser = $this->prufOpen_User($tokenpublic)){  
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

        $chPasswords = Main::comparePasswords($frm['nneupaswrt'], $frm['repnneupaswrt']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }


        $ps = Main::passEncode($frm['nneupaswrt']);
        $auth_key = Main::encode($this->config["security"].Main::strrand());
        $unique = Main::strrand(20);

        if($updatePassword = $this->db->update("allusers", array("userpass"=>$ps["p"], "usersalt"=>$ps["s"], "auth_key"=>$auth_key, "unique_key"=>$unique, "activated"=>1), array('crt'=>$thisUser))){
          //setcookie("precovers", "", time() - 3600, "/", DOMAIN, true, true); 
          setcookie("precovers","", [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => DOMAIN,
            'secure' => TRUE,
            'httponly' => TRUE,
            'samesite' => 'None',
          ]);  
          return array("user_error"=>1, "user_reason"=>1, "sweets"=>"Jetzt können Sie sich anmelden", "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";anmeldung")));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }




}
?>