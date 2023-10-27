<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class EditeinzelheitenController extends Controller {
    protected $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config=$config;
  	   $this->db=$db;

       $this->model = $this->model('Editeinzelheiten', $this->config, $this->db);
    }

    public function index($params = ''){

        if(!$params && (!$params[0] || empty($params[0]))){
            Main::redirect("company");
            exit;
        }

        $this->model->checkIsLogged('firma');

        $this->isPage = 'firma/editeinzelheiten';
        $params = func_get_args();
        $this->model->text['params'] = $params;

        $this->model->getAnsprechpartners();

        $this->model->text["datain"] = ["confs"=>json_decode(Main::config('dropdowns'), true), "params"=>["frmname"=>ucfirst($this->model->text['u']['frmname']), "frmdate"=>main::nice_date($this->model->text['u']['created'])]];

        $this->model->getAnzeigen($params[0]);

        $this->model->getStatsPayiment();
  
        $this->view($this->isPage,  $this->model->text);
    }


    

    public function editanzeigen($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->editAnzeigge($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }

    public function eduploadedbildanz($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->anzEdLogo($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function uploadvideoseddanz($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->anzEdVideo($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    
 


    
}
?>