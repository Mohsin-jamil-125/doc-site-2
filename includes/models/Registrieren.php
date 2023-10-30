<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\Tools;
use App\includes\middlewares\Middleware;

use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;

use Hybridauth\Hybridauth;
use App\includes\models\Anmeldung;

class Registrieren extends Model  {

  protected $config = [], $db, $socialsregister, $socialsloginAllowed, $email, $anmeldung;
  public $text = [];
  protected $socialsConfig = FALSE;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->anmeldung = new Anmeldung($config, $db);

    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->socialsregister = Main::websettings('socialsregister'); 
    $this->socialsloginAllowed = Main::websettings('socialsAuth'); 
    $this->text = array("title"=>e("Registrieren"), "bottomPic"=>"/static/images/banners/people.png", "socialsloginAllowed"=>$this->socialsloginAllowed,  "socialsregister"=>$this->socialsregister, "public_token"=>$this->config['public_token']);

    Main::pagetitle($this->text['title']);

    if($this->socialsregister == 1){
      include SOCIALAUTH.'autoload.php';
      $this->socialsConfig = $this->config['socialsAuth'];

      $hybridauth = new Hybridauth($this->socialsConfig);
      $adapters = $hybridauth->getConnectedAdapters();

      foreach ($hybridauth->getProviders() as $name) {
        if($name === "Xing"){
          $this->text['socials'][$name] = "socialNet?prov=Xing";
        } else {
          $this->text['socials'][$name] = $this->socialsConfig['callback'] . "?provider=".$name;
        }
      }
    }
  }



  public function doRegister($params = false){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");

     

      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
			}

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}

      try{  
        if(Main::csrfcheck($frm['token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Registrieren fehlgeschlagen').";registrieren")));
        } 
        $chPasswords = Main::comparePasswords($frm['password'], $frm['passwordrep']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }

        if (!filter_var($frm['reptusermail'], FILTER_VALIDATE_EMAIL) || ($frm['reptusermail'] != $frm['usermail'])) {
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Emails does not match!"))));
        }

        $allProfile = array('emailVerified'=>$frm['usermail'], 'password'=>$frm['password'], 'firstName'=>'', 'lastName'=>'', 'phone'=>'');

        $register = new Middleware($this->config, $this->db);
        if(!$r = $register->usersRegister($allProfile, TRUE)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Registrierung fehlgeschlagen"))));
        }
        if($r && is_array($r) && array_key_exists("user_error", $r)){
          return $r;
        }

        if(Main::websettings("registermail") == 1){
          $emailDo = Main::emailCreate('register', array('b1link'=>'https://doc-site.de/pruf/prufMail/'.$r['uniq'], 'b2link'=>'')); // 'b2link'=>'https://doc-site.de/pruf/newMail/'.$r['uniq']
          file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "usermail- ". $frm['usermail']."\n", FILE_APPEND);
          file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "subject - ".$frm['subject']."\n", FILE_APPEND);
          file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', "message - ".$frm['message']."\n", FILE_APPEND);
          @$this->sendmail($frm['usermail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);

          // send email to admin
            $emailDo = Main::emailCreate('registerInform', array('user_email'=>'dsaldk@asd.com'));
            @$this->sendmail($this->config['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        }

          // after registration complete. set user session as login
        $parm = [
            'mail' => $frm['usermail'],
            'password' => $frm['password']
        ];
        $this->anmeldung->setLoginSession($parm);
        
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " ------- Success in Registration.php -----  level value = ".$r['level']."\n", FILE_APPEND);
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Registrierung erfolgreich!').";".$r['level'])));
      }catch (Exception $e){
        file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " Exception in Registration.php "."\n", FILE_APPEND);
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))), "error"=>$e->getMessage());
      }
    }else{

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " Message e fehler in Registration.php "."\n", FILE_APPEND);
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler'));
    }
  }

}
?>