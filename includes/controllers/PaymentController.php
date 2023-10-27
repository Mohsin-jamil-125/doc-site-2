<?php
namespace App\includes\controllers;
use App\includes\Controller;

class PaymentController extends Controller {

  protected $model;
  protected $accessLevel;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config = $config;
     $this->db = $db;
     $this->accessLevel = $this->config['accessLevel']['admins'];

     $this->model = $this->model('Payment', $config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('admin');

    $this->isPage = 'admin/payment';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->view($this->isPage,  $this->model->text);

  }



  

}