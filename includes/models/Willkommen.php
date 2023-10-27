<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Willkommen extends Model  {


  public function __construct($config, $db){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['register'];

    $this->text = ["title"=>e("Willkommen"), "color"=>"dark", "bottomPic"=>"/static/images/banners/doctors.png"];

    Main::pagetitle($this->text['title']);
    
  }




}
?>