<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\Tools;
use App\includes\middlewares\Middleware;

use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;

use Hybridauth\Hybridauth;


class Anmeldung extends Model  {

  protected $logged = FALSE;
  protected $socialsConfig = FALSE;
  protected $hybridauth;
  protected $adapters;
	protected $admin = FALSE, $userid = "0";
	protected $id, $ansprechId, $ansprecheralias, $frmalias, $loggedLevel, $membership, $expires, $email, $firstname, $lastname, $password, $salz, $auth_key, $unique_key, $userverified, $userbanned, $activated;
  protected  $socialslogin, $socialsloginAllowed;
  public $datas;
 

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->socialslogin = Main::websettings('socialslogin');
    $this->socialsloginAllowed = Main::websettings('socialsAuth');
    $this->text = array("title"=>e("Login"), "bottomPic"=>"/static/images/banners/people.png", "socialsloginAllowed"=>$this->socialsloginAllowed, "socialslogin"=>$this->socialslogin, "public_token"=>$this->config['public_token'], "aa"=>$this->config);

    Main::pagetitle($this->text['title']);

    if($this->socialslogin == 1){
      include SOCIALAUTH.'autoload.php';
      $this->socialsConfig = $this->config['socialsAuth'];

      $this->hybridauth = new Hybridauth($this->socialsConfig);         /// json_decode($this->config['websettings'], true); 
      $this->adapters = $this->hybridauth->getConnectedAdapters();
      foreach ($this->hybridauth->getProviders() as $name) {
        if (!isset($this->adapters[$name]) ){
          if($name === "Xing"){
            $this->text['socials'][$name] = "socialNet?prov=Xing";
          } else {
            $this->text['socials'][$name] = $this->socialsConfig['callback'] . "?provider=".$name;
          }
        }
      }
    }
  }


  public function pubDoBewerbt($files, $posted){
    if(!isset($files['lebensKandidatBewerbS']['name']) || empty($files['lebensKandidatBewerbS']['name']) || !$files['lebensKandidatBewerbS']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler!Nichts hochzuladen!"))));
    } 

    $aiToken = filter_var($posted['publictoken'], FILTER_SANITIZE_STRING);
    if($aiToken !=  $this->config['public_token']){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if(!$tokenWahat = Main::decrypt($posted['tokenpublic'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ihre E Mail adresse ist falsch!'))));
    }
    if(!$tokenWahat >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if(!$theMail = Main::email($posted['bewreeeusmail'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ihre E Mail adresse ist falsch!'))));
    }

    if(!$theRepMail = Main::email($posted['beweeusermail'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ihre E Mail adresse ist falsch!'))));
    }

    if($theMail != $theRepMail){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail-Adressen stimmen nicht überein!'))));
    }

    $bExtra = isset($posted['sofbewdescr']) ? filter_var($posted['sofbewdescr'], FILTER_SANITIZE_STRING) : FALSE;


      try{  
        if(Main::csrfcheck($posted['token'])){
         // return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Registrieren fehlgeschlagen').";registrieren")));
        } 

        if(!$onWhat = $this->db->get("anzeigen",  array("select"=>"anzeigen.crt, anzeigen.titel, firmen.usid, firmen.frmname", "join"=>array(
          array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"anzeigen.usid"))),
          'where'=>array('anzeigen.crt'=>$tokenWahat), 'limit'=>1, 'return_type'=>'single'))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        $thePass = Main::randomString(8);
        $allProfile = array('emailVerified'=>$theRepMail, 'password'=>$thePass, 'firstName'=>'***  ', 'lastName'=>'*****', 'phone'=>'1984-#@@#-001', 'bewerbt'=>'1234567');

        if($user = $this->db->get("allusers", array('select'=>'crt, leveltype', 'where'=>array('isdel'=>2, 'usermail'=>$theRepMail),'limit'=>1, 'return_type'=>'single'))){
           if($user['leveltype'] != 20){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Anmeldung zu Ihrem Konto!"))));
           }elseif($user['leveltype'] == 20){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Bitte füllen Sie Ihre Registrierung aus!"))));
           }
        } 
        

        $register = new Middleware($this->config, $this->db);
        if(!$r = $register->usersRegister($allProfile, TRUE)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }
        if($r && is_array($r) && array_key_exists("user_error", $r)){
          return $r;
        }

        $targetDir = UPLOAD_PATH_LEBENS.$r."/";  
        $isLeben=false;
        mkdir(ROOT."/".$targetDir);
        
        $upload = Uploads::factory($targetDir);
        $upload->file($_FILES['lebensKandidatBewerbS']);
        if($results = $upload->upload()){
          $insertCV = $this->db->insert("lebens", array("usid"=>$r, "leben"=>$results['filename']));

          $insertBewerbt = $this->db->insert("bewerbeliste", array("usid"=>$r, "stellen"=>$onWhat['crt'], "firma"=>$onWhat['usid']));

         if($bExtra){ $insertBeschreib = $this->db->insert("optextra", array("usid"=>$r, "optextra"=>$bExtra)); }
        }
      }catch (Exception $e){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
      }

     // if(Main::websettings("registermail") == 1){  
        // $emailDo = Main::emailCreate('registerBewerb', array('b1link'=>'https://doc-site.de/anmeldung', 'b2link'=>'https://doc-site.de/kontakt'));
         $emailDo = Main::emailCreate('email', array(     
           'customsubject'=>'Doc-Site - Herzlich willkommen',
           'username'=>e('Herzlich willkommen bei doc-site!'), 
           'l1'=>e('Sie haben sich erfolgreich als')."<br/><b>". $onWhat['titel'] .' beworben '.$onWhat['frmname']."<b><br/>",
           'l2'=>e('Sie können Ihre Doc-Site-Registrierung jederzeit abschließen.')."<br/>".e('Verwenden Sie die E-Mail-Adresse, mit der Sie sich auf die Stelle beworben haben, und das Passwort.:')." ".$thePass,
           'b1'=>e('Hier anmelden'),
           'b1link'=>Main::config('url').'/anmeldung',
           'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
           'l5'=>Main::config('title')." TEAM"
         ));

         @$this->sendmail('2alexsk8888@gmail.com', $emailDo['subject'], $emailDo['message'],  $emailDo['template']); // $theRepMail
      // }
    return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolgreich!').";/stellenangebote")));
  }



  public function socialsLogin(){
    if($this->adapters){
      foreach ($this->adapters as $name => $adapter){
        $allProfile = json_decode(json_encode($adapter->getUserProfile()), true);

         $logged = new Middleware($this->config, $this->db);
         $action = $logged->checkSocialAnmeldung($allProfile);  // array("action"=>2 / 1 reg, "datas"=>$this->setUserIN());
         if($action['action'] && $action['action'] == 1 ){
            $this->datas = $action['datas'];  // doar pt a manipula eventuale erori :: Email Exists 

            $redir = Main::convertLevelLink($logged->loggedLevel);
            Main::redirect($redir);
            die();
         }else if($action['action'] && $action['action'] == 2 ){ //login
            $redir = Main::convertLevelLink($logged->loggedLevel);
            Main::redirect($redir);
            die();
         }else{
          $logged->destroiy();
          die();
         }

      }
    }
  }


  public function doAnmeldung($params = false){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");
      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
			}

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}

      if(Main::csrfcheck($frm['token'])){
       // return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Login fehlgeschlagen').";anmeldung")));
      }

      try{
				$this->email = $frm['mail']; // $ansprecheralias, $frmalias,

        $email_exists = $this->emailExists(); 
        if($this->userbanned == 1){  
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Konto ist geschlossen"))));
        } 

        if($this->userverified == 0 || $this->activated == 0){  
         return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Konto nicht aktiviert'))));
        } 

        if($ansprecher = $this->isHeAnsprechpartner($this->ansprechId)){ /// crt, usid, activated
          if(!$ansprecher['activated'] == 1) { return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Konto nicht aktiviert')))); }
        }

        if($email_exists && $checks = $this->passwordOk($this->id, $this->password, $frm['password'], $this->salz)){
          if($checks['result'] >= 2){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$checks['msg'])));
          }

          if($this->userverified == 2 || $this->activated == 2){ 
            $decoy = Main::randomString( 5 ); 
            Main::cookie("precovers", $decoy.$this->unique_key, 120);
            return array("user_error"=>1, "user_reason"=>3, "sweets"=>e("Richten Sie Ihr neues Passwort ein!"), "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>"Passwort erstellen!;/neupasswort")));
          }

          if($this->ansprecheralias && $this->frmalias){
            $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->auth_key.$this->id, "uniq"=>$this->unique_key, "membership"=>$this->membership, "leveltype"=>$this->loggedLevel, "ansprecheralias"=>$this->ansprecheralias, "frmalias"=>$this->frmalias )));
          }else{
            $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->auth_key.$this->id, "uniq"=>$this->unique_key, "membership"=>$this->membership, "leveltype"=>$this->loggedLevel)));
          }


          $token = Tools::setJwt(array("id"=>$jsonArr)); 
          $jwt = $this->unique_key.JWT::encode($token, JWTKEY);  
          
          $user_browser = $_SERVER['HTTP_USER_AGENT'];
          $sessData['login_string'] = hash('sha512', $frm['password'] . $user_browser); 		
          $_SESSION['sessData'] = $sessData;

          $_SESSION["login"] = $jsonArr;
				//	$_SESSION["logi"] = $this->unique_key;
				//	$_SESSION['loogiwh'] = $this->loggedLevel;
					$this->logged=TRUE;	
          Main::cookie("loowt", $jwt);

          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Login erfolgreich').";".$this->loggedLevel))); 
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Login fehlgeschlagen'))));
        }
      }catch (Exception $e){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))), "error"=>$e->getMessage());
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }

  public function doSimpleLogin($params = false){
      if($params){
      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
			}

      try{
          $this->email = $params['mail']; // $ansprecheralias, $frmalias,

        $email_exists = $this->emailExists();
        if($this->userbanned == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Konto ist geschlossen"))));
        }

        if($this->userverified == 0 || $this->activated == 0){
         return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Konto nicht aktiviert'))));
        }

        // check account activate
        if($ansprecher = $this->isHeAnsprechpartner($this->ansprechId)){ /// crt, usid, activated
          if(!$ansprecher['activated'] == 1) { return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Konto nicht aktiviert')))); }
        }

        if($email_exists && $checks = $this->passwordOk($this->id, $this->password, $frm['password'], $this->salz)){
          if($checks['result'] >= 2){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$checks['msg'])));
          }

          if($this->userverified == 2 || $this->activated == 2){
            $decoy = Main::randomString( 5 );
            Main::cookie("precovers", $decoy.$this->unique_key, 120);
            return array("user_error"=>1, "user_reason"=>3, "sweets"=>e("Richten Sie Ihr neues Passwort ein!"), "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>"Passwort erstellen!;/neupasswort")));
          }

          if($this->ansprecheralias && $this->frmalias){
            $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->auth_key.$this->id, "uniq"=>$this->unique_key, "membership"=>$this->membership, "leveltype"=>$this->loggedLevel, "ansprecheralias"=>$this->ansprecheralias, "frmalias"=>$this->frmalias )));
          }else{
            $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->auth_key.$this->id, "uniq"=>$this->unique_key, "membership"=>$this->membership, "leveltype"=>$this->loggedLevel)));
          }


          $token = Tools::setJwt(array("id"=>$jsonArr));
          $jwt = $this->unique_key.JWT::encode($token, JWTKEY);

          $user_browser = $_SERVER['HTTP_USER_AGENT'];
          $sessData['login_string'] = hash('sha512', $frm['password'] . $user_browser);
          $_SESSION['sessData'] = $sessData;

          $_SESSION["login"] = $jsonArr;
				//	$_SESSION["logi"] = $this->unique_key;
				//	$_SESSION['loogiwh'] = $this->loggedLevel;
					$this->logged=TRUE;
          Main::cookie("loowt", $jwt);

          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Login erfolgreich').";".$this->loggedLevel)));
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Login fehlgeschlagen'))));
        }
      }catch (Exception $e){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))), "error"=>$e->getMessage());
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }


/**
* Check if Email exits | LOGIN HERE
**/
protected function emailExists($check = FALSE){
	if(!$check){
		if ($m = Main::email($this->email)) {
			$wh = "usermail"; 
		} else {
			if($m = Main::username($this->email)){
				$wh = "usernames";
			}else{
				return false;
			}
		}
	}else{
		  $m = $check;
			$wh = "usermail"; 
	}
		  
  $checkMail = $this->db->get("allusers", array('select'=>'crt, ansprech, leveltype, membership, expires, userfstname, userlstname, userpass, usersalt, auth_key, unique_key, banned, userverified, activated', "where"=>array($wh=>$m, 'isdel'=>2), 'limit'=>1, 'return_type'=>'single'));
  if($checkMail && $checkMail['crt'] >= 1){
		if(!$check){
      $this->id = $checkMail['crt'];
      $this->ansprechId = $checkMail['ansprech'];
			$this->loggedLevel = $checkMail['leveltype'];
			$this->membership = $checkMail['membership'];
			$this->expires = $checkMail['expires'];
      $this->firstname = $checkMail['userfstname'];
      $this->lastname = $checkMail['userlstname'];
      $this->password = $checkMail['userpass'];
			$this->salz = $checkMail['usersalt'];
			$this->auth_key = $checkMail['auth_key'];  
			$this->unique_key = $checkMail['unique_key'];
      $this->userbanned = $checkMail['banned']; 
			$this->userverified = $checkMail['userverified']; 
			$this->activated = $checkMail['activated']; 
		}
    return true;
  }
  return false;
 } 

 protected function isHeAnsprechpartner($id){
  if(!$id >= 1){
    return false;      
  }

  if(!$ans = $this->db->get("ansprechpartners",  array('select'=>'crt, usid, activated', 'where'=>array('crt' =>$id, 'isdel'=>2), 'limit'=>1, 'return_type'=>'single'))){       
    return false;
  }

  if($ans['usid'] && $ans['usid'] >= 1) { 
    $this->frmalias = $ans['usid'];
    $this->ansprecheralias = $ans['crt'];
  }

 return $ans;
 }

/**
* Check the password on Login
**/
 public function checkbrute($userid) {
	$now = time(); 
	$valid_attempts = $now - (1 * 60 * 60);
	$trys = $this->db->get("logins_err",  array('select'=>'crf', 'where'=>array('user_id' =>$userid), 'compbig'=>array('time'=>$valid_attempts), 'return_type'=>'count'));        
		if (!$trys || ($trys && $trys <= 5)) {
			return false;
		}
  return true;
 }
/**
* Check the password on Login
**/
protected function passwordOk($id, $dbpas, $pas, $code){
  $password_sha = openssl_digest($pas, 'sha512');
  $password = hash('sha512', $password_sha . $code);
		if ($dbpas == $password && !$this->checkbrute($id)){
			if($this->db->doquery("UPDATE allusers SET tot_visits = tot_visits + 1 WHERE crt = ".$id)){
				$upd = $this->db->update("allusers", array('on_line'=>1), array('crt'=>$id));
				
				$delLogins = $this->db->delete("logins_err", array('user_id' => $id));
			}
		 return array("result"=>1, "msg"=>e('Erfolg!'));
		}else{
			if(!$this->checkbrute($id)){
				$this->db->insert("logins_err", array('user_id'=>$id, 'time'=>time()), TRUE);
			  return array("result"=>2, "msg"=>e('Login fehlgeschlagen') );
			}
      return array("result"=>3, "msg"=>e("Ihr Konto ist für 1 Stunde gesperrt!"));
		}     
 }







}