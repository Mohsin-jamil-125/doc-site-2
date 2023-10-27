<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;


class PrufController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Pruf', $this->config, $this->db);
    }

    public function index($params = ''){

      if(!$params && (!$params[0] || empty($params[0]))){
        Main::redirect("");
        exit;
      }
        
        $this->model->checkIsLogged();
        
        $this->isPage = 'home/pruf';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->view($this->isPage,  $this->model->text);


        $this->doPruff($params);
    }



    public function doPruff($params = ''){ 
      if(Main::bot()) return $this->content("_404");
      if($p = $this->model->getPruf($params)){
        Main::cookie("pruff", "PruffEmail", FALSE);
        Main::redirect("anmeldung");
       }else{
        Main::cookie("pruff", "PruffEmailErr", FALSE);
        Main::redirect("");
       } 
    }









    
}
?>