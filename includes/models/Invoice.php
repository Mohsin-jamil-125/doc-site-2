<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Invoice extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    if($us = Main::user()){
      $this->users = $us;
    }

    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e("Rechnung"), "pgname" => e("Rechnung"), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];

  }

  


}