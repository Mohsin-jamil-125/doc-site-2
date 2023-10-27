<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Payment extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];
    

    $this->text = ["title" => e('Zahlungseinstellungen'), "rabatkod"=>rand(111111, 999999), "pgname" => e('Zahlungseinstellungen'), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];

    $this->getPayPlans();
  }

  public function getPayPlans(){
     $pm = $this->db->get("payplans", array('select'=>'crt, planname', 'return_type'=>'all'));

     $this->text['paymentPakets'] = $pm ? $pm : '';

     $pmlst = $this->db->get("rabatt", array("select"=>"rabatt.crt, rabatt.rabatt, payplans.planname", "join"=>array(
      array("type"=>"join", "table"=>"payplans", "on"=>array("payplans.crt"=>"rabatt.planid"))), 'where'=>array('rabatt.ison'=>2), 'return_type'=>'all'));

     $this->text['rabatList'] = $pmlst ? $pmlst : '';
    
  }

}