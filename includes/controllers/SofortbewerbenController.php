<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class SofortbewerbenController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config = $config;
     $this->db = $db;

     $this->model = $this->model('Sofortbewerben',  $this->config, $this->db);
  }

  public function index($params = ''){

    if(!$params || empty($params[0])){
        Main::redirect("");
        exit;
     }

    $this->model->checkIsLogged();

    $this->isPage = 'home/sofortbewerben';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getPublicAnzeigen($params[0]);

    $this->view($this->isPage,  $this->model->text);
  }



  
}
?>