<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Dashboard extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];
  public $social = ['facebook', 'google', 'linkedin', 'instagram', 'xing', 'youtube'];
  public $eserv = ['host', 'port', 'pass', 'noreply'];
  public $euploads = ['width', 'height', 'size'];
  public $socialsAuthentication = ['Google', 'LinkedIn', 'Facebook', 'Instagram', 'Xing'];
  public $socialsFooter = ['facebook', 'linkedin', 'instagram', 'xing-square', 'twitter', 'youtube'];
  public $payMethods = ['card', 'sofort', 'giro', 'uberweiss', 'sepa', 'paypal'];



  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];
    

    $this->text = ["title" => e("Dashboard"), "pgname" => e("Dashboard"), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];

  }



  public function doWebSettings($params = false){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");

      

      if(isset($params->analiticscode)){
        $aKod = $params->analiticscode;
        $aKod =  $aKod->name == '' ? ' ' : $aKod->name;
        $ga = Main::analitics($aKod);
        unset($params->analiticscode);

      }

      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
			}

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}

      try{
        if(!$this->user = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($frm && array_key_exists("loginsetts", $frm) && array_key_exists("google",  $frm)){
          $frmSocs = array();
          foreach($this->social as $key){
            $frmSocs[$key] = $frm[$key];
            unset($frm[$key]);
          }
          $frm['linkSocials'] = json_encode($frmSocs);
        }

        if($frm && (array_key_exists("host",  $frm) || array_key_exists("noreply",  $frm))){
          $frmSocs = array();
          foreach($this->eserv as $key){
            if($key == "noreply") $frmSocs['user'] = $frm['noreply'];
            $frmSocs[$key] = $frm[$key];
            unset($frm[$key]);
          }
          $frm['smtp'] = json_encode($frmSocs);
        }

        if($frm && (array_key_exists("host",  $frm) || array_key_exists("noreply",  $frm))){
          $frmSocs = array();
          foreach($this->euploads as $key){
            $frmSocs[$key] = $frm[$key];
            unset($frm[$key]);
          }
          $frm['minUpload'] = json_encode($frmSocs);
        }

        if( !isset($frm['maintenance']) || !array_key_exists('maintenance', $frm)){ $frm['maintenance'] = 2; }


        if($frm && array_key_exists("loginwithsocials",  $frm) || array_key_exists("xing-square",  $frm) || array_key_exists("socialsregister",  $frm)){
          $frmSocsAuth = array();
          foreach($this->socialsAuthentication as $key){   
            $frmSocsAuth[$key] = $frm['login-'.$key];
            unset($frm[$key]);
          }
      
          $frmSocfooter = array();
          foreach($this->socialsFooter as $key){   
            $frmSocfooter[$key] = $frm[$key];
            unset($frm[$key]);
          }
         
          $frm['websettings'] = json_encode(array("registermail"=>$frm['registermail'], "wellcomemodal"=>$frm['wellcomemodal'], "topbar"=>$frm['topbar'], "socialsregister"=>$frm['socialsregister'], "socialslogin"=>$frm['socialslogin'], "socialsAuth"=>$frmSocsAuth, "socials"=>$frmSocfooter, "payments"=>$frm['payments'], "userprivate"=>$frm['userprivate'], "showbankverbindung"=>$frm['showbankverbindung'],
          "mailKandidatanlegen"=>$frm['mailKandidatanlegen'], "mailFirmanlegen"=>$frm['mailFirmanlegen'], "mailKandidateDelete"=>$frm['mailKandidateDelete'], "mailFirmaDelete"=>$frm['mailFirmaDelete']));
        }  


        if($frm && array_key_exists("post-Facebook",  $frm) || array_key_exists("post-Linkedin",  $frm)){
          $frm['doshare'] = json_encode(array("facebook"=>$frm['post-Facebook'], "linkedin"=>$frm['post-Linkedin'], "instagram"=>$frm['post-Instagram'], "xing"=>$frm['post-Xing'] ));
        }
       

      foreach($frm as $key => $updvalue){
        if($key == 'terms' || $key == 'privacy' || $key == 'wieesfunctioniert' || $key == 'uberuns' || $key == 'impressum'){
           $updSocials = $this->db->htmlupdate("webshave", array('var'=>$updvalue), array("config"=>$key), TRUE);
        }else{
          $updSocials = $this->db->update("setting", array('var'=>$updvalue), array("config"=>$key), TRUE);
        }
      }
        
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));

      }catch (Exception $e){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }


    public function doTestEmail($params = false){
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

        if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$email = Main::email($frm['testedEmailAddress'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültiges Email'))));
        }

        switch($frm['emailtesttype']){
          case "register":
            $emailDo = Main::emailCreate('register', array('b1link'=>'https://doc-site.de', 'b2link'=>'https://doc-site.de'));
            break;
          case "recovery":
            $emailDo = Main::emailCreate('recovery', array('code'=>'ax45W', 'b1link'=>'https://doc-site.de/vergessen'));
            break;
          case "confirm":
            $emailDo = Main::emailCreate('confirm', array('username'=>'Frau Marion Neumann', 'b1link'=>'https://doc-site.de/anmeldung', 'b2link'=>'https://doc-site.de/kontakt'));
            break;
          default:
          $emailDo = FALSE;
        }

        if($emailDo){
          if($p = $this->sendmail($email, $emailDo['subject'], $emailDo['message'],  $emailDo['template'])){
            $insTest = $this->db->insert("emailtest", array('usid'=>$thisUser, 'emailaddress'=>$email));

            return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
          } 
          $insTest = $this->db->insert("emailtest", array('usid'=>$thisUser, 'emailaddress'=>$email, 'status'=>1));

          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler! Momentan nicht möglich!"))));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option"))));
        }
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }

    public function doTestChat($params = false){
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

        if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($frm['chattestwhl'] == '--'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option"))));
        }

        if($frm['chattestwhl'] == 'selbst'){
          $sendTo = $frm['ai_token'];
        }elseif($frm['chattestwhl'] == 'alles'){
          $sendTo = 'docsite';
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option"))));
        }

        

      /* $batch = [];
        $batch[] = ['channel' => 'doc-site', 'name' => 'oAqp8cBHIOLbG3Nq4OQe', 'data' => $smPush];
        $batch[] = ['channel' => 'doc-site', 'name' => 'kaei7Y0q2Tj3OHs3Yk3P', 'data' => $smPush];   
        $batch[] = ['channel' => 'doc-site', 'name' => 'docsite', 'data' => ['message'=>'Herzlich willkommen bei Doc-Site!']];       
        $rex = chat('oAqp8cBHIOLbG3Nq4OQe', $smPush,  $batch);  //docsite kaei7Y0q2Tj3OHs3Yk3P     support == oAqp8cBHIOLbG3Nq4OQe 
        var_dump($rex); */ 

        $smPush= array(
          "chat-system"=>Main::config('title'),
          "message"=>$frm['testedChatText']
          );

        if($testChat = chat($sendTo, $smPush)){
            $insTest = $this->db->insert("chattest", array('usid'=>$thisUser, 'chattext'=>$frm['testedChatText']));

            return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
          } 
          $insTest = $this->db->insert("chattest", array('usid'=>$thisUser, 'chattext'=>$frm['testedChatText'], 'status'=>1));

          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler! Momentan nicht möglich!"))));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
        }
    }
 

    public function zugangsEdit($params = false){
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
                //setcookie("loowt", "", time() - 3600, "/", DOMAIN, true, true);  
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
            setcookie("loowt", "", time() - 3600, "/", DOMAIN, true, true);    
            session_destroy();
            return array("user_error"=>1, "user_reason"=>1, "sweets"=>"Bitte melden Sie sich erneut an!", "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg aktualisiert').";anmeldung")));
          }else{
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }
    }





    public function dodoChatSend($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if(!$this->user = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e("Fehler, versuche es erneut!"))));
        }

        // if(!$thisUser = $this->prufAltAdmin($params->doto)){
        //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"2 ".e('Fehler, versuche es erneut!'))));
        // }
  
        // if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
        //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"3 ".e('Fehler, versuche es erneut!'))));
        // }

       if(!$params->doto){
      //  if (!property_exists($params, 'doto') || !array_key_exists('doto', get_object_vars($params))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option")))); 
        }else{
          $sendTo = $params->doto; 
        }
  
        // doit: "fafaefwfewefw", doto: "kaei7Y0q2Tj3OHs3Yk3P" }

        if(!$userReceive = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$sendTo),'limit'=>1, 'return_type'=>'single'))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        } 
  
         $smPush= array(
          "chat-system"=>Main::config('title'),
          "from"=>$this->user[2],
          "message"=>$params->doit
          );
  
          if($testChat = chat($sendTo, $smPush)){
            $insChat = $this->db->insert("chats", array('fromid'=>$this->user[0], 'toid'=>$userReceive['crt'], 'chatmsg'=>$params->doit));

            return array("user_error"=>1, "user_reason"=>1, "msg"=>"Sended");
          } 
          return array("user_error"=>1, "user_reason"=>2, "msg"=>"4 ".e("Fehler, versuche es erneut!"));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>"5 ".e("Fehler, versuche es erneut!"));
        }
    }


    public function getChatsTalks($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if(!$this->user = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e("Fehler, versuche es erneut!"))));
        }


        if(!$userReceive = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$params),'limit'=>1, 'return_type'=>'single'))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        } 
 

        if(!$theChatList = $this->db->get("chats", array('select'=>'chatmsg, fromid, created', 'where'=>array('fromid'=>$this->user[0], 'toid'=>$userReceive['crt']), 'where-or-and'=>array('fromid'=>$userReceive['crt'], 'toid'=>$this->user[0]), 'limit'=>10, 'order_by'=>'crt DESC', 'return_type'=>'all'))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Keine Nachrichten!"))));
        } 

        $chat=[];
        $currDate=false;
        foreach(array_reverse($theChatList)  as $key => $value){
          if($value['fromid'] == $this->user[0]) {$send = 1;}else{ $send=2;}
          $chat[] = array("sended"=>$send, "text"=>$value['chatmsg'], "texttime"=>Main::timeAgo($value['created']));  // "currDate"=>$value['created']
          if(Main::dateTomorow($value['created'], $currDate)){ $chat[$key]['currDate'] = Main::nice_date($value['created'], TRUE); }//else{$chat['currDate'] = '';}

          $currDate = Main::dateTomorow($value['created']);
        }
        return array("user_error"=>1, "user_reason"=>1, "msg"=>$chat);
        
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>"5 ".e("Fehler, versuche es erneut!"));
      }
    }

    
    public function doAngebsett($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
          }

          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }

          if(!$frm['angbpaket'] || !$frm['angbpaket'] >= 1){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Wählen Sie den gewünschten Plan aus'))));
          }


          if(!$ang = $this->db->insert("angebots", array('ison'=>2, 'plannr'=>$frm['angbpaket'], 'messtext'=>$frm['angmessaj'], 'whatbuy'=>$frm['angkauff'], 'whatoffer'=>$frm['anggett'],
          'times'=>$frm['angtimes'],  'gultig'=>$frm['angdatum']))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }

          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!'))));
           
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }


    }
    
    public function doAngebotvieed($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
          }

          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }


          if( !isset($frm['pubangebot']) || !array_key_exists('pubangebot', $frm)){ $frm['pubangebot'] = 2; }
          if( !isset($frm['aktvangebot']) || !array_key_exists('aktvangebot', $frm)){ $frm['aktvangebot'] = 2; }  
           
 

          $updA = $this->db->update("setting", array('var'=>$frm['pubangebot']), array("config"=>"publicangebott"), TRUE);

          $updB = $this->db->update("setting", array('var'=>$frm['aktvangebot']), array("config"=>"angebott"), TRUE);

          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!'))));
           
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }

    
    public function doRabatDelete($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")."1")));
          }

          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."2")));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."3")));
          }
 
          $rabatpkt = isset($frm['wharabtloesch']) ? filter_var($frm['wharabtloesch'], FILTER_SANITIZE_NUMBER_INT) : "";
          if(!$rabatpkt || !$rabatpkt >= 1){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."3")));
          }
          
          if(!$delRabat = $this->db->update("rabatt", array('ison'=>1), array('crt'=>$rabatpkt))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."4"))); 
          }
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";payment")));
           
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }


    public function doRabathinzufungen($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")."1")));
          }

          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."2")));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."3")));
          }

          $frm['rabatnumberc'] = filter_var($frm['rabatnumberc'], FILTER_SANITIZE_STRING);
          $frm['rabatproc'] = filter_var($frm['rabatproc'], FILTER_SANITIZE_STRING);
          $rabatpkt = filter_var($frm['rabatpkt'], FILTER_SANITIZE_NUMBER_INT);

          
          if(strlen($frm['rabatnumberc']) != 6){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";payment")));
          }
          
          if(strlen($frm['rabatproc']) != 3){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Rabattwert ist falsch'))));
          }

          $rabat = $frm['rabatproc']."-".$frm['rabatnumberc'];
          if($isExists = $this->db->get("rabatt", array('select'=>'crt', 'where'=>array('ison'=>2, 'rabatt'=>$rabat), 'return_type'=>'single'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Rabattcode existiert bereits!'))));
          }


          if(!$insRabat = $this->db->insert("rabatt", array('ison'=>2, 'planid'=>$rabatpkt, 'rabatt'=>$rabat))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')."4"))); 
          }
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";payment")));
           
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }


    }


    public function doZahhll($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
          }

          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }

          $payms = array();
          foreach($this->payMethods as $key){   
            if( !isset($frm[$key]) || !array_key_exists($key, $frm)){ $frm[$key] = 2; }
            $payms[$key] = $frm[$key];
            unset($frm[$key]);
          }
          $paymsMethods = json_encode($payms);

          if( !isset($frm["doccurrency"]) || !array_key_exists("doccurrency", $frm)){ $frm["doccurrency"] = "EUR"; } 
          if( !isset($frm['rabatfiled']) || !array_key_exists('rabatfiled', $frm)){ $frm['rabatfiled'] = 2; }  

          $updPOayms = $this->db->update("setting", array('var'=>$paymsMethods), array("config"=>"paymethods"), TRUE);

          $zah = ['rabatt'=>$frm['rabatfiled'], 'doccurrency'=>$frm['doccurrency']  ];
          foreach($zah as $key => $val){   
            $updCurr = $this->db->update("setting", array('var'=>$val), array("config"=>$key), TRUE);
          }
  
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }


    public function doZahhllpaypal($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
          }


          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
 

          if( !isset($frm["paypal_email"]) || empty($frm["paypal_email"]) ){ 
            $updPOayms = $this->db->update("setting", array('var'=>$paymsMethods), array("config"=>"paymethods"), TRUE);
            $paymsMethods = json_encode($payms);

           }

 
          $updPaypal = $this->db->update("setting", array('var'=>$frm["paypal_email"]), array("config"=>"paypal_email"), TRUE);

          $updPayKey = $this->db->update("setting", array('var'=>$frm["paypal_token"]), array("config"=>"paypal_token"), TRUE);

          $updPayLiveP = $this->db->update("setting", array('var'=>$frm["paypal_sandbox"]), array("config"=>"paypal_sandbox"), TRUE);
        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }



    public function doZahhllrechser($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }
  
        try{
          if(!$this->user = Main::user()){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
          }


          if(!$thisUser = $this->prufUser($frm['ai_token'])){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
  
          if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
 

          $updPaypal = $this->db->update("setting", array('var'=>$frm["seriennr"]), array("config"=>"seriennr"), TRUE);

          $updPayKey = $this->db->update("setting", array('var'=>$frm["rechnrn"]), array("config"=>"rechnrn"), TRUE);

        }catch (Exception $e){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Aktualisiert!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
    }



/* Delete from Admin */
    public function doDeleteStellenangebote($params = false){
      if($params){
        header("Content-Type: application/json; charset=UTF-8");

        $idDelete = Main::decrypt($params);
        if(!$idDelete || !$idDelete >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        if(!$this->user = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }


        if($this->user[3] != 'admin'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($this->deltDoStellens($idDelete)){
          $del = $this->db->delete("paystats", array('anzid' => $idDelete));
          if($delStell = $this->db->delete("anzeigen", array('crt' => $idDelete))){
            $delBw = $this->db->delete("bewerbeliste", array('stellen' => $idDelete));

            $targetDir = UPLOAD_PATH_ANZEIGE.$idDelete."/"; 
            @Main::empty_directory(ROOT."/".$targetDir);
          }else{
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";/adminstellen")));
          }
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolgreich gelöscht!").";/adminstellen")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }
    }



    public function doDeleteUnternehm($params = false){
      header("Content-Type: application/json; charset=UTF-8");

      $idDelete = Main::decrypt($params);
      if(!$idDelete || !$idDelete >= 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if($this->user[3] != 'admin'){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      $del = array("allusers"=>"crt", "ansprechpartners"=>"usid", "anzeigen"=>"usid", "rechnungen"=>"usid", "payments"=>"usid", "paystats"=>"usid", "bewerbeliste"=>"usid", "firmsliebste"=>"usid", "chats"=>"fromid", "chats"=>"toid"); 

      $allAnzeigen = $this->db->get("anzeigen", array("select"=>"crt", 'where'=>array('usid'=>$idDelete), 'return_type'=>'all'));

      $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();

        foreach($del as $tbl => $col){
          $dellt = $this->db->delete($tbl, array($col=>$idDelete));
        }

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
       // die($e->getMessage());
       return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e("Fehler, versuche es erneut!").";/adminunternehmen")));
      }

      if($dellt){
        $targetDirVideo = UPLOAD_PATH_VIDEO.$idDelete."/";  
        @Main::empty_directory(ROOT."/".$targetDirVideo);

        $targetDirWall = UPLOAD_PATH_WALL.$idDelete."/";  
        @Main::empty_directory(ROOT."/".$targetDirWall);
     
        $targetDirAvatar = UPLOAD_PATH_AVATARS.$idDelete."/";  
        @Main::empty_directory(ROOT."/".$targetDirAvatar);
        
        $targetDirLogo = UPLOAD_PATH_LOGO.$idDelete."/";  
        @Main::empty_directory(ROOT."/".$targetDirLogo);

        if(isset($allAnzeigen)){
          foreach($allAnzeigen as $key => $vals){
            @Main::empty_directory(ROOT."/".UPLOAD_PATH_ANZEIGE.$vals['crt']."/");
          }
        }
      }

    return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolgreich gelöscht!").";/adminunternehmen")));
    }





  

}