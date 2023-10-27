<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Tools;
use App\includes\Main;

class Paypal extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  protected $allowedPayPal = ['verify', 'cancel'];
  public $users, $text = [];

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title"=>"PayPal", "company"=>$this->accessLevel, "color"=>"dark", "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }


/*
  public function doPayPalActions($params = ''){
    if(!is_array($params) || !in_array($params[0], $this->allowedPayPal) || method_exists("App\includes\models", $params[0])){
      Main::redirect("", array("danger", "Zahlungen sind derzeit nicht möglich!"));
    }
    return $this->{$params[0]}($params);
  } */


  public function doVerify(){
    Main::cookie("meinpay", "meinpay", FALSE);

    Main::redirect("/rechnungen");
  }


  public function doIpn($params = ''){
    $raw_post_array = explode('&', $params); 
    $myPost = array(); 
    foreach ($raw_post_array as $keyval) { 
      $keyval = explode ('=', $keyval); 
      if (count($keyval) == 2) 
          $myPost[$keyval[0]] = urldecode($keyval[1]); 
      } 
      $req = 'cmd=_notify-validate'; 

      foreach ($myPost as $key => $value) { 
        $value = urlencode(stripslashes($value)); 
        $req .= "&$key=$value"; 
      } 

      if(Main::config("paypal_sandbox") == 1){
        $paypal_url="https://www.sandbox.paypal.com/cgi-bin/webscr?";
      }else{
        $paypal_url="https://www.paypal.com/cgi-bin/webscr?";
      }
      $ch = curl_init($paypal_url); 
      if ($ch == FALSE) { 
          return FALSE; 
      } 

      curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
      curl_setopt($ch, CURLOPT_POST, 1); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
      curl_setopt($ch, CURLOPT_POSTFIELDS, $req); 
      curl_setopt($ch, CURLOPT_SSLVERSION, 6); 
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); 
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
      curl_setopt($ch, CURLOPT_FORBID_REUSE, 1); 
      // Set TCP timeout to 30 seconds 
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); 
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: doc-site')); 
      $res = curl_exec($ch); 
      /* 
      * Inspect IPN validation result and act accordingly 
      * Split response headers and payload, a better way for strcmp 
      */ 
      $tokens = explode("\r\n\r\n", trim($res)); 
      $res = trim(end($tokens)); 

      if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0) { 
        $item_number    =  isset($_POST['item_number']) ? filter_var($_POST['item_number'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $txn_id         = isset($_POST['txn_id']) ? filter_var($_POST['txn_id'], FILTER_SANITIZE_STRING) : 0;
        $anzeigeID = isset($_POST['payCrt']) ? filter_var($_POST['payCrt'], FILTER_SANITIZE_NUMBER_INT) : null; 
        $customVals         = isset($_POST['custom']) ? strip_tags($_POST['custom']) : "";

        $payment_status = isset($_POST['payment_status']) ? filter_var($_POST['payment_status'], FILTER_SANITIZE_STRING) : "";

        $custom = json_decode($customVals, TRUE);

        $paymentstatus = $payment_status == "Completed" ? 1 : 3; 


        $startdate = date('Y-m-d');
        $gultig_bis = date('Y-m-d', strtotime("+{$custom['planweeks']} week", strtotime($startdate)));
        $sichtbar_bis = date('Y-m-d', strtotime("+{$custom['planmonths']} month", strtotime($startdate)));
        $upd = $this->db->update("payments", array('txn_id'=>$txn_id, "paystatus"=>$paymentstatus, "timepay"=>$startdate, "paystaterr"=>$customVals, "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis), array('rechnnr'=>$item_number));

        $updRechn = $this->db->update("rechnungen", array('rechstat'=>$paymentstatus), array('crt'=>$item_number));

        if(!empty($custom['payCrt']) && ($custom['payCrt'] >= 1)){
           $prelAnzeigu = $this->db->update("paystats", array('ishidd'=>1), array("anzid"=>$custom['payCrt']));
        } 
          $paymentstatus = $payment_status == "Completed" ? 3 : 1;
           
          $insertPayStat = $this->db->insert("paystats", array("usid"=>$custom['userid'], "anzid"=>$custom['payCrt'] ? $custom['payCrt'] : 0, "ison"=>$paymentstatus, 'planid'=>$custom['planid'], 'payverlng'=>1, 'billnr'=>$item_number, 'datactivat'=>date("Y-m-d H:i:s"), "gultigbis"=>$gultig_bis, "sichtbarbis"=>$sichtbar_bis));
        
        

         // $updRechnStat = $this->db->update("paystats", array('ison'=>3, 'datactivat'=>date("Y-m-d H:i:s")), array('billnr'=>$item_number));

        if($frm = $this->db->get("rechnungen", 
        array("select"=>"rechnungen.rechnunr, rechnungen.planid, rechnungen.planname, rechnungen.rechval, firmen.frmname, frmpreffs.email", "join"=>array(
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid")),
        array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"rechnungen.usid"))),
        'where'=>array('rechnungen.crt'=>$item_number), 'return_type'=>'single'))){

            $emailDo = Main::emailCreate('email', array(     
              'customsubject'=>'Doc-Site.: '.e('Bezahlung bestätigt'),
              'username'=>$frm['frmname'], 
              'l1'=>e('Ihre Zahlung ist bestätigt!'),
              'l2'=>e('Rechnung').' '.$frm['rechnunr'].'<br/>'
                    .e('Leistung').' '.$frm['planname'].'<br/>'
                    .e('Preis').' '.$frm['rechval'].' '.Main::config('currency').'<br/>'
                    .e('Zahlungsart').' PayPal<br/>',
              'b1'=>e('Hier anmelden'),
              'b1link'=>Main::config('url').'/anmeldung',
              'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
              'l5'=>Main::config('title')." TEAM"
            ));

            @$this->sendmail($frm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
            usleep(200);
        }
      } 
  }



  public function doCancel(){
    if(!$this->user = Main::user()){
      Main::redirect("", array("danger", "Zahlungen sind derzeit nicht möglich!"));
      die();
    }
    if($_SESSION['ppl'] && !empty($_SESSION['ppl'])){
      $insertPlan = filter_var($_SESSION['ppl'], FILTER_SANITIZE_NUMBER_INT); 
      if($insertPlan && $insertPlan >= 1){
        $upd = $this->db->update("payments", array('paystatus'=>6, "paystaterr"=>"Firma - Canceled" ), array('rechnnr'=>$insertPlan));

        $updRechn = $this->db->update("rechnungen", array('rechstat'=>6), array('crt'=>$insertPlan));



        if($frm = $this->db->get("rechnungen", 
        array("select"=>"rechnungen.rechnunr, rechnungen.planid, rechnungen.planname, rechnungen.rechval, firmen.frmname, frmpreffs.email", "join"=>array(
        array("type"=>"join", "table"=>"firmen", "on"=>array("firmen.usid"=>"rechnungen.usid")),
        array("type"=>"join", "table"=>"frmpreffs", "on"=>array("frmpreffs.usid"=>"rechnungen.usid"))),
        'where'=>array('rechnungen.crt'=>$insertPlan), 'return_type'=>'single'))){

            $emailDo = Main::emailCreate('email', array(     
              'customsubject'=>'Doc-Site.: '.e('Zahlung storniert'),
              'username'=>$frm['frmname'], 
              'l1'=>e('Ihre PayPal-Zahlung wurde storniert!'),
              'l2'=>e('Rechnung').' '.$frm['rechnunr'].'<br/>'
                    .e('Leistung').' '.$frm['planname'].'<br/>'
                    .e('Preis').' '.$frm['rechval'].' '.Main::config('currency').'<br/>'
                    .e('Zahlungsart').' PayPal<br/>',
              'b1'=>e('Hier anmelden'),
              'b1link'=>Main::config('url').'/anmeldung',
              'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
              'l5'=>Main::config('title')." TEAM"
            ));

            @$this->sendmail($frm['email'], $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
            usleep(200);
        }

        Main::redirect("/rechnungen");
      }else{
        Main::redirect("");
        die();
      }
    }else{
      Main::redirect("");
      die();
    }
  }








}
?>