<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class WillkommenController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Willkommen', $this->config, $this->db);
    }


    public function index($params = ''){

        $this->model->checkIsLogged();

        $this->isPage = 'kandidat/willkommen';
        $params = func_get_args();
        $this->model->text['params'] = $params;


        $this->view($this->isPage,  $this->model->text);
    }


    
 


    
}
?>