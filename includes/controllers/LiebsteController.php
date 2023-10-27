<?php
namespace App\includes\controllers;
use App\includes\Controller;

class LiebsteController extends Controller {
    
    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Liebste', $this->config, $this->db);
    }


    public function index($params = ''){
        
        $this->model->checkIsLogged('kandidat');

        $this->isPage = 'kandidat/liebste';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->model->getFavoAnzeigen();
  
        $this->view($this->isPage,  $this->model->text);
  
    }
 


    
}
?>