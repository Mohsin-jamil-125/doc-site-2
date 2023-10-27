<?php
namespace App\includes\models;
use App\includes\Model;

class Newmessage extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['kandidat'];

    $this->text = ["title" => e("E-Mail schreiben"), "pgname" =>e("E-Mail schreiben"), "bottomPic"=>"/static/images/banners/nurse.png", "bottomBackground"=>"transparent"];


  }




}
