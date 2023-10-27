<?php
namespace App\includes\controllers;
use App\includes\Controller;

class FacebookController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Facebook', $config, $db);
  }

  public function index($params = ''){
      $params = func_get_args();
      $this->model->text['params'] = $params;

     // $this->model->checkFacebook();
  }
 

   




  
}