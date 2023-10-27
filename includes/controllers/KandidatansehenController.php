<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class KandidatansehenController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config=$config;
     $this->db=$db;

     $this->model = $this->model('Kandidatansehen', $this->config, $this->db);
  }

  public function index($params = ''){

    if(!$params[0] || empty($params[0])){
      Main::redirect("");
      exit;
    }

    $this->model->checkIsLogged('firma');

    $this->isPage = 'firma/kandidatansehen';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getPublicKandidaten($this->model->text, $params[0]);


    $this->view($this->isPage,  $this->model->text);
  }


  

}