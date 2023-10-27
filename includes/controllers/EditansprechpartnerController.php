<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class EditansprechpartnerController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Editansprechpartner', $this->config, $this->db);
    }

    public function index($params = ''){

      if(!$params[0] || empty($params[0])){
         Main::redirect("company");
         exit;
      }

      $this->model->checkIsLogged('firma');  

      $this->isPage = 'firma/editansprechpartner';
      $params = func_get_args();
      $this->model->text['params'] = $params;

      $this->model->getAnsprechpartners($params[0]);

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




}
?>