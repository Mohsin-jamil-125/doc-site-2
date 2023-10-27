<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class MerklisteController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Merkliste', $this->config, $this->db);
    }

    public function index($params = ''){

      $this->model->checkIsLogged('firma'); 

      $this->isPage = 'firma/merkliste';
      $params = func_get_args();
      $this->model->text['params'] = $params;


      $this->model->getFirmLiebsteKandidaten();


      $this->view($this->isPage, $this->model->text);
    }



    
 


    
}
?>