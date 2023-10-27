<?php
namespace App\includes\controllers;
use App\includes\Controller;
use App\includes\Main;

class DashprofilController extends Controller {

    protected $user, $model;
    protected $db, $config = [];

    public function __construct($db, $config){
       $this->config = $config;
  	   $this->db = $db;

       $this->model = $this->model('Dashprofil', $this->config, $this->db);
    }


    public function index($params = ''){

        $this->model->checkIsLogged('kandidat');

        $this->isPage = 'kandidat/dashprofil';
        $params = func_get_args();
        $this->model->text['params'] = $params;

  
        $this->view($this->isPage, $this->model->text);
    }


    public function addberwerbe($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->kandidatBewerbt($data)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }
    
    public function addlibste($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->kandidatLiebste($data)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }


    public function kandidatUpdate($params = ''){
       if(Main::bot()) return $this->content("_404");
       $data = json_decode(file_get_contents("php://input"));
       if($p = $this->model->kandidatEdit($data->doit)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function kandidatUpdateKalif($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->kandidatEditKalif($data->doit)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function douploadavatar($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->kandidatUploadAvatar($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function uploadvidkandidat($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->kandidatUploadVideos($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }

    


    public function douplolebens($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->kandidatUploadLebens($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function douplopts($params = ''){
        if(Main::bot()) return $this->content("_404");
        if($p = $this->model->kandidatUploadOpts($_FILES, $_POST)){
            http_response_code(200);
            echo json_encode($p);
        }else{
            http_response_code(200);
            echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
        } 
    }


    public function doweiters($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->kandidatWeiters($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }


    public function delebenslauf($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->deletelebenslauf($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }

    public function delavatar($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->deleteavatar($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }



    public function deledateien($params = ''){
        if(Main::bot()) return $this->content("_404");
        $data = json_decode(file_get_contents("php://input"));
        if($p = $this->model->deleteoptdatains($data->doit)){
             http_response_code(200);
             echo json_encode($p);
         }else{
             http_response_code(200);
             echo json_encode(array("user_error"=>1, "user_reason"=>2, "msg"=>e('Fehler, versuche es erneut!')));
         } 
    }



    


    

    

    



    
 

    
}
?>