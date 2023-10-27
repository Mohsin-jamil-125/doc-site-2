<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class NeueransprechpartnerController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Neueransprechpartner', $this->config, $this->db);
    }

    public function index($params = ''){

      $this->model->checkIsLogged('firma'); /// firma/company

      $this->isPage = 'firma/neueransprechpartner';
      $params = func_get_args();
      $this->model->text['params'] = $params;


      $this->view($this->isPage, $this->model->text);
    }


    public function addNeuerAnsprechfrm($params = ''){ 
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->addNeuerAnsprecher($data->doit)){
           http_response_code(200);
           echo json_encode($p);
       }else{
           http_response_code(200);
           echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
       } 
    }

    public function editAnsprechfrm($params = ''){ 
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->editsAnsprech($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
         } 
      }


    public function uploadAnsprechAvatar($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->ansprechUploadAvatar($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
        } 
      }


/*

    



   

    public function delAnsprechAvatar($params = ''){
      if(Main::bot()) return $this->content("_404");
      $data = json_decode(file_get_contents("php://input"));
      if($p = $this->model->deletefrmavatar($data->doit)){
          http_response_code(200);
          echo json_encode($p);
      }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
      } 
    } */

 

   


}
?>