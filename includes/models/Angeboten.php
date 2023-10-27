<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Angeboten extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];
    

    $this->text = ["title" => e('Angebote'), "pgname" => e('Angebote'), "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token']];

    Main::pagetitle($this->text['title']);

    $this->getPlansInfos();
  }


  public function getPlansInfos(){
    if($payPlans = $this->db->get("payplans", array('return_type'=>'all'))){ 
      $this->text['payPlans'] = $payPlans;
    }else{
      $this->text['payPlans'] = '';
    } 
  }
 

}