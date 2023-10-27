<?php
namespace App\includes\models;
use App\includes\Model;

class Indexmap extends Model  {


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

   // $this->text = [];
  }



}