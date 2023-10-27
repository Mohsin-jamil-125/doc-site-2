<?php
namespace App\includes\models;
use App\includes\Model;

class Rechnungen extends Model  {

  protected $config = [], $db, $socialslogin;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title"=>e("Rechnungen"), "color"=>"dark", "company"=>$this->accessLevel,];
  }



}
?>