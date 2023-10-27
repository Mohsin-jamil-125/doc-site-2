<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Zahlen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];  // firma

    //$this->users = Main::user();

   // $this->text = ["title"=>e("Pay"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'], "color"=>"dark"];

   $this->text = ["title"=>e("Pay"), "color"=>"dark"];
  }


/**
* Payment function data from Pay Provider  
**/
  public function paymentFunction($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");

        $hid = isset($params->hid)?$params->hid:FALSE;  // hid heidel ID
        $whatIsPaidID = isset($params->theId)?$params->theId:FALSE;  // id produs
        $fel = isset($params->theArt)?$params->theArt:FALSE; // card   
        $suma = isset($params->theSumm)?$params->theSumm:FALSE; // 12Euro
        $planName = isset($params->thePlan)?$params->thePlan:FALSE; // month 

        if(!$hid || !$suma || !$suma >= 1 || !$planName || !$fel || !$whatIsPaidID){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Bitte versuchen Sie es erneut!'))));
        }

        if(!$thisUser = $this->prufUser('oAqp8cBHIOLbG3Nq4OQe')){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }  //{"hid":"s-crd-no07llmsjjqk","theArt":"card","theId":"Einteigerpaket","thePlan":"Einteigerpaket","theSumm":"390"}: 


        $url = 'https://api.heidelpay.com/v1/payments/charges';
        if($fel && $fel == "card"){ 
          $dataPaym = array("amount" => $suma,
                        "currency"=> Main::config('currency'),
                        "card3ds"=> "true",
                        "paymentReference"=> $whatIsPaidID,                                
                        "returnUrl"=> Main::config('url')."/bezahlung/".$fel,
                        "resources.typeId"=> $hid
                      );
            }elseif($fel && $fel == "sofort"){
                $dataPaym = array("amount" => $suma,
                        "currency"=> Main::config('currency'),
                        "paymentReference"=> $whatIsPaidID,                                
                        "returnUrl"=>  Main::config('url')."/bezahlung/".$fel,
                        "resources.typeId"=> $hid
                      );  
            }elseif($fel == "giro"){
                $dataPaym = array("amount" => $suma,
                        "currency"=> Main::config('currency'),
                        "paymentReference"=> $whatIsPaidID,                                
                        "returnUrl"=>  Main::config('url')."/bezahlung/".$fel,
                        "resources.typeId"=> $hid
                      );  
            }


          if($dataPaym){
              $data_string = http_build_query($dataPaym);
              $headers[] = 'Content-Type: application/x-www-form-urlencoded';
              $ch = curl_init(); 
              curl_setopt( $ch,CURLOPT_URL,$url ); 
              curl_setopt( $ch,CURLOPT_POST, 1 ); 
              curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers ); 
              curl_setopt( $ch,CURLOPT_RETURNTRANSFER, 1 );
              curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );  
              curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
              curl_setopt($ch, CURLOPT_USERPWD, HEIDEL_PASS . ':' . ''); /// HEIDEL_PASS
              $heidelResponse = curl_exec($ch);
              $err = curl_error($ch);
              curl_close($ch);
              if ($err) {
                return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$err)));
              } 
          } 

          if($saveZahlung = $this->savePayment($thisUser, $params, $heidelResponse)){
            if($saveZahlung['error'] == 2 && !empty($saveZahlung['msg'])){
              return array("error"=>2, "msg"=>$saveZahlung['msg']);
            }
            return array("user_error"=>1, "user_reason"=>1, "msg"=>$heidelResponse);
          }
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }
  }


/**
* Save payment data to DB (Prepayments functions) 
**/
/*
$hid = isset($params->hid)?$params->hid:FALSE;  // hid heidel ID
        $whatIsPaidID = isset($params->theId)?$params->theId:FALSE;  // id produs
        $fel = isset($params->theArt)?$params->theArt:FALSE; // card   
        $suma = isset($params->theSumm)?$params->theSumm:FALSE; // 12Euro
        $planName = isset($params->thePlan)?$params->thePlan:FALSE; // month 

        $myArray['resources']['paymentId'], $myArray['processing']['shortId'], $myArray['processing']['uniqueId'],
*/

  protected function savePayment($thisUser, $data, $heidelResponse){  
    if($heidelResponse && $data && ($thisUser && $thisUser >= 1)){  
      $decodedText = html_entity_decode($heidelResponse);
      $myArray = json_decode($decodedText, true); 

      if($myArray['id'] && $myArray['resources']['paymentId']){
        //$bbiss = $this->addMoreTimePayment($data->theId, $data->theWhat, $data->thePeriod, $data->thePlan);
        $bis = date('Y-m-d');
        $anzeigeid = 1;
        $thePlan = 1;

        if($i = $this->db->insert("payments", array('usid'=>$thisUser, 'anzeigeid'=>$anzeigeid, 'theplan'=>$thePlan, 'planname'=>$data->thePlan, 'paymetod'=>$data->theArt, 'heid'=>$data->hid, 'tranzaction'=>$myArray['resources']['paymentId'], 'shortId'=>$myArray['processing']['shortId'], 'longid'=>$myArray['processing']['uniqueId'], 'paysume'=>$data->theSumm,   'paystaterr'=>'none', 'timepay'=>date('Y-m-d'), 'gultigbis'=>$bis))){ 
          return array("error"=>1, "msg"=>""); 
        } 
        return array("error"=>2, "msg"=>e('Bitte versuchen Sie es erneut!'));
      }else{   
        return array("error"=>2, "msg"=>e('Die Zahlung ist vorerst nicht möglich!'));   
      }
    }
  return array("error"=>2, "msg"=>e('Bitte versuchen Sie es erneut!'));
  }




  








}
?>