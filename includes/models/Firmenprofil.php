<?php
namespace App\includes\models;
use App\includes\Model;

class Firmenprofil extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" => e("Firmen Profil"), "pgname" => e("Firmenprofil")];


  }




}
