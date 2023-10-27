<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Chatnachrichten extends Model {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['firma'];

    $this->text = ["title" => e('Chat'), "pgname" => e('Chat'), "bottomPic"=>"/static/images/banners/nurse.png", "bottomBackground"=>"transparent",
    "mdl"=>["shows"=>"show", "displays"=>"style=\"display: block;\"", "titel"=>e('Sie haben derzeit keine aktiv Anzeigen! '), "untertitle"=>e('Aktivieren Sie eine Anzeige, um auf die Kandidatenliste zuzugreifen'),
    "onclick"=>" onClick=\"window.location.replace('meine-anzeigen');\""]];


  }




}