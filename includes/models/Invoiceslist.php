<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Invoiceslist extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();

    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" => e('Rechnungsliste'), "pgname" => e('Rechnungsliste'), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token'],
                  "enPP"=>["titel"=>e('Bestätigen Sie die Zahlung?'), "body"=>e('Hier bestätigen Sie die Zahlung für diese Rechnung'), "pbnt"=>e('Bestätigen')],
                  "stoPP"=>["titel"=>e('Diese Rechnung stornieren?'), "body"=>e('Hier bestätigen Sie die Stornierung für diese Rechnung'), "pbnt"=>e('Stornieren')]];
  }


  

  public function doStornoBestatg($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8"); 

      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      $frm = explode('-', $params->doit);  /// '.$val['crt'].'-'.$inputs['ai_token'].'-'.$inputs['public_token'].'

      if(!$crt = Main::decrypt($frm[0])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if(!$thisUser = $this->prufUser($frm[1])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($frm = $this->db->get("rechnungen", 
                array("select"=>"rechnungen.rechnunr, rechnungen.planid, rechnungen.verlng, rechnungen.planname, rechnungen.rechval, payments.anzeigeid, firmen.usid, firmen.frmname, frmpreffs.email", "join"=>array(
                array("type"=>"join", "table"=>"payments", "on"=>array("payments.rechnnr"=>"rechnungen.crt")),
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid")),
                array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"rechnungen.usid"))),
                'where'=>array('rechnungen.crt'=>$crt), 'return_type'=>'single'))){

        $pdo =  $this->db->getConnection();  //  weiterangabe
        try {
          $pdo->beginTransaction();

          $storno = $this->db->update("rechnungen", array("rechstat"=>6, "storno"=>1), array("crt"=>$crt));
          $stornoPaymnts = $this->db->update("payments", array('paystatus'=>6), array('rechnnr'=>$crt));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";invoiceslist")));  
        }

        $emailDo = Main::emailCreate('email', array(     
          'customsubject'=>'Doc-Site.: '.e('Rechnung storniert'),   
          'username'=>$frm['frmname'], 
          'l1'=>e('Rechnung storniert'),
          'l2'=>e('Wir bestätigen die Stornierung von Rechnung Nr.:').'  '.$frm['rechnunr'].'<br/>'
                .e('Leistung').' '.$frm['planname'].'<br/>'
                .e('Preis').' '.$frm['rechval'].' '.Main::config('currency').'<br/>',
          'b1'=>e('Hier anmelden'),
          'b1link'=>Main::config('url').'/anmeldung',
          'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),     
          'l5'=>Main::config('title')." TEAM"
        ));

        @$this->sendmail($frm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        usleep(200);
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";invoiceslist")));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>"Rechn  error");
      }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }


  public function doPymntBestatg($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8"); 

      if(!$this->user = Main::user()){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      $frm = explode('-', $params->doit);  /// '.$val['crt'].'-'.$inputs['ai_token'].'-'.$inputs['public_token'].'

      if(!$crt = Main::decrypt($frm[0])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
      }

      if(!$thisUser = $this->prufUser($frm[1])){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if(($this->user[0] != $thisUser) || $this->user[3] != 'admin'){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($frm = $this->db->get("rechnungen", 
                array("select"=>"rechnungen.rechnunr, rechnungen.planid, rechnungen.verlng, rechnungen.planname, rechnungen.rechval, payments.anzeigeid, firmen.usid, firmen.frmname, frmpreffs.email", "join"=>array(
                array("type"=>"join", "table"=>"payments", "on"=>array("payments.rechnnr"=>"rechnungen.crt")),
                array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid")),
                array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"rechnungen.usid"))),
                'where'=>array('rechnungen.crt'=>$crt), 'return_type'=>'single'))){


        $anzeigeid = !empty($frm['anzeigeid']) && ($frm['anzeigeid'] >= 1) ? $frm['anzeigeid'] : 0;

          $emailDo = Main::emailCreate('email', array(     
            'customsubject'=>'Doc-Site.: '.e('Bezahlung bestätigt'),   
            'username'=>$frm['frmname'], 
            'l1'=>e('Ihre Zahlung ist bestätigt!'),
            'l2'=>e('Rechnung').' '.$frm['rechnunr'].'<br/>'
                  .e('Leistung').' '.$frm['planname'].'<br/>'
                  .e('Preis').' '.$frm['rechval'].' '.Main::config('currency').'<br/>',
            'b1'=>e('Hier anmelden'),
            'b1link'=>Main::config('url').'/anmeldung',
            'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),     
            'l5'=>Main::config('title')." TEAM"
          ));



        $pdo =  $this->db->getConnection();  //  weiterangabe
        try {
          $pdo->beginTransaction();

          $fUpd = $this->db->update("rechnungen", array('rechstat'=>1), array('crt'=>$crt));

          $payPlans = $this->db->get("payplans", array("select"=>"planweeks, planmonths", 'where'=>array('crt'=>$frm['planid']), 'limit'=>'1', 'return_type'=>'single'));

          $startdate = date('Y-m-d');
          $gultig_bis = date('Y-m-d', strtotime("+{$payPlans['planweeks']} week", strtotime($startdate)));
          $sichtbar_bis = date('Y-m-d', strtotime("+{$payPlans['planmonths']} month", strtotime($startdate)));

          $fUpdPaymnts = $this->db->update("payments", array('paystatus'=>1, 'timepay'=>$startdate,  'gultigbis'=>$gultig_bis, 'sichtbarbis'=>$sichtbar_bis), array('rechnnr'=>$crt));

         // $fUpdStat = $this->db->update("paystats", array('ison'=>3, 'datactivat'=>date("Y-m-d H:i:s")), array('billnr'=>$crt)); Nu am ce paystat pt ca nu e inserted

         if($anzeigeid && $anzeigeid >= 1){
          $fUpdStat = $this->db->update("paystats", array('ishidd'=>1), array('anzid'=>$anzeigeid));
         }


         $insertPayStat = $this->db->insert("paystats", array("usid"=>$frm['usid'], "anzid"=>$anzeigeid ?$anzeigeid : 0, "ison"=>3, 'planid'=>$frm['planid'], 'payverlng'=>$frm['verlng'] , 'billnr'=>$crt, 'datactivat'=>date("Y-m-d H:i:s"), "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis));

          $pdo->commit();
        } catch (\PDOException $e) {
          $pdo->rollBack();
          //return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$e->getMessage()))); 
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";invoiceslist")));  
        }

          if($fUpdPaymnts && $payPlans){
            @$this->sendmail($frm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
            usleep(200);
            return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";invoiceslist")));
          }
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Fehler, versuche es erneut!').";invoiceslist")));
      }

    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
    }
  }




}
