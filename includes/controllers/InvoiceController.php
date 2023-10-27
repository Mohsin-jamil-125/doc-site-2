<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class InvoiceController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Invoice', $this->config, $this->db);
  }

  public function index($params = ''){
    if(!$params[0] || empty($params[0])){
      Main::redirect("dashboard");
      exit;
    }

    $this->model->checkIsLogged('admin');

    $this->isPage = 'admin/invoice';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getRechnungen($params[0]);

    $this->view($this->isPage,  $this->model->text);
  }



  
}
?>