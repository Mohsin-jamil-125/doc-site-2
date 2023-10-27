<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class MeineanzeigenController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Meineanzeigen', $this->config, $this->db);
    }

    public function index($params = ''){

      $this->model->checkIsLogged('firma'); /// firma/company

      $this->isPage = 'firma/meineanzeigen';
      $params = func_get_args();
      $this->model->text['params'] = $params;

      $this->model->getAnzeigen();

      $this->model->getStatsPayiment();


      $this->view($this->isPage, $this->model->text);
    }



    
 


    
}
?>