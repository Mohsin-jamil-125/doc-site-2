<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class DocronController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Docron', $config, $db);
  }

  public function index($params = ''){
    if(!isset($params) || empty($params)){  
      Main::redirect(""); 
      exit;
    }

    if($params != DOC_APIUS_SECRET){
      Main::redirect("");
      exit;
    }

    
    $this->model->doAllCrons();
  }
 

   




  
}