<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Tools;
use App\includes\Main;
use App\includes\library\phpjwt\JWT;
use App\includes\library\phpjwt\JWK;

class Firmanlegen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  protected $membership = "firma"; 
  protected $leveltype = 2;
  public $user, $text = [];

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['register'];

    $this->users = Main::user();

    $this->text = ["title"=>e("Firmanlegen"), "color"=>"dark", "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'],
    "fin"=>['banner'=>Main::config('url').'/static/images/banners/doctors.png', 'title'=>e('Was möchten Sie als nächstes tun?'), 
    'picone'=>Main::config('url').'/static/images/svg/uploadFiles.svg', 'textone'=>e('Profil bearbeiten'), 'linkone'=>'/company-profil', 
    'pictwo'=>Main::config('url').'/static/images/svg/stellens.svg', 'textwo'=>e('Zur Kandidatensuche'), 'linktwo'=>'/kandidaten', 
    'picthree'=>Main::config('url').'/static/images/svg/jobAlert.svg', 'textthree'=>e('Kandidaten-Suche aktivieren'), 'linkthree'=>'/company/kandidatenalerts']];
  }


  public function frmanlegen($params){
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

     $intersartze = array_key_exists("intersartze", $frm) ? $frm['intersartze'] : 2;
     $intersnurse = array_key_exists("intersnurse", $frm) ? $frm['intersnurse'] : 2;
     $interspharma = array_key_exists("interspharma", $frm) ? $frm['interspharma'] : 2;

     $medsfachs = array_key_exists("medsfachs", $frm) ? $frm['medsfachs'] : 2;
     $therafachs = array_key_exists("therafachs", $frm) ? $frm['therafachs'] : 2;
     $verwafach = array_key_exists("verwafach", $frm) ? $frm['verwafach'] : 2;
     $techfach = array_key_exists("techfach", $frm) ? $frm['techfach'] : 2;
     $sonstiges = array_key_exists("sonstiges", $frm) ? $frm['sonstiges'] : 2;
 
      $firmen = array("usid"=>$thisUser, "frmname"=>$frm['einrichtungname'], "frmsteuer"=>$frm['frmsteuer'], "frmeinricht"=>$frm['chooseeinrichtung'], "intersartze"=>$intersartze, "intersnurse"=>$intersnurse, "interspharma"=>$interspharma, "medsfachs"=>$medsfachs, "therafachs"=>$therafachs, "verwafach"=>$verwafach, "techfach"=>$techfach, "sonstiges"=>$sonstiges);

      $phone = $frm['frmcode']."-#@@#-".$frm['frmhone']; 
      $frmpreffs = array("usid"=>$thisUser, "suadmin"=>1, "anrede"=>$frm['herrfrm'], "vorname"=>$frm['frmvorname'], "name"=>$frm['frmname'],
      "position"=>$frm['frmposition'], "telefon"=>$phone, "email"=>$frm['frmmail'], "hausnr"=>$frm['frmstreetnr'],
      "strasse"=>$frm['frmstreet'], "hausnr"=>$frm['frmstreetnr'], "citycode"=>$frm['frmspostcode'], "city"=>$frm['frmsort']);

      if(Main::websettings('showbankverbindung') == 1){
        $bankDetails = ["inhaber"=>$frm['frmkonto'], "bankname"=>$frm['frmbank'], "swift"=>$frm['frmsbic'], "iban"=>$frm['frmiban']];
        array_merge($frmpreffs, $bankDetails);
      }


      $personals = array("membership"=>$this->membership, "leveltype"=>$this->leveltype, "userfstname"=>$frm['frmvorname'], "userlstname"=>$frm['frmname'], "userphone"=>$phone);

      $pdo =  $this->db->getConnection();
      try {
        $pdo->beginTransaction();

        $updPersonals = $this->db->update("allusers", $personals, array('crt'=>$thisUser));

        $insertFirma = $this->db->insert("firmen", $firmen);
        
        $insertFrmPreffs = $this->db->insert("frmpreffs", $frmpreffs);

        $pdo->commit();
      } catch (\PDOException $e) {
        $pdo->rollBack();
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
      }

      $this->accessLevel = $this->membership;
      $jsonArr = base64_encode(json_encode(array("loggedin"=>TRUE, "key"=>$this->users[1].$thisUser, "uniq"=>$this->users[2], "membership"=>$this->membership, "leveltype"=>$this->leveltype)));
      $_SESSION["login"] = $jsonArr;

      if(Main::websettings('mailFirmanlegen') == 1){
        $emailDo = Main::emailCreate('email', array(     
          'customsubject'=>'Doc-Site.: Sie haben einen neuen Unternehmen',
          'username'=>$frm['frmname'], 
          'l1'=>$frm['frmvorname'] .' '.$frm['frmname'],
          'l2'=>"Email.: ".$frm['frmmail']."<br/>Tel.: ".$frm['frmcode']."".$frm['frmhone'],
          'b1'=>e('Hier anmelden'),
          'b1link'=>Main::config('url').'/anmeldung',
          'l5'=>Main::config('title')
        ));

        @$this->sendmail(Main::config('email'), $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        usleep(200);
      }

      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>"Ok!")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler'));
    }
  }




}
?>