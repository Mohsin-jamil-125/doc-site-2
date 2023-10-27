<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Anzeige extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->getPlansInfos();
  }



  public function getPlansInfos(){
    if(!$payPlans = $this->db->get("payplans", array('return_type'=>'all'))){ 
      $this->text = ["title" => e("Anzeige")];
    }else{
      if(!empty($this->users) && ($this->users[3] == "firma" && $this->users[4] == 2)){
        if(Main::config('angebott') == 1){  $angebots = $this->getAngebots() ?? FALSE; }else{ $angebots = FALSE; }

        $this->text = ["title" => e("Anzeige"), "angebots"=>$angebots, "payplans"=>$payPlans, "ai_token"=>$this->users[2] ?? FALSE, "public_token"=>$this->config['public_token'] ?? FALSE];
      }else{
        if((Main::config('publicangebott') == 1) && (Main::config('angebott') == 1)){  $angebots = $this->getAngebots() ?? FALSE; }else{ $angebots = FALSE; }

        $this->text = ["title" => e("Anzeige"), "angebots"=>$angebots, "payplans"=>$payPlans, "ai_token"=>FALSE, "public_token"=>FALSE];
      }
    } 

    if($rabats = $this->db->get("rabatt", array('select'=>'crt, planid', 'where'=>array('ison'=>2), 'return_type'=>'all'))){ 
      $this->text['datain'] = $rabats;
    }

    Main::pagetitle($this->text['title']);
  }

 

  

}