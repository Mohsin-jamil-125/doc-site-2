<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\middlewares\Middleware;

class Editfirmenprofil extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->users = Main::user();

    $this->text = ["title" => e("Webplattform-Administratoren"), "pgname" => e("Webplattform-Administratoren"), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token'],
    "titel"=>array("", e('Loeschen')), "info"=>array("", e('Dieses Konto wird gelöscht und alle zugehörigen Daten gehen verloren!'))];
  }


  public function doNewAdministrator($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");
      if(!$frm = Main::forms($params)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
      } 

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
      }


      if(!$thisUser = $this->prufUser($frm['ai_token'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e('Fehler, versuche es erneut!'))));
      }

      if($frm['token_ai'] || !empty($frm['token_ai'])){
        if(!$edAdm = $this->prufAltAdmin($frm['token_ai'])){
          return array("user_error"=>1, "user_reason"=>22, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e('Fehler, versuche es erneut!'))));
        }
        $frm['token_ai'] = $edAdm;

       return $this->editsAdmins($frm);
      }

     
      $chPasswords = Main::comparePasswords($frm['nwadmepasswd'], $frm['nwadmerepasswd']);
      if($chPasswords['err'] == 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
      }

      $frm['alskontactsprechner'] = array_key_exists('alskontactsprechner', $frm) ? $frm['alskontactsprechner'] : 2;
      $frm['role_cansendsemail'] = array_key_exists('role_cansendsemail', $frm) ? $frm['role_cansendsemail'] : 2;
      $frm['isihnaktiv'] = array_key_exists('isihnaktiv', $frm) && $frm['isihnaktiv'] == 1 ? $frm['isihnaktiv'] : '0';

      $roles = []; 
      foreach($frm as $key => $val){
        if(strpos($key, "role_") !== false){
          $roles[$key] = $val;
        }
        $frm['insertRoles'] = json_encode($roles);
      }

      $register = new Middleware($this->config, $this->db); 
      if(!$r = $register->altAdminRegister($frm)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Registrierung fehlgeschlagen'))));
      }

      if($r && is_array($r) && array_key_exists("user_error", $r)){
        return $r;
      }

      if($frm['role_cansendsemail'] == 1){
        $emailDo = Main::emailCreate('email', array(   
          'customsubject'=>e('Admin-Konto'),
          'username'=>$frm['admname'] .' '.$frm['lstadmname'], 
          'l1'=>e('Ihr Administratorkonto wurde erfolgreich erstellt!'),
          'l2'=>e('Verwenden Sie diese Daten für den Login')."<br/>".e('Ihr Email-Adresse').".:".$frm['nwadmemail']."<br/>".e('Ihr Passwort').".:".$frm['nwadmepasswd'],
          'b1'=>e('Hier anmelden'),
          'b1link'=>Main::config('url').'/anmeldung',
        // 'l4'=>'Penultima linie de jos',
          'l5'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email')
        ));

        if(!$this->sendmail($frm['nwadmemail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Das Konto wurde erfolgreich erstellt, aber eine E-Mail konnte nicht gesendet werden!').";editfirmenprofil")));
        } 
      }
      

      return array("user_error"=>1, "user_reason"=>1, "ado"=>json_encode($frm), "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erforg!').";editfirmenprofil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler'));
    }
  }


  public function editsAdmins($frm){

      if($frm['nwadmepasswd'] || $frm['nwadmerepasswd']){
        $chPasswords = Main::comparePasswords($frm['nwadmepasswd'], $frm['nwadmerepasswd']);
        if($chPasswords['err'] == 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$chPasswords['msg'])));
        }
      }else{
        unset($frm['nwadmepasswd'], $frm['nwadmerepasswd']);
      }


      $frm['alskontactsprechner'] = array_key_exists('alskontactsprechner', $frm) ? $frm['alskontactsprechner'] : 2;
      $frm['role_cansendsemail'] = array_key_exists('role_cansendsemail', $frm) ? $frm['role_cansendsemail'] : 2;
      $frm['isihnaktiv'] = array_key_exists('isihnaktiv', $frm) && $frm['isihnaktiv'] == 1 ? $frm['isihnaktiv'] : '0';

      $roles = []; 
      foreach($frm as $key => $val){
        if(strpos($key, "role_") !== false){
          $roles[$key] = $val;
        }
        $frm['insertRoles'] = json_encode($roles);
      }

      $register = new Middleware($this->config, $this->db); 
      if(!$r = $register->altAdminEdit($frm)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($r && is_array($r) && array_key_exists("user_error", $r)){
        return $r;
      }

      if($frm['role_cansendsemail'] == 1){
        $edtMess = $frm['isihnaktiv'] == 1 ? e('Ihr Konto wurde erfolgreich aktualisiert!') : e('Ihr Administratorkonto wurde gesperrt!');
        if($frm['isihnaktiv'] == 1){
          $edtMess = e('Ihr Konto wurde erfolgreich aktualisiert!');
          $edtBtn = e('Hier anmelden');
          $edtLink = Main::config('url').'/anmeldung';
        }else{
          $edtMess = e('Ihr Administratorkonto wurde gesperrt!');
          $edtBtn = Main::config('title');
          $edtLink = Main::config('url');
        }
        $emailDo = Main::emailCreate('email', array(   
          'customsubject'=>e('Admin-Konto'),
          'username'=>$frm['admname'] .' '.$frm['lstadmname'], 
          'l1'=>$edtMess,
          'l2'=>"<br/>",
          'b1'=>$edtBtn,
          'b1link'=>$edtLink,
        // 'l4'=>'Penultima linie de jos',
          'l5'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email')
        ));

        if(!$this->sendmail($frm['nwadmemail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Das Konto wurde erfolgreich aktualisiert, aber eine E-Mail konnte nicht gesendet werden!').";editfirmenprofil")));
        } 
      }

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erforg!').";editfirmenprofil")));
  }


  
  public function doDeleteAdminis($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");
      if(!$thisAdmin = $this->prufAltAdmin($params)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e('Fehler, versuche es erneut!'))));
      }

      if($this->users[3] != "admin" ){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      $targetDir = UPLOAD_PATH_AVATARS.$thisAdmin."/";  

      $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();
        $usr = $this->db->get('allusers', array('select'=>'avatar, userfstname, userlstname, usermail', 'where' => array('crt'=>$thisAdmin), 'limit' => 1, 'return_type' => 'single'));
        if( is_dir(ROOT."/".$targetDir) && $usr['avatar']){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }

        $dell = $this->db->delete("allusers", array('crt'=>$thisAdmin));


        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        die($e->getMessage());
      }

      $emailDo = Main::emailCreate('email', array(   
        'customsubject'=>e('Admin-Konto'),
        'username'=>$usr['userfstname'] .' '.$usr['userlstname'], 
        'l1'=>e('Ihr Administratorkonto wurde gelöscht'),
        'l2'=>"<br/><br/>",
        'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
        'l5'=>Main::config('title')." TEAM"
      ));

      if(!$this->sendmail($usr['usermail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Das Konto wurde erfolgreich gelöscht, aber eine E-Mail konnte nicht gesendet werden!').";editfirmenprofil")));
      } 
   

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";editfirmenprofil"))); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }





}
