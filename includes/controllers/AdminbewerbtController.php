<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class AdminbewerbtController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Adminbewerbt', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('admin');

    $this->isPage = 'admin/adminbewerbt';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getAdmBewerbt();

    $this->view($this->isPage,  $this->model->text);
  }



  
}
?>