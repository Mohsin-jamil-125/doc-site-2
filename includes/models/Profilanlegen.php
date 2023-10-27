<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Tools;
use App\includes\Main;
use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;

class Profilanlegen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  protected $membership = "kandidat"; 
  protected $leveltype = 3;
  public $users, $text = [];

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['register'];

    $this->text = ["title"=>e("Profilanlegen"), "color"=>"dark", "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token'],
   "fin"=>['banner'=>Main::config('url').'/static/images/banners/doctors.png', 'title'=>e('Was möchten Sie als nächstes tun?'), 
   'picone'=>Main::config('url').'/static/images/svg/uploadFiles.svg', 'textone'=>e('Profil bearbeiten'), 'linkone'=>'dashprofil', 
   'pictwo'=>Main::config('url').'/static/images/svg/stellens.svg', 'textwo'=>e('Zu den Stellenanzeigen'), 'linktwo'=>'stellenangebote', 
   'picthree'=>Main::config('url').'/static/images/svg/jobAlert.svg', 'textthree'=>e('Job-Alert aktivieren'), 'linkthree'=>'dash/jobalerts']];
  }


  public function doReplaceMail($params){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");

      if(!$user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if (!filter_var($params, FILTER_VALIDATE_EMAIL)) {
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültige E-Mail-Adresse!'))));
      }

      if ($chk = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('usermail'=>$params), 'return_type'=>'single'))) {
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('E-Mail ist bereits vorhanden!'))));
      }
 

      if($upd = $this->db->update("allusers", array("usermail"=>$params), array("unique_key"=>$this->users[2]))){

        $emailDo = Main::emailCreate('register', array('b1link'=>'https://doc-site.de/pruf/prufMail/'.$this->users[2], 'b2link'=>'')); // 'b2link'=>'https://doc-site.de/pruf/newMail/'.$r['uniq']

        @$this->sendmail($params, $emailDo['subject'], $emailDo['message'],  $emailDo['template']);

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg!').";/willkommen")));
      }
     return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler') );
    }
  }


  public function usranlegen($params){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");
      

      if($selectsAnsprechers = Main::formMultiSelect($params, 'landsjob')){
        unset($params->selectsAnsprechers);
        $selectsAnsprechers = $selectsAnsprechers ? serialize($selectsAnsprechers) : '';  
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Bundesland/länder auswählen'))));
      }


      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
			} 

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}


      if(!$thisUser = $this->prufUser($frm['ai_token'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }


      if (property_exists($params, 'anonymes') && !empty($params->anonymes) && $frm['typeprivat'] == 3){
        $asa = array('herrjob','titeljob','jobvorname','jobname', 'jobstreet','jobstreetnr','jobspostcode','jobsort','jobphcode','jobphone','jobbirth','birthdatum','usermonth','useryear');
        foreach($asa as $k){
          unset($params->{$k});
        }
      }else{
        $asa = FALSE;
      }


      //$isAllowed = Main::isAllowedWork($frm);

      $isAllowed = $frm['mustsjobapp'] == 4 ? 2 : 1; 

      
      $suzatz = [];
      $arrZusatz = ['zustazzanlg', 'zustazzanlg1', 'zustazzanlg2', 'zustazzanlg3'];
      foreach($arrZusatz as $val){
        if(array_key_exists($val, $frm)){
          $suzatz[$val] = $frm[$val]; 
        }
      }

     /// $jobbirth is not done


    $kandidat = array("usid"=>$thisUser, "isalowed"=>$isAllowed,  "mustsjobapp"=>$frm['mustsjobapp'], "mustsjoberlaub"=>0, "mustsjobaank"=>0,
    "suzatz"=>json_encode($suzatz), "choosejob"=>$frm['choosejob'], "positionjob"=>$frm['positionjob'], "subjectjob"=>$frm['subjectjob'], "arts"=>$frm['chooseanstellung'], "vertrag"=>$frm['choosevetrag'], "landsjob"=>$selectsAnsprechers);

      if(!$asa && empty($asa)){
        $preffs = array("usid"=>$thisUser, "typeprivat"=>$frm['typeprivat'], "titeljob"=>$frm['titeljob'], "herrjob"=>$frm['herrjob'], "jobstreet"=>$frm['jobstreet'], "jobstreetnr"=>$frm['jobstreetnr'], "jobspostcode"=>$frm['jobspostcode'], "jobsort"=>$frm['jobsort'], "jobbirth"=>"", "preffs"=>$this->config['uspreffs']);

        $phone = $frm['jobphcode']."-#@@#-".$frm['jobphone'];  
        $usernam = Main::generate_username($frm['jobvorname']." ".$frm['jobname']);
        $usernam = $usernam ? $usernam :  Main::strrand(8);
        $personal = array("membership"=>$this->membership, "leveltype"=>$this->leveltype, "usernames"=>$usernam,  "userfstname"=>$frm['jobvorname'], "userlstname"=>$frm['jobname'], "userphone"=>$phone);
      }elseif($asa && !empty($asa)){
        $preffs = array("usid"=>$thisUser, "typeprivat"=>$frm['typeprivat'], "titeljob"=>"", "herrjob"=>"", "jobstreet"=>"*", "jobstreetnr"=>"*", "jobspostcode"=>"*", "jobsort"=>"*", "jobbirth"=>"*", "preffs"=>$this->config['uspreffs']);   

        $phone = "1984-#@@#-000"; 
        $jobvorname = Main::strrand(); 
        $jobname = Main::strrand();
        $usernam = Main::generate_username($jobvorname." ".$jobname);
        $usernam = $usernam ? $usernam :  Main::strrand(8);
        $personal = array("membership"=>$this->membership, "leveltype"=>$this->leveltype, "usernames"=>$usernam,  "userfstname"=>"***  ", "userlstname"=>"*****", "userphone"=>$phone);
      }


      $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();

        $updPersonal = $this->db->update("allusers", $personal, array('crt'=>$thisUser));

        $insertKandidat = $this->db->insert("kandidate", $kandidat);
        
        $insertUserPreffs = $this->db->insert("usrpreffs", $preffs);

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
      }

      $this->accessLevel = $this->membership;
      $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->users[1].$thisUser, "uniq"=>$this->users[2], "membership"=>$this->membership, "leveltype"=>$this->leveltype)));
      $_SESSION["login"] = $jsonArr;



      if(Main::websettings('mailKandidatanlegen') == 1){
        $jobvorname = isset($frm['jobvorname']) ? $frm['jobvorname'] : "***  ";
        $jobname = isset($frm['jobname']) ? $frm['jobname'] : "***  ";  
        $emailDo = Main::emailCreate('email', array(     
          'customsubject'=>'Doc-Site.: Sie haben einen neuen Kandidat',
          'username'=>$jobvorname.' '.$jobname,  
          'l1'=>"Tel.: ".str_replace('_normal', '', $personal['userphone']),
          'l2'=>"Berufsfeld.: ".Main::returnValue("dropdowns", "berufsfeld", $kandidat['choosejob'])."<br/>Position.: ".Main::relreturnValue($kandidat['choosejob'], 'positionjob', $kandidat['positionjob']),
          'b1'=>e('Hier anmelden'),
          'b1link'=>Main::config('url').'/anmeldung',     
          'l5'=>Main::config('title')
        ));

        @$this->sendmail(Main::config('email'), $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        usleep(200);
      }

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler') );
    }
  }




}
?>