<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Bezahlung extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];  // firma

    //$this->users = Main::user();

   // $this->text = ["title"=>e("Pay"), "color"=>"dark", "company"=>$this->accessLevel, "ai_token"=>$this->users[2], "public_token"=>$this->config['public_token'], "color"=>"dark"];

   $this->text = ["title"=>e("Pay"), "color"=>"dark"];
  }


 


}
?>