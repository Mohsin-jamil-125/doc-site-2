<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Sofortbewerben extends Model  {

  protected $config = [], $db;
  protected $accessLevel;
  public $user, $text = [];


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text = array("title"=>e("Sofortbewerben"), "bottomPic"=>"/static/images/banners/nurse.png", "public_token"=>$this->config['public_token']);

    Main::pagetitle($this->text['title']);
  }


}

