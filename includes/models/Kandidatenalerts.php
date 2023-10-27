<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Kandidatenalerts extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->users = Main::user();

    $this->text = ["title"=>e("Job Alerts"), "color"=>"dark", "company"=>$this->accessLevel];
  }

  public function delAlertJob($params = ''){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");  

      if(!$this->users && !$this->users[0] >= 1){
        return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
      }

      if($decry = Main::decrypt($params)){
        if(!$decry >= 1){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }

        if(!$delLogins = $this->db->delete("kandidatealerts", array('crt' => $decry, "usid"=>$this->users[0]))){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
        }
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>e("Erfolg!").";/kandidatenalerts"))); 
      }
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e('Fehler, versuche es erneut!'))));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
    }
  }



}
?>