<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

class Dashprofil extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['kandidat']; //"ai_token"=>$this->user[2], "public_token"=>$this->config['public_token']

    $this->text = ["title"=>e('Mein Profil'), "color"=>"dark", "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token']];
  }



  public function kandidatBewerbt($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");  

        if(!$thisUser = $this->prufUser($params->token)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!1'))));
        }

        if($this->users[0] != $thisUser ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!2'))));
        }

        if(!$doff = Main::decrypt($params->doff)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!3'))));
        }

        if(!$doff >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!4'))));
        }

        if(!$doit = Main::decrypt($params->doit)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!5'))));
        }

        if(!$doit >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!6'))));
        }

        if(!$isStell = $this->db->get('anzeigen', array('select'=>'crt', 'where' => array('crt'=>$doit, 'usid'=>$doff), 'limit' => 1, 'return_type' => 'single'))){ 
           return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        if($bewerb = $this->db->get('bewerbeliste', array('select'=>'crt', 'where' => array('usid'=>$thisUser, 'stellen'=>$doit), 'limit' => 1, 'return_type' => 'single'))){ 
          $doBewerb = $this->db->delete("bewerbeliste", array("crt"=>$bewerb['crt']));
          $doBewerb = $doBewerb ? "deleted" : FALSE;
        }else{
          $doBewerb = $this->db->insert("bewerbeliste", array("usid"=>$thisUser, "stellen"=>$doit, "firma"=>$doff));


          $details = $this->getAnzeigeBewerbt($thisUser, $doff, $doit);

          $emailDo = Main::emailCreate('email', array(     
            'customsubject'=>'Doc-Site.: Sie haben einen neuen Kandidaten',
            'username'=>$details['firma']['vorname'] .' '.$details['firma']['name'], 
            'l1'=>$details['kandidat']['userfstname'] .' '.$details['kandidat']['userlstname'] .' hat sich um eine Stelle beworben',
            'l2'=>"Der Kandidat bewarb sich als ".$details['anzeige']['titel'],
            'b1'=>e('Hier anmelden'),
            'b1link'=>Main::config('url').'/anmeldung',
            //'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
            'l5'=>Main::config('title')." TEAM"
          ));

          @$this->sendmail($details['firma']['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
          usleep(200);

          $emailDo = Main::emailCreate('email', array(     
            'customsubject'=>'Doc-Site.: '.$details['anzeige']['titel'],
            'username'=>$details['kandidat']['userfstname'] .' '.$details['kandidat']['userlstname'], 
            'l1'=>'Sie haben sich erfolgreich als'. $details['anzeige']['titel'] .' beworben '.$details['firma']['unique_key'],
            //'l2'=>"Der Kandidat bewarb sich als ".$details['anzeige']['titel'],
            'b1'=>e('Hier anmelden'),
            'b1link'=>Main::config('url').'/anmeldung',
            //'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
            'l5'=>Main::config('title')." TEAM"
          ));

          @$this->sendmail($details['kandidat']['usermail'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);

          $smPush= array(
            "chat-system"=>Main::config('title'),
            "message"=>'Neu bewerben'
            );
    
          @chat($details['firma']['unique_key'], $smPush);
        }

        if(!$doBewerb){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        return array("user_error"=>1, "user_reason"=>1, "werbs"=>$doBewerb, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!")))); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }


  public function kandidatLiebste($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");  

        if(!$thisUser = $this->prufUser($params->token)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($this->users[0] != $thisUser ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$doff = Main::decrypt($params->doff)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$doff >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$doit = Main::decrypt($params->doit)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$doit >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        if($liebste = $this->db->get('kandidatliebste', array('select'=>'crt', 'where' => array('usid'=>$thisUser, 'liebste'=>$doit), 'limit' => 1, 'return_type' => 'single'))){ 
          $doLiebste = $this->db->delete("kandidatliebste", array("crt"=>$liebste['crt']));
          $doLiebste = $doLiebste ? "deleted" : FALSE;
        }else{
          $doLiebste = $this->db->insert("kandidatliebste", array("usid"=>$thisUser, "liebste"=>$doit, "firma"=>$doff));
        }

        if(!$doLiebste){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        return array("user_error"=>1, "user_reason"=>1, "lieb"=>$doLiebste, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!")))); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }
  
  public function kandidatEditKalif($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if($selectsAnsprechers = Main::formMultiSelect($params, 'landsjob')){
          unset($params->selectsAnsprechers);
          $selectsAnsprechers = $selectsAnsprechers ? serialize($selectsAnsprechers) : '';  
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Bundesland/länder auswählen'))));
        }

        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));  
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        // $isAllowed = Main::isAllowedWork($frm);

        // $kandidattable = array("isalowed"=>$isAllowed?1:2,  "mustsjobapp"=>$isAllowed?$isAllowed['mustsjobapp']:0, "mustsjoberlaub"=>$isAllowed?$isAllowed['mustsjoberlaub']:0, "mustsjobaank"=>$isAllowed?$isAllowed['mustsjobaank']:0, "choosejob"=>$frm['choosejob'], "positionjob"=>$frm['positionjob'], "subjectjob"=>$frm['subjectjob'], "landsjob"=>$frm['landsjob']);

      $isAllowed = $frm['mustsjobapp'] == 4 ? 2 : 1; 


      $suzatz = [];
      $arrZusatz = ['zustazzanlg', 'zustazzanlg1', 'zustazzanlg2', 'zustazzanlg3'];
      foreach($arrZusatz as $val){
        if(array_key_exists($val, $frm)){
          $suzatz[$val] = $frm[$val]; 
        }
      }


        


      $kandidattable = array("isalowed"=>$isAllowed,  "mustsjobapp"=>$frm['mustsjobapp'], "mustsjoberlaub"=>0, "mustsjobaank"=>0,
    "suzatz"=>json_encode($suzatz), "choosejob"=>$frm['choosejob'], "positionjob"=>$frm['positionjob'], "subjectjob"=>$frm['subjectjob'], "arts"=>$frm['chooseanstellung'], "vertrag"=>$frm['choosevetrag'], "landsjob"=>$selectsAnsprechers);


        $pdo =  $this->db->getConnection();  //  weiterangabe
        try {
          $pdo->beginTransaction();

          if($stmt = $this->db->get('optextra', array('select'=>'crt', 'where' => array('usid'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))){
            if(!$frm['weiterangabe'] || $frm['weiterangabe'] == '') {
              $updCV = $this->db->delete("optextra", array("usid"=>$thisUser));
            }else{
              $updCV = $this->db->update("optextra", array("optextra"=>$frm['weiterangabe']), array("usid"=>$thisUser));
            }  
          }else{
              $updCV = $this->db->insert("optextra", array("usid"=>$thisUser, "optextra"=>$frm['weiterangabe']));
          }


           $updateKandidat = $this->db->update("kandidate", $kandidattable, array('usid'=>$thisUser));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));   
        }
        if(!$isAllowed){
          $resArr = array("action"=>"success;modal", "action_do"=>e("Erfolg aktualisiert").";noWorkAllowed");
        }else{
          $resArr = array("action"=>"success", "action_do"=>e("Erfolg aktualisiert"));
        }
       return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate($resArr)); 
    }else{
      //return array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr");
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }

  public function kandidatEdit($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($frm['typeprivat'] == 3){
          if($p = $this->db->get('usrpreffs', array('select'=>'typeprivat', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))){
            if($p && $p['typeprivat'] == 3){
              return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Das Konto ist weiterhin anonym!'))));
            }   
          }

          $preffsTable = array("typeprivat"=>$frm['typeprivat'], "titeljob"=>"", "herrjob"=>"", "jobstreet"=>"*", "jobstreetnr"=>"*", "jobspostcode"=>"*", "jobsort"=>"*", "jobbirth"=>"*");

          $phone = "1984-#@@#-000"; 
          $jobvorname = Main::strrand(); 
          $jobname = Main::strrand();
          $usernam = Main::generate_username($jobvorname." ".$jobname);
          $personalTable = array("usernames"=>$usernam,  "userfstname"=>"***  ", "userlstname"=>"*****", "userphone"=>$phone);

        }else{

          $datumBirth = Main::doBirthdate($frm['jobbirth'], $frm['userday'], $frm['usermonth'], $frm['useryear']);

          $preffsTable = array("typeprivat"=>$frm['typeprivat'], "titeljob"=>$frm['titeljob'], "herrjob"=>$frm['herrjob'], "jobstreet"=>$frm['jobstreet'], "jobstreetnr"=>$frm['jobstreetnr'], "jobspostcode"=>$frm['jobspostcode'], "jobsort"=>$frm['jobsort'], "jobbirth"=>$datumBirth); 
          $phone = $frm['userphone']."-#@@#-".$frm['jobphone']; 

          $personalTable = array("userfstname"=>$frm['jobvorname'], "userlstname"=>$frm['jobname'], "userphone"=>$phone);
        }


        $pdo =  $this->db->getConnection();
        try {
          $pdo->beginTransaction();

          $updPersonal = $this->db->update("allusers", $personalTable, array('crt'=>$thisUser));
          
          $updatePreffs = $this->db->update("usrpreffs", $preffsTable, array('usid'=>$thisUser));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
        }
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg aktualisiert").";dashprofil"))); 
    }else{
      //return array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr");
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }

    
  public function kandidatUploadAvatar($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

   /* if(!isset($files['avatarKandidat']['name']) || empty($files['avatarKandidat']['name']) || !$files['avatarKandidat']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler!Nichts hochzuladen!"))));
    } 

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    } */


    $targetDir = UPLOAD_PATH_AVATARS.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('allusers', array('select'=>'avatar', 'where' => array('crt'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }

    

    $resize = new ResizeImages(json_decode($this->config["minUpload"]));   
    $ismoved = $resize->doimages("cropimage", ROOT."/".$targetDir, 350, 350);
    if($ismoved['type'] == 2){                        
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." ".$ismoved['message'])));
    }else{
      $fileNamePhoto = $ismoved['message'];  
    } 

    if($updPic = $this->db->update("allusers", array("avatar"=>$fileNamePhoto), array("crt"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Logo erfolgreich gespeichert").";dashprofil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
    
  }

  
  public function kandidatUploadVideos($files, $posted){
    if(!isset($_FILES['videoBewerbeKandidat']['name']) || empty($files['videoBewerbeKandidat']['name']) || empty($files['videoBewerbeKandidat']['tmp_name'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    }

    if(!$files['videoBewerbeKandidat']['size'] || $files['videoBewerbeKandidat']['size'] > 10485760){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('max.Dateigröße 10 MB'))));
    }

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $onyoutube = isset($_POST['onyoutube']) ? filter_var($_POST['onyoutube'], FILTER_SANITIZE_NUMBER_INT) : 2;

    $targetDir = UPLOAD_PATH_USRVIDEO.$thisUser."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('usrpreffs', array('select'=>'usrvideo', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }
   
    $video_ex = pathinfo($files['videoBewerbeKandidat']['name'], PATHINFO_EXTENSION);
    $video_ex_lc = strtolower($video_ex);
    $allowed_exs = array('mp4');

    if (in_array($video_ex_lc, $allowed_exs)) {   
      $new_video_name = uniqid("video-", true).time().'.'.$video_ex_lc;
      $targetDir = UPLOAD_PATH_USRVIDEO.$thisUser."/";  
      if(!is_dir(ROOT."/".$targetDir)) {
        mkdir(ROOT."/".$targetDir);
      }else{
        if( $stmt = $this->db->get('usrpreffs', array('select'=>'usrvideo', 'where' => array('usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }
      }
      if(!move_uploaded_file($files['videoBewerbeKandidat']['tmp_name'], ROOT."/".$targetDir."/".$new_video_name)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler beim Hochladen der Videodatei!'))));
      }
    }else {
    	return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Erlaubtes Format: MP4'))));
    }

    if($updPic = $this->db->update("usrpreffs", array("usrvideo"=>$new_video_name, "ytpromot"=>$onyoutube), array("usid"=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Logo erfolgreich gespeichert"))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }



  public function kandidatUploadLebens($files, $posted){
    if(!isset($files['lebensKandidat']['name']) || empty($files['lebensKandidat']['name']) || !$files['lebensKandidat']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler!Nichts hochzuladen!"))));
    } 

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_LEBENS.$thisUser."/";  
    $isLeben=false;
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('lebens', array('select'=>'crt', 'where' => array('usid'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))){ 
        $isLeben=true;
        @Main::empty_directory(ROOT."/".$targetDir);
      } 
    }
    
    $upload = Uploads::factory($targetDir);
    $upload->file($_FILES['lebensKandidat']);
     // $validation = new Uploadsvalidation;
    //  $upload->callbacks($validation, array('check_name_length'));
    if($results = $upload->upload()){
      if($isLeben){
        $updCV = $this->db->update("lebens", array("leben"=>$results['filename']), array("usid"=>$thisUser));
      }else{
        $updCV = $this->db->insert("lebens", array("usid"=>$thisUser, "leben"=>$results['filename']));
      }

      if($updCV){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolgreich gespeichert").";dashprofil")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


  public function kandidatUploadOpts($files, $posted){
    if(!isset($files['optsKandidat']['name']) || empty($files['optsKandidat']['name']) || !$files['optsKandidat']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler!Nichts hochzuladen!"))));
    } 

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_OPTS.$thisUser."/";  
    $isLeben=false;
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('optionals', array('select'=>'crt', 'where' => array('usid'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))){ 
        $isLeben=true;
        @Main::empty_directory(ROOT."/".$targetDir);
      } 
    }

    $upload = Uploads::factory($targetDir);
    $upload->file($_FILES['optsKandidat']);
     // $validation = new Uploadsvalidation;
    //  $upload->callbacks($validation, array('check_name_length'));
    if($results = $upload->upload()){
      if($isLeben){
      //  $updCV = $this->db->update("optionals", array("opts"=>$results['filename'], array("usid"=>$thisUser)));
        $updCV = $this->db->update("optionals", array("opts"=>$results['filename']), array("usid"=>$thisUser));
      }else{
        $updCV = $this->db->insert("optionals", array("usid"=>$thisUser, "opts"=>$results['filename']));
      }

      if($updCV){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolgreich gespeichert").";dashprofil")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }



  public function kandidatWeiters($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }

        $aiToken = filter_var($frm['ai_token'], FILTER_SANITIZE_STRING);
        if(!$thisUser = $this->prufUser($aiToken)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($stmt = $this->db->get('optextra', array('select'=>'crt', 'where' => array('usid'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))){
          if(!$frm['weiterangabe'] || $frm['weiterangabe'] == '') {
            $updCV = $this->db->delete("optextra", array("usid"=>$thisUser));
          }else{
            $updCV = $this->db->update("optextra", array("optextra"=>$frm['weiterangabe']), array("usid"=>$thisUser));
          }  
        }else{
            $updCV = $this->db->insert("optextra", array("usid"=>$thisUser, "optextra"=>$frm['weiterangabe']));
        }

        if($updCV){
          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg aktualisiert")))); 
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }



  
  public function deletelebenslauf($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    if(!$lebens = $this->db->get("lebens", array('select'=>'leben', 'where'=>array('crt'=>$id, 'usid'=>$this->users[0]),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"Error!try again later1!")));
    }

    if($delLebens = $this->db->delete("lebens", array("crt"=>$id))){
      $targetDir = UPLOAD_PATH_LEBENS.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";dashprofil ")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }



  public function deleteoptdatains($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    if(!$lebens = $this->db->get("optionals", array('select'=>'opts', 'where'=>array('crt'=>$id, 'usid'=>$this->users[0]),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if($delLebens = $this->db->delete("optionals", array("crt"=>$id))){
      $targetDir = UPLOAD_PATH_OPTS.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";dashprofil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }

  public function deleteavatar($params = false){
    if(!$this->users){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    $id = Main::decrypt($params);
    if(!$id || !$id >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
    }

    if(!$stmt = $this->db->get("allusers", array('select'=>'avatar', 'where'=>array('crt'=>$id),'limit'=>1,'return_type'=>'single'))){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." ".$id)));
    }

    if($stmt = $this->db->update("allusers", array("avatar"=>NULL), array("crt"=>$id))){
      $targetDir = UPLOAD_PATH_AVATARS.$this->users[0]."/"; 
      @Main::empty_directory(ROOT."/".$targetDir);
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";dashprofil")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }





}
?>