<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Kontakt extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];  
    $this->capcha = $this->config['captcha'];

    $this->text =  ["title" => e("Kontakt"), "bottomBackground"=>"#fff", "bottomStyle"=>"style=\"height: 60px;height: 60px;float:right;\"", "bottomPic"=>"/static/images/banners/doc-site-kontakt.svg", "color" => "#dark", "public_token"=>$this->config['public_token']];

    Main::pagetitle($this->text['title']);
  }


  public function webKontakt($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Zugriff verweigert'))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }

        if(!$user_browser = $_SERVER['HTTP_USER_AGENT']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!1'))));
        }

        if(Main::csrfcheck($frm['token'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error;redirect", "action_do"=>e('Versuche es erneut!').";kontakt")));
        } 


        if($this->config['public_token'] != $frm['publictoken']){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!2'))));
        }

       if($this->capcha == 1 && array_key_exists('g-recaptcha-response', $frm)){
        if(!$cap = Main::check_captcha($frm['g-recaptcha-response'])){
         return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Bitte richtig antworten!'))));
        }
       }

        if($frm['kntthema'] == '--' ){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Wählen Sie Ihr Thema aus'))));
        }

        if(!$kntktmail = Main::email($frm['kntktmail'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Ungültige E-Mail-Adresse!'))));
        }

        $emailDo = Main::emailCreate('email', array(     
          'customsubject'=>e('Doc-Site Kontaktseite').'.: '.e('Sie haben eine neue E-Mail'),
          'username'=>e('Doc-Site Kontaktseite'), 
          'l1'=>$frm['kntktname'] .' hat Ihnen eine E-Mail von der Kontaktseite gesendet',
          'l2'=>e('Email-Adresse').'.: '.$frm['kntktmail'].'<br/>'.e('Thema').'.: '.$frm['kntthema'].'<br/>'.e('Nachricht').'.: '.$frm['kntknachricht'],
          //'b1'=>e('Thema').'.: '.$emailDo['subject'],
          //'b1link'=>Main::config('url').'/anmeldung',
          //'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
          'l5'=>e('Doc-Site Kontaktseite')
        ));


        if($emailDo){
          if( $this->sendmail(Main::config('company_email'), $emailDo['subject'], $emailDo['message'],  $emailDo['template']) ){$sended = 2;}else{ $sended = 3; }

          $insertWebKontakt = $this->db->insert("webkontakt", array("stat"=>$sended, "kname"=>$frm['kntktname'], "kmail"=>$frm['kntktmail'], "ksubject"=>$frm['kntthema'], "ktext"=>$frm['kntknachricht']));



          $emailDoUser = Main::emailCreate('email', array(     
            'customsubject'=>e('Doc-Site Kontaktseite').'.: ',   	
            'username'=>$frm['kntktname'], 
            'l1'=>e('Wir haben Ihre E-Mail erhalten.'),
            'l2'=>e('Vielen Dank für Ihr Interesse an Doc-Site').'<br/>'.e('Ein Doc-Site-Betreiber wird Ihre Fragen so schnell wie möglich beantworten'),
            'b1'=>'Doc-site',
            'b1link'=>Main::config('url'),
            'l4'=>e('Zögern Sie nicht, uns bei Problemen zu kontaktieren').".:".Main::config('email'),
            'l5'=>e('Doc-Site Kontaktseite')
          ));
          @$this->sendmail($frm['kntktmail'], $emailDoUser['subject'], $emailDoUser['message'],  $emailDoUser['template']);


          return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e('Erfolg').";/kontakt"))); 
        }

        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Momentan nicht möglich!'))));

    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut3!'))));
    }
  }





}


