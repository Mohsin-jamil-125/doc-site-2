<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class ChatController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Chat', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('kandidat');

    $this->isPage = 'kandidat/chat';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    if( $params && ($params[0] || !empty($params[0])) ){  
       Main::myDebug($params[0]);
       $this->model->text["datain"] = ["chatsID"=>$params[0]];
     }

    $this->model->getForKandidatesChats();
    
    $this->view($this->isPage,  $this->model->text);
  }



  public function getChats($params = ''){
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->getChatsTalks($data->doit)){
          http_response_code(200);
          echo json_encode($p);
    }else{
          http_response_code(200);
          echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
    } 
  }



  
}
?>