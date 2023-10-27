<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Webtranslation extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e('Übersetzungen'), "pgname" =>e('Übersetzungen')];

  }



  public function doWebUbersetzung($key, $word){
		if($key && $word){
      header("Content-Type: application/json; charset=UTF-8");

       if(Main::bearbeitenTranslations($key, $word)){
        return array("user_error"=>1, "user_reason"=>1, "msg"=>Main::actCreate(array("action"=>"success;redirect", "action_do"=>"Erfolg;/webtranslation")));
       }

      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"Fehler")));
    }else{
      return array("user_error"=>1, "user_reason"=>2, "msg"=>Main::actCreate(array("action"=>"error", "action_do"=>"Fehler")));
    }
  }


}

