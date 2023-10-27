<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class EinzelheitenansehenController extends Controller {
    protected $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config=$config;
  	   $this->db=$db;

       $this->model = $this->model('Einzelheitenansehen', $this->config, $this->db);
    }

    public function index($params = ''){

        if(!$params[0] || empty($params[0])){
            Main::redirect("company");
            exit;
        }

        $this->model->checkIsLogged('firma');

        $this->isPage = 'firma/einzelheitenansehen';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->model->getAnzeigen($params[0]);

  
        $this->view($this->isPage,  $this->model->text);
    }
 


    
}
?>