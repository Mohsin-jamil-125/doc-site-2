<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Datenschutz extends Model  {


  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text = ["title" => e('Datenschutz-Bestimmungen')];
    
    $this->getDatenSchutz();
  }


  public function getDatenSchutz(){
    if(!$dschutz = $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>'privacy'),'limit'=>1, 'return_type'=>'single'))){
      Main::redirect("");
    } 
    $this->text['subtitle'] = $dschutz ?  htmlspecialchars_decode($dschutz['var']).'</pre>' : '';

    Main::pagetitle($this->text['title']);
  }

  

}