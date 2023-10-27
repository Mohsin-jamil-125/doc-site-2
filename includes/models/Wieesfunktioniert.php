<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Wieesfunktioniert extends Model  {

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

   $this->text = ["title" => ''];

   $this->getWieesfuncst();

  }


  public function getWieesfuncst(){
    if(!$wih= $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>'wieesfunctioniert'),'limit'=>1, 'return_type'=>'single'))){
      Main::redirect("");
    } 
    $this->text['subtitle'] = $wih ?  htmlspecialchars_decode($wih['var']).'</pre>' : '';

    Main::pagetitle('wie-es-funktioniert');
  }


}