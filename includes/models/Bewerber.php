<?php
namespace App\includes\models; 
use App\includes\Model;
use App\includes\Main;


class Bewerber extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];  

    $this->users = Main::user();

    $this->text = ["title"=>e("Bewerber"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'], "color"=>"dark"];
  }




  








}
?>