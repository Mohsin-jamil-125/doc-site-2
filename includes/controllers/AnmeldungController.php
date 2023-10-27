<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class AnmeldungController extends Controller {

  public $post, $unique_key;
  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
     $this->config = $config;
     $this->db = $db;

     $this->model = $this->model('Anmeldung', $config, $db);
  }

  public function index($params = ''){

      $this->model->checkLogin();

     $this->isPage = 'home/anmeldung';
      $params = func_get_args();
      $this->model->text['params'] = $params;

      file_put_contents('d:/Log/log_'.date("j.n.Y").'.log', $params, FILE_APPEND);

      $this->view($this->isPage,  $this->model->text); 
  }


  public function socials(){
    if($p = $this->model->socialsLogin()){
      http_response_code(200);
      echo json_encode($p);
    }else{
      http_response_code(200);
      echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  } 


  public function dologin($params = ''){
   if(Main::bot()) return $this->content("_404");
		$data = json_decode(file_get_contents("php://input"));
		if($p = $this->model->doAnmeldung($data->doit)){
      http_response_code(200);
      echo json_encode($p);
    }else{
      http_response_code(200);
      echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  } 


   public function dobewerb($params = ''){
    if(Main::bot()) return $this->content("_404");
    if($p = $this->model->pubDoBewerbt($_FILES, $_POST)){
        http_response_code(200);
        echo json_encode($p);
    }else{
        http_response_code(200);
        echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }


  
}