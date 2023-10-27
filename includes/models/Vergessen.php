<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Vergessen extends Model {

   public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];  

    $this->text = array("title"=>e("Passwort vergessen"), "bottomPic"=>"/static/images/banners/people.png", "public_token"=>$this->config['public_token']);

    Main::pagetitle($this->text['title']);
  }


  public function vergessen($params = false){
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

        if(Main::csrfcheck($frm['token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Versuche es erneut!').";vergessen")));
        } 

        if($this->config['public_token'] != $frm['publictoken']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$recoveEmail = Main::email($frm['forgotmail'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültige E-Mail-Adresse!'))));
        }

        $now = time(); 
	      $valid_time = $now - (1 * 60 * 60);
        $kode = Main::randomString( 5 );

        $user = $this->db->get("allusers", array('select'=>'crt, userfstname, userlstname,  banned', 'where'=>array('isdel'=>2, 'usermail'=>$recoveEmail),'limit'=>1, 'return_type'=>'single'));
        if($user && $user['crt'] >= 1 && $user['banned'] == 2){

          $trys = $this->db->get("passrequests",  array('select'=>'crt', 'where'=>array('user_id' =>$user['crt']), 'compbig'=>array('time'=>$valid_time), 'return_type'=>'count'));        
          if (!$trys || ($trys && $trys <= 3)) {
            $insRequest =  $this->db->insert("passrequests", array('user_id'=>$user['crt'], 'kode'=>$kode, 'time'=>time()), TRUE);
 
            Main::cookie("precovers", $recoveEmail, 120);

            //$this->sendmail($recoveEmail, "Recover password ".$kode, "We have a password request from you . CODE.: ");

            if($emailDo = Main::emailCreate('recovery', array('b1link'=>'https://doc-site.de/vergessen', 'code'=>$kode, 'username'=>$user['userfstname'].' '.$user['userlstname']))){
              $this->sendmail($recoveEmail, $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
            }

            return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg').";/recover"))); 
          }else{
           // setcookie("precovers", "", time() - 3600, "/", DOMAIN, true, true); 
            setcookie("precovers","", [
              'expires' => time() - 3600,
              'path' => '/',
              'domain' => DOMAIN,
              'secure' => TRUE,
              'httponly' => FALSE,
              'samesite' => 'None',
            ]); 
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Momentan nicht möglich!'))));
          }
        }else{
         // setcookie("precovers", "", time() - 3600, "/", DOMAIN, true, true); 
          setcookie("precovers","", [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => DOMAIN,
            'secure' => TRUE,
            'httponly' => FALSE,
            'samesite' => 'None',
          ]); 
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Momentan nicht möglich!'))));
        } 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


}
?>