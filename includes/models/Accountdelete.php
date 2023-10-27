<?php
namespace App\includes\models;
use App\includes\Model;

class Accountdelete extends Model  {

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title"=>e('Account löschen'), "company"=>$this->accessLevel, "color"=>"dark"];
  }



}
?>