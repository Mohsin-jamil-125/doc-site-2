<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Tools;
use App\includes\Main;


class TestController extends Controller {
    protected $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Test', $this->config, $this->db);
    }

 
 
        
        
    }

?>