<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Websettings extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->users = Main::user();

    $this->text = ["title" => e("Einstellungen"), "pgname" => e("Einstellungen"), "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'],  "datapage"=>$this->config];

    $this->getWebHave();
  }


  public function getWebHave(){
    $wesb = array('terms','privacy','wieesfunctioniert','uberuns','impressum');
    $w = [];
    foreach($wesb as $key){
      if(!$agb = $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>$key),'limit'=>1, 'return_type'=>'single'))){
        $w[$key]="";
      }else{
        $w[$key]= $agb['var'];
      }
    }

    $this->text['datawebs'] = $w;

    // if($getGoogle = $this->getGoogles()){
    //   $this->text['datawebs']['google_analitics'] = $getGoogle;
    // }
  }


  // public function getGoogles(){
  //   if($ga = $this->db->get("thegoogle", array('select'=>'valls', 'where'=>array('config'=>'analitics'),'limit'=>1, 'return_type'=>'single'))){
  //     return $ga['valls'];
  //   } 
  //   return false;
  // }


 

}