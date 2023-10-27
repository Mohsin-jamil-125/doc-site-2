<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Kandidatansehen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    if($us = Main::user()){
      $this->users = $us;
    }

   $this->text = ["title" => e("Kandidat ansehen"), "color" => "#dark", "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];
  }






}