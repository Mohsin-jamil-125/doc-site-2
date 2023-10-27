<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Uber extends Model  {

  public function __construct($config, $db){

    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text = ["title" => e("Über doc-site"), "color" => "#dark"];

    $this->getUberuns();
  }  


  public function getUberuns(){
    if(!$ubers = $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>'uberuns'),'limit'=>1, 'return_type'=>'single'))){
      Main::redirect("");
    } 
    $this->text['subtitle'] = $ubers ?  htmlspecialchars_decode($ubers['var']).'</pre>' : '';

    Main::pagetitle($this->text['title']);
  }

}
