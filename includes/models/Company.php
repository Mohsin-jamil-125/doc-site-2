<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Tools;
use App\includes\Main;

class Company extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];


    $this->text = ["title"=>e("Dasboard"), "company"=>$this->accessLevel, "color"=>"dark"];
  }



  public function doRabatt($params){
    if(!$this->user = Main::user()){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"Err3");  // "doit":"100-430489","dowher":"3" 
    }

    $rabat = strtoupper( filter_var($params->doit, FILTER_SANITIZE_STRING) );
    $planID = isset($params->dowher) ? filter_var($params->dowher, FILTER_SANITIZE_NUMBER_INT) : "";

    if(!$rabat || !strlen($rabat) == 10){   
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"Err1");
    }

    if(!$r = $this->checkRabatt($rabat, $planID)){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"Err2   ".$rabat);
    }
    return array("user_error"=>1, "user_reason"=>1, "msg"=>$r, "msgrr"=>Main::encrypt($rabat));  
  }



  public function doFrimPay($params = false){ 
    if($params){
        header("Content-Type: application/json; charset=UTF-8");

        if(!$params->doit || !$params->dates  || !$params->radioPay ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")." 1")));
        }

        if(!$this->user = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")." 2")));
        }


        if(!$pOpts = json_decode(Main::config('paymethods'), true)){   
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")." 3")));
        }

        $a = array();
        foreach($pOpts as $k => $pays){
          if($pays && ($pays == $k)){  
            array_push($a, $k);
          }
        }
        if(!in_array($params->radioPay, $a)) { return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Unbekannte Zahlungsmethode!")))); }

        $paraDates = explode('-', $params->dates);

        if(!$thisUser = $this->prufUser($paraDates[0])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." 4")));
        }

        if(($this->user[0] != $thisUser) || $this->user[3] != 'firma'){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." 5")));
        }

        if($params->vrlng){  
          $verLang = Main::decrypt($params->vrlng);
          if(!$verLang  || !$verLang >= 1 || !$plan = $this->db->get("anzeigen", array('select'=>'crt', 'where'=>array('crt'=>$verLang, 'usid'=>$thisUser), 'limit'=>1, 'return_type'=>'single'))){  
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!')." 888".$verLang )));
          }
        }else{
          $verLang = '';
        }

        if(!$verLang || !$verLang >= 1){
          if($params->doit == 1){
            if($cK = $this->checkStartUps($thisUser, $params->doit, TRUE)){
              return array("user_error"=>1, "user_reason"=>3, "msg"=>$cK);
            }
          }elseif($params->doit >= 2){
            if($cK = $this->checkStartUps($thisUser, $params->doit)){
              return array("user_error"=>1, "user_reason"=>3, "msg"=>$cK);
            }
          }else{
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
          }
        }


        if($params->rrabbt && !empty($params->rrabbt)){  
          $rrabbt = Main::decrypt($params->rrabbt);
          if(!$theRabat = $this->checkRabatt($rrabbt, $params->doit)){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Der Rabattcode ist abgelaufen!'))));
          }
        }else{ $theRabat = 0; $discountWert = 0;}


        $pdo =  $this->db->getConnection();
        try {
          $pdo->beginTransaction();

          if($verLang){
            $plan = $this->db->get("payplans", array('select'=>'planname, planstellen, planlangerval as planprice, planlangerwoch as planweeks, planmonths, planlanger', 'where'=>array('crt'=>$params->doit), 'limit'=>1,'return_type'=>'single'));
            $nrstellen = 1;
            $nrstellenGeb = 1;

          }else{
            $plan = $this->db->get("payplans", array('select'=>'planname, planstellen, planprice, planweeks, planmonths, planlanger', 'where'=>array('crt'=>$params->doit), 'limit'=>1,'return_type'=>'single'));
            $nrstellen = $plan['planstellen'];
            $nrstellenGeb = 0;

            if($theRabat && $theRabat >= 1){
              $priceBefore = $plan['planprice']; // 100 euro
              $plan['planprice'] = Main::rabatPrice($priceBefore, $theRabat); // 75 euro
              $discountWert = ($priceBefore-$plan['planprice']);
              $discountWert = number_format((float)$discountWert, 2, '.', ''); // 25.00 euro
            }
          }


          $rechnInsert = $this->makeRechnung(array("usid"=>$thisUser, "planid"=>$params->doit, "nrstellen"=>$nrstellen, "planname"=>$plan['planname'], "rechval"=>$plan['planprice'], "rechstat"=>3, "anzeigeid"=>$verLang, "rabat"=>$theRabat, "rabatval"=>$discountWert));
          
          $paymitVAT = Main::addVAT($plan['planprice']);  ///   nrstellen  nrstellgebucht  'paystats.ishidd' => 2  nrstellen

          $anzeigeidInsert = $verLang >= 1 ? $verLang : 0;

          $insertPlan = $this->db->insert("payments", array('usid'=>$thisUser, 'rechnnr'=>$rechnInsert, 'anzeigeid'=>$anzeigeidInsert, 'theplan'=>$params->doit, 'nrstellen'=>$nrstellen, 'nrstellgebucht'=>$nrstellenGeb, 'planname'=>$plan['planname'], 'paymetod'=>$params->radioPay, 'paysume'=>$plan['planprice'], 'paymitvat'=>$paymitVAT));


          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage())));
        }

        if($params->radioPay == "paypal"){
          if(!$paypalUrl = Tools::payPayPal( ["plan"=>$plan['planname'], "usrid"=>["userid"=>$thisUser, "planid"=>$params->doit, "planweeks"=>$plan['planweeks'], "planmonths"=>$plan['planmonths'], "payCrt"=>$verLang], "payId"=>$rechnInsert, "amount"=>$paymitVAT])){

            $upd = $this->db->update("payments", array('paystatus'=>2, "paystaterr"=>"DocSite - PayPal link Error" ), array('rechnnr'=>$rechnInsert));

            $updRechn = $this->db->update("rechnungen", array('rechstat'=>2), array('crt'=>$rechnInsert));

            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('PayPal-Zahlungen funktionieren derzeit nicht'))));
          } 
        }else{
          $paypalUrl = '';

          if($frm = $this->db->get("rechnungen", 
            array("select"=>"rechnungen.rechnunr, rechnungen.planid, rechnungen.planname, rechnungen.rechval, rechnungen.rabat, firmen.frmname, frmpreffs.email", "join"=>array(
            array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid")),
            array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"rechnungen.usid"))),
            'where'=>array('rechnungen.crt'=>$rechnInsert), 'return_type'=>'single'))){
             if($frm['rabat'] && $frm['rabat'] >= 1){ $derRabat = "(". $frm['rabat']."% Rabatt )"; }else{ $derRabat = "";}
                $emailDo = Main::emailCreate('email', array(     
                  'customsubject'=>'Doc-Site.: '.e('Bestellung erfolgreich registriert!'),   
                  'username'=>$frm['frmname'], 
                  'l1'=>e('Bestellung erfolgreich registriert!').'<br/>'.e('Sie können die Rechnung innerhalb von 8 Tagen bezahlen!').'<br/>'.e('Sobald der Rechnungsbetrag eingegangen ist, schaltet das System die Anzeige frei.'),
                  'l2'=>e('Rechnung').' '.$frm['rechnunr'].'<br/>'
                        .e('Leistung').' '.$frm['planname'].'<br/>'
                        .e('Preis').' '.$frm['rechval'].' '.Main::config('currency').' '.$derRabat.'<br/>'
                        .e('Zahlungsart').' '.e('Überweisung/Auf Rechnung').'<br/>',
                  'b1'=>e('Hier anmelden'),
                  'b1link'=>Main::config('url').'/anmeldung',
                  'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
                  'l5'=>Main::config('title')." TEAM"
                ));

                @$this->sendmail($frm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
                usleep(500);
          }

        }
        $_SESSION['ppl'] = $rechnInsert;
        return array("user_error"=>1, "user_reason"=>1, "lnkrdr"=>$paypalUrl, "typos"=>$params->radioPay, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg')))); 
    }else{
      //return array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr");
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"1 ".e('Fehler, versuche es erneut!')." 777")));
    }
  }


  public function doCheckPaypal($params = ''){
    if(!$this->user = Main::user()){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"1 ");
    }

    if($this->user[3] != 'firma'){
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"2 ");
    }

    if($_SESSION['ppl'] && !empty($_SESSION['ppl'])){
      $insertRechNr = filter_var($_SESSION['ppl'], FILTER_SANITIZE_NUMBER_INT);
      unset($_SESSION['ppl']); 
    }else{
      $insertRechNr = '';
    }

    if($insertRechNr >= 1){
      $plan = $this->db->get("payments", array('select'=>'prt, txn_id', 'where'=>array('rechnnr'=>$insertRechNr, 'usid'=>$this->user[0]), 'limit'=>1,'return_type'=>'single'));
    }else{
      $plan = $this->db->get("payments", array('select'=>'prt, txn_id', 'where'=>array('usid'=>$this->user[0]), 'order_by'=>'prt DESC',  'limit'=>1,'return_type'=>'single'));
    }

    if($plan){
      if(!empty($plan['txn_id'])){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>"");
      }else{
        return array("user_error"=>1, "user_reason"=>3, "msg"=>"");
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>"3 ");
    }
  }




}
?>