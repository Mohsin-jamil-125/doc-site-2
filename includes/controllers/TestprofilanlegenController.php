<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;


class TestprofilanlegenController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Testprofilanlegen', $this->config, $this->db);
    }


    public function index($params = ''){
        $this->model->checkIsLogged();
        $this->isPage = 'kandidat/testprofilanlegen';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->view($this->isPage,  $this->model->text);
    }

/*
    public function theregister($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->usranlegen($data->doit)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
        } 
    } 
*/

    public function getdropvals($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
       /* if($p = $this->model->usranlegen($data->doit)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>"Errorrr"));
        }
        
        '.Main::dropdowns('positionen', true).'   wheres:wheres, wherestoo:wherestoo
        
        */

        if($p =$data->doit){
           // $web = json_decode($this->config['dropdowns'], true); reldropdowns($conf)
           // $rr = $web[$data->woo];
            
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>Main::reldropdowns($data->doit, $data->wheres, $data->wherestoo)));
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    } 


    public function getdropvalsFilter($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));  //Main::returnValue('dropselect', 'dokmedic', 'subjectjob');
        if($p =$data->doit){
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>Main::returnValue('dropselect', $data->doit, 'positionjob')));
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    } 



    public function addalerts($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        // if($p = $this->model->kandidatenAlerts($data->doit)){
        //   http_response_code(200);
        //   echo json_encode($p);
        // }else{
        //    http_response_code(200);
        //    echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        // } 
        http_response_code(200);
        echo json_encode(array("user_error"=>1, "user_reason"=>1, "msg"=>e('Erfolg!')));
      }


    
}
?>

