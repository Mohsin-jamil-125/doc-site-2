<?php
namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;


class Docron extends Model  {

  protected $config = [], $db;
  public $text = [];
  protected $socialsConfig;

  public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
  }


  public function doAllCrons(){
    $do = Main::socialsDoShares();
    foreach($do as $val){  
      if(is_string($val) && method_exists("App\includes\Model", $val)){
        $aa = $this->{$val}();
      }
    }
  return true;
  }


  

    

     



}
