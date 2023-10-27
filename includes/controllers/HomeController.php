<?php
namespace App\includes\controllers;
use App\includes\Controller;


class HomeController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Home', $this->config, $this->db);
    }

    public function index($params = ''){
        
        $this->model->checkIsLogged();
        
        $this->isPage = 'home/index';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->model->getTicker();

        $this->view($this->isPage,  $this->model->text);
    }


    
}
?>