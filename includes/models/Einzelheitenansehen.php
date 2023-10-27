<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Einzelheitenansehen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['firma'];


    $this->text = ["title"=>e("Einzelheitenansehen"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token']];

    Main::pagetitle('Medizinisches Stellenportal - '.$this->text['title']);
  }



}