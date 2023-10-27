<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Impressum extends Model  {

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['open'];

    $this->text = ["title" => '', "color" => "#dark"];

    $this->getImpressum();
  }

 
  public function getImpressum(){ //'title',     "subtitle"=>htmlspecialchars_decode($this->config['impressum'])
    $content = $this->getImpressContent();
    $content = htmlspecialchars_decode($content);
    $impresss = array('company', 'company_email', 'phone', 'companyreg', 'companyvat', 'companyaddress', 'companycity', 'companycountry');

    foreach($impresss as $val){
      if($tt = Main::config($val)){
        $content=str_replace("[{$val}]", $tt, $content);
      }
    }
    $this->text['subtitle'] = $content;  
  }


  public function getImpressContent(){
    if(!$imprs = $this->db->get("webshave", array('select'=>'var', 'where'=>array('config'=>'impressum'),'limit'=>1, 'return_type'=>'single'))){
      Main::redirect("");
    } 
    return $imprs['var'];
  }


}