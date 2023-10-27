<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Adminunternehmen extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $users, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;

    $this->users = Main::user();
    $this->accessLevel = $this->config['accessLevel']['admins'];

    $this->text = ["title" =>e("Unternehmen"), "pgname" => e("Unternehmen"), "ai_token"=>$this->users ? $this->users[2] : FALSE, "public_token"=>$this->config['public_token'],
    "ddll"=>['titel'=>e('Unternehmen löschen'), "body"=>e('Alle Daten zu dieser Unternehmen gehen unwiderruflich verloren.'), "button"=>e('Löschen'), "admWhere"=>'delUnternehn']];

  }

  


}