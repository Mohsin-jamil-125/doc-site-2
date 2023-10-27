<?php
namespace App\includes\controllers;
use App\includes\Controller;

class LogoutController extends Controller {

  protected $model;

  public function __construct($db, $config){

    $this->model = $this->model('Logout', $config, $db);
    
  }

  public function index($params = ''){

    $this->model->ausloggen();

  }

}
?>