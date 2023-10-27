<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\middlewares\Middleware;
use App\includes\library\cPanelApi;

class Companyeinstellungen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->users = Main::user();

    $this->text = array("title"=>e("Firma einstellungen"), "company"=>$this->accessLevel, "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'], "color"=>"dark");
  }



  public function getMails(){
    if(!$m = $this->doMailsLists()){
      $this->text['mailsList'] =  FALSE;
    }
    $this->text['mailsList'] =  $m;
  }


  public function doCreateFirmaEmails($params = false){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");
      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
			}

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}

 
      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if(!$thisUser = $this->prufUser($frm['ai_token'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(($this->user[0] != $thisUser) || $this->user[3] != 'firma'){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(!$email = Main::email($frm['adnnewmailtest']."@".DOMAIN)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültiges Email'))));
      }

      if($email){
        $password = Main::strrand(8);
        $api = new cPanelApi(DOMAIN, DOC_APIUS_SECRET, DOC_API_SECRET);
        if(!$theApi = $api->createEmail($email, $password, $this->config['frmemailsgb'] )){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        // $apiAct = json_decode($theApi, true);
        // if($apiAct->act != "success"){
        //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        // }

         $stmt = $this->db->update("allusers", array('docmail'=>$email), array('crt'=>$thisUser, 'isdel'=>2));

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg").";/company-einstellungen")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option"))));
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }




  public function firmaPrivacyEdit($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!') )));
        }


        $privacyFields = array("newsletter", "sounds", "vorname", "tagdatum", "monatdatum", "jahrdatum", "profilbild", "video", "lebenslauf", "weiteres");

        if(!$privacy = Main::radiochecks($privacyFields, $frm, TRUE)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        if(array_key_exists("chat", $frm)){ $ch = 2;}else{$ch = 1;}
        if(array_key_exists("telefon", $frm)){ $tf = 2;}else{$tf = 1;}
        if(array_key_exists("mail", $frm)){ $ml = 2;}else{$ml = 1;}

        if($updatePreffs = $this->db->update("frmpreffs", array("telpref"=>$tf, "chatpref"=>$ch, "mailpref"=>$ml, "preffs"=>$privacy), array('usid'=>$thisUser))){
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg aktualisiert'))));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


 
  public function firmaZugangsEdit($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert')
        )));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        $cpw = new Middleware($this->config, $this->db);
        $updEmail = false;
        if(array_key_exists("akkmail", $frm)){
          if(!filter_var($frm['akkmail'], FILTER_VALIDATE_EMAIL)){ 
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("E-Mail-Adresse ist falsch!"))));
          }else{
            if(!$cpw->emailExists($frm['akkmail'], TRUE)){ 
              return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail bereits registriert oder nicht korrekt!'))));
            }
            $updEmail = filter_var($frm['akkmail'], FILTER_SANITIZE_EMAIL);
          }
        }

        if(array_key_exists("jobakktlpasswd", $frm)){
          if(!$frm['jobakktlpasswd'] || empty($frm['jobakktlpasswd'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Aktuelles Passwort einfuegen"))));
          }

          
          if($passChecked = $cpw->checkUserPassword($thisUser, $frm['jobakktlpasswd'])){
            if($passChecked['result'] == 3){
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
              return array("user_error"=>1, "user_reason"=>2, "sweets"=>$passChecked['msg'], "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>$passChecked['msg'].";anmeldung")));
            }elseif($passChecked['result'] == 2){
              return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$passChecked['msg'])));
            }
          }else{
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
        }

        $chPasswords = Main::comparePasswords($frm['jobneupasswd'], $frm['reppjobneupasswd']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }

        $ps = Main::passEncode($frm['jobneupasswd']);
        $auth_key = Main::encode($this->config["security"].Main::strrand());
        $unique = Main::strrand(20);

        if($updEmail){
          $usrArr = array("usermail"=>$updEmail, "userpass"=>$ps["p"], "usersalt"=>$ps["s"], "pwchanged"=>1, "auth_key"=>$auth_key, "unique_key"=>$unique);
        }else{
          $usrArr = array("userpass"=>$ps["p"], "usersalt"=>$ps["s"], "pwchanged"=>1, "auth_key"=>$auth_key, "unique_key"=>$unique);
        }

        if($updatePassword = $this->db->update("allusers", $usrArr, array('crt'=>$thisUser))){
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
          return array("user_error"=>1, "user_reason"=>1, "sweets"=>"Bitte melden Sie sich erneut an!", "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg aktualisiert').";anmeldung")));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }





}
?>