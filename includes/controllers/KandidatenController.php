<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class KandidatenController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config=$config;
     $this->db=$db;

     $this->model = $this->model('Kandidaten', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('firma');

    $this->isPage = 'firma/kandidaten';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    //$this->model->getPublicKandidaten($this->model->text);


    $this->view($this->isPage,  $this->model->text);
  }


  public function addlibstes($params = ''){
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->kandidatenLiebste($data)){
      http_response_code(200);
      echo json_encode($p);
    }else{
       http_response_code(200);
       echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }

  public function addalerts($params = ''){
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->kandidatenAlerts($data->doit)){
      http_response_code(200);
      echo json_encode($p);
    }else{
       http_response_code(200);
       echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }
  
  public function getCandidates($params = ''){
    if(Main::bot()) return $this->content("_404");
          $data = json_decode(file_get_contents("php://input"));
          if($p = $this->model->getAjaxKandidates($data->doit)){
                http_response_code(200);
                echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>$p));
          }else{
                http_response_code(200);
                echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Keine Ergebnisse!')));
          } 
  }


  public function sendNachrichten($params = ''){
    if(Main::bot()) return $this->content("_404");
          $data = json_decode(file_get_contents("php://input"));
          if($p = $this->model->sendNachEmails($data)){
                http_response_code(200);
                echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>$p));
          }else{
                http_response_code(200);
                echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Keine Ergebnisse!')));
          } 
  }


  

}