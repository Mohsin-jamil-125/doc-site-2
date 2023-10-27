<?php
namespace App\includes\models;
use App\includes\Model;

class Addslist extends Model  {
  

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $config['accessLevel']['admins'];
    $this->text = ["title" => "Adds List", "pgname" => "Addslist"];
  }



}
