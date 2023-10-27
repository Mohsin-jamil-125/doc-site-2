<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Neuenachricht extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title" => e("E-Mail schreiben"), "pgname" =>e("E-Mail schreiben"), "bottomPic"=>"/static/images/banners/nurse.png", "bottomBackground"=>"transparent"];


  }




  public function saveEntwurf($params = false){
    if($params){
      header("Content-Type: application/json; charset=UTF-8");

        if(!$this->users = Main::user()){
          return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>e("Fehler, versuche es erneut!"))));
        }

        if($theEntwurf = $this->db->get("mailentwufe", array("select"=>"crt", "where"=>array("usid"=>$this->users[0]), "limit"=>1, "return_type"=>"single"))){
          $updateEntwf = $this->db->update("mailentwufe", 
          array("tosid"=>$params->dowo, "mailsubject"=>$params->dosub, "mailmess"=>$params->domsg), 
          array("usid"=>$this->users[0]));
        }else{
          $updateEntwf = $this->db->insert("mailentwufe", array("usid"=>$this->users[0], "tosid"=>$params->dowo, "mailsubject"=>$params->dosub, "mailmess"=>$params->domsg));
        }

        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success", "action_do"=>e("Erfolg!"))));
      }else{
        return array("user_error"=>1, "user_reason"=>2, "msg"=>e("Fehler, versuche es erneut!"));
      }
  }





}
