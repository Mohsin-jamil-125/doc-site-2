<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Home extends Model  {
  

  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];
    $this->text = [];

    Main::pagetitle('Medizinisches Stellenportal');
  }



}