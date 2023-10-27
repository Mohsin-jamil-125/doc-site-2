<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\middlewares\Middleware;

class Covid extends Model  {
  

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $config['accessLevel']['open'];
    $this->text = ["title" => e("Covid Infos"), "color" => "#dark"];
  }



}