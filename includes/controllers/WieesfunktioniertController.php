<?php
namespace App\includes\controllers;
use App\includes\Controller;

class WieesfunktioniertController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config = $config;
     $this->db = $db;

     $this->model = $this->model('Wieesfunktioniert', $this->config, $this->db);
  }


  public function index($params = ''){

    $this->model->checkIsLogged();

    $this->isPage = 'home/wieesfunktioniert';
    $params = func_get_args();
    $this->model->text['params'] = $params;


    $this->view($this->isPage,  $this->model->text);
  }




 


  
}
?>