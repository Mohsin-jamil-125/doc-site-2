<?php

namespace App\includes\models;
use App\includes\Model;
use App\includes\Main;

class Faq extends Model  {

  public function __construct($config, $db){

    $this->config = $config;
    $this->db = $db;

    $this->accessLevel = $config['accessLevel']['open'];
    $this->text = ["title" => '', 'subtitle'=> ' '];

    Main::pagetitle('FAQ.: Medizinisches Stellenportal');

    $this->getFAQ();
  }
  
  
  public function getFAQ(){   //array(2) { ["firma"]=> int(1) ["kandidate"]=> int(2) }  "faq": {"FAQ & Hilfe - für Unternehmen":1, "FAQ & Hilfe - für Kandidaten":2}
 // $web =  Main::websettings('faq')
    if($web =  [e("FAQ & Hilfe - für Unternehmen")=>1, e("FAQ & Hilfe - für Kandidaten")=>2]) {
        foreach($web as $k => $val){
          if(!$faqget = $this->db->get("faqa", array('select'=>'crt, categ, titel, content', 'where'=>array('categ'=>$val), 'return_type'=>'all'))){
            $faq[$val] = [];
            $titeld[$val] = [];
          }
          $faq[$val] = $faqget; 
          $titeld[$val] = $k; 
        }
    }
    $this->text['listFaq'] = $faq;
    $this->text['listFaqTitles'] = $titeld;
  }

}
