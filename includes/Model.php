<?php

/*
########################
If you are a coder you know what to do with this !!!

SGVsbG8gY29sbGVhZ3VlISAKSWYgeW91IHJlYWQgdGhlc2UgbGluZXMgaXQgbWVhbnMgdGhhdCB5b3Ugd2lsbCBjb250aW51ZSB0byB3b3JrIG9uIHRoaXMgc2NyaXB0IG9yIHRoYXQgeW91IHdhbnQgdG8gd29yay4KT2YgY291cnNlIHlvdSB3ZXJlIHRvbGQgdGhhdCB5b3Ugd2lsbCBoYXZlIGEgbG90IG9mIHByb2plY3RzIHRvIGRvIGFuZCB0aGF0IHRoZXkgYXJlIGV4Y2VsbGVudCBwZW9wbGUgYW5kIHRoYXQgdGhleSB3aWxsIHBheSBldmVyeSBwZW5ueSAuLi4uCkkgd29ya2VkIGFsb25lIGRheSBhbmQgbmlnaHQgYW5kIHdva2UgdXAgd2l0aCBhIGJpZyBraWNrIGluIHRoZSBhc3MuIFRha2UgY2FyZSEKSSBjYW4ndCB0ZWxsIHlvdSB0aGUgd2hvbGUgc3RvcnkgaGVyZSwgYnV0IEkgd2FybmVkIHlvdSEKU3VjY2VzcyE=

########################
*/

namespace App\includes;
use App\includes\middlewares\Middleware;
use App\includes\Main;
use App\includes\library\cPanelApi;
use GuzzleHttp\Client;

class Model{

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $userAnsprech, $userKandidates, $text = []; 


  public function checkIsLogged($depth = ''){
    $check = new Middleware($this->config, $this->db);
    if($this->user = $check->checkLevel($this->accessLevel, $depth)){
      $this->text['u'] = $this->user;
    }
  }


  public function getAnsprechpartners($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getAnsprechers($id)){
      $this->text['anspr'] = $this->userAnsprech;
    }
  }


  public function getStatsPayiment($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getStatsPayimentFirms($id)){
      $this->text['pym'] = $this->userAnsprech;
    }
  }


  public function getAnzeigen($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getMeineAnzeigen($id)){
      $this->text['anz'] = $this->userAnsprech;
    }
  }

  public function getRechnungen($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getMeineRecgnungens($id)){
      $this->text['rchngs'] = $this->userAnsprech;
    }
  }

  public function getAllAlerts($id = FALSE){
    $checkAlerts = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAlerts->getKandidatAlerts($id)){
      $this->text['alerts'] = $this->userAnsprech;
    }
  }

  public function getFrmsAlerts($id = FALSE){
    $checkAlerts = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAlerts->getFrmAlerts($id)){
      $this->text['alerts'] = $this->userAnsprech;
    }
  }

  public function getPublicAnzeigen($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getOpenAnzeigen($id)){
      $this->text['anz'] = $this->userAnsprech;
    }
  }


  public function getPublicUnternehmen($id = FALSE){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getOffenFirmaProfil($id)){
      $this->text['anzu'] = $this->userAnsprech;
    }
  }

  public function getTicker(){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getPublicTicker()){
      $this->text['tick'] = $this->userAnsprech;
    }
  }


  public function getFavoAnzeigen(){
    $checkAnspr = new Middleware($this->config, $this->db);
    if($this->userAnsprech = $checkAnspr->getFavoAnzeigen()){
      $this->text['anz'] = $this->userAnsprech;
    }
  }

  public function getPublicKandidaten($logged = FALSE, $id = FALSE){
    $checKandi = new Middleware($this->config, $this->db);
    if($this->userKandidates = $checKandi->getOpenKandidaten($logged, $id)){
      $this->text['knd'] = $this->userKandidates;  
    }
  }

  public function getFirmLiebsteKandidaten($bewerb = FALSE){
    $checKandi = new Middleware($this->config, $this->db);
    if($this->userKandidates = $checKandi->getFavKandidaten($bewerb)){
      $this->text['knd'] = $this->userKandidates;  
    }
  }

  public function getAdminStats(){
    $checks = new Middleware($this->config, $this->db);
    if($this->adminStats = $checks->getAllStats()){
      $this->text['adm'] = $this->adminStats;  
    }
  }

  public function adminkandidatenList(){
    $checks = new Middleware($this->config, $this->db);
    if($this->adminStats = $checks->getAdminKandidatenList()){
      $this->text['adkandt'] = $this->adminStats;  
    }
  }

  public function adminFirmenList($bewerb = FALSE){
    $checKandi = new Middleware($this->config, $this->db);
    if($this->userKandidates = $checKandi->getAdminFirmenList($bewerb)){
      $this->text['adkmlst'] = $this->userKandidates;  
    }
  }


  public function getAdmStellen(){
    $checks = new Middleware($this->config, $this->db);
    if($this->adminStats = $checks->getAdminAnzeigensList()){
      $this->text['adstell'] = $this->adminStats;  
    }
  }


  public function getAdmBewerbt(){
    $checks = new Middleware($this->config, $this->db);
    if($this->adminStats = $checks->getAdminBewerbt()){
      $this->text['adbww'] = $this->adminStats;  
    }
  }

  

  public function getAdminsList($id = FALSE){
    $checks = new Middleware($this->config, $this->db);
    if($this->adminStats = $checks->getAdmins($id)){
      if(!$id){
        $this->text['admnis'] = $this->adminStats; 
      }else{
        return $this->adminStats; 
      }
    }
  }


  public function getPruffEmail(){ // pruffs
    if(!$info = Main::user()){
      $this->text['pruffs'] = null;
    } 

    if($pruff = $this->db->get('allusers', array("select"=>"usermail, activated", 'where'=>array('unique_key'=>$info[2]), 'limit'=>1, 'return_type'=>'single'))){	
      $this->text['pruffs'] = [$pruff['activated'], $pruff['usermail']];
    }else{
      $this->text['pruffs'] = null;
    }
  }


  public function getEmailVorlagen($id = FALSE){
    if($vorlg = $this->db->get("emailsvorlagen", array("select"=>"crt, email, content", 'return_type'=>'all'))){	
        $this->text['vorlagen'] = $vorlg; 
      }
    return false;
  }

  public function getAlerts($table, $id, $alerts){  /// kandidatealerts  getAlerts("getAlerts", 2, "fgdfe");
    $res = [];
    if($a = explode(',', $alerts)){
      foreach($a as $v){
        $bf = explode('-', $v);
        $beruf = filter_var($bf[0], FILTER_SANITIZE_STRING);
        $fach = isset($bf[1]) ? filter_var($bf[1], FILTER_SANITIZE_STRING) : '';
        if(!$aa = $this->db->get($table, array("select"=>"crt", 'where'=>array('usid'=>$id, 'beruf'=>$beruf, 'fach'=>$fach), 'limit'=>1, 'return_type'=>'single'))){	
          $res[] = array("beruf"=>$beruf, "fach"=>$fach);
        }
      }
    }
    return $res;
  }


  public function getFrimAlerts($table, $id, $alerts){   
    $res = [];
    if($a = explode(',', $alerts)){
      foreach($a as $v){
        $beruf = filter_var($v, FILTER_SANITIZE_STRING);
        if(!$aa = $this->db->get($table, array("select"=>"crt", 'where'=>array('usid'=>$id, 'beruf'=>$beruf), 'limit'=>1, 'return_type'=>'single'))){	
          $res[] = array("beruf"=>$beruf);
        }
      }
    }
    return $res;
  }


  /*  Pagination here starts */
  public function getAllResults($where){
    if($where == "kandidates"){
       return "onWork";
    }elseif($where == "stellenangebote"){
      if($aallAnz = $this->db->get("anzeigen", array("select"=>"anzeigen.crt, paystats.crt as pprt", "join"=>array(
        array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))
        ),'where'=>array('paystats.ison'=>3), 'return_type'=>'count'))){	
          return $aallAnz;
        }
      return 0;
    }else{
      return false;
    }
  }
  /* pagination here ends */


  public function deltDoStellens($id){
    if($anz = $this->db->get("paystats", array("select"=>"billnr, datactivat, gultigbis", 'where'=>array('anzid'=>$id), 'limit'=>1, 'return_type'=>'single'))){	
      if($anz['datactivat'] || $anz['gultigbis'] || $anz['sichtbarbis']){ // a fost activat
        if($anzRech = $this->db->get("rechnungen", array("select"=>"gebucht", 'where'=>array('crt'=>$anz['billnr']), 'limit'=>1, 'return_type'=>'single'))){	
          if(!$anzRech['gebucht'] || empty($anzRech['gebucht'])){
            return true;
          }

          $gebucht = unserialize($anzRech['gebucht']);
          if (in_array($id, $gebucht)) {
            unset($gebucht[array_search($id, $gebucht)]);
          }
          $gebucht = array_values($gebucht);
          $gebucht = serialize($gebucht);

          $upRech = $this->db->update("rechnungen", array('gebucht'=>$gebucht), array('crt'=>$anz['billnr']));
        }
        return true;
      }else{
        return true;
      }
    }
  return true;
  }






  public function doSetAktive($usid, $anzID, $RechnungID){
    if($single = $this->db->get("rechnungen", 
                array("select"=>"rechnungen.crt, rechnungen.nrstellen, rechnungen.gebucht, rechnungen.rechstat, paystats.ison, paystats.datactivat, paystats.gultigbis", 
                "join"=>array(
                array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.usid"=>"rechnungen.usid", "paystats.billnr"=>"rechnungen.crt"))),
                'where'=>array('rechnungen.crt'=>$RechnungID, 'rechnungen.usid'=>$usid), 'limit'=>1, 'return_type'=>'single'))){

      if($single['rechstat'] == 3){
        return array("stat"=>5); // nu este platita factura  ~~~~timp de 8 zile~~~~   "paystats.anzid"=>$anzID  "paystats.anzid"=>$anzID
      }elseif($single['rechstat'] == 6 || $single['rechstat'] == 5 || $single['rechstat'] == 4){
        return array("stat"=>4); /// cumpara paket
      }

      // if($single['ison'] == 3){
      //   return array("stat"=>1); // este deja active
      // }

      if($single['gebucht'] && !empty($single['gebucht'])){
        $gebucht = unserialize($single['gebucht']);
        $manyGebucht  = count($gebucht);  
        $nr = ($single['nrstellen']-$manyGebucht);
        if( $nr >= 1  ){
          if(in_array($anzID, $gebucht)){  
            return array("stat"=>3, "gebucht"=>$gebucht, "reactivate"=>"reactivate");  // === REACTIVATE THE SAME INSERAT
          } else{
            return array("stat"=>3, "gebucht"=>$gebucht); // OKI DOKI!!! ACTIVEAZA-l
          } 
        }else{
          if(!$single['datactivat'] || !$single['gultigbis']){ // NOU NEW
            return array("stat"=>4); /// cumpara paket
          }else{
            if(in_array($anzID, $gebucht)){  
              return array("stat"=>3, "gebucht"=>$gebucht, "reactivate"=>"reactivate");  // === REACTIVATE THE SAME INSERAT
            }else{
              //return array("stat"=>3, "gebucht"=>$gebucht);
              return array("stat"=>4); /// cumpara paket
            } 
          }
        }
      }else{
        return array("stat"=>3, "gebucht"=>[]); // OKI DOKI!!! ACTIVEAZA-l
      }
    }
    return false;
  }


  public function prufPlans($myid, $anzid = ''){    //  ???????????????????????   este comentat
    if($singlePlans = $this->db->get("payments", 
                array("select"=>"payments.prt, payments.nrstellen, payments.rechnnr, paystats.crt, paystats.planid", "join"=>array(
                array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.usid"=>"payments.usid", 
                "paystats.planid"=>"payments.theplan", "paystats.billnr"=>"payments.rechnnr"))),
                'where'=>array('payments.usid'=>$myid, 'payments.anzeigeid'=>0, 'payments.paystatus'=>1, "paystats.anzid"=>0), 'return_type'=>'all'))){

        foreach($singlePlans as $k => $val){  

          if($val['nrstellen'] == 1){

            $updPay = $this->db->update("payments", array("anzeigeid"=>$anzid, "nrstellgebucht"=>1), array("prt"=>$val['prt']));

            $updPayStat = $this->db->update("paystats", array("anzid"=>$anzid), array("crt"=>$val['crt']));

            $updPayAnzeig = $this->db->update("anzeigen", array("payid"=>$val['rechnnr'], "planid"=>$val['planid']), array("crt"=>$anzid));

            return array("stellen"=>1);
          }
          /*
          if($this->db->doquery("UPDATE allusers SET tot_visits = tot_visits + 1 WHERE crt = ".$this->id)){
          $upd = $this->db->update("allusers", array('on_line'=>1), array('crt'=>$this->id));
        } */
          
      }
    }
    return false;
  }



  public function checkLogin(){
    $logged = new Middleware($this->config, $this->db);
    $logged->checkAnmeldung();
  }


  public function prufUser($uniq){ 
    if(!$info = Main::user()){
      return FALSE;
    }elseif($info[2] != $uniq){
      return FALSE;
    }else{
      if($user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$uniq),'limit'=>1,'return_type'=>'single'))){								
        return $user['crt'] >= 1 ? $user['crt'] : FALSE;
      }
    }
  return FALSE;
  }

  public function prufAltAdmin($uniq){ 
    if($user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$uniq),'limit'=>1,'return_type'=>'single'))){								
      return $user['crt'] >= 1 ? $user['crt'] : FALSE;
    }
  return FALSE;
  }

  public function prufOpen_User($uniq){ 
      if($user = $this->db->get("allusers", array('select'=>'crt', 'where'=>array('isdel'=>2, 'banned'=>2, 'unique_key'=>$uniq),'limit'=>1,'return_type'=>'single'))){								
        return $user['crt'] >= 1 ? $user['crt'] : FALSE;
      }
  return FALSE;
  }

  public function prufAnsprechpartner($uniq, $mein = FALSE){ 
    if($user = $this->db->get("ansprechpartners", array('select'=>'crt, usid', 'where'=>array('unique_key'=>$uniq),'limit'=>1,'return_type'=>'single'))){
      if($mein){
        return $user['usid'] >= 1 ? [$user['crt'], $user['usid']] : FALSE;
      }								
      return $user['crt'] >= 1 ? $user['crt'] : FALSE;
    }
  return FALSE;
  }


  public function sendmail($to, $subject, $message, $template = 1){
    $mail["to"]=Main::clean($to, 3);
    $mail["subject"]= $subject;							
    $mail["message"] = $message;
    if(!Main::send($mail, $template)) {

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', " ++++++++++++++++ Mail is not sent +++++++++++++++++++ "."\n", FILE_APPEND);
      return false;
    }
    return true;
}


public function getAnzeigeBewerbt($k, $f, $a){

  $kandidat = $this->db->get("allusers", array('select'=>'userfstname, userlstname, usermail', 'where'=>array('isdel'=>2, 'banned'=>2, 'crt'=>$k),'limit'=>1,'return_type'=>'single'));

   $firma = $this->db->get("allusers", 
         array("select"=>"allusers.crt, frmpreffs.vorname, allusers.unique_key, frmpreffs.name, frmpreffs.email", "join"=>array(
         array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"allusers.crt"))),
         'where'=>array('allusers.crt'=>$f, 'allusers.isdel'=>2), 'limit'=>1, 'return_type'=>'single'));

  $anz = $this->db->get("anzeigen", array('select'=>'titel', 'where'=>array('crt'=>$a),'limit'=>1,'return_type'=>'single'));


  return array('kandidat'=>$kandidat, 'firma'=>$firma, 'anzeige'=>$anz);
}


  public function QQQQQQQsendmail($to, $subject, $message){
      //$activate="{$this->config["url"]}/user/activate/$unique";
     // $activate="LINKUL";
      $mail["to"]=Main::clean($to, 3);
      // $mail["to"]="alenolteanu@gmail.com";
      $mail["subject"]= $subject;							
      /* $mail["message"]="<b>Hello!</b>
      <p>You have been successfully registered at DocSite. To login you will have to activate your account by clicking the URL below.</p>
      <p><a href='$activate' target='_blank'>$activate</a></p>"; */
      $mail["message"] = $message;

      if(!Main::send($mail)) {
        return false;
      }
      return true;
  }


  public function getForChats(){
    $checks = new Middleware($this->config, $this->db);
    if($chats = $checks->getAllChatsUsers()){
      $this->text['uchat'] = $chats;  
    }
  }

  public function getForKandidatesChats(){
    $checks = new Middleware($this->config, $this->db);
    if($chats = $checks->getAllChatsKandidates()){
      $this->text['uchat'] = $chats;  
    }
  }

  public function getAdminChats(){
    $checks = new Middleware($this->config, $this->db);
    if($chats = $checks->getAdminsChats()){
      $this->text['uchat'] = $chats;  
    }
  }


  public function doMailsLists(){
    $api = new cPanelApi("doc-site.de","vwegdwvs", "(D23XhWst;6Ty5");
    if(!$theApi = $api->listEmail()){
      return FALSE;
    }

    $mailLists =  json_decode($theApi, true);
    if($mailLists['data']){
      $aktuelList = array();
      foreach($mailLists['data'] as $key => $val){
        array_push($aktuelList, $val['email']);
      }
      return  $aktuelList;
    }
    return  FALSE;
  }  
  
  
  public function doMailsProperty($id, $mailid){
    if(!$mid = $this->db->get("mailserver", array('select'=>'usid, tosid, mailstat', 'where'=>array('crt'=>$mailid), 'limit'=>1,'return_type'=>'single'))){
      return false;
    }

    if($mid['usid'] == $id ){
      return array('usidview', $mid['mailstat']);
    }

    if($mid['tosid'] == $id ){
      return array('tosidview', $mid['mailstat']);
    }

  return false;
  }


  public function getPayPlansDets($id){
    if($pn = $this->db->get("payplans", array('select'=>'planname, planstellen, planprice, planweeks, planmonths, planlanger', 'where'=>array('crt'=>$id), 'limit'=>1,'return_type'=>'single'))){
      return $pn;
    }
    return '-';
  }

  public function rechPattern($nr){
    $pattern = '/0/i';
    $n =preg_replace($pattern, '', $nr);
    if($n >= 1){
      return $n;
    }
    return false;
  }


  public function makeRechnung($rechn = array()){ // $this->makeRechnung(array("usid"=>$thisUser, "planid"=>$params->doit, "verlng"=>$verTimes, "planname"=>$plan['planname'], "rechval"=>$plan['planprice'], "rechstat"=>3, "anzeigeid"=>$verLang, "rabat"=>$theRabat, "rabatval"=>$discountWert));
    $stSerr = Main::config('seriennr');
    $stSerr = $stSerr ? $stSerr : "";
    if(!$pn = $this->db->get("rechnungen", array('select'=>'rechint', 'limit'=>1,'return_type'=>'single'))){
      $stInt = Main::config('rechnrn');
      if(!$rP = $this->rechPattern($stInt)){
        $stInteger = 1;
        $stInt = $stInt.$stInteger;
      }else{
        $stInteger = $rP;
        $stInt = $stInt.$stInteger;
      }

      $rechNr = $stSerr." ".$stInt;

      $rechn['rechint'] = $stInteger; 
      $rechn['rechnunr'] = $rechNr; 
    }else{
      $pn = $this->db->get("rechnungen", array('select'=>'rechint, rechnunr', 'limit'=>1, 'order_by'=>'crt DESC', 'return_type'=>'single'));
      $rechn['rechint'] = ($pn['rechint']+1); 

      $stSerr = Main::config('seriennr');
      $stInt = Main::config('rechnrn');
      $stSerr = $stSerr ? $stSerr : "";

      $rechn['rechnunr'] = $stSerr.$stInt.($pn['rechint']+1);
    }

    $anzeigeid = $rechn['anzeigeid'] ? $rechn['anzeigeid'] : 0;
    unset($rechn['anzeigeid']);
    
    if(!$insertRechnung = $this->db->insert("rechnungen", $rechn)){
      return false;
    }

    // $payverlng = $anzeigeid >= 1 ? 2 : 1;
    // array("usid"=>$thisUser, "planid"=>$params->doit, "verlng"=>$verTimes, "planname"=>$plan['planname'], "rechval"=>$plan['planprice'], "rechstat"=>3, "anzeigeid"=>$verLang)
    /* 
    if( $payverlng == 1) { // only for firsr payment type
      if(!$insertPayStat = $this->db->insert("paystats", array("usid"=>$rechn['usid'], "anzid"=>$anzeigeid, "ison"=>2, 'planid'=>$rechn['planid'], 'payverlng'=>$payverlng, 
      'billnr'=>$insertRechnung))){
        $delP = $this->db->delete("rechnungen", array('crt'=>$insertRechnung));
        return false;
      }
    } */

    return $insertRechnung;
  }


  public function checkStartUps($thisUser, $planid, $startUp = FALSE){
    if($chekStartup = $this->db->get("payments", array('select'=>'prt, theplan, paystatus', 'where'=>array('isdel'=>2, 'usid'=>$thisUser), 'return_type'=>'all'))){
      if($startUp){
        foreach($chekStartup as $k => $val){
          // if($val['paystatus'] == 1 && $val['theplan'] >= 1){
          //   $p = $this->getPayPlansDets($val['theplan']);
          //   return e('Sie haben den Plan bereits reserviert.:')." ".$p['planname'];
          // }else

          if($val['paystatus'] == 3 && $val['theplan'] == 1){
            $p = $this->getPayPlansDets($val['theplan']);
            return e('Sie haben den Plan bereits reserviert.:')." ".$p['planname'];
          }elseif($val['paystatus'] == 1 && $val['theplan'] == 1){
            return e('Kann nur einmal verwendet werden!');
          }elseif($val['paystatus'] == 1 && $val['theplan'] >= 2){
            return e('Dieser Plan ist nur für Neukunden!');
          }
        }
      }elseif(!$startUp){
        foreach($chekStartup as $k => $val){
          if($val['paystatus'] == 3 && $val['theplan'] >= 2 && $val['theplan'] == $planid){
            $p = $this->getPayPlansDets($val['theplan']);
            return e('Sie haben den Plan bereits reserviert.:')." ".$p['planname'];
          }
        }
      }
   }
   return false;
  }


  public function checkRabatt($rabat, $planID){
    if(!$r = $this->db->get("rabatt", array('select'=>'crt, rabatt', 'where'=>array('ison'=>2, 'planid'=>$planID), 'return_type'=>'all'))){
      return false;
    }
    foreach($r as $k => $val){
      if($val['rabatt'] == $rabat){
        $rabat = explode('-', $rabat);
        return $rabat[0];
      }
    }
    return false;
  }


    public function checkAngebots($arr){
      if($arr){
        foreach($arr as $k => $val){
          if(empty($val['gultig']) && $val['times'] >= 1){
              if($val['mal'] >= $val['times']){
                unset($arr[$k]);
              }
          }elseif(!empty($val['gultig']) && $val['times'] == 0){
            $today = strtotime(date("Y-m-d"));
            $expire = strtotime($val['gultig']);  
            if ($expire < $today) { 
              unset($arr[$k]);
            }
          }elseif(!empty($val['gultig']) && $val['times'] >= 1){
            $today = strtotime(date("Y-m-d"));
            $expire = strtotime($val['gultig']); 
            if ($expire < $today) { 
              unset($arr[$k]);
            } 
            if ($val['mal'] >= $val['times']) {
              unset($arr[$k]);
            } 
          }
        }
        return $arr;
      }
      return false;
    }


  public function getAngebots($id = FALSE, $planid = FALSE){
    if($planid && $planid >= 1){
      if(!$a = $this->db->get("angebots", array('select'=>'plannr, messtext, whatbuy, whatoffer, times, mal, gultig', 'where'=>array('ison'=>2, 'plannr'=>$planid), 'limit'=>1, 'return_type'=>'single'))){
        return false;
      }
      $angebot = $this->checkAngebots($a);
    }elseif($id && $id >= 1){
      if(!$b = $this->db->get("angebots", array('select'=>'plannr, messtext, whatbuy, whatoffer, times, mal, gultig', 'where'=>array('ison'=>2, 'crt'=>$id), 'limit'=>1, 'return_type'=>'single'))){
        return false;
      }
      $angebot = $this->checkAngebots($b);
    }else{
      if(!$c = $this->db->get("angebots", array('select'=>'plannr, messtext, whatbuy, whatoffer, times, mal, gultig', 'where'=>array('ison'=>2), 'return_type'=>'all'))){
        return false;
      }
      $angebot =  $this->checkAngebots($c);
    }
    return $angebot; 
  }


  // $theFiltersConditions['ip_from'] = $ip;   The ip2long() and long2ip()
     // "between"=>array($ip_num, array("start"=>"end")), 
  public function haveWeGeo($ip='', $coords = ''){
    $els = array('select'=>'country_code, city_name, latitude, longitude', 'limit'=>1, 'return_type'=>'single');
    if($ip){
      $els['between'] = array(ip2long($ip), array("ip_from"=>"ip_to"));
    }elseif($coords){
      $theFiltersConditions['between'] = array(ip2long($ip), array("latitude"=>"longitude"));
    }else{
      return false;
    }

    if(!$infos = $this->db->get("geosip", $els)){
      return false;
    }
    return $infos;
  }  

  public function geoFinder($ort = '', $country = 'DE', $coords = ''){ 
    if(!$ort && !$coords){
      $ip = Main::ip();
      if($city = $this->haveWeGeo($ip)){
        return $city;
      }
      return false;
    }elseif($ort && !$coords){
      if($coordinates = Main::get_lat_long($ort,  $country)){
        $coordinates = explode(',', $coordinates);
        return array('country_code'=>$country, 'city_name'=>'', 'latitude'=>$coordinates[0], 'longitude'=>$coordinates[1]);
      }
      return false;
    }

   } 


  public function checkRadius($id, $lati, $longi, $dist){
   $sql = '';

    $sql = "SELECT art, ( 6371 * acos( cos( radians($lati) ) 
    * cos( radians( latit ) ) 
    * cos( radians( longit ) - radians($longi) ) + sin( radians($lati) ) 
    * sin( radians( latit ) ) ) ) as distenta FROM anzeigen_view "; 
    $sql .= " WHERE art=$id ";
    $sql .= "  HAVING distenta <= ".$dist."";


    $result = $this->db->doQuery($sql);
    if($result->rowCount() > 0){
      $data = $result->fetch();
    }else{$data= FALSE;}
    
    return $data;  
  }  
  
  
  /* Share on Socials */

  public function isShared($id){ // 1 Facebook, 2 Linkedin
    if(!$shr = $this->db->get('autoshares', array('select'=>'crt', 'where' => array('anzid'=>$id), 'limit' => 1, 'return_type' => 'single'))){    
      $this->db->insert("autoshares", array('anzid'=>$id));
    }
    return FALSE;
  }

  public function itWasShared($soc = ''){
    if($shr = $this->db->get('autoshares', array('select'=>'anzid, facebook, linkedin', 'where'=>array('social'=>0),  'limit'=>1, 'return_type' => 'single'))){ 

      if($shr[$soc] >= 1){ return FALSE; } // 'where' => array('social' => 0),

      if($besch = $this->db->get('anzeigen', array('select'=>'anzeigen.titel, anzeigen.beschreibung, anzeigen.anzlogo', 
        'join'=>array(array("type"=>"join", "table"=>"paystats", "on"=>array("paystats.anzid"=>"anzeigen.crt"))),
        'where' => array('anzeigen.crt'=>$shr['anzid'], 'paystats.ison'=>3), 'limit' => 1, 'return_type' => 'single'))){
        return !empty($besch['beschreibung']) && !empty($besch['anzlogo']) ? ['id'=>$shr['anzid'], 'anzid'=>Main::encrypt($shr['anzid']), 'besch'=>$besch['beschreibung'], 'ttitel'=>$besch['titel'], 'logo'=>$besch['anzlogo']] : FALSE; 
      }
      return FALSE;
    }
    return FALSE;
  }   

  // Facebook 1 moth - issued 05.07.2022
  public function facebook(){

    if(!$shred = $this->itWasShared('facebook')){
      return FALSE;
    }

    $fb = new \Facebook\Facebook([
          'app_id' => '750148829465198',
          'app_secret' => '52c8d2a7090a78fb173e15de38e5b8de',
          'default_graph_version' => 'v2.2',
        ]);


          $linkData = [
            'link' => 'https://doc-site.de/einzelheiten/'.$shred['anzid'],
            'message' => $shred['besch']
          ];
          $pageAccessToken ='EAAKqQaKCSm4BACcmCtavT2NQWZCqDMtMZAWT9x0LqJIRVHV4qgaVzrNfzXNsmNZBQnno5OBaTsZBHSapJyVwTu4UVs4ljZCpXFcBBqoae6BwGtFDPLY4KehZB1AMJrrVsVsYVvSEX0vlP1DsWBXQ5gCIf8TLHmD7H343LRYRpXFbGAkTeGIShn';
          
          try {
            $response = $fb->post('/me/feed', $linkData, $pageAccessToken);
          } catch(Facebook\Exceptions\FacebookResponseException $e) {
            return FALSE;
          } catch(Facebook\Exceptions\FacebookSDKException $e) { // echo 'Facebook SDK returned an error: '.$e->getMessage();
            return FALSE;
          }
          //$graphNode = $response->getGraphNode();

          $upd = $this->db->update("autoshares", array('facebook'=>1), array('anzid'=>$shred['id']));

          return FALSE;
  }

  // LinkedIn  // 2 moths - issued 07.07.2022
  public function linkedin(){

    if(!$shred = $this->itWasShared('linkedin')){
      return FALSE;
    }

    $link = 'https://doc-site.de/einzelheiten/'.$shred['anzid'];
    $access_token = LINKEDIN_SHARE_ACCESS_TOKEN;
    $linkedin_id = LINKEDIN_SHARE_ID;
    $body = new \stdClass();
    $body->content = new \stdClass();
    $body->content->contentEntities[0] = new \stdClass();
    $body->text = new \stdClass();
    $body->content->contentEntities[0]->thumbnails[0] = new \stdClass();
    $body->content->contentEntities[0]->entityLocation = $link;
    $body->content->contentEntities[0]->thumbnails[0]->resolvedUrl = "https://doc-site.de/static/uploads/anzeige/".$shred['id']."/".$shred['logo'];
    $body->content->title = $shred['ttitel'];
    $body->owner = 'urn:li:person:'.$linkedin_id;
    $body->text->text = $shred['besch'];
    $body_json = json_encode($body, true);
      
    try {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('POST', '/v2/shares', [
            'headers' => [
                "Authorization" => "Bearer " . $access_token,
                "Content-Type"  => "application/json",
                "x-li-format"   => "json"
            ],
            'body' => $body_json,
        ]);
      
        if ($response->getStatusCode() !== 201) {
           // echo 'Error: '. $response->getLastBody()->errors[0]->message;
           return FALSE;
        }
       // echo 'Post is shared on LinkedIn successfully.';
    } catch(Exception $e) {
      //  echo $e->getMessage(). ' for link '. $link;
      return FALSE;
    }

    $upd = $this->db->update("autoshares", array('social'=>2, 'linkedin'=>1), array('anzid'=>$shred['id']));

    return true;
}




  /* Share on Socials */


  public function destroiy(){
    unset($_COOKIE['loowt']); 
   // setcookie("loowt", "", time() - 3600, "/", DOMAIN, true, true);
    setcookie("loowt","", [
			'expires' => time() - 3600,
			'path' => '/',
			'domain' => DOMAIN,
			'secure' => TRUE,
			'httponly' => TRUE,
			'samesite' => 'None',
	  ]);    
    session_destroy();

    Main::redirect("anmeldung"); 
    die();
  }
  

}
?>