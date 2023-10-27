<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Unternehmenprofil extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text = ["title"=>e("Unternehmenprofil"), "color"=>"dark",  "public_token"=>$this->config['public_token'], "jobsperemail"=>["titel"=>e("Jobs per Email erhalten"), "whhr"=>"stellenangebote", "untertitel"=>e("Aktivieren Sie jetzt Ihren Job-Alert für:")]];

    Main::pagetitle($this->text['title']);
  }



}