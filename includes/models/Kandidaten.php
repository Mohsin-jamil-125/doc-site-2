<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\middlewares\Middleware;
use App\includes\Main;

class Kandidaten extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->users = Main::user();

    $this->text = ["title" => e("Kandidaten"), "modal"=>["titel"=>e("Leider haben wir aktuell keine passende Kandidaten für")."<br/><span>".e("Ihre Suchanfrage")."</span>", "overbutton"=>e("Verpassen Sie keine neu veröffentlichen Kandidaten zu Ihrer Suche"),
    "button"=>e("Neue Kandidaten per Email erhalten"), "img"=>"kandidatsuche.svg"],
    "jobsperemail"=>["titel"=>e("Kandidaten-Vorschläge per Email erhalten"), "untertitel"=>e("Aktivieren Sie jetzt Ihren Kandidaten-Alert für:"), "whhr"=>"kandidaten"],
    "joberr"=>["titel"=>e('Kandidaten-Vorschläge per E-Mail erhalten'), "untertitel"=>e('Wählen Sie eine Ihrer Anzeigen aus!'), "whhr"=>"kandidaten"]
  ];
  }


  public function getAjaxKandidates($params = ''){

      if(!$info = Main::user()){
        return false;
      }

      if(!$myAds = $this->db->get("anzeigen", array("select"=>"anzeigen.titel, anzeigen.positionen, anzeigen.fachbereich, paystats.crt", "join"=>array(
        array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))), 
        'where'=>array('paystats.usid'=>$info[0]), 'big-equal'=>array('paystats.ison'=>3), 'return_type'=>'all'))){
         return false;
       }else{
        $myAdsPos = array();
        $myAdsFach = array();
        foreach($myAds as $k => $v){
          array_push($myAdsPos, $v['positionen']);

          array_push($myAdsFach, $v['fachbereich']);
        }
      }

      
      $filtr=[]; $relev=1; /*$positionen = []; $anstellung = []; $alleVertrag = []; $alleRegion = [];*/ 
      if($params){   
        $bystellen = isset($params->ihre_stellen) ? $params->ihre_stellen : FALSE;  /// filter by stellen
        $bysubj = isset($params->ihre_subj) ? $params->ihre_subj : FALSE;  /// filter by othopedie, psyho..
        $vetrag = isset($params->stll_vetrag) ? $params->stll_vetrag : FALSE;  /// alleVertrag befristed 
        $art = isset($params->stll_art) ? $params->stll_art : FALSE;  /// alleanstellung  Teilzeit vollzeit 
        $region = isset($params->stll_reg) ? $params->stll_reg : FALSE;  // Bundesweit  Berlin Rheinlan
        $relev = isset($params->stll_relev) ? $params->stll_relev : 1;  /// filter by stellen

        if($bystellen){
          $filtr['positionjob'] = $bystellen;
        }

        if($bysubj){
          $filtr['subjectjob'] = $bysubj;
        }
        
        if(Main::checkCheckuri($art, "alleanstellung")){  // anstellung
          $filtr['arts'] = $art;
        }
    
        if(Main::checkCheckuri($vetrag, "alleVertrag")){  // vertrag
          $filtr['vertrag'] = $vetrag;
        }
    
        if(Main::checkCheckuri($region, "Bundesweit")){  // region
          $filtr['landsjob'] = $region;
        }  
      }


    $conditions = array('return_type'=>'all');
    if($relev == 2){$conditions['order_by'] = 'crt DESC';}

    if($kandidates = $this->db->get("candidates_view", $conditions)){
        if($params && $filtr){
          foreach($kandidates as $key => $user){
            foreach($filtr as $k => $val) { 
              if($k == 'landsjob'){ 
                $l = unserialize($user['landsjob']);
                if(!array_intersect($l, $val)){
                  unset($kandidates[$key]);
                }
              }else{
                if (!in_array($user[$k], $filtr[$k])){ 
                  unset($kandidates[$key]);
                }
              }
            }
          }
        }
        
        $respKandidates = $this->responseAjaxKandidaten($kandidates, $myAdsPos, $myAdsFach, $info);

        if($relev == 3){ 
          usort($respKandidates, function($a,$b){
            return $b['beliebt'] <=> $a['beliebt'];
          });
        }

        return ["k"=>$respKandidates, "s"=>$myAds];
      }
    return false;
  }


  public function responseAjaxKandidaten($kandidates, $myAdsPos, $myAdsFach, $info){
    foreach($kandidates as $key => $user){
      if (!in_array($user['positionjob'], $myAdsPos)){
        unset($kandidates[$key]);
      }

      if (!in_array($user['subjectjob'], $myAdsFach)){
        unset($kandidates[$key]);
      }
    }

   $kandidates = array_values($kandidates);
   $funcs = new Middleware($this->config, $this->db);
    foreach($kandidates as $key => $user){                         
      if($lbs = $this->db->get('firmsliebste', array('select'=>'crt', 'where' => array('usid'=>$info[0], 'kandidat'=>$user['crt']), 'limit' => 1, 'return_type' => 'single'))){  
        $lbsCounts = $this->db->get('firmsliebste', array('select'=>'crt', 'where' => array('kandidat'=>$user['crt']), 'return_type' => 'count'));  
        $anzliebste = $lbs['crt'];
        if($lbsCounts && $lbsCounts >=1){ $lbsCounts = (int)$lbsCounts;}
      }else{
        $anzliebste = FALSE;
        $lbsCounts = 0;
      }
      
      $urele = Main::config('url');
      $crt = Main::encrypt($user['crt']);                
      if(!$user['avatar'] || empty($user['avatar'])){
        if($avatar = $funcs->checkAvatar($user['crt'])){
          $kandidates[$key]['avatar'] = $avatar; 
        }else{
          $kandidates[$key]['avatar'] = $urele.'/static/images/users/no-avatars/male-no-avatar.png';
        } 
      }else{
        $kandidates[$key]['avatar'] = $urele.'/static/images/users/'.$user['crt'].'/'.$user['avatar'];
      }

      $kandidates[$key]['fullname'] = Main::userProtect($user['userfstname'], $user['userlstname'], $user['typeprivat']);
      if($user['leben'] || trim($user['leben'])){
        $lebenname = str_replace(' ', '', $kandidates[$key]['fullname']);
        $lebenslauf = $urele.UPLOAD_PATH_LEBENS.$user['crt'].'/'.$user['leben']; 
      }else{
        $lebenname = '';
        $lebenslauf = '';
      }


      if(!$user['usermail'] || empty($user['usermail'])){
        $userSocialEmail = $funcs->checkSocialsEmail($user['crt']);
        if($userSocialEmail && $userSocialEmail['emailVerified']){
          $userEmail = $userSocialEmail['emailVerified']; 
        }else{
          $userEmail = FALSE;
        }
      }else{
        $userEmail = $user['usermail'];
      }

     // $shoLogin = !$leveltype ? 'doLoginFor' : '';
     // $themail = $allowedLogged && $userEmail ? $userEmail : '';
      $thephone  = '+'.str_replace('-#@@#-', '', $user['userphone']);
      $kandidates[$key]['kid'] = "kandidatansehen/".$crt;     
      $kandidates[$key]['titeljob'] =  $user['titeljob'] ? ucwords($user['titeljob']) : Main::returnValue("dropdowns", "anredes", $user['herrjob']);
      $kandidates[$key]['choosejobs'] = Main::returnValue("dropdowns", "berufsfeld", $user['choosejob']);
      $kandidates[$key]['positionjobs'] = Main::relreturnValue($user['choosejob'], "positionjob", $user['positionjob']);
      $kandidates[$key]['subjectjobs'] = Main::relreturnValue($user['choosejob'], "subjectjob", $user['subjectjob']);

      $kandidates[$key]['libClass']=  (isset($anzliebste) && $anzliebste >= 1) ? "fa-heart" : "fa-heart-o";
      $kandidates[$key]['beliebt'] = $lbsCounts;

      $kandidates[$key]['phone']= $thephone ? '<a tabindex="0" class="border text-primary p-0 copiedPhns" data-container="body" data-toggle="popover" data-popover-color="pop-primary" title="Telefon" data-placement="top" data-content="<i class=\'fa fa-phone\'></i> <span class=\'lstPhones\'>'.filter_var($thephone, FILTER_SANITIZE_NUMBER_INT).'</span>&nbsp;&nbsp;&nbsp;<i style=\'cursor: copy;\' class=\'fa fa-clone cpyLstPhone\'></i>"><i class="fa fa-phone"></i></a><span class="mr-2"> '.e('Anrufen').'</span>':'';

      $kandidates[$key]['mail']= $userEmail ? '<a href="javascript:void(0);" data-emailadrs="'.$userEmail.'" data-mailname="'.$kandidates[$key]['fullname'].'" data-ssmail="'.$user['unique_key'].'" data-mailid="'.$crt.'" class="border text-primary p-0 mailchatclick"><i class="fa fa-envelope"></i></a><span class="mr-2"> '.e('Nachricht senden').'</span>':'';

      $kandidates[$key]['chat'] = $crt ? '<a href="#" data-chatname="'.$kandidates[$key]['fullname'].'" data-sschat="'.$user['unique_key'].'" data-chat="'.$crt.'" class="border text-primary p-0 smchatsclicks"><i class="fa fa-comment"></i></a> '.e('Chat').'':'';
      $kandidates[$key]['dnld'] = $lebenslauf ? '<a download="'.$lebenname.'vita.pdf" href="'.$lebenslauf.'" class="btn btn-pill mt-3 mt-sm-0" style="background-color: #027aaf;border: 2px solid #027aaf;color: #fff;font-weight: bold;line-height: 0.95;"><i class="fa fa-download"></i> '.e('Download Vita').'</a>':'';

      //$kandidates[$key]['allowedLogged'] = $leveltype;
      $kandidates[$key]['approbiation'] = e('Deutsche Approbiation vorhanden');
      $kandidates[$key]['crtt'] = $crt;
     // $kandidates[$key]['shoLogin'] = '';
      $kandidates[$key]['uniquekeyme'] = $info ? $info[2] : FALSE;

      if(array_key_exists('leben', $kandidates[$key])) unset($kandidates[$key]['leben']);
        unset($kandidates[$key]['userfstname'], $kandidates[$key]['userlstname'], $kandidates[$key]['choosejob'], $kandidates[$key]['positionjob'], $kandidates[$key]['subjectjob']);  
      }
 
      return array_unique($kandidates, SORT_REGULAR);
  }



  public function kandidatenLiebste($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");  

        if(!$thisUser = $this->prufUser($params->token)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if($this->users[0] != $thisUser ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$domerk = Main::decrypt($params->domerk)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$domerk >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }


        if($liebste = $this->db->get('firmsliebste', array('select'=>'crt', 'where' => array('usid'=>$thisUser, 'kandidat'=>$domerk), 'limit' => 1, 'return_type' => 'single'))){ 
          $doLiebste = $this->db->delete("firmsliebste", array("crt"=>$liebste['crt']));
          $doLiebste = $doLiebste ? "deleted" : FALSE;
        }else{
          $doLiebste = $this->db->insert("firmsliebste", array("usid"=>$thisUser, "kandidat"=>$domerk));
        }

        if(!$doLiebste){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        return array("user_error"=>1, "user_reason"=>1, "lieb"=>$doLiebste, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!")))); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }


  public function kandidatenAlerts($params = ''){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");  

      if(!$this->users && !$this->users[0] >= 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }
      
      $inserat=0;
      $alerte = $this->getAlerts("kandidatealerts", $this->users[0], $params);
      if($alerte && count($alerte)){
        foreach($alerte as $key => $val){
          $ins = $this->db->insert("kandidatealerts", array("usid"=>$this->users[0], "beruf"=>$val['beruf'], "fach"=>$val['fach']));
          $inserat += $ins;
        }
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Job-Alert existiert bereits!'))));
      }

      if($inserat && $inserat >= 1){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!")))); 
      }
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }


// doth: thema, doit: msgch, doto: nchto, dotid: nchid
  public function sendNachEmails($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");  

        if(!$this->users && !$this->users[0] >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
        }

        if(!$toUser = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$params->doto),'limit'=>1,'return_type'=>'single'))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
        }


        if($dotid = Main::decrypt($params->dotid)){
          if(!$dotid || !$dotid >= 1){ return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')); }
        }else{
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
        }

        if($dotid != $toUser['crt']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')); 
        }

        if(!$eml = $this->db->get('allusers', array('select'=>'usermail', 'where' => array('crt'=>$dotid, 'unique_key'=>$params->doto), 'limit'=>1, 'return_type' => 'single'))){ 
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')); 
        } 

        if(!$mid = $this->db->insert("mailserver", array('usid'=>$this->users[0], 'usidview'=>$this->users[0], 'tosid'=>$dotid, 'mailsubject'=>$params->doth, 'mailmess'=>$params->doit))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')); 
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>e("Erfolg!")); 
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!'));
    }
  }




}