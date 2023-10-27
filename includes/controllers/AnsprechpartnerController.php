<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class AnsprechpartnerController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Ansprechpartner', $this->config, $this->db);
    }

    public function index($params = ''){

      $this->model->checkIsLogged('firma'); /// firma/company

      $this->isPage = 'firma/ansprechpartner';
      $params = func_get_args();
      $this->model->text['params'] = $params;

      $this->model->getAnsprechpartners();


      $this->view($this->isPage, $this->model->text);
    }


    public function suspAnsprechModal($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->suspendAnsprechModal($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      } 
    }

    public function dellAnsprechModal($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->deleteAnsprechModal($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      } 
    }

    public function enblAnsprechModal($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->enableAnsprechModal($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      } 
    }


    public function delansprbild($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));

      if($p = $this->model->deleteAnsprechBild($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      }  
    }



    public function douploadedbilder($params = ''){
      if(Main::bot()) return $this->content("_404");
      if($p = $this->model->ansprechEditBild($_FILES, $_POST)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      } 
  }




}
?>