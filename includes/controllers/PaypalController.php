<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Tools;
use App\includes\Main;

class PaypalController extends Controller {

    protected $user, $model;
    protected $db, $config = [];
    protected $allowedPayPal = ['verify', 'cancel', 'ipn'];  // 'doPayPal',

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Paypal', $this->config, $this->db);
    }


    public function index($params = ''){
      if(!$params || empty($params[0])){
        session_destroy();
        Main::redirect("https://google.com", [], "", TRUE);
        exit;
      }

      $this->model->checkIsLogged('firma');

      $this->isPage = 'firma/paypal';
      $params = func_get_args();
      $this->model->text['params'] = $params;

      if(!is_array($params) || !in_array($params[0], $this->allowedPayPal)){
        Main::redirect("", array("danger", "Zahlungen sind derzeit nicht möglich!"));
        die();
      }

      $this->view($this->isPage,  $this->model->text); 
    }



    public function verify(){
      if(Main::bot()) return $this->content("_404");
       $this->model->doVerify();
    }


     


    public function ipn(){
      if(Main::bot()) return $this->content("_404");
      $data = file_get_contents('php://input');

      $this->model->doIpn($data);
    }


    public function cancel(){
      if(Main::bot()) return $this->content("_404");

      $this->model->doCancel();
    }

    


   


    
}
?>