<?php
namespace App\includes\controllers;
use App\includes\Controller;

class WebController extends Controller {
    protected $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config=$config;
  	   $this->db=$db;

       $this->model = $this->model('Underconstruction', $this->config, $this->db);
    }

    public function index($params = ''){

        $this->isPage = 'web/index';
        $params = func_get_args();

        $this->view($this->isPage,  $params);
    }
 


    
}
?>