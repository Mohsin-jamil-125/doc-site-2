<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Adminbewerbt extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e('Beworben'), "pgname" => e('Beworben'), "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token']];

  }

  


}