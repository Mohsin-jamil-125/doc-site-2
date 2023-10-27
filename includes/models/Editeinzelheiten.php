<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;
use App\includes\library\Resizeimages;
use App\includes\library\Uploads;
use App\includes\library\Uploadsvalidation;

class Editeinzelheiten extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title"=>e("Einzelheiten bearbeiten"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }


  public function editAnzeigge($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if($selectsAnsprechers = Main::formMultiSelect($params, 'selectsAnsprechers')){  ///  ai_token_plan
          unset($params->selectsAnsprechers);
        }else{
          $selectsAnsprechers = null;
        }
       
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }


        if(!$thisUser = $this->prufUser($frm['ai_token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$frm['anztoken']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$frm['rechntoken']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        $anzID = Main::decrypt($frm['anztoken']);  // idPlanRadios  tkn_plaRadios      ai_token_plan
        if(!$anzID >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($OLD_Rechnung = Main::decrypt($frm['rechntoken'])){
          if(!$OLD_Rechnung >= 1){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
        }


        if(!array_key_exists('ai_token_plan', $frm)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }else{
          $tokenNr = explode('-@-', $frm['ai_token_plan']);
          if($tokenNr[0] = Main::decrypt($tokenNr[0])){
            if(!$tokenNr[0] >= 1 || !$tokenNr[1] >= 1){  /// $tokenNr[0] - rechNR    $tokenNr[1] --- PlanID
              return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
            }else{
              $RechnungID = $tokenNr[0];
            }
          }
        }


        if(!$payPlans = $this->db->get("payplans", array("select"=>"planweeks, planmonths", 'where'=>array('crt'=>$tokenNr[1]), 'limit'=>'1', 'return_type'=>'single'))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        if($frm['aktivateRadio'] == 3){
          if(!$aktivate = $this->doSetAktive($thisUser, $anzID, $RechnungID)){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Keine Anzeige zu diesem Paket'))));
          }else{
            if($aktivate['stat'] == 4){
              $actiReturn = e('Ihr Kontingent an verfügbaren Anzeigen ist aufgebraucht'); // no more places free    2 acelasi anunt
            }elseif($aktivate['stat'] == 5){
              $actiReturn = e('Ihre Anzeige könnte nicht aktiviert werden!').'<br/>'.e('Sie haben noch offene Beträge zu zahlen');
            }elseif($aktivate['stat'] == 3){
              if(array_key_exists("gebucht", $aktivate)){ 
                if(!array_key_exists("reactivate", $aktivate)){
                  array_push($aktivate['gebucht'], $anzID);
                  $gebuchten = serialize($aktivate['gebucht']);
                }elseif(array_key_exists("reactivate", $aktivate)){
                  $gebuchten = serialize($aktivate['gebucht']);
                }
              }


              if($getAnzeigeDates = $this->db->get("paystats", array("select"=>"datactivat, gultigbis", "where"=>array("anzid"=>$anzID, "billnr"=>$RechnungID), "limit"=>1, "return_type"=>"single"))){
                if(!empty($getAnzeigeDates['datactivat']) && !empty($getAnzeigeDates['gultigbis'])){
                  if(! $updAnzeigeStats = $this->db->update("paystats", array("ison"=>3), array("anzid"=>$anzID, 'billnr'=>$RechnungID, 'usid'=>$thisUser))){
                    return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
                  }
                }else{

                  $payPlans = $this->db->get("payplans", array("select"=>"planweeks, planmonths", 'where'=>array('crt'=>$tokenNr[1]), 'limit'=>'1', 'return_type'=>'single'));
                  $startdate = date('Y-m-d');
                  $gultig_bis = date('Y-m-d', strtotime("+{$payPlans['planweeks']} week", strtotime($startdate)));
                  $sichtbar_bis = date('Y-m-d', strtotime("+{$payPlans['planmonths']} month", strtotime($startdate)));


                  $updAnzeigeBuch = $this->db->update("rechnungen", array("gebucht"=>$gebuchten), array('crt'=>$RechnungID, 'usid'=>$thisUser));

                  $updAnzeigeStatus = $this->db->update("paystats", array("ison"=>3, "datactivat"=>date("Y-m-d H:i:s"), "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis), array('billnr'=>$RechnungID, 'usid'=>$thisUser, 'anzid'=>$anzID));

                }
              }else{  // WHEN THE BILL AND PLAN WERE CHANGED!!!!!!!!!!!

                  $payPlans = $this->db->get("payplans", array("select"=>"planweeks, planmonths", 'where'=>array('crt'=>$tokenNr[1]), 'limit'=>'1', 'return_type'=>'single'));
                  $startdate = date('Y-m-d');
                  $gultig_bis = date('Y-m-d', strtotime("+{$payPlans['planweeks']} week", strtotime($startdate)));
                  $sichtbar_bis = date('Y-m-d', strtotime("+{$payPlans['planmonths']} month", strtotime($startdate)));

                if($RechnungID != $OLD_Rechnung){ // a schimbat planul
                  $updAnzeigeBuch = $this->db->update("rechnungen", array("gebucht"=>$gebuchten), array('crt'=>$RechnungID, 'usid'=>$thisUser));
                  $updAnzeigeStats = $this->db->update("paystats", array("ison"=>3, "planid"=>$tokenNr[1], 'billnr'=>$RechnungID, "datactivat"=>date("Y-m-d H:i:s"), "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis), array('billnr'=>$OLD_Rechnung, 'usid'=>$thisUser, 'anzid'=>$anzID));
                }else{
                  $updAnzeigeBuch = $this->db->update("rechnungen", array("gebucht"=>$gebuchten), array('crt'=>$OLD_Rechnung, 'usid'=>$thisUser));

                  $updAnzeigeStatus = $this->db->update("paystats", array("ison"=>3, "datactivat"=>date("Y-m-d H:i:s"), "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis), array('billnr'=>$OLD_Rechnung, 'usid'=>$thisUser, 'anzid'=>$anzID));
                }

              }
            }elseif($aktivate['stat'] == 2){
              $actiReturn = 'STAT 2';
            }
          }
        }elseif($frm['aktivateRadio'] == 2){  /// disactivate anzeige here
          $updAnzeigeStats = $this->db->update("paystats", array("ison"=>2), array('billnr'=>$OLD_Rechnung, 'anzid'=>$anzID));
        }


       if(trim($frm['linkanzeigg'])){
        if(!$linkanzeigg = Main::is_url_extended($frm['linkanzeigg'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler! Ungültige URL!'))));
        }
        $parse = parse_url(rtrim($linkanzeigg,"/"));
        if(array_key_exists("path", $parse) || array_key_exists("query", $parse)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Geben Sie Ihren Domainnamen ein!'))));
        }
       }else{
        $linkanzeigg = "";
       }
 
        $selectsAnsprechers = $selectsAnsprechers ? serialize($selectsAnsprechers) : '';  
       
        
        if($thecoords = Main::get_lat_long($frm['strasse'].' '.$frm['hausnr'].' '.$frm['citycode'].' '.$frm['city'], $frm['chooselandanzz'])){
          $thecoords = explode(',', $thecoords);
        } else{
          $thecoords = ['',''];
        } 

        $bnds = Main::derbundes($frm['chooserpp'], TRUE);

        $anzeige = array("usid"=>$thisUser, "ansprechs"=>$selectsAnsprechers, "titel"=>$frm['whatanztitel'], "berufsfeld"=>$frm['choosejob'], "positionen"=>$frm['positionjob'], "fachbereich"=>$frm['subjectjob'], "arts"=>$frm['chooseanstellung'], "vertrag"=>$frm['choosevetrag'], "filiale"=> $frm['whatfiliale'] ?? "",  "starts"=>Main::dbdate($frm['whatstartdate']), "citycode"=>$frm['citycode'], "city"=>$frm['city'], "derbundes"=>$bnds, "derland"=>$frm['chooselandanzz'], "latit"=>$thecoords[0], "longit"=>$thecoords[1], "anznr"=>$frm['hausnr'], "anzstr"=>$frm['strasse'], "beschreibung"=>$frm['bescreibung'], "besherwartet"=>$frm['besherwartet'] ?? "", "beshbieten"=>$frm['beshbieten'] ?? "", "beshwirerw"=>$frm['beshwirerw'] ?? "", "urlink"=>$linkanzeigg);  // "soclink"=>$frm['linksoccanzeigg']

        if($insAnzeige = $this->db->update("anzeigen", $anzeige, array("crt"=>$anzID, "usid"=>$thisUser))){
          $this->isShared($anzID); // Update Socials Shares List
          
          return array("user_error"=>1, "user_reason"=>1, "returnact"=>$actiReturn ?? "", "anzg"=>$insAnzeige, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg aktualisiert')))); 
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }

  public function anzEdLogo($files, $posted){
    if(!isset($files['cropimage']['name']) || empty($files['cropimage']['name']) || !$files['cropimage']['size'] > 0){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    } 

    $alte = explode('-', $posted['alte']);

    $aiToken = filter_var($alte[0], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    if(!$posted['regtoken'] || empty($posted['regtoken'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
    // $thisIdNr = filter_var($posted['regtoken'], FILTER_SANITIZE_NUMBER_INT);
    // if(!$thisIdNr || !$thisIdNr >= 1){
    //   return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    // }
    $thisIdNr = Main::decrypt(filter_var($posted['regtoken'], FILTER_SANITIZE_STRING));
    if(!$thisIdNr || !$thisIdNr >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }


    $targetDir = UPLOAD_PATH_ANZEIGE.$thisIdNr."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if($stmt = $this->db->get('anzeigen', array('select'=>'anzlogo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))){ 
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

    if($updPic = $this->db->update("anzeigen", array("anzlogo"=>$fileNamePhoto), array('crt'=>$thisIdNr, 'usid'=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Logo erfolgreich gespeichert'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }

  public function anzEdVideo($files, $posted){  
    if(!isset($_FILES['viddeoFrmEddAnz']['name']) || empty($files['viddeoFrmEddAnz']['name']) || empty($files['viddeoFrmEddAnz']['tmp_name'])){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler!Nichts hochzuladen!'))));
    }

    if(!$files['viddeoFrmEddAnz']['size'] || $files['viddeoFrmEddAnz']['size'] > 10485760){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('max.Dateigröße 10 MB'))));
    }

    $aiToken = filter_var($_POST['ai_token'], FILTER_SANITIZE_STRING);
    if(!$thisUser = $this->prufUser($aiToken)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }

    $thisIdNr = filter_var(Main::decrypt($_POST['anzregtoken']), FILTER_SANITIZE_NUMBER_INT);
    if(!$thisIdNr || !$thisIdNr >= 1){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }  

    $onyoutube = isset($_POST['onyoutube']) ? filter_var($_POST['onyoutube'], FILTER_SANITIZE_NUMBER_INT) : 2;

    $targetDir = UPLOAD_PATH_VIDEO.$thisIdNr."/";  
    if(!is_dir(ROOT."/".$targetDir)) {
      mkdir(ROOT."/".$targetDir);
    }else{
      if( $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
        @Main::empty_directory(ROOT."/".$targetDir);
      }
    }
   
    $video_ex = pathinfo($files['viddeoFrmEddAnz']['name'], PATHINFO_EXTENSION);
    $video_ex_lc = strtolower($video_ex);
    $allowed_exs = array('mp4');

    if (in_array($video_ex_lc, $allowed_exs)) {   
      $new_video_name = uniqid("video-", true).time().'.'.$video_ex_lc;
      $targetDir = UPLOAD_PATH_VIDEO.$thisIdNr."/";  
      if(!is_dir(ROOT."/".$targetDir)) {
        mkdir(ROOT."/".$targetDir);
      }else{
        if( $stmt = $this->db->get('anzeigen', array('select'=>'anzvideo', 'where' => array('crt'=>$thisIdNr, 'usid'=>$thisUser), 'limit' => 1, 'return_type' => 'single'))  ){ 
          @Main::empty_directory(ROOT."/".$targetDir);
        }
      }
      if(!move_uploaded_file($files['viddeoFrmEddAnz']['tmp_name'], ROOT."/".$targetDir."/".$new_video_name)){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler beim Hochladen der Videodatei!'))));
      }
    }else {
    	return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Erlaubtes Format: MP4'))));
    }

    if($updPic = $this->db->update("anzeigen", array("anzvideo"=>$new_video_name, "ytpromo"=>$onyoutube), array('crt'=>$thisIdNr, 'usid'=>$thisUser))){
      return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Video erfolgreich gespeichert'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));  
    }
  }



}