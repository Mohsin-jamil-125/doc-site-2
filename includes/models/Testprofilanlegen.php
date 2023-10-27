<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Tools;
use App\includes\Main;
use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;

class Testprofilanlegen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  protected $membership = "kandidat"; 
  protected $leveltype = 3;
  public $user, $text = [];

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    if($u = Main::user()){
      $this->users = $u;
    }

 //   $this->text = ["title"=>e("Testprofilanlegen"), "color"=>"dark", "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token']];
  }

/*
  public function usranlegen($params){
		if($params){
      header("Content-Type: application/json; charset=UTF-8");
      if(!$frm = Main::forms($params)){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
			} 

      if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
				return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
			}


      if(!$thisUser = $this->prufUser($frm['ai_token'])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }


      $datumBirth = Main::doBirthdate($frm['jobbirth'], $frm['userday'], $frm['usermonth'], $frm['useryear']);

      $isAllowed = Main::isAllowedWork($frm);

      $kandidat = array("usid"=>$thisUser, "isalowed"=>$isAllowed?1:2,  "mustsjobapp"=>$isAllowed?$isAllowed['mustsjobapp']:0, "mustsjoberlaub"=>$isAllowed?$isAllowed['mustsjoberlaub']:0, "mustsjobaank"=>$isAllowed?$isAllowed['mustsjobaank']:0, "choosejob"=>$frm['choosejob'], "positionjob"=>$frm['positionjob'], "subjectjob"=>$frm['subjectjob'], "landsjob"=>$frm['landsjob']);


      $preffs = array("usid"=>$thisUser, "typeprivat"=>$frm['typeprivat'], "titeljob"=>$frm['titeljob'], "herrjob"=>$frm['herrjob'], "jobstreet"=>$frm['jobstreet'], "jobstreetnr"=>$frm['jobstreetnr'], "jobspostcode"=>$frm['jobspostcode'], "jobsort"=>$frm['jobsort'], "jobbirth"=>$datumBirth, "preffs"=>$this->config['uspreffs']);

      $phone = $frm['jobphcode']."-#@@#-".$frm['jobphone'];  
      $usernam = Main::generate_username($frm['jobvorname']." ".$frm['jobname']);
      $personal = array("membership"=>$this->membership, "leveltype"=>$this->leveltype, "usernames"=>$usernam,  "userfstname"=>$frm['jobvorname'], "userlstname"=>$frm['jobname'], "userphone"=>$phone);

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

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>"Ok!")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler') );
    }
  }
*/



}
?>