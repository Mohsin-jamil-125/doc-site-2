<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class EditfirmenprofilController extends Controller {

  protected $model;
  protected $db, $config = [];

  public function __construct($db, $config){
    $this->config = $config;
    $this->db = $db;

    $this->model = $this->model('Editfirmenprofil', $this->config, $this->db);
  }

  public function index($params = ''){

    $this->model->checkIsLogged('admin');

    $this->isPage = 'admin/editfirmenprofil';
    $params = func_get_args();
    $this->model->text['params'] = $params;

    $this->model->getAdminsList();
 

    $this->view($this->isPage,  $this->model->text);
  }


  public function newAdmins($params = ''){ 
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->doNewAdministrator($data->doit)){
         http_response_code(200);
         echo json_encode($p);
     }else{
         http_response_code(200);
         echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"aqq ".e('Fehler, versuche es erneut!')));
     } 
  }

  public function getAdmins($params = ''){ 
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->getAdminsList($data->doit)){
         http_response_code(200);
         echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>$p));
     }else{
         http_response_code(200);
         echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"az ".e('Fehler, versuche es erneut!')));
     } 
  }


  public function dellparty($params = ''){ 
    if(Main::bot()) return $this->content("_404");
    $data = json_decode(file_get_contents("php://input"));
    if($p = $this->model->doDeleteAdminis($data->doit)){
         http_response_code(200);
         echo json_encode($p);
     }else{
         http_response_code(200);
         echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"aqq ".e('Fehler, versuche es erneut!')));
     } 
  }


  


  
  
}
?>