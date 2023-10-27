<?php
namespace App\includes\models;
use App\includes\Model;

class Liebste extends Model  {


   public function __construct($config = NULL, $db = NULL){
    $this->config = $config;
    $this->db = $db;
    $this->accessLevel = $this->config['accessLevel']['kandidat'];

    $this->text = array("title"=>"Meine Merkliste", "u"=>array("usernames"=>"aLAINd",  "userfstname"=>"oFF",  "userlstname"=>"Olteanu"), "color"=>"dark");
  }




}
?>