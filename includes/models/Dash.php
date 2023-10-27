<?php
namespace App\includes\models;
use App\includes\Model;

class Dash extends Model  {

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['kandidat'];

    $this->text = ["title"=>e("Dasboard"), "color"=>"dark"];
  }






}
?>