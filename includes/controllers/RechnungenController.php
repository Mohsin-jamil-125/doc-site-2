<?php
namespace App\includes\controllers;
use App\includes\Controller;

class RechnungenController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Rechnungen', $this->config, $this->db);
    }


    public function index($params = ''){

        $this->model->checkIsLogged('firma');

        $this->isPage = 'firma/rechnungen';
        $params = func_get_args();
        $this->model->text['params'] = $params;


        $this->model->getRechnungen(FALSE);

  
        $this->view($this->isPage,  $this->model->text);
    }


    
 


    
}
?>