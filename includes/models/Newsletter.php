<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Newsletter extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];
   // $this->capcha = $this->config['captcha'];

    //$this->text =  ["title" => e("Kontakt"), "color" => "#dark", "public_token"=>$this->config['public_token']];
  }



  public function saveNewsletter($params = false){
    if($params){
        header("Content-Type: application/json; charset=UTF-8");
        if(!$frm = Main::forms($params)){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Zugriff verweigert"))));
        }
  
        if(array_key_exists('_er', $frm) && !empty($frm['_er'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>$frm['message'])));
        }

        if(!$newsletter = Main::email($frm['newslett'])){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Ungültige E-Mail-Adresse!"))));       
        }


        if($stmt = $this->db->get('newsletter', array('select'=>'crt, stats', 'where' => array('emails'=>$newsletter), 'limit'=>1, 'return_type'=>'single'))){ 
         if($stmt['stats'] == 1) {
          $stmt = $this->db->update('newsletter', array('stats'=>2), array('crt'=>$stmt['crt']));
         }
        // return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!')))); 
        }else{
          if(!$stmt = $this->db->insert('newsletter', array('emails'=>$newsletter))){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")))); 
          }
         // return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!')))); 
        }   



        if(!$stmt = $this->db->get('mrk__recipient', array('select'=>'id', 'where' => array('email'=>$newsletter), 'limit'=>1, 'return_type'=>'single'))){ 
          if(!$stmt = $this->db->insert('mrk__recipient', array('person'=>'Doc-Site', 'email'=>$newsletter, 'comment'=>'Öffentlich registriert'), TRUE)){
            return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!")))); 
          } 
        } 


        $emailDo = Main::emailCreate('email', array(     
          'customsubject'=>'Doc-Site.: Newsletter',
          'username'=>'Hallo, ', 
          'l1'=>' Ihre E-Mail-Adresse '. $newsletter .' wurde erfolgreich registriert!',
          'l2'=>' Sie können sich jederzeit vom Doc-Site Newsletter abmelden! ',
          //'b1'=>e('Hier anmelden'),
         // 'b1link'=>Main::config('url').'/anmeldung',
          'l4'=>e('Vielen Dank!'),
          'l5'=>Main::config('title')." TEAM"
        ));

        @$this->sendmail($newsletter, $emailDo['subject'], $emailDo['message'],  $emailDo['template']);
        usleep(200);
        
        
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e('Erfolg!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"2".e("Fehler, versuche es erneut!"))));
    }
  }










}


