<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Recover extends Model {

   public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];  

    $this->text = array("title"=>e("Passwort recover"), "bottomPic"=>"/static/images/banners/doctors.png", "precovers"=>isset($_COOKIE['precovers']) ? $_COOKIE['precovers']: null, "public_token"=>$this->config['public_token']);

    Main::pagetitle($this->text['title']);
  }


  public function recoveryPassword($params = false){
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

        if(!$recoveEmail = Main::email($frm['codemail'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültige E-Mail-Adresse!'))));
        }

        $kodInserted = $frm['one'].$frm['two'].$frm['three'].$frm['four'].$frm['five'];

        $user = $this->db->get("allusers", array('select'=>'crt, banned, unique_key', 'where'=>array('isdel'=>2, 'usermail'=>$recoveEmail),'limit'=>1, 'return_type'=>'single'));
        if($user && $user['crt'] >= 1 && $user['banned'] == 2){
          $kode = $this->db->get("passrequests",  array('select'=>'kode', 'where'=>array('user_id' =>$user['crt']), 'order_by'=>'crt DESC', 'limit'=>1, 'return_type'=>'single'));        
          if ($kode && ( strtolower($kode['kode']) == strtolower($kodInserted) )) {
            if($userUpd = $this->db->update("allusers", array("on_line"=>2, "activated"=>2),  array('crt'=>$user['crt'], 'isdel'=>2))){
              $clearRequests = $this->db->delete("passrequests", array('user_id'=>$user['crt']));

              $decoy = Main::randomString( 5 ); 
              Main::cookie("precovers", $decoy.$user['unique_key'], 120);

              return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg;/neupasswort')))); 
            }
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Geben Sie die Daten richtig ein!'))));
          }else{
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Geben Sie die Daten richtig ein!'))));
          }
        }else{ 
         // setcookie("precovers", "", time() - 3600, "/", DOMAIN, true, true);
          setcookie("precovers","", [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => DOMAIN,
            'secure' => TRUE,
            'httponly' => TRUE,
            'samesite' => 'None',
          ]); 
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Geben Sie die Daten richtig ein!').';/anmeldung')));
        } 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }




}
?>