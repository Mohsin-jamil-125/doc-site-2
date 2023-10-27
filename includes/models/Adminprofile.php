<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\cPanelApi;


class Adminprofile extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];
    

    $this->text = ["title" => e('Admin profil'), "pgname" => e('Profil'), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];

  }


  public function getMails(){
    if(!$m = $this->doMailsLists()){
      $this->text['mailsList'] =  FALSE;
    }
    $this->text['mailsList'] =  $m;
  }


  public function doCreateEmails($params = false){
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

      if(!$email = Main::email($frm['adnnewmailtest']."@".DOMAIN)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültiges Email'))));
      }

      if($email){
        $password = Main::strrand(8);
        $api = new cPanelApi(DOMAIN, DOC_APIUS_SECRET, DOC_API_SECRET);
        if(!$theApi = $api->createEmail($email, $password, $quota = '500')  ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        // $apiAct = json_decode($theApi, true);
        // if($apiAct->act != "success"){
        //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        // }

        if($userFrm = $this->db->get("allusers", 
        array("select"=>"allusers.userfstname, allusers.userlstname, frmpreffs.email", "join"=>array(
        array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"allusers.crt"))),
        'where'=>array('allusers.isdel'=>2, 'allusers.crt'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))
        ){
          $stmt = $this->db->update("allusers", array('docmail'=>$email), array('crt'=>$thisUser, 'isdel'=>2));

          $emailDo = Main::emailCreate('email', array(     
            'customsubject'=>'Doc-Site.: Sie haben das E-Mail-Postfach erstellt',
            'username'=>$userFrm['userfstname'] .' '.$userFrm['userlstname'], 
            'l1'=>'Sie haben das E-Mail-Postfach erfolgreich erstellt. Das steht für Sie bereit!<br/>',
            'l2'=>"Hier ist Ihr Passwort.: ".$password."<br/>Die Einstellungen für die Verbindung zu Ihrem E-Mail-Posteingang befinden sich in Ihrem Doc-Site-Konto",
            'b1'=>e('Hier anmelden'),
            'b1link'=>Main::config('url').'/anmeldung',
            'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
            'l5'=>Main::config('title')." TEAM"
          ));

          @$this->sendmail($userFrm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        }


        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg").";/adminprofile")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Wähle eine Option"))));
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }


  
   
   
    








}
