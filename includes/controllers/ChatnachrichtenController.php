<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class ChatnachrichtenController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Chatnachrichten', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('firma');

    $this->isPage = 'firma/chatnachrichten';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    if( $params && ($params[0] || !empty($params[0])) ){  
     //Main::myDebug($params[0]);
      $this->model->text["datain"] = ["chatsID"=>$params[0]];
    }

    

    $this->model->getForChats();
    
    $this->view($this->isPage,  $this->model->text);
  }



  
}
?>