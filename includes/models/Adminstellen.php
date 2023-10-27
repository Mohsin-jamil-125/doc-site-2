<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Adminstellen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();

    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e("Stellenangebote"), "pgname" => e("Stellenangebote"), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token'],
  "ddll"=>['titel'=>e('Stellenangebote löschen'), "body"=>e('Alle Daten zu dieser Anzeige gehen unwiderruflich verloren.'), "button"=>e('Löschen'), "admWhere"=>'delStellen']];

  }



}