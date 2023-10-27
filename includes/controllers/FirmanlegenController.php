<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;


class FirmanlegenController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Firmanlegen', $this->config, $this->db);
    }


    public function index($params = ''){
        $this->model->checkIsLogged();
        $this->isPage = 'firma/firmanlegen';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->model->getPruffEmail();

        $this->view($this->isPage,  $this->model->text);
    }


    public function thefrmregister($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->frmanlegen($data->doit)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    } 


    
}
?>

