<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Unternehmen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

   $this->text = ["title" => e("Unternehmen"), "passendecolor" => "#dark"];

   Main::pagetitle($this->text['title']);
  }






}