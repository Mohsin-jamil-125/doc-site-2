<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class InvoiceslistController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Invoiceslist', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('admin');

    $this->isPage = 'admin/invoiceslist';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getRechnungen(FALSE);

    $this->view($this->isPage,  $this->model->text);
  }

  public function doPymnt($params = ''){
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->doPymntBestatg($data)){
      http_response_code(200);
      echo json_encode($p);
    }else{
       http_response_code(200);
       echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }

  public function doStorno($params = ''){
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->doStornoBestatg($data)){
      http_response_code(200);
      echo json_encode($p);
    }else{
       http_response_code(200);
       echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }

  



  
}
?>