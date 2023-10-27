<?php

namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Agb extends Model  {

  public function __construct($config, $db){

    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $config['accessLevel']['open'];
    $this->text = ["title" => e('Allgemeine Geschäftsbedingungen')];

    Main::pagetitle($this->text['title']);

    $this->getAgbs();
  }

  public function getAgbs(){
    if(!$agb = $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>'terms'),'limit'=>1, 'return_type'=>'single'))){
      Main::redirect("");
    } 
    $this->text['subtitle'] = $agb ?  htmlspecialchars_decode($agb['var']).'</pre>' : '';
  }

  
  

}